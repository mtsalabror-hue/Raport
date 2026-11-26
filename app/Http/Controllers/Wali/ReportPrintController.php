<?php

namespace App\Http\Controllers\Wali;

use App\Http\Controllers\Controller;
use App\Models\HomeroomAssignment;
use App\Models\ReportCard;
use App\Models\Teacher;
use App\Services\ReportCardPdfService;
use Illuminate\Http\Request;

/**
 * Cetak tambahan Fase F (RDM) untuk wali kelas: sampul, identitas,
 * dan transkrip per siswa. Wali hanya boleh mencetak rapor dari
 * kelas walinya sendiri (pola assertHomeroom yang sama dengan
 * Wali\ReportCardController).
 *
 * Sampul & identitas tidak memuat nilai, jadi tersedia untuk
 * semua status rapor. Transkrip memakai snapshot dan hanya ada
 * bila rapor sudah diterbitkan/dikunci.
 */
class ReportPrintController extends Controller
{
    public function __construct(private ReportCardPdfService $pdf)
    {
    }

    private function resolveTeacher(Request $request): Teacher
    {
        $user = $request->user();

        if ($user && $user->teacher_id) {
            return Teacher::findOrFail($user->teacher_id);
        }

        if (Teacher::count() === 1) {
            return Teacher::first();
        }

        abort(403, 'Akun wali kelas belum terhubung ke data guru. Minta admin menghubungkan akun ini di data pengguna.');
    }

    private function assertHomeroom(int $teacherId, ReportCard $card): void
    {
        $exists = HomeroomAssignment::query()
            ->where('teacher_id', $teacherId)
            ->where('class_id', $card->class_id)
            ->where('academic_year_id', $card->academic_year_id)
            ->where('semester_id', $card->semester_id)
            ->exists();

        if (! $exists) {
            abort(403, 'Rapor ini bukan dari kelas wali Anda.');
        }
    }

    public function cover(Request $request, ReportCard $reportCard)
    {
        $teacher = $this->resolveTeacher($request);
        $this->assertHomeroom($teacher->id, $reportCard);

        return $this->pdf->coverDownload($reportCard);
    }

    public function identity(Request $request, ReportCard $reportCard)
    {
        $teacher = $this->resolveTeacher($request);
        $this->assertHomeroom($teacher->id, $reportCard);

        return $this->pdf->identityDownload($reportCard);
    }

    public function transcript(Request $request, ReportCard $reportCard)
    {
        $teacher = $this->resolveTeacher($request);
        $this->assertHomeroom($teacher->id, $reportCard);

        if (! $reportCard->isPublished()) {
            abort(403, 'Transkrip hanya tersedia untuk rapor yang sudah diterbitkan.');
        }

        return $this->pdf->transcriptDownload($reportCard->student);
    }
}
