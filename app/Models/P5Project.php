<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Fase C (RDM): proyek P5 (Projek Penguatan Profil Pelajar
 * Pancasila) per kelas per semester. Satu proyek = satu kelas +
 * satu koordinator (guru, nullable).
 */
class P5Project extends Model
{
    public const DIMENSI_BERIMAN = 'Beriman, Bertakwa kepada Tuhan YME, dan Berakhlak Mulia';

    public const DIMENSI_BERKEBINEKAAN = 'Berkebinekaan Global';

    public const DIMENSI_GOTONG_ROYONG = 'Bergotong Royong';

    public const DIMENSI_MANDIRI = 'Mandiri';

    public const DIMENSI_BERNALAR_KRITIS = 'Bernalar Kritis';

    public const DIMENSI_KREATIF = 'Kreatif';

    public const DIMENSI = [
        self::DIMENSI_BERIMAN,
        self::DIMENSI_BERKEBINEKAAN,
        self::DIMENSI_GOTONG_ROYONG,
        self::DIMENSI_MANDIRI,
        self::DIMENSI_BERNALAR_KRITIS,
        self::DIMENSI_KREATIF,
    ];

    protected $fillable = [
        'tema',
        'dimensi',
        'academic_year_id',
        'semester_id',
        'class_id',
        'coordinator_id',
    ];

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class, 'semester_id');
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function coordinator()
    {
        return $this->belongsTo(Teacher::class, 'coordinator_id');
    }

    public function scores()
    {
        return $this->hasMany(P5Score::class, 'p5_project_id');
    }
}
