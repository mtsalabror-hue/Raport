<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportCard extends Model
{
    protected $fillable = [
        'student_id',
        'class_id',
        'academic_year_id',
        'semester_id',
        'status',
        'issued_at',
        'locked_at',
        'issued_by',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'locked_at' => 'datetime',
    ];

    public const STATUSES = ['draft', 'diajukan', 'divalidasi', 'diterbitkan', 'dikunci'];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class, 'semester_id');
    }

    public function issuer()
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function notes()
    {
        return $this->hasMany(ReportCardNote::class, 'report_card_id');
    }

    /**
     * Catatan wali kelas (note_type wali_kelas) pada rapor ini.
     */
    public function homeroomNote()
    {
        return $this->hasOne(ReportCardNote::class, 'report_card_id')
            ->where('note_type', 'wali_kelas')
            ->latestOfMany();
    }

    /**
     * Mata pelajaran pada rapor ini (tabel report_card_subjects).
     * Relasi baca saja; model ReportCardSubject dibuat di Stage 10.
     */
    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'report_card_subjects', 'report_card_id', 'subject_id')
            ->withPivot(['final_score', 'description'])
            ->withTimestamps();
    }

    /**
     * Baris snapshot nilai akhir + deskripsi per mapel
     * (tabel report_card_subjects, ditulis saat penerbitan).
     */
    public function reportCardSubjects()
    {
        return $this->hasMany(ReportCardSubject::class, 'report_card_id');
    }

    /**
     * Riwayat keputusan persetujuan rapor ini.
     */
    public function approvals()
    {
        return $this->hasMany(ReportCardApproval::class, 'report_card_id');
    }

    /**
     * Keputusan persetujuan terakhir (bila ada).
     */
    public function latestApproval()
    {
        return $this->hasOne(ReportCardApproval::class, 'report_card_id')
            ->latestOfMany();
    }

    /**
     * Label status dokumen (keputusan desain Stage 10, dipetakan dari
     * enum status tabel — enum tidak diubah):
     *   draft       -> Belum Lengkap, atau Siap Diperiksa bila semua
     *                  komponen lengkap ($complete = true);
     *   diajukan    -> Diajukan;
     *   divalidasi  -> Disetujui;
     *   diterbitkan -> Diterbitkan;
     *   dikunci     -> Terkunci (setelah terbit).
     * "Perlu Perbaikan" bukan status enum: diwakili approval rejected
     * + note alasan, dan rapor kembali ke draft.
     */
    public function documentLabel(?bool $complete = null): string
    {
        return match ($this->status) {
            'draft' => $complete ? 'Siap Diperiksa' : 'Belum Lengkap',
            'diajukan' => 'Diajukan',
            'divalidasi' => 'Disetujui',
            'diterbitkan' => 'Diterbitkan',
            'dikunci' => 'Terkunci',
            default => $this->status,
        };
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isSubmitted(): bool
    {
        return $this->status === 'diajukan';
    }

    public function isValidated(): bool
    {
        return $this->status === 'divalidasi';
    }

    public function isPublished(): bool
    {
        return in_array($this->status, ['diterbitkan', 'dikunci'], true);
    }

    public function isLocked(): bool
    {
        return $this->status === 'dikunci';
    }
}
