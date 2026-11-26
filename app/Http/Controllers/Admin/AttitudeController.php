<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentAttitude;
use Illuminate\Http\Request;

/**
 * Fase B (RDM): rekap sikap sosial & spiritual per kelas,
 * hanya-baca untuk admin.
 */
class AttitudeController extends Controller
{
    public function recap(Request $request)
    {
        $filters = $request->validate([
            'class_id' => 'nullable|integer',
            'academic_year_id' => 'nullable|integer',
            'semester_id' => 'nullable|integer',
        ]);

        $students = collect();
        $attitudes = collect();

        if (! empty($filters['class_id']) && ! empty($filters['academic_year_id']) && ! empty($filters['semester_id'])) {
            $students = Student::query()
                ->where('class_id', $filters['class_id'])
                ->orderBy('name')
                ->get();

            $attitudes = StudentAttitude::query()
                ->whereIn('student_id', $students->pluck('id'))
                ->where('academic_year_id', $filters['academic_year_id'])
                ->where('semester_id', $filters['semester_id'])
                ->get()
                ->groupBy('student_id')
                ->map(fn ($rows) => $rows->keyBy('jenis'));
        }

        return view('admin.attitudes.recap', [
            'filters' => $filters,
            'classes' => SchoolClass::orderBy('name')->get(),
            'academicYears' => AcademicYear::orderBy('name')->get(),
            'semesters' => Semester::orderBy('name')->get(),
            'students' => $students,
            'attitudes' => $attitudes,
            'jenisLabels' => StudentAttitude::JENIS_LABELS,
        ]);
    }
}
