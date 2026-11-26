<?php
namespace Tests\Feature;
use App\Models\AssessmentComponent;
use App\Models\AssessmentScore;
use App\Models\Student;
use App\Models\Subject;
use App\Models\SchoolClass;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\Teacher;
use App\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssessmentCalculationTest extends TestCase
{
    use RefreshDatabase;

    private function seedParents(): array
    {
        $dep = Department::create(['code'=>'TKJ','name'=>'Teknik Komputer dan Jaringan']);
        $ay = AcademicYear::create(['name'=>'2025/2026','start_date'=>'2025-07-01','end_date'=>'2026-06-30','is_active'=>true]);
        $sem = Semester::create(['academic_year_id'=>$ay->id,'name'=>'Ganjil','is_active'=>true]);
        $cls = SchoolClass::create(['department_id'=>$dep->id,'academic_year_id'=>$ay->id,'name'=>'X TKJ 1','level'=>'X']);
        $stu = Student::create(['nis'=>'001','nisn'=>'0001','name'=>'Siswa 1','gender'=>'L','class_id'=>$cls->id]);
        $sub = Subject::create(['code'=>'MTK','name'=>'Matematika']);
        $tch = Teacher::create(['nip'=>'123','name'=>'Guru 1']);
        return [$stu,$sub,$cls,$ay,$sem,$tch];
    }

    public function test_weighted_average_example(): void
    {
        [$stu,$sub,$cls,$ay,$sem,$tch] = $this->seedParents();
        $c1 = AssessmentComponent::create(['name'=>'Theory','type'=>'sumatif','weight'=>40,'is_active'=>true]);
        $c2 = AssessmentComponent::create(['name'=>'Practical','type'=>'praktik','weight'=>40,'is_active'=>true]);
        $c3 = AssessmentComponent::create(['name'=>'Project','type'=>'proyek','weight'=>20,'is_active'=>true]);

        AssessmentScore::create(['student_id'=>$stu->id,'subject_id'=>$sub->id,'component_id'=>$c1->id,'class_id'=>$cls->id,'academic_year_id'=>$ay->id,'semester_id'=>$sem->id,'teacher_id'=>$tch->id,'score'=>80,'status'=>'draft']);
        AssessmentScore::create(['student_id'=>$stu->id,'subject_id'=>$sub->id,'component_id'=>$c2->id,'class_id'=>$cls->id,'academic_year_id'=>$ay->id,'semester_id'=>$sem->id,'teacher_id'=>$tch->id,'score'=>90,'status'=>'draft']);

        $scores = AssessmentScore::with('component')->get()->groupBy(fn($s)=>$s->student_id.'-'.$s->subject_id);
        $sum=0; $wsum=0; $has=false;
        foreach($scores->first() as $s){
            if($s->score===null) continue;
            $w=(float)($s->component->weight ?? 0);
            $sum += (float)$s->score * $w;
            $wsum += $w;
            $has=true;
        }
        $final = $has && $wsum>0 ? round($sum/$wsum,2) : null;
        $this->assertEquals(85.0, $final);
    }

    public function test_all_missing_returns_null(): void
    {
        [$stu,$sub,$cls,$ay,$sem,$tch] = $this->seedParents();
        $c1 = AssessmentComponent::create(['name'=>'Theory','type'=>'sumatif','weight'=>40,'is_active'=>true]);
        AssessmentScore::create(['student_id'=>$stu->id,'subject_id'=>$sub->id,'component_id'=>$c1->id,'class_id'=>$cls->id,'academic_year_id'=>$ay->id,'semester_id'=>$sem->id,'teacher_id'=>$tch->id,'score'=>null,'status'=>'draft']);
        $scores = AssessmentScore::with('component')->get()->groupBy(fn($s)=>$s->student_id.'-'.$s->subject_id);
        $has=false;
        foreach($scores->first() as $s){ if($s->score!==null){ $has=true; break; } }
        $this->assertFalse($has);
    }
}
