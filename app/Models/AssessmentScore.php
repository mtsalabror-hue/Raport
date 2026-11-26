<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class AssessmentScore extends Model {
  protected $fillable=['student_id','subject_id','component_id','class_id','academic_year_id','semester_id','teacher_id','score','status','note'];
  protected $casts=['score'=>'decimal:2'];
  public function component(){ return $this->belongsTo(AssessmentComponent::class,'component_id'); }
  public function student(){ return $this->belongsTo(Student::class,'student_id'); }
}
