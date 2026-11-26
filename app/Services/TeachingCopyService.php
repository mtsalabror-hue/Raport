<?php

namespace App\Services;

use App\Models\Semester;
use App\Models\TeachingAssignment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Fase E (RDM): Salin Mengajar — menyalin seluruh penugasan mengajar
 * dari satu semester ke semester lain (biasanya Ganjil ke Genap dalam
 * tahun ajaran yang sama; lintas tahun ajaran diizinkan).
 *
 * Penugasan hasil salinan memakai academic_year_id dari semester
 * tujuan (bukan asal), supaya tahun ajaran dan semester konsisten.
 * Duplikat (guru + mapel + kelas + tahun ajaran + semester tujuan
 * sudah ada) dilewati, tidak diduplikasi.
 */
class TeachingCopyService
{
    /**
     * @return array{copied: int, skipped: int, source: Semester, target: Semester}
     */
    public function copy(int $fromSemesterId, int $toSemesterId): array
    {
        if ($fromSemesterId === $toSemesterId) {
            throw ValidationException::withMessages([
                'to_semester_id' => 'Semester tujuan harus berbeda dengan semester asal.',
            ]);
        }

        $source = Semester::with('academicYear')->findOrFail($fromSemesterId);
        $target = Semester::with('academicYear')->findOrFail($toSemesterId);

        $assignments = TeachingAssignment::query()
            ->where('semester_id', $fromSemesterId)
            ->orderBy('teacher_id')
            ->orderBy('class_id')
            ->get();

        if ($assignments->isEmpty()) {
            throw ValidationException::withMessages([
                'from_semester_id' => 'Semester asal tidak memiliki penugasan mengajar untuk disalin.',
            ]);
        }

        $copied = 0;
        $skipped = 0;

        DB::transaction(function () use ($assignments, $target, &$copied, &$skipped) {
            foreach ($assignments as $assignment) {
                $exists = TeachingAssignment::query()
                    ->where('teacher_id', $assignment->teacher_id)
                    ->where('subject_id', $assignment->subject_id)
                    ->where('class_id', $assignment->class_id)
                    ->where('academic_year_id', $target->academic_year_id)
                    ->where('semester_id', $target->id)
                    ->exists();

                if ($exists) {
                    $skipped++;
                    continue;
                }

                TeachingAssignment::create([
                    'teacher_id' => $assignment->teacher_id,
                    'subject_id' => $assignment->subject_id,
                    'class_id' => $assignment->class_id,
                    'academic_year_id' => $target->academic_year_id,
                    'semester_id' => $target->id,
                    'hours' => $assignment->hours,
                ]);
                $copied++;
            }
        });

        return [
            'copied' => $copied,
            'skipped' => $skipped,
            'source' => $source,
            'target' => $target,
        ];
    }
}
