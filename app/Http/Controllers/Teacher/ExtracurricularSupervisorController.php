<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\ActivityLog;
use App\Models\Extracurricular;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentExtracurricular;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Ekstra Binaan (Fase F / RDM): guru yang ditunjuk sebagai
 * pembina menilai siswa binaannya pada ekstrakurikuler yang
 * dibinanya.
 *
 * Nilai memakai tabel student_extracurriculars yang sama dengan
 * pengisian oleh wali kelas: pembina dan wali mengisi record yang
 * sama, yang terakhir menyimpan menang (last-writer-wins).
 * Setiap penyimpanan dicatat di ActivityLog agar ketahuan siapa
 * yang terakhir mengubah nilai.
 */
class ExtracurricularSupervisorController extends Controller
{
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

    private function assertSupervisor(int $teacherId, Extracurricular $extracurricular): void
    {
        if ((int) $extracurricular->supervisor_id !== (int) $teacherId) {
            abort(403, 'Ekstrakurikuler ini bukan binaan Anda.');
        }
    }

    private function defaultPeriod(Request $request): array
    {
        $ay = $request->integer('academic_year_id') ?: null;
        $sem = $request->integer('semester_id') ?: null;

        if ($ay && $sem) {
            return [$ay, $sem];
        }

        $activeYear = AcademicYear::where('is_active', true)->first();
        $activeSem = $activeYear
            ? Semester::where('academic_year_id', $activeYear->id)->where('is_active', true)->first()
            : null;

        return [$activeYear?->id, $activeSem?->id];
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
     * Daftar ekstrakurikuler yang dibina guru yang login.
     */
    public function index(Request $request)
    {
        $teacher = $this->resolveTeacher($request);

        $supervised = Extracurricular::query()
            ->where('supervisor_id', $teacher->id)
            ->withCount('studentRecords')
            ->orderBy('name')
            ->get();

        return view('teacher.supervised.index', [
            'teacher' => $teacher,
            'supervised' => $supervised,
        ]);
    }

    /**
     * Daftar siswa binaan pada satu ekstrakurikuler + periode,
     * beserta nilai + deskripsi yang sudah diisi.
     */
    public function show(Request $request, Extracurricular $extracurricular)
    {
        $teacher = $this->resolveTeacher($request);
        $this->assertSupervisor($teacher->id, $extracurricular);

        $filters = $request->validate([
            'academic_year_id' => 'nullable|integer',
            'semester_id' => 'nullable|integer',
        ]);

        [$ay, $sem] = $this->defaultPeriod($request);
        $filters['academic_year_id'] ??= $ay;
        $filters['semester_id'] ??= $sem;

        $records = collect();
        $availableStudents = collect();

        if ($ay && $sem) {
            $records = StudentExtracurricular::query()
                ->with('student')
                ->where('extracurricular_id', $extracurricular->id)
                ->where('academic_year_id', $ay)
                ->where('semester_id', $sem)
                ->orderByDesc('updated_at')
                ->get();

            $availableStudents = Student::query()
                ->whereNotIn('id', $records->pluck('student_id'))
                ->orderBy('name')
                ->get();
        }

        return view('teacher.supervised.show', [
            'teacher' => $teacher,
            'extracurricular' => $extracurricular,
            'filters' => $filters,
            'records' => $records,
            'availableStudents' => $availableStudents,
            'academicYears' => AcademicYear::orderBy('name')->get(),
            'semesters' => Semester::orderBy('name')->get(),
        ]);
    }

    /**
     * Tambah siswa binaan (membuat record kosong; nilai diisi
     * lewat form penilaian di halaman yang sama).
     */
    public function addStudent(Request $request, Extracurricular $extracurricular)
    {
        $d = $request->validate([
            'student_id' => 'required|integer|exists:students,id',
            'academic_year_id' => 'required|integer|exists:academic_years,id',
            'semester_id' => 'required|integer|exists:semesters,id',
        ]);

        $teacher = $this->resolveTeacher($request);
        $this->assertSupervisor($teacher->id, $extracurricular);

        $record = StudentExtracurricular::firstOrCreate(
            [
                'student_id' => $d['student_id'],
                'extracurricular_id' => $extracurricular->id,
                'academic_year_id' => $d['academic_year_id'],
                'semester_id' => $d['semester_id'],
            ],
            ['score' => null, 'description' => null]
        );

        $this->logActivity($request, 'student_extracurricular.supervisor_added', StudentExtracurricular::class, $record->id, [
            'student_id' => $d['student_id'],
            'extracurricular_id' => $extracurricular->id,
            'supervisor_id' => $teacher->id,
        ]);

        return back()->with('success', 'Siswa ditambahkan ke binaan. Silakan isi nilai dan deskripsinya.');
    }

    /**
     * Simpan nilai + deskripsi massal untuk siswa binaan.
     * Upsert per (siswa, ekskul, AY, semester) pada tabel yang
     * sama dengan pengisian wali — yang terakhir menyimpan menang.
     */
    public function storeScores(Request $request, Extracurricular $extracurricular)
    {
        $d = $request->validate([
            'academic_year_id' => 'required|integer|exists:academic_years,id',
            'semester_id' => 'required|integer|exists:semesters,id',
            'records' => 'required|array|min:1',
            'records.*.student_id' => 'required|integer|exists:students,id|distinct',
            'records.*.score' => 'nullable|numeric|min:0|max:100',
            'records.*.description' => 'nullable|string|max:2000',
        ]);

        $teacher = $this->resolveTeacher($request);
        $this->assertSupervisor($teacher->id, $extracurricular);

        $saved = 0;

        DB::transaction(function () use ($d, $extracurricular, $request, $teacher, &$saved) {
            foreach ($d['records'] as $row) {
                $record = StudentExtracurricular::updateOrCreate(
                    [
                        'student_id' => $row['student_id'],
                        'extracurricular_id' => $extracurricular->id,
                        'academic_year_id' => $d['academic_year_id'],
                        'semester_id' => $d['semester_id'],
                    ],
                    [
                        'score' => $row['score'] ?? null,
                        'description' => $row['description'] ?? null,
                    ]
                );

                $this->logActivity($request, 'student_extracurricular.supervisor_scored', StudentExtracurricular::class, $record->id, [
                    'student_id' => $row['student_id'],
                    'extracurricular_id' => $extracurricular->id,
                    'supervisor_id' => $teacher->id,
                    'score' => $row['score'] ?? null,
                ]);

                $saved++;
            }
        });

        return back()->with('success', "{$saved} nilai ekstrakurikuler binaan disimpan.");
    }
}
