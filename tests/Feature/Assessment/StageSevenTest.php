<?php

namespace Tests\Feature\Assessment;

use App\Models\AcademicYear;
use App\Models\AssessmentComponent;
use App\Models\AssessmentScore;
use App\Models\ActivityLog;
use App\Models\Department;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use App\Services\AssessmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class StageSevenTest extends TestCase
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

    private function loginGuru(?int $teacherId = null): User
    {
        $role = Role::firstOrCreate(['slug' => 'guru'], ['name' => 'Guru']);
        $user = User::create([
            'name' => 'Guru',
            'email' => 'guru'.($teacherId ?? 1).'@smk.test',
            'password' => bcrypt('password'),
            'role_id' => $role->id,
            'teacher_id' => $teacherId,
            'is_active' => true,
        ]);
        $this->actingAs($user);

        return $user;
    }

    private function loginWali(): User
    {
        $role = Role::firstOrCreate(['slug' => 'wali_kelas'], ['name' => 'Wali Kelas']);
        $user = User::create([
            'name' => 'Wali',
            'email' => 'wali@smk.test',
            'password' => bcrypt('password'),
            'role_id' => $role->id,
            'is_active' => true,
        ]);
        $this->actingAs($user);

        return $user;
    }

    private function seedComponents(array $weights): array
    {
        $types = ['formatif', 'sumatif', 'praktik', 'proyek'];
        $components = [];
        foreach ($weights as $i => $weight) {
            $components[] = AssessmentComponent::create([
                'name' => 'Komponen '.($i + 1),
                'type' => $types[$i % 4],
                'weight' => $weight,
                'is_active' => true,
            ]);
        }

        return $components;
    }

    private function seedScore($stu, $sub, $cls, $ay, $sem, $tch, $component, $score, $status = 'draft'): AssessmentScore
    {
        return AssessmentScore::create([
            'student_id' => $stu->id,
            'subject_id' => $sub->id,
            'component_id' => $component->id,
            'class_id' => $cls->id,
            'academic_year_id' => $ay->id,
            'semester_id' => $sem->id,
            'teacher_id' => $tch->id,
            'score' => $score,
            'status' => $status,
        ]);
    }

    public function test_final_score_draft_example(): void
    {
        [$stu, $sub, $cls, $ay, $sem, $tch] = $this->seedParents();
        $components = $this->seedComponents([30, 40, 20, 10]);
        $scores = [80, 75, 90, 85];

        foreach ($components as $i => $component) {
            $this->seedScore($stu, $sub, $cls, $ay, $sem, $tch, $component, $scores[$i]);
        }

        $service = app(AssessmentService::class);
        $this->assertEquals(80.50, $service->finalScore($stu->id, $sub->id, $ay->id, $sem->id));
    }

    public function test_final_score_excludes_missing_component_from_denominator(): void
    {
        [$stu, $sub, $cls, $ay, $sem, $tch] = $this->seedParents();
        $components = $this->seedComponents([30, 40, 20, 10]);

        $this->seedScore($stu, $sub, $cls, $ay, $sem, $tch, $components[0], 80);
        $this->seedScore($stu, $sub, $cls, $ay, $sem, $tch, $components[1], 75);
        $this->seedScore($stu, $sub, $cls, $ay, $sem, $tch, $components[2], 90);

        $service = app(AssessmentService::class);
        $this->assertEquals(80.00, $service->finalScore($stu->id, $sub->id, $ay->id, $sem->id));
    }

    public function test_final_score_null_when_no_scores(): void
    {
        [$stu, $sub, $cls, $ay, $sem, $tch] = $this->seedParents();
        $components = $this->seedComponents([40, 60]);
        $this->seedScore($stu, $sub, $cls, $ay, $sem, $tch, $components[0], null);

        $service = app(AssessmentService::class);
        $this->assertNull($service->finalScore($stu->id, $sub->id, $ay->id, $sem->id));
    }

    public function test_final_score_rejects_invalid_weight_total(): void
    {
        [$stu, $sub, $cls, $ay, $sem, $tch] = $this->seedParents();
        $components = $this->seedComponents([40, 40]);
        $this->seedScore($stu, $sub, $cls, $ay, $sem, $tch, $components[0], 80);

        $service = app(AssessmentService::class);

        $this->expectException(ValidationException::class);
        $service->finalScore($stu->id, $sub->id, $ay->id, $sem->id);
    }

    public function test_kktp_description_generation(): void
    {
        $service = app(AssessmentService::class);

        $this->assertEquals(75.0, $service->kktpFor(null));
        $this->assertStringContainsString('Mencapai KKTP', $service->descriptionFor(80.5, 75, 'Matematika'));
        $this->assertStringContainsString('Belum mencapai KKTP', $service->descriptionFor(60.0, 75, 'Matematika'));
    }

    public function test_submit_only_own_drafts(): void
    {
        [$stu, $sub, $cls, $ay, $sem, $tch] = $this->seedParents();
        $stu2 = Student::create(['nis' => '002', 'nisn' => '0002', 'name' => 'Siswa 2', 'gender' => 'P', 'class_id' => $cls->id]);
        $tch2 = Teacher::create(['nip' => '124', 'name' => 'Guru 2']);
        $components = $this->seedComponents([100]);

        $mine = $this->seedScore($stu, $sub, $cls, $ay, $sem, $tch, $components[0], 80, 'draft');
        $theirs = $this->seedScore($stu2, $sub, $cls, $ay, $sem, $tch2, $components[0], 70, 'draft');

        $this->loginGuru($tch->id);
        $res = $this->post('/guru/assessments/submit', []);
        $res->assertSessionHas('success');

        $this->assertEquals('terkirim', $mine->fresh()->status);
        $this->assertEquals('draft', $theirs->fresh()->status);
    }

    public function test_locked_score_cannot_be_edited_directly(): void
    {
        [$stu, $sub, $cls, $ay, $sem, $tch] = $this->seedParents();
        $components = $this->seedComponents([100]);
        $score = $this->seedScore($stu, $sub, $cls, $ay, $sem, $tch, $components[0], 80, 'terkirim');

        $this->loginGuru($tch->id);
        $res = $this->post('/guru/assessments', [
            'student_id' => $stu->id,
            'subject_id' => $sub->id,
            'component_id' => $components[0]->id,
            'class_id' => $cls->id,
            'academic_year_id' => $ay->id,
            'semester_id' => $sem->id,
            'score' => 90,
        ]);

        $res->assertSessionHasErrors('score');
        $this->assertEquals(80, (float) $score->fresh()->score);
    }

    public function test_revision_flow_approve_and_reject(): void
    {
        [$stu, $sub, $cls, $ay, $sem, $tch] = $this->seedParents();
        $components = $this->seedComponents([100]);
        $score = $this->seedScore($stu, $sub, $cls, $ay, $sem, $tch, $components[0], 80, 'terkirim');

        $requester = $this->loginGuru($tch->id);
        $res = $this->post('/guru/assessments/revision', [
            'assessment_score_id' => $score->id,
            'reason' => 'Salah input',
            'new_score' => 85,
        ]);
        $res->assertSessionHas('success');
        $this->assertEquals('perlu_revisi', $score->fresh()->status);
        $this->assertDatabaseHas('score_revisions', ['assessment_score_id' => $score->id, 'status' => 'diajukan']);

        $revisionId = \App\Models\ScoreRevision::first()->id;

        // Pengaju tidak boleh menyetujui revisinya sendiri.
        $res = $this->post('/guru/assessments/revision/approve', [
            'revision_id' => $revisionId,
            'decision' => 'disetujui',
        ]);
        $res->assertForbidden();

        // Guru lain menyetujui.
        $tch2 = Teacher::create(['nip' => '125', 'name' => 'Guru 2']);
        $this->loginGuru($tch2->id);
        $res = $this->post('/guru/assessments/revision/approve', [
            'revision_id' => $revisionId,
            'decision' => 'disetujui',
        ]);
        $res->assertSessionHas('success');
        $this->assertEquals(85, (float) $score->fresh()->score);
        $this->assertEquals('terkirim', $score->fresh()->status);
    }

    public function test_bulk_store_and_conflict_detection(): void
    {
        [$stu, $sub, $cls, $ay, $sem, $tch] = $this->seedParents();
        $components = $this->seedComponents([60, 40]);

        $this->loginGuru($tch->id);
        $res = $this->post('/guru/assessments/bulk', [
            'subject_id' => $sub->id,
            'class_id' => $cls->id,
            'academic_year_id' => $ay->id,
            'semester_id' => $sem->id,
            'loaded_at' => now()->toIso8601String(),
            'scores' => [
                ['student_id' => $stu->id, 'component_id' => $components[0]->id, 'score' => 80],
                ['student_id' => $stu->id, 'component_id' => $components[1]->id, 'score' => 90],
            ],
        ]);
        $res->assertSessionHas('success');
        $this->assertDatabaseHas('assessment_scores', ['student_id' => $stu->id, 'score' => 80]);
        $this->assertDatabaseHas('assessment_scores', ['student_id' => $stu->id, 'score' => 90]);

        // Data berubah setelah halaman dimuat -> konflik.
        $res = $this->post('/guru/assessments/bulk', [
            'subject_id' => $sub->id,
            'class_id' => $cls->id,
            'academic_year_id' => $ay->id,
            'semester_id' => $sem->id,
            'loaded_at' => now()->subHour()->toIso8601String(),
            'scores' => [
                ['student_id' => $stu->id, 'component_id' => $components[0]->id, 'score' => 50],
            ],
        ]);
        $res->assertSessionHasErrors('scores');
        $this->assertDatabaseMissing('assessment_scores', ['student_id' => $stu->id, 'score' => 50]);
    }

    public function test_audit_log_written_on_score_create(): void
    {
        [$stu, $sub, $cls, $ay, $sem, $tch] = $this->seedParents();
        $components = $this->seedComponents([100]);

        $this->loginGuru($tch->id);
        $this->post('/guru/assessments', [
            'student_id' => $stu->id,
            'subject_id' => $sub->id,
            'component_id' => $components[0]->id,
            'class_id' => $cls->id,
            'academic_year_id' => $ay->id,
            'semester_id' => $sem->id,
            'score' => 88,
        ]);

        $this->assertDatabaseHas('activity_logs', ['action' => 'score.created']);
    }

    public function test_wali_recap_read_only_submitted_only(): void
    {
        [$stu, $sub, $cls, $ay, $sem, $tch] = $this->seedParents();
        $stu2 = Student::create(['nis' => '002', 'nisn' => '0002', 'name' => 'Siswa 2', 'gender' => 'P', 'class_id' => $cls->id]);
        $components = $this->seedComponents([100]);
        $this->seedScore($stu, $sub, $cls, $ay, $sem, $tch, $components[0], 80, 'draft');
        $this->seedScore($stu2, $sub, $cls, $ay, $sem, $tch, $components[0], 90, 'terkirim');

        $this->loginWali();
        $res = $this->get('/wali/assessments/recap?subject_id='.$sub->id.'&academic_year_id='.$ay->id.'&semester_id='.$sem->id);
        $res->assertStatus(200);
        $res->assertViewHas('scores', function ($scores) {
            return $scores->count() === 1 && $scores->first()->status === 'terkirim';
        });
        $res->assertViewHas('readOnly', true);
    }

    public function test_component_weight_scoped_per_subject(): void
    {
        [$stu, $sub, $cls, $ay, $sem, $tch] = $this->seedParents();
        $sub2 = Subject::create(['code' => 'BIN', 'name' => 'Bahasa Indonesia']);

        $this->loginGuru($tch->id);

        // Mapel MTK sudah 100, tambah lagi untuk MTK -> ditolak.
        AssessmentComponent::create(['name' => 'A', 'type' => 'sumatif', 'weight' => 100, 'subject_id' => $sub->id, 'is_active' => true]);
        $res = $this->post('/guru/components', [
            'name' => 'B', 'type' => 'sumatif', 'weight' => 10, 'subject_id' => $sub->id,
        ]);
        $res->assertSessionHasErrors('weight');

        // Mapel lain masih boleh.
        $res = $this->post('/guru/components', [
            'name' => 'C', 'type' => 'sumatif', 'weight' => 10, 'subject_id' => $sub2->id,
        ]);
        $res->assertSessionHas('success');
    }

    public function test_auto_description_from_final_score(): void
    {
        [$stu, $sub, $cls, $ay, $sem, $tch] = $this->seedParents();
        $components = $this->seedComponents([100]);
        $this->seedScore($stu, $sub, $cls, $ay, $sem, $tch, $components[0], 80, 'draft');

        $this->loginGuru($tch->id);
        $res = $this->post('/guru/descriptions', [
            'student_id' => $stu->id,
            'subject_id' => $sub->id,
            'academic_year_id' => $ay->id,
            'semester_id' => $sem->id,
            'description' => '',
        ]);
        $res->assertSessionHas('success');
        $this->assertDatabaseHas('teacher_descriptions', ['student_id' => $stu->id]);
    }

    public function test_history_page_returns_200_for_guru(): void
    {
        [$stu, $sub, $cls, $ay, $sem, $tch] = $this->seedParents();
        $this->loginGuru($tch->id);
        $res = $this->get('/guru/assessments/history');
        $res->assertStatus(200);
    }
}
