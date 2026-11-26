<?php

namespace App\Services;

use App\Models\AssessmentScore;
use App\Models\ReportCard;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\Subject;
use App\Models\TeacherDescription;
use App\Models\TeachingAssignment;
use Illuminate\Validation\ValidationException;

/**
 * Logika kelengkapan rapor Stage 9.
 *
 * Daftar komponen wajib (keputusan sementara, menunggu penetapan
 * sekolah — lihat STAGE-09-PROGRESS.md):
 *   (1) semua mapel yang diajarkan di kelas itu punya nilai akhir
 *       yang sudah terkirim/dikunci untuk siswa tersebut;
 *   (2) ada minimal satu catatan presensi pada periode itu;
 *   (3) ada deskripsi capaian (teacher_descriptions) untuk tiap mapel;
 *   (4) ada catatan wali kelas (report_card_notes note_type
 *       wali_kelas) pada rapor siswa itu.
 *
 * Setiap kekurangan dikembalikan beserta tautan ke sumber datanya
 * (input nilai / presensi / deskripsi / catatan) agar wali kelas
 * dapat langsung memperbaikinya.
 *
 * Bentuk hasil check():
 *   ['student' => Student, 'complete' => bool,
 *    'missing' => [['code','message','url'], ...],
 *    'subjects' => Collection<Subject>,
 *    'attendance_count' => int, 'has_homeroom_note' => bool]
 */
class ReportCompletenessService
{
    public function __construct(private AssessmentService $scores)
    {
    }

    /**
     * Mapel yang diajarkan di sebuah kelas pada satu periode.
     * Diambil dari teaching_assignments; bila belum ada penugasan
     * sama sekali untuk kelas/periode itu, dipakai mapel yang sudah
     * punya nilai pada kelas/periode itu (pola soft-check Stage 7/8).
     */
    public function subjectsForClass(int $classId, int $academicYearId, int $semesterId)
    {
        $subjectIds = TeachingAssignment::query()
            ->where('class_id', $classId)
            ->where('academic_year_id', $academicYearId)
            ->where('semester_id', $semesterId)
            ->distinct()
            ->pluck('subject_id');

        if ($subjectIds->isEmpty()) {
            $subjectIds = AssessmentScore::query()
                ->where('class_id', $classId)
                ->where('academic_year_id', $academicYearId)
                ->where('semester_id', $semesterId)
                ->distinct()
                ->pluck('subject_id');
        }

        return Subject::query()
            ->whereIn('id', $subjectIds)
            ->orderBy('name')
            ->get();
    }

    /**
     * Periksa kelengkapan rapor satu siswa pada satu periode.
     */
    public function check(Student $student, int $classId, int $academicYearId, int $semesterId): array
    {
        $missing = [];
        $subjects = $this->subjectsForClass($classId, $academicYearId, $semesterId);

        foreach ($subjects as $subject) {
            $scoreUrl = '/guru/assessments?'.http_build_query([
                'class_id' => $classId,
                'subject_id' => $subject->id,
                'academic_year_id' => $academicYearId,
                'semester_id' => $semesterId,
            ]);

            $submitted = AssessmentScore::query()
                ->where('student_id', $student->id)
                ->where('subject_id', $subject->id)
                ->where('academic_year_id', $academicYearId)
                ->where('semester_id', $semesterId)
                ->whereIn('status', ['terkirim', 'dikunci'])
                ->exists();

            if (! $submitted) {
                $missing[] = [
                    'code' => 'nilai_belum_terkirim',
                    'message' => "Nilai akhir {$subject->name} belum terkirim untuk {$student->name}.",
                    'url' => $scoreUrl,
                    'subject_id' => $subject->id,
                ];

                continue;
            }

            try {
                $final = $this->scores->finalScore(
                    $student->id,
                    $subject->id,
                    $academicYearId,
                    $semesterId
                );
            } catch (ValidationException $e) {
                $missing[] = [
                    'code' => 'nilai_bobot_tidak_valid',
                    'message' => "Nilai akhir {$subject->name} tidak dapat dihitung: ".($e->errors()['weight'][0] ?? 'total bobot tidak valid.'),
                    'url' => $scoreUrl,
                    'subject_id' => $subject->id,
                ];

                continue;
            }

            if ($final === null) {
                $missing[] = [
                    'code' => 'nilai_akhir_kosong',
                    'message' => "Nilai akhir {$subject->name} untuk {$student->name} masih kosong.",
                    'url' => $scoreUrl,
                    'subject_id' => $subject->id,
                ];
            }

            $hasDescription = TeacherDescription::query()
                ->where('student_id', $student->id)
                ->where('subject_id', $subject->id)
                ->where('academic_year_id', $academicYearId)
                ->where('semester_id', $semesterId)
                ->exists();

            if (! $hasDescription) {
                $missing[] = [
                    'code' => 'deskripsi_belum_ada',
                    'message' => "Deskripsi capaian {$subject->name} untuk {$student->name} belum ada.",
                    'url' => '/guru/descriptions',
                    'subject_id' => $subject->id,
                ];
            }
        }

        $attendanceCount = StudentAttendance::query()
            ->where('student_id', $student->id)
            ->where('class_id', $classId)
            ->where('academic_year_id', $academicYearId)
            ->where('semester_id', $semesterId)
            ->count();

        if ($attendanceCount === 0) {
            $missing[] = [
                'code' => 'presensi_belum_ada',
                'message' => "Belum ada catatan presensi untuk {$student->name} pada periode ini.",
                'url' => '/guru/attendance?'.http_build_query([
                    'class_id' => $classId,
                    'academic_year_id' => $academicYearId,
                    'semester_id' => $semesterId,
                ]),
                'subject_id' => null,
            ];
        }

        $reportCard = ReportCard::query()
            ->where('student_id', $student->id)
            ->where('academic_year_id', $academicYearId)
            ->where('semester_id', $semesterId)
            ->first();

        $hasNote = $reportCard !== null && $reportCard->notes()
            ->where('note_type', 'wali_kelas')
            ->exists();

        if (! $hasNote) {
            $missing[] = [
                'code' => 'catatan_wali_belum_ada',
                'message' => "Catatan wali kelas untuk {$student->name} belum ada.",
                'url' => '/wali/notes?'.http_build_query([
                    'student_id' => $student->id,
                    'academic_year_id' => $academicYearId,
                    'semester_id' => $semesterId,
                ]),
                'subject_id' => null,
            ];
        }

        return [
            'student' => $student,
            'complete' => empty($missing),
            'missing' => $missing,
            'subjects' => $subjects,
            'attendance_count' => $attendanceCount,
            'has_homeroom_note' => $hasNote,
            'report_card' => $reportCard,
        ];
    }

    /**
     * Periksa kelengkapan seluruh siswa dalam satu kelas + periode.
     *
     * @return array<int, array> hasil check() per siswa, urut nama.
     */
    public function checkClass(int $classId, int $academicYearId, int $semesterId): array
    {
        $students = Student::query()
            ->where('class_id', $classId)
            ->orderBy('name')
            ->get();

        $results = [];

        foreach ($students as $student) {
            $results[] = $this->check($student, $classId, $academicYearId, $semesterId);
        }

        return $results;
    }
}
