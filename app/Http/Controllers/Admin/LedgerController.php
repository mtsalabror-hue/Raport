<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Services\LedgerService;
use App\Services\ReportCardPdfService;
use Illuminate\Http\Request;

/**
 * Leger kelas (Fase F / RDM, admin saja): tabel rekap nilai akhir
 * per siswa per mapel untuk satu kelas + semester. Halaman HTML
 * dengan tombol cetak (window.print) dan unduh PDF; sumber data
 * dipilih LedgerService (snapshot bila semua rapor terbit, nilai
 * akhir live bila belum) dan label sumbernya ditampilkan.
 */
class LedgerController extends Controller
{
    public function __construct(
        private LedgerService $ledger,
        private ReportCardPdfService $pdf,
    ) {
    }

    public function index(Request $request)
    {
        $filters = $request->validate([
            'class_id' => 'nullable|integer',
            'academic_year_id' => 'nullable|integer',
            'semester_id' => 'nullable|integer',
        ]);

        $activeYear = AcademicYear::where('is_active', true)->first();
        $activeSem = $activeYear
            ? Semester::where('academic_year_id', $activeYear->id)->where('is_active', true)->first()
            : null;

        $filters['academic_year_id'] ??= $activeYear?->id;
        $filters['semester_id'] ??= $activeSem?->id;

        $data = null;
        $class = null;

        if (! empty($filters['class_id']) && ! empty($filters['academic_year_id']) && ! empty($filters['semester_id'])) {
            $class = SchoolClass::findOrFail($filters['class_id']);
            $data = $this->ledger->ledgerData($class, $filters['academic_year_id'], $filters['semester_id']);
        }

        return view('admin.ledger.index', [
            'filters' => $filters,
            'classes' => SchoolClass::orderBy('name')->get(),
            'academicYears' => AcademicYear::orderBy('name')->get(),
            'semesters' => Semester::orderBy('name')->get(),
            'ledger' => $data,
            'class' => $class,
        ]);
    }

    public function pdf(Request $request)
    {
        $d = $request->validate([
            'class_id' => 'required|integer|exists:classes,id',
            'academic_year_id' => 'required|integer|exists:academic_years,id',
            'semester_id' => 'required|integer|exists:semesters,id',
        ]);

        $class = SchoolClass::findOrFail($d['class_id']);
        $data = $this->ledger->ledgerData($class, $d['academic_year_id'], $d['semester_id']);

        $ay = AcademicYear::find($d['academic_year_id']);
        $sem = Semester::find($d['semester_id']);
        $periodLabel = trim(($ay?->name ?? '').' '.($sem?->name ?? ''));

        return $this->pdf->ledgerDownload($class, $data, $periodLabel);
    }
}
