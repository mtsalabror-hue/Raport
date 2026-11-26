<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportCardNote extends Model
{
    protected $fillable = [
        'report_card_id',
        'note_type',
        'content',
    ];

    public const TYPES = ['wali_kelas', 'kepala_sekolah'];

    public function reportCard()
    {
        return $this->belongsTo(ReportCard::class, 'report_card_id');
    }
}
