<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherDescription extends Model
{
    protected $fillable = [
        'student_id',
        'subject_id',
        'teacher_id',
        'academic_year_id',
        'semester_id',
        'description',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }
}
