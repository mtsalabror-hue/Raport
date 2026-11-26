<?php

namespace App\Services;

use App\Models\ReportCard;
use App\Models\SchoolClass;
use App\Models\Student;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;

/**
 * Pembungkus kecil di atas barryvdh/laravel-dompdf untuk PDF rapor.
 * PDF selalu memakai data snapshot (ReportCardService::pdfData),
 * bukan data live. Nama file: rapor-{nis}-{semester}.pdf dengan
 * nama semester di-slug agar aman.
 *
 * Fase F: sampul, identitas, transkrip, dan leger memakai pola
 * yang sama (template dompdf sederhana, tanpa Tailwind CDN).
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

    // -----------------------------------------------------------------
    // Fase F: sampul + identitas (per rapor) + transkrip + leger
    // -----------------------------------------------------------------

    public function coverFilename(ReportCard $card): string
    {
        $card->loadMissing(['student', 'semester']);

        $semester = Str::slug($card->semester?->name ?? (string) $card->semester_id);

        return "sampul-{$card->student->nis}-{$semester}.pdf";
    }

    public function coverDownload(ReportCard $card)
    {
        return Pdf::loadView('admin.report_cards.cover', $this->cards->pdfData($card))
            ->setPaper('a4', 'portrait')
            ->download($this->coverFilename($card));
    }

    public function identityFilename(ReportCard $card): string
    {
        $card->loadMissing(['student', 'semester']);

        $semester = Str::slug($card->semester?->name ?? (string) $card->semester_id);

        return "identitas-{$card->student->nis}-{$semester}.pdf";
    }

    public function identityDownload(ReportCard $card)
    {
        return Pdf::loadView('admin.report_cards.identity', $this->cards->pdfData($card))
            ->setPaper('a4', 'portrait')
            ->download($this->identityFilename($card));
    }

    public function transcriptDownload(Student $student)
    {
        $profile = app(SchoolProfileService::class);
        $print = $profile->printSettings();

        return Pdf::loadView('admin.report_cards.transcript', array_merge(
            $this->cards->transcriptData($student),
            [
                'school_name' => $profile->profile()['school_name'],
                'school_npsn' => $profile->get('school_npsn'),
                'school_address' => $profile->get('school_address'),
                'school_logo_path' => $profile->logoAbsolutePath(),
                'principal_name' => $print['principal_name'],
                'principal_nip' => $print['principal_nip'],
                'print_city' => $print['print_city'],
                'print_date_formatted' => $print['print_date']
                    ? \Carbon\Carbon::parse($print['print_date'])->locale('id')->isoFormat('D MMMM Y')
                    : null,
            ]
        ))
            ->setPaper('a4', 'portrait')
            ->download("transkrip-{$student->nis}.pdf");
    }

    public function ledgerDownload(SchoolClass $class, array $ledger, string $periodLabel)
    {
        $profile = app(SchoolProfileService::class);

        return Pdf::loadView('admin.report_cards.ledger-pdf', array_merge($ledger, [
            'period_label' => $periodLabel,
            'school_name' => $profile->profile()['school_name'],
            'school_npsn' => $profile->get('school_npsn'),
            'school_address' => $profile->get('school_address'),
        ]))
            ->setPaper('a4', 'landscape')
            ->download('leger-'.Str::slug($class->name).'-'.Str::slug($periodLabel).'.pdf');
    }
}
