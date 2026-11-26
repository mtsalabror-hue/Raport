<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssessmentScore extends Model
{
    protected $fillable = ['student_id', 'subject_id', 'component_id', 'class_id', 'academic_year_id', 'semester_id', 'teacher_id', 'score', 'status', 'note'];

    protected $casts = ['score' => 'decimal:2'];

    public function component()
    {
        return $this->belongsTo(AssessmentComponent::class, 'component_id');
    }

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

    public function revisions()
    {
        return $this->hasMany(ScoreRevision::class, 'assessment_score_id');
    }

    public function isLocked(): bool
    {
        return in_array($this->status, ['terkirim', 'dikunci'], true);
    }
}
