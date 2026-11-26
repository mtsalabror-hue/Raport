<?php

namespace Tests\Feature\ReportCard;

use App\Models\AcademicYear;
use App\Models\ActivityLog;
use App\Models\AssessmentComponent;
use App\Models\AssessmentScore;
use App\Models\Department;
use App\Models\Extracurricular;
use App\Models\HomeroomAssignment;
use App\Models\ReportCard;
use App\Models\ReportCardSubject;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentExtracurricular;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Fase F (RDM): cetak tambahan (sampul/identitas/leger/transkrip),
 * pembina ekstrakurikuler, profil guru, dan panel dashboard admin.
 */
class PhaseFTest extends TestCase
{
    use RefreshDatabase;

    private static int $userSeq = 0;

    private function seedParents(): array
    {
        $dep = Department::create(['code' => 'TKJ', 'name' => 'Teknik Komputer dan Jaringan']);
        $ay = AcademicYear::create(['name' => '2025/2026', 'start_date' => '2025-07-01', 'end_date' => '2026-06-30', 'is_active' => true]);
        $sem1 = Semester::create(['academic_year_id' => $ay->id, 'name' => 'Ganjil', 'is_active' => true]);
        $sem2 = Semester::create(['academic_year_id' => $ay->id, 'name' => 'Genap', 'is_active' => false]);
        $cls = SchoolClass::create(['department_id' => $dep->id, 'academic_year_id' => $ay->id, 'name' => 'X TKJ 1', 'level' => 'X']);
        $sub = Subject::create(['code' => 'MTK', 'name' => 'Matematika']);
        $wali = Teacher::create(['nip' => '111', 'name' => 'Wali Satu']);
        $guru = Teacher::create(['nip' => '222', 'name' => 'Guru Dua']);

        return [$dep, $ay, $sem1, $sem2, $cls, $sub, $wali, $guru];
    }

    private function makeStudent(string $nis, string $name, int $classId): Student
    {
        $stu = Student::create(['nis' => $nis, 'nisn' => 'N'.$nis, 'name' => $name]);
        $stu->class_id = $classId;
        $stu->gender = 'L';
        $stu->birth_date = '2010-05-04';
        $stu->address = 'Jl. Merdeka No. 1';
        $stu->save();

        return $stu->fresh();
    }

    private function loginAs(string $roleSlug, ?int $teacherId = null): User
    {
        $role = Role::firstOrCreate(['slug' => $roleSlug], ['name' => $roleSlug]);
        self::$userSeq++;
        $user = User::create([
            'name' => ucfirst($roleSlug).' '.self::$userSeq,
            'email' => $roleSlug.self::$userSeq.'@smk.test',
            'password' => bcrypt('password'),
            'role_id' => $role->id,
            'teacher_id' => $teacherId,
            'is_active' => true,
        ]);
        $this->actingAs($user);

        return $user;
    }

    private function makeHomeroom($tch, $cls, $ay, $sem): HomeroomAssignment
    {
        return HomeroomAssignment::create([
            'teacher_id' => $tch->id, 'class_id' => $cls->id,
            'academic_year_id' => $ay->id, 'semester_id' => $sem->id,
        ]);
    }

    private function makePublishedCard($stu, $cls, $ay, $sem, $sub, float $score = 85): ReportCard
    {
        $card = ReportCard::create([
            'student_id' => $stu->id, 'class_id' => $cls->id,
            'academic_year_id' => $ay->id, 'semester_id' => $sem->id,
            'status' => 'diterbitkan', 'issued_at' => now(),
        ]);

        ReportCardSubject::create([
            'report_card_id' => $card->id, 'subject_id' => $sub->id,
            'final_score' => $score, 'description' => 'Mencapai KKTP.',
        ]);

        return $card;
    }

    private function assertPdf($response, string $filename): void
    {
        $response->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringContainsString($filename, $response->headers->get('Content-Disposition'));
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    // -----------------------------------------------------------------
    // (a) Sampul / identitas / transkrip
    // -----------------------------------------------------------------

    public function test_wali_can_download_cover_identity_transcript_for_own_class(): void
    {
        [$dep, $ay, $sem1, $sem2, $cls, $sub, $wali, $guru] = $this->seedParents();
        $stu = $this->makeStudent('001', 'Andi', $cls->id);
        $this->makeHomeroom($wali, $cls, $ay, $sem1);
        $card = $this->makePublishedCard($stu, $cls, $ay, $sem1, $sub);

        $this->loginAs('wali_kelas', $wali->id);

        $this->assertPdf($this->get("/wali/report-cards/{$card->id}/cover"), 'sampul-001-ganjil.pdf');
        $this->assertPdf($this->get("/wali/report-cards/{$card->id}/identity"), 'identitas-001-ganjil.pdf');
        $this->assertPdf($this->get("/wali/report-cards/{$card->id}/transcript"), 'transkrip-001.pdf');
    }

    public function test_other_wali_cannot_download_print_pdfs(): void
    {
        [$dep, $ay, $sem1, $sem2, $cls, $sub, $wali, $guru] = $this->seedParents();
        $stu = $this->makeStudent('001', 'Andi', $cls->id);
        $this->makeHomeroom($wali, $cls, $ay, $sem1);
        $card = $this->makePublishedCard($stu, $cls, $ay, $sem1, $sub);

        // Wali lain (bukan wali kelas ini) ditolak di ketiganya.
        $this->loginAs('wali_kelas', $guru->id);

        $this->get("/wali/report-cards/{$card->id}/cover")->assertStatus(403);
        $this->get("/wali/report-cards/{$card->id}/identity")->assertStatus(403);
        $this->get("/wali/report-cards/{$card->id}/transcript")->assertStatus(403);
    }

    public function test_transcript_forbidden_before_publish_but_cover_identity_allowed(): void
    {
        [$dep, $ay, $sem1, $sem2, $cls, $sub, $wali, $guru] = $this->seedParents();
        $stu = $this->makeStudent('001', 'Andi', $cls->id);
        $this->makeHomeroom($wali, $cls, $ay, $sem1);
        $card = ReportCard::create([
            'student_id' => $stu->id, 'class_id' => $cls->id,
            'academic_year_id' => $ay->id, 'semester_id' => $sem1->id, 'status' => 'draft',
        ]);

        $this->loginAs('wali_kelas', $wali->id);

        $this->get("/wali/report-cards/{$card->id}/cover")->assertStatus(200);
        $this->get("/wali/report-cards/{$card->id}/identity")->assertStatus(200);
        $this->get("/wali/report-cards/{$card->id}/transcript")->assertStatus(403);
    }

    // -----------------------------------------------------------------
    // (b) Leger
    // -----------------------------------------------------------------

    public function test_ledger_admin_200_with_live_source_and_guru_403(): void
    {
        [$dep, $ay, $sem1, $sem2, $cls, $sub, $wali, $guru] = $this->seedParents();
        $stu = $this->makeStudent('001', 'Andi', $cls->id);

        TeachingAssignment::create([
            'teacher_id' => $guru->id, 'subject_id' => $sub->id, 'class_id' => $cls->id,
            'academic_year_id' => $ay->id, 'semester_id' => $sem1->id,
        ]);
        $component = AssessmentComponent::create([
            'name' => 'Sumatif', 'type' => 'sumatif', 'weight' => 100,
            'subject_id' => $sub->id, 'kktp' => 75, 'is_active' => true,
        ]);
        AssessmentScore::create([
            'student_id' => $stu->id, 'subject_id' => $sub->id, 'component_id' => $component->id,
            'class_id' => $cls->id, 'academic_year_id' => $ay->id, 'semester_id' => $sem1->id,
            'teacher_id' => $guru->id, 'score' => 80, 'status' => 'terkirim',
        ]);

        $this->loginAs('admin');

        $res = $this->get("/admin/ledger?class_id={$cls->id}&academic_year_id={$ay->id}&semester_id={$sem1->id}");
        $res->assertStatus(200);
        $res->assertSee('Leger');
        $res->assertSee('Andi');
        $res->assertSee('Matematika');
        // Rapor belum terbit -> sumber live, nilai akhir tampil.
        $res->assertSee('Nilai akhir live');
        $res->assertSee('80,00');

        // PDF leger juga 200.
        $this->assertPdf(
            $this->get("/admin/ledger/pdf?class_id={$cls->id}&academic_year_id={$ay->id}&semester_id={$sem1->id}"),
            'leger-x-tkj-1'
        );

        // Guru tidak boleh mengakses leger admin.
        $this->loginAs('guru', $guru->id);
        $this->get("/admin/ledger?class_id={$cls->id}&academic_year_id={$ay->id}&semester_id={$sem1->id}")->assertStatus(403);
        $this->get("/admin/ledger/pdf?class_id={$cls->id}&academic_year_id={$ay->id}&semester_id={$sem1->id}")->assertStatus(403);
    }

    public function test_ledger_uses_snapshot_when_all_report_cards_published(): void
    {
        [$dep, $ay, $sem1, $sem2, $cls, $sub, $wali, $guru] = $this->seedParents();
        $stu = $this->makeStudent('001', 'Andi', $cls->id);
        $this->makePublishedCard($stu, $cls, $ay, $sem1, $sub, 92);

        TeachingAssignment::create([
            'teacher_id' => $guru->id, 'subject_id' => $sub->id, 'class_id' => $cls->id,
            'academic_year_id' => $ay->id, 'semester_id' => $sem1->id,
        ]);

        $this->loginAs('admin');

        $res = $this->get("/admin/ledger?class_id={$cls->id}&academic_year_id={$ay->id}&semester_id={$sem1->id}");
        $res->assertStatus(200);
        $res->assertSee('Snapshot rapor terbit');
        $res->assertSee('92,00');
    }

    // -----------------------------------------------------------------
    // (c) Pembina ekstrakurikuler
    // -----------------------------------------------------------------

    public function test_supervisor_can_score_own_extracurricular_and_other_teacher_403(): void
    {
        [$dep, $ay, $sem1, $sem2, $cls, $sub, $wali, $guru] = $this->seedParents();
        $stu = $this->makeStudent('001', 'Andi', $cls->id);

        $ekskul = Extracurricular::create(['name' => 'Pramuka', 'supervisor_id' => $guru->id]);

        // Pembina: daftar binaan tampil, nilai tersimpan + tercatat.
        $this->loginAs('guru', $guru->id);

        $this->get('/guru/ekstra-binaan')->assertStatus(200)->assertSee('Pramuka');
        $this->get("/guru/ekstra-binaan/{$ekskul->id}")->assertStatus(200);

        $res = $this->post("/guru/ekstra-binaan/{$ekskul->id}/scores", [
            'academic_year_id' => $ay->id,
            'semester_id' => $sem1->id,
            'records' => [
                ['student_id' => $stu->id, 'score' => 90, 'description' => 'Aktif dan disiplin.'],
            ],
        ]);
        $res->assertRedirect();

        $record = StudentExtracurricular::where('student_id', $stu->id)
            ->where('extracurricular_id', $ekskul->id)->firstOrFail();
        $this->assertEquals(90, (float) $record->score);
        $this->assertEquals('Aktif dan disiplin.', $record->description);
        $this->assertDatabaseHas('activity_logs', [
            'action' => 'student_extracurricular.supervisor_scored',
            'model_id' => $record->id,
        ]);

        // Guru lain (bukan pembina) ditolak.
        $other = Teacher::create(['nip' => '333', 'name' => 'Guru Tiga']);
        $this->loginAs('guru', $other->id);
        $this->get("/guru/ekstra-binaan/{$ekskul->id}")->assertStatus(403);
        $this->post("/guru/ekstra-binaan/{$ekskul->id}/scores", [
            'academic_year_id' => $ay->id,
            'semester_id' => $sem1->id,
            'records' => [
                ['student_id' => $stu->id, 'score' => 50, 'description' => 'Diubah orang lain.'],
            ],
        ])->assertStatus(403);
        $this->assertEquals(90, (float) $record->fresh()->score);
    }

    public function test_admin_can_assign_supervisor(): void
    {
        [$dep, $ay, $sem1, $sem2, $cls, $sub, $wali, $guru] = $this->seedParents();
        $ekskul = Extracurricular::create(['name' => 'Pramuka']);

        $this->loginAs('admin');

        $this->get('/admin/extracurriculars')->assertStatus(200)->assertSee('Pramuka');

        $this->put("/admin/extracurriculars/{$ekskul->id}/supervisor", [
            'supervisor_id' => $guru->id,
        ])->assertRedirect();

        $this->assertEquals($guru->id, $ekskul->fresh()->supervisor_id);
        $this->assertDatabaseHas('activity_logs', [
            'action' => 'extracurricular.supervisor_assigned',
            'model_id' => $ekskul->id,
        ]);
    }

    // -----------------------------------------------------------------
    // (d) Profil guru
    // -----------------------------------------------------------------

    public function test_teacher_profile_update_and_password_change(): void
    {
        [$dep, $ay, $sem1, $sem2, $cls, $sub, $wali, $guru] = $this->seedParents();

        $user = $this->loginAs('guru', $guru->id);

        $this->get('/guru/profil')->assertStatus(200)->assertSee('Profil Saya');

        $res = $this->put('/guru/profil', [
            'name' => 'Guru Dua Baru',
            'phone' => '081234567890',
            'address' => 'Jl. Baru No. 2',
        ]);
        $res->assertRedirect();

        $this->assertEquals('081234567890', $guru->fresh()->phone);
        $this->assertEquals('Jl. Baru No. 2', $guru->fresh()->address);
        $this->assertEquals('Guru Dua Baru', $guru->fresh()->name);
        $this->assertEquals('Guru Dua Baru', $user->fresh()->name);

        // Password lama salah -> ditolak, password tidak berubah.
        $this->put('/guru/profil/password', [
            'current_password' => 'salah',
            'password' => 'passwordbaru',
            'password_confirmation' => 'passwordbaru',
        ])->assertSessionHasErrors('current_password');

        // Password lama benar -> berhasil, login baru memakai password baru.
        $this->put('/guru/profil/password', [
            'current_password' => 'password',
            'password' => 'passwordbaru',
            'password_confirmation' => 'passwordbaru',
        ])->assertRedirect();

        $this->post('/logout');
        $this->post('/login', ['email' => $user->email, 'password' => 'passwordbaru'])
            ->assertRedirect('/guru/dashboard');
    }

    public function test_wali_profile_page_loads(): void
    {
        [$dep, $ay, $sem1, $sem2, $cls, $sub, $wali, $guru] = $this->seedParents();

        $this->loginAs('wali_kelas', $wali->id);

        $this->get('/wali/profil')->assertStatus(200)->assertSee('Profil Saya');

        $this->put('/wali/profil', [
            'name' => 'Wali Satu Baru',
            'phone' => '089999999999',
            'address' => null,
        ])->assertRedirect();

        $this->assertEquals('089999999999', $wali->fresh()->phone);
    }

    // -----------------------------------------------------------------
    // (e) Dashboard admin: panel status nilai + aktivitas
    // -----------------------------------------------------------------

    public function test_admin_dashboard_shows_score_status_and_recent_activity(): void
    {
        [$dep, $ay, $sem1, $sem2, $cls, $sub, $wali, $guru] = $this->seedParents();
        $stu = $this->makeStudent('001', 'Andi', $cls->id);

        TeachingAssignment::create([
            'teacher_id' => $guru->id, 'subject_id' => $sub->id, 'class_id' => $cls->id,
            'academic_year_id' => $ay->id, 'semester_id' => $sem1->id,
        ]);
        $component = AssessmentComponent::create([
            'name' => 'Sumatif', 'type' => 'sumatif', 'weight' => 100,
            'subject_id' => $sub->id, 'kktp' => 75, 'is_active' => true,
        ]);
        AssessmentScore::create([
            'student_id' => $stu->id, 'subject_id' => $sub->id, 'component_id' => $component->id,
            'class_id' => $cls->id, 'academic_year_id' => $ay->id, 'semester_id' => $sem1->id,
            'teacher_id' => $guru->id, 'score' => 80, 'status' => 'draft',
        ]);

        $guruUser = $this->loginAs('guru', $guru->id);
        ActivityLog::create([
            'user_id' => $guruUser->id,
            'action' => 'assessment_score.created',
            'model_type' => AssessmentScore::class,
            'model_id' => 1,
        ]);

        $this->loginAs('admin');

        $res = $this->get('/admin/dashboard');
        $res->assertStatus(200);
        $res->assertSee('Status Pengiriman Nilai');
        $res->assertSee('Matematika');
        $res->assertSee('Aktivitas Guru Terakhir');
        $res->assertSee('assessment_score.created');
        $res->assertSee($guruUser->name);
    }
}
