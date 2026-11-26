<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Internship;
use App\Models\InternshipAssessment;
use App\Models\Teacher;
use App\Models\TeachingAssignment;
use App\Services\CompetencyExamService;
use App\Services\InternshipService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Akses guru pembimbing PKL (Stage 11).
 *
 * Aturan yang dipakai karena skema tidak punya FK pembimbing:
 * guru hanya boleh melihat dan menilai penempatan yang kolom
 * supervisor-nya sama dengan nama guru yang login (perbandingan
 * tanpa memperhatikan huruf besar/kecil). Penempatan dengan
 * supervisor guru lain, atau tanpa supervisor, tidak terlihat
 * dan tidak dapat dinilai — ditegakkan di backend.
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

        abort(403, 'Akun guru belum terhubung ke data guru. Minta admin menghubungkan akun ini di data pengguna.');
    }

    /** Penempatan yang dibimbing guru ini (supervisor = namanya). */
    private function myQuery(Teacher $teacher)
    {
        return Internship::query()
            ->with(['student', 'assessments'])
            ->whereRaw('LOWER(supervisor) = ?', [mb_strtolower(trim($teacher->name))]);
    }

    private function assertMine(Teacher $teacher, Internship $internship): void
    {
        if (mb_strtolower(trim((string) $internship->supervisor)) !== mb_strtolower(trim($teacher->name))) {
            abort(403, 'Penempatan PKL ini bukan bimbingan Anda.');
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
        $teacher = $this->resolveTeacher($request);

        $items = $this->myQuery($teacher)
            ->orderBy('start_date')
            ->paginate(20)
            ->withQueryString();

        return view('guru.internships.index', [
            'teacher' => $teacher,
            'items' => $items,
        ]);
    }

    public function show(Request $request, Internship $internship)
    {
        $teacher = $this->resolveTeacher($request);
        $this->assertMine($teacher, $internship);

        $internship->load(['student', 'assessments']);
        $recap = $this->internships->recapFor($internship);

        return view('guru.internships.show', [
            'teacher' => $teacher,
            'internship' => $internship,
            'recap' => $recap,
        ]);
    }

    public function storeAssessment(Request $request, Internship $internship)
    {
        $teacher = $this->resolveTeacher($request);
        $this->assertMine($teacher, $internship);

        $d = $request->validate([
            'aspect' => 'required|string|max:255',
            'score' => 'nullable|numeric|min:0|max:100',
            'note' => 'nullable|string|max:2000',
        ]);

        $existing = InternshipAssessment::query()
            ->where('internship_id', $internship->id)
            ->where('aspect', $d['aspect'])
            ->first();

        DB::transaction(function () use ($internship, $d, $existing, $request) {
            if ($existing) {
                $old = $existing->score;
                $existing->update(['score' => $d['score'] ?? null, 'note' => $d['note'] ?? null]);
                $this->logActivity($request, 'internship_assessment.updated', InternshipAssessment::class, $existing->id, [
                    'internship_id' => $internship->id,
                    'aspect' => $d['aspect'],
                    'old_score' => $old,
                    'new_score' => $d['score'] ?? null,
                ]);
            } else {
                $assessment = InternshipAssessment::create([
                    'internship_id' => $internship->id,
                    'aspect' => $d['aspect'],
                    'score' => $d['score'] ?? null,
                    'note' => $d['note'] ?? null,
                ]);
                $this->logActivity($request, 'internship_assessment.created', InternshipAssessment::class, $assessment->id, [
                    'internship_id' => $internship->id,
                    'aspect' => $d['aspect'],
                    'new_score' => $d['score'] ?? null,
                ]);
            }
        });

        return back()->with('success', 'Penilaian aspek PKL disimpan.');
    }

    public function updateAssessment(Request $request, InternshipAssessment $assessment)
    {
        $teacher = $this->resolveTeacher($request);
        $this->assertMine($teacher, $assessment->internship);

        $d = $request->validate([
            'aspect' => 'required|string|max:255',
            'score' => 'nullable|numeric|min:0|max:100',
            'note' => 'nullable|string|max:2000',
        ]);

        $clash = InternshipAssessment::query()
            ->where('internship_id', $assessment->internship_id)
            ->where('aspect', $d['aspect'])
            ->where('id', '!=', $assessment->id)
            ->exists();

        if ($clash) {
            return back()->withErrors(['aspect' => "Aspek {$d['aspect']} sudah ada pada penempatan ini."]);
        }

        DB::transaction(function () use ($assessment, $d, $request) {
            $old = $assessment->score;
            $assessment->update([
                'aspect' => $d['aspect'],
                'score' => $d['score'] ?? null,
                'note' => $d['note'] ?? null,
            ]);
            $this->logActivity($request, 'internship_assessment.updated', InternshipAssessment::class, $assessment->id, [
                'internship_id' => $assessment->internship_id,
                'aspect' => $d['aspect'],
                'old_score' => $old,
                'new_score' => $d['score'] ?? null,
            ]);
        });

        return back()->with('success', 'Penilaian aspek PKL diperbarui.');
    }

    public function destroyAssessment(Request $request, InternshipAssessment $assessment)
    {
        $teacher = $this->resolveTeacher($request);
        $this->assertMine($teacher, $assessment->internship);

        DB::transaction(function () use ($assessment, $request) {
            $assessment->delete();
            $this->logActivity($request, 'internship_assessment.deleted', InternshipAssessment::class, $assessment->id, [
                'internship_id' => $assessment->internship_id,
                'aspect' => $assessment->aspect,
            ]);
        });

        return back()->with('success', 'Penilaian aspek PKL dihapus.');
    }

    /**
     * UKK hanya-baca untuk guru: jadwal + rekap untuk mapel yang
     * diampu guru ini (teaching_assignments, periode mana pun —
     * UKK tidak punya kolom periode).
     */
    public function competencyExams(Request $request)
    {
        $teacher = $this->resolveTeacher($request);

        $subjectIds = TeachingAssignment::query()
            ->where('teacher_id', $teacher->id)
            ->distinct()
            ->pluck('subject_id');

        $schedules = $this->exams->schedules(
            \App\Models\CompetencyExam::query()->with(['subject', 'student'])
                ->whereIn('subject_id', $subjectIds)
        );

        $perSubject = $this->exams->recapPerSubject(
            \App\Models\CompetencyExam::query()->with('subject')
                ->whereIn('subject_id', $subjectIds)
        );

        return view('guru.competency_exams.index', [
            'teacher' => $teacher,
            'schedules' => $schedules,
            'perSubject' => $perSubject,
        ]);
    }
}
