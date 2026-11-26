<?php

namespace Tests\Feature\P5;

use App\Models\AcademicYear;
use App\Models\AssessmentComponent;
use App\Models\AssessmentScore;
use App\Models\Department;
use App\Models\HomeroomAssignment;
use App\Models\P5Project;
use App\Models\P5Score;
use App\Models\ReportCard;
use App\Models\ReportCardNote;
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

/**
 * Fase C (RDM): proyek P5 yang dikelola admin, capaian diisi
 * koordinator (guru) secara massal per siswa, tampil di preview
 * rapor dan PDF rapor.
 */
class P5Test extends TestCase
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
        $tch = Teacher::create(['nip' => '123', 'name' => 'Koordinator 1']);
        $tch2 = Teacher::create(['nip' => '124', 'name' => 'Guru Lain']);

        return [$dep, $ay, $sem1, $sem2, $cls, $sub, $tch, $tch2];
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

    private function makeProject($cls, $ay, $sem, $tch, string $tema = 'Gaya Hidup Berkelanjutan', string $dimensi = 'Bernalar Kritis'): P5Project
    {
        return P5Project::create([
            'tema' => $tema,
            'dimensi' => $dimensi,
            'academic_year_id' => $ay->id,
            'semester_id' => $sem->id,
            'class_id' => $cls->id,
            'coordinator_id' => $tch->id,
        ]);
    }

    private function projectPayload($cls, $ay, $sem, $tch): array
    {
        return [
            'tema' => 'Gaya Hidup Berkelanjutan',
            'dimensi' => 'Bernalar Kritis',
            'academic_year_id' => $ay->id,
            'semester_id' => $sem->id,
            'class_id' => $cls->id,
            'coordinator_id' => $tch->id,
        ];
    }

    private function scoresPayload($stu1, $stu2, $stu3): array
    {
        return [
            'records' => [
                ['student_id' => $stu1->id, 'capaian' => 'Mampu memilah sampah dengan benar.', 'deskripsi' => 'Siswa aktif dalam kegiatan bank sampah.'],
                ['student_id' => $stu2->id, 'capaian' => 'Mampu membuat kompos sederhana.', 'deskripsi' => null],
                // Siswa 3: capaian + deskripsi kosong -> dilewati.
                ['student_id' => $stu3->id, 'capaian' => null, 'deskripsi' => null],
            ],
        ];
    }

    public function test_admin_p5_index_200_and_guru_403(): void
    {
        [, $ay, $sem1, , $cls, , $tch] = $this->seedParents();
        $project = $this->makeProject($cls, $ay, $sem1, $tch);

        $this->loginAs('admin');
        $res = $this->get('/admin/p5-projects');
        $res->assertStatus(200);
        $res->assertSee('Gaya Hidup Berkelanjutan');
        $res->assertSee('Bernalar Kritis');
        $res->assertSee('Koordinator 1');

        // Guru tidak boleh membuka halaman admin.
        $this->loginAs('guru', $tch->id);
        $res = $this->get('/admin/p5-projects');
        $res->assertForbidden();

        $this->assertDatabaseHas('p5_projects', ['id' => $project->id, 'tema' => 'Gaya Hidup Berkelanjutan']);
    }

    public function test_admin_can_create_project_and_validates_dimensi(): void
    {
        [, $ay, $sem1, , $cls, , $tch] = $this->seedParents();

        $this->loginAs('admin');

        $res = $this->post('/admin/p5-projects', $this->projectPayload($cls, $ay, $sem1, $tch));
        $res->assertSessionHas('success');
        $this->assertDatabaseHas('p5_projects', [
            'tema' => 'Gaya Hidup Berkelanjutan',
            'dimensi' => 'Bernalar Kritis',
            'class_id' => $cls->id,
            'coordinator_id' => $tch->id,
        ]);

        // Dimensi di luar 6 dimensi Profil Pelajar Pancasila ditolak.
        $payload = $this->projectPayload($cls, $ay, $sem1, $tch);
        $payload['tema'] = 'Tema Lain';
        $payload['dimensi'] = 'Disiplin';

        $res = $this->post('/admin/p5-projects', $payload);
        $res->assertSessionHasErrors('dimensi');
        $this->assertEquals(1, P5Project::count());
    }

    public function test_coordinator_my_p5_page_and_non_coordinator_forbidden(): void
    {
        [, $ay, $sem1, , $cls, , $tch, $tch2] = $this->seedParents();
        $project = $this->makeProject($cls, $ay, $sem1, $tch);
        $stu1 = $this->makeStudent('001', 'Siswa 1', $cls->id);
        $stu2 = $this->makeStudent('002', 'Siswa 2', $cls->id);
        $stu3 = $this->makeStudent('003', 'Siswa 3', $cls->id);

        // Koordinator: daftar "P5 Saya" dan halaman isi capaian 200.
        $this->loginAs('guru', $tch->id);

        $res = $this->get('/guru/p5');
        $res->assertStatus(200);
        $res->assertSee('Gaya Hidup Berkelanjutan');

        $res = $this->get("/guru/p5/{$project->id}");
        $res->assertStatus(200);
        $res->assertSee('Siswa 1');
        $res->assertSee('Gaya Hidup Berkelanjutan');

        // Guru lain (bukan koordinator): daftarnya kosong, dan
        // membuka/mengisi proyek orang lain -> 403.
        $this->loginAs('guru', $tch2->id);

        $res = $this->get('/guru/p5');
        $res->assertStatus(200);
        $res->assertDontSee('Gaya Hidup Berkelanjutan');

        $res = $this->get("/guru/p5/{$project->id}");
        $res->assertForbidden();

        $res = $this->post("/guru/p5/{$project->id}/scores", $this->scoresPayload($stu1, $stu2, $stu3));
        $res->assertForbidden();
        $this->assertEquals(0, P5Score::count());
    }

    public function test_bulk_store_scores_saves_and_upserts(): void
    {
        [, $ay, $sem1, , $cls, , $tch] = $this->seedParents();
        $project = $this->makeProject($cls, $ay, $sem1, $tch);
        $stu1 = $this->makeStudent('001', 'Siswa 1', $cls->id);
        $stu2 = $this->makeStudent('002', 'Siswa 2', $cls->id);
        $stu3 = $this->makeStudent('003', 'Siswa 3', $cls->id);

        $this->loginAs('guru', $tch->id);

        $res = $this->post("/guru/p5/{$project->id}/scores", $this->scoresPayload($stu1, $stu2, $stu3));
        $res->assertSessionHas('success');

        // Siswa 1 + 2 tersimpan; siswa 3 (kosong) dilewati.
        $this->assertEquals(2, P5Score::count());
        $this->assertDatabaseHas('p5_scores', [
            'p5_project_id' => $project->id, 'student_id' => $stu1->id,
            'capaian' => 'Mampu memilah sampah dengan benar.',
            'deskripsi' => 'Siswa aktif dalam kegiatan bank sampah.',
        ]);
        $this->assertDatabaseHas('p5_scores', [
            'p5_project_id' => $project->id, 'student_id' => $stu2->id,
            'capaian' => 'Mampu membuat kompos sederhana.',
        ]);
        $this->assertDatabaseMissing('p5_scores', [
            'p5_project_id' => $project->id, 'student_id' => $stu3->id,
        ]);

        // Simpan ulang: upsert memperbarui, bukan menambah baris.
        $payload = $this->scoresPayload($stu1, $stu2, $stu3);
        $payload['records'][0]['capaian'] = 'Mampu memilah dan mengolah sampah dengan benar.';
        $payload['records'][0]['deskripsi'] = 'Siswa sangat aktif dalam kegiatan bank sampah.';

        $res = $this->post("/guru/p5/{$project->id}/scores", $payload);
        $res->assertSessionHas('success');
        $this->assertEquals(2, P5Score::count());
        $this->assertDatabaseHas('p5_scores', [
            'p5_project_id' => $project->id, 'student_id' => $stu1->id,
            'capaian' => 'Mampu memilah dan mengolah sampah dengan benar.',
            'deskripsi' => 'Siswa sangat aktif dalam kegiatan bank sampah.',
        ]);
    }

    public function test_bulk_store_rejects_student_from_other_class(): void
    {
        [$dep, $ay, $sem1, , $cls, , $tch] = $this->seedParents();
        $cls2 = SchoolClass::create(['department_id' => $dep->id, 'academic_year_id' => $ay->id, 'name' => 'X TKJ 2', 'level' => 'X']);
        $project = $this->makeProject($cls, $ay, $sem1, $tch);
        $stu1 = $this->makeStudent('001', 'Siswa 1', $cls->id);
        $stuOther = $this->makeStudent('002', 'Siswa Lain', $cls2->id);
        $stu3 = $this->makeStudent('003', 'Siswa 3', $cls->id);

        $this->loginAs('guru', $tch->id);

        $res = $this->post("/guru/p5/{$project->id}/scores", $this->scoresPayload($stu1, $stuOther, $stu3));
        $res->assertSessionHasErrors('records');
        $this->assertEquals(0, P5Score::count());
    }

    public function test_preview_shows_p5_and_dash_when_empty(): void
    {
        [, $ay, $sem1, , $cls, $sub, $tch] = $this->seedParents();
        $stu1 = $this->makeStudent('001', 'Siswa 1', $cls->id);
        $stu2 = $this->makeStudent('002', 'Siswa 2', $cls->id);
        $this->makeHomeroom($tch, $cls, $ay, $sem1);
        $this->makeTeaching($tch, $sub, $cls, $ay, $sem1);
        $component = $this->makeComponent($sub);
        $this->seedComplete($stu1, $sub, $component, $cls, $ay, $sem1, $tch);

        $project = $this->makeProject($cls, $ay, $sem1, $tch);
        P5Score::create([
            'p5_project_id' => $project->id, 'student_id' => $stu1->id,
            'capaian' => 'Mampu memilah sampah dengan benar.',
            'deskripsi' => 'Siswa aktif dalam kegiatan bank sampah.',
        ]);

        $this->loginAs('wali_kelas', $tch->id);

        $res = $this->get("/wali/preview/{$stu1->id}?academic_year_id={$ay->id}&semester_id={$sem1->id}");
        $res->assertStatus(200);
        $res->assertSee('P5 (Projek Penguatan Profil Pelajar Pancasila)');
        $res->assertSee('Gaya Hidup Berkelanjutan');
        $res->assertSee('Bernalar Kritis');
        $res->assertSee('Mampu memilah sampah dengan benar.');
        $res->assertSee('Siswa aktif dalam kegiatan bank sampah.');

        // Siswa tanpa nilai P5: bagian P5 tetap tampil dengan pesan kosong.
        $res = $this->get("/wali/preview/{$stu2->id}?academic_year_id={$ay->id}&semester_id={$sem1->id}");
        $res->assertStatus(200);
        $res->assertViewHas('p5Scores', fn ($s) => $s->isEmpty());
        $res->assertSee('Belum ada nilai P5 pada periode ini.');
    }

    public function test_pdf_data_contains_p5_and_pdf_downloads(): void
    {
        [, $ay, $sem1, , $cls, $sub, $tch] = $this->seedParents();
        $stu = $this->makeStudent('001', 'Siswa 1', $cls->id);
        $this->makeHomeroom($tch, $cls, $ay, $sem1);
        $this->makeTeaching($tch, $sub, $cls, $ay, $sem1);
        $component = $this->makeComponent($sub);
        $card = $this->seedComplete($stu, $sub, $component, $cls, $ay, $sem1, $tch);

        $project = $this->makeProject($cls, $ay, $sem1, $tch);
        P5Score::create([
            'p5_project_id' => $project->id, 'student_id' => $stu->id,
            'capaian' => 'Mampu memilah sampah dengan benar.',
            'deskripsi' => 'Siswa aktif dalam kegiatan bank sampah.',
        ]);

        $card = $this->publishFlow($card, $stu, $cls, $ay, $sem1, $tch);
        $this->assertEquals('diterbitkan', $card->status);

        $data = app(ReportCardService::class)->pdfData($card->fresh());
        $this->assertCount(1, $data['p5Scores']);
        $this->assertEquals('Gaya Hidup Berkelanjutan', $data['p5Scores'][0]->project->tema);
        $this->assertEquals('Bernalar Kritis', $data['p5Scores'][0]->project->dimensi);
        $this->assertEquals('Mampu memilah sampah dengan benar.', $data['p5Scores'][0]->capaian);

        $this->loginAs('admin');
        $res = $this->get("/admin/report-cards/{$card->id}/pdf");
        $res->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $res->headers->get('Content-Type'));
        $this->assertStringStartsWith('%PDF', $res->getContent());
    }

    public function test_p5_does_not_block_submission(): void
    {
        [, $ay, $sem1, , $cls, $sub, $tch] = $this->seedParents();
        $stu = $this->makeStudent('001', 'Siswa 1', $cls->id);
        $this->makeHomeroom($tch, $cls, $ay, $sem1);
        $this->makeTeaching($tch, $sub, $cls, $ay, $sem1);
        $component = $this->makeComponent($sub);
        $card = $this->seedComplete($stu, $sub, $component, $cls, $ay, $sem1, $tch);

        // Tanpa proyek/nilai P5 sama sekali, rapor lengkap tetap bisa diajukan.
        $this->loginAs('wali_kelas', $tch->id);
        $res = $this->post('/wali/submit', [
            'student_id' => $stu->id, 'class_id' => $cls->id,
            'academic_year_id' => $ay->id, 'semester_id' => $sem1->id,
        ]);
        $res->assertSessionHas('success');
        $this->assertEquals('diajukan', $card->fresh()->status);
    }
}
