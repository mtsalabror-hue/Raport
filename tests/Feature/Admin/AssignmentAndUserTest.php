<?php

namespace Tests\Feature\Admin;

use App\Models\AcademicYear;
use App\Models\Department;
use App\Models\HomeroomAssignment;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssignmentAndUserTest extends TestCase
{
    use RefreshDatabase;

    private function seedParents(): array
    {
        $dep = Department::create(['code' => 'TKJ', 'name' => 'Teknik Komputer dan Jaringan']);
        $ay = AcademicYear::create(['name' => '2025/2026', 'start_date' => '2025-07-01', 'end_date' => '2026-06-30', 'is_active' => true]);
        $sem = Semester::create(['academic_year_id' => $ay->id, 'name' => 'Ganjil', 'is_active' => true]);
        $cls = SchoolClass::create(['department_id' => $dep->id, 'academic_year_id' => $ay->id, 'name' => 'X TKJ 1', 'level' => 'X']);
        $sub = Subject::create(['code' => 'MTK', 'name' => 'Matematika']);
        $tch = Teacher::create(['nip' => '123', 'name' => 'Guru 1']);

        return [$dep, $ay, $sem, $cls, $sub, $tch];
    }

    private function makeUser(string $roleSlug, string $email): User
    {
        $role = Role::firstOrCreate(['slug' => $roleSlug], ['name' => $roleSlug]);
        $user = User::create([
            'name' => $roleSlug, 'email' => $email, 'password' => bcrypt('password'),
            'role_id' => $role->id, 'is_active' => true,
        ]);
        $this->actingAs($user);

        return $user;
    }

    public function test_admin_can_create_teaching_assignment(): void
    {
        [$dep, $ay, $sem, $cls, $sub, $tch] = $this->seedParents();
        $this->makeUser('admin', 'admin@smk.test');

        $res = $this->get('/admin/teaching-assignments');
        $res->assertStatus(200);

        $res = $this->post('/admin/teaching-assignments', [
            'teacher_id' => $tch->id, 'subject_id' => $sub->id, 'class_id' => $cls->id,
            'academic_year_id' => $ay->id, 'semester_id' => $sem->id, 'hours' => 4,
        ]);
        $res->assertSessionHas('success');
        $this->assertDatabaseHas('teaching_assignments', [
            'teacher_id' => $tch->id, 'subject_id' => $sub->id, 'class_id' => $cls->id, 'hours' => 4,
        ]);
    }

    public function test_duplicate_teaching_assignment_rejected(): void
    {
        [$dep, $ay, $sem, $cls, $sub, $tch] = $this->seedParents();
        $this->makeUser('admin', 'admin@smk.test');
        TeachingAssignment::create([
            'teacher_id' => $tch->id, 'subject_id' => $sub->id, 'class_id' => $cls->id,
            'academic_year_id' => $ay->id, 'semester_id' => $sem->id, 'hours' => 2,
        ]);

        $res = $this->post('/admin/teaching-assignments', [
            'teacher_id' => $tch->id, 'subject_id' => $sub->id, 'class_id' => $cls->id,
            'academic_year_id' => $ay->id, 'semester_id' => $sem->id, 'hours' => 2,
        ]);
        $res->assertSessionHasErrors('teacher_id');
        $this->assertEquals(1, TeachingAssignment::count());
    }

    public function test_admin_can_create_homeroom_assignment(): void
    {
        [$dep, $ay, $sem, $cls, $sub, $tch] = $this->seedParents();
        $this->makeUser('admin', 'admin@smk.test');

        $res = $this->get('/admin/homeroom-assignments');
        $res->assertStatus(200);

        $res = $this->post('/admin/homeroom-assignments', [
            'teacher_id' => $tch->id, 'class_id' => $cls->id,
            'academic_year_id' => $ay->id, 'semester_id' => $sem->id,
        ]);
        $res->assertSessionHas('success');
        $this->assertDatabaseHas('homeroom_assignments', ['teacher_id' => $tch->id, 'class_id' => $cls->id]);
    }

    public function test_second_homeroom_for_same_class_and_period_rejected(): void
    {
        [$dep, $ay, $sem, $cls, $sub, $tch] = $this->seedParents();
        $this->makeUser('admin', 'admin@smk.test');
        $tch2 = Teacher::create(['nip' => '124', 'name' => 'Guru 2']);
        HomeroomAssignment::create([
            'teacher_id' => $tch->id, 'class_id' => $cls->id,
            'academic_year_id' => $ay->id, 'semester_id' => $sem->id,
        ]);

        $res = $this->post('/admin/homeroom-assignments', [
            'teacher_id' => $tch2->id, 'class_id' => $cls->id,
            'academic_year_id' => $ay->id, 'semester_id' => $sem->id,
        ]);
        $res->assertSessionHasErrors('class_id');
        $this->assertEquals(1, HomeroomAssignment::count());
    }

    public function test_admin_can_create_user_linked_to_teacher(): void
    {
        [$dep, $ay, $sem, $cls, $sub, $tch] = $this->seedParents();
        $this->makeUser('admin', 'admin@smk.test');
        $role = Role::firstOrCreate(['slug' => 'guru'], ['name' => 'guru']);

        $res = $this->get('/admin/users');
        $res->assertStatus(200);

        $res = $this->post('/admin/users', [
            'name' => 'Guru Baru', 'email' => 'guru@smk.test',
            'password' => 'rahasia123', 'password_confirmation' => 'rahasia123',
            'role_id' => $role->id, 'teacher_id' => $tch->id, 'is_active' => '1',
        ]);
        $res->assertSessionHas('success');
        $user = User::where('email', 'guru@smk.test')->first();
        $this->assertNotNull($user);
        $this->assertEquals($tch->id, $user->teacher_id);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('rahasia123', $user->password));
    }

    public function test_admin_cannot_delete_own_account(): void
    {
        $admin = $this->makeUser('admin', 'admin@smk.test');

        $res = $this->delete('/admin/users/'.$admin->id);
        $res->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_guru_cannot_access_assignment_and_user_routes(): void
    {
        [$dep, $ay, $sem, $cls, $sub, $tch] = $this->seedParents();
        $this->makeUser('guru', 'guru@smk.test');

        $this->get('/admin/teaching-assignments')->assertForbidden();
        $this->get('/admin/homeroom-assignments')->assertForbidden();
        $this->get('/admin/users')->assertForbidden();
    }
}
