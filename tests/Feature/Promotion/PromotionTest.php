<?php

namespace Tests\Feature\Promotion;

use App\Models\AcademicYear;
use App\Models\Department;
use App\Models\HomeroomAssignment;
use App\Models\PromotionDecision;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Fase D (RDM): kenaikan kelas & kelulusan.
 * - Wali menetapkan naik/tidak naik hanya pada semester Genap.
 * - Operator meluluskan kelas XII dulu ke Alumni, baru menaikkan
 *   kelas di bawahnya; siswa tidak naik tetap di kelas asal.
 */
class PromotionTest extends TestCase
{
    use RefreshDatabase;

    private static int $userSeq = 0;

    private function seedParents(): array
    {
        $dep = Department::create(['code' => 'TKJ', 'name' => 'Teknik Komputer dan Jaringan']);
        $ay = AcademicYear::create(['name' => '2025/2026', 'start_date' => '2025-07-01', 'end_date' => '2026-06-30', 'is_active' => true]);
        $sem1 = Semester::create(['academic_year_id' => $ay->id, 'name' => 'Ganjil', 'is_active' => true]);
        $sem2 = Semester::create(['academic_year_id' => $ay->id, 'name' => 'Genap', 'is_active' => false]);
        $clsX = SchoolClass::create(['department_id' => $dep->id, 'academic_year_id' => $ay->id, 'name' => 'X TKJ 1', 'level' => 'X']);
        $clsXI = SchoolClass::create(['department_id' => $dep->id, 'academic_year_id' => $ay->id, 'name' => 'XI TKJ 1', 'level' => 'XI']);
        $clsXII = SchoolClass::create(['department_id' => $dep->id, 'academic_year_id' => $ay->id, 'name' => 'XII TKJ 1', 'level' => 'XII']);
        $tch = Teacher::create(['nip' => '123', 'name' => 'Wali 1']);

        return [$dep, $ay, $sem1, $sem2, $clsX, $clsXI, $clsXII, $tch];
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

    private function decide($stu, $cls, $ay, $sem, string $status, ?int $decidedBy = null): PromotionDecision
    {
        return PromotionDecision::create([
            'student_id' => $stu->id, 'class_id' => $cls->id,
            'academic_year_id' => $ay->id, 'semester_id' => $sem->id,
            'status' => $status, 'decided_by' => $decidedBy,
        ]);
    }

    public function test_wali_can_decide_promotion_in_even_semester(): void
    {
        [, $ay, , $sem2, $clsX, , , $tch] = $this->seedParents();
        $stu1 = $this->makeStudent('001', 'Siswa 1', $clsX->id);
        $stu2 = $this->makeStudent('002', 'Siswa 2', $clsX->id);
        $this->makeHomeroom($tch, $clsX, $ay, $sem2);

        $this->loginAs('wali_kelas', $tch->id);

        $res = $this->get("/wali/promotions?class_id={$clsX->id}&academic_year_id={$ay->id}&semester_id={$sem2->id}");
        $res->assertStatus(200);
        $res->assertSee('Siswa 1');
        $res->assertSee('Kenaikan Kelas');

        $res = $this->post('/wali/promotions', [
            'class_id' => $clsX->id,
            'academic_year_id' => $ay->id,
            'semester_id' => $sem2->id,
            'records' => [
                ['student_id' => $stu1->id, 'status' => 'naik'],
                ['student_id' => $stu2->id, 'status' => 'tidak_naik'],
            ],
        ]);
        $res->assertSessionHas('success');

        $this->assertDatabaseHas('promotion_decisions', [
            'student_id' => $stu1->id, 'academic_year_id' => $ay->id,
            'semester_id' => $sem2->id, 'status' => 'naik',
        ]);
        $this->assertDatabaseHas('promotion_decisions', [
            'student_id' => $stu2->id, 'academic_year_id' => $ay->id,
            'semester_id' => $sem2->id, 'status' => 'tidak_naik',
        ]);
    }

    public function test_wali_cannot_decide_promotion_in_odd_semester(): void
    {
        [, $ay, $sem1, , $clsX, , , $tch] = $this->seedParents();
        $stu = $this->makeStudent('001', 'Siswa 1', $clsX->id);
        $this->makeHomeroom($tch, $clsX, $ay, $sem1);

        $this->loginAs('wali_kelas', $tch->id);

        $res = $this->get("/wali/promotions?class_id={$clsX->id}&academic_year_id={$ay->id}&semester_id={$sem1->id}");
        $res->assertStatus(200);
        $res->assertSee('Ganjil');

        $res = $this->post('/wali/promotions', [
            'class_id' => $clsX->id,
            'academic_year_id' => $ay->id,
            'semester_id' => $sem1->id,
            'records' => [
                ['student_id' => $stu->id, 'status' => 'naik'],
            ],
        ]);
        $res->assertSessionHasErrors('semester_id');
        $this->assertDatabaseCount('promotion_decisions', 0);
    }

    public function test_wali_cannot_decide_for_other_homeroom_class(): void
    {
        [, $ay, , $sem2, $clsX, $clsXI, , $tch] = $this->seedParents();
        $stu = $this->makeStudent('001', 'Siswa 1', $clsXI->id);
        $this->makeHomeroom($tch, $clsX, $ay, $sem2);

        $this->loginAs('wali_kelas', $tch->id);

        $res = $this->post('/wali/promotions', [
            'class_id' => $clsXI->id,
            'academic_year_id' => $ay->id,
            'semester_id' => $sem2->id,
            'records' => [
                ['student_id' => $stu->id, 'status' => 'naik'],
            ],
        ]);
        $res->assertForbidden();
    }

    public function test_graduate_xii_then_promote(): void
    {
        [, $ay, , $sem2, $clsX, $clsXI, $clsXII, $tch] = $this->seedParents();
        $x1 = $this->makeStudent('101', 'Siswa X Naik', $clsX->id);
        $x2 = $this->makeStudent('102', 'Siswa X Tinggal', $clsX->id);
        $xi1 = $this->makeStudent('111', 'Siswa XI Naik', $clsXI->id);
        $xi2 = $this->makeStudent('112', 'Siswa XI Tinggal', $clsXI->id);
        $xii1 = $this->makeStudent('121', 'Siswa XII Lulus', $clsXII->id);
        $xii2 = $this->makeStudent('122', 'Siswa XII Tinggal', $clsXII->id);

        $this->decide($x1, $clsX, $ay, $sem2, 'naik');
        $this->decide($x2, $clsX, $ay, $sem2, 'tidak_naik');
        $this->decide($xi1, $clsXI, $ay, $sem2, 'naik');
        $this->decide($xi2, $clsXI, $ay, $sem2, 'tidak_naik');
        $this->decide($xii1, $clsXII, $ay, $sem2, 'naik');
        $this->decide($xii2, $clsXII, $ay, $sem2, 'tidak_naik');

        $this->loginAs('admin');

        // Langkah 1: luluskan XII.
        $res = $this->post('/admin/promotions/graduate', [
            'academic_year_id' => $ay->id, 'semester_id' => $sem2->id,
        ]);
        $res->assertSessionHas('success');

        $xii1 = $xii1->fresh();
        $this->assertNull($xii1->class_id);
        $this->assertTrue((bool) $xii1->is_alumni);
        $this->assertSame('2025/2026', $xii1->alumni_year);
        $this->assertSame('Siswa XII Lulus', $xii1->name); // data tidak hilang
        $this->assertDatabaseHas('students', ['id' => $xii1->id, 'nis' => '121']);

        // XII yang tidak naik tetap di kelas asal.
        $this->assertSame($clsXII->id, $xii2->fresh()->class_id);
        $this->assertFalse((bool) $xii2->fresh()->is_alumni);

        // Langkah 2: naikkan X -> XI dan XI -> XII.
        $res = $this->post('/admin/promotions/promote', [
            'academic_year_id' => $ay->id,
            'semester_id' => $sem2->id,
            'mappings' => [
                ['from_class_id' => $clsX->id, 'to_class_id' => $clsXI->id],
                ['from_class_id' => $clsXI->id, 'to_class_id' => $clsXII->id],
            ],
        ]);
        $res->assertSessionHas('success');

        $this->assertSame($clsXI->id, $x1->fresh()->class_id);
        $this->assertSame($clsX->id, $x2->fresh()->class_id);
        $this->assertSame($clsXII->id, $xi1->fresh()->class_id);
        $this->assertSame($clsXI->id, $xi2->fresh()->class_id);
    }

    public function test_promote_blocked_while_xii_not_graduated(): void
    {
        [, $ay, , $sem2, $clsX, $clsXI, $clsXII] = $this->seedParents();
        $x1 = $this->makeStudent('101', 'Siswa X Naik', $clsX->id);
        $xii1 = $this->makeStudent('121', 'Siswa XII Lulus', $clsXII->id);

        $this->decide($x1, $clsX, $ay, $sem2, 'naik');
        $this->decide($xii1, $clsXII, $ay, $sem2, 'naik');

        $this->loginAs('admin');

        $res = $this->post('/admin/promotions/promote', [
            'academic_year_id' => $ay->id,
            'semester_id' => $sem2->id,
            'mappings' => [
                ['from_class_id' => $clsX->id, 'to_class_id' => $clsXI->id],
            ],
        ]);
        $res->assertSessionHasErrors('promotion');

        // Tidak ada yang pindah selama ditolak.
        $this->assertSame($clsX->id, $x1->fresh()->class_id);
        $this->assertSame($clsXII->id, $xii1->fresh()->class_id);
    }

    public function test_promotion_and_alumni_pages_access(): void
    {
        [, $ay, , $sem2, $clsX, $clsXI, $clsXII, $tch] = $this->seedParents();
        $xii1 = $this->makeStudent('121', 'Siswa XII Lulus', $clsXII->id);
        $this->decide($xii1, $clsXII, $ay, $sem2, 'naik');

        $this->loginAs('admin');

        $res = $this->get('/admin/promotions?academic_year_id='.$ay->id.'&semester_id='.$sem2->id);
        $res->assertStatus(200);
        $res->assertSee('Luluskan Kelas XII');
        $res->assertSee('Naikkan Kelas');

        $res = $this->get('/admin/alumni');
        $res->assertStatus(200);
        $res->assertSee('Belum ada alumni');

        // Setelah lulus, alumni tampil dengan kelas terakhir + tahun.
        $this->post('/admin/promotions/graduate', [
            'academic_year_id' => $ay->id, 'semester_id' => $sem2->id,
        ])->assertSessionHas('success');

        $res = $this->get('/admin/alumni');
        $res->assertStatus(200);
        $res->assertSee('Siswa XII Lulus');
        $res->assertSee('XII TKJ 1');
        $res->assertSee('2025/2026');

        $res = $this->get('/admin/alumni?alumni_year=2025/2026');
        $res->assertStatus(200);
        $res->assertSee('Siswa XII Lulus');

        $res = $this->get('/admin/alumni?alumni_year=2024/2025');
        $res->assertStatus(200);
        $res->assertDontSee('Siswa XII Lulus');

        // Guru tidak boleh membuka halaman admin ini.
        $this->loginAs('guru', $tch->id);
        $this->get('/admin/promotions')->assertForbidden();
        $this->get('/admin/alumni')->assertForbidden();
        $this->post('/admin/promotions/graduate', [
            'academic_year_id' => $ay->id, 'semester_id' => $sem2->id,
        ])->assertForbidden();
    }

    public function test_preview_shows_promotion_status(): void
    {
        [, $ay, , $sem2, $clsX, , , $tch] = $this->seedParents();
        $stu1 = $this->makeStudent('001', 'Siswa 1', $clsX->id);
        $stu2 = $this->makeStudent('002', 'Siswa 2', $clsX->id);
        $this->makeHomeroom($tch, $clsX, $ay, $sem2);
        $this->decide($stu1, $clsX, $ay, $sem2, 'naik');

        $this->loginAs('wali_kelas', $tch->id);

        $res = $this->get("/wali/preview/{$stu1->id}?academic_year_id={$ay->id}&semester_id={$sem2->id}");
        $res->assertStatus(200);
        $res->assertSee('Keputusan Kenaikan Kelas');
        $res->assertSee('Naik Kelas');

        $res = $this->get("/wali/preview/{$stu2->id}?academic_year_id={$ay->id}&semester_id={$sem2->id}");
        $res->assertStatus(200);
        $res->assertSee('Belum ditetapkan');
    }

    public function test_promote_rejected_in_odd_semester(): void
    {
        [, $ay, $sem1, , $clsX, $clsXI] = $this->seedParents();

        $this->loginAs('admin');

        $res = $this->post('/admin/promotions/promote', [
            'academic_year_id' => $ay->id,
            'semester_id' => $sem1->id,
            'mappings' => [
                ['from_class_id' => $clsX->id, 'to_class_id' => $clsXI->id],
            ],
        ]);
        $res->assertSessionHasErrors('semester_id');
    }
}
