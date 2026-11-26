<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScoreRevision extends Model
{
    protected $fillable = ['assessment_score_id', 'requested_by', 'reason', 'status', 'decided_by', 'decided_at', 'old_score', 'new_score'];

    protected $casts = ['decided_at' => 'datetime'];

    public function score()
    {
        return $this->belongsTo(AssessmentScore::class, 'assessment_score_id');
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function decidedBy()
    {
        return $this->belongsTo(User::class, 'decided_by');
    }
}
