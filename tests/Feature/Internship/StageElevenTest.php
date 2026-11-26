<?php

namespace Tests\Feature\Internship;

use App\Models\AcademicYear;
use App\Models\ActivityLog;
use App\Models\AssessmentComponent;
use App\Models\AssessmentScore;
use App\Models\CompetencyExam;
use App\Models\Department;
use App\Models\HomeroomAssignment;
use App\Models\Internship;
use App\Models\InternshipAssessment;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingAssignment;
use App\Models\User;
use App\Services\AssessmentService;
use App\Services\CompetencyExamService;
use App\Services\InternshipService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StageElevenTest extends TestCase
{
    use RefreshDatabase;

    private static int $userSeq = 0;

    private function seedParents(): array
    {
        $dep = Department::create(['code' => 'TKJ', 'name' => 'Teknik Komputer dan Jaringan']);
        $ay = AcademicYear::create(['name' => '2025/2026', 'start_date' => '2025-07-01', 'end_date' => '2026-06-30', 'is_active' => true]);
        $sem1 = Semester::create(['academic_year_id' => $ay->id, 'name' => 'Ganjil', 'is_active' => true]);
        $cls = SchoolClass::create(['department_id' => $dep->id, 'academic_year_id' => $ay->id, 'name' => 'X TKJ 1', 'level' => 'X']);
        $cls2 = SchoolClass::create(['department_id' => $dep->id, 'academic_year_id' => $ay->id, 'name' => 'X TKJ 2', 'level' => 'X']);
        $sub = Subject::create(['code' => 'MTK', 'name' => 'Matematika']);
        $sub2 = Subject::create(['code' => 'INF', 'name' => 'Informatika']);
        $tch = Teacher::create(['nip' => '123', 'name' => 'Pembimbing Satu']);
        $tch2 = Teacher::create(['nip' => '456', 'name' => 'Pembimbing Dua']);

        return [$dep, $ay, $sem1, $cls, $cls2, $sub, $sub2, $tch, $tch2];
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

    private function makeInternship($stu, string $company, string $start, string $end, ?string $supervisor = null): Internship
    {
        return Internship::create([
            'student_id' => $stu->id, 'company_name' => $company,
            'start_date' => $start, 'end_date' => $end, 'supervisor' => $supervisor,
        ]);
    }

    private function placementPayload($stu, string $company = 'PT Maju Jaya', string $start = '2026-01-05', string $end = '2026-03-31', ?string $supervisor = 'Pembimbing Satu'): array
    {
        return [
            'student_id' => $stu->id, 'company_name' => $company,
            'start_date' => $start, 'end_date' => $end, 'supervisor' => $supervisor,
        ];
    }

    // -----------------------------------------------------------------
    // Admin: halaman + CRUD penempatan
    // -----------------------------------------------------------------

    public function test_admin_internship_pages_load(): void
    {
        [, $ay, $sem1, $cls, , , , $tch] = $this->seedParents();
        $stu = $this->makeStudent('001', 'Siswa 1', $cls->id);
        $this->makeInternship($stu, 'PT Maju Jaya', '2026-01-05', '2026-03-31', 'Pembimbing Satu');

        $this->loginAs('admin');

        $res = $this->get('/admin/internships');
        $res->assertStatus(200);
        $res->assertSee('PT Maju Jaya');
        $res->assertSee('Siswa 1');

        $res = $this->get('/admin/internships/monitoring');
        $res->assertStatus(200);
        $res->assertSee('PT Maju Jaya');

        $res = $this->get('/admin/internships/recap');
        $res->assertStatus(200);
        $res->assertSee('Siswa 1');
    }

    public function test_admin_creates_internship_and_logs(): void
    {
        [, , , $cls, , , ,] = $this->seedParents();
        $stu = $this->makeStudent('001', 'Siswa 1', $cls->id);

        $this->loginAs('admin');
        $res = $this->post('/admin/internships', $this->placementPayload($stu));
        $res->assertSessionHas('success');

        $this->assertDatabaseHas('internships', [
            'student_id' => $stu->id, 'company_name' => 'PT Maju Jaya',
            'supervisor' => 'Pembimbing Satu',
        ]);
        // Tanggal dicek lewat model (SQLite menyimpan kolom date
        // sebagai datetime, jadi assertDatabaseHas dengan 'Y-m-d'
        // tidak cocok).
        $created = Internship::first();
        $this->assertEquals('2026-01-05', $created->start_date->toDateString());
        $this->assertEquals('2026-03-31', $created->end_date->toDateString());
        $this->assertDatabaseHas('activity_logs', ['action' => 'internship.created']);
    }

    public function test_admin_updates_and_deletes_internship(): void
    {
        [, , , $cls, , , ,] = $this->seedParents();
        $stu = $this->makeStudent('001', 'Siswa 1', $cls->id);
        $item = $this->makeInternship($stu, 'PT Maju Jaya', '2026-01-05', '2026-03-31', 'Pembimbing Satu');

        $this->loginAs('admin');

        $res = $this->get("/admin/internships/{$item->id}/edit");
        $res->assertStatus(200);
        $res->assertSee('PT Maju Jaya');

        $res = $this->put("/admin/internships/{$item->id}", $this->placementPayload($stu, 'CV Sinar Abadi', '2026-01-05', '2026-03-31'));
        $res->assertSessionHas('success');
        $this->assertEquals('CV Sinar Abadi', $item->fresh()->company_name);
        $this->assertDatabaseHas('activity_logs', ['action' => 'internship.updated']);

        // Penilaian aspek ikut terhapus (cascade) saat penempatan dihapus.
        InternshipAssessment::create(['internship_id' => $item->id, 'aspect' => 'kedisiplinan', 'score' => 85]);
        $res = $this->delete("/admin/internships/{$item->id}");
        $res->assertSessionHas('success');
        $this->assertNull(Internship::find($item->id));
        $this->assertEquals(0, InternshipAssessment::count());
        $this->assertDatabaseHas('activity_logs', ['action' => 'internship.deleted']);
    }

    // -----------------------------------------------------------------
    // Tumpang tindih penempatan
    // -----------------------------------------------------------------

    public function test_overlap_partial_rejected(): void
    {
        [, , , $cls, , , ,] = $this->seedParents();
        $stu = $this->makeStudent('001', 'Siswa 1', $cls->id);
        $this->makeInternship($stu, 'PT Maju Jaya', '2026-01-05', '2026-03-31');

        $this->loginAs('admin');

        // Irisan sebagian di akhir periode pertama.
        $res = $this->post('/admin/internships', $this->placementPayload($stu, 'PT Lain', '2026-03-01', '2026-05-31'));
        $res->assertSessionHasErrors('start_date');
        $this->assertEquals(1, Internship::count());

        // Irisan sebagian di awal periode pertama.
        $res = $this->post('/admin/internships', $this->placementPayload($stu, 'PT Lain', '2025-12-01', '2026-01-10'));
        $res->assertSessionHasErrors('start_date');
        $this->assertEquals(1, Internship::count());
    }

    public function test_overlap_full_containment_rejected(): void
    {
        [, , , $cls, , , ,] = $this->seedParents();
        $stu = $this->makeStudent('001', 'Siswa 1', $cls->id);
        $this->makeInternship($stu, 'PT Maju Jaya', '2026-01-05', '2026-03-31');

        $this->loginAs('admin');

        // Periode baru sepenuhnya di dalam periode lama.
        $res = $this->post('/admin/internships', $this->placementPayload($stu, 'PT Lain', '2026-02-01', '2026-02-28'));
        $res->assertSessionHasErrors('start_date');

        // Periode baru sepenuhnya menelan periode lama.
        $res = $this->post('/admin/internships', $this->placementPayload($stu, 'PT Lain', '2025-12-01', '2026-06-30'));
        $res->assertSessionHasErrors('start_date');

        $this->assertEquals(1, Internship::count());
    }

    public function test_sequential_no_overlap_accepted(): void
    {
        [, , , $cls, , , ,] = $this->seedParents();
        $stu = $this->makeStudent('001', 'Siswa 1', $cls->id);
        $this->makeInternship($stu, 'PT Maju Jaya', '2026-01-05', '2026-03-31');

        $this->loginAs('admin');

        // Mulai sehari setelah periode pertama berakhir: diterima.
        $res = $this->post('/admin/internships', $this->placementPayload($stu, 'PT Lain', '2026-04-01', '2026-06-30'));
        $res->assertSessionHas('success');
        $this->assertEquals(2, Internship::count());

        // Berakhir sehari sebelum periode pertama dimulai: diterima.
        $res = $this->post('/admin/internships', $this->placementPayload($stu, 'PT Ketiga', '2025-10-01', '2026-01-04'));
        $res->assertSessionHas('success');
        $this->assertEquals(3, Internship::count());
    }

    public function test_overlap_same_dates_different_student_accepted(): void
    {
        [, , , $cls, , , ,] = $this->seedParents();
        $stu = $this->makeStudent('001', 'Siswa 1', $cls->id);
        $stu2 = $this->makeStudent('002', 'Siswa 2', $cls->id);
        $this->makeInternship($stu, 'PT Maju Jaya', '2026-01-05', '2026-03-31');

        $this->loginAs('admin');
        $res = $this->post('/admin/internships', $this->placementPayload($stu2, 'PT Maju Jaya', '2026-01-05', '2026-03-31'));
        $res->assertSessionHas('success');
        $this->assertEquals(2, Internship::count());
    }

    public function test_update_causing_overlap_rejected(): void
    {
        [, , , $cls, , , ,] = $this->seedParents();
        $stu = $this->makeStudent('001', 'Siswa 1', $cls->id);
        $this->makeInternship($stu, 'PT Maju Jaya', '2026-01-05', '2026-03-31');
        $second = $this->makeInternship($stu, 'PT Lain', '2026-04-01', '2026-06-30');

        $this->loginAs('admin');

        // Ubah penempatan kedua agar beririsan dengan yang pertama.
        $res = $this->put("/admin/internships/{$second->id}", $this->placementPayload($stu, 'PT Lain', '2026-03-01', '2026-06-30'));
        $res->assertSessionHasErrors('start_date');
        $this->assertEquals('2026-04-01', $second->fresh()->start_date->toDateString());

        // Ubah tanpa mengubah periode (diri sendiri dikecualikan): diterima.
        $res = $this->put("/admin/internships/{$second->id}", $this->placementPayload($stu, 'PT Lain Baru', '2026-04-01', '2026-06-30'));
        $res->assertSessionHas('success');
        $this->assertEquals('PT Lain Baru', $second->fresh()->company_name);
    }

    public function test_end_before_start_rejected(): void
    {
        [, , , $cls, , , ,] = $this->seedParents();
        $stu = $this->makeStudent('001', 'Siswa 1', $cls->id);

        $this->loginAs('admin');
        $res = $this->post('/admin/internships', $this->placementPayload($stu, 'PT Maju Jaya', '2026-03-31', '2026-01-05'));
        $res->assertSessionHasErrors('end_date');
        $this->assertEquals(0, Internship::count());
    }

    public function test_overlap_service_direct(): void
    {
        [, , , $cls, , , ,] = $this->seedParents();
        $stu = $this->makeStudent('001', 'Siswa 1', $cls->id);
        $item = $this->makeInternship($stu, 'PT Maju Jaya', '2026-01-05', '2026-03-31');

        $svc = app(InternshipService::class);
        $this->assertNotNull($svc->findOverlap($stu->id, '2026-03-01', '2026-05-31'));
        $this->assertNull($svc->findOverlap($stu->id, '2026-04-01', '2026-06-30'));
        // Diri sendiri dikecualikan saat ubah.
        $this->assertNull($svc->findOverlap($stu->id, '2026-01-05', '2026-03-31', $item->id));

        // Status periode dari tanggal.
        $this->assertEquals('selesai', $item->status(now()->addYear()));
        $this->assertEquals('mendatang', $item->status('2025-01-01'));
        $this->assertEquals('berjalan', $item->status('2026-02-01'));
    }

    // -----------------------------------------------------------------
    // Penilaian per aspek + rekap PKL
    // -----------------------------------------------------------------

    public function test_aspect_assessment_and_recap_average(): void
    {
        [, , , $cls, , , ,] = $this->seedParents();
        $stu = $this->makeStudent('001', 'Siswa 1', $cls->id);
        $item = $this->makeInternship($stu, 'PT Maju Jaya', '2026-01-05', '2026-03-31', 'Pembimbing Satu');

        $this->loginAs('admin');

        $res = $this->get("/admin/internships/{$item->id}/assessments");
        $res->assertStatus(200);

        $this->post("/admin/internships/{$item->id}/assessments", ['aspect' => 'kedisiplinan', 'score' => 80, 'note' => 'Tepat waktu.'])->assertSessionHas('success');
        $this->post("/admin/internships/{$item->id}/assessments", ['aspect' => 'keterampilan', 'score' => 90])->assertSessionHas('success');
        $this->post("/admin/internships/{$item->id}/assessments", ['aspect' => 'sikap', 'score' => null, 'note' => 'Belum dinilai.'])->assertSessionHas('success');

        // Rata-rata hanya dari aspek yang bernilai: (80 + 90) / 2 = 85.
        $this->assertEquals(85.0, $item->fresh()->averageScore());
        $this->assertDatabaseHas('activity_logs', ['action' => 'internship_assessment.created']);

        $svc = app(InternshipService::class);
        $recap = $svc->recapForStudent($stu->id);
        $this->assertEquals(85.0, $recap['average']);
        $this->assertEquals(1, $recap['total']);
        $this->assertCount(3, $recap['placements'][0]['aspects']);

        $res = $this->get('/admin/internships/recap');
        $res->assertStatus(200);
        $res->assertSee('Siswa 1');
        $res->assertSee('85,00');
    }

    public function test_same_aspect_updates_not_duplicates(): void
    {
        [, , , $cls, , , ,] = $this->seedParents();
        $stu = $this->makeStudent('001', 'Siswa 1', $cls->id);
        $item = $this->makeInternship($stu, 'PT Maju Jaya', '2026-01-05', '2026-03-31');

        $this->loginAs('admin');
        $this->post("/admin/internships/{$item->id}/assessments", ['aspect' => 'kedisiplinan', 'score' => 70])->assertSessionHas('success');
        $this->post("/admin/internships/{$item->id}/assessments", ['aspect' => 'kedisiplinan', 'score' => 95])->assertSessionHas('success');

        $this->assertEquals(1, InternshipAssessment::where('internship_id', $item->id)->count());
        $this->assertEquals(95.0, (float) InternshipAssessment::first()->score);
        $this->assertDatabaseHas('activity_logs', ['action' => 'internship_assessment.updated']);
    }

    public function test_assessment_update_and_delete(): void
    {
        [, , , $cls, , , ,] = $this->seedParents();
        $stu = $this->makeStudent('001', 'Siswa 1', $cls->id);
        $item = $this->makeInternship($stu, 'PT Maju Jaya', '2026-01-05', '2026-03-31');
        $a = InternshipAssessment::create(['internship_id' => $item->id, 'aspect' => 'sikap', 'score' => 75]);

        $this->loginAs('admin');
        $res = $this->put("/admin/internships/assessments/{$a->id}", ['aspect' => 'sikap', 'score' => 88, 'note' => 'Baik.']);
        $res->assertSessionHas('success');
        $this->assertEquals(88.0, (float) $a->fresh()->score);

        $res = $this->delete("/admin/internships/assessments/{$a->id}");
        $res->assertSessionHas('success');
        $this->assertNull(InternshipAssessment::find($a->id));
        $this->assertDatabaseHas('activity_logs', ['action' => 'internship_assessment.deleted']);
    }

    // -----------------------------------------------------------------
    // UKK: jadwal, peserta, input hasil, rekap
    // -----------------------------------------------------------------

    public function test_ukk_pages_load(): void
    {
        [, , , $cls, , $sub, ,] = $this->seedParents();
        $stu = $this->makeStudent('001', 'Siswa 1', $cls->id);
        CompetencyExam::create(['student_id' => $stu->id, 'subject_id' => $sub->id, 'score' => 80, 'exam_date' => '2026-03-10']);

        $this->loginAs('admin');

        $res = $this->get('/admin/competency-exams');
        $res->assertStatus(200);
        $res->assertSee('Matematika');

        $res = $this->get("/admin/competency-exams/schedule?subject_id={$sub->id}&exam_date=2026-03-10");
        $res->assertStatus(200);
        $res->assertSee('Siswa 1');

        $res = $this->get('/admin/competency-exams/recap');
        $res->assertStatus(200);
        $res->assertSee('Siswa 1');
        $res->assertSee('80,00');
    }

    public function test_ukk_add_participant_and_input_result_with_examiner(): void
    {
        [, , , $cls, , $sub, ,] = $this->seedParents();
        $stu = $this->makeStudent('001', 'Siswa 1', $cls->id);

        $this->loginAs('admin');

        // Tambah peserta tanpa nilai (score null).
        $res = $this->post('/admin/competency-exams', [
            'student_id' => $stu->id, 'subject_id' => $sub->id, 'exam_date' => '2026-03-10',
        ]);
        $res->assertSessionHas('success');

        $exam = CompetencyExam::first();
        $this->assertNotNull($exam);
        $this->assertNull($exam->score);
        $this->assertDatabaseHas('activity_logs', ['action' => 'competency_exam.created']);

        // Input hasil + penguji lewat konvensi note.
        $res = $this->put("/admin/competency-exams/{$exam->id}", [
            'score' => 88, 'examiner' => 'Budi Penguji', 'note' => 'Kerja bagus.',
        ]);
        $res->assertSessionHas('success');

        $fresh = $exam->fresh();
        $this->assertEquals(88.0, (float) $fresh->score);
        $this->assertEquals('Budi Penguji', $fresh->examiner());
        $this->assertEquals('Kerja bagus.', $fresh->noteWithoutExaminer());
        $this->assertStringContainsString('Penguji: Budi Penguji', $fresh->note);
        $this->assertDatabaseHas('activity_logs', ['action' => 'competency_exam.updated']);
    }

    public function test_ukk_duplicate_participant_not_duplicated(): void
    {
        [, , , $cls, , $sub, ,] = $this->seedParents();
        $stu = $this->makeStudent('001', 'Siswa 1', $cls->id);

        $this->loginAs('admin');
        $this->post('/admin/competency-exams', [
            'student_id' => $stu->id, 'subject_id' => $sub->id, 'exam_date' => '2026-03-10', 'score' => 70,
        ])->assertSessionHas('success');
        $this->post('/admin/competency-exams', [
            'student_id' => $stu->id, 'subject_id' => $sub->id, 'exam_date' => '2026-03-10', 'score' => 92,
        ])->assertSessionHas('success');

        $this->assertEquals(1, CompetencyExam::count());
        $this->assertEquals(92.0, (float) CompetencyExam::first()->score);
    }

    public function test_ukk_recap_average_per_student_and_subject(): void
    {
        [, , , $cls, , $sub, $sub2,] = $this->seedParents();
        $stu = $this->makeStudent('001', 'Siswa 1', $cls->id);
        $stu2 = $this->makeStudent('002', 'Siswa 2', $cls->id);

        CompetencyExam::create(['student_id' => $stu->id, 'subject_id' => $sub->id, 'score' => 80, 'exam_date' => '2026-03-10']);
        CompetencyExam::create(['student_id' => $stu->id, 'subject_id' => $sub->id, 'score' => 90, 'exam_date' => '2026-03-11']);
        CompetencyExam::create(['student_id' => $stu->id, 'subject_id' => $sub2->id, 'score' => 70, 'exam_date' => '2026-03-12']);
        CompetencyExam::create(['student_id' => $stu2->id, 'subject_id' => $sub->id, 'score' => 60, 'exam_date' => '2026-03-10']);

        $svc = app(CompetencyExamService::class);

        $recap = $svc->recapForStudent($stu->id);
        $this->assertEquals(80.0, $recap['average']); // (80+90+70)/3
        $this->assertEquals(85.0, $recap['per_subject'][0]['average']); // MTK (80+90)/2
        $this->assertEquals(70.0, $recap['per_subject'][1]['average']); // INF

        $perSubject = $svc->recapPerSubject();
        $mtk = collect($perSubject)->firstWhere('subject_id', $sub->id);
        $this->assertEquals(3, $mtk['participants']);
        $this->assertEquals(76.67, $mtk['average']); // (80+90+60)/3

        // Jadwal dikelompokkan per (mapel, tanggal): 3 jadwal.
        $this->assertCount(3, $svc->schedules());
        $first = $svc->schedules()->first();
        $this->assertEquals(2, $first['participants']); // MTK 2026-03-10: 2 peserta
        $this->assertEquals(70.0, $first['average']); // (80+60)/2

        // Tanpa label lulus/tidak lulus di rekap: hanya skor.
        $this->loginAs('admin');
        $res = $this->get('/admin/competency-exams/recap');
        $res->assertStatus(200);
        $res->assertDontSee('Lulus');
        $res->assertDontSee('Tidak Lulus');
    }

    public function test_ukk_delete(): void
    {
        [, , , $cls, , $sub, ,] = $this->seedParents();
        $stu = $this->makeStudent('001', 'Siswa 1', $cls->id);
        $exam = CompetencyExam::create(['student_id' => $stu->id, 'subject_id' => $sub->id, 'score' => 80, 'exam_date' => '2026-03-10']);

        $this->loginAs('admin');
        $res = $this->delete("/admin/competency-exams/{$exam->id}");
        $res->assertSessionHas('success');
        $this->assertNull(CompetencyExam::find($exam->id));
        $this->assertDatabaseHas('activity_logs', ['action' => 'competency_exam.deleted']);
    }

    // -----------------------------------------------------------------
    // Akses role
    // -----------------------------------------------------------------

    public function test_guru_can_view_and_assess_own_only(): void
    {
        [, , , $cls, , , , $tch, $tch2] = $this->seedParents();
        $stu = $this->makeStudent('001', 'Siswa 1', $cls->id);
        $own = $this->makeInternship($stu, 'PT Maju Jaya', '2026-01-05', '2026-03-31', 'Pembimbing Satu');
        $other = $this->makeInternship($stu, 'PT Rahasia', '2026-04-01', '2026-06-30', 'Pembimbing Dua');

        // Guru 1: daftar hanya menampilkan bimbingannya.
        $this->loginAs('guru', $tch->id);
        $res = $this->get('/guru/internships');
        $res->assertStatus(200);
        $res->assertSee('PT Maju Jaya');
        $res->assertDontSee('PT Rahasia');

        // Guru 1: detail + nilai bimbingan sendiri boleh.
        $res = $this->get("/guru/internships/{$own->id}");
        $res->assertStatus(200);
        $res = $this->post("/guru/internships/{$own->id}/assessments", ['aspect' => 'kedisiplinan', 'score' => 90]);
        $res->assertSessionHas('success');
        $this->assertEquals(90.0, (float) InternshipAssessment::first()->score);

        // Guru 1: bimbingan guru lain 403 (lihat + nilai).
        $res = $this->get("/guru/internships/{$other->id}");
        $res->assertForbidden();
        $res = $this->post("/guru/internships/{$other->id}/assessments", ['aspect' => 'kedisiplinan', 'score' => 50]);
        $res->assertForbidden();
        $this->assertEquals(1, InternshipAssessment::count());

        // Guru 2: sebaliknya — hanya bimbingannya yang terlihat.
        $this->loginAs('guru', $tch2->id);
        $res = $this->get('/guru/internships');
        $res->assertStatus(200);
        $res->assertSee('PT Rahasia');
        $res->assertDontSee('PT Maju Jaya');

        // Guru tidak boleh masuk route admin.
        $res = $this->post('/admin/internships', $this->placementPayload($stu));
        $res->assertForbidden();
    }

    public function test_guru_ukk_read_only_own_subjects(): void
    {
        [, $ay, $sem1, $cls, , $sub, $sub2, $tch] = $this->seedParents();
        $stu = $this->makeStudent('001', 'Siswa 1', $cls->id);
        $this->makeTeaching($tch, $sub, $cls, $ay, $sem1);
        CompetencyExam::create(['student_id' => $stu->id, 'subject_id' => $sub->id, 'score' => 80, 'exam_date' => '2026-03-10']);
        CompetencyExam::create(['student_id' => $stu->id, 'subject_id' => $sub2->id, 'score' => 70, 'exam_date' => '2026-03-10']);

        $this->loginAs('guru', $tch->id);
        $res = $this->get('/guru/competency-exams');
        $res->assertStatus(200);
        $res->assertSee('Matematika');
        $res->assertDontSee('Informatika');
    }

    public function test_wali_can_view_own_class_only_read_only(): void
    {
        [, $ay, $sem1, $cls, $cls2, $sub, , $tch, $tch2] = $this->seedParents();
        $stu = $this->makeStudent('001', 'Siswa 1', $cls->id);
        $stuOther = $this->makeStudent('002', 'Siswa Lain', $cls2->id);
        $this->makeHomeroom($tch, $cls, $ay, $sem1);
        $this->makeHomeroom($tch2, $cls2, $ay, $sem1);
        $this->makeInternship($stu, 'PT Maju Jaya', '2026-01-05', '2026-03-31', 'Pembimbing Satu');
        $this->makeInternship($stuOther, 'PT Rahasia', '2026-01-05', '2026-03-31', 'Pembimbing Dua');
        CompetencyExam::create(['student_id' => $stu->id, 'subject_id' => $sub->id, 'score' => 80, 'exam_date' => '2026-03-10']);
        CompetencyExam::create(['student_id' => $stuOther->id, 'subject_id' => $sub->id, 'score' => 60, 'exam_date' => '2026-03-10']);

        $this->loginAs('wali_kelas', $tch->id);

        $res = $this->get('/wali/internships');
        $res->assertStatus(200);
        $res->assertSee('PT Maju Jaya');
        $res->assertDontSee('PT Rahasia');

        $res = $this->get('/wali/internships/recap');
        $res->assertStatus(200);
        $res->assertSee('Siswa 1');
        $res->assertDontSee('Siswa Lain');

        $res = $this->get('/wali/competency-exams');
        $res->assertStatus(200);
        $res->assertSee('Siswa 1');
        $res->assertDontSee('Siswa Lain');

        // Filter kelas lain -> 403; filter siswa kelas lain -> 403.
        $res = $this->get("/wali/internships?class_id={$cls2->id}");
        $res->assertForbidden();
        $res = $this->get("/wali/internships?student_id={$stuOther->id}");
        $res->assertForbidden();
        $res = $this->get("/wali/competency-exams?student_id={$stuOther->id}");
        $res->assertForbidden();

        // Wali tidak boleh masuk route admin.
        $res = $this->post('/admin/internships', $this->placementPayload($stu));
        $res->assertForbidden();
        $res = $this->post('/admin/competency-exams', [
            'student_id' => $stu->id, 'subject_id' => $sub->id, 'exam_date' => '2026-03-10',
        ]);
        $res->assertForbidden();
    }

    // -----------------------------------------------------------------
    // PKL/UKK tidak mengubah nilai akhir rapor
    // -----------------------------------------------------------------

    public function test_pkl_ukk_do_not_change_report_final_score(): void
    {
        [, $ay, $sem1, $cls, , $sub, , $tch] = $this->seedParents();
        $stu = $this->makeStudent('001', 'Siswa 1', $cls->id);

        $component = AssessmentComponent::create([
            'name' => 'Sumatif', 'type' => 'sumatif', 'weight' => 100,
            'subject_id' => $sub->id, 'kktp' => 75, 'is_active' => true,
        ]);
        AssessmentScore::create([
            'student_id' => $stu->id, 'subject_id' => $sub->id, 'component_id' => $component->id,
            'class_id' => $cls->id, 'academic_year_id' => $ay->id, 'semester_id' => $sem1->id,
            'teacher_id' => $tch->id, 'score' => 80, 'status' => 'terkirim',
        ]);

        $scores = app(AssessmentService::class);
        $before = $scores->finalScore($stu->id, $sub->id, $ay->id, $sem1->id);
        $this->assertEquals(80.0, $before);

        // Tambah data PKL (penempatan + penilaian aspek + grade)
        // dan data UKK dengan nilai yang berbeda jauh.
        $item = $this->makeInternship($stu, 'PT Maju Jaya', '2026-01-05', '2026-03-31', 'Pembimbing Satu');
        $item->update(['grade' => 'A']);
        InternshipAssessment::create(['internship_id' => $item->id, 'aspect' => 'kedisiplinan', 'score' => 100]);
        InternshipAssessment::create(['internship_id' => $item->id, 'aspect' => 'keterampilan', 'score' => 40]);
        CompetencyExam::create(['student_id' => $stu->id, 'subject_id' => $sub->id, 'score' => 55, 'exam_date' => '2026-03-10']);

        $after = $scores->finalScore($stu->id, $sub->id, $ay->id, $sem1->id);
        $this->assertEquals(80.0, $after);
        $this->assertEquals($before, $after);
    }

    public function test_guest_redirected_to_login(): void
    {
        [, , , $cls, , , ,] = $this->seedParents();
        $stu = $this->makeStudent('001', 'Siswa 1', $cls->id);
        $item = $this->makeInternship($stu, 'PT Maju Jaya', '2026-01-05', '2026-03-31');

        $res = $this->get('/admin/internships');
        $res->assertRedirect('/login');
        $res = $this->get('/admin/competency-exams');
        $res->assertRedirect('/login');
        $res = $this->get('/guru/internships');
        $res->assertRedirect('/login');
        $res = $this->get("/guru/internships/{$item->id}");
        $res->assertRedirect('/login');
        $res = $this->get('/wali/internships');
        $res->assertRedirect('/login');
        $res = $this->get('/wali/competency-exams');
        $res->assertRedirect('/login');
    }
}
