<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\P5Project;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Fase C (RDM): CRUD proyek P5 (admin saja).
 * Satu proyek = satu kelas + satu koordinator (guru, nullable).
 */
class P5ProjectController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'class_id' => 'nullable|integer',
            'academic_year_id' => 'nullable|integer',
            'semester_id' => 'nullable|integer',
        ]);

        $items = P5Project::query()
            ->with(['schoolClass', 'academicYear', 'semester', 'coordinator'])
            ->when(! empty($filters['class_id']), fn ($q) => $q->where('class_id', $filters['class_id']))
            ->when(! empty($filters['academic_year_id']), fn ($q) => $q->where('academic_year_id', $filters['academic_year_id']))
            ->when(! empty($filters['semester_id']), fn ($q) => $q->where('semester_id', $filters['semester_id']))
            ->orderByDesc('updated_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.p5.index', [
            'filters' => $filters,
            'items' => $items,
            'classes' => SchoolClass::orderBy('name')->get(),
            'academicYears' => AcademicYear::orderBy('name')->get(),
            'semesters' => Semester::orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        return view('admin.p5.create', $this->formLists());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tema' => 'required|string|max:255',
            'dimensi' => ['required', 'string', Rule::in(P5Project::DIMENSI)],
            'academic_year_id' => 'required|integer|exists:academic_years,id',
            'semester_id' => 'required|integer|exists:semesters,id',
            'class_id' => 'required|integer|exists:classes,id',
            'coordinator_id' => 'nullable|integer|exists:teachers,id',
        ]);

        P5Project::create($data);

        return redirect()->route('admin.p5-projects.index')->with('success', 'Proyek P5 berhasil ditambahkan.');
    }

    public function edit(P5Project $p5Project)
    {
        return view('admin.p5.edit', array_merge(
            $this->formLists(),
            ['project' => $p5Project]
        ));
    }

    public function update(Request $request, P5Project $p5Project)
    {
        $data = $request->validate([
            'tema' => 'required|string|max:255',
            'dimensi' => ['required', 'string', Rule::in(P5Project::DIMENSI)],
            'academic_year_id' => 'required|integer|exists:academic_years,id',
            'semester_id' => 'required|integer|exists:semesters,id',
            'class_id' => 'required|integer|exists:classes,id',
            'coordinator_id' => 'nullable|integer|exists:teachers,id',
        ]);

        $p5Project->update($data);

        return redirect()->route('admin.p5-projects.index')->with('success', 'Proyek P5 diperbarui.');
    }

    public function destroy(P5Project $p5Project)
    {
        if ($p5Project->scores()->exists()) {
            return back()->withErrors([
                'project' => "Proyek P5 {$p5Project->tema} sudah memiliki capaian siswa dan tidak dapat dihapus.",
            ]);
        }

        $p5Project->delete();

        return redirect()->route('admin.p5-projects.index')->with('success', 'Proyek P5 dihapus.');
    }

    private function formLists(): array
    {
        return [
            'classes' => SchoolClass::orderBy('name')->get(),
            'academicYears' => AcademicYear::orderBy('name')->get(),
            'semesters' => Semester::orderBy('name')->get(),
            'teachers' => Teacher::orderBy('name')->get(),
            'dimensiOptions' => P5Project::DIMENSI,
        ];
    }
}
