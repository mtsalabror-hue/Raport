<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\P5Project;
use App\Models\P5Score;
use App\Models\ReportCard;
use App\Models\Student;
use App\Models\Teacher;
use App\Services\ReportCardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Fase C (RDM): P5 yang dikoordinatori guru yang login.
 *
 * Guru hanya boleh membuka dan mengisi proyek yang
 * coordinator_id-nya sama dengan data guru akunnya — ditegakkan
 * di backend (pola Stage 11: guru pembimbing PKL).
 */
class P5Controller extends Controller
{
    public function __construct(
        private ReportCardService $reportCards,
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

    private function assertCoordinator(Teacher $teacher, P5Project $project): void
    {
        if ((int) $project->coordinator_id !== (int) $teacher->id) {
            abort(403, 'Proyek P5 ini bukan yang Anda koordinatori.');
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

    /** Daftar proyek P5 yang dikoordinatori guru ini. */
    public function index(Request $request)
    {
        $teacher = $this->resolveTeacher($request);

        $projects = P5Project::query()
            ->with(['schoolClass', 'academicYear', 'semester'])
            ->where('coordinator_id', $teacher->id)
            ->orderByDesc('updated_at')
            ->paginate(20);

        return view('teacher.p5.index', [
            'projects' => $projects,
            'teacher' => $teacher,
        ]);
    }

    /**
     * Form isi capaian massal: satu halaman, semua siswa kelas
     * proyek, tulis capaian + deskripsi per siswa
     * (pola form bulk sikap/presensi).
     */
    public function show(Request $request, P5Project $project)
    {
        $teacher = $this->resolveTeacher($request);
        $this->assertCoordinator($teacher, $project);

        $project->loadMissing(['schoolClass', 'academicYear', 'semester', 'coordinator']);

        $students = Student::query()
            ->where('class_id', $project->class_id)
            ->orderBy('name')
            ->get();

        $existing = P5Score::query()
            ->where('p5_project_id', $project->id)
            ->whereIn('student_id', $students->pluck('id'))
            ->get()
            ->keyBy('student_id');

        return view('teacher.p5.show', [
            'project' => $project,
            'students' => $students,
            'existing' => $existing,
        ]);
    }

    /**
     * Simpan capaian massal untuk satu proyek dalam satu transaksi.
     * Upsert per (proyek, siswa): baris yang capaian dan
     * deskripsinya kosong dilewati (tidak dibuat, tidak dihapus).
     * Rapor yang sudah diterbitkan/dikunci tidak boleh dikoreksi
     * lewat jalur ini (pola assertEditable Stage 10).
     */
    public function storeScores(Request $request, P5Project $project)
    {
        $d = $request->validate([
            'records' => 'required|array|min:1',
            'records.*.student_id' => 'required|integer|exists:students,id|distinct',
            'records.*.capaian' => 'nullable|string|max:2000',
            'records.*.deskripsi' => 'nullable|string|max:2000',
        ]);

        $teacher = $this->resolveTeacher($request);
        $this->assertCoordinator($teacher, $project);

        // Semua siswa pada payload harus anggota kelas proyek ini.
        $studentIds = collect($d['records'])->pluck('student_id')->unique()->values();
        $inClass = Student::query()
            ->whereIn('id', $studentIds)
            ->where('class_id', $project->class_id)
            ->count();

        if ($inClass !== $studentIds->count()) {
            return back()->withErrors(['records' => 'Ada siswa yang bukan anggota kelas proyek ini.']);
        }

        // P5 bukan komponen kelengkapan rapor, tetapi rapor yang
        // sudah diterbitkan/dikunci tetap tidak boleh dikoreksi.
        $published = ReportCard::query()
            ->whereIn('student_id', $studentIds)
            ->where('academic_year_id', $project->academic_year_id)
            ->where('semester_id', $project->semester_id)
            ->whereIn('status', ReportCardService::PUBLISHED_STATUSES)
            ->first();

        if ($published) {
            $this->reportCards->assertEditable($published, $request->user(), 'nilai P5');
        }

        $saved = 0;

        DB::transaction(function () use ($d, $project, $request, &$saved) {
            foreach ($d['records'] as $row) {
                $capaian = $row['capaian'] ?? null;
                $deskripsi = $row['deskripsi'] ?? null;

                if (empty($capaian) && empty($deskripsi)) {
                    continue;
                }

                $score = P5Score::updateOrCreate(
                    [
                        'p5_project_id' => $project->id,
                        'student_id' => $row['student_id'],
                    ],
                    [
                        'capaian' => $capaian,
                        'deskripsi' => $deskripsi,
                    ]
                );

                $this->logActivity($request, 'p5_score.saved', P5Score::class, $score->id, [
                    'p5_project_id' => $project->id,
                    'student_id' => $row['student_id'],
                ]);

                $saved++;
            }
        });

        return back()->with('success', "{$saved} capaian P5 disimpan.");
    }
}
