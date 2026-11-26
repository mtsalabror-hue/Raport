<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentExtracurricular extends Model
{
    protected $fillable = [
        'student_id',
        'extracurricular_id',
        'academic_year_id',
        'semester_id',
        'score',
        'description',
    ];

    protected $casts = [
        'score' => 'decimal:2',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function extracurricular()
    {
        return $this->belongsTo(Extracurricular::class, 'extracurricular_id');
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
