<?php

namespace App\Services;

use App\Models\ReportCard;
use App\Models\ReportCardSubject;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Validation\ValidationException;

/**
 * Leger kelas (Fase F / RDM): rekap nilai akhir per siswa per
 * mapel untuk satu kelas + semester.
 *
 * Pilihan sumber data (satu aturan konsisten per kelas + periode,
 * ditampilkan sebagai label di halaman leger):
 * - Bila SEMUA siswa kelas itu sudah punya rapor yang diterbitkan/
 *   dikunci pada periode tersebut, dipakai SNAPSHOT
 *   (report_card_subjects) — angka yang sama dengan rapor terbit.
 * - Bila belum, dipakai NILAI AKHIR LIVE dari AssessmentService
 *   (finalScore; komponen yang belum bernilai dikecualikan, bobot
 *   harus 100 — sel yang gagal dihitung tampil '-').
 * Aturan per kelas (bukan per siswa) supaya satu leger tidak
 * mencampur dua sumber dalam satu tabel.
 */
class LedgerService
{
    public function __construct(
        private ReportCompletenessService $completeness,
        private AssessmentService $scores,
    ) {
    }

    public function ledgerData(SchoolClass $class, int $academicYearId, int $semesterId): array
    {
        $subjects = $this->completeness->subjectsForClass($class->id, $academicYearId, $semesterId);

        $students = Student::query()
            ->where('class_id', $class->id)
            ->orderBy('name')
            ->get();

        $cards = ReportCard::query()
            ->where('class_id', $class->id)
            ->where('academic_year_id', $academicYearId)
            ->where('semester_id', $semesterId)
            ->get()
            ->keyBy('student_id');

        $allPublished = $students->isNotEmpty()
            && $students->every(fn (Student $s) => ($cards[$s->id] ?? null)?->isPublished());

        $source = $allPublished ? 'snapshot' : 'live';
        $sourceLabel = $allPublished
            ? 'Snapshot rapor terbit (report_card_subjects)'
            : 'Nilai akhir live (AssessmentService) — belum semua rapor diterbitkan';

        // Peta snapshot per (siswa, mapel) bila sumbernya snapshot.
        $snapshot = [];
        if ($allPublished) {
            $rows = ReportCardSubject::query()
                ->whereIn('report_card_id', $cards->pluck('id'))
                ->get();

            foreach ($rows as $row) {
                $studentId = $cards->firstWhere('id', $row->report_card_id)?->student_id;
                if ($studentId) {
                    $snapshot[$studentId][$row->subject_id] = $row->final_score !== null
                        ? (float) $row->final_score
                        : null;
                }
            }
        }

        $matrix = [];
        foreach ($students as $student) {
            $scores = [];
            $sum = 0.0;
            $count = 0;

            foreach ($subjects as $subject) {
                $value = null;

                if ($source === 'snapshot') {
                    $value = $snapshot[$student->id][$subject->id] ?? null;
                } else {
                    try {
                        $value = $this->scores->finalScore(
                            $student->id, $subject->id, $academicYearId, $semesterId
                        );
                    } catch (ValidationException) {
                        $value = null;
                    }
                }

                $scores[$subject->id] = $value;

                if ($value !== null) {
                    $sum += $value;
                    $count++;
                }
            }

            $matrix[] = [
                'student' => $student,
                'scores' => $scores,
                'average' => $count > 0 ? round($sum / $count, 2) : null,
            ];
        }

        return [
            'class' => $class,
            'subjects' => $subjects,
            'rows' => $matrix,
            'source' => $source,
            'source_label' => $sourceLabel,
            'student_count' => $students->count(),
        ];
    }
}
