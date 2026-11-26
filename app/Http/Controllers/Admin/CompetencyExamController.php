<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\CompetencyExam;
use App\Models\Student;
use App\Models\Subject;
use App\Services\CompetencyExamService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompetencyExamController extends Controller
{
    public function __construct(private CompetencyExamService $exams)
    {
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

    /**
     * Daftar jadwal ujian (kelompok subject_id + exam_date) +
     * form tambah peserta / jadwal baru.
     */
    public function index(Request $request)
    {
        $filters = $request->validate([
            'subject_id' => 'nullable|integer',
            'exam_date' => 'nullable|date',
        ]);

        $query = CompetencyExam::query()->with(['subject', 'student'])
            ->when(! empty($filters['subject_id']), fn ($q) => $q->where('subject_id', $filters['subject_id']))
            ->when(! empty($filters['exam_date']), fn ($q) => $q->whereDate('exam_date', $filters['exam_date']));

        $schedules = $this->exams->schedules($query);

        return view('admin.competency_exams.index', [
            'filters' => $filters,
            'schedules' => $schedules,
            'subjects' => Subject::orderBy('name')->get(),
            'students' => Student::orderBy('name')->get(),
        ]);
    }

    /**
     * Detail satu jadwal: daftar peserta + input hasil per peserta.
     * Jadwal diidentifikasi (subject_id, exam_date).
     */
    public function schedule(Request $request)
    {
        $d = $request->validate([
            'subject_id' => 'required|integer|exists:subjects,id',
            'exam_date' => 'required|date',
        ]);

        $subject = Subject::findOrFail($d['subject_id']);

        $participants = CompetencyExam::query()
            ->with('student')
            ->where('subject_id', $d['subject_id'])
            ->whereDate('exam_date', $d['exam_date'])
            ->orderBy('student_id')
            ->get();

        // Siswa yang belum menjadi peserta jadwal ini.
        $takenIds = $participants->pluck('student_id');
        $candidates = Student::query()
            ->whereNotIn('id', $takenIds)
            ->orderBy('name')
            ->get();

        $scored = $participants->whereNotNull('score');

        return view('admin.competency_exams.schedule', [
            'subject' => $subject,
            'exam_date' => $d['exam_date'],
            'participants' => $participants,
            'candidates' => $candidates,
            'average' => $scored->isEmpty()
                ? null
                : round($scored->avg(fn ($r) => (float) $r->score), 2, PHP_ROUND_HALF_UP),
        ]);
    }

    /**
     * Tambah peserta ke jadwal (baris score null), atau bila
     * siswa sudah terdaftar pada jadwal itu, perbarui hasilnya.
     * Satu siswa + satu mapel + satu tanggal hanya satu baris.
     */
    public function store(Request $request)
    {
        $d = $request->validate([
            'student_id' => 'required|integer|exists:students,id',
            'subject_id' => 'required|integer|exists:subjects,id',
            'exam_date' => 'required|date',
            'score' => 'nullable|numeric|min:0|max:100',
            'examiner' => 'nullable|string|max:255',
            'note' => 'nullable|string|max:2000',
        ]);

        $note = CompetencyExamService::buildNote($d['examiner'] ?? null, $d['note'] ?? null);

        $exam = null;
        $created = false;

        DB::transaction(function () use ($d, $note, $request, &$exam, &$created) {
            $exam = CompetencyExam::query()
                ->where('student_id', $d['student_id'])
                ->where('subject_id', $d['subject_id'])
                ->whereDate('exam_date', $d['exam_date'])
                ->first();

            if ($exam) {
                $old = $exam->score;
                $exam->update(['score' => $d['score'] ?? null, 'note' => $note]);
                $this->logActivity($request, 'competency_exam.updated', CompetencyExam::class, $exam->id, [
                    'student_id' => $d['student_id'],
                    'subject_id' => $d['subject_id'],
                    'exam_date' => $d['exam_date'],
                    'old_score' => $old,
                    'new_score' => $d['score'] ?? null,
                ]);
            } else {
                $exam = CompetencyExam::create([
                    'student_id' => $d['student_id'],
                    'subject_id' => $d['subject_id'],
                    'exam_date' => $d['exam_date'],
                    'score' => $d['score'] ?? null,
                    'note' => $note,
                ]);
                $created = true;
                $this->logActivity($request, 'competency_exam.created', CompetencyExam::class, $exam->id, [
                    'student_id' => $d['student_id'],
                    'subject_id' => $d['subject_id'],
                    'exam_date' => $d['exam_date'],
                    'new_score' => $d['score'] ?? null,
                ]);
            }
        });

        return back()->with('success', $created ? 'Peserta UKK ditambahkan ke jadwal.' : 'Hasil UKK peserta diperbarui.');
    }

    /**
     * Input / ubah hasil penilaian satu peserta (score + note).
     * Penguji dicatat lewat konvensi "Penguji: ..." di note.
     */
    public function update(Request $request, CompetencyExam $exam)
    {
        $d = $request->validate([
            'score' => 'nullable|numeric|min:0|max:100',
            'examiner' => 'nullable|string|max:255',
            'note' => 'nullable|string|max:2000',
        ]);

        // Bila form tidak mengirim penguji, pertahankan penguji
        // lama yang sudah tercatat di note.
        $examiner = array_key_exists('examiner', $d) && $d['examiner'] !== null
            ? $d['examiner']
            : $exam->examiner();

        $note = CompetencyExamService::buildNote($examiner, $d['note'] ?? $exam->noteWithoutExaminer());

        DB::transaction(function () use ($exam, $d, $note, $request) {
            $old = $exam->score;
            $exam->update(['score' => $d['score'] ?? null, 'note' => $note]);
            $this->logActivity($request, 'competency_exam.updated', CompetencyExam::class, $exam->id, [
                'student_id' => $exam->student_id,
                'subject_id' => $exam->subject_id,
                'exam_date' => $exam->exam_date?->toDateString(),
                'old_score' => $old,
                'new_score' => $d['score'] ?? null,
            ]);
        });

        return back()->with('success', 'Hasil UKK disimpan.');
    }

    public function destroy(Request $request, CompetencyExam $exam)
    {
        DB::transaction(function () use ($exam, $request) {
            $exam->delete();
            $this->logActivity($request, 'competency_exam.deleted', CompetencyExam::class, $exam->id, [
                'student_id' => $exam->student_id,
                'subject_id' => $exam->subject_id,
                'exam_date' => $exam->exam_date?->toDateString(),
            ]);
        });

        return back()->with('success', 'Data UKK dihapus.');
    }

    /**
     * Rekap hasil UKK: per siswa (rata-rata per mapel + gabungan)
     * dan per mapel. Tanpa label lulus/tidak lulus — ambang
     * kelulusan belum ditetapkan sekolah.
     */
    public function recap(Request $request)
    {
        $filters = $request->validate([
            'student_id' => 'nullable|integer',
            'subject_id' => 'nullable|integer',
        ]);

        $studentIds = CompetencyExam::query()
            ->when(! empty($filters['student_id']), fn ($q) => $q->where('student_id', $filters['student_id']))
            ->when(! empty($filters['subject_id']), fn ($q) => $q->where('subject_id', $filters['subject_id']))
            ->distinct()
            ->pluck('student_id');

        $recaps = $studentIds->map(fn ($id) => $this->exams->recapForStudent($id))->values()->all();
        $students = Student::query()->whereIn('id', $studentIds)->orderBy('name')->get()->keyBy('id');

        $perSubject = $this->exams->recapPerSubject(
            CompetencyExam::query()->with('subject')
                ->when(! empty($filters['subject_id']), fn ($q) => $q->where('subject_id', $filters['subject_id']))
        );

        return view('admin.competency_exams.recap', [
            'filters' => $filters,
            'recaps' => $recaps,
            'students' => $students,
            'perSubject' => $perSubject,
            'allStudents' => Student::orderBy('name')->get(),
            'subjects' => Subject::orderBy('name')->get(),
        ]);
    }
}
