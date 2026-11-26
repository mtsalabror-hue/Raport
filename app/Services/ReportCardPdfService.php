<?php

namespace App\Services;

use App\Models\ReportCard;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

/**
 * Pembungkus kecil di atas barryvdh/laravel-dompdf untuk PDF rapor.
 * PDF selalu memakai data snapshot (ReportCardService::pdfData),
 * bukan data live. Nama file: rapor-{nis}-{semester}.pdf dengan
 * nama semester di-slug agar aman.
 */
class ReportCardPdfService
{
    public function __construct(private ReportCardService $cards)
    {
    }

    public function filename(ReportCard $card): string
    {
        $card->loadMissing(['student', 'semester']);

        $semester = Str::slug($card->semester?->name ?? (string) $card->semester_id);

        return "rapor-{$card->student->nis}-{$semester}.pdf";
    }

    public function render(ReportCard $card): string
    {
        return Pdf::loadView('admin.report_cards.pdf', $this->cards->pdfData($card))
            ->setPaper('a4', 'portrait')
            ->output();
    }

    public function download(ReportCard $card)
    {
        return Pdf::loadView('admin.report_cards.pdf', $this->cards->pdfData($card))
            ->setPaper('a4', 'portrait')
            ->download($this->filename($card));
    }
}
