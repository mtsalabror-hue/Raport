<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeachingAssignment extends Model
{
    protected $fillable = ['teacher_id', 'subject_id', 'class_id', 'academic_year_id', 'semester_id', 'hours'];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }
}
