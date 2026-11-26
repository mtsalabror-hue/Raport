<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportCardSubject extends Model
{
    protected $fillable = [
        'report_card_id',
        'subject_id',
        'final_score',
        'description',
    ];

    protected $casts = [
        'final_score' => 'decimal:2',
    ];

    public function reportCard()
    {
        return $this->belongsTo(ReportCard::class, 'report_card_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }
}
