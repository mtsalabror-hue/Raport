<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Fase B (RDM): sikap sosial & spiritual siswa per semester.
 * Satu baris per (siswa, tahun ajaran, semester, jenis).
 */
class StudentAttitude extends Model
{
    public const JENIS_SOSIAL = 'sosial';

    public const JENIS_SPIRITUAL = 'spiritual';

    public const JENIS = [self::JENIS_SOSIAL, self::JENIS_SPIRITUAL];

    public const JENIS_LABELS = [
        self::JENIS_SOSIAL => 'Sikap Sosial',
        self::JENIS_SPIRITUAL => 'Sikap Spiritual',
    ];

    public const PREDIKAT = ['Sangat Baik', 'Baik', 'Cukup', 'Kurang'];

    protected $fillable = [
        'student_id',
        'academic_year_id',
        'semester_id',
        'jenis',
        'predikat',
        'deskripsi',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class, 'semester_id');
    }
}
