<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\PromotionDecision;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Student;
use App\Services\PromotionService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Fase D (RDM): kenaikan kelas & kelulusan (admin/operator).
 * Urutan wajib: Langkah 1 luluskan kelas XII ke Alumni, baru
 * Langkah 2 naikkan kelas di bawahnya (ditegakkan PromotionService).
 */
class PromotionController extends Controller
{
    public function __construct(
        private PromotionService $promotions,
    ) {
    }

    /**
     * Halaman "Naik Kelas": langkah 1 luluskan XII, langkah 2
     * pemetaan kelas asal ke kelas tujuan.
     */
    public function index(Request $request)
    {
        $filters = $request->validate([
            'academic_year_id' => 'nullable|integer',
            'semester_id' => 'nullable|integer',
        ]);

        $academicYears = AcademicYear::orderBy('name')->get();

        $filters['academic_year_id'] ??= AcademicYear::where('is_active', true)->value('id')
            ?? $academicYears->first()?->id;

        $semesters = Semester::query()
            ->when($filters['academic_year_id'], fn ($q) => $q->where('academic_year_id', $filters['academic_year_id']))
            ->orderBy('name')
            ->get();

        $filters['semester_id'] ??= $semesters->firstWhere(
            fn ($s) => PromotionService::isEvenSemester($s)
        )?->id ?? $semesters->first()?->id;

        $semester = $filters['semester_id'] ? Semester::find($filters['semester_id']) : null;
        $isEven = $semester ? PromotionService::isEvenSemester($semester) : false;

        $classes = SchoolClass::query()
            ->where('academic_year_id', $filters['academic_year_id'])
            ->orderBy('level')
            ->orderBy('name')
            ->get();

        $classesByLevel = $classes->groupBy('level');

        // Statistik kelas XII: berapa siswa, berapa yang diputuskan
        // naik (siap diluluskan), berapa yang sudah alumni.
        $xiiStats = [];
        $pendingGraduation = 0;

        if ($filters['academic_year_id'] && $filters['semester_id']) {
            $pendingGraduation = $this->promotions->pendingGraduationCount(
                $filters['academic_year_id'], $filters['semester_id']
            );

            foreach ($classesByLevel[PromotionService::FINAL_LEVEL] ?? [] as $xii) {
                $total = Student::query()
                    ->where('class_id', $xii->id)
                    ->where('is_alumni', false)
                    ->count();

                $decidedNaik = PromotionDecision::query()
                    ->where('class_id', $xii->id)
                    ->where('academic_year_id', $filters['academic_year_id'])
                    ->where('semester_id', $filters['semester_id'])
                    ->where('status', PromotionDecision::STATUS_NAIK)
                    ->count();

                $xiiStats[] = [
                    'class' => $xii,
                    'total' => $total,
                    'decided_naik' => $decidedNaik,
                ];
            }
        }

        // Ringkasan keputusan per kelas asal (naik / tidak naik /
        // belum ditetapkan) untuk periode yang dipilih.
        $decisionSummary = [];

        if ($filters['academic_year_id'] && $filters['semester_id']) {
            foreach ($classes as $class) {
                $total = Student::query()
                    ->where('class_id', $class->id)
                    ->where('is_alumni', false)
                    ->count();

                $decided = PromotionDecision::query()
                    ->where('class_id', $class->id)
                    ->where('academic_year_id', $filters['academic_year_id'])
                    ->where('semester_id', $filters['semester_id'])
                    ->get()
                    ->groupBy('status')
                    ->map->count();

                $decisionSummary[$class->id] = [
                    'total' => $total,
                    'naik' => $decided[PromotionDecision::STATUS_NAIK] ?? 0,
                    'tidak_naik' => $decided[PromotionDecision::STATUS_TIDAK_NAIK] ?? 0,
                    'belum' => $total - (($decided[PromotionDecision::STATUS_NAIK] ?? 0) + ($decided[PromotionDecision::STATUS_TIDAK_NAIK] ?? 0)),
                ];
            }
        }

        return view('admin.promotions.index', [
            'filters' => $filters,
            'academicYears' => $academicYears,
            'semesters' => $semesters,
            'semester' => $semester,
            'isEven' => $isEven,
            'classes' => $classes,
            'classesByLevel' => $classesByLevel,
            'xiiStats' => $xiiStats,
            'pendingGraduation' => $pendingGraduation,
            'decisionSummary' => $decisionSummary,
        ]);
    }

    /**
     * Langkah 1: luluskan kelas XII ke Alumni.
     */
    public function graduate(Request $request)
    {
        $d = $request->validate([
            'academic_year_id' => 'required|integer|exists:academic_years,id',
            'semester_id' => 'required|integer|exists:semesters,id',
        ]);

        try {
            $result = $this->promotions->graduate($d['academic_year_id'], $d['semester_id'], $request->user());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('success', "Kelulusan selesai: {$result['graduated']} siswa kelas XII diluluskan menjadi alumni, {$result['stayed']} siswa tetap di kelas XII (tidak naik atau belum diputuskan).");
    }

    /**
     * Langkah 2: naikkan kelas sesuai pemetaan.
     */
    public function promote(Request $request)
    {
        $d = $request->validate([
            'academic_year_id' => 'required|integer|exists:academic_years,id',
            'semester_id' => 'required|integer|exists:semesters,id',
            'mappings' => 'required|array|min:1',
            'mappings.*.from_class_id' => 'required|integer|exists:classes,id',
            'mappings.*.to_class_id' => 'required|integer|exists:classes,id',
        ]);

        // Baris tanpa tujuan (pilihan kosong) diabaikan.
        $mappings = collect($d['mappings'])
            ->filter(fn ($m) => ! empty($m['to_class_id']))
            ->map(fn ($m) => [
                'from_class_id' => (int) $m['from_class_id'],
                'to_class_id' => (int) $m['to_class_id'],
            ])
            ->values()
            ->all();

        if (empty($mappings)) {
            return back()->withErrors(['mappings' => 'Pilih minimal satu kelas tujuan untuk menaikkan kelas.']);
        }

        try {
            $result = $this->promotions->promote($mappings, $d['academic_year_id'], $d['semester_id'], $request->user());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        $details = collect($result['details'])
            ->map(fn ($row) => "{$row['from']} ke {$row['to']}: {$row['promoted']} naik, {$row['stayed']} tinggal")
            ->implode('; ');

        return back()->with('success', "Kenaikan kelas selesai: {$result['promoted']} siswa naik kelas, {$result['stayed']} siswa tetap di kelas asal. {$details}");
    }

    /**
     * Halaman alumni (hanya-baca): nama, NIS, kelas terakhir,
     * tahun lulus; filter per tahun.
     */
    public function alumni(Request $request)
    {
        $filters = $request->validate([
            'alumni_year' => 'nullable|string|max:20',
        ]);

        $years = Student::query()
            ->where('is_alumni', true)
            ->whereNotNull('alumni_year')
            ->distinct()
            ->orderByDesc('alumni_year')
            ->pluck('alumni_year');

        $students = Student::query()
            ->where('is_alumni', true)
            ->when(! empty($filters['alumni_year']), fn ($q) => $q->where('alumni_year', $filters['alumni_year']))
            ->orderBy('alumni_year', 'desc')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        // Kelas terakhir: dari keputusan kenaikan kelas "naik" siswa
        // itu (class_id dikosongkan saat lulus, jadi kelas terakhir
        // dibaca dari baris promotion_decisions).
        $lastClasses = PromotionDecision::query()
            ->with('schoolClass')
            ->whereIn('student_id', $students->pluck('id'))
            ->where('status', PromotionDecision::STATUS_NAIK)
            ->orderByDesc('updated_at')
            ->get()
            ->groupBy('student_id')
            ->map(fn ($rows) => $rows->first()->schoolClass->name ?? '-');

        return view('admin.alumni.index', [
            'filters' => $filters,
            'years' => $years,
            'students' => $students,
            'lastClasses' => $lastClasses,
        ]);
    }
}
