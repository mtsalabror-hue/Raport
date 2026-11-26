<?php

namespace App\Http\Controllers\Wali;

use App\Http\Controllers\Controller;
use App\Models\CompetencyExam;
use App\Models\HomeroomAssignment;
use App\Models\Internship;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use App\Services\CompetencyExamService;
use App\Services\InternshipService;
use Illuminate\Http\Request;

/**
 * Akses wali kelas untuk PKL/UKK (Stage 11): hanya-baca, dibatasi
 * pada siswa di kelas walinya (homeroom_assignments, periode mana
 * pun — PKL/UKK tidak punya kolom tahun ajaran/semester).
 * Semua pemeriksaan dilakukan di backend.
 */
class InternshipController extends Controller
{
    public function __construct(
        private InternshipService $internships,
        private CompetencyExamService $exams,
    ) {
    }

    private function resolveTeacher(Request $request): Teacher
    {
        $user = $request->user();

        if ($user && $user->teacher_id) {
            return Teacher::findOrFail($user->teacher_id);
        }

        if (Teacher::count() === 1) {
            return Teacher::first();
        }

        abort(403, 'Akun wali kelas belum terhubung ke data guru. Minta admin menghubungkan akun ini di data pengguna.');
    }

    /** Kelas-kelas wali yang login (periode mana pun). */
    private function myClasses(int $teacherId)
    {
        $classIds = HomeroomAssignment::query()
            ->where('teacher_id', $teacherId)
            ->pluck('class_id')
            ->unique()
            ->values();

        return SchoolClass::query()->whereIn('id', $classIds)->orderBy('name')->get();
    }

    private function assertHomeroom(int $teacherId, int $classId): void
    {
        $exists = HomeroomAssignment::query()
            ->where('teacher_id', $teacherId)
            ->where('class_id', $classId)
            ->exists();

        if (! $exists) {
            abort(403, 'Kelas ini bukan kelas wali Anda.');
        }
    }

    public function index(Request $request)
    {
        $filters = $request->validate([
            'class_id' => 'nullable|integer',
            'student_id' => 'nullable|integer',
        ]);

        $teacher = $this->resolveTeacher($request);
        $classes = $this->myClasses($teacher->id);
        $classIds = $classes->pluck('id');

        if (! empty($filters['class_id'])) {
            $this->assertHomeroom($teacher->id, $filters['class_id']);
            $classIds = collect([(int) $filters['class_id']]);
        }

        $studentIds = Student::query()->whereIn('class_id', $classIds)->pluck('id');

        if (! empty($filters['student_id'])) {
            if (! $studentIds->contains((int) $filters['student_id'])) {
                abort(403, 'Siswa ini bukan siswa kelas wali Anda.');
            }
            $studentIds = collect([(int) $filters['student_id']]);
        }

        $items = Internship::query()
            ->with(['student', 'assessments'])
            ->whereIn('student_id', $studentIds)
            ->orderBy('start_date')
            ->paginate(20)
            ->withQueryString();

        $students = Student::query()->whereIn('class_id', $classes->pluck('id'))->orderBy('name')->get();

        return view('wali.internships.index', [
            'filters' => $filters,
            'classes' => $classes,
            'students' => $students,
            'items' => $items,
        ]);
    }

    public function recap(Request $request)
    {
        $filters = $request->validate([
            'class_id' => 'nullable|integer',
            'student_id' => 'nullable|integer',
        ]);

        $teacher = $this->resolveTeacher($request);
        $classes = $this->myClasses($teacher->id);
        $classIds = $classes->pluck('id');

        if (! empty($filters['class_id'])) {
            $this->assertHomeroom($teacher->id, $filters['class_id']);
            $classIds = collect([(int) $filters['class_id']]);
        }

        $studentIds = Student::query()->whereIn('class_id', $classIds)->pluck('id');

        if (! empty($filters['student_id'])) {
            if (! $studentIds->contains((int) $filters['student_id'])) {
                abort(403, 'Siswa ini bukan siswa kelas wali Anda.');
            }
            $studentIds = collect([(int) $filters['student_id']]);
        }

        $placedIds = Internship::query()->whereIn('student_id', $studentIds)->distinct()->pluck('student_id');
        $recaps = $placedIds->map(fn ($id) => $this->internships->recapForStudent($id))->values()->all();
        $students = Student::query()->whereIn('id', $placedIds)->orderBy('name')->get()->keyBy('id');
        $allStudents = Student::query()->whereIn('class_id', $classes->pluck('id'))->orderBy('name')->get();

        return view('wali.internships.recap', [
            'filters' => $filters,
            'classes' => $classes,
            'recaps' => $recaps,
            'students' => $students,
            'allStudents' => $allStudents,
        ]);
    }

    public function competencyExams(Request $request)
    {
        $filters = $request->validate([
            'class_id' => 'nullable|integer',
            'student_id' => 'nullable|integer',
            'subject_id' => 'nullable|integer',
        ]);

        $teacher = $this->resolveTeacher($request);
        $classes = $this->myClasses($teacher->id);
        $classIds = $classes->pluck('id');

        if (! empty($filters['class_id'])) {
            $this->assertHomeroom($teacher->id, $filters['class_id']);
            $classIds = collect([(int) $filters['class_id']]);
        }

        $studentIds = Student::query()->whereIn('class_id', $classIds)->pluck('id');

        if (! empty($filters['student_id'])) {
            if (! $studentIds->contains((int) $filters['student_id'])) {
                abort(403, 'Siswa ini bukan siswa kelas wali Anda.');
            }
            $studentIds = collect([(int) $filters['student_id']]);
        }

        $query = CompetencyExam::query()->with(['subject', 'student'])
            ->whereIn('student_id', $studentIds)
            ->when(! empty($filters['subject_id']), fn ($q) => $q->where('subject_id', $filters['subject_id']));

        $schedules = $this->exams->schedules($query);
        $recaps = $studentIds->map(fn ($id) => $this->exams->recapForStudent($id))
            ->filter(fn ($r) => $r['total'] > 0)
            ->values()
            ->all();
        $students = Student::query()->whereIn('id', $studentIds)->orderBy('name')->get()->keyBy('id');
        $allStudents = Student::query()->whereIn('class_id', $classes->pluck('id'))->orderBy('name')->get();

        return view('wali.competency_exams.index', [
            'filters' => $filters,
            'classes' => $classes,
            'schedules' => $schedules,
            'recaps' => $recaps,
            'students' => $students,
            'allStudents' => $allStudents,
            'subjects' => \App\Models\Subject::orderBy('name')->get(),
        ]);
    }
}
