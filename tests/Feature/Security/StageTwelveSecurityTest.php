<?php

namespace Tests\Feature\Security;

use App\Models\AcademicYear;
use App\Models\AssessmentComponent;
use App\Models\AssessmentScore;
use App\Models\Department;
use App\Models\HomeroomAssignment;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StageTwelveSecurityTest extends TestCase
{
    use RefreshDatabase;

    private function seedParents(): array
    {
        $dep = Department::create(['code' => 'TKJ', 'name' => 'Teknik Komputer dan Jaringan']);
        $ay = AcademicYear::create(['name' => '2025/2026', 'start_date' => '2025-07-01', 'end_date' => '2026-06-30', 'is_active' => true]);
        $sem = Semester::create(['academic_year_id' => $ay->id, 'name' => 'Ganjil', 'is_active' => true]);
        $cls = SchoolClass::create(['department_id' => $dep->id, 'academic_year_id' => $ay->id, 'name' => 'X TKJ 1', 'level' => 'X']);
        $stu = Student::create(['nis' => '001', 'nisn' => '0001', 'name' => 'Siswa 1', 'gender' => 'L', 'class_id' => $cls->id]);
        $sub = Subject::create(['code' => 'MTK', 'name' => 'Matematika']);
        $tch = Teacher::create(['nip' => '123', 'name' => 'Guru 1']);

        return [$stu, $sub, $cls, $ay, $sem, $tch];
    }

    private function makeUser(string $roleSlug, ?int $teacherId = null, string $email = 'user@smk.test'): User
    {
        $role = Role::firstOrCreate(['slug' => $roleSlug], ['name' => $roleSlug]);
        $user = User::create([
            'name' => $roleSlug,
            'email' => $email,
            'password' => bcrypt('password'),
            'role_id' => $role->id,
            'teacher_id' => $teacherId,
            'is_active' => true,
        ]);
        $this->actingAs($user);

        return $user;
    }

    public function test_mass_assignment_score_status_and_teacher_ignored(): void
    {
        [$stu, $sub, $cls, $ay, $sem, $tch] = $this->seedParents();
        $comp = AssessmentComponent::create(['name' => 'T', 'type' => 'sumatif', 'weight' => 100, 'is_active' => true]);
        $this->makeUser('guru', $tch->id, 'guru1@smk.test');

        $res = $this->post('/guru/assessments', [
            'student_id' => $stu->id,
            'subject_id' => $sub->id,
            'component_id' => $comp->id,
            'class_id' => $cls->id,
            'academic_year_id' => $ay->id,
            'semester_id' => $sem->id,
            'score' => 80,
            'status' => 'terkirim',
            'teacher_id' => 999,
        ]);

        $res->assertSessionHas('success');
        $score = AssessmentScore::first();
        $this->assertEquals('draft', $score->status);
        $this->assertEquals($tch->id, $score->teacher_id);
    }

    public function test_student_create_saves_gender_and_class(): void
    {
        [$stu, $sub, $cls, $ay, $sem, $tch] = $this->seedParents();
        $this->makeUser('admin', null, 'admin@smk.test');

        $res = $this->post('/admin/students', [
            'nis' => '002', 'nisn' => '0002', 'name' => 'Siswa 2',
            'gender' => 'P', 'class_id' => $cls->id,
        ]);

        $res->assertSessionHas('success');
        $this->assertDatabaseHas('students', ['nis' => '002', 'gender' => 'P', 'class_id' => $cls->id]);
    }

    public function test_teacher_create_saves_gender_phone_address(): void
    {
        $this->makeUser('admin', null, 'admin@smk.test');

        $res = $this->post('/admin/teachers', [
            'nip' => '999', 'name' => 'Guru Baru', 'gender' => 'P',
            'phone' => '081234', 'address' => 'Jl. Test 1',
        ]);

        $res->assertSessionHas('success');
        $this->assertDatabaseHas('teachers', ['nip' => '999', 'gender' => 'P', 'phone' => '081234', 'address' => 'Jl. Test 1']);
    }

    public function test_xss_in_student_name_is_escaped(): void
    {
        [$stu, $sub, $cls, $ay, $sem, $tch] = $this->seedParents();
        $stu->update(['name' => '<script>alert("xss")</script>']);
        $comp = AssessmentComponent::create(['name' => 'T', 'type' => 'sumatif', 'weight' => 100, 'is_active' => true]);
        AssessmentScore::create([
            'student_id' => $stu->id, 'subject_id' => $sub->id, 'component_id' => $comp->id,
            'class_id' => $cls->id, 'academic_year_id' => $ay->id, 'semester_id' => $sem->id,
            'teacher_id' => $tch->id, 'score' => 80, 'status' => 'draft',
        ]);

        $this->makeUser('guru', $tch->id, 'guru1@smk.test');
        $res = $this->get('/guru/assessments/recap');
        $res->assertStatus(200);
        $res->assertDontSee('<script>alert', false);
        $res->assertSee('&lt;script&gt;', false);
    }

    public function test_sql_injection_in_filter_is_rejected(): void
    {
        [$stu, $sub, $cls, $ay, $sem, $tch] = $this->seedParents();
        $this->makeUser('guru', $tch->id, 'guru1@smk.test');

        $res = $this->get('/guru/assessments/recap?subject_id=1%20OR%201=1');
        $this->assertNotEquals(500, $res->getStatusCode());
        $res->assertRedirect();
    }

    public function test_guru_cannot_store_score_for_other_teachers_assignment(): void
    {
        [$stu, $sub, $cls, $ay, $sem, $tch] = $this->seedParents();
        $tch2 = Teacher::create(['nip' => '124', 'name' => 'Guru 2']);
        TeachingAssignment::create([
            'teacher_id' => $tch2->id, 'subject_id' => $sub->id, 'class_id' => $cls->id,
            'academic_year_id' => $ay->id, 'semester_id' => $sem->id, 'hours' => 2,
        ]);
        $comp = AssessmentComponent::create(['name' => 'T', 'type' => 'sumatif', 'weight' => 100, 'is_active' => true]);

        $this->makeUser('guru', $tch->id, 'guru1@smk.test');
        $res = $this->post('/guru/assessments', [
            'student_id' => $stu->id, 'subject_id' => $sub->id, 'component_id' => $comp->id,
            'class_id' => $cls->id, 'academic_year_id' => $ay->id, 'semester_id' => $sem->id,
            'score' => 80,
        ]);

        $res->assertForbidden();
        $this->assertDatabaseMissing('assessment_scores', ['student_id' => $stu->id]);
    }

    public function test_wali_cannot_preview_student_from_other_class(): void
    {
        [$stu, $sub, $cls, $ay, $sem, $tch] = $this->seedParents();
        $cls2 = SchoolClass::create(['department_id' => $cls->department_id, 'academic_year_id' => $ay->id, 'name' => 'X TKJ 2', 'level' => 'X']);
        $stu2 = Student::create(['nis' => '002', 'nisn' => '0002', 'name' => 'Siswa 2', 'gender' => 'P', 'class_id' => $cls2->id]);
        HomeroomAssignment::create([
            'teacher_id' => $tch->id, 'class_id' => $cls->id,
            'academic_year_id' => $ay->id, 'semester_id' => $sem->id,
        ]);

        $this->makeUser('wali_kelas', $tch->id, 'wali@smk.test');
        $res = $this->get('/wali/preview/'.$stu2->id);
        $res->assertForbidden();
    }

    public function test_guru_cannot_post_to_wali_routes(): void
    {
        [$stu, $sub, $cls, $ay, $sem, $tch] = $this->seedParents();
        $this->makeUser('guru', $tch->id, 'guru1@smk.test');

        $res = $this->post('/wali/notes', ['content' => 'x']);
        $res->assertForbidden();
    }

    public function test_guest_cannot_access_pdf(): void
    {
        $res = $this->get('/admin/report-cards/1/pdf');
        $res->assertRedirect('/login');
    }

    public function test_unique_constraint_on_assessment_score(): void
    {
        [$stu, $sub, $cls, $ay, $sem, $tch] = $this->seedParents();
        $comp = AssessmentComponent::create(['name' => 'T', 'type' => 'sumatif', 'weight' => 100, 'is_active' => true]);
        $data = [
            'student_id' => $stu->id, 'subject_id' => $sub->id, 'component_id' => $comp->id,
            'class_id' => $cls->id, 'academic_year_id' => $ay->id, 'semester_id' => $sem->id,
            'teacher_id' => $tch->id, 'score' => 80, 'status' => 'draft',
        ];
        AssessmentScore::create($data);

        $this->expectException(QueryException::class);
        AssessmentScore::create($data);
    }

    public function test_foreign_key_violation_rejected(): void
    {
        $this->expectException(QueryException::class);
        AssessmentScore::create([
            'student_id' => 99999, 'subject_id' => 99999, 'component_id' => 99999,
            'class_id' => 99999, 'academic_year_id' => 99999, 'semester_id' => 99999,
            'teacher_id' => 99999, 'score' => 80, 'status' => 'draft',
        ]);
    }

    public function test_csrf_middleware_registered_on_web_group(): void
    {
        // bootstrap/app.php hanya menambah alias `role`; grup `web` diwarisi
        // dari konfigurasi default framework, yang wajib memuat CSRF.
        $groups = (new \Illuminate\Foundation\Configuration\Middleware())->getMiddlewareGroups();
        $this->assertContains(
            \Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class,
            $groups['web'] ?? []
        );
        $this->assertTrue(class_exists(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class));
    }

    public function test_login_rate_limited_after_ten_attempts(): void
    {
        $role = Role::create(['name' => 'Admin', 'slug' => 'admin']);
        User::create([
            'name' => 'Admin', 'email' => 'admin@smk.test', 'password' => bcrypt('password'),
            'role_id' => $role->id, 'is_active' => true,
        ]);

        for ($i = 0; $i < 10; $i++) {
            $res = $this->post('/login', ['email' => 'admin@smk.test', 'password' => 'salah']);
            $this->assertNotEquals(429, $res->getStatusCode());
        }

        $res = $this->post('/login', ['email' => 'admin@smk.test', 'password' => 'salah']);
        $res->assertStatus(429);
    }

    public function test_admin_classes_route_exists(): void
    {
        [$stu, $sub, $cls, $ay, $sem, $tch] = $this->seedParents();
        $this->makeUser('admin', null, 'admin@smk.test');

        $res = $this->get('/admin/classes');
        $res->assertStatus(200);

        $res = $this->post('/admin/classes', [
            'department_id' => $cls->department_id,
            'academic_year_id' => $ay->id,
            'name' => 'XI TKJ 1',
            'level' => 'XI',
        ]);
        $res->assertSessionHas('success');
        $this->assertDatabaseHas('classes', ['name' => 'XI TKJ 1', 'level' => 'XI']);
    }

    public function test_audit_log_records_submit(): void
    {
        [$stu, $sub, $cls, $ay, $sem, $tch] = $this->seedParents();
        $comp = AssessmentComponent::create(['name' => 'T', 'type' => 'sumatif', 'weight' => 100, 'is_active' => true]);
        AssessmentScore::create([
            'student_id' => $stu->id, 'subject_id' => $sub->id, 'component_id' => $comp->id,
            'class_id' => $cls->id, 'academic_year_id' => $ay->id, 'semester_id' => $sem->id,
            'teacher_id' => $tch->id, 'score' => 80, 'status' => 'draft',
        ]);

        $this->makeUser('guru', $tch->id, 'guru1@smk.test');
        $this->post('/guru/assessments/submit', []);

        $this->assertDatabaseHas('activity_logs', ['action' => 'score.submitted']);
        $log = \App\Models\ActivityLog::where('action', 'score.submitted')->first();
        $this->assertNotNull($log->user_id);
        $this->assertArrayNotHasKey('password', (array) ($log->metadata ?? []));
    }
}
