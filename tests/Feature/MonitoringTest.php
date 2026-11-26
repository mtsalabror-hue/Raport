<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\AssessmentComponent;
use App\Models\AssessmentScore;
use App\Models\Department;
use App\Models\ReportCard;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MonitoringTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function user(string $email): User
    {
        return User::where('email', $email)->firstOrFail();
    }

    private function seedPeriod(): array
    {
        $dep = Department::create(['code' => 'TKJ', 'name' => 'Teknik Komputer dan Jaringan']);
        $ay = AcademicYear::create(['name' => '2025/2026', 'start_date' => '2025-07-01', 'end_date' => '2026-06-30', 'is_active' => true]);
        $sem = Semester::create(['academic_year_id' => $ay->id, 'name' => 'Ganjil', 'is_active' => true]);
        $cls = SchoolClass::create(['department_id' => $dep->id, 'academic_year_id' => $ay->id, 'name' => 'X TKJ 1', 'level' => 'X']);
        $sub = Subject::create(['code' => 'MTK', 'name' => 'Matematika']);
        $tch = Teacher::create(['nip' => '123', 'name' => 'Guru Matematika']);
        $stu = Student::create(['nis' => '001', 'nisn' => 'N001', 'name' => 'Andi', 'class_id' => $cls->id, 'gender' => 'L']);

        return [$ay, $sem, $cls, $sub, $tch, $stu];
    }

    private function makePublishedCard(array $period): ReportCard
    {
        [$ay, $sem, $cls, $sub, $tch, $stu] = $period;

        return ReportCard::create([
            'student_id' => $stu->id, 'class_id' => $cls->id,
            'academic_year_id' => $ay->id, 'semester_id' => $sem->id,
            'status' => 'diterbitkan', 'issued_at' => now(),
        ]);
    }

    public function test_kepala_and_waka_login_redirects(): void
    {
        $this->post('/login', ['email' => 'kepala@smk.test', 'password' => 'password'])
            ->assertRedirect('/kepala_sekolah/dashboard');
        $this->post('/logout');

        $this->post('/login', ['email' => 'waka@smk.test', 'password' => 'password'])
            ->assertRedirect('/waka_kurikulum/dashboard');
    }

    public function test_kepala_dashboard_open_by_kepala_only(): void
    {
        $this->actingAs($this->user('kepala@smk.test'))
            ->get('/kepala_sekolah/dashboard')->assertStatus(200);

        $this->actingAs($this->user('admin@smk.test'))
            ->get('/kepala_sekolah/dashboard')->assertStatus(403);

        $this->actingAs($this->user('guru@smk.test'))
            ->get('/kepala_sekolah/dashboard')->assertStatus(403);

        $this->actingAs($this->user('waka@smk.test'))
            ->get('/kepala_sekolah/dashboard')->assertStatus(403);
    }

    public function test_waka_dashboard_open_by_waka_only(): void
    {
        $this->actingAs($this->user('waka@smk.test'))
            ->get('/waka_kurikulum/dashboard')->assertStatus(200);

        $this->actingAs($this->user('kepala@smk.test'))
            ->get('/waka_kurikulum/dashboard')->assertStatus(403);

        $this->actingAs($this->user('wali@smk.test'))
            ->get('/waka_kurikulum/dashboard')->assertStatus(403);
    }

    public function test_dashboard_shows_score_status(): void
    {
        [$ay, $sem, $cls, $sub, $tch, $stu] = $this->seedPeriod();

        TeachingAssignment::create([
            'teacher_id' => $tch->id, 'subject_id' => $sub->id, 'class_id' => $cls->id,
            'academic_year_id' => $ay->id, 'semester_id' => $sem->id,
        ]);
        $component = AssessmentComponent::create([
            'name' => 'Sumatif', 'type' => 'sumatif', 'weight' => 100,
            'subject_id' => $sub->id, 'kktp' => 75, 'is_active' => true,
        ]);
        AssessmentScore::create([
            'student_id' => $stu->id, 'subject_id' => $sub->id, 'component_id' => $component->id,
            'class_id' => $cls->id, 'academic_year_id' => $ay->id, 'semester_id' => $sem->id,
            'teacher_id' => $tch->id, 'score' => 80, 'status' => 'draft',
        ]);

        $this->actingAs($this->user('kepala@smk.test'))
            ->get('/kepala_sekolah/dashboard')
            ->assertStatus(200)
            ->assertSee('Guru Matematika')
            ->assertSee('Matematika')
            ->assertSee('Masih draft')
            ->assertSee('X TKJ 1');
    }

    public function test_kepala_can_lock_published_report_card(): void
    {
        $card = $this->makePublishedCard($this->seedPeriod());

        $this->actingAs($this->user('kepala@smk.test'))
            ->post("/kepala_sekolah/report-cards/{$card->id}/lock")
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame('dikunci', $card->fresh()->status);
        $this->assertNotNull($card->fresh()->locked_at);
    }

    public function test_kepala_cannot_lock_draft_report_card(): void
    {
        [$ay, $sem, $cls, $sub, $tch, $stu] = $this->seedPeriod();
        $card = ReportCard::create([
            'student_id' => $stu->id, 'class_id' => $cls->id,
            'academic_year_id' => $ay->id, 'semester_id' => $sem->id,
            'status' => 'draft',
        ]);

        $this->actingAs($this->user('kepala@smk.test'))
            ->post("/kepala_sekolah/report-cards/{$card->id}/lock")
            ->assertSessionHasErrors('status');

        $this->assertSame('draft', $card->fresh()->status);
    }

    public function test_waka_and_admin_cannot_lock(): void
    {
        $card = $this->makePublishedCard($this->seedPeriod());

        $this->actingAs($this->user('waka@smk.test'))
            ->post("/kepala_sekolah/report-cards/{$card->id}/lock")
            ->assertStatus(403);

        $this->actingAs($this->user('admin@smk.test'))
            ->post("/kepala_sekolah/report-cards/{$card->id}/lock")
            ->assertStatus(403);

        $this->assertSame('diterbitkan', $card->fresh()->status);
    }
}
