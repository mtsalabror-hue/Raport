<?php

namespace Tests\Feature\Homeroom;

use App\Models\AcademicYear;
use App\Models\AssessmentComponent;
use App\Models\AssessmentScore;
use App\Models\Department;
use App\Models\HomeroomAssignment;
use App\Models\ReportCard;
use App\Models\ReportCardNote;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\StudentAttitude;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherDescription;
use App\Models\TeachingAssignment;
use App\Models\User;
use App\Services\ReportCardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Fase B (RDM): sikap sosial & spiritual yang diisi wali kelas
 * per siswa per semester, tampil di preview rapor dan PDF rapor.
 */
class StudentAttitudeTest extends TestCase
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

    private function publishFlow(ReportCard $card, $stu, $cls, $ay, $sem, $tch): ReportCard
    {
        $this->loginAs('wali_kelas', $tch->id);
        $this->post('/wali/submit', [
            'student_id' => $stu->id, 'class_id' => $cls->id,
            'academic_year_id' => $ay->id, 'semester_id' => $sem->id,
        ])->assertSessionHas('success');

        $this->loginAs('admin');
        $this->post("/admin/report-cards/{$card->id}/approve")->assertSessionHas('success');
        $this->post("/admin/report-cards/{$card->id}/publish")->assertSessionHas('success');

        return $card->fresh();
    }

    private function attitudesPayload($stu1, $stu2, $cls, $ay, $sem): array
    {
        return [
            'class_id' => $cls->id,
            'academic_year_id' => $ay->id,
            'semester_id' => $sem->id,
            'records' => [
                [
                    'student_id' => $stu1->id,
                    'sosial' => ['predikat' => 'Sangat Baik', 'deskripsi' => 'Sopan dan suka menolong teman.'],
                    'spiritual' => ['predikat' => 'Baik', 'deskripsi' => 'Rajin beribadah.'],
                ],
                [
                    'student_id' => $stu2->id,
                    'sosial' => ['predikat' => 'Cukup', 'deskripsi' => null],
                    'spiritual' => ['predikat' => null, 'deskripsi' => null],
                ],
            ],
        ];
    }

    public function test_wali_attitudes_page_only_own_class(): void
    {
        [$dep, $ay, $sem1, , $cls, , $tch] = $this->seedParents();
        $cls2 = SchoolClass::create(['department_id' => $dep->id, 'academic_year_id' => $ay->id, 'name' => 'X TKJ 2', 'level' => 'X']);
        $this->makeStudent('001', 'Siswa 1', $cls->id);
        $this->makeHomeroom($tch, $cls, $ay, $sem1);

        $this->loginAs('wali_kelas', $tch->id);

        $res = $this->get("/wali/attitudes?class_id={$cls->id}&academic_year_id={$ay->id}&semester_id={$sem1->id}");
        $res->assertStatus(200);
        $res->assertSee('Siswa 1');
        $res->assertSee('Sikap Sosial');

        // Kelas wali lain -> 403.
        $res = $this->get("/wali/attitudes?class_id={$cls2->id}&academic_year_id={$ay->id}&semester_id={$sem1->id}");
        $res->assertForbidden();
    }

    public function test_guru_cannot_open_or_store_attitudes(): void
    {
        [, $ay, $sem1, , $cls, , $tch] = $this->seedParents();
        $stu = $this->makeStudent('001', 'Siswa 1', $cls->id);
        $this->makeHomeroom($tch, $cls, $ay, $sem1);

        $this->loginAs('guru', $tch->id);

        $res = $this->get("/wali/attitudes?class_id={$cls->id}&academic_year_id={$ay->id}&semester_id={$sem1->id}");
        $res->assertForbidden();

        $res = $this->post('/wali/attitudes', $this->attitudesPayload($stu, $stu, $cls, $ay, $sem1));
        $res->assertForbidden();
        $this->assertEquals(0, StudentAttitude::count());
    }

    public function test_bulk_store_saves_both_jenis_and_upserts(): void
    {
        [, $ay, $sem1, , $cls, , $tch] = $this->seedParents();
        $stu1 = $this->makeStudent('001', 'Siswa 1', $cls->id);
        $stu2 = $this->makeStudent('002', 'Siswa 2', $cls->id);
        $this->makeHomeroom($tch, $cls, $ay, $sem1);

        $this->loginAs('wali_kelas', $tch->id);

        $res = $this->post('/wali/attitudes', $this->attitudesPayload($stu1, $stu2, $cls, $ay, $sem1));
        $res->assertSessionHas('success');

        // Siswa 1: sosial + spiritual; siswa 2: hanya sosial
        // (spiritual kosong dilewati, bukan dibuat).
        $this->assertEquals(3, StudentAttitude::count());
        $this->assertDatabaseHas('student_attitudes', [
            'student_id' => $stu1->id, 'academic_year_id' => $ay->id, 'semester_id' => $sem1->id,
            'jenis' => 'sosial', 'predikat' => 'Sangat Baik', 'deskripsi' => 'Sopan dan suka menolong teman.',
        ]);
        $this->assertDatabaseHas('student_attitudes', [
            'student_id' => $stu1->id, 'jenis' => 'spiritual', 'predikat' => 'Baik', 'deskripsi' => 'Rajin beribadah.',
        ]);
        $this->assertDatabaseHas('student_attitudes', [
            'student_id' => $stu2->id, 'jenis' => 'sosial', 'predikat' => 'Cukup',
        ]);
        $this->assertDatabaseMissing('student_attitudes', [
            'student_id' => $stu2->id, 'jenis' => 'spiritual',
        ]);

        // Simpan ulang: upsert memperbarui, bukan menambah baris.
        $payload = $this->attitudesPayload($stu1, $stu2, $cls, $ay, $sem1);
        $payload['records'][0]['sosial']['predikat'] = 'Baik';
        $payload['records'][0]['sosial']['deskripsi'] = 'Sopan dan cukup aktif membantu teman.';

        $res = $this->post('/wali/attitudes', $payload);
        $res->assertSessionHas('success');
        $this->assertEquals(3, StudentAttitude::count());
        $this->assertDatabaseHas('student_attitudes', [
            'student_id' => $stu1->id, 'jenis' => 'sosial', 'predikat' => 'Baik',
            'deskripsi' => 'Sopan dan cukup aktif membantu teman.',
        ]);
    }

    public function test_bulk_store_rejects_student_from_other_class(): void
    {
        [$dep, $ay, $sem1, , $cls, , $tch] = $this->seedParents();
        $cls2 = SchoolClass::create(['department_id' => $dep->id, 'academic_year_id' => $ay->id, 'name' => 'X TKJ 2', 'level' => 'X']);
        $stu1 = $this->makeStudent('001', 'Siswa 1', $cls->id);
        $stuOther = $this->makeStudent('002', 'Siswa Lain', $cls2->id);
        $this->makeHomeroom($tch, $cls, $ay, $sem1);

        $this->loginAs('wali_kelas', $tch->id);

        $res = $this->post('/wali/attitudes', $this->attitudesPayload($stu1, $stuOther, $cls, $ay, $sem1));
        $res->assertSessionHasErrors('records');
        $this->assertEquals(0, StudentAttitude::count());
    }

    public function test_bulk_store_rejects_invalid_predikat(): void
    {
        [, $ay, $sem1, , $cls, , $tch] = $this->seedParents();
        $stu1 = $this->makeStudent('001', 'Siswa 1', $cls->id);
        $stu2 = $this->makeStudent('002', 'Siswa 2', $cls->id);
        $this->makeHomeroom($tch, $cls, $ay, $sem1);

        $this->loginAs('wali_kelas', $tch->id);

        $payload = $this->attitudesPayload($stu1, $stu2, $cls, $ay, $sem1);
        $payload['records'][0]['sosial']['predikat'] = 'Luar Biasa';

        $res = $this->post('/wali/attitudes', $payload);
        $res->assertSessionHasErrors('records.0.sosial.predikat');
        $this->assertEquals(0, StudentAttitude::count());
    }

    public function test_preview_shows_attitudes_and_dash_when_empty(): void
    {
        [, $ay, $sem1, , $cls, $sub, $tch] = $this->seedParents();
        $stu1 = $this->makeStudent('001', 'Siswa 1', $cls->id);
        $stu2 = $this->makeStudent('002', 'Siswa 2', $cls->id);
        $this->makeHomeroom($tch, $cls, $ay, $sem1);
        $this->makeTeaching($tch, $sub, $cls, $ay, $sem1);
        $component = $this->makeComponent($sub);
        $this->seedComplete($stu1, $sub, $component, $cls, $ay, $sem1, $tch);

        StudentAttitude::create([
            'student_id' => $stu1->id, 'academic_year_id' => $ay->id, 'semester_id' => $sem1->id,
            'jenis' => 'sosial', 'predikat' => 'Sangat Baik', 'deskripsi' => 'Sopan dan suka menolong teman.',
        ]);
        StudentAttitude::create([
            'student_id' => $stu1->id, 'academic_year_id' => $ay->id, 'semester_id' => $sem1->id,
            'jenis' => 'spiritual', 'predikat' => 'Baik', 'deskripsi' => 'Rajin beribadah.',
        ]);

        $this->loginAs('wali_kelas', $tch->id);

        $res = $this->get("/wali/preview/{$stu1->id}?academic_year_id={$ay->id}&semester_id={$sem1->id}");
        $res->assertStatus(200);
        $res->assertSee('Sikap');
        $res->assertSee('Sikap Sosial');
        $res->assertSee('Sangat Baik');
        $res->assertSee('Sopan dan suka menolong teman.');
        $res->assertSee('Sikap Spiritual');
        $res->assertSee('Rajin beribadah.');

        // Siswa tanpa sikap: bagian Sikap tetap tampil dengan '-'.
        $res = $this->get("/wali/preview/{$stu2->id}?academic_year_id={$ay->id}&semester_id={$sem1->id}");
        $res->assertStatus(200);
        $res->assertViewHas('attitudes', fn ($a) => $a->isEmpty());
        $res->assertSee('Sikap Sosial');
    }

    public function test_pdf_data_contains_attitudes_and_pdf_downloads(): void
    {
        [, $ay, $sem1, , $cls, $sub, $tch] = $this->seedParents();
        $stu = $this->makeStudent('001', 'Siswa 1', $cls->id);
        $this->makeHomeroom($tch, $cls, $ay, $sem1);
        $this->makeTeaching($tch, $sub, $cls, $ay, $sem1);
        $component = $this->makeComponent($sub);
        $card = $this->seedComplete($stu, $sub, $component, $cls, $ay, $sem1, $tch);

        StudentAttitude::create([
            'student_id' => $stu->id, 'academic_year_id' => $ay->id, 'semester_id' => $sem1->id,
            'jenis' => 'sosial', 'predikat' => 'Sangat Baik', 'deskripsi' => 'Sopan dan suka menolong teman.',
        ]);
        StudentAttitude::create([
            'student_id' => $stu->id, 'academic_year_id' => $ay->id, 'semester_id' => $sem1->id,
            'jenis' => 'spiritual', 'predikat' => 'Baik', 'deskripsi' => 'Rajin beribadah.',
        ]);

        $card = $this->publishFlow($card, $stu, $cls, $ay, $sem1, $tch);
        $this->assertEquals('diterbitkan', $card->status);

        $data = app(ReportCardService::class)->pdfData($card->fresh());
        $this->assertEquals('Sangat Baik', $data['attitudes']['sosial']->predikat);
        $this->assertEquals('Sopan dan suka menolong teman.', $data['attitudes']['sosial']->deskripsi);
        $this->assertEquals('Baik', $data['attitudes']['spiritual']->predikat);

        $this->loginAs('admin');
        $res = $this->get("/admin/report-cards/{$card->id}/pdf");
        $res->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $res->headers->get('Content-Type'));
        $this->assertStringStartsWith('%PDF', $res->getContent());
    }

    public function test_admin_recap_read_only(): void
    {
        [, $ay, $sem1, , $cls, , $tch] = $this->seedParents();
        $stu = $this->makeStudent('001', 'Siswa 1', $cls->id);
        $this->makeHomeroom($tch, $cls, $ay, $sem1);

        StudentAttitude::create([
            'student_id' => $stu->id, 'academic_year_id' => $ay->id, 'semester_id' => $sem1->id,
            'jenis' => 'sosial', 'predikat' => 'Sangat Baik', 'deskripsi' => 'Sopan dan suka menolong teman.',
        ]);

        $this->loginAs('admin');
        $res = $this->get("/admin/attitudes/recap?class_id={$cls->id}&academic_year_id={$ay->id}&semester_id={$sem1->id}");
        $res->assertStatus(200);
        $res->assertSee('Siswa 1');
        $res->assertSee('Sangat Baik');
        $res->assertSee('Sopan dan suka menolong teman.');

        // Guru tidak boleh membuka rekap admin.
        $this->loginAs('guru', $tch->id);
        $res = $this->get("/admin/attitudes/recap?class_id={$cls->id}&academic_year_id={$ay->id}&semester_id={$sem1->id}");
        $res->assertForbidden();
    }

    public function test_attitudes_do_not_block_submission(): void
    {
        [, $ay, $sem1, , $cls, $sub, $tch] = $this->seedParents();
        $stu = $this->makeStudent('001', 'Siswa 1', $cls->id);
        $this->makeHomeroom($tch, $cls, $ay, $sem1);
        $this->makeTeaching($tch, $sub, $cls, $ay, $sem1);
        $component = $this->makeComponent($sub);
        $card = $this->seedComplete($stu, $sub, $component, $cls, $ay, $sem1, $tch);

        // Tanpa sikap sama sekali, rapor lengkap tetap bisa diajukan.
        $this->loginAs('wali_kelas', $tch->id);
        $res = $this->post('/wali/submit', [
            'student_id' => $stu->id, 'class_id' => $cls->id,
            'academic_year_id' => $ay->id, 'semester_id' => $sem1->id,
        ]);
        $res->assertSessionHas('success');
        $this->assertEquals('diajukan', $card->fresh()->status);
    }
}
