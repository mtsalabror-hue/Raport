<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\ActivityLog;
use App\Models\PromotionDecision;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Fase D (RDM): kenaikan kelas & kelulusan.
 *
 * Aturan urutan (seperti RDM): luluskan kelas XII dulu ke Alumni,
 * baru naikkan kelas di bawahnya. Menaikkan kelas ditolak selama
 * masih ada siswa kelas XII yang diputuskan naik pada periode
 * berjalan tetapi belum diluluskan.
 *
 * Kelulusan: siswa kelas XII dengan keputusan "naik" menjadi alumni
 * (class_id dikosongkan, is_alumni = true, alumni_year = tahun
 * ajaran yang berakhir); datanya tidak dihapus. Siswa yang
 * diputuskan "tidak naik" (atau belum diputuskan) tetap di kelas
 * asal, baik di kelas XII maupun saat naik kelas.
 *
 * Semua perubahan berjalan dalam satu transaksi database.
 */
class PromotionService
{
    public const LEVELS = ['X', 'XI', 'XII'];

    public const FINAL_LEVEL = 'XII';

    /**
     * Semester Genap dikenali dari namanya (konvensi aplikasi:
     * semester bernama "Ganjil" / "Genap").
     */
    public static function isEvenSemester(Semester $semester): bool
    {
        return strtolower(trim($semester->name)) === 'genap';
    }

    public function decisionFor(int $studentId, int $academicYearId, int $semesterId): ?PromotionDecision
    {
        return PromotionDecision::query()
            ->where('student_id', $studentId)
            ->where('academic_year_id', $academicYearId)
            ->where('semester_id', $semesterId)
            ->first();
    }

    /**
     * Siswa kelas XII pada tahun ajaran berjalan yang diputuskan naik
     * pada periode ini tetapi belum diluluskan (bukan alumni).
     * Merekalah yang menghalangi proses naik kelas.
     */
    public function pendingGraduationCount(int $academicYearId, int $semesterId): int
    {
        $xiiClassIds = SchoolClass::query()
            ->where('academic_year_id', $academicYearId)
            ->where('level', self::FINAL_LEVEL)
            ->pluck('id');

        return PromotionDecision::query()
            ->where('academic_year_id', $academicYearId)
            ->where('semester_id', $semesterId)
            ->where('status', PromotionDecision::STATUS_NAIK)
            ->whereIn('class_id', $xiiClassIds)
            ->whereHas('student', fn ($q) => $q->where('is_alumni', false))
            ->count();
    }

    /**
     * Langkah 1 — luluskan kelas XII: siswa kelas XII dengan
     * keputusan "naik" menjadi alumni.
     *
     * @return array{graduated: int, stayed: int}
     *
     * @throws ValidationException bila semester bukan Genap.
     */
    public function graduate(int $academicYearId, int $semesterId, User $admin): array
    {
        $semester = Semester::findOrFail($semesterId);
        $this->assertEvenSemester($semester);

        $academicYear = AcademicYear::findOrFail($academicYearId);

        $xiiClassIds = SchoolClass::query()
            ->where('academic_year_id', $academicYearId)
            ->where('level', self::FINAL_LEVEL)
            ->pluck('id');

        $graduated = 0;
        $stayed = 0;

        DB::transaction(function () use ($xiiClassIds, $academicYearId, $semesterId, $academicYear, $admin, &$graduated, &$stayed) {
            $students = Student::query()
                ->whereIn('class_id', $xiiClassIds)
                ->where('is_alumni', false)
                ->orderBy('name')
                ->get();

            foreach ($students as $student) {
                $decision = $this->decisionFor($student->id, $academicYearId, $semesterId);

                if ($decision && $decision->status === PromotionDecision::STATUS_NAIK) {
                    $student->update([
                        'class_id' => null,
                        'is_alumni' => true,
                        'alumni_year' => $academicYear->name,
                    ]);

                    $this->log($admin->id, 'promotion.graduated', $student, [
                        'academic_year_id' => $academicYearId,
                        'semester_id' => $semesterId,
                        'alumni_year' => $academicYear->name,
                    ]);

                    $graduated++;
                } else {
                    // Tidak naik atau belum diputuskan: tetap di kelas XII.
                    $stayed++;
                }
            }
        });

        return ['graduated' => $graduated, 'stayed' => $stayed];
    }

    /**
     * Langkah 2 — naikkan kelas sesuai pemetaan kelas asal ke kelas
     * tujuan. Hanya siswa dengan keputusan "naik" yang pindah;
     * siswa "tidak naik" atau belum diputuskan tetap di kelas asal.
     *
     * @param  array<int, array{from_class_id: int, to_class_id: int}>  $mappings
     * @return array{promoted: int, stayed: int, details: array<int, array{from: string, to: string, promoted: int, stayed: int}>}
     *
     * @throws ValidationException bila semester bukan Genap, pemetaan
     *         tidak valid, atau masih ada siswa XII yang belum
     *         diluluskan.
     */
    public function promote(array $mappings, int $academicYearId, int $semesterId, User $admin): array
    {
        $semester = Semester::findOrFail($semesterId);
        $this->assertEvenSemester($semester);

        $pending = $this->pendingGraduationCount($academicYearId, $semesterId);

        if ($pending > 0) {
            throw ValidationException::withMessages([
                'promotion' => "Masih ada {$pending} siswa kelas XII yang diputuskan naik tetapi belum diluluskan. Luluskan kelas XII dulu sebelum menaikkan kelas.",
            ]);
        }

        $classes = SchoolClass::query()->whereIn('id', collect($mappings)->flatMap(
            fn ($m) => [$m['from_class_id'], $m['to_class_id']]
        )->unique()->values())->get()->keyBy('id');

        foreach ($mappings as $mapping) {
            $from = $classes[$mapping['from_class_id']] ?? null;
            $to = $classes[$mapping['to_class_id']] ?? null;

            if (! $from || ! $to) {
                throw ValidationException::withMessages([
                    'mappings' => 'Pemetaan kelas tidak valid: ada kelas asal atau kelas tujuan yang tidak ditemukan.',
                ]);
            }

            if ($from->level === self::FINAL_LEVEL) {
                throw ValidationException::withMessages([
                    'mappings' => "Kelas {$from->name} adalah kelas XII dan tidak dapat dinaikkan; gunakan Luluskan Kelas XII.",
                ]);
            }

            $expectedTo = $this->nextLevel($from->level);

            if ($expectedTo === null || $to->level !== $expectedTo) {
                throw ValidationException::withMessages([
                    'mappings' => "Kelas {$from->name} (tingkat {$from->level}) hanya dapat dinaikkan ke tingkat {$expectedTo}.",
                ]);
            }
        }

        $promoted = 0;
        $stayed = 0;
        $details = [];

        // Rencana dulu, eksekusi kemudian: siswa diambil berdasarkan
        // kelas asal sebelum ada yang pindah, supaya pemetaan
        // berantai (X -> XI, XI -> XII) tidak memindahkan siswa yang
        // sama dua kali dalam satu proses.
        $plan = [];

        foreach ($mappings as $mapping) {
            $from = $classes[$mapping['from_class_id']];
            $to = $classes[$mapping['to_class_id']];

            $students = Student::query()
                ->where('class_id', $from->id)
                ->where('is_alumni', false)
                ->orderBy('name')
                ->get();

            $moves = [];
            $kept = 0;

            foreach ($students as $student) {
                $decision = $this->decisionFor($student->id, $academicYearId, $semesterId);

                if ($decision && $decision->status === PromotionDecision::STATUS_NAIK) {
                    $moves[] = $student;
                } else {
                    // Tidak naik atau belum diputuskan: tetap di kelas asal.
                    $kept++;
                }
            }

            $plan[] = ['from' => $from, 'to' => $to, 'moves' => $moves, 'kept' => $kept];
        }

        DB::transaction(function () use ($plan, $academicYearId, $semesterId, $admin, &$promoted, &$stayed, &$details) {
            foreach ($plan as $step) {
                $from = $step['from'];
                $to = $step['to'];

                foreach ($step['moves'] as $student) {
                    $student->update(['class_id' => $to->id]);

                    $this->log($admin->id, 'promotion.promoted', $student, [
                        'from_class_id' => $from->id,
                        'to_class_id' => $to->id,
                        'academic_year_id' => $academicYearId,
                        'semester_id' => $semesterId,
                    ]);
                }

                $promoted += count($step['moves']);
                $stayed += $step['kept'];
                $details[] = [
                    'from' => $from->name,
                    'to' => $to->name,
                    'promoted' => count($step['moves']),
                    'stayed' => $step['kept'],
                ];
            }
        });

        return ['promoted' => $promoted, 'stayed' => $stayed, 'details' => $details];
    }

    private function nextLevel(string $level): ?string
    {
        $index = array_search($level, self::LEVELS, true);

        if ($index === false || $index + 1 >= count(self::LEVELS)) {
            return null;
        }

        return self::LEVELS[$index + 1];
    }

    private function assertEvenSemester(Semester $semester): void
    {
        if (! self::isEvenSemester($semester)) {
            throw ValidationException::withMessages([
                'semester_id' => 'Kenaikan kelas dan kelulusan hanya dapat diproses pada semester Genap.',
            ]);
        }
    }

    private function log(int $userId, string $action, Student $student, array $metadata = []): void
    {
        ActivityLog::create([
            'user_id' => $userId,
            'action' => $action,
            'model_type' => Student::class,
            'model_id' => $student->id,
            'metadata' => $metadata,
        ]);
    }
}
