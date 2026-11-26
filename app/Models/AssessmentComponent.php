<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssessmentComponent extends Model
{
    protected $fillable = ['name', 'type', 'weight', 'subject_id', 'curriculum_id', 'kktp', 'is_active'];

    protected $casts = [
        'weight' => 'decimal:2',
        'kktp' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function curriculum()
    {
        return $this->belongsTo(Curriculum::class, 'curriculum_id');
    }

    public function scores()
    {
        return $this->hasMany(AssessmentScore::class, 'component_id');
    }
}
