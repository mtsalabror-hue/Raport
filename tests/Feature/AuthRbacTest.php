<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthRbacTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $admin = Role::create(['name' => 'Admin', 'slug' => 'admin']);
        $guru = Role::create(['name' => 'Guru', 'slug' => 'guru']);
        $wali = Role::create(['name' => 'Wali', 'slug' => 'wali_kelas']);
        User::create(['name' => 'Admin', 'email' => 'admin@smk.test', 'password' => bcrypt('password'), 'role_id' => $admin->id, 'is_active' => true]);
        User::create(['name' => 'Guru', 'email' => 'guru@smk.test', 'password' => bcrypt('password'), 'role_id' => $guru->id, 'is_active' => true]);
        User::create(['name' => 'Wali', 'email' => 'wali@smk.test', 'password' => bcrypt('password'), 'role_id' => $wali->id, 'is_active' => true]);
    }

    public function test_invalid_login(): void
    {
        $this->post('/login', ['email' => 'admin@smk.test', 'password' => 'wrong'])->assertSessionHasErrors('email');
    }

    public function test_admin_login_dashboard(): void
    {
        $this->post('/login', ['email' => 'admin@smk.test', 'password' => 'password'])->assertRedirect('/admin/dashboard');
        $this->get('/admin/dashboard')->assertStatus(200);
    }

    public function test_guru_login_dashboard(): void
    {
        $this->post('/login', ['email' => 'guru@smk.test', 'password' => 'password'])->assertRedirect('/guru/dashboard');
    }

    public function test_wali_login_dashboard(): void
    {
        $this->post('/login', ['email' => 'wali@smk.test', 'password' => 'password'])->assertRedirect('/wali/dashboard');
    }

    public function test_unauth_protected(): void
    {
        $this->get('/admin/dashboard')->assertRedirect('/login');
    }

    public function test_cross_role_forbidden(): void
    {
        $this->post('/login', ['email' => 'guru@smk.test', 'password' => 'password']);
        $this->get('/admin/dashboard')->assertStatus(403);
    }

    public function test_logout(): void
    {
        $this->post('/login', ['email' => 'admin@smk.test', 'password' => 'password']);
        $this->post('/logout')->assertRedirect('/login');
        $this->get('/admin/dashboard')->assertRedirect('/login');
    }
}
