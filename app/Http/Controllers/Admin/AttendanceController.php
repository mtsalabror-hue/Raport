<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\AssessmentScore;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Services\AttendanceService;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function __construct(private AttendanceService $attendance)
    {
    }

    /**
     * Monitoring admin: (1) nilai yang belum dikirim — skor berstatus
     * draft, dikelompokkan per guru/mapel/kelas/periode; (2) rekap
     * presensi per kelas untuk satu semester eksplisit.
     */
    public function monitoring(Request $request)
    {
        $filters = $request->validate([
            'academic_year_id' => 'nullable|integer',
            'semester_id' => 'nullable|integer',
            'teacher_id' => 'nullable|integer',
        ]);

        $drafts = AssessmentScore::query()
            ->select('teacher_id', 'subject_id', 'class_id', 'academic_year_id', 'semester_id')
            ->selectRaw('COUNT(*) as draft_count')
            ->selectRaw('COUNT(DISTINCT student_id) as student_count')
            ->where('status', 'draft')
            ->when(! empty($filters['academic_year_id']), fn ($q) => $q->where('academic_year_id', $filters['academic_year_id']))
            ->when(! empty($filters['semester_id']), fn ($q) => $q->where('semester_id', $filters['semester_id']))
            ->when(! empty($filters['teacher_id']), fn ($q) => $q->where('teacher_id', $filters['teacher_id']))
            ->groupBy('teacher_id', 'subject_id', 'class_id', 'academic_year_id', 'semester_id')
            ->with(['teacher', 'subject', 'schoolClass', 'academicYear', 'semester'])
            ->orderBy('teacher_id')
            ->get();

        $attendanceByClass = [];
        $totalDrafts = (int) $drafts->sum('draft_count');

        if (! empty($filters['academic_year_id']) && ! empty($filters['semester_id'])) {
            $attendanceByClass = $this->attendance->recapPerSemester($filters['academic_year_id'], $filters['semester_id']);
        }

        return view('admin.attendance.monitoring', [
            'filters' => $filters,
            'drafts' => $drafts,
            'totalDrafts' => $totalDrafts,
            'attendanceByClass' => $attendanceByClass,
            'classes' => SchoolClass::orderBy('name')->get(),
            'academicYears' => AcademicYear::orderBy('name')->get(),
            'semesters' => Semester::orderBy('name')->get(),
            'teachers' => \App\Models\Teacher::orderBy('name')->get(),
        ]);
    }
}
