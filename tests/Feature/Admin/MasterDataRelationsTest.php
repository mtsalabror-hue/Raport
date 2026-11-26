<?php

namespace Tests\Feature\Admin;

use App\Models\AcademicYear;
use App\Models\Role;
use App\Models\Semester;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterDataRelationsTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(): void
    {
        $role = Role::firstOrCreate(['slug' => 'admin'], ['name' => 'admin']);
        $user = User::create([
            'name' => 'admin', 'email' => 'admin@smk.test', 'password' => bcrypt('password'),
            'role_id' => $role->id, 'is_active' => true,
        ]);
        $this->actingAs($user);
    }

    public function test_semester_index_and_create_pages_render_with_academic_year(): void
    {
        $ay = AcademicYear::create([
            'name' => '2025/2026', 'start_date' => '2025-07-01', 'end_date' => '2026-06-30', 'is_active' => true,
        ]);
        $sem = Semester::create(['academic_year_id' => $ay->id, 'name' => 'Ganjil', 'is_active' => true]);

        // Relasi academicYear harus ada (dulu RelationNotFoundException).
        $this->assertSame('2025/2026', $sem->fresh()->academicYear->name);

        $this->makeAdmin();

        $this->get('/admin/semesters')->assertStatus(200)->assertSee('2025/2026');
        $this->get('/admin/semesters/create')->assertStatus(200)->assertSee('2025/2026');
        $this->get('/admin/classes')->assertStatus(200);
        $this->get('/admin/curricula')->assertStatus(200);
    }
}
