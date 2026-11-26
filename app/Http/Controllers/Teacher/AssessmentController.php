<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\AssessmentComponent;
use App\Models\AssessmentScore;
use App\Models\ScoreRevision;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherDescription;
use App\Models\TeachingAssignment;
use App\Services\AssessmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AssessmentController extends Controller
{
    public function __construct(private AssessmentService $scores)
    {
    }

    /**
     * Selesaikan guru yang sedang login.
     * Urutan: akun terhubung ke data guru -> penugasan mengajar untuk
     * mapel/kelas/periode yang diminta -> satu-satunya guru di sistem.
     * Jika tidak ada yang cocok, tolak dengan pesan yang jelas.
     */
    private function resolveTeacher(Request $request, ?array $scope = null): Teacher
    {
        $user = $request->user();

        if ($user && $user->teacher_id) {
            return Teacher::findOrFail($user->teacher_id);
        }

        if ($scope) {
            $assigned = TeachingAssignment::query()
                ->where('subject_id', $scope['subject_id'])
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
     * Periksa apakah guru berhak mengelola nilai untuk mapel/kelas/periode.
     * Jika sudah ada penugasan mengajar untuk kombinasi tersebut, guru
     * harus termasuk di dalamnya. Jika belum ada penugasan sama sekali,
     * pemeriksaan dilewati (Stage 6 belum mengatur penugasan).
     */
    private function assertAssignment(int $teacherId, int $subjectId, int $classId, int $academicYearId, int $semesterId): void
    {
        $any = TeachingAssignment::query()
            ->where('subject_id', $subjectId)
            ->where('class_id', $classId)
            ->where('academic_year_id', $academicYearId)
            ->where('semester_id', $semesterId)
            ->exists();

        if (! $any) {
            return;
        }

        $mine = TeachingAssignment::query()
            ->where('teacher_id', $teacherId)
            ->where('subject_id', $subjectId)
            ->where('class_id', $classId)
            ->where('academic_year_id', $academicYearId)
            ->where('semester_id', $semesterId)
            ->exists();

        if (! $mine) {
            abort(403, 'Nilai ini bukan penugasan Anda.');
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

    public function index(Request $request)
    {
        $filters = $request->validate([
            'class_id' => 'nullable|integer',
            'subject_id' => 'nullable|integer',
            'academic_year_id' => 'nullable|integer',
            'semester_id' => 'nullable|integer',
        ]);

        $students = collect();
        $components = collect();
        $scores = collect();
        $loadedAt = now()->toIso8601String();

        if (! empty($filters['class_id']) && ! empty($filters['subject_id'])) {
            $students = Student::query()->where('class_id', $filters['class_id'])->orderBy('name')->get();
            $components = $this->scores->componentsFor($filters['subject_id']);
            $scores = AssessmentScore::query()
                ->where('class_id', $filters['class_id'])
                ->where('subject_id', $filters['subject_id'])
                ->when(! empty($filters['academic_year_id']), fn ($q) => $q->where('academic_year_id', $filters['academic_year_id']))
                ->when(! empty($filters['semester_id']), fn ($q) => $q->where('semester_id', $filters['semester_id']))
                ->get()
                ->keyBy(fn ($s) => $s->student_id.'-'.$s->component_id);
        }

        return view('teacher.assessments.index', [
            'filters' => $filters,
            'students' => $students,
            'components' => $components,
            'scores' => $scores,
            'loadedAt' => $loadedAt,
            'classes' => \App\Models\SchoolClass::orderBy('name')->get(),
            'subjects' => \App\Models\Subject::orderBy('name')->get(),
            'academicYears' => \App\Models\AcademicYear::orderBy('name')->get(),
            'semesters' => \App\Models\Semester::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $d = $request->validate([
            'student_id' => 'required|integer|exists:students,id',
            'subject_id' => 'required|integer|exists:subjects,id',
            'component_id' => 'required|integer|exists:assessment_components,id',
            'class_id' => 'required|integer|exists:classes,id',
            'academic_year_id' => 'required|integer|exists:academic_years,id',
            'semester_id' => 'required|integer|exists:semesters,id',
            'score' => 'required|numeric|min:0|max:100',
        ]);

        $teacher = $this->resolveTeacher($request, $d);
        $this->assertAssignment($teacher->id, $d['subject_id'], $d['class_id'], $d['academic_year_id'], $d['semester_id']);

        $exists = AssessmentScore::query()
            ->where('student_id', $d['student_id'])
            ->where('subject_id', $d['subject_id'])
            ->where('component_id', $d['component_id'])
            ->where('academic_year_id', $d['academic_year_id'])
            ->where('semester_id', $d['semester_id'])
            ->first();

        if ($exists && $exists->isLocked()) {
            return back()->withErrors(['score' => 'Nilai sudah terkirim atau terkunci dan tidak dapat diubah langsung. Ajukan revisi.']);
        }

        DB::transaction(function () use ($d, $teacher, $exists, $request) {
            if ($exists) {
                $old = $exists->score;
                $exists->update(['score' => $d['score']]);
                $this->logActivity($request, 'score.updated', AssessmentScore::class, $exists->id, [
                    'old_score' => $old, 'new_score' => $d['score'],
                ]);
            } else {
                $score = AssessmentScore::create($d + ['teacher_id' => $teacher->id, 'status' => 'draft']);
                $this->logActivity($request, 'score.created', AssessmentScore::class, $score->id, [
                    'new_score' => $d['score'],
                ]);
            }
        });

        return back()->with('success', 'Nilai disimpan sebagai draft.');
    }

    /**
     * Simpan massal gaya spreadsheet. Payload:
     * scores: [{student_id, component_id, score|null}], plus filter umum
     * (subject_id, class_id, academic_year_id, semester_id) dan loaded_at
     * untuk deteksi konflik (data berubah sejak halaman dimuat).
     */
    public function storeBulk(Request $request)
    {
        $d = $request->validate([
            'subject_id' => 'required|integer|exists:subjects,id',
            'class_id' => 'required|integer|exists:classes,id',
            'academic_year_id' => 'required|integer|exists:academic_years,id',
            'semester_id' => 'required|integer|exists:semesters,id',
            'loaded_at' => 'nullable|date',
            'scores' => 'required|array|min:1',
            'scores.*.student_id' => 'required|integer|exists:students,id',
            'scores.*.component_id' => 'required|integer|exists:assessment_components,id',
            'scores.*.score' => 'nullable|numeric|min:0|max:100',
        ]);

        $teacher = $this->resolveTeacher($request, $d);
        $this->assertAssignment($teacher->id, $d['subject_id'], $d['class_id'], $d['academic_year_id'], $d['semester_id']);

        if (! empty($d['loaded_at'])) {
            // Samakan format ke 'Y-m-d H:i:s' agar perbandingan dengan
            // kolom updated_at benar di semua driver database (string
            // ISO 8601 dengan 'T' akan kalah secara leksikografis).
            $loadedAt = \Illuminate\Support\Carbon::parse($d['loaded_at'])->toDateTimeString();

            $changed = AssessmentScore::query()
                ->where('subject_id', $d['subject_id'])
                ->where('class_id', $d['class_id'])
                ->where('academic_year_id', $d['academic_year_id'])
                ->where('semester_id', $d['semester_id'])
                ->where('updated_at', '>', $loadedAt)
                ->count();

            if ($changed > 0) {
                return back()->withErrors([
                    'scores' => "Data nilai berubah sejak halaman dimuat ({$changed} nilai diperbarui orang lain). Muat ulang halaman dan periksa kembali sebelum menyimpan.",
                ]);
            }
        }

        $saved = 0;
        $skippedLocked = 0;

        DB::transaction(function () use ($d, $teacher, $request, &$saved, &$skippedLocked) {
            foreach ($d['scores'] as $row) {
                $exists = AssessmentScore::query()
                    ->where('student_id', $row['student_id'])
                    ->where('subject_id', $d['subject_id'])
                    ->where('component_id', $row['component_id'])
                    ->where('academic_year_id', $d['academic_year_id'])
                    ->where('semester_id', $d['semester_id'])
                    ->first();

                if ($exists && $exists->isLocked()) {
                    $skippedLocked++;
                    continue;
                }

                if ($row['score'] === null && ! $exists) {
                    continue;
                }

                if ($exists) {
                    $old = $exists->score;
                    $exists->update(['score' => $row['score']]);
                    $this->logActivity($request, 'score.updated', AssessmentScore::class, $exists->id, [
                        'old_score' => $old, 'new_score' => $row['score'],
                    ]);
                } else {
                    $score = AssessmentScore::create([
                        'student_id' => $row['student_id'],
                        'subject_id' => $d['subject_id'],
                        'component_id' => $row['component_id'],
                        'class_id' => $d['class_id'],
                        'academic_year_id' => $d['academic_year_id'],
                        'semester_id' => $d['semester_id'],
                        'teacher_id' => $teacher->id,
                        'score' => $row['score'],
                        'status' => 'draft',
                    ]);
                    $this->logActivity($request, 'score.created', AssessmentScore::class, $score->id, [
                        'new_score' => $row['score'],
                    ]);
                }

                $saved++;
            }
        });

        $message = "{$saved} nilai disimpan sebagai draft.";
        if ($skippedLocked > 0) {
            $message .= " {$skippedLocked} nilai dilewati karena sudah terkirim atau terkunci.";
        }

        return back()->with('success', $message);
    }

    public function submit(Request $request)
    {
        $d = $request->validate([
            'subject_id' => 'nullable|integer|exists:subjects,id',
            'class_id' => 'nullable|integer|exists:classes,id',
            'academic_year_id' => 'nullable|integer|exists:academic_years,id',
            'semester_id' => 'nullable|integer|exists:semesters,id',
        ]);

        $teacher = $this->resolveTeacher($request, ! empty($d['subject_id']) && ! empty($d['class_id']) ? $d : null);

        $query = AssessmentScore::query()
            ->where('teacher_id', $teacher->id)
            ->whereIn('status', ['draft', 'siap_kirim']);

        foreach (['subject_id', 'class_id', 'academic_year_id', 'semester_id'] as $key) {
            if (! empty($d[$key])) {
                $query->where($key, $d[$key]);
            }
        }

        $ids = $query->pluck('id');
        $count = $ids->count();

        DB::transaction(function () use ($ids, $request) {
            AssessmentScore::query()->whereIn('id', $ids)->update(['status' => 'terkirim']);

            foreach ($ids as $id) {
                $this->logActivity($request, 'score.submitted', AssessmentScore::class, $id, []);
            }
        });

        return back()->with('success', "{$count} nilai dikirim dan dikunci dari perubahan langsung.");
    }

    public function lock(Request $request)
    {
        $d = $request->validate([
            'subject_id' => 'nullable|integer|exists:subjects,id',
            'class_id' => 'nullable|integer|exists:classes,id',
            'academic_year_id' => 'nullable|integer|exists:academic_years,id',
            'semester_id' => 'nullable|integer|exists:semesters,id',
        ]);

        $teacher = $this->resolveTeacher($request, ! empty($d['subject_id']) && ! empty($d['class_id']) ? $d : null);

        $query = AssessmentScore::query()
            ->where('teacher_id', $teacher->id)
            ->where('status', 'terkirim');

        foreach (['subject_id', 'class_id', 'academic_year_id', 'semester_id'] as $key) {
            if (! empty($d[$key])) {
                $query->where($key, $d[$key]);
            }
        }

        $ids = $query->pluck('id');

        DB::transaction(function () use ($ids, $request) {
            AssessmentScore::query()->whereIn('id', $ids)->update(['status' => 'dikunci']);

            foreach ($ids as $id) {
                $this->logActivity($request, 'score.locked', AssessmentScore::class, $id, []);
            }
        });

        return back()->with('success', "{$ids->count()} nilai dikunci.");
    }

    public function requestRevision(Request $request)
    {
        $d = $request->validate([
            'assessment_score_id' => 'required|integer|exists:assessment_scores,id',
            'reason' => 'required|string|max:1000',
            'new_score' => 'required|numeric|min:0|max:100',
        ]);

        $score = AssessmentScore::findOrFail($d['assessment_score_id']);
        $teacher = $this->resolveTeacher($request, [
            'subject_id' => $score->subject_id,
            'class_id' => $score->class_id,
            'academic_year_id' => $score->academic_year_id,
            'semester_id' => $score->semester_id,
        ]);

        if ($score->teacher_id !== $teacher->id) {
            abort(403, 'Revisi hanya dapat diajukan oleh guru pemilik nilai.');
        }

        if (! $score->isLocked()) {
            return back()->withErrors(['revision' => 'Hanya nilai yang sudah terkirim atau terkunci yang dapat direvisi.']);
        }

        $revision = null;

        DB::transaction(function () use ($d, $score, $request, &$revision) {
            $revision = ScoreRevision::create([
                'assessment_score_id' => $score->id,
                'requested_by' => $request->user()->id,
                'reason' => $d['reason'],
                'old_score' => $score->score,
                'new_score' => $d['new_score'],
                'status' => 'diajukan',
            ]);
            $score->update(['status' => 'perlu_revisi']);
            $this->logActivity($request, 'revision.requested', ScoreRevision::class, $revision->id, [
                'assessment_score_id' => $score->id,
                'old_score' => $score->score,
                'new_score' => $d['new_score'],
            ]);
        });

        return back()->with('success', 'Revisi diajukan dan menunggu keputusan.');
    }

    public function approveRevision(Request $request)
    {
        $d = $request->validate([
            'revision_id' => 'required|integer|exists:score_revisions,id',
            'decision' => 'required|in:disetujui,ditolak',
        ]);

        $revision = ScoreRevision::findOrFail($d['revision_id']);

        if ($revision->requested_by === $request->user()->id) {
            abort(403, 'Pengaju revisi tidak dapat menyetujui revisinya sendiri.');
        }

        if ($revision->status !== 'diajukan') {
            return back()->withErrors(['revision' => 'Revisi ini sudah diputuskan.']);
        }

        $score = AssessmentScore::findOrFail($revision->assessment_score_id);

        DB::transaction(function () use ($d, $revision, $score, $request) {
            $revision->update([
                'status' => $d['decision'],
                'decided_by' => $request->user()->id,
                'decided_at' => now(),
            ]);

            if ($d['decision'] === 'disetujui') {
                $score->update(['score' => $revision->new_score, 'status' => 'terkirim']);
            } else {
                $score->update(['status' => 'terkirim']);
            }

            $this->logActivity($request, 'revision.'.$d['decision'], ScoreRevision::class, $revision->id, [
                'assessment_score_id' => $score->id,
                'old_score' => $revision->old_score,
                'new_score' => $revision->new_score,
            ]);
        });

        return back()->with('success', 'Keputusan revisi disimpan.');
    }

    public function recap(Request $request)
    {
        $filters = $request->validate([
            'class_id' => 'nullable|integer',
            'subject_id' => 'nullable|integer',
            'academic_year_id' => 'nullable|integer',
            'semester_id' => 'nullable|integer',
        ]);

        $scores = AssessmentScore::query()
            ->with(['student', 'component'])
            ->when(! empty($filters['class_id']), fn ($q) => $q->where('class_id', $filters['class_id']))
            ->when(! empty($filters['subject_id']), fn ($q) => $q->where('subject_id', $filters['subject_id']))
            ->when(! empty($filters['academic_year_id']), fn ($q) => $q->where('academic_year_id', $filters['academic_year_id']))
            ->when(! empty($filters['semester_id']), fn ($q) => $q->where('semester_id', $filters['semester_id']))
            ->orderBy('student_id')
            ->get();

        $finals = [];
        $kktp = null;

        if (! empty($filters['subject_id']) && ! empty($filters['academic_year_id']) && ! empty($filters['semester_id'])) {
            try {
                $kktp = $this->scores->kktpFor($filters['subject_id']);

                foreach ($scores->groupBy('student_id') as $studentId => $group) {
                    $finals[$studentId] = $this->scores->finalScore(
                        $studentId,
                        $filters['subject_id'],
                        $filters['academic_year_id'],
                        $filters['semester_id']
                    );
                }
            } catch (ValidationException $e) {
                return back()->withErrors($e->errors());
            }
        }

        return view('teacher.assessments.recap', [
            'scores' => $scores,
            'filters' => $filters,
            'finals' => $finals,
            'kktp' => $kktp,
            'classes' => \App\Models\SchoolClass::orderBy('name')->get(),
            'subjects' => \App\Models\Subject::orderBy('name')->get(),
            'academicYears' => \App\Models\AcademicYear::orderBy('name')->get(),
            'semesters' => \App\Models\Semester::orderBy('name')->get(),
        ]);
    }

    /**
     * Rekap hanya-baca untuk wali kelas: hanya nilai yang sudah
     * terkirim atau terkunci yang terlihat.
     */
    public function waliRecap(Request $request)
    {
        $filters = $request->validate([
            'class_id' => 'nullable|integer',
            'subject_id' => 'nullable|integer',
            'academic_year_id' => 'nullable|integer',
            'semester_id' => 'nullable|integer',
        ]);

        $scores = AssessmentScore::query()
            ->with(['student', 'component'])
            ->whereIn('status', ['terkirim', 'dikunci'])
            ->when(! empty($filters['class_id']), fn ($q) => $q->where('class_id', $filters['class_id']))
            ->when(! empty($filters['subject_id']), fn ($q) => $q->where('subject_id', $filters['subject_id']))
            ->when(! empty($filters['academic_year_id']), fn ($q) => $q->where('academic_year_id', $filters['academic_year_id']))
            ->when(! empty($filters['semester_id']), fn ($q) => $q->where('semester_id', $filters['semester_id']))
            ->orderBy('student_id')
            ->get();

        $finals = [];
        $kktp = null;

        if (! empty($filters['subject_id']) && ! empty($filters['academic_year_id']) && ! empty($filters['semester_id'])) {
            try {
                $kktp = $this->scores->kktpFor($filters['subject_id']);

                foreach ($scores->groupBy('student_id') as $studentId => $group) {
                    $finals[$studentId] = $this->scores->finalScore(
                        $studentId,
                        $filters['subject_id'],
                        $filters['academic_year_id'],
                        $filters['semester_id']
                    );
                }
            } catch (ValidationException $e) {
                return back()->withErrors($e->errors());
            }
        }

        return view('teacher.assessments.recap', [
            'scores' => $scores,
            'filters' => $filters,
            'finals' => $finals,
            'kktp' => $kktp,
            'readOnly' => true,
            'classes' => \App\Models\SchoolClass::orderBy('name')->get(),
            'subjects' => \App\Models\Subject::orderBy('name')->get(),
            'academicYears' => \App\Models\AcademicYear::orderBy('name')->get(),
            'semesters' => \App\Models\Semester::orderBy('name')->get(),
        ]);
    }

    public function descriptions(Request $request)
    {
        return view('teacher.assessments.descriptions', [
            'students' => \App\Models\Student::orderBy('name')->get(),
            'subjects' => \App\Models\Subject::orderBy('name')->get(),
            'academicYears' => \App\Models\AcademicYear::orderBy('name')->get(),
            'semesters' => \App\Models\Semester::orderBy('name')->get(),
            'items' => TeacherDescription::query()->with(['student', 'subject'])->orderByDesc('updated_at')->paginate(20),
        ]);
    }

    public function history(Request $request)
    {
        $teacher = $this->resolveTeacher($request);

        $revisions = ScoreRevision::query()
            ->with(['score.student', 'score.subject'])
            ->whereHas('score', fn ($q) => $q->where('teacher_id', $teacher->id))
            ->orderByDesc('created_at')
            ->paginate(20);

        $logs = ActivityLog::query()
            ->where('user_id', $request->user()->id)
            ->whereIn('action', ['score.created', 'score.updated', 'score.submitted', 'score.locked', 'revision.requested', 'revision.disetujui', 'revision.ditolak'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('teacher.assessments.history', [
            'revisions' => $revisions,
            'logs' => $logs,
        ]);
    }

    public function components(Request $request)
    {
        $components = AssessmentComponent::query()
            ->with(['subject'])
            ->orderBy('subject_id')
            ->orderBy('name')
            ->get();

        return view('teacher.assessments.components', [
            'components' => $components,
            'subjects' => Subject::orderBy('name')->get(),
        ]);
    }

    public function storeComponent(Request $request)
    {
        $d = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:formatif,sumatif,praktik,proyek',
            'weight' => 'required|numeric|min:0|max:100',
            'subject_id' => 'nullable|integer|exists:subjects,id',
            'curriculum_id' => 'nullable|integer|exists:curricula,id',
            'kktp' => 'nullable|numeric|min:0|max:100',
        ]);

        $scopeTotal = (float) AssessmentComponent::query()
            ->where('is_active', true)
            ->where('subject_id', $d['subject_id'] ?? null)
            ->where('curriculum_id', $d['curriculum_id'] ?? null)
            ->sum('weight');

        if ($scopeTotal + (float) $d['weight'] > 100) {
            return back()->withErrors([
                'weight' => "Total bobot pada cakupan ini menjadi {$scopeTotal} + {$d['weight']}, melebihi 100.",
            ]);
        }

        AssessmentComponent::create($d + ['is_active' => true]);

        return back()->with('success', 'Komponen penilaian dibuat.');
    }

    public function calculate(Request $request)
    {
        $d = $request->validate([
            'subject_id' => 'nullable|integer',
            'academic_year_id' => 'nullable|integer',
            'semester_id' => 'nullable|integer',
        ]);

        $results = [];
        $errors = [];

        $groups = AssessmentScore::query()
            ->with('component')
            ->when(! empty($d['subject_id']), fn ($q) => $q->where('subject_id', $d['subject_id']))
            ->when(! empty($d['academic_year_id']), fn ($q) => $q->where('academic_year_id', $d['academic_year_id']))
            ->when(! empty($d['semester_id']), fn ($q) => $q->where('semester_id', $d['semester_id']))
            ->get()
            ->groupBy(fn ($s) => $s->student_id.'-'.$s->subject_id);

        foreach ($groups as $key => $group) {
            $first = $group->first();

            try {
                $final = $this->scores->finalScore(
                    $first->student_id,
                    $first->subject_id,
                    $first->academic_year_id,
                    $first->semester_id
                );
                $results[$key] = $final ?? 'belum dapat dihitung (tidak ada nilai)';
            } catch (ValidationException $e) {
                $errors[$key] = $e->errors()['weight'][0] ?? 'Total bobot tidak valid.';
                $results[$key] = 'belum dapat dihitung (bobot tidak valid)';
            }
        }

        return view('teacher.assessments.calculate', [
            'results' => $results,
            'errors' => $errors,
        ]);
    }

    public function storeDescription(Request $request)
    {
        $d = $request->validate([
            'student_id' => 'required|integer|exists:students,id',
            'subject_id' => 'required|integer|exists:subjects,id',
            'academic_year_id' => 'required|integer|exists:academic_years,id',
            'semester_id' => 'required|integer|exists:semesters,id',
            'description' => 'nullable|string|max:2000',
        ]);

        $teacher = $this->resolveTeacher($request, $d);

        $final = $this->scores->finalScore(
            $d['student_id'],
            $d['subject_id'],
            $d['academic_year_id'],
            $d['semester_id']
        );

        if ($final === null && empty($d['description'])) {
            return back()->withErrors([
                'description' => 'Belum ada nilai untuk siswa dan mapel ini, sehingga deskripsi otomatis tidak dapat dibuat. Tulis deskripsi secara manual.',
            ]);
        }

        $subject = Subject::find($d['subject_id']);
        $kktp = $this->scores->kktpFor($d['subject_id']);
        $description = $d['description'] ?: $this->scores->descriptionFor($final, $kktp, $subject?->name);

        TeacherDescription::updateOrCreate(
            [
                'student_id' => $d['student_id'],
                'subject_id' => $d['subject_id'],
                'academic_year_id' => $d['academic_year_id'],
                'semester_id' => $d['semester_id'],
            ],
            ['description' => $description, 'teacher_id' => $teacher->id]
        );

        return back()->with('success', 'Deskripsi capaian disimpan.');
    }
}
