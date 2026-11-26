<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\HomeroomAssignment;
use App\Models\P5Score;
use App\Models\PromotionDecision;
use App\Models\ReportCard;
use App\Models\ReportCardApproval;
use App\Models\ReportCardSubject;
use App\Models\StudentAchievement;
use App\Models\StudentAttitude;
use App\Models\StudentExtracurricular;
use App\Models\SystemSetting;
use App\Models\TeacherDescription;use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Logika pemeriksaan, persetujuan, pengembalian, penerbitan,
 * penguncian, dan snapshot rapor Stage 10.
 *
 * Pemetaan status dokumen (keputusan desain Stage 10, enum tabel
 * tidak diubah): draft = Belum Lengkap / Siap Diperiksa,
 * diajukan = Diajukan, divalidasi = Disetujui,
 * diterbitkan = Diterbitkan, dikunci = Terkunci.
 * "Perlu Perbaikan" diwakili approval rejected + note alasan, dan
 * rapor kembali ke draft.
 *
 * Snapshot: saat menerbitkan, nilai akhir + deskripsi disalin ke
 * report_card_subjects; catatan sudah tersimpan di
 * report_card_notes dan dipakai apa adanya sebagai snapshot
 * catatan. PDF memakai data snapshot tersebut, bukan data live.
 * Presensi, prestasi, dan ekstrakurikuler pada PDF dibaca live
 * tetapi selalu dibatasi periode (AY + semester) rapor itu, jadi
 * data semester lain tidak pernah tercampur; tidak ada tabel
 * snapshot untuk ketiganya (lihat STAGE-10-PROGRESS.md).
 *
 * Semua perubahan status berjalan dalam transaksi dan mencatat
 * ActivityLog (report_card.approved / .rejected / .published /
 * .locked / .correction_rejected).
 */
class ReportCardService
{
    public const PUBLISHED_STATUSES = ['diterbitkan', 'dikunci'];

    public function __construct(
        private ReportCompletenessService $completeness,
        private AssessmentService $scores,
        private AttendanceService $attendance,
    ) {
    }

    // -----------------------------------------------------------------
    // Data pemeriksaan (dipakai detail admin + PDF)
    // -----------------------------------------------------------------

    /**
     * Kumpulkan seluruh komponen rapor untuk pemeriksaan: hasil
     * validasi kelengkapan, nilai akhir + deskripsi per mapel,
     * presensi, prestasi, ekstrakurikuler, catatan wali, snapshot
     * (bila sudah diterbitkan), riwayat persetujuan, dan riwayat
     * status dari ActivityLog.
     */
    public function reviewData(ReportCard $card): array
    {
        $card->loadMissing(['student', 'schoolClass', 'academicYear', 'semester', 'issuer']);

        $student = $card->student;
        $ay = $card->academic_year_id;
        $sem = $card->semester_id;

        $check = $this->completeness->check($student, $card->class_id, $ay, $sem);

        $finals = [];
        $descriptions = TeacherDescription::query()
            ->where('student_id', $student->id)
            ->where('academic_year_id', $ay)
            ->where('semester_id', $sem)
            ->get()
            ->keyBy('subject_id');

        foreach ($check['subjects'] as $subject) {
            $hasSubmitted = \App\Models\AssessmentScore::query()
                ->where('student_id', $student->id)
                ->where('subject_id', $subject->id)
                ->where('academic_year_id', $ay)
                ->where('semester_id', $sem)
                ->whereIn('status', ['terkirim', 'dikunci'])
                ->exists();

            $final = null;

            if ($hasSubmitted) {
                try {
                    $final = $this->scores->finalScore($student->id, $subject->id, $ay, $sem);
                } catch (ValidationException) {
                    $final = null;
                }
            }

            $kktp = $this->scores->kktpFor($subject->id);

            $finals[] = [
                'subject' => $subject,
                'final_score' => $final,
                'kktp' => $kktp,
                'passed' => $final === null ? null : $final >= $kktp,
                'description' => $descriptions[$subject->id]->description ?? null,
                'submitted' => $hasSubmitted,
            ];
        }

        $aggregates = $this->attendance->aggregate([
            'class_id' => $card->class_id,
            'academic_year_id' => $ay,
            'semester_id' => $sem,
        ]);
        $attendance = $aggregates[$student->id] ?? [
            'hadir' => 0, 'sakit' => 0, 'izin' => 0, 'alpa' => 0,
            'total' => 0, 'persentase_hadir' => null,
        ];

        $achievements = StudentAchievement::query()
            ->where('student_id', $student->id)
            ->where('academic_year_id', $ay)
            ->where('semester_id', $sem)
            ->orderBy('title')
            ->get();

        $extracurriculars = StudentExtracurricular::query()
            ->with('extracurricular')
            ->where('student_id', $student->id)
            ->where('academic_year_id', $ay)
            ->where('semester_id', $sem)
            ->get();

        // Fase B: sikap sosial & spiritual, dibaca live per periode
        // rapor (seperti presensi/prestasi/ekskul — bukan snapshot).
        // Bukan komponen kelengkapan: kosong berarti '-' di PDF.
        $attitudes = StudentAttitude::query()
            ->where('student_id', $student->id)
            ->where('academic_year_id', $ay)
            ->where('semester_id', $sem)
            ->get()
            ->keyBy('jenis');

        // Fase C: nilai P5, dibaca live per periode rapor (seperti
        // sikap/presensi — bukan snapshot). Bukan komponen
        // kelengkapan: kosong berarti '-' di PDF.
        $p5Scores = P5Score::query()
            ->with('project')
            ->where('student_id', $student->id)
            ->whereHas('project', fn ($q) => $q
                ->where('academic_year_id', $ay)
                ->where('semester_id', $sem))
            ->get()
            ->sortBy(fn ($s) => $s->project->tema ?? '')
            ->values();

        $homeroomNote = $card->notes()->where('note_type', 'wali_kelas')->latest()->first();
        $principalNote = $card->notes()->where('note_type', 'kepala_sekolah')->latest()->first();

        // Fase D: keputusan kenaikan kelas, dibaca live per periode
        // rapor (seperti sikap/P5 — bukan snapshot). Hanya ada pada
        // semester Genap; kosong berarti 'Belum ditetapkan' di PDF.
        $promotionDecision = PromotionDecision::query()
            ->where('student_id', $student->id)
            ->where('academic_year_id', $ay)
            ->where('semester_id', $sem)
            ->first();

        $snapshot = ReportCardSubject::query()
            ->with('subject')
            ->where('report_card_id', $card->id)
            ->join('subjects', 'subjects.id', '=', 'report_card_subjects.subject_id')
            ->orderBy('subjects.name')
            ->select('report_card_subjects.*')
            ->get();

        $approvals = $card->approvals()->with('approver')->orderByDesc('created_at')->get();

        $history = ActivityLog::query()
            ->with('user')
            ->where('model_type', ReportCard::class)
            ->where('model_id', $card->id)
            ->orderByDesc('created_at')
            ->get();

        return [
            'report_card' => $card,
            'student' => $student,
            'check' => $check,
            'finals' => $finals,
            'attendance' => $attendance,
            'achievements' => $achievements,
            'extracurriculars' => $extracurriculars,
            'attitudes' => $attitudes,
            'p5Scores' => $p5Scores,
            'promotionDecision' => $promotionDecision,
            'homeroom_note' => $homeroomNote,
            'principal_note' => $principalNote,
            'snapshot' => $snapshot,
            'approvals' => $approvals,
            'history' => $history,
        ];
    }

    /**
     * Data untuk PDF: memakai snapshot (report_card_subjects +
     * report_card_notes), bukan data live. Presensi, prestasi, dan
     * ekstrakurikuler dibaca per periode rapor (lihat catatan kelas).
     */
    public function pdfData(ReportCard $card): array
    {
        $data = $this->reviewData($card);

        $homeroom = HomeroomAssignment::query()
            ->with('teacher')
            ->where('class_id', $card->class_id)
            ->where('academic_year_id', $card->academic_year_id)
            ->where('semester_id', $card->semester_id)
            ->first();

        $schoolName = SystemSetting::query()->where('key', 'school_name')->first();

        // Fase A: data profil sekolah + pengaturan cetak untuk PDF.
        $profile = app(SchoolProfileService::class);
        $print = $profile->printSettings();
        $printDate = $print['print_date']
            ? \Carbon\Carbon::parse($print['print_date'])->locale('id')->isoFormat('D MMMM Y')
            : null;

        return array_merge($data, [
            'school_name' => $schoolName ? ($schoolName->value['value'] ?? null) : null,
            'school_npsn' => $profile->get('school_npsn'),
            'school_address' => $profile->get('school_address'),
            'school_logo_path' => $profile->logoAbsolutePath(),
            'principal_name' => $print['principal_name'],
            'principal_nip' => $print['principal_nip'],
            'print_city' => $print['print_city'],
            'print_date_formatted' => $printDate,
            'print_watermark' => $print['print_watermark'],
            'homeroom_teacher_name' => $homeroom?->teacher?->name,
            'issued_by_name' => $card->issuer?->name,
        ]);
    }

    // -----------------------------------------------------------------
    // Persetujuan / pengembalian
    // -----------------------------------------------------------------

    /**
     * Setujui rapor yang diajukan (admin saja; wali tidak boleh
     * menyetujui — ditegakkan middleware role:admin pada route).
     * Rapor harus lengkap menurut ReportCompletenessService.
     *
     * @throws ValidationException bila status bukan diajukan atau
     *         rapor tidak lengkap.
     */
    public function approve(ReportCard $card, User $admin): ReportCard
    {
        if ($card->status !== 'diajukan') {
            throw ValidationException::withMessages([
                'status' => "Rapor ini berstatus {$card->status} dan hanya rapor yang diajukan yang dapat disetujui.",
            ]);
        }

        $check = $this->completeness->check(
            $card->student, $card->class_id, $card->academic_year_id, $card->semester_id
        );

        if (! $check['complete']) {
            $messages = collect($check['missing'])->pluck('message')->implode(' ');

            throw ValidationException::withMessages([
                'completeness' => 'Rapor belum lengkap dan tidak dapat disetujui. Kekurangan: '.$messages,
            ]);
        }

        DB::transaction(function () use ($card, $admin) {
            ReportCardApproval::updateOrCreate(
                ['report_card_id' => $card->id, 'approver_id' => $admin->id],
                ['status' => 'approved', 'note' => null, 'approved_at' => now()]
            );

            $card->update(['status' => 'divalidasi']);

            $this->log($admin->id, 'report_card.approved', $card, [
                'student_id' => $card->student_id,
                'approver_id' => $admin->id,
            ]);
        });

        return $card->fresh();
    }

    /**
     * Kembalikan rapor ke draft dengan alasan wajib. Keputusan
     * tercatat sebagai approval rejected + note; status "Perlu
     * Perbaikan" diwakili kombinasi tersebut (bukan enum baru).
     *
     * @throws ValidationException bila status bukan diajukan atau
     *         alasan kosong.
     */
    public function reject(ReportCard $card, User $admin, string $reason): ReportCard
    {
        if ($card->status !== 'diajukan') {
            throw ValidationException::withMessages([
                'status' => "Rapor ini berstatus {$card->status} dan hanya rapor yang diajukan yang dapat dikembalikan.",
            ]);
        }

        if (trim($reason) === '') {
            throw ValidationException::withMessages([
                'note' => 'Alasan pengembalian wajib diisi.',
            ]);
        }

        DB::transaction(function () use ($card, $admin, $reason) {
            ReportCardApproval::updateOrCreate(
                ['report_card_id' => $card->id, 'approver_id' => $admin->id],
                ['status' => 'rejected', 'note' => trim($reason), 'approved_at' => null]
            );

            $card->update(['status' => 'draft']);

            $this->log($admin->id, 'report_card.rejected', $card, [
                'student_id' => $card->student_id,
                'approver_id' => $admin->id,
                'reason' => trim($reason),
            ]);
        });

        return $card->fresh();
    }

    // -----------------------------------------------------------------
    // Penerbitan + snapshot + kunci
    // -----------------------------------------------------------------

    /**
     * Terbitkan rapor yang sudah disetujui: salin nilai akhir +
     * deskripsi per mapel ke report_card_subjects (snapshot), isi
     * issued_at + issued_by, ubah status ke diterbitkan.
     * Terbit ganda ditolak (status sudah diterbitkan/dikunci).
     *
     * @throws ValidationException bila status bukan divalidasi atau
     *         rapor tidak lengkap.
     */
    public function publish(ReportCard $card, User $admin): ReportCard
    {
        if (in_array($card->status, self::PUBLISHED_STATUSES, true)) {
            throw ValidationException::withMessages([
                'status' => 'Rapor ini sudah diterbitkan dan tidak dapat diterbitkan ulang.',
            ]);
        }

        if ($card->status !== 'divalidasi') {
            throw ValidationException::withMessages([
                'status' => "Rapor ini berstatus {$card->status} dan hanya rapor yang disetujui yang dapat diterbitkan.",
            ]);
        }

        $check = $this->completeness->check(
            $card->student, $card->class_id, $card->academic_year_id, $card->semester_id
        );

        if (! $check['complete']) {
            $messages = collect($check['missing'])->pluck('message')->implode(' ');

            throw ValidationException::withMessages([
                'completeness' => 'Rapor belum lengkap dan tidak dapat diterbitkan. Kekurangan: '.$messages,
            ]);
        }

        DB::transaction(function () use ($card, $admin, $check) {
            foreach ($check['subjects'] as $subject) {
                $final = $this->scores->finalScore(
                    $card->student_id, $subject->id, $card->academic_year_id, $card->semester_id
                );

                $description = TeacherDescription::query()
                    ->where('student_id', $card->student_id)
                    ->where('subject_id', $subject->id)
                    ->where('academic_year_id', $card->academic_year_id)
                    ->where('semester_id', $card->semester_id)
                    ->value('description')
                    ?? ($final === null
                        ? null
                        : $this->scores->descriptionFor($final, $this->scores->kktpFor($subject->id), $subject->name));

                ReportCardSubject::updateOrCreate(
                    ['report_card_id' => $card->id, 'subject_id' => $subject->id],
                    ['final_score' => $final, 'description' => $description]
                );
            }

            $card->update([
                'status' => 'diterbitkan',
                'issued_at' => now(),
                'issued_by' => $admin->id,
            ]);

            $this->log($admin->id, 'report_card.published', $card, [
                'student_id' => $card->student_id,
                'issued_by' => $admin->id,
                'subject_count' => count($check['subjects']),
            ]);
        });

        return $card->fresh();
    }

    /**
     * Kunci rapor yang sudah diterbitkan (diterbitkan -> dikunci).
     *
     * @throws ValidationException bila status bukan diterbitkan.
     */
    public function lock(ReportCard $card, User $admin): ReportCard
    {
        if ($card->status !== 'diterbitkan') {
            throw ValidationException::withMessages([
                'status' => "Rapor ini berstatus {$card->status} dan hanya rapor yang diterbitkan yang dapat dikunci.",
            ]);
        }

        DB::transaction(function () use ($card, $admin) {
            $card->update(['status' => 'dikunci', 'locked_at' => now()]);

            $this->log($admin->id, 'report_card.locked', $card, [
                'student_id' => $card->student_id,
            ]);
        });

        return $card->fresh();
    }

    // -----------------------------------------------------------------
    // Koreksi setelah terbit
    // -----------------------------------------------------------------

    /**
     * Tolak percobaan koreksi pada rapor yang sudah diterbitkan /
     * dikunci: catat percobaan di ActivityLog lalu abort 403.
     * Dipakai endpoint yang mengubah catatan rapor (wali) — perubahan
     * nilai/deskripsi live guru tidak menyentuh snapshot dan PDF,
     * dan nilai yang sudah terkirim/dikunci sudah ditolak endpoint
     * guru lewat alur revisi Stage 7.
     */
    public function assertEditable(ReportCard $card, ?User $user, string $field): void
    {
        if (! in_array($card->status, self::PUBLISHED_STATUSES, true)) {
            return;
        }

        $this->log($user?->id, 'report_card.correction_rejected', $card, [
            'student_id' => $card->student_id,
            'field' => $field,
            'status' => $card->status,
        ]);

        abort(403, 'Rapor ini sudah diterbitkan dan tidak dapat dikoreksi tanpa prosedur resmi.');
    }

    private function log(?int $userId, string $action, ReportCard $card, array $metadata = []): void
    {
        ActivityLog::create([
            'user_id' => $userId,
            'action' => $action,
            'model_type' => ReportCard::class,
            'model_id' => $card->id,
            'metadata' => $metadata,
        ]);
    }
}
