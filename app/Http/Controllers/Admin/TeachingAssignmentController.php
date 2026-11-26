<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingAssignment;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TeachingAssignmentController extends Controller
{
    public function index(Request $r)
    {
        $items = TeachingAssignment::with(['teacher', 'subject', 'schoolClass'])
            ->when($r->get('teacher_id'), fn ($q, $v) => $q->where('teacher_id', $v))
            ->when($r->get('class_id'), fn ($q, $v) => $q->where('class_id', $v))
            ->orderBy('teacher_id')->orderBy('class_id')
            ->paginate(15)->withQueryString();

        return view('admin.teaching_assignments.index', [
            'items' => $items,
            'teachers' => Teacher::orderBy('name')->get(),
            'classes' => SchoolClass::orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        return view('admin.teaching_assignments.create', $this->options());
    }

    public function store(Request $r)
    {
        $d = $this->validateData($r);
        TeachingAssignment::create($d);

        return redirect()->route('admin.teaching-assignments.index')->with('success', 'OK');
    }

    public function edit(TeachingAssignment $teaching_assignment)
    {
        return view('admin.teaching_assignments.edit', array_merge(
            ['assignment' => $teaching_assignment],
            $this->options()
        ));
    }

    public function update(Request $r, TeachingAssignment $teaching_assignment)
    {
        $d = $this->validateData($r, $teaching_assignment->id);
        $teaching_assignment->update($d);

        return redirect()->route('admin.teaching-assignments.index')->with('success', 'OK');
    }

    public function destroy(TeachingAssignment $teaching_assignment)
    {
        $teaching_assignment->delete();

        return redirect()->route('admin.teaching-assignments.index')->with('success', 'OK');
    }

    private function validateData(Request $r, ?int $ignoreId = null): array
    {
        return $r->validate([
            'subject_id' => 'required|exists:subjects,id',
            'class_id' => 'required|exists:classes,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'semester_id' => 'required|exists:semesters,id',
            'hours' => 'nullable|integer|min:0|max:40',
            // Satu guru tidak boleh punya dua penugasan yang sama.
            'teacher_id' => [
                'required', 'exists:teachers,id',
                Rule::unique('teaching_assignments')->where(function ($q) use ($r) {
                    return $q->where('subject_id', $r->subject_id)
                        ->where('class_id', $r->class_id)
                        ->where('academic_year_id', $r->academic_year_id)
                        ->where('semester_id', $r->semester_id);
                })->ignore($ignoreId),
            ],
        ]);
    }

    private function options(): array
    {
        return [
            'teachers' => Teacher::orderBy('name')->get(),
            'subjects' => Subject::orderBy('name')->get(),
            'classes' => SchoolClass::orderBy('name')->get(),
            'years' => AcademicYear::orderByDesc('start_date')->get(),
            'semesters' => Semester::orderBy('name')->get(),
        ];
    }
}
