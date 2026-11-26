<?php
namespace Tests\Feature;
use App\Models\AssessmentComponent;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComponentCrudTest extends TestCase
{
    use RefreshDatabase;

    private function loginGuru(): void
    {
        $role = Role::create(['name'=>'Guru','slug'=>'guru']);
        $user = User::create(['name'=>'Guru','email'=>'guru@smk.test','password'=>bcrypt('password'),'role_id'=>$role->id,'is_active'=>true]);
        $this->actingAs($user);
    }

    public function test_create_component(): void
    {
        $this->loginGuru();
        $res = $this->post('/guru/components', ['name'=>'Theory','type'=>'sumatif','weight'=>40]);
        $res->assertSessionHas('success');
        $this->assertDatabaseHas('assessment_components', ['name'=>'Theory','weight'=>40]);
    }

    public function test_create_invalid_weight(): void
    {
        $this->loginGuru();
        $res = $this->post('/guru/components', ['name'=>'Bad','type'=>'sumatif','weight'=>-5]);
        $res->assertSessionHasErrors('weight');
    }

    public function test_weight_total_exceeds(): void
    {
        $this->loginGuru();
        AssessmentComponent::create(['name'=>'A','type'=>'sumatif','weight'=>60,'is_active'=>true]);
        $res = $this->post('/guru/components', ['name'=>'B','type'=>'sumatif','weight'=>60]);
        $res->assertSessionHasErrors('weight');
    }

    public function test_read_components(): void
    {
        $this->loginGuru();
        AssessmentComponent::create(['name'=>'X','type'=>'sumatif','weight'=>10,'is_active'=>true]);
        $res = $this->get('/guru/components');
        $res->assertStatus(200);
    }
}
