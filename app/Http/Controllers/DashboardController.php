<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Teacher;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\AcademicYear;

class DashboardController extends Controller
{
    public function admin()
    {
        return view('dashboards.admin', [
            'students' => Student::count(),
            'teachers' => Teacher::count(),
            'classes' => SchoolClass::count(),
            'subjects' => Subject::count(),
            'activeYear' => AcademicYear::where('is_active', true)->first(),
        ]);
    }

    public function guru()
    {
        return view('dashboards.guru');
    }

    public function wali()
    {
        return view('dashboards.wali');
    }
}
