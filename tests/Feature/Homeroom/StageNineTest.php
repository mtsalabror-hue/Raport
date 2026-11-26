<?php

namespace Tests\Feature\Homeroom;

use App\Models\AcademicYear;
use App\Models\ActivityLog;
use App\Models\AssessmentComponent;
use App\Models\AssessmentScore;
use App\Models\Department;
use App\Models\Extracurricular;
use App\Models\HomeroomAssignment;
use App\Models\ReportCard;
use App\Models\ReportCardNote;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentAchievement;
use App\Models\StudentAttendance;
use App\Models\StudentExtracurricular;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherDescription;
use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StageNineTest extends TestCase
{
    use RefreshDatabase;

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
        // Model Student hanya mengizinkan mass-assignment nis/nisn/name,
        // jadi class_id diisi lewat assignment langsung.
        $stu = Student::create(['nis' => $nis, 'nisn' => 'N'.$nis, 'name' => $name]);
        $stu->class_id = $classId;
        $stu->gender = 'L';
        $stu->save();

        return $stu->fresh();
    }

    private function loginAs(string $roleSlug, ?int $teacherId = null): User
    {
        $role = Role::firstOrCreate(['slug' => $roleSlug], ['name' => $roleSlug]);
        $user = User::create([
            'name' => ucfirst($roleSlug),
            'email' => $roleSlug.($teacherId ?? 1).'@smk.test',
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
     * Lengkapi seluruh komponen wajib rapor satu siswa:
     * nilai terkirim + deskripsi + presensi + catatan wali.
     */
    private function seedComplete($stu, $sub, $component, $cls, $ay, $sem, $tch): ReportCard
    {
        $this->seedSubmittedScore($stu, $sub, $component, $cls, $ay, $sem, $tch);

        TeacherDescription::create([
            'student_id' => $stu->id, 'subject_id' => $sub->id, 'teacher_id' => $tch->id,
            'academic_year_id' => $ay->id, 'semester_id' => $sem->id,
            'description' => 'Mencapai KKTP pada Matematika dengan nilai akhir 80,00.',
        ]);

        StudentAttendance::create([
            'student_id' => $stu->id, 'class_id' => $cls->id,
            'academic_year_id' => $ay->id, 'semester_id' => $sem->id,
            'date' => '2025-08-04', 'status' => 'H',
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

    public function test_wali_dashboard_only_own_class(): void
    {
        [$dep, $ay, $sem1, , $cls, , $tch] = $this->seedParents();
        $cls2 = SchoolClass::create(['department_id' => $dep->id, 'academic_year_id' => $ay->id, 'name' => 'X TKJ 2', 'level' => 'X']);
        $this->makeStudent('001', 'Siswa 1', $cls->id);
        $this->makeHomeroom($tch, $cls, $ay, $sem1);

        $this->loginAs('wali_kelas', $tch->id);

        $res = $this->get("/wali/dashboard?class_id={$cls->id}&academic_year_id={$ay->id}&semester_id={$sem1->id}");
        $res->assertStatus(200);
        $res->assertViewHas('stats', fn ($s) => $s['total_siswa'] === 1 && $s['lengkap'] === 0);

        // Kelas wali lain -> 403.
        $res = $this->get("/wali/dashboard?class_id={$cls2->id}&academic_year_id={$ay->id}&semester_id={$sem1->id}");
        $res->assertForbidden();

        $res = $this->get("/wali/students?class_id={$cls2->id}&academic_year_id={$ay->id}&semester_id={$sem1->id}");
        $res->assertForbidden();
    }

    public function test_wali_cannot_change_scores(): void
    {
        [, $ay, $sem1, , $cls, $sub, $tch] = $this->seedParents();
        $stu = $this->makeStudent('001', 'Siswa 1', $cls->id);
        $component = $this->makeComponent($sub);
        $this->makeHomeroom($tch, $cls, $ay, $sem1);

        // Wali kelas tidak punya role guru, jadi endpoint guru menolak.
        $this->loginAs('wali_kelas', $tch->id);

        $res = $this->post('/guru/assessments', [
            'student_id' => $stu->id, 'subject_id' => $sub->id, 'component_id' => $component->id,
            'class_id' => $cls->id, 'academic_year_id' => $ay->id, 'semester_id' => $sem1->id,
            'score' => 90,
        ]);
        $res->assertForbidden();

        $res = $this->post('/guru/assessments/bulk', [
            'subject_id' => $sub->id, 'class_id' => $cls->id,
            'academic_year_id' => $ay->id, 'semester_id' => $sem1->id,
            'scores' => [['student_id' => $stu->id, 'component_id' => $component->id, 'score' => 90]],
        ]);
        $res->assertForbidden();

        $this->assertEquals(0, AssessmentScore::count());
    }

    public function test_completeness_incomplete_lists_missing_items(): void
    {
        [, $ay, $sem1, , $cls, $sub, $tch] = $this->seedParents();
        $stu = $this->makeStudent('001', 'Siswa 1', $cls->id);
        $this->makeHomeroom($tch, $cls, $ay, $sem1);
        $this->makeTeaching($tch, $sub, $cls, $ay, $sem1);

        $this->loginAs('wali_kelas', $tch->id);
        $res = $this->get("/wali/completeness?class_id={$cls->id}&academic_year_id={$ay->id}&semester_id={$sem1->id}");
        $res->assertStatus(200);
        $res->assertViewHas('stats', fn ($s) => $s['total'] === 1 && $s['lengkap'] === 0 && $s['belum_lengkap'] === 1);
        $res->assertViewHas('checks', function ($checks) use ($stu) {
            $check = collect($checks)->firstWhere('student.id', $stu->id);
            $codes = collect($check['missing'])->pluck('code')->all();

            return $check['complete'] === false
                && in_array('nilai_belum_terkirim', $codes, true)
                && in_array('presensi_belum_ada', $codes, true)
                && in_array('catatan_wali_belum_ada', $codes, true)
                // Setiap kekurangan membawa tautan ke sumber datanya.
                && collect($check['missing'])->every(fn ($m) => ! empty($m['url']) && ! empty($m['message']));
        });
    }

    public function test_completeness_flags_missing_description_when_score_submitted(): void
    {
        [, $ay, $sem1, , $cls, $sub, $tch] = $this->seedParents();
        $stu = $this->makeStudent('001', 'Siswa 1', $cls->id);
        $this->makeHomeroom($tch, $cls, $ay, $sem1);
        $this->makeTeaching($tch, $sub, $cls, $ay, $sem1);
        $component = $this->makeComponent($sub);
        $this->seedSubmittedScore($stu, $sub, $component, $cls, $ay, $sem1, $tch);

        $this->loginAs('wali_kelas', $tch->id);
        $res = $this->get("/wali/completeness?class_id={$cls->id}&academic_year_id={$ay->id}&semester_id={$sem1->id}");
        $res->assertViewHas('checks', function ($checks) {
            $codes = collect($checks[0]['missing'])->pluck('code')->all();

            return in_array('deskripsi_belum_ada', $codes, true)
                && ! in_array('nilai_belum_terkirim', $codes, true);
        });
    }

    public function test_completeness_complete_when_all_components_present(): void
    {
        [, $ay, $sem1, , $cls, $sub, $tch] = $this->seedParents();
        $stu = $this->makeStudent('001', 'Siswa 1', $cls->id);
        $this->makeHomeroom($tch, $cls, $ay, $sem1);
        $this->makeTeaching($tch, $sub, $cls, $ay, $sem1);
        $component = $this->makeComponent($sub);
        $this->seedComplete($stu, $sub, $component, $cls, $ay, $sem1, $tch);

        $this->loginAs('wali_kelas', $tch->id);
        $res = $this->get("/wali/completeness?class_id={$cls->id}&academic_year_id={$ay->id}&semester_id={$sem1->id}");
        $res->assertStatus(200);
        $res->assertViewHas('stats', fn ($s) => $s['lengkap'] === 1 && $s['belum_lengkap'] === 0);
        $res->assertViewHas('checks', fn ($checks) => $checks[0]['complete'] === true && $checks[0]['missing'] === []);
    }

    public function test_note_crud_creates_draft_report_card(): void
    {
        [, $ay, $sem1, , $cls, , $tch] = $this->seedParents();
        $stu = $this->makeStudent('001', 'Siswa 1', $cls->id);
        $this->makeHomeroom($tch, $cls, $ay, $sem1);

        $this->loginAs('wali_kelas', $tch->id);

        $res = $this->post('/wali/notes', [
            'student_id' => $stu->id, 'academic_year_id' => $ay->id, 'semester_id' => $sem1->id,
            'content' => 'Siswa rajin dan disiplin.',
        ]);
        $res->assertSessionHas('success');

        // Menyimpan catatan otomatis membuat rapor draft.
        $card = ReportCard::where('student_id', $stu->id)->first();
        $this->assertNotNull($card);
        $this->assertEquals('draft', $card->status);
        $note = ReportCardNote::where('report_card_id', $card->id)->first();
        $this->assertNotNull($note);
        $this->assertEquals('wali_kelas', $note->note_type);
        $this->assertEquals('Siswa rajin dan disiplin.', $note->content);
        $this->assertDatabaseHas('activity_logs', ['action' => 'report_card_note.created']);

        $res = $this->put("/wali/notes/{$note->id}", ['content' => 'Siswa sangat rajin.']);
        $res->assertSessionHas('success');
        $this->assertEquals('Siswa sangat rajin.', $note->fresh()->content);
        $this->assertDatabaseHas('activity_logs', ['action' => 'report_card_note.updated']);

        $res = $this->delete("/wali/notes/{$note->id}");
        $res->assertSessionHas('success');
        $this->assertDatabaseMissing('report_card_notes', ['id' => $note->id]);
        $this->assertDatabaseHas('activity_logs', ['action' => 'report_card_note.deleted']);
    }

    public function test_note_for_other_class_forbidden(): void
    {
        [$dep, $ay, $sem1, , $cls, , $tch] = $this->seedParents();
        $cls2 = SchoolClass::create(['department_id' => $dep->id, 'academic_year_id' => $ay->id, 'name' => 'X TKJ 2', 'level' => 'X']);
        $stuOther = $this->makeStudent('002', 'Siswa Lain', $cls2->id);
        $this->makeHomeroom($tch, $cls, $ay, $sem1);

        $this->loginAs('wali_kelas', $tch->id);
        $res = $this->post('/wali/notes', [
            'student_id' => $stuOther->id, 'academic_year_id' => $ay->id, 'semester_id' => $sem1->id,
            'content' => 'Tidak boleh.',
        ]);
        $res->assertForbidden();
        $this->assertEquals(0, ReportCardNote::count());
    }

    public function test_achievement_crud(): void
    {
        [, $ay, $sem1, , $cls, , $tch] = $this->seedParents();
        $stu = $this->makeStudent('001', 'Siswa 1', $cls->id);
        $this->makeHomeroom($tch, $cls, $ay, $sem1);

        $this->loginAs('wali_kelas', $tch->id);

        $res = $this->post('/wali/achievements', [
            'student_id' => $stu->id, 'academic_year_id' => $ay->id, 'semester_id' => $sem1->id,
            'title' => 'Juara 1 Lomba Coding', 'level' => 'Kabupaten', 'description' => 'Lomba antar SMK.',
        ]);
        $res->assertSessionHas('success');
        $item = StudentAchievement::first();
        $this->assertNotNull($item);
        $this->assertEquals('Juara 1 Lomba Coding', $item->title);
        $this->assertDatabaseHas('activity_logs', ['action' => 'student_achievement.created']);

        $res = $this->put("/wali/achievements/{$item->id}", [
            'title' => 'Juara 1 Lomba Coding', 'level' => 'Provinsi', 'description' => 'Lomba antar SMK.',
        ]);
        $res->assertSessionHas('success');
        $this->assertEquals('Provinsi', $item->fresh()->level);

        $res = $this->delete("/wali/achievements/{$item->id}");
        $res->assertSessionHas('success');
        $this->assertEquals(0, StudentAchievement::count());
    }

    public function test_extracurricular_crud_and_duplicate_rejected(): void
    {
        [, $ay, $sem1, , $cls, , $tch] = $this->seedParents();
        $stu = $this->makeStudent('001', 'Siswa 1', $cls->id);
        $this->makeHomeroom($tch, $cls, $ay, $sem1);

        $this->loginAs('wali_kelas', $tch->id);

        // Data master dibuat dulu oleh wali.
        $res = $this->post('/wali/extracurriculars/master', ['name' => 'Pramuka', 'description' => 'Kegiatan kepramukaan.']);
        $res->assertSessionHas('success');
        $master = Extracurricular::where('name', 'Pramuka')->first();
        $this->assertNotNull($master);

        $payload = [
            'student_id' => $stu->id, 'extracurricular_id' => $master->id,
            'academic_year_id' => $ay->id, 'semester_id' => $sem1->id,
            'score' => 85, 'description' => 'Aktif.',
        ];

        $res = $this->post('/wali/extracurriculars', $payload);
        $res->assertSessionHas('success');
        $this->assertEquals(1, StudentExtracurricular::count());

        // Duplikasi siswa + ekskul + periode ditolak, tidak ada data ganda.
        $res = $this->post('/wali/extracurriculars', $payload);
        $res->assertSessionHasErrors('extracurricular_id');
        $this->assertEquals(1, StudentExtracurricular::count());

        $record = StudentExtracurricular::first();
        $res = $this->put("/wali/extracurriculars/{$record->id}", ['score' => 90, 'description' => 'Sangat aktif.']);
        $res->assertSessionHas('success');
        $this->assertEquals(90.0, (float) $record->fresh()->score);

        $res = $this->delete("/wali/extracurriculars/{$record->id}");
        $res->assertSessionHas('success');
        $this->assertEquals(0, StudentExtracurricular::count());
    }

    public function test_preview_shows_all_components(): void
    {
        [, $ay, $sem1, , $cls, $sub, $tch] = $this->seedParents();
        $stu = $this->makeStudent('001', 'Siswa 1', $cls->id);
        $this->makeHomeroom($tch, $cls, $ay, $sem1);
        $this->makeTeaching($tch, $sub, $cls, $ay, $sem1);
        $component = $this->makeComponent($sub);
        $this->seedComplete($stu, $sub, $component, $cls, $ay, $sem1, $tch);

        StudentAchievement::create([
            'student_id' => $stu->id, 'academic_year_id' => $ay->id, 'semester_id' => $sem1->id,
            'title' => 'Juara 1 Lomba Coding', 'level' => 'Kabupaten',
        ]);
        $master = Extracurricular::create(['name' => 'Pramuka']);
        StudentExtracurricular::create([
            'student_id' => $stu->id, 'extracurricular_id' => $master->id,
            'academic_year_id' => $ay->id, 'semester_id' => $sem1->id, 'score' => 85,
        ]);

        $this->loginAs('wali_kelas', $tch->id);
        $res = $this->get("/wali/preview/{$stu->id}?academic_year_id={$ay->id}&semester_id={$sem1->id}");
        $res->assertStatus(200);
        $res->assertSee('Siswa 1');
        $res->assertSee('Matematika');
        $res->assertSee('80,00');
        $res->assertSee('Mencapai KKTP pada Matematika');
        $res->assertSee('Hadir 1');
        $res->assertSee('Juara 1 Lomba Coding');
        $res->assertSee('Pramuka');
        $res->assertSee('Siswa rajin dan disiplin.');
        $res->assertSee('Lengkap');
    }

    public function test_preview_other_class_forbidden(): void
    {
        [$dep, $ay, $sem1, , $cls, , $tch] = $this->seedParents();
        $cls2 = SchoolClass::create(['department_id' => $dep->id, 'academic_year_id' => $ay->id, 'name' => 'X TKJ 2', 'level' => 'X']);
        $stuOther = $this->makeStudent('002', 'Siswa Lain', $cls2->id);
        $this->makeHomeroom($tch, $cls, $ay, $sem1);

        $this->loginAs('wali_kelas', $tch->id);
        $res = $this->get("/wali/preview/{$stuOther->id}?academic_year_id={$ay->id}&semester_id={$sem1->id}");
        $res->assertForbidden();
    }

    public function test_submit_rejected_when_incomplete(): void
    {
        [, $ay, $sem1, , $cls, $sub, $tch] = $this->seedParents();
        $stu = $this->makeStudent('001', 'Siswa 1', $cls->id);
        $this->makeHomeroom($tch, $cls, $ay, $sem1);
        $this->makeTeaching($tch, $sub, $cls, $ay, $sem1);

        $this->loginAs('wali_kelas', $tch->id);
        $res = $this->post('/wali/submit', [
            'student_id' => $stu->id, 'class_id' => $cls->id,
            'academic_year_id' => $ay->id, 'semester_id' => $sem1->id,
        ]);
        $res->assertSessionHasErrors('completeness');
        $this->assertDatabaseMissing('activity_logs', ['action' => 'report_card.submitted']);

        // Rapor tetap draft (atau belum ada) — tidak berubah ke diajukan.
        $card = ReportCard::where('student_id', $stu->id)->first();
        $this->assertTrue($card === null || $card->status === 'draft');
    }

    public function test_submit_succeeds_when_complete_and_logs_submitter(): void
    {
        [, $ay, $sem1, , $cls, $sub, $tch] = $this->seedParents();
        $stu = $this->makeStudent('001', 'Siswa 1', $cls->id);
        $this->makeHomeroom($tch, $cls, $ay, $sem1);
        $this->makeTeaching($tch, $sub, $cls, $ay, $sem1);
        $component = $this->makeComponent($sub);
        $card = $this->seedComplete($stu, $sub, $component, $cls, $ay, $sem1, $tch);

        $user = $this->loginAs('wali_kelas', $tch->id);
        $res = $this->post('/wali/submit', [
            'student_id' => $stu->id, 'class_id' => $cls->id,
            'academic_year_id' => $ay->id, 'semester_id' => $sem1->id,
        ]);
        $res->assertSessionHas('success');

        $this->assertEquals('diajukan', $card->fresh()->status);
        // issued_by tetap kosong: itu kolom penerbit (Stage 10).
        $this->assertNull($card->fresh()->issued_by);

        $log = ActivityLog::where('action', 'report_card.submitted')->first();
        $this->assertNotNull($log);
        $this->assertEquals(ReportCard::class, $log->model_type);
        $this->assertEquals($card->id, $log->model_id);
        $this->assertEquals($user->id, $log->metadata['submitted_by']);
        $this->assertNotEmpty($log->metadata['submitted_at']);

        // Pengajuan kedua ditolak: rapor sudah diajukan.
        $res = $this->post('/wali/submit', [
            'student_id' => $stu->id, 'class_id' => $cls->id,
            'academic_year_id' => $ay->id, 'semester_id' => $sem1->id,
        ]);
        $res->assertSessionHasErrors('status');
        $this->assertEquals('diajukan', $card->fresh()->status);
        $this->assertEquals(1, ActivityLog::where('action', 'report_card.submitted')->count());
    }

    public function test_submit_other_class_forbidden(): void
    {
        [$dep, $ay, $sem1, , $cls, , $tch] = $this->seedParents();
        $cls2 = SchoolClass::create(['department_id' => $dep->id, 'academic_year_id' => $ay->id, 'name' => 'X TKJ 2', 'level' => 'X']);
        $stuOther = $this->makeStudent('002', 'Siswa Lain', $cls2->id);
        $this->makeHomeroom($tch, $cls, $ay, $sem1);

        $this->loginAs('wali_kelas', $tch->id);
        $res = $this->post('/wali/submit', [
            'student_id' => $stuOther->id, 'class_id' => $cls2->id,
            'academic_year_id' => $ay->id, 'semester_id' => $sem1->id,
        ]);
        $res->assertForbidden();
    }

    public function test_history_across_semesters(): void
    {
        [, $ay, $sem1, $sem2, $cls, $sub, $tch] = $this->seedParents();
        $stu = $this->makeStudent('001', 'Siswa 1', $cls->id);
        $this->makeHomeroom($tch, $cls, $ay, $sem1);
        $this->makeHomeroom($tch, $cls, $ay, $sem2);
        $component = $this->makeComponent($sub);

        // Semester 1: nilai 80, hadir 1x, prestasi.
        $this->seedSubmittedScore($stu, $sub, $component, $cls, $ay, $sem1, $tch, 80);
        StudentAttendance::create([
            'student_id' => $stu->id, 'class_id' => $cls->id,
            'academic_year_id' => $ay->id, 'semester_id' => $sem1->id,
            'date' => '2025-08-04', 'status' => 'H',
        ]);
        StudentAchievement::create([
            'student_id' => $stu->id, 'academic_year_id' => $ay->id, 'semester_id' => $sem1->id,
            'title' => 'Juara 1 Lomba Coding', 'level' => 'Kabupaten',
        ]);

        // Semester 2: nilai 90, hadir 2x, tanpa prestasi.
        $this->seedSubmittedScore($stu, $sub, $component, $cls, $ay, $sem2, $tch, 90);
        StudentAttendance::create([
            'student_id' => $stu->id, 'class_id' => $cls->id,
            'academic_year_id' => $ay->id, 'semester_id' => $sem2->id,
            'date' => '2026-01-05', 'status' => 'H',
        ]);
        StudentAttendance::create([
            'student_id' => $stu->id, 'class_id' => $cls->id,
            'academic_year_id' => $ay->id, 'semester_id' => $sem2->id,
            'date' => '2026-01-06', 'status' => 'S',
        ]);

        $this->loginAs('wali_kelas', $tch->id);
        $res = $this->get("/wali/history/{$stu->id}");
        $res->assertStatus(200);
        $res->assertViewHas('periods', function ($periods) {
            return $periods->count() === 2
                && collect($periods)->contains(fn ($p) => $p['semester_id'] === 1 || $p['semester']->name === 'Ganjil')
                && collect($periods)->contains(fn ($p) => $p['semester']->name === 'Genap');
        });
        $res->assertSee('80,00');
        $res->assertSee('90,00');
        $res->assertSee('Juara 1 Lomba Coding');
        $res->assertSee('Ganjil');
        $res->assertSee('Genap');
    }
}
