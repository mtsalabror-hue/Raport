<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Fase D (RDM): keputusan kenaikan kelas / kelulusan siswa.
 * Satu baris per (siswa, tahun ajaran, semester), ditetapkan oleh
 * wali kelas hanya pada semester Genap.
 */
class PromotionDecision extends Model
{
    public const STATUS_NAIK = 'naik';

    public const STATUS_TIDAK_NAIK = 'tidak_naik';

    public const STATUSES = [self::STATUS_NAIK, self::STATUS_TIDAK_NAIK];

    public const LABELS = [
        self::STATUS_NAIK => 'Naik Kelas',
        self::STATUS_TIDAK_NAIK => 'Tidak Naik Kelas',
    ];

    protected $fillable = [
        'student_id',
        'class_id',
        'academic_year_id',
        'semester_id',
        'status',
        'decided_by',
    ];

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

    public function decider()
    {
        return $this->belongsTo(User::class, 'decided_by');
    }

    public function label(): string
    {
        return self::LABELS[$this->status] ?? $this->status;
    }
}
