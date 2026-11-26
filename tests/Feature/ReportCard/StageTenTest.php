<?php

namespace Tests\Feature\ReportCard;

use App\Models\AcademicYear;
use App\Models\ActivityLog;
use App\Models\AssessmentComponent;
use App\Models\AssessmentScore;
use App\Models\Department;
use App\Models\HomeroomAssignment;
use App\Models\ReportCard;
use App\Models\ReportCardApproval;
use App\Models\ReportCardNote;
use App\Models\ReportCardSubject;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherDescription;
use App\Models\TeachingAssignment;
use App\Models\User;
use App\Services\ReportCardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StageTenTest extends TestCase
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
        $tch = Teacher::create(['nip' => '123', 'name' => 'Wali 1']);

        return [$dep, $ay, $sem1, $sem2, $cls, $sub, $tch];
    }

    private function makeStudent(string $nis, string $name, int $classId): Student
    {
        $stu = Student::create(['nis' => $nis, 'nisn' => 'N'.$nis, 'name' => $name]);
        $stu->class_id = $classId;
        $stu->gender = 'L';
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

    private function makeTeaching($tch, $sub, $cls, $ay, $sem): TeachingAssignment
    {
        return TeachingAssignment::create([
            'teacher_id' => $tch->id, 'subject_id' => $sub->id, 'class_id' => $cls->id,
            'academic_year_id' => $ay->id, 'semester_id' => $sem->id,
        ]);
    }

    private function makeComponent($sub): AssessmentComponent
    {
        return AssessmentComponent::create([
            'name' => 'Sumatif', 'type' => 'sumatif', 'weight' => 100,
            'subject_id' => $sub->id, 'kktp' => 75, 'is_active' => true,
        ]);
    }

    private function seedSubmittedScore($stu, $sub, $component, $cls, $ay, $sem, $tch, int $score = 80): AssessmentScore
    {
        return AssessmentScore::create([
            'student_id' => $stu->id, 'subject_id' => $sub->id, 'component_id' => $component->id,
            'class_id' => $cls->id, 'academic_year_id' => $ay->id, 'semester_id' => $sem->id,
            'teacher_id' => $tch->id, 'score' => $score, 'status' => 'terkirim',
        ]);
    }

    /**
     * Lengkapi seluruh komponen wajib rapor satu siswa pada satu
     * periode: nilai terkirim + deskripsi + presensi + catatan wali.
     */
    private function seedComplete($stu, $sub, $component, $cls, $ay, $sem, $tch, int $score = 80, string $date = '2025-08-04'): ReportCard
    {
        $this->seedSubmittedScore($stu, $sub, $component, $cls, $ay, $sem, $tch, $score);

        TeacherDescription::create([
            'student_id' => $stu->id, 'subject_id' => $sub->id, 'teacher_id' => $tch->id,
            'academic_year_id' => $ay->id, 'semester_id' => $sem->id,
            'description' => 'Mencapai KKTP pada Matematika dengan nilai akhir '.$score.',00.',
        ]);

        StudentAttendance::create([
            'student_id' => $stu->id, 'class_id' => $cls->id,
            'academic_year_id' => $ay->id, 'semester_id' => $sem->id,
            'date' => $date, 'status' => 'H',
        ]);

        $card = ReportCard::create([
            'student_id' => $stu->id, 'class_id' => $cls->id,
            'academic_year_id' => $ay->id, 'semester_id' => $sem->id, 'status' => 'draft',
        ]);

        ReportCardNote::create([
            'report_card_id' => $card->id, 'note_type' => 'wali_kelas',
            'content' => 'Siswa rajin dan disiplin.',
        ]);

        return $card;
    }

    /**
     * Alur lengkap sampai diterbitkan: wali mengajukan, admin
     * menyetujui, admin menerbitkan. Mengembalikan rapor segar.
     */
    private function publishFlow(ReportCard $card, $stu, $cls, $ay, $sem, $tch): ReportCard
    {
        $this->loginAs('wali_kelas', $tch->id);
        $this->post('/wali/submit', [
            'student_id' => $stu->id, 'class_id' => $cls->id,
            'academic_year_id' => $ay->id, 'semester_id' => $sem->id,
        ])->assertSessionHas('success');

        $admin = $this->loginAs('admin');
        $this->post("/admin/report-cards/{$card->id}/approve")->assertSessionHas('success');
        $this->post("/admin/report-cards/{$card->id}/publish")->assertSessionHas('success');

        return $card->fresh();
    }

    public function test_admin_index_show_history_pages_load(): void
    {
        [, $ay, $sem1, , $cls, $sub, $tch] = $this->seedParents();
        $stu = $this->makeStudent('001', 'Siswa 1', $cls->id);
        $this->makeHomeroom($tch, $cls, $ay, $sem1);
        $this->makeTeaching($tch, $sub, $cls, $ay, $sem1);
        $component = $this->makeComponent($sub);
        $card = $this->seedComplete($stu, $sub, $component, $cls, $ay, $sem1, $tch);

        $this->loginAs('admin');

        $res = $this->get('/admin/report-cards');
        $res->assertStatus(200);
        $res->assertSee('Siswa 1');

        $res = $this->get("/admin/report-cards/{$card->id}");
        $res->assertStatus(200);
        $res->assertSee('Siswa 1');
        $res->assertSee('Matematika');
        // Rapor draft tapi lengkap -> label dokumen "Siap Diperiksa".
        $res->assertSee('Siap Diperiksa');

        $res = $this->get("/admin/report-cards/{$card->id}/history");
        $res->assertStatus(200);
        $res->assertSee('Riwayat Rapor');
    }

    public function test_approve_requires_submitted_status(): void
    {
        [, $ay, $sem1, , $cls, $sub, $tch] = $this->seedParents();
        $stu = $this->makeStudent('001', 'Siswa 1', $cls->id);
        $this->makeHomeroom($tch, $cls, $ay, $sem1);
        $this->makeTeaching($tch, $sub, $cls, $ay, $sem1);
        $component = $this->makeComponent($sub);
        $card = $this->seedComplete($stu, $sub, $component, $cls, $ay, $sem1, $tch);

        // Rapor masih draft: tidak bisa disetujui.
        $this->loginAs('admin');
        $res = $this->post("/admin/report-cards/{$card->id}/approve");
        $res->assertSessionHasErrors('status');
        $this->assertEquals('draft', $card->fresh()->status);
        $this->assertEquals(0, ReportCardApproval::count());
    }

    public function test_approve_rejected_when_incomplete_after_submit(): void
    {
        [, $ay, $sem1, , $cls, $sub, $tch] = $this->seedParents();
        $stu = $this->makeStudent('001', 'Siswa 1', $cls->id);
        $this->makeHomeroom($tch, $cls, $ay, $sem1);
        $this->makeTeaching($tch, $sub, $cls, $ay, $sem1);
        $component = $this->makeComponent($sub);
        $card = $this->seedComplete($stu, $sub, $component, $cls, $ay, $sem1, $tch);

        $this->loginAs('wali_kelas', $tch->id);
        $this->post('/wali/submit', [
            'student_id' => $stu->id, 'class_id' => $cls->id,
            'academic_year_id' => $ay->id, 'semester_id' => $sem1->id,
        ])->assertSessionHas('success');
        $this->assertEquals('diajukan', $card->fresh()->status);

        // Komponen hilang setelah pengajuan: deskripsi dihapus.
        TeacherDescription::query()->delete();

        $this->loginAs('admin');
        $res = $this->post("/admin/report-cards/{$card->id}/approve");
        $res->assertSessionHasErrors('completeness');
        $this->assertEquals('diajukan', $card->fresh()->status);
        $this->assertDatabaseMissing('activity_logs', ['action' => 'report_card.approved']);
    }

    public function test_approve_succeeds_and_logs_approver(): void
    {
        [, $ay, $sem1, , $cls, $sub, $tch] = $this->seedParents();
        $stu = $this->makeStudent('001', 'Siswa 1', $cls->id);
        $this->makeHomeroom($tch, $cls, $ay, $sem1);
        $this->makeTeaching($tch, $sub, $cls, $ay, $sem1);
        $component = $this->makeComponent($sub);
        $card = $this->seedComplete($stu, $sub, $component, $cls, $ay, $sem1, $tch);

        $this->loginAs('wali_kelas', $tch->id);
        $this->post('/wali/submit', [
            'student_id' => $stu->id, 'class_id' => $cls->id,
            'academic_year_id' => $ay->id, 'semester_id' => $sem1->id,
        ])->assertSessionHas('success');

        $admin = $this->loginAs('admin');
        $res = $this->post("/admin/report-cards/{$card->id}/approve");
        $res->assertSessionHas('success');

        $this->assertEquals('divalidasi', $card->fresh()->status);

        $approval = ReportCardApproval::where('report_card_id', $card->id)->first();
        $this->assertNotNull($approval);
        $this->assertEquals('approved', $approval->status);
        $this->assertEquals($admin->id, $approval->approver_id);
        $this->assertNotNull($approval->approved_at);

        $log = ActivityLog::where('action', 'report_card.approved')->first();
        $this->assertNotNull($log);
        $this->assertEquals(ReportCard::class, $log->model_type);
        $this->assertEquals($card->id, $log->model_id);
        $this->assertEquals($admin->id, $log->user_id);

        // Persetujuan kedua ditolak: status bukan diajukan lagi.
        $res = $this->post("/admin/report-cards/{$card->id}/approve");
        $res->assertSessionHasErrors('status');
        $this->assertEquals(1, ReportCardApproval::where('status', 'approved')->count());
    }

    public function test_wali_and_guru_cannot_approve(): void
    {
        [, $ay, $sem1, , $cls, $sub, $tch] = $this->seedParents();
        $stu = $this->makeStudent('001', 'Siswa 1', $cls->id);
        $this->makeHomeroom($tch, $cls, $ay, $sem1);
        $this->makeTeaching($tch, $sub, $cls, $ay, $sem1);
        $component = $this->makeComponent($sub);
        $card = $this->seedComplete($stu, $sub, $component, $cls, $ay, $sem1, $tch);

        $this->loginAs('wali_kelas', $tch->id);
        $this->post('/wali/submit', [
            'student_id' => $stu->id, 'class_id' => $cls->id,
            'academic_year_id' => $ay->id, 'semester_id' => $sem1->id,
        ])->assertSessionHas('success');

        // Wali kelas tidak boleh menyetujui rapor kelasnya sendiri:
        // route admin menolak role wali (403).
        $res = $this->post("/admin/report-cards/{$card->id}/approve");
        $res->assertForbidden();
        $this->assertEquals('diajukan', $card->fresh()->status);

        $this->loginAs('guru', $tch->id);
        $res = $this->post("/admin/report-cards/{$card->id}/approve");
        $res->assertForbidden();
        $this->assertEquals('diajukan', $card->fresh()->status);
        $this->assertEquals(0, ReportCardApproval::count());
    }

    public function test_reject_requires_reason_and_returns_to_draft(): void
    {
        [, $ay, $sem1, , $cls, $sub, $tch] = $this->seedParents();
        $stu = $this->makeStudent('001', 'Siswa 1', $cls->id);
        $this->makeHomeroom($tch, $cls, $ay, $sem1);
        $this->makeTeaching($tch, $sub, $cls, $ay, $sem1);
        $component = $this->makeComponent($sub);
        $card = $this->seedComplete($stu, $sub, $component, $cls, $ay, $sem1, $tch);

        $this->loginAs('wali_kelas', $tch->id);
        $this->post('/wali/submit', [
            'student_id' => $stu->id, 'class_id' => $cls->id,
            'academic_year_id' => $ay->id, 'semester_id' => $sem1->id,
        ])->assertSessionHas('success');

        $admin = $this->loginAs('admin');

        // Tanpa alasan: ditolak validasi, rapor tetap diajukan.
        $res = $this->post("/admin/report-cards/{$card->id}/reject", ['note' => '']);
        $res->assertSessionHasErrors('note');
        $this->assertEquals('diajukan', $card->fresh()->status);

        // Dengan alasan: kembali draft, approval rejected tercatat.
        $res = $this->post("/admin/report-cards/{$card->id}/reject", ['note' => 'Deskripsi Matematika kurang rinci.']);
        $res->assertSessionHas('success');
        $this->assertEquals('draft', $card->fresh()->status);

        $approval = ReportCardApproval::where('report_card_id', $card->id)->first();
        $this->assertNotNull($approval);
        $this->assertEquals('rejected', $approval->status);
        $this->assertEquals('Deskripsi Matematika kurang rinci.', $approval->note);
        $this->assertEquals($admin->id, $approval->approver_id);

        $this->assertDatabaseHas('activity_logs', ['action' => 'report_card.rejected']);

        // Detail menampilkan status Perlu Perbaikan lewat riwayat penolakan.
        $res = $this->get("/admin/report-cards/{$card->id}");
        $res->assertStatus(200);
        $res->assertSee('Dikembalikan (Perlu Perbaikan)');
        $res->assertSee('Deskripsi Matematika kurang rinci.');
    }

    public function test_publish_requires_validated_status(): void
    {
        [, $ay, $sem1, , $cls, $sub, $tch] = $this->seedParents();
        $stu = $this->makeStudent('001', 'Siswa 1', $cls->id);
        $this->makeHomeroom($tch, $cls, $ay, $sem1);
        $this->makeTeaching($tch, $sub, $cls, $ay, $sem1);
        $component = $this->makeComponent($sub);
        $card = $this->seedComplete($stu, $sub, $component, $cls, $ay, $sem1, $tch);

        $this->loginAs('admin');

        // Draft belum bisa diterbitkan.
        $res = $this->post("/admin/report-cards/{$card->id}/publish");
        $res->assertSessionHasErrors('status');
        $this->assertEquals('draft', $card->fresh()->status);
        $this->assertNull($card->fresh()->issued_at);
    }

    public function test_publish_rejected_when_incomplete_after_approval(): void
    {
        [, $ay, $sem1, , $cls, $sub, $tch] = $this->seedParents();
        $stu = $this->makeStudent('001', 'Siswa 1', $cls->id);
        $this->makeHomeroom($tch, $cls, $ay, $sem1);
        $this->makeTeaching($tch, $sub, $cls, $ay, $sem1);
        $component = $this->makeComponent($sub);
        $card = $this->seedComplete($stu, $sub, $component, $cls, $ay, $sem1, $tch);

        $this->loginAs('wali_kelas', $tch->id);
        $this->post('/wali/submit', [
            'student_id' => $stu->id, 'class_id' => $cls->id,
            'academic_year_id' => $ay->id, 'semester_id' => $sem1->id,
        ])->assertSessionHas('success');

        $this->loginAs('admin');
        $this->post("/admin/report-cards/{$card->id}/approve")->assertSessionHas('success');
        $this->assertEquals('divalidasi', $card->fresh()->status);

        // Presensi dihapus setelah persetujuan: terbit ditolak.
        StudentAttendance::query()->delete();

        $res = $this->post("/admin/report-cards/{$card->id}/publish");
        $res->assertSessionHasErrors('completeness');
        $this->assertEquals('divalidasi', $card->fresh()->status);
        $this->assertEquals(0, ReportCardSubject::count());
    }

    public function test_publish_creates_snapshot_and_fills_issuer(): void
    {
        [, $ay, $sem1, , $cls, $sub, $tch] = $this->seedParents();
        $stu = $this->makeStudent('001', 'Siswa 1', $cls->id);
        $this->makeHomeroom($tch, $cls, $ay, $sem1);
        $this->makeTeaching($tch, $sub, $cls, $ay, $sem1);
        $component = $this->makeComponent($sub);
        $card = $this->seedComplete($stu, $sub, $component, $cls, $ay, $sem1, $tch);

        $this->loginAs('wali_kelas', $tch->id);
        $this->post('/wali/submit', [
            'student_id' => $stu->id, 'class_id' => $cls->id,
            'academic_year_id' => $ay->id, 'semester_id' => $sem1->id,
        ])->assertSessionHas('success');

        $admin = $this->loginAs('admin');
        $this->post("/admin/report-cards/{$card->id}/approve")->assertSessionHas('success');
        $res = $this->post("/admin/report-cards/{$card->id}/publish");
        $res->assertSessionHas('success');

        $fresh = $card->fresh();
        $this->assertEquals('diterbitkan', $fresh->status);
        $this->assertNotNull($fresh->issued_at);
        $this->assertEquals($admin->id, $fresh->issued_by);

        // Snapshot tersimpan: nilai akhir + deskripsi guru.
        $snapshot = ReportCardSubject::where('report_card_id', $card->id)->first();
        $this->assertNotNull($snapshot);
        $this->assertEquals($sub->id, $snapshot->subject_id);
        $this->assertEquals(80.0, (float) $snapshot->final_score);
        $this->assertEquals('Mencapai KKTP pada Matematika dengan nilai akhir 80,00.', $snapshot->description);

        $this->assertDatabaseHas('activity_logs', ['action' => 'report_card.published']);
    }

    public function test_double_publish_rejected(): void
    {
        [, $ay, $sem1, , $cls, $sub, $tch] = $this->seedParents();
        $stu = $this->makeStudent('001', 'Siswa 1', $cls->id);
        $this->makeHomeroom($tch, $cls, $ay, $sem1);
        $this->makeTeaching($tch, $sub, $cls, $ay, $sem1);
        $component = $this->makeComponent($sub);
        $card = $this->seedComplete($stu, $sub, $component, $cls, $ay, $sem1, $tch);

        $card = $this->publishFlow($card, $stu, $cls, $ay, $sem1, $tch);
        $this->assertEquals('diterbitkan', $card->status);
        $firstIssuedAt = $card->issued_at;

        $this->loginAs('admin');
        $res = $this->post("/admin/report-cards/{$card->id}/publish");
        $res->assertSessionHasErrors('status');
        $this->assertEquals('diterbitkan', $card->fresh()->status);
        $this->assertEquals($firstIssuedAt, $card->fresh()->issued_at);
        $this->assertEquals(1, ActivityLog::where('action', 'report_card.published')->count());
        $this->assertEquals(1, ReportCardSubject::where('report_card_id', $card->id)->count());
    }

    public function test_lock_only_from_published(): void
    {
        [, $ay, $sem1, , $cls, $sub, $tch] = $this->seedParents();
        $stu = $this->makeStudent('001', 'Siswa 1', $cls->id);
        $this->makeHomeroom($tch, $cls, $ay, $sem1);
        $this->makeTeaching($tch, $sub, $cls, $ay, $sem1);
        $component = $this->makeComponent($sub);
        $card = $this->seedComplete($stu, $sub, $component, $cls, $ay, $sem1, $tch);

        $this->loginAs('admin');

        // Draft tidak bisa dikunci.
        $res = $this->post("/admin/report-cards/{$card->id}/lock");
        $res->assertSessionHasErrors('status');
        $this->assertEquals('draft', $card->fresh()->status);

        $card = $this->publishFlow($card, $stu, $cls, $ay, $sem1, $tch);

        $res = $this->post("/admin/report-cards/{$card->id}/lock");
        $res->assertSessionHas('success');
        $this->assertEquals('dikunci', $card->fresh()->status);
        $this->assertNotNull($card->fresh()->locked_at);
        $this->assertDatabaseHas('activity_logs', ['action' => 'report_card.locked']);

        // Kunci kedua ditolak.
        $res = $this->post("/admin/report-cards/{$card->id}/lock");
        $res->assertSessionHasErrors('status');
    }

    public function test_pdf_uses_snapshot_not_live_data(): void
    {
        [, $ay, $sem1, , $cls, $sub, $tch] = $this->seedParents();
        $stu = $this->makeStudent('001', 'Siswa 1', $cls->id);
        $this->makeHomeroom($tch, $cls, $ay, $sem1);
        $this->makeTeaching($tch, $sub, $cls, $ay, $sem1);
        $component = $this->makeComponent($sub);
        $card = $this->seedComplete($stu, $sub, $component, $cls, $ay, $sem1, $tch);

        $card = $this->publishFlow($card, $stu, $cls, $ay, $sem1, $tch);
        $this->assertEquals('diterbitkan', $card->status);

        // Data live berubah setelah terbit: nilai 80 -> 95, deskripsi diubah.
        AssessmentScore::query()->update(['score' => 95]);
        TeacherDescription::query()->update(['description' => 'Deskripsi diubah setelah terbit.']);

        // Snapshot tidak berubah.
        $snapshot = ReportCardSubject::where('report_card_id', $card->id)->first();
        $this->assertEquals(80.0, (float) $snapshot->final_score);
        $this->assertEquals('Mencapai KKTP pada Matematika dengan nilai akhir 80,00.', $snapshot->description);

        // PDF memakai snapshot: nilai pada data PDF tetap 80.
        $data = app(ReportCardService::class)->pdfData($card->fresh());
        $this->assertEquals(80.0, (float) $data['snapshot'][0]->final_score);
        $this->assertEquals('Mencapai KKTP pada Matematika dengan nilai akhir 80,00.', $data['snapshot'][0]->description);

        // PDF terunduh: status 200, content-type PDF, nama file benar.
        $this->loginAs('admin');
        $res = $this->get("/admin/report-cards/{$card->id}/pdf");
        $res->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $res->headers->get('Content-Type'));
        $this->assertStringContainsString('rapor-001-ganjil.pdf', $res->headers->get('Content-Disposition'));
        $this->assertStringStartsWith('%PDF', $res->getContent());
    }

    public function test_pdf_forbidden_before_publish(): void
    {
        [, $ay, $sem1, , $cls, $sub, $tch] = $this->seedParents();
        $stu = $this->makeStudent('001', 'Siswa 1', $cls->id);
        $this->makeHomeroom($tch, $cls, $ay, $sem1);
        $this->makeTeaching($tch, $sub, $cls, $ay, $sem1);
        $component = $this->makeComponent($sub);
        $card = $this->seedComplete($stu, $sub, $component, $cls, $ay, $sem1, $tch);

        $this->loginAs('admin');
        $res = $this->get("/admin/report-cards/{$card->id}/pdf");
        $res->assertForbidden();

        // Wali kelas sendiri juga belum bisa mengunduh sebelum terbit.
        $this->loginAs('wali_kelas', $tch->id);
        $res = $this->get("/wali/report-cards/{$card->id}/pdf");
        $res->assertForbidden();
    }

    public function test_wali_pdf_access_only_own_class(): void
    {
        [$dep, $ay, $sem1, , $cls, $sub, $tch] = $this->seedParents();
        $cls2 = SchoolClass::create(['department_id' => $dep->id, 'academic_year_id' => $ay->id, 'name' => 'X TKJ 2', 'level' => 'X']);
        $tch2 = Teacher::create(['nip' => '456', 'name' => 'Wali 2']);
        $stu = $this->makeStudent('001', 'Siswa 1', $cls->id);
        $stuOther = $this->makeStudent('002', 'Siswa Lain', $cls2->id);
        $this->makeHomeroom($tch, $cls, $ay, $sem1);
        $this->makeHomeroom($tch2, $cls2, $ay, $sem1);
        $this->makeTeaching($tch, $sub, $cls, $ay, $sem1);
        $this->makeTeaching($tch2, $sub, $cls2, $ay, $sem1);
        $component = $this->makeComponent($sub);
        $card = $this->seedComplete($stu, $sub, $component, $cls, $ay, $sem1, $tch);
        $cardOther = $this->seedComplete($stuOther, $sub, $component, $cls2, $ay, $sem1, $tch2);

        $card = $this->publishFlow($card, $stu, $cls, $ay, $sem1, $tch);
        $cardOther = $this->publishFlow($cardOther, $stuOther, $cls2, $ay, $sem1, $tch2);

        // Wali 1: PDF kelas sendiri boleh.
        $this->loginAs('wali_kelas', $tch->id);
        $res = $this->get("/wali/report-cards/{$card->id}/pdf");
        $res->assertStatus(200);
        $this->assertStringContainsString('rapor-001-ganjil.pdf', $res->headers->get('Content-Disposition'));

        // Wali 1: PDF kelas wali lain 403.
        $res = $this->get("/wali/report-cards/{$cardOther->id}/pdf");
        $res->assertForbidden();

        // Wali 1: daftar status hanya menampilkan kelasnya (bukan kelas lain).
        $res = $this->get('/wali/report-cards');
        $res->assertStatus(200);
        $res->assertSee('Siswa 1');
        $res->assertDontSee('Siswa Lain');

        // Wali 1 memfilter kelas lain -> 403.
        $res = $this->get("/wali/report-cards?class_id={$cls2->id}");
        $res->assertForbidden();
    }

    public function test_guru_cannot_download_pdf(): void
    {
        [, $ay, $sem1, , $cls, $sub, $tch] = $this->seedParents();
        $stu = $this->makeStudent('001', 'Siswa 1', $cls->id);
        $this->makeHomeroom($tch, $cls, $ay, $sem1);
        $this->makeTeaching($tch, $sub, $cls, $ay, $sem1);
        $component = $this->makeComponent($sub);
        $card = $this->seedComplete($stu, $sub, $component, $cls, $ay, $sem1, $tch);

        $card = $this->publishFlow($card, $stu, $cls, $ay, $sem1, $tch);

        // Guru tidak punya akses PDF: route admin menolak role guru,
        // route wali menolak role guru (hanya admin + wali kelasnya).
        $this->loginAs('guru', $tch->id);
        $res = $this->get("/admin/report-cards/{$card->id}/pdf");
        $res->assertForbidden();
        $res = $this->get("/wali/report-cards/{$card->id}/pdf");
        $res->assertForbidden();
    }

    public function test_guest_redirected_to_login(): void
    {
        [, $ay, $sem1, , $cls, $sub, $tch] = $this->seedParents();
        $stu = $this->makeStudent('001', 'Siswa 1', $cls->id);
        $this->makeHomeroom($tch, $cls, $ay, $sem1);
        $this->makeTeaching($tch, $sub, $cls, $ay, $sem1);
        $component = $this->makeComponent($sub);
        $card = $this->seedComplete($stu, $sub, $component, $cls, $ay, $sem1, $tch);

        $res = $this->get('/admin/report-cards');
        $res->assertRedirect('/login');
        $res = $this->get("/admin/report-cards/{$card->id}/pdf");
        $res->assertRedirect('/login');
        $res = $this->get('/wali/report-cards');
        $res->assertRedirect('/login');
        $res = $this->get("/wali/report-cards/{$card->id}/pdf");
        $res->assertRedirect('/login');
    }

    public function test_correction_after_publish_rejected_and_logged(): void
    {
        [, $ay, $sem1, , $cls, $sub, $tch] = $this->seedParents();
        $stu = $this->makeStudent('001', 'Siswa 1', $cls->id);
        $this->makeHomeroom($tch, $cls, $ay, $sem1);
        $this->makeTeaching($tch, $sub, $cls, $ay, $sem1);
        $component = $this->makeComponent($sub);
        $card = $this->seedComplete($stu, $sub, $component, $cls, $ay, $sem1, $tch);

        $card = $this->publishFlow($card, $stu, $cls, $ay, $sem1, $tch);
        $note = ReportCardNote::where('report_card_id', $card->id)->first();

        $this->loginAs('wali_kelas', $tch->id);

        // Ubah catatan rapor yang sudah diterbitkan -> 403 + log.
        $res = $this->put("/wali/notes/{$note->id}", ['content' => 'Catatan diubah setelah terbit.']);
        $res->assertForbidden();
        $this->assertEquals('Siswa rajin dan disiplin.', $note->fresh()->content);
        $this->assertDatabaseHas('activity_logs', ['action' => 'report_card.correction_rejected']);

        // Tambah catatan baru pada rapor terbit -> 403 juga.
        $res = $this->post('/wali/notes', [
            'student_id' => $stu->id, 'academic_year_id' => $ay->id, 'semester_id' => $sem1->id,
            'content' => 'Catatan tambahan.',
        ]);
        $res->assertForbidden();
        $this->assertEquals(1, ReportCardNote::where('report_card_id', $card->id)->count());
        $this->assertEquals(2, ActivityLog::where('action', 'report_card.correction_rejected')->count());

        // Hapus catatan rapor terbit -> 403.
        $res = $this->delete("/wali/notes/{$note->id}");
        $res->assertForbidden();
        $this->assertNotNull(ReportCardNote::find($note->id));
    }

    public function test_cross_semester_data_not_mixed(): void
    {
        [, $ay, $sem1, $sem2, $cls, $sub, $tch] = $this->seedParents();
        $stu = $this->makeStudent('001', 'Siswa 1', $cls->id);
        $this->makeHomeroom($tch, $cls, $ay, $sem1);
        $this->makeHomeroom($tch, $cls, $ay, $sem2);
        $this->makeTeaching($tch, $sub, $cls, $ay, $sem1);
        $this->makeTeaching($tch, $sub, $cls, $ay, $sem2);
        $component = $this->makeComponent($sub);

        // Semester 1 nilai 80, semester 2 nilai 90.
        $card1 = $this->seedComplete($stu, $sub, $component, $cls, $ay, $sem1, $tch, 80, '2025-08-04');
        $card2 = $this->seedComplete($stu, $sub, $component, $cls, $ay, $sem2, $tch, 90, '2026-01-05');

        $card1 = $this->publishFlow($card1, $stu, $cls, $ay, $sem1, $tch);

        // Snapshot semester 1 tetap 80; rapor semester 2 tetap draft
        // dan snapshot-nya kosong (belum diterbitkan).
        $snap1 = ReportCardSubject::where('report_card_id', $card1->id)->first();
        $this->assertEquals(80.0, (float) $snap1->final_score);
        $this->assertEquals('draft', $card2->fresh()->status);
        $this->assertEquals(0, ReportCardSubject::where('report_card_id', $card2->id)->count());

        // Nilai live semester 2 tetap 90 pada data pemeriksaan.
        $data2 = app(ReportCardService::class)->reviewData($card2->fresh());
        $this->assertEquals(90.0, $data2['finals'][0]['final_score']);
        $data1 = app(ReportCardService::class)->pdfData($card1->fresh());
        $this->assertEquals(80.0, (float) $data1['snapshot'][0]->final_score);

        // Nama file PDF semester 1 memakai semester Ganjil.
        $this->loginAs('admin');
        $res = $this->get("/admin/report-cards/{$card1->id}/pdf");
        $res->assertStatus(200);
        $this->assertStringContainsString('rapor-001-ganjil.pdf', $res->headers->get('Content-Disposition'));
    }

    public function test_document_label_mapping(): void
    {
        [, $ay, $sem1, , $cls, $sub, $tch] = $this->seedParents();
        $stu = $this->makeStudent('001', 'Siswa 1', $cls->id);
        $this->makeHomeroom($tch, $cls, $ay, $sem1);
        $this->makeTeaching($tch, $sub, $cls, $ay, $sem1);
        $component = $this->makeComponent($sub);
        $card = $this->seedComplete($stu, $sub, $component, $cls, $ay, $sem1, $tch);

        // Draft + lengkap -> Siap Diperiksa; draft + tidak lengkap -> Belum Lengkap.
        $this->assertEquals('Siap Diperiksa', $card->documentLabel(true));
        $this->assertEquals('Belum Lengkap', $card->documentLabel(false));

        $card->update(['status' => 'diajukan']);
        $this->assertEquals('Diajukan', $card->fresh()->documentLabel());
        $card->update(['status' => 'divalidasi']);
        $this->assertEquals('Disetujui', $card->fresh()->documentLabel());
        $card->update(['status' => 'diterbitkan']);
        $this->assertEquals('Diterbitkan', $card->fresh()->documentLabel());
        $card->update(['status' => 'dikunci']);
        $this->assertEquals('Terkunci', $card->fresh()->documentLabel());
    }
}
