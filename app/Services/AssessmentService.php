<?php

namespace App\Services;

use App\Models\AssessmentComponent;
use App\Models\AssessmentScore;
use App\Models\SystemSetting;
use Illuminate\Validation\ValidationException;

/**
 * Logika perhitungan penilaian Stage 7.
 *
 * Rumus nilai akhir (sesuai DRAF-KEPUTUSAN-FORMULA-STAGE-07.md, masih
 * menunggu konfirmasi sekolah; angka default di bawah ini adalah usulan):
 *
 *   Nilai akhir = (Σ skor × bobot) / (Σ bobot komponen yang ada nilainya)
 *
 * Hanya komponen aktif yang dihitung. Komponen yang nilainya kosong
 * dikeluarkan dari penyebut (bukan dianggap 0). Pembulatan 2 desimal,
 * half up. Jika tidak ada satu pun komponen bernilai, hasilnya null.
 */
class AssessmentService
{
    public const DEFAULT_KKTP = 75;

    public const ROUND_PRECISION = 2;

    /**
     * Komponen aktif untuk sebuah mapel/kurikulum.
     * Komponen khusus mapel didahulukan; jika tidak ada, dipakai komponen
     * global (subject_id null). Aturan yang sama untuk kurikulum.
     */
    public function componentsFor(?int $subjectId, ?int $curriculumId = null)
    {
        $subjectSpecific = AssessmentComponent::query()
            ->where('is_active', true)
            ->where('subject_id', $subjectId)
            ->when($curriculumId, fn ($q) => $q->where(function ($q) use ($curriculumId) {
                $q->where('curriculum_id', $curriculumId)->orWhereNull('curriculum_id');
            }))
            ->get();

        if ($subjectSpecific->isNotEmpty()) {
            return $subjectSpecific;
        }

        return AssessmentComponent::query()
            ->where('is_active', true)
            ->whereNull('subject_id')
            ->when($curriculumId, fn ($q) => $q->where(function ($q) use ($curriculumId) {
                $q->where('curriculum_id', $curriculumId)->orWhereNull('curriculum_id');
            }))
            ->get();
    }

    /**
     * Validasi total bobot komponen aktif harus tepat 100.
     * Dipanggil sebelum perhitungan nilai akhir dijalankan.
     *
     * @throws ValidationException
     */
    public function validateWeights(?int $subjectId, ?int $curriculumId = null): float
    {
        $total = (float) $this->componentsFor($subjectId, $curriculumId)->sum('weight');

        if (abs($total - 100.0) > 0.009) {
            throw ValidationException::withMessages([
                'weight' => "Total bobot komponen aktif adalah {$total}, harus tepat 100 sebelum menghitung nilai akhir.",
            ]);
        }

        return $total;
    }

    /**
     * Hitung nilai akhir seorang siswa untuk satu mapel pada satu periode.
     * Mengembalikan null jika tidak ada komponen yang bernilai.
     *
     * @throws ValidationException jika total bobot aktif bukan 100.
     */
    public function finalScore(int $studentId, int $subjectId, int $academicYearId, int $semesterId, ?int $curriculumId = null): ?float
    {
        $this->validateWeights($subjectId, $curriculumId);

        $scores = AssessmentScore::query()
            ->with('component')
            ->where('student_id', $studentId)
            ->where('subject_id', $subjectId)
            ->where('academic_year_id', $academicYearId)
            ->where('semester_id', $semesterId)
            ->whereNotNull('score')
            ->get();

        if ($scores->isEmpty()) {
            return null;
        }

        $sum = 0.0;
        $weightSum = 0.0;

        foreach ($scores as $score) {
            $weight = (float) ($score->component->weight ?? 0);
            $sum += (float) $score->score * $weight;
            $weightSum += $weight;
        }

        if ($weightSum <= 0) {
            return null;
        }

        return round($sum / $weightSum, self::ROUND_PRECISION, PHP_ROUND_HALF_UP);
    }

    /**
     * KKTP untuk sebuah mapel: nilai KKTP terendah di antara komponen
     * aktif mapel tersebut, atau default global dari SystemSetting.
     */
    public function kktpFor(?int $subjectId, ?int $curriculumId = null): float
    {
        $components = $this->componentsFor($subjectId, $curriculumId);

        if ($components->isNotEmpty()) {
            return (float) $components->min('kktp');
        }

        $setting = SystemSetting::query()->where('key', 'kktp_default')->first();

        return (float) ($setting->value['value'] ?? self::DEFAULT_KKTP);
    }

    /**
     * Deskripsi capaian berdasarkan nilai akhir dan KKTP.
     * Guru dapat mengubah teks ini; fungsi ini hanya memberi draf awal
     * yang relevan dengan hasil penilaian.
     */
    public function descriptionFor(float $finalScore, float $kktp, ?string $subjectName = null): string
    {
        $subject = $subjectName ? " pada {$subjectName}" : '';
        $score = number_format($finalScore, 2, ',', '.');
        $kktpText = number_format($kktp, 0, ',', '.');

        if ($finalScore >= $kktp) {
            return "Mencapai KKTP ({$kktpText}){$subject} dengan nilai akhir {$score}. Peserta didik menunjukkan penguasaan capaian pembelajaran yang diharapkan.";
        }

        return "Belum mencapai KKTP ({$kktpText}){$subject}, nilai akhir {$score}. Peserta didik perlu peningkatan dan pendampingan pada capaian pembelajaran yang belum dikuasai.";
    }

    /**
     * Rekap nilai akhir seluruh siswa dalam satu kelas + mapel + periode.
     *
     * @return array<int, array{student_id: int, final_score: float|null, kktp: float, passed: bool|null}>
     */
    public function recap(int $classId, int $subjectId, int $academicYearId, int $semesterId, ?int $curriculumId = null): array
    {
        $kktp = $this->kktpFor($subjectId, $curriculumId);

        $studentIds = AssessmentScore::query()
            ->where('class_id', $classId)
            ->where('subject_id', $subjectId)
            ->where('academic_year_id', $academicYearId)
            ->where('semester_id', $semesterId)
            ->distinct()
            ->pluck('student_id');

        $results = [];

        foreach ($studentIds as $studentId) {
            $final = $this->finalScore($studentId, $subjectId, $academicYearId, $semesterId, $curriculumId);

            $results[] = [
                'student_id' => $studentId,
                'final_score' => $final,
                'kktp' => $kktp,
                'passed' => $final === null ? null : $final >= $kktp,
            ];
        }

        return $results;
    }
}
