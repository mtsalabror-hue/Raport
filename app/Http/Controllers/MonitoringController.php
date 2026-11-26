<?php

namespace App\Http\Controllers;

use App\Models\ReportCard;
use App\Services\MonitoringService;
use App\Services\ReportCardService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class MonitoringController extends Controller
{
    public function __construct(
        private MonitoringService $monitoring,
        private ReportCardService $cards,
    ) {
    }

    /**
     * Dashboard Kepala Sekolah: pemantauan status nilai +
     * kelengkapan rapor, plus aksi kunci rapor yang diterbitkan.
     * Kepala sekolah tidak mengubah nilai (hanya kunci).
     */
    public function kepala()
    {
        return view('dashboards.kepala', $this->dashboardData(true));
    }

    /**
     * Dashboard Waka Kurikulum: pemantauan saja, tanpa aksi kunci.
     */
    public function waka()
    {
        return view('dashboards.waka', $this->dashboardData(false));
    }

    /**
     * Kunci satu rapor yang sudah diterbitkan (kepala sekolah
     * saja; route memakai middleware role:kepala_sekolah).
     * Hanya status diterbitkan yang bisa dikunci — ditegakkan
     * ReportCardService::lock.
     */
    public function lockReportCard(Request $request, ReportCard $reportCard)
    {
        try {
            $this->cards->lock($reportCard, $request->user());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('success', "Rapor {$reportCard->student->name} dikunci.");
    }

    private function dashboardData(bool $canLock): array
    {
        $period = $this->monitoring->activePeriod();
        $academicYear = $period['academicYear'];
        $semester = $period['semester'];

        $scoreStatus = collect();
        $classCompleteness = collect();
        $published = collect();
        $lockedCount = 0;

        if ($academicYear && $semester) {
            $scoreStatus = $this->monitoring->scoreStatus($academicYear->id, $semester->id);
            $classCompleteness = $this->monitoring->classCompleteness($academicYear->id, $semester->id);
            $published = $this->monitoring->publishedReportCards($academicYear->id, $semester->id);
            $lockedCount = $this->monitoring->lockedCount($academicYear->id, $semester->id);
        }

        $fullySent = $scoreStatus->filter(fn ($row) => $row['total'] > 0 && $row['draft'] === 0)->count();

        return [
            'activeYear' => $academicYear,
            'semester' => $semester,
            'scoreStatus' => $scoreStatus,
            'classCompleteness' => $classCompleteness,
            'published' => $published,
            'lockedCount' => $lockedCount,
            'fullySentCount' => $fullySent,
            'canLock' => $canLock,
        ];
    }
}
