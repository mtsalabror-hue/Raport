<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\ActivityLog;
use App\Models\AssessmentScore;
use App\Models\HomeroomAssignment;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingAssignment;
use App\Services\AssessmentService;
use App\Services\AttendanceService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AttendanceController extends Controller
{
    public function __construct(
        private AttendanceService $attendance,
        private AssessmentService $scores,
    ) {
    }

    /**
     * Selesaikan guru yang sedang login (pola sama dengan Stage 7).
     */
    private function resolveTeacher(Request $request, ?array $scope = null): Teacher
    {
        $user = $request->user();

        if ($user && $user->teacher_id) {
            return Teacher::findOrFail($user->teacher_id);
        }

        if ($scope) {
            $assigned = TeachingAssignment::query()
                ->where('class_id', $scope['class_id'])
                ->where('academic_year_id', $scope['academic_year_id'])
                ->where('semester_id', $scope['semester_id'])
                ->first();

            if ($assigned) {
                return Teacher::findOrFail($assigned->teacher_id);
            }
        }

        if (Teacher::count() === 1) {
            return Teacher::first();
        }

        abort(403, 'Akun guru belum terhubung ke data guru. Minta admin menghubungkan akun ini di data pengguna.');
    }

    /**
     * Periksa apakah guru berhak mengelola presensi sebuah kelas.
     * Jika sudah ada penugasan mengajar ATAU penugasan wali kelas untuk
     * kelas/periode itu, guru harus termasuk di dalamnya. Jika belum ada
     * penugasan sama sekali, pemeriksaan dilewati (pola soft-check
     * assertAssignment Stage 7).
     */
    private function assertClassAccess(int $teacherId, int $classId, int $academicYearId, int $semesterId): void
    {
        $anyTeaching = TeachingAssignment::query()
            ->where('class_id', $classId)
            ->where('academic_year_id', $academicYearId)
            ->where('semester_id', $semesterId)
            ->exists();

        $anyHomeroom = HomeroomAssignment::query()
            ->where('class_id', $classId)
            ->where('academic_year_id', $academicYearId)
            ->where('semester_id', $semesterId)
            ->exists();

        if (! $anyTeaching && ! $anyHomeroom) {
            return;
        }

        $mineTeaching = TeachingAssignment::query()
            ->where('teacher_id', $teacherId)
            ->where('class_id', $classId)
            ->where('academic_year_id', $academicYearId)
            ->where('semester_id', $semesterId)
            ->exists();

        $mineHomeroom = HomeroomAssignment::query()
            ->where('teacher_id', $teacherId)
            ->where('class_id', $classId)
            ->where('academic_year_id', $academicYearId)
            ->where('semester_id', $semesterId)
            ->exists();

        if (! $mineTeaching && ! $mineHomeroom) {
            abort(403, 'Presensi kelas ini bukan penugasan Anda.');
        }
    }

    /**
     * Periksa apakah wali kelas berhak melihat sebuah kelas.
     * Wali hanya boleh kelas pada homeroom_assignments miliknya.
     */
    private function assertHomeroom(int $teacherId, int $classId, ?int $academicYearId = null, ?int $semesterId = null): void
    {
        $query = HomeroomAssignment::query()
            ->where('teacher_id', $teacherId)
            ->where('class_id', $classId);

        if ($academicYearId) {
            $query->where('academic_year_id', $academicYearId);
        }

        if ($semesterId) {
            $query->where('semester_id', $semesterId);
        }

        if (! $query->exists()) {
            abort(403, 'Kelas ini bukan kelas wali Anda.');
        }
    }

    private function logActivity(Request $request, string $action, string $modelType, int $modelId, array $metadata = []): void
    {
        ActivityLog::create([
            'user_id' => $request->user()?->id,
            'action' => $action,
            'model_type' => $modelType,
            'model_id' => $modelId,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'metadata' => $metadata,
        ]);
    }

    private function filterLists(): array
    {
        return [
            'classes' => SchoolClass::orderBy('name')->get(),
            'academicYears' => AcademicYear::orderBy('name')->get(),
            'semesters' => Semester::orderBy('name')->get(),
            'subjects' => Subject::orderBy('name')->get(),
        ];
    }

    /**
     * Form input presensi massal: pilih kelas + tanggal, tampilkan satu
     * baris per siswa dengan status H/S/I/A.
     */
    public function index(Request $request)
    {
        $filters = $request->validate([
            'class_id' => 'nullable|integer',
            'date' => 'nullable|date',
            'academic_year_id' => 'nullable|integer',
            'semester_id' => 'nullable|integer',
        ]);

        $students = collect();
        $existing = collect();

        if (! empty($filters['class_id']) && ! empty($filters['date'])) {
            $students = Student::query()
                ->where('class_id', $filters['class_id'])
                ->orderBy('name')
                ->get();

            $existing = StudentAttendance::query()
                ->where('class_id', $filters['class_id'])
                ->whereDate('date', $filters['date'])
                ->get()
                ->keyBy('student_id');
        }

        return view('teacher.attendance.index', array_merge($this->filterLists(), [
            'filters' => $filters,
            'students' => $students,
            'existing' => $existing,
        ]));
    }

    /**
     * Simpan presensi massal untuk satu kelas + tanggal dalam satu
     * transaksi. Duplikasi (siswa sudah punya catatan pada tanggal itu)
     * ditolak lewat validasi; pelanggaran unique di database ditangkap
     * sebagai pengaman terakhir.
     */
    public function storeBulk(Request $request)
    {
        $d = $request->validate([
            'class_id' => 'required|integer|exists:classes,id',
            'date' => 'required|date',
            'academic_year_id' => 'required|integer|exists:academic_years,id',
            'semester_id' => 'required|integer|exists:semesters,id',
            'records' => 'required|array|min:1',
            'records.*.student_id' => 'required|integer|exists:students,id|distinct',
            'records.*.status' => 'required|in:H,S,I,A',
            'records.*.note' => 'nullable|string|max:500',
        ]);

        $teacher = $this->resolveTeacher($request, $d);
        $this->assertClassAccess($teacher->id, $d['class_id'], $d['academic_year_id'], $d['semester_id']);

        // Semua siswa pada payload harus anggota kelas yang dipilih.
        $studentIds = collect($d['records'])->pluck('student_id')->unique()->values();
        $inClass = Student::query()
            ->whereIn('id', $studentIds)
            ->where('class_id', $d['class_id'])
            ->count();

        if ($inClass !== $studentIds->count()) {
            return back()->withErrors(['records' => 'Ada siswa yang bukan anggota kelas yang dipilih.']);
        }

        // Cegah duplikasi sebelum menyimpan apa pun.
        $duplicates = StudentAttendance::query()
            ->whereIn('student_id', $studentIds)
            ->whereDate('date', $d['date'])
            ->with('student')
            ->get();

        if ($duplicates->isNotEmpty()) {
            $names = $duplicates->map(fn ($a) => $a->student?->name ?? ('#'.$a->student_id))->implode(', ');

            return back()->withErrors([
                'records' => "Presensi tanggal {$d['date']} sudah ada untuk: {$names}. Gunakan koreksi untuk mengubahnya.",
            ]);
        }

        $saved = 0;

        try {
            DB::transaction(function () use ($d, $request, &$saved) {
                foreach ($d['records'] as $row) {
                    $attendance = StudentAttendance::create([
                        'student_id' => $row['student_id'],
                        'class_id' => $d['class_id'],
                        'academic_year_id' => $d['academic_year_id'],
                        'semester_id' => $d['semester_id'],
                        'date' => $d['date'],
                        'status' => $row['status'],
                        'note' => $row['note'] ?? null,
                    ]);

                    $this->logActivity($request, 'attendance.created', StudentAttendance::class, $attendance->id, [
                        'student_id' => $row['student_id'],
                        'class_id' => $d['class_id'],
                        'date' => $d['date'],
                        'status' => $row['status'],
                    ]);

                    $saved++;
                }
            });
        } catch (QueryException $e) {
            // Pengaman terakhir: pelanggaran unique (student_id, date).
            if ((int) $e->getCode() === 23000 || str_contains($e->getMessage(), 'student_attendances')) {
                return back()->withErrors([
                    'records' => "Presensi tanggal {$d['date']} sudah tercatat untuk sebagian siswa (duplikasi ditolak database). Tidak ada data yang disimpan.",
                ]);
            }

            throw $e;
        }

        return back()->with('success', "{$saved} catatan presensi disimpan untuk tanggal {$d['date']}.");
    }

    /**
     * Koreksi satu catatan presensi. Nilai lama dan baru dicatat ke
     * ActivityLog (attendance.corrected) sebagai audit trail.
     */
    public function correct(Request $request, StudentAttendance $attendance)
    {
        $d = $request->validate([
            'status' => 'required|in:H,S,I,A',
            'note' => 'nullable|string|max:500',
        ]);

        $teacher = $this->resolveTeacher($request, [
            'class_id' => $attendance->class_id,
            'academic_year_id' => $attendance->academic_year_id,
            'semester_id' => $attendance->semester_id,
        ]);
        $this->assertClassAccess($teacher->id, $attendance->class_id, $attendance->academic_year_id, $attendance->semester_id);

        $oldStatus = $attendance->status;
        $oldNote = $attendance->note;

        DB::transaction(function () use ($attendance, $d, $request, $oldStatus, $oldNote) {
            $attendance->update(['status' => $d['status'], 'note' => $d['note'] ?? null]);

            $this->logActivity($request, 'attendance.corrected', StudentAttendance::class, $attendance->id, [
                'student_id' => $attendance->student_id,
                'class_id' => $attendance->class_id,
                'date' => $attendance->date->toDateString(),
                'old_status' => $oldStatus,
                'new_status' => $d['status'],
                'old_note' => $oldNote,
                'new_note' => $d['note'] ?? null,
            ]);
        });

        return back()->with('success', "Presensi {$attendance->student?->name} tanggal {$attendance->date->toDateString()} dikoreksi dari {$oldStatus} menjadi {$d['status']}.");
    }

    /**
     * Riwayat presensi dengan filter kelas, tanggal/periode, AY, semester.
     */
    public function history(Request $request)
    {
        $filters = $request->validate([
            'class_id' => 'nullable|integer',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
            'academic_year_id' => 'nullable|integer',
            'semester_id' => 'nullable|integer',
            'status' => 'nullable|in:H,S,I,A',
        ]);

        $records = StudentAttendance::query()
            ->with(['student', 'schoolClass'])
            ->when(! empty($filters['class_id']), fn ($q) => $q->where('class_id', $filters['class_id']))
            ->when(! empty($filters['date_from']), fn ($q) => $q->whereDate('date', '>=', $filters['date_from']))
            ->when(! empty($filters['date_to']), fn ($q) => $q->whereDate('date', '<=', $filters['date_to']))
            ->when(! empty($filters['academic_year_id']), fn ($q) => $q->where('academic_year_id', $filters['academic_year_id']))
            ->when(! empty($filters['semester_id']), fn ($q) => $q->where('semester_id', $filters['semester_id']))
            ->when(! empty($filters['status']), fn ($q) => $q->where('status', $filters['status']))
            ->orderByDesc('date')
            ->orderBy('student_id')
            ->paginate(20)
            ->withQueryString();

        return view('teacher.attendance.history', array_merge($this->filterLists(), [
            'filters' => $filters,
            'records' => $records,
        ]));
    }

    /**
     * Rekap presensi per siswa + total per kelas untuk satu periode
     * eksplisit (kelas + AY + semester).
     */
    public function recap(Request $request)
    {
        $filters = $request->validate([
            'class_id' => 'nullable|integer',
            'academic_year_id' => 'nullable|integer',
            'semester_id' => 'nullable|integer',
        ]);

        $rows = [];
        $classTotal = null;

        if (! empty($filters['class_id']) && ! empty($filters['academic_year_id']) && ! empty($filters['semester_id'])) {
            $teacher = $this->resolveTeacher($request, $filters);
            $this->assertClassAccess($teacher->id, $filters['class_id'], $filters['academic_year_id'], $filters['semester_id']);

            $rows = $this->attendance->recapPerStudent($filters['class_id'], $filters['academic_year_id'], $filters['semester_id']);
            $classTotal = $this->attendance->recapPerClass($filters['class_id'], $filters['academic_year_id'], $filters['semester_id']);
        }

        return view('teacher.attendance.recap', array_merge($this->filterLists(), [
            'filters' => $filters,
            'rows' => $rows,
            'classTotal' => $classTotal,
            'readOnly' => false,
        ]));
    }

    /**
     * Rekap akademik: nilai akhir per siswa per mapel dalam satu kelas +
     * periode, dengan status ketuntasan KKTP (via AssessmentService).
     * Filter AY/semester eksplisit; data lintas semester tidak dicampur.
     */
    public function academicRecap(Request $request)
    {
        $filters = $request->validate([
            'class_id' => 'nullable|integer',
            'subject_id' => 'nullable|integer',
            'academic_year_id' => 'nullable|integer',
            'semester_id' => 'nullable|integer',
        ]);

        $students = collect();
        $finals = [];
        $kktp = null;
        $stats = null;

        if (! empty($filters['class_id']) && ! empty($filters['subject_id'])
            && ! empty($filters['academic_year_id']) && ! empty($filters['semester_id'])) {
            $teacher = $this->resolveTeacher($request, $filters);
            $this->assertClassAccess($teacher->id, $filters['class_id'], $filters['academic_year_id'], $filters['semester_id']);

            try {
                $kktp = $this->scores->kktpFor($filters['subject_id']);
                $recap = $this->scores->recap($filters['class_id'], $filters['subject_id'], $filters['academic_year_id'], $filters['semester_id']);
                $finals = collect($recap)->keyBy('student_id');

                $students = Student::query()
                    ->where('class_id', $filters['class_id'])
                    ->orderBy('name')
                    ->get();

                $passed = $finals->where('passed', true)->count();
                $failed = $finals->where('passed', false)->count();
                $unscored = $students->count() - $finals->count();

                $stats = [
                    'total_siswa' => $students->count(),
                    'tuntas' => $passed,
                    'belum_tuntas' => $failed,
                    'belum_dinilai' => max(0, $unscored),
                ];
            } catch (ValidationException $e) {
                return back()->withErrors($e->errors());
            }
        }

        return view('teacher.attendance.academic-recap', array_merge($this->filterLists(), [
            'filters' => $filters,
            'students' => $students,
            'finals' => $finals,
            'kktp' => $kktp,
            'stats' => $stats,
            'readOnly' => false,
        ]));
    }

    /**
     * Rekap presensi hanya-baca untuk wali kelas: hanya kelas pada
     * homeroom_assignments miliknya.
     */
    public function waliRecap(Request $request)
    {
        $filters = $request->validate([
            'class_id' => 'nullable|integer',
            'academic_year_id' => 'nullable|integer',
            'semester_id' => 'nullable|integer',
        ]);

        $teacher = $this->resolveTeacher($request);

        $myClassIds = HomeroomAssignment::query()
            ->where('teacher_id', $teacher->id)
            ->when(! empty($filters['academic_year_id']), fn ($q) => $q->where('academic_year_id', $filters['academic_year_id']))
            ->when(! empty($filters['semester_id']), fn ($q) => $q->where('semester_id', $filters['semester_id']))
            ->pluck('class_id')
            ->unique()
            ->values();

        $classes = SchoolClass::query()->whereIn('id', $myClassIds)->orderBy('name')->get();

        $rows = [];
        $classTotal = null;

        if (! empty($filters['class_id'])) {
            $this->assertHomeroom($teacher->id, $filters['class_id'], $filters['academic_year_id'] ?? null, $filters['semester_id'] ?? null);

            if (! empty($filters['academic_year_id']) && ! empty($filters['semester_id'])) {
                $rows = $this->attendance->recapPerStudent($filters['class_id'], $filters['academic_year_id'], $filters['semester_id']);
                $classTotal = $this->attendance->recapPerClass($filters['class_id'], $filters['academic_year_id'], $filters['semester_id']);
            }
        }

        return view('teacher.attendance.recap', array_merge($this->filterLists(), [
            'classes' => $classes,
            'filters' => $filters,
            'rows' => $rows,
            'classTotal' => $classTotal,
            'readOnly' => true,
        ]));
    }

    /**
     * Rekap akademik hanya-baca untuk wali kelas: hanya nilai yang sudah
     * terkirim atau terkunci, dan hanya kelas walinya.
     */
    public function waliAcademicRecap(Request $request)
    {
        $filters = $request->validate([
            'class_id' => 'nullable|integer',
            'subject_id' => 'nullable|integer',
            'academic_year_id' => 'nullable|integer',
            'semester_id' => 'nullable|integer',
        ]);

        $teacher = $this->resolveTeacher($request);

        $myClassIds = HomeroomAssignment::query()
            ->where('teacher_id', $teacher->id)
            ->pluck('class_id')
            ->unique()
            ->values();

        $classes = SchoolClass::query()->whereIn('id', $myClassIds)->orderBy('name')->get();

        $students = collect();
        $finals = [];
        $kktp = null;
        $stats = null;

        if (! empty($filters['class_id']) && ! empty($filters['subject_id'])
            && ! empty($filters['academic_year_id']) && ! empty($filters['semester_id'])) {
            $this->assertHomeroom($teacher->id, $filters['class_id'], $filters['academic_year_id'], $filters['semester_id']);

            try {
                $kktp = $this->scores->kktpFor($filters['subject_id']);

                // Wali hanya melihat nilai yang sudah terkirim/dikunci.
                $submittedStudentIds = AssessmentScore::query()
                    ->where('class_id', $filters['class_id'])
                    ->where('subject_id', $filters['subject_id'])
                    ->where('academic_year_id', $filters['academic_year_id'])
                    ->where('semester_id', $filters['semester_id'])
                    ->whereIn('status', ['terkirim', 'dikunci'])
                    ->distinct()
                    ->pluck('student_id');

                $students = Student::query()
                    ->where('class_id', $filters['class_id'])
                    ->whereIn('id', $submittedStudentIds)
                    ->orderBy('name')
                    ->get();

                foreach ($students as $student) {
                    $final = $this->scores->finalScore(
                        $student->id,
                        $filters['subject_id'],
                        $filters['academic_year_id'],
                        $filters['semester_id']
                    );

                    $finals[$student->id] = [
                        'student_id' => $student->id,
                        'final_score' => $final,
                        'kktp' => $kktp,
                        'passed' => $final === null ? null : $final >= $kktp,
                    ];
                }

                $stats = [
                    'total_siswa' => $students->count(),
                    'tuntas' => collect($finals)->where('passed', true)->count(),
                    'belum_tuntas' => collect($finals)->where('passed', false)->count(),
                    'belum_dinilai' => collect($finals)->whereNull('final_score')->count(),
                ];
            } catch (ValidationException $e) {
                return back()->withErrors($e->errors());
            }
        }

        return view('teacher.attendance.academic-recap', array_merge($this->filterLists(), [
            'classes' => $classes,
            'filters' => $filters,
            'students' => $students,
            'finals' => $finals,
            'kktp' => $kktp,
            'stats' => $stats,
            'readOnly' => true,
        ]));
    }
}
