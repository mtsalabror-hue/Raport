<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\HomeroomAssignment;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class HomeroomAssignmentController extends Controller
{
    public function index(Request $r)
    {
        $items = HomeroomAssignment::with(['teacher', 'schoolClass', 'academicYear', 'semester'])
            ->when($r->get('class_id'), fn ($q, $v) => $q->where('class_id', $v))
            ->orderBy('class_id')
            ->paginate(15)->withQueryString();

        return view('admin.homeroom_assignments.index', [
            'items' => $items,
            'classes' => SchoolClass::orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        return view('admin.homeroom_assignments.create', $this->options());
    }

    public function store(Request $r)
    {
        $d = $this->validateData($r);
        HomeroomAssignment::create($d);

        return redirect()->route('admin.homeroom-assignments.index')->with('success', 'OK');
    }

    public function edit(HomeroomAssignment $homeroom_assignment)
    {
        return view('admin.homeroom_assignments.edit', array_merge(
            ['assignment' => $homeroom_assignment],
            $this->options()
        ));
    }

    public function update(Request $r, HomeroomAssignment $homeroom_assignment)
    {
        $d = $this->validateData($r, $homeroom_assignment->id);
        $homeroom_assignment->update($d);

        return redirect()->route('admin.homeroom-assignments.index')->with('success', 'OK');
    }

    public function destroy(HomeroomAssignment $homeroom_assignment)
    {
        $homeroom_assignment->delete();

        return redirect()->route('admin.homeroom-assignments.index')->with('success', 'OK');
    }

    private function validateData(Request $r, ?int $ignoreId = null): array
    {
        return $r->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'semester_id' => 'required|exists:semesters,id',
            // Satu kelas hanya punya satu wali kelas per tahun ajaran dan semester.
            'class_id' => [
                'required', 'exists:classes,id',
                Rule::unique('homeroom_assignments')->where(function ($q) use ($r) {
                    return $q->where('academic_year_id', $r->academic_year_id)
                        ->where('semester_id', $r->semester_id);
                })->ignore($ignoreId),
            ],
        ]);
    }

    private function options(): array
    {
        return [
            'teachers' => Teacher::orderBy('name')->get(),
            'classes' => SchoolClass::orderBy('name')->get(),
            'years' => AcademicYear::orderByDesc('start_date')->get(),
            'semesters' => Semester::orderBy('name')->get(),
        ];
    }
}
