<?php

namespace App\Services;

use App\Models\CompetencyExam;
use Illuminate\Support\Collection;

/**
 * Logika UKK Stage 11.
 *
 * - Satu baris competency_exams = satu siswa + satu mapel pada
 *   satu exam_date. "Jadwal ujian" adalah pengelompokan menurut
 *   (subject_id, exam_date); "peserta" adalah baris dengan score
 *   null pada jadwal itu.
 * - Komponen UKK: bila satu mapel diuji lewat beberapa komponen,
 *   komponen dibedakan lewat baris "Komponen: ..." di kolom note
 *   (keputusan Stage 11, lihat STAGE-11-PROGRESS.md). Tidak ada
 *   tabel skema/komponen UKK di skema yang ada.
 * - TIDAK ADA ambang kelulusan yang ditetapkan sekolah: rekap
 *   hanya menampilkan skor (rata-rata), tanpa label lulus/tidak
 *   lulus. Nilai UKK TIDAK masuk ke rapor.
 */
class CompetencyExamService
{
    /**
     * Daftar jadwal ujian: satu baris per (subject_id, exam_date),
     * beserta jumlah peserta dan rata-rata nilai yang sudah terisi.
     *
     * @return \Illuminate\Support\Collection<int, array{subject_id: int, subject: \App\Models\Subject|null, exam_date: string|null, participants: int, scored: int, average: float|null}>
     */
    public function schedules($query = null): Collection
    {
        $rows = ($query ?? CompetencyExam::query()->with(['subject', 'student']))
            ->orderBy('exam_date')
            ->orderBy('subject_id')
            ->get();

        return $rows->groupBy(fn ($r) => $r->subject_id.'|'.($r->exam_date?->toDateString() ?? ''))
            ->map(function ($group) {
                $first = $group->first();
                $scored = $group->whereNotNull('score');

                return [
                    'subject_id' => $first->subject_id,
                    'subject' => $first->subject,
                    'exam_date' => $first->exam_date?->toDateString(),
                    'participants' => $group->count(),
                    'scored' => $scored->count(),
                    'average' => $scored->isEmpty()
                        ? null
                        : round($scored->avg(fn ($r) => (float) $r->score), 2, PHP_ROUND_HALF_UP),
                ];
            })
            ->values();
    }

    /**
     * Rekap UKK per siswa: rata-rata per mapel + rata-rata gabungan.
     *
     * @return array{student_id: int, per_subject: array<int, array{subject_id: int, subject: \App\Models\Subject|null, count: int, average: float|null}>, average: float|null, total: int}
     */
    public function recapForStudent(int $studentId): array
    {
        $rows = CompetencyExam::query()
            ->with('subject')
            ->where('student_id', $studentId)
            ->orderBy('exam_date')
            ->get();

        $perSubject = $rows->groupBy('subject_id')->map(function ($group, $subjectId) {
            $scored = $group->whereNotNull('score');

            return [
                'subject_id' => (int) $subjectId,
                'subject' => $group->first()->subject,
                'count' => $group->count(),
                'average' => $scored->isEmpty()
                    ? null
                    : round($scored->avg(fn ($r) => (float) $r->score), 2, PHP_ROUND_HALF_UP),
            ];
        })->values()->all();

        $scoredAll = $rows->whereNotNull('score');

        return [
            'student_id' => $studentId,
            'per_subject' => $perSubject,
            'average' => $scoredAll->isEmpty()
                ? null
                : round($scoredAll->avg(fn ($r) => (float) $r->score), 2, PHP_ROUND_HALF_UP),
            'total' => $rows->count(),
        ];
    }

    /**
     * Rekap per mapel: rata-rata seluruh peserta yang sudah dinilai.
     *
     * @return array<int, array{subject_id: int, subject: \App\Models\Subject|null, participants: int, scored: int, average: float|null}>
     */
    public function recapPerSubject($query = null): array
    {
        $rows = ($query ?? CompetencyExam::query()->with('subject'))->get();

        return $rows->groupBy('subject_id')->map(function ($group, $subjectId) {
            $scored = $group->whereNotNull('score');

            return [
                'subject_id' => (int) $subjectId,
                'subject' => $group->first()->subject,
                'participants' => $group->count(),
                'scored' => $scored->count(),
                'average' => $scored->isEmpty()
                    ? null
                    : round($scored->avg(fn ($r) => (float) $r->score), 2, PHP_ROUND_HALF_UP),
            ];
        })->values()->all();
    }

    /**
     * Susun kolom note dari penguji (konvensi "Penguji: ...") dan
     * catatan bebas. Baris penguji selalu di baris pertama bila ada.
     */
    public static function buildNote(?string $examiner, ?string $note): ?string
    {
        $parts = [];

        if ($examiner !== null && trim($examiner) !== '') {
            $parts[] = 'Penguji: '.trim($examiner);
        }

        if ($note !== null && trim($note) !== '') {
            $parts[] = trim($note);
        }

        return $parts === [] ? null : implode("\n", $parts);
    }
}
