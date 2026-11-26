<?php

namespace App\Http\Controllers\Wali;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\ActivityLog;
use App\Models\AssessmentScore;
use App\Models\Extracurricular;
use App\Models\HomeroomAssignment;
use App\Models\ReportCard;
use App\Models\ReportCardNote;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentAchievement;
use App\Models\StudentExtracurricular;
use App\Models\Teacher;
use App\Models\TeacherDescription;
use App\Services\AssessmentService;
use App\Services\AttendanceService;
use App\Services\ReportCardService;
use App\Services\ReportCompletenessService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class HomeroomController extends Controller
{
    public function __construct(
        private ReportCompletenessService $completeness,
        private AssessmentService $scores,
        private AttendanceService $attendance,
        private ReportCardService $reportCards,
    ) {
    }

    /**
     * Selesaikan guru (wali kelas) yang sedang login.
     * Urutan: akun terhubung ke data guru -> satu-satunya guru di
     * sistem. Jika tidak ada yang cocok, tolak (pola Stage 7/8).
     */
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

    /**
     * Periksa apakah wali kelas berhak atas sebuah kelas pada periode
     * yang diminta (homeroom_assignments miliknya). Tanpa periode,
     * cukup ada penugasan wali untuk kelas itu pada periode mana pun.
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

    /**
     * Periksa apakah seorang siswa berada di kelas wali yang login
     * pada periode yang diminta.
     */
    private function assertStudentAccess(int $teacherId, Student $student, int $academicYearId, int $semesterId): void
    {
        if (! $student->class_id) {
            abort(403, 'Siswa ini belum masuk ke kelas mana pun.');
        }

        $this->assertHomeroom($teacherId, $student->class_id, $academicYearId, $semesterId);
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
            'academicYears' => AcademicYear::orderBy('name')->get(),
            'semesters' => Semester::orderBy('name')->get(),
        ];
    }

    /**
     * Kelas-kelas wali yang login (dibatasi periode bila diminta).
     */
    private function myClasses(int $teacherId, ?int $academicYearId = null, ?int $semesterId = null)
    {
        $classIds = HomeroomAssignment::query()
            ->where('teacher_id', $teacherId)
            ->when($academicYearId, fn ($q) => $q->where('academic_year_id', $academicYearId))
            ->when($semesterId, fn ($q) => $q->where('semester_id', $semesterId))
            ->pluck('class_id')
            ->unique()
            ->values();

        return SchoolClass::query()->whereIn('id', $classIds)->orderBy('name')->get();
    }

    /**
     * Periode default: dari filter, atau penugasan wali pertama,
     * atau tahun/semester aktif.
     */
    private function defaultPeriod(Request $request, int $teacherId): array
    {
        $ay = $request->integer('academic_year_id') ?: null;
        $sem = $request->integer('semester_id') ?: null;

        if ($ay && $sem) {
            return [$ay, $sem];
        }

        $first = HomeroomAssignment::query()
            ->where('teacher_id', $teacherId)
            ->orderBy('academic_year_id')
            ->orderBy('semester_id')
            ->first();

        if ($first) {
            return [$first->academic_year_id, $first->semester_id];
        }

        $activeYear = AcademicYear::where('is_active', true)->first();
        $activeSem = $activeYear
            ? Semester::where('academic_year_id', $activeYear->id)->where('is_active', true)->first()
            : null;

        return [$activeYear?->id, $activeSem?->id];
    }

    /**
     * Cari atau buat rapor draft untuk siswa + periode (dalam
     * transaksi pemanggil bila ada). Dipakai saat wali menulis
     * catatan: catatan menempel pada rapor, jadi rapor draft dibuat
     * dulu bila belum ada.
     */
    private function findOrCreateDraft(Student $student, int $academicYearId, int $semesterId): ReportCard
    {
        return ReportCard::firstOrCreate(
            [
                'student_id' => $student->id,
                'academic_year_id' => $academicYearId,
                'semester_id' => $semesterId,
            ],
            [
                'class_id' => $student->class_id,
                'status' => 'draft',
            ]
        );
    }

    // -----------------------------------------------------------------
    // Dashboard kelas
    // -----------------------------------------------------------------

    public function dashboard(Request $request)
    {
        $filters = $request->validate([
            'class_id' => 'nullable|integer',
            'academic_year_id' => 'nullable|integer',
            'semester_id' => 'nullable|integer',
        ]);

        $teacher = $this->resolveTeacher($request);
        $classes = $this->myClasses($teacher->id);

        [$defaultAy, $defaultSem] = $this->defaultPeriod($request, $teacher->id);
        $filters['academic_year_id'] ??= $defaultAy;
        $filters['semester_id'] ??= $defaultSem;

        $students = collect();
        $checks = [];
        $stats = null;

        if (! empty($filters['class_id'])) {
            $this->assertHomeroom($teacher->id, $filters['class_id'], $filters['academic_year_id'] ?? null, $filters['semester_id'] ?? null);

            $students = Student::query()
                ->where('class_id', $filters['class_id'])
                ->orderBy('name')
                ->get();

            if (! empty($filters['academic_year_id']) && ! empty($filters['semester_id'])) {
                $checks = $this->completeness->checkClass(
                    $filters['class_id'],
                    $filters['academic_year_id'],
                    $filters['semester_id']
                );

                $completeCount = collect($checks)->where('complete', true)->count();
                $submittedCount = ReportCard::query()
                    ->where('class_id', $filters['class_id'])
                    ->where('academic_year_id', $filters['academic_year_id'])
                    ->where('semester_id', $filters['semester_id'])
                    ->where('status', 'diajukan')
                    ->count();

                // Nilai belum dikirim: pasangan (siswa, mapel) yang
                // diajarkan di kelas ini tanpa nilai terkirim/dikunci.
                $pendingScores = 0;
                foreach ($checks as $check) {
                    foreach ($check['missing'] as $item) {
                        if ($item['code'] === 'nilai_belum_terkirim') {
                            $pendingScores++;
                        }
                    }
                }

                $stats = [
                    'total_siswa' => $students->count(),
                    'lengkap' => $completeCount,
                    'belum_lengkap' => $students->count() - $completeCount,
                    'diajukan' => $submittedCount,
                    'nilai_belum_terkirim' => $pendingScores,
                ];
            }
        }

        return view('wali.dashboard', array_merge($this->filterLists(), [
            'filters' => $filters,
            'classes' => $classes,
            'students' => $students,
            'checks' => $checks,
            'stats' => $stats,
            'teacher' => $teacher,
        ]));
    }

    // -----------------------------------------------------------------
    // Data siswa kelas
    // -----------------------------------------------------------------

    public function students(Request $request)
    {
        $filters = $request->validate([
            'class_id' => 'nullable|integer',
            'academic_year_id' => 'nullable|integer',
            'semester_id' => 'nullable|integer',
        ]);

        $teacher = $this->resolveTeacher($request);
        $classes = $this->myClasses($teacher->id);

        [$defaultAy, $defaultSem] = $this->defaultPeriod($request, $teacher->id);
        $filters['academic_year_id'] ??= $defaultAy;
        $filters['semester_id'] ??= $defaultSem;

        $students = collect();
        $reportCards = collect();

        if (! empty($filters['class_id'])) {
            $this->assertHomeroom($teacher->id, $filters['class_id'], $filters['academic_year_id'] ?? null, $filters['semester_id'] ?? null);

            $students = Student::query()
                ->where('class_id', $filters['class_id'])
                ->orderBy('name')
                ->get();

            if (! empty($filters['academic_year_id']) && ! empty($filters['semester_id'])) {
                $reportCards = ReportCard::query()
                    ->where('class_id', $filters['class_id'])
                    ->where('academic_year_id', $filters['academic_year_id'])
                    ->where('semester_id', $filters['semester_id'])
                    ->get()
                    ->keyBy('student_id');
            }
        }

        return view('wali.students', array_merge($this->filterLists(), [
            'filters' => $filters,
            'classes' => $classes,
            'students' => $students,
            'reportCards' => $reportCards,
        ]));
    }

    // -----------------------------------------------------------------
    // Catatan wali kelas (report_card_notes, note_type wali_kelas)
    // -----------------------------------------------------------------

    public function notes(Request $request)
    {
        $filters = $request->validate([
            'student_id' => 'nullable|integer',
            'academic_year_id' => 'nullable|integer',
            'semester_id' => 'nullable|integer',
        ]);

        $teacher = $this->resolveTeacher($request);
        $classes = $this->myClasses($teacher->id);

        [$defaultAy, $defaultSem] = $this->defaultPeriod($request, $teacher->id);
        $filters['academic_year_id'] ??= $defaultAy;
        $filters['semester_id'] ??= $defaultSem;

        $student = null;
        $reportCard = null;
        $notes = collect();

        if (! empty($filters['student_id'])) {
            $student = Student::findOrFail($filters['student_id']);
            $this->assertStudentAccess(
                $teacher->id,
                $student,
                $filters['academic_year_id'],
                $filters['semester_id']
            );

            $reportCard = ReportCard::query()
                ->where('student_id', $student->id)
                ->where('academic_year_id', $filters['academic_year_id'])
                ->where('semester_id', $filters['semester_id'])
                ->first();

            if ($reportCard) {
                $notes = $reportCard->notes()->where('note_type', 'wali_kelas')->orderByDesc('updated_at')->get();
            }
        }

        $students = Student::query()
            ->whereIn('class_id', $classes->pluck('id'))
            ->orderBy('name')
            ->get();

        return view('wali.notes', array_merge($this->filterLists(), [
            'filters' => $filters,
            'students' => $students,
            'student' => $student,
            'reportCard' => $reportCard,
            'notes' => $notes,
        ]));
    }

    public function storeNote(Request $request)
    {
        $d = $request->validate([
            'student_id' => 'required|integer|exists:students,id',
            'academic_year_id' => 'required|integer|exists:academic_years,id',
            'semester_id' => 'required|integer|exists:semesters,id',
            'content' => 'required|string|max:5000',
        ]);

        $teacher = $this->resolveTeacher($request);
        $student = Student::findOrFail($d['student_id']);
        $this->assertStudentAccess($teacher->id, $student, $d['academic_year_id'], $d['semester_id']);

        // Stage 10: rapor yang sudah diterbitkan/dikunci tidak boleh
        // ditambahi catatan — percobaan dicatat lalu ditolak 403.
        $existing = ReportCard::query()
            ->where('student_id', $student->id)
            ->where('academic_year_id', $d['academic_year_id'])
            ->where('semester_id', $d['semester_id'])
            ->first();

        if ($existing) {
            $this->reportCards->assertEditable($existing, $request->user(), 'catatan wali kelas');
        }

        $note = null;

        DB::transaction(function () use ($d, $student, $request, &$note) {
            $reportCard = $this->findOrCreateDraft($student, $d['academic_year_id'], $d['semester_id']);

            $note = ReportCardNote::create([
                'report_card_id' => $reportCard->id,
                'note_type' => 'wali_kelas',
                'content' => $d['content'],
            ]);

            $this->logActivity($request, 'report_card_note.created', ReportCardNote::class, $note->id, [
                'student_id' => $student->id,
                'report_card_id' => $reportCard->id,
            ]);
        });

        return back()->with('success', 'Catatan wali kelas disimpan.');
    }

    public function updateNote(Request $request, ReportCardNote $note)
    {
        $d = $request->validate([
            'content' => 'required|string|max:5000',
        ]);

        $teacher = $this->resolveTeacher($request);
        $reportCard = $note->reportCard;
        $this->assertHomeroom($teacher->id, $reportCard->class_id, $reportCard->academic_year_id, $reportCard->semester_id);

        if ($note->note_type !== 'wali_kelas') {
            abort(403, 'Hanya catatan wali kelas yang dapat diubah wali kelas.');
        }

        // Stage 10: rapor yang sudah diterbitkan/dikunci tidak boleh dikoreksi.
        $this->reportCards->assertEditable($reportCard, $request->user(), 'catatan wali kelas');

        $old = $note->content;

        DB::transaction(function () use ($note, $d, $request, $old) {
            $note->update(['content' => $d['content']]);

            $this->logActivity($request, 'report_card_note.updated', ReportCardNote::class, $note->id, [
                'student_id' => $note->reportCard->student_id,
                'old_content' => $old,
                'new_content' => $d['content'],
            ]);
        });

        return back()->with('success', 'Catatan wali kelas diperbarui.');
    }

    public function destroyNote(Request $request, ReportCardNote $note)
    {
        $teacher = $this->resolveTeacher($request);
        $reportCard = $note->reportCard;
        $this->assertHomeroom($teacher->id, $reportCard->class_id, $reportCard->academic_year_id, $reportCard->semester_id);

        if ($note->note_type !== 'wali_kelas') {
            abort(403, 'Hanya catatan wali kelas yang dapat dihapus wali kelas.');
        }

        // Stage 10: rapor yang sudah diterbitkan/dikunci tidak boleh dikoreksi.
        $this->reportCards->assertEditable($reportCard, $request->user(), 'catatan wali kelas');

        DB::transaction(function () use ($note, $request) {
            $note->delete();

            $this->logActivity($request, 'report_card_note.deleted', ReportCardNote::class, $note->id, [
                'student_id' => $note->reportCard->student_id,
                'report_card_id' => $note->report_card_id,
            ]);
        });

        return back()->with('success', 'Catatan wali kelas dihapus.');
    }

    // -----------------------------------------------------------------
    // Prestasi siswa
    // -----------------------------------------------------------------

    public function achievements(Request $request)
    {
        $filters = $request->validate([
            'student_id' => 'nullable|integer',
            'academic_year_id' => 'nullable|integer',
            'semester_id' => 'nullable|integer',
        ]);

        $teacher = $this->resolveTeacher($request);
        $classes = $this->myClasses($teacher->id);

        [$defaultAy, $defaultSem] = $this->defaultPeriod($request, $teacher->id);
        $filters['academic_year_id'] ??= $defaultAy;
        $filters['semester_id'] ??= $defaultSem;

        $items = StudentAchievement::query()
            ->with(['student'])
            ->whereHas('student', fn ($q) => $q->whereIn('class_id', $classes->pluck('id')))
            ->when(! empty($filters['student_id']), fn ($q) => $q->where('student_id', $filters['student_id']))
            ->when(! empty($filters['academic_year_id']), fn ($q) => $q->where('academic_year_id', $filters['academic_year_id']))
            ->when(! empty($filters['semester_id']), fn ($q) => $q->where('semester_id', $filters['semester_id']))
            ->orderByDesc('updated_at')
            ->paginate(20)
            ->withQueryString();

        $students = Student::query()
            ->whereIn('class_id', $classes->pluck('id'))
            ->orderBy('name')
            ->get();

        return view('wali.achievements', array_merge($this->filterLists(), [
            'filters' => $filters,
            'students' => $students,
            'items' => $items,
        ]));
    }

    public function storeAchievement(Request $request)
    {
        $d = $request->validate([
            'student_id' => 'required|integer|exists:students,id',
            'academic_year_id' => 'required|integer|exists:academic_years,id',
            'semester_id' => 'required|integer|exists:semesters,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'level' => 'nullable|string|max:100',
        ]);

        $teacher = $this->resolveTeacher($request);
        $student = Student::findOrFail($d['student_id']);
        $this->assertStudentAccess($teacher->id, $student, $d['academic_year_id'], $d['semester_id']);

        $item = null;

        DB::transaction(function () use ($d, $request, &$item) {
            $item = StudentAchievement::create($d);

            $this->logActivity($request, 'student_achievement.created', StudentAchievement::class, $item->id, [
                'student_id' => $d['student_id'],
                'title' => $d['title'],
            ]);
        });

        return back()->with('success', 'Prestasi siswa disimpan.');
    }

    public function updateAchievement(Request $request, StudentAchievement $achievement)
    {
        $d = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'level' => 'nullable|string|max:100',
        ]);

        $teacher = $this->resolveTeacher($request);
        $this->assertStudentAccess($teacher->id, $achievement->student, $achievement->academic_year_id, $achievement->semester_id);

        DB::transaction(function () use ($achievement, $d, $request) {
            $achievement->update($d);

            $this->logActivity($request, 'student_achievement.updated', StudentAchievement::class, $achievement->id, [
                'student_id' => $achievement->student_id,
                'title' => $d['title'],
            ]);
        });

        return back()->with('success', 'Prestasi siswa diperbarui.');
    }

    public function destroyAchievement(Request $request, StudentAchievement $achievement)
    {
        $teacher = $this->resolveTeacher($request);
        $this->assertStudentAccess($teacher->id, $achievement->student, $achievement->academic_year_id, $achievement->semester_id);

        DB::transaction(function () use ($achievement, $request) {
            $achievement->delete();

            $this->logActivity($request, 'student_achievement.deleted', StudentAchievement::class, $achievement->id, [
                'student_id' => $achievement->student_id,
            ]);
        });

        return back()->with('success', 'Prestasi siswa dihapus.');
    }

    // -----------------------------------------------------------------
    // Ekstrakurikuler siswa + data master
    // -----------------------------------------------------------------

    public function extracurriculars(Request $request)
    {
        $filters = $request->validate([
            'student_id' => 'nullable|integer',
            'academic_year_id' => 'nullable|integer',
            'semester_id' => 'nullable|integer',
        ]);

        $teacher = $this->resolveTeacher($request);
        $classes = $this->myClasses($teacher->id);

        [$defaultAy, $defaultSem] = $this->defaultPeriod($request, $teacher->id);
        $filters['academic_year_id'] ??= $defaultAy;
        $filters['semester_id'] ??= $defaultSem;

        $records = StudentExtracurricular::query()
            ->with(['student', 'extracurricular'])
            ->whereHas('student', fn ($q) => $q->whereIn('class_id', $classes->pluck('id')))
            ->when(! empty($filters['student_id']), fn ($q) => $q->where('student_id', $filters['student_id']))
            ->when(! empty($filters['academic_year_id']), fn ($q) => $q->where('academic_year_id', $filters['academic_year_id']))
            ->when(! empty($filters['semester_id']), fn ($q) => $q->where('semester_id', $filters['semester_id']))
            ->orderByDesc('updated_at')
            ->paginate(20)
            ->withQueryString();

        $students = Student::query()
            ->whereIn('class_id', $classes->pluck('id'))
            ->orderBy('name')
            ->get();

        return view('wali.extracurriculars', array_merge($this->filterLists(), [
            'filters' => $filters,
            'students' => $students,
            'records' => $records,
            'masters' => Extracurricular::orderBy('name')->get(),
        ]));
    }

    public function storeExtracurricular(Request $request)
    {
        $d = $request->validate([
            'student_id' => 'required|integer|exists:students,id',
            'extracurricular_id' => 'required|integer|exists:extracurriculars,id',
            'academic_year_id' => 'required|integer|exists:academic_years,id',
            'semester_id' => 'required|integer|exists:semesters,id',
            'score' => 'nullable|numeric|min:0|max:100',
            'description' => 'nullable|string|max:2000',
        ]);

        $teacher = $this->resolveTeacher($request);
        $student = Student::findOrFail($d['student_id']);
        $this->assertStudentAccess($teacher->id, $student, $d['academic_year_id'], $d['semester_id']);

        // Satu siswa hanya boleh tercatat sekali per ekskul per periode.
        $exists = StudentExtracurricular::query()
            ->where('student_id', $d['student_id'])
            ->where('extracurricular_id', $d['extracurricular_id'])
            ->where('academic_year_id', $d['academic_year_id'])
            ->where('semester_id', $d['semester_id'])
            ->exists();

        if ($exists) {
            return back()->withErrors([
                'extracurricular_id' => 'Siswa ini sudah tercatat pada ekstrakurikuler tersebut untuk periode ini.',
            ]);
        }

        $record = null;

        try {
            DB::transaction(function () use ($d, $request, &$record) {
                $record = StudentExtracurricular::create($d);

                $this->logActivity($request, 'student_extracurricular.created', StudentExtracurricular::class, $record->id, [
                    'student_id' => $d['student_id'],
                    'extracurricular_id' => $d['extracurricular_id'],
                ]);
            });
        } catch (QueryException $e) {
            // Pengaman terakhir: pelanggaran unique
            // (student, ekskul, AY, semester) di database.
            if ((int) $e->getCode() === 23000 || str_contains($e->getMessage(), 'student_extracurriculars')) {
                return back()->withErrors([
                    'extracurricular_id' => 'Siswa ini sudah tercatat pada ekstrakurikuler tersebut untuk periode ini.',
                ]);
            }

            throw $e;
        }

        return back()->with('success', 'Ekstrakurikuler siswa disimpan.');
    }

    public function updateExtracurricular(Request $request, StudentExtracurricular $record)
    {
        $d = $request->validate([
            'score' => 'nullable|numeric|min:0|max:100',
            'description' => 'nullable|string|max:2000',
        ]);

        $teacher = $this->resolveTeacher($request);
        $this->assertStudentAccess($teacher->id, $record->student, $record->academic_year_id, $record->semester_id);

        DB::transaction(function () use ($record, $d, $request) {
            $record->update($d);

            $this->logActivity($request, 'student_extracurricular.updated', StudentExtracurricular::class, $record->id, [
                'student_id' => $record->student_id,
                'extracurricular_id' => $record->extracurricular_id,
            ]);
        });

        return back()->with('success', 'Ekstrakurikuler siswa diperbarui.');
    }

    public function destroyExtracurricular(Request $request, StudentExtracurricular $record)
    {
        $teacher = $this->resolveTeacher($request);
        $this->assertStudentAccess($teacher->id, $record->student, $record->academic_year_id, $record->semester_id);

        DB::transaction(function () use ($record, $request) {
            $record->delete();

            $this->logActivity($request, 'student_extracurricular.deleted', StudentExtracurricular::class, $record->id, [
                'student_id' => $record->student_id,
                'extracurricular_id' => $record->extracurricular_id,
            ]);
        });

        return back()->with('success', 'Ekstrakurikuler siswa dihapus.');
    }

    public function storeExtracurricularMaster(Request $request)
    {
        $d = $request->validate([
            'name' => 'required|string|max:255|unique:extracurriculars,name',
            'description' => 'nullable|string|max:2000',
        ]);

        $this->resolveTeacher($request);

        $master = Extracurricular::create($d);
        $this->logActivity($request, 'extracurricular.created', Extracurricular::class, $master->id, [
            'name' => $d['name'],
        ]);

        return back()->with('success', "Ekstrakurikuler {$d['name']} ditambahkan.");
    }

    public function updateExtracurricularMaster(Request $request, Extracurricular $extracurricular)
    {
        $d = $request->validate([
            'name' => 'required|string|max:255|unique:extracurriculars,name,'.$extracurricular->id,
            'description' => 'nullable|string|max:2000',
        ]);

        $this->resolveTeacher($request);

        $extracurricular->update($d);
        $this->logActivity($request, 'extracurricular.updated', Extracurricular::class, $extracurricular->id, [
            'name' => $d['name'],
        ]);

        return back()->with('success', 'Data ekstrakurikuler diperbarui.');
    }

    public function destroyExtracurricularMaster(Request $request, Extracurricular $extracurricular)
    {
        $this->resolveTeacher($request);

        if ($extracurricular->studentRecords()->exists()) {
            return back()->withErrors([
                'extracurricular' => "Ekstrakurikuler {$extracurricular->name} masih dipakai catatan siswa dan tidak dapat dihapus.",
            ]);
        }

        $extracurricular->delete();
        $this->logActivity($request, 'extracurricular.deleted', Extracurricular::class, $extracurricular->id, [
            'name' => $extracurricular->name,
        ]);

        return back()->with('success', 'Data ekstrakurikuler dihapus.');
    }

    // -----------------------------------------------------------------
    // Monitoring kelengkapan rapor
    // -----------------------------------------------------------------

    public function completeness(Request $request)
    {
        $filters = $request->validate([
            'class_id' => 'nullable|integer',
            'academic_year_id' => 'nullable|integer',
            'semester_id' => 'nullable|integer',
        ]);

        $teacher = $this->resolveTeacher($request);
        $classes = $this->myClasses($teacher->id);

        [$defaultAy, $defaultSem] = $this->defaultPeriod($request, $teacher->id);
        $filters['academic_year_id'] ??= $defaultAy;
        $filters['semester_id'] ??= $defaultSem;

        $checks = [];
        $stats = null;

        if (! empty($filters['class_id']) && ! empty($filters['academic_year_id']) && ! empty($filters['semester_id'])) {
            $this->assertHomeroom($teacher->id, $filters['class_id'], $filters['academic_year_id'], $filters['semester_id']);

            $checks = $this->completeness->checkClass(
                $filters['class_id'],
                $filters['academic_year_id'],
                $filters['semester_id']
            );

            $stats = [
                'total' => count($checks),
                'lengkap' => collect($checks)->where('complete', true)->count(),
                'belum_lengkap' => collect($checks)->where('complete', false)->count(),
            ];
        }

        return view('wali.completeness', array_merge($this->filterLists(), [
            'filters' => $filters,
            'classes' => $classes,
            'checks' => $checks,
            'stats' => $stats,
        ]));
    }

    // -----------------------------------------------------------------
    // Preview rapor satu siswa
    // -----------------------------------------------------------------

    public function preview(Request $request, Student $student)
    {
        $filters = $request->validate([
            'academic_year_id' => 'nullable|integer',
            'semester_id' => 'nullable|integer',
        ]);

        $teacher = $this->resolveTeacher($request);

        [$defaultAy, $defaultSem] = $this->defaultPeriod($request, $teacher->id);
        $filters['academic_year_id'] ??= $defaultAy;
        $filters['semester_id'] ??= $defaultSem;

        $this->assertStudentAccess(
            $teacher->id,
            $student,
            $filters['academic_year_id'],
            $filters['semester_id']
        );

        $classId = $student->class_id;
        $ay = $filters['academic_year_id'];
        $sem = $filters['semester_id'];

        $check = $this->completeness->check($student, $classId, $ay, $sem);

        // Nilai akhir per mapel (hanya mapel yang diajarkan di kelas).
        $finals = [];
        $descriptions = TeacherDescription::query()
            ->where('student_id', $student->id)
            ->where('academic_year_id', $ay)
            ->where('semester_id', $sem)
            ->get()
            ->keyBy('subject_id');

        foreach ($check['subjects'] as $subject) {
            $hasSubmitted = AssessmentScore::query()
                ->where('student_id', $student->id)
                ->where('subject_id', $subject->id)
                ->where('academic_year_id', $ay)
                ->where('semester_id', $sem)
                ->whereIn('status', ['terkirim', 'dikunci'])
                ->exists();

            $final = null;

            if ($hasSubmitted) {
                try {
                    $final = $this->scores->finalScore($student->id, $subject->id, $ay, $sem);
                } catch (ValidationException) {
                    $final = null;
                }
            }

            $finals[] = [
                'subject' => $subject,
                'final_score' => $final,
                'kktp' => $this->scores->kktpFor($subject->id),
                'passed' => $final === null ? null : $final >= $this->scores->kktpFor($subject->id),
                'description' => $descriptions[$subject->id]->description ?? null,
                'submitted' => $hasSubmitted,
            ];
        }

        $attendanceRows = $this->attendance->recapPerStudent($classId, $ay, $sem);
        $attendance = collect($attendanceRows)->firstWhere('student.id', $student->id)
            ?? ['hadir' => 0, 'sakit' => 0, 'izin' => 0, 'alpa' => 0, 'total' => 0, 'persentase_hadir' => null];

        $achievements = StudentAchievement::query()
            ->where('student_id', $student->id)
            ->where('academic_year_id', $ay)
            ->where('semester_id', $sem)
            ->orderBy('title')
            ->get();

        $extracurriculars = StudentExtracurricular::query()
            ->with('extracurricular')
            ->where('student_id', $student->id)
            ->where('academic_year_id', $ay)
            ->where('semester_id', $sem)
            ->get();

        $reportCard = $check['report_card'];
        $homeroomNote = $reportCard?->notes()->where('note_type', 'wali_kelas')->latest()->first();

        return view('wali.preview', array_merge($this->filterLists(), [
            'student' => $student,
            'filters' => $filters,
            'check' => $check,
            'finals' => $finals,
            'attendance' => $attendance,
            'achievements' => $achievements,
            'extracurriculars' => $extracurriculars,
            'homeroomNote' => $homeroomNote,
            'reportCard' => $reportCard,
        ]));
    }

    // -----------------------------------------------------------------
    // Pengajuan rapor
    // -----------------------------------------------------------------

    public function submit(Request $request)
    {
        $d = $request->validate([
            'student_id' => 'required|integer|exists:students,id',
            'class_id' => 'required|integer|exists:classes,id',
            'academic_year_id' => 'required|integer|exists:academic_years,id',
            'semester_id' => 'required|integer|exists:semesters,id',
        ]);

        $teacher = $this->resolveTeacher($request);
        $student = Student::findOrFail($d['student_id']);

        if ((int) $student->class_id !== (int) $d['class_id']) {
            return back()->withErrors(['student_id' => 'Siswa ini bukan anggota kelas yang dipilih.']);
        }

        $this->assertHomeroom($teacher->id, $d['class_id'], $d['academic_year_id'], $d['semester_id']);

        $check = $this->completeness->check($student, $d['class_id'], $d['academic_year_id'], $d['semester_id']);

        if (! $check['complete']) {
            $messages = collect($check['missing'])->pluck('message')->implode(' ');

            return back()->withErrors([
                'completeness' => 'Rapor belum lengkap dan tidak dapat diajukan. Kekurangan: '.$messages,
            ]);
        }

        $reportCard = null;

        DB::transaction(function () use ($d, $student, $request, &$reportCard) {
            $reportCard = $this->findOrCreateDraft($student, $d['academic_year_id'], $d['semester_id']);

            if (! $reportCard->isDraft()) {
                throw ValidationException::withMessages([
                    'status' => "Rapor ini sudah berstatus {$reportCard->status} dan tidak dapat diajukan ulang.",
                ]);
            }

            $reportCard->update(['status' => 'diajukan']);

            // Pengaju dan waktu pengajuan dicatat lewat ActivityLog
            // (kolom issued_by dipakai penerbit di Stage 10).
            $this->logActivity($request, 'report_card.submitted', ReportCard::class, $reportCard->id, [
                'student_id' => $student->id,
                'class_id' => $d['class_id'],
                'academic_year_id' => $d['academic_year_id'],
                'semester_id' => $d['semester_id'],
                'submitted_by' => $request->user()->id,
                'submitted_at' => now()->toIso8601String(),
            ]);
        });

        return back()->with('success', "Rapor {$student->name} diajukan untuk pemeriksaan.");
    }

    // -----------------------------------------------------------------
    // Riwayat akademik siswa (lintas semester, hanya-baca)
    // -----------------------------------------------------------------

    public function history(Request $request, Student $student)
    {
        $teacher = $this->resolveTeacher($request);

        // Wali hanya boleh melihat riwayat siswa di kelas walinya
        // (periode mana pun).
        $this->assertHomeroom($teacher->id, $student->class_id ?? 0);

        $periods = collect();

        $scorePeriods = AssessmentScore::query()
            ->where('student_id', $student->id)
            ->select(['academic_year_id', 'semester_id'])
            ->distinct()
            ->get();

        $attendancePeriods = \App\Models\StudentAttendance::query()
            ->where('student_id', $student->id)
            ->select(['academic_year_id', 'semester_id'])
            ->distinct()
            ->get();

        $achievementPeriods = StudentAchievement::query()
            ->where('student_id', $student->id)
            ->select(['academic_year_id', 'semester_id'])
            ->distinct()
            ->get();

        // Kunci periode diambil sebagai string lewat koleksi dasar:
        // merge() pada Eloquent Collection mendeduplikasi berdasarkan
        // kunci model, dan model hasil select parsial ini tidak punya
        // id sehingga semuanya akan terbuang.
        $periodKeyOf = fn ($rows) => $rows->map(
            fn ($r) => $r->academic_year_id.'-'.$r->semester_id
        );

        $periodKeys = $periodKeyOf($scorePeriods)
            ->merge($periodKeyOf($attendancePeriods))
            ->merge($periodKeyOf($achievementPeriods))
            ->unique()
            ->values();

        foreach ($periodKeys as $key) {
            [$ay, $sem] = explode('-', $key);
            $ay = (int) $ay;
            $sem = (int) $sem;

            $subjectIds = AssessmentScore::query()
                ->where('student_id', $student->id)
                ->where('academic_year_id', $ay)
                ->where('semester_id', $sem)
                ->distinct()
                ->pluck('subject_id');

            $finals = [];

            foreach ($subjectIds as $subjectId) {
                $subject = \App\Models\Subject::find($subjectId);

                try {
                    $final = $this->scores->finalScore($student->id, $subjectId, $ay, $sem);
                } catch (ValidationException) {
                    $final = null;
                }

                $finals[] = [
                    'subject' => $subject,
                    'final_score' => $final,
                    'kktp' => $this->scores->kktpFor($subjectId),
                ];
            }

            $agg = $this->attendance->aggregate([
                'class_id' => $student->class_id,
                'academic_year_id' => $ay,
                'semester_id' => $sem,
            ]);
            $attendance = $agg[$student->id] ?? [
                'hadir' => 0, 'sakit' => 0, 'izin' => 0, 'alpa' => 0,
                'total' => 0, 'persentase_hadir' => null,
            ];

            $achievements = StudentAchievement::query()
                ->where('student_id', $student->id)
                ->where('academic_year_id', $ay)
                ->where('semester_id', $sem)
                ->orderBy('title')
                ->get();

            $periods->push([
                'academic_year' => AcademicYear::find($ay),
                'semester' => Semester::find($sem),
                'academic_year_id' => $ay,
                'semester_id' => $sem,
                'finals' => $finals,
                'attendance' => $attendance,
                'achievements' => $achievements,
            ]);
        }

        $periods = $periods->sortBy([
            ['academic_year_id', 'desc'],
            ['semester_id', 'desc'],
        ])->values();

        return view('wali.history', [
            'student' => $student,
            'periods' => $periods,
        ]);
    }
}
