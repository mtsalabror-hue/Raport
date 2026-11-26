<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Satu baris penilaian PKL per aspek (aspect bebas, misal
 * kedisiplinan / keterampilan / sikap — belum ada daftar aspek
 * baku dari sekolah, lihat STAGE-11-PROGRESS.md).
 */
class InternshipAssessment extends Model
{
    protected $fillable = [
        'internship_id',
        'aspect',
        'score',
        'note',
    ];

    protected $casts = [
        'score' => 'decimal:2',
    ];

    public function internship()
    {
        return $this->belongsTo(Internship::class, 'internship_id');
    }
}
