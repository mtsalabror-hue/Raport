<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomeroomAssignment extends Model
{
    protected $fillable = [
        'teacher_id',
        'class_id',
        'academic_year_id',
        'semester_id',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
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
}
