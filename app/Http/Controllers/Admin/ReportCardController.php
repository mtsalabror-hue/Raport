<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\ReportCard;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Services\ReportCardPdfService;
use App\Services\ReportCardService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ReportCardController extends Controller
{
    public function __construct(
        private ReportCardService $cards,
        private ReportCardPdfService $pdf,
    ) {
    }

    /**
     * Daftar rapor untuk pemeriksaan, dengan filter kelas / tahun
     * ajaran / semester / status.
     */
    public function index(Request $request)
    {
        $filters = $request->validate([
            'class_id' => 'nullable|integer',
            'academic_year_id' => 'nullable|integer',
            'semester_id' => 'nullable|integer',
            'status' => 'nullable|string|in:draft,diajukan,divalidasi,diterbitkan,dikunci',
        ]);

        $cards = ReportCard::query()
            ->with(['student', 'schoolClass', 'academicYear', 'semester', 'issuer'])
            ->when(! empty($filters['class_id']), fn ($q) => $q->where('class_id', $filters['class_id']))
            ->when(! empty($filters['academic_year_id']), fn ($q) => $q->where('academic_year_id', $filters['academic_year_id']))
            ->when(! empty($filters['semester_id']), fn ($q) => $q->where('semester_id', $filters['semester_id']))
            ->when(! empty($filters['status']), fn ($q) => $q->where('status', $filters['status']))
            ->orderByDesc('updated_at')
            ->paginate(20)
            ->withQueryString();

        $counts = ReportCard::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();

        return view('admin.report_cards.index', [
            'filters' => $filters,
            'cards' => $cards,
            'counts' => $counts,
            'classes' => SchoolClass::orderBy('name')->get(),
            'academicYears' => AcademicYear::orderBy('name')->get(),
            'semesters' => Semester::orderBy('name')->get(),
        ]);
    }

    /**
     * Detail pemeriksaan satu rapor: komponen + hasil validasi
     * kelengkapan + snapshot (bila sudah diterbitkan) + riwayat.
     */
    public function show(ReportCard $reportCard)
    {
        $data = $this->cards->reviewData($reportCard);

        return view('admin.report_cards.show', array_merge($data, [
            'document_label' => $reportCard->documentLabel($data['check']['complete']),
        ]));
    }

    /**
     * Riwayat status dan persetujuan satu rapor.
     */
    public function history(ReportCard $reportCard)
    {
        $data = $this->cards->reviewData($reportCard);

        return view('admin.report_cards.history', $data);
    }

    /**
     * Setujui rapor yang diajukan (admin saja — route di bawah
     * middleware role:admin; wali tidak punya route ini).
     */
    public function approve(Request $request, ReportCard $reportCard)
    {
        try {
            $this->cards->approve($reportCard, $request->user());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('success', "Rapor {$reportCard->student->name} disetujui dan siap diterbitkan.");
    }

    /**
     * Kembalikan rapor ke draft; alasan wajib.
     */
    public function reject(Request $request, ReportCard $reportCard)
    {
        $d = $request->validate([
            'note' => 'required|string|max:2000',
        ]);

        try {
            $this->cards->reject($reportCard, $request->user(), $d['note']);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('success', "Rapor {$reportCard->student->name} dikembalikan ke wali kelas untuk diperbaiki.");
    }

    /**
     * Terbitkan rapor yang sudah disetujui (snapshot + issued_at +
     * issued_by). Terbit ganda ditolak service.
     */
    public function publish(Request $request, ReportCard $reportCard)
    {
        try {
            $this->cards->publish($reportCard, $request->user());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('success', "Rapor {$reportCard->student->name} diterbitkan.");
    }

    /**
     * Kunci rapor yang sudah diterbitkan.
     */
    public function lock(Request $request, ReportCard $reportCard)
    {
        try {
            $this->cards->lock($reportCard, $request->user());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('success', "Rapor {$reportCard->student->name} dikunci.");
    }

    /**
     * Unduh PDF rapor dari snapshot. Hanya rapor yang sudah
     * diterbitkan/dikunci yang bisa diunduh (belum ada snapshot
     * sebelumnya).
     */
    public function pdf(ReportCard $reportCard)
    {
        if (! $reportCard->isPublished()) {
            abort(403, 'Rapor ini belum diterbitkan sehingga PDF belum tersedia.');
        }

        return $this->pdf->download($reportCard);
    }
}
