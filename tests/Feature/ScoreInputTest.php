<?php
namespace Tests\Feature;
use App\Models\AssessmentComponent;
use App\Models\AssessmentScore;
use App\Models\Role;
use App\Models\User;
use App\Models\Student;
use App\Models\Subject;
use App\Models\SchoolClass;
use App\Models\AcademicYear;
use App\Models\Semester;
use App\Models\Teacher;
use App\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScoreInputTest extends TestCase
{
    use RefreshDatabase;

    private function seedAll(): array
    {
        $dep = Department::create(['code'=>'TKJ','name'=>'Teknik Komputer dan Jaringan']);
        $ay = AcademicYear::create(['name'=>'2025/2026','start_date'=>'2025-07-01','end_date'=>'2026-06-30','is_active'=>true]);
        $sem = Semester::create(['academic_year_id'=>$ay->id,'name'=>'Ganjil','is_active'=>true]);
        $cls = SchoolClass::create(['department_id'=>$dep->id,'academic_year_id'=>$ay->id,'name'=>'X TKJ 1','level'=>'X']);
        $stu = Student::create(['nis'=>'001','nisn'=>'0001','name'=>'Siswa 1','gender'=>'L','class_id'=>$cls->id]);
        $sub = Subject::create(['code'=>'MTK','name'=>'Matematika']);
        $tch = Teacher::create(['nip'=>'123','name'=>'Guru 1']);
        $role = Role::create(['name'=>'Guru','slug'=>'guru']);
        $user = User::create(['name'=>'Guru','email'=>'guru@smk.test','password'=>bcrypt('password'),'role_id'=>$role->id,'is_active'=>true]);
        $this->actingAs($user);
        return [$stu,$sub,$cls,$ay,$sem,$tch];
    }

    public function test_store_score(): void
    {
        [$stu,$sub,$cls,$ay,$sem,$tch] = $this->seedAll();
        $comp = AssessmentComponent::create(['name'=>'Theory','type'=>'sumatif','weight'=>40,'is_active'=>true]);
        $res = $this->post('/guru/assessments', ['student_id'=>$stu->id,'subject_id'=>$sub->id,'component_id'=>$comp->id,'class_id'=>$cls->id,'academic_year_id'=>$ay->id,'semester_id'=>$sem->id,'score'=>85]);
        $res->assertSessionHas('success');
        $this->assertDatabaseHas('assessment_scores', ['score'=>85]);
    }

    public function test_invalid_score_range(): void
    {
        [$stu,$sub,$cls,$ay,$sem,$tch] = $this->seedAll();
        $comp = AssessmentComponent::create(['name'=>'Theory','type'=>'sumatif','weight'=>40,'is_active'=>true]);
        $res = $this->post('/guru/assessments', ['student_id'=>$stu->id,'subject_id'=>$sub->id,'component_id'=>$comp->id,'class_id'=>$cls->id,'academic_year_id'=>$ay->id,'semester_id'=>$sem->id,'score'=>150]);
        $res->assertSessionHasErrors('score');
    }

    public function test_unauthenticated_blocked(): void
    {
        $res = $this->get('/guru/assessments');
        $res->assertRedirect('/login');
    }
}
