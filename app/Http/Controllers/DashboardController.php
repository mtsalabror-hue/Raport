<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\AcademicYear;
use App\Services\MonitoringService;

class DashboardController extends Controller
{
    public function __construct(private MonitoringService $monitoring)
    {
    }

    public function admin()
    {
        // Fase F: dua panel tambahan dari data asli — status
        // pengiriman nilai (MonitoringService Fase A, diagregasi
        // per mapel) dan aktivitas guru terakhir (activity_logs).
        $scoreBySubject = collect();
        $activeSemester = null;

        $period = $this->monitoring->activePeriod();
        $academicYear = $period['academicYear'];
        $activeSemester = $period['semester'];

        if ($academicYear && $activeSemester) {
            $rows = $this->monitoring->scoreStatus($academicYear->id, $activeSemester->id);

            $scoreBySubject = $rows
                ->groupBy(fn ($row) => $row['subject']?->name ?? '-')
                ->map(fn ($group, $subjectName) => [
                    'subject' => $subjectName,
                    'draft' => $group->sum('draft'),
                    'terkirim' => $group->sum('terkirim'),
                    'dikunci' => $group->sum('dikunci'),
                    'total' => $group->sum('total'),
                ])
                ->sortKeys()
                ->values();
        }

        $recentActivities = ActivityLog::query()
            ->with('user')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('dashboards.admin', [
            'students' => Student::count(),
            'teachers' => Teacher::count(),
            'classes' => SchoolClass::count(),
            'subjects' => Subject::count(),
            'activeYear' => AcademicYear::where('is_active', true)->first(),
            'scoreBySubject' => $scoreBySubject,
            'activeSemester' => $activeSemester,
            'recentActivities' => $recentActivities,
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
