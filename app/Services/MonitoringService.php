<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\AssessmentScore;
use App\Models\ReportCard;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\TeachingAssignment;
use Illuminate\Support\Collection;

/**
 * Data pemantauan untuk dashboard Kepala Sekolah dan Waka
 * Kurikulum (Fase A): status pengiriman nilai guru per mapel dan
 * kelengkapan rapor per kelas pada periode aktif. Hanya-baca;
 * satu-satunya aksi tulis kepala sekolah (kunci rapor) ada di
 * ReportCardService::lock, bukan di sini.
 */
class MonitoringService
{
    public function __construct(private ReportCompletenessService $completeness)
    {
    }

    public function activePeriod(): array
    {
        return [
            'academicYear' => AcademicYear::where('is_active', true)->first(),
            'semester' => Semester::where('is_active', true)->first(),
        ];
    }

    /**
     * Status nilai per pasangan guru-mapel pada satu periode.
     * Baris dasar diambil dari teaching_assignments agar pasangan
     * yang belum menginput nilai pun terlihat (total 0); pasangan
     * yang punya nilai tanpa penugasan ikut ditambahkan.
     *
     * Tiap baris: ['teacher' => Teacher|null, 'subject' => Subject|null,
     * 'total' => int, 'draft' => int, 'terkirim' => int, 'dikunci' => int]
     */
    public function scoreStatus(int $academicYearId, int $semesterId): Collection
    {
        $rows = collect();

        $assignments = TeachingAssignment::query()
            ->with(['teacher', 'subject'])
            ->where('academic_year_id', $academicYearId)
            ->where('semester_id', $semesterId)
            ->get();

        foreach ($assignments as $assignment) {
            $rows->put($assignment->teacher_id.'-'.$assignment->subject_id, [
                'teacher' => $assignment->teacher,
                'subject' => $assignment->subject,
                'total' => 0,
                'draft' => 0,
                'terkirim' => 0,
                'dikunci' => 0,
            ]);
        }

        $counts = AssessmentScore::query()
            ->where('academic_year_id', $academicYearId)
            ->where('semester_id', $semesterId)
            ->selectRaw('teacher_id, subject_id, status, COUNT(*) as c')
            ->groupBy(['teacher_id', 'subject_id', 'status'])
            ->get();

        foreach ($counts as $count) {
            $key = $count->teacher_id.'-'.$count->subject_id;

            if (! $rows->has($key)) {
                $rows->put($key, [
                    'teacher' => $count->teacher,
                    'subject' => $count->subject,
                    'total' => 0,
                    'draft' => 0,
                    'terkirim' => 0,
                    'dikunci' => 0,
                ]);
            }

            $row = $rows->get($key);
            $status = in_array($count->status, ['draft', 'terkirim', 'dikunci'], true)
                ? $count->status
                : 'draft';
            $row[$status] += (int) $count->c;
            $row['total'] += (int) $count->c;
            $rows->put($key, $row);
        }

        return $rows->values()->sortBy([
            fn ($a, $b) => strcmp($a['teacher']?->name ?? '', $b['teacher']?->name ?? ''),
            fn ($a, $b) => strcmp($a['subject']?->name ?? '', $b['subject']?->name ?? ''),
        ])->values();
    }

    /**
     * Kelengkapan rapor per kelas pada satu periode.
     * Tiap baris: ['class' => SchoolClass, 'students' => int,
     * 'complete' => int, 'cards' => ['draft' => n, ...]]
     * 'complete' dihitung dengan ReportCompletenessService::check
     * per siswa (komponen wajib yang sama dengan alur wali).
     */
    public function classCompleteness(int $academicYearId, int $semesterId): Collection
    {
        $classes = SchoolClass::query()
            ->where('academic_year_id', $academicYearId)
            ->orderBy('name')
            ->get();

        return $classes->map(function (SchoolClass $class) use ($academicYearId, $semesterId) {
            $checks = $this->completeness->checkClass($class->id, $academicYearId, $semesterId);

            $cards = ReportCard::query()
                ->where('class_id', $class->id)
                ->where('academic_year_id', $academicYearId)
                ->where('semester_id', $semesterId)
                ->selectRaw('status, COUNT(*) as c')
                ->groupBy('status')
                ->pluck('c', 'status')
                ->all();

            return [
                'class' => $class,
                'students' => count($checks),
                'complete' => collect($checks)->where('complete', true)->count(),
                'cards' => $cards,
            ];
        });
    }

    /**
     * Rapor berstatus diterbitkan (siap dikunci kepala sekolah)
     * pada satu periode, lengkap dengan siswa + kelas.
     */
    public function publishedReportCards(int $academicYearId, int $semesterId): Collection
    {
        return ReportCard::query()
            ->with(['student', 'schoolClass'])
            ->where('academic_year_id', $academicYearId)
            ->where('semester_id', $semesterId)
            ->where('status', 'diterbitkan')
            ->orderBy('class_id')
            ->get();
    }

    public function lockedCount(int $academicYearId, int $semesterId): int
    {
        return ReportCard::query()
            ->where('academic_year_id', $academicYearId)
            ->where('semester_id', $semesterId)
            ->where('status', 'dikunci')
            ->count();
    }
}
