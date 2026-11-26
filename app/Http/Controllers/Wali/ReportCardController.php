<?php

namespace App\Http\Controllers\Wali;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\HomeroomAssignment;
use App\Models\ReportCard;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Teacher;
use App\Services\ReportCardPdfService;
use Illuminate\Http\Request;

class ReportCardController extends Controller
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

    private function myClasses(int $teacherId)
    {
        $classIds = HomeroomAssignment::query()
            ->where('teacher_id', $teacherId)
            ->pluck('class_id')
            ->unique()
            ->values();

        return SchoolClass::query()->whereIn('id', $classIds)->orderBy('name')->get();
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

    /**
     * Status rapor kelas wali yang login (hanya-baca).
     */
    public function index(Request $request)
    {
        $filters = $request->validate([
            'class_id' => 'nullable|integer',
            'academic_year_id' => 'nullable|integer',
            'semester_id' => 'nullable|integer',
            'status' => 'nullable|string|in:draft,diajukan,divalidasi,diterbitkan,dikunci',
        ]);

        $teacher = $this->resolveTeacher($request);
        $classes = $this->myClasses($teacher->id);

        $classIds = $classes->pluck('id');

        if (! empty($filters['class_id'])) {
            if (! $classIds->contains((int) $filters['class_id'])) {
                abort(403, 'Kelas ini bukan kelas wali Anda.');
            }

            $classIds = collect([(int) $filters['class_id']]);
        }

        $cards = ReportCard::query()
            ->with(['student', 'schoolClass', 'academicYear', 'semester'])
            ->whereIn('class_id', $classIds)
            ->when(! empty($filters['academic_year_id']), fn ($q) => $q->where('academic_year_id', $filters['academic_year_id']))
            ->when(! empty($filters['semester_id']), fn ($q) => $q->where('semester_id', $filters['semester_id']))
            ->when(! empty($filters['status']), fn ($q) => $q->where('status', $filters['status']))
            ->orderByDesc('updated_at')
            ->paginate(20)
            ->withQueryString();

        return view('wali.report_cards.index', [
            'filters' => $filters,
            'cards' => $cards,
            'classes' => $classes,
            'academicYears' => AcademicYear::orderBy('name')->get(),
            'semesters' => Semester::orderBy('name')->get(),
        ]);
    }

    /**
     * Unduh PDF rapor kelas wali sendiri. Hanya rapor yang sudah
     * diterbitkan/dikunci yang bisa diunduh.
     */
    public function pdf(Request $request, ReportCard $reportCard)
    {
        $teacher = $this->resolveTeacher($request);
        $this->assertHomeroom($teacher->id, $reportCard);

        if (! $reportCard->isPublished()) {
            abort(403, 'Rapor ini belum diterbitkan sehingga PDF belum tersedia.');
        }

        return $this->pdf->download($reportCard);
    }
}
