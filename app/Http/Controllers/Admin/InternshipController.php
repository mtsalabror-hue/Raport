<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Internship;
use App\Models\InternshipAssessment;
use App\Models\Student;
use App\Services\InternshipService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InternshipController extends Controller
{
    public function __construct(private InternshipService $internships)
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
     * Daftar penempatan + daftar mitra industri (company_name yang
     * sudah ada) + form tambah penempatan.
     */
    public function index(Request $request)
    {
        $filters = $request->validate([
            'student_id' => 'nullable|integer',
            'company_name' => 'nullable|string|max:255',
            'status' => 'nullable|string|in:mendatang,berjalan,selesai',
        ]);

        $items = Internship::query()
            ->with(['student', 'assessments'])
            ->when(! empty($filters['student_id']), fn ($q) => $q->where('student_id', $filters['student_id']))
            ->when(! empty($filters['company_name']), fn ($q) => $q->where('company_name', $filters['company_name']))
            ->orderBy('start_date')
            ->paginate(20)
            ->withQueryString();

        if (! empty($filters['status'])) {
            // Status dihitung dari tanggal (bukan kolom), jadi filter
            // diterapkan pada koleksi halaman ini.
            $items->setCollection($items->getCollection()->filter(
                fn ($p) => $p->status() === $filters['status']
            )->values());
        }

        return view('admin.internships.index', [
            'filters' => $filters,
            'items' => $items,
            'companies' => $this->internships->companies(),
            'students' => Student::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $d = $request->validate([
            'student_id' => 'required|integer|exists:students,id',
            'company_name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'supervisor' => 'nullable|string|max:255',
            'grade' => 'nullable|string|max:255',
        ]);

        try {
            $this->internships->assertNoOverlap($d['student_id'], $d['start_date'], $d['end_date']);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        $item = null;

        DB::transaction(function () use ($d, $request, &$item) {
            $item = Internship::create($d);
            $this->logActivity($request, 'internship.created', Internship::class, $item->id, [
                'student_id' => $d['student_id'],
                'company_name' => $d['company_name'],
                'start_date' => $d['start_date'],
                'end_date' => $d['end_date'],
            ]);
        });

        return back()->with('success', 'Penempatan PKL disimpan.');
    }

    public function edit(Internship $internship)
    {
        $internship->load(['student', 'assessments']);

        return view('admin.internships.edit', [
            'internship' => $internship,
            'students' => Student::orderBy('name')->get(),
            'companies' => $this->internships->companies(),
        ]);
    }

    public function update(Request $request, Internship $internship)
    {
        $d = $request->validate([
            'student_id' => 'required|integer|exists:students,id',
            'company_name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'supervisor' => 'nullable|string|max:255',
            'grade' => 'nullable|string|max:255',
        ]);

        try {
            $this->internships->assertNoOverlap($d['student_id'], $d['start_date'], $d['end_date'], $internship->id);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        DB::transaction(function () use ($internship, $d, $request) {
            $internship->update($d);
            $this->logActivity($request, 'internship.updated', Internship::class, $internship->id, [
                'student_id' => $d['student_id'],
                'company_name' => $d['company_name'],
                'start_date' => $d['start_date'],
                'end_date' => $d['end_date'],
            ]);
        });

        return back()->with('success', 'Penempatan PKL diperbarui.');
    }

    public function destroy(Request $request, Internship $internship)
    {
        DB::transaction(function () use ($internship, $request) {
            $internship->delete();
            $this->logActivity($request, 'internship.deleted', Internship::class, $internship->id, [
                'student_id' => $internship->student_id,
                'company_name' => $internship->company_name,
            ]);
        });

        return back()->with('success', 'Penempatan PKL dihapus (penilaian per aspek ikut terhapus).');
    }

    /**
     * Monitoring kegiatan: penempatan aktif/berjalan, mendatang,
     * dan selesai, dikelompokkan dari tanggal (tanpa kolom periode).
     */
    public function monitoring(Request $request)
    {
        $filters = $request->validate([
            'company_name' => 'nullable|string|max:255',
            'student_id' => 'nullable|integer',
        ]);

        $query = Internship::query()->with(['student', 'assessments'])
            ->when(! empty($filters['company_name']), fn ($q) => $q->where('company_name', $filters['company_name']))
            ->when(! empty($filters['student_id']), fn ($q) => $q->where('student_id', $filters['student_id']));

        $groups = $this->internships->monitoring($query);

        return view('admin.internships.monitoring', [
            'filters' => $filters,
            'groups' => $groups,
            'companies' => $this->internships->companies(),
            'students' => Student::orderBy('name')->get(),
        ]);
    }

    /**
     * Penilaian per aspek untuk satu penempatan + form tambah aspek.
     */
    public function assessments(Internship $internship)
    {
        $internship->load(['student', 'assessments']);
        $recap = $this->internships->recapFor($internship);

        return view('admin.internships.assessments', [
            'internship' => $internship,
            'recap' => $recap,
        ]);
    }

    public function storeAssessment(Request $request, Internship $internship)
    {
        $d = $request->validate([
            'aspect' => 'required|string|max:255',
            'score' => 'nullable|numeric|min:0|max:100',
            'note' => 'nullable|string|max:2000',
        ]);

        // Satu aspek satu baris per penempatan: aspek yang sama
        // diperbarui, bukan diduplikasi.
        $existing = InternshipAssessment::query()
            ->where('internship_id', $internship->id)
            ->where('aspect', $d['aspect'])
            ->first();

        $assessment = null;

        DB::transaction(function () use ($internship, $d, $existing, $request, &$assessment) {
            if ($existing) {
                $old = $existing->score;
                $existing->update(['score' => $d['score'] ?? null, 'note' => $d['note'] ?? null]);
                $assessment = $existing;
                $this->logActivity($request, 'internship_assessment.updated', InternshipAssessment::class, $assessment->id, [
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
        $d = $request->validate([
            'aspect' => 'required|string|max:255',
            'score' => 'nullable|numeric|min:0|max:100',
            'note' => 'nullable|string|max:2000',
        ]);

        // Aspek yang sama pada penempatan yang sama tidak boleh ganda.
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
     * Rekap hasil PKL: per siswa (rata-rata gabungan + jumlah
     * penempatan selesai/berjalan) dan per penempatan.
     */
    public function recap(Request $request)
    {
        $filters = $request->validate([
            'student_id' => 'nullable|integer',
            'company_name' => 'nullable|string|max:255',
        ]);

        $studentIds = Internship::query()
            ->when(! empty($filters['student_id']), fn ($q) => $q->where('student_id', $filters['student_id']))
            ->when(! empty($filters['company_name']), fn ($q) => $q->where('company_name', $filters['company_name']))
            ->distinct()
            ->pluck('student_id');

        $recaps = $studentIds->map(fn ($id) => $this->internships->recapForStudent($id))->values()->all();
        $students = Student::query()->whereIn('id', $studentIds)->orderBy('name')->get()->keyBy('id');

        return view('admin.internships.recap', [
            'filters' => $filters,
            'recaps' => $recaps,
            'students' => $students,
            'allStudents' => Student::orderBy('name')->get(),
            'companies' => $this->internships->companies(),
        ]);
    }
}
