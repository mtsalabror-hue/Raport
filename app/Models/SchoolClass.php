<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolClass extends Model
{
    protected $table = 'classes';
    protected $fillable = ['department_id', 'academic_year_id', 'name', 'level', 'homeroom_teacher_id'];

    public function department(){ return $this->belongsTo(Department::class); }
    public function academicYear(){ return $this->belongsTo(AcademicYear::class, 'academic_year_id'); }
    public function homeroomTeacher(){ return $this->belongsTo(Teacher::class, 'homeroom_teacher_id'); }
    public function students(){ return $this->hasMany(Student::class, 'class_id'); }
}
