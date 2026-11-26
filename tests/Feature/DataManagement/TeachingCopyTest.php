<?php

namespace Tests\Feature\DataManagement;

use App\Models\AcademicYear;
use App\Models\Department;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Fase E (RDM): Salin Mengajar (admin saja).
 * - Penugasan tersalin ke semester tujuan, duplikat dilewati,
 *   ringkasan jumlah tersalin/dilewati benar.
 * - Semester asal sama dengan tujuan dan semester asal kosong
 *   ditolak; peran lain 403.
 */
class TeachingCopyTest extends TestCase
{
    use RefreshDatabase;

    private static int $userSeq = 0;

    private function loginAs(string $roleSlug): User
    {
        $role = Role::firstOrCreate(['slug' => $roleSlug], ['name' => $roleSlug]);
        self::$userSeq++;
        $user = User::create([
            'name' => ucfirst($roleSlug).' '.self::$userSeq,
            'email' => $roleSlug.self::$userSeq.'@smk.test',
            'password' => bcrypt('password'),
            'role_id' => $role->id,
            'is_active' => true,
        ]);
        $this->actingAs($user);

        return $user;
    }

    private function seedData(): array
    {
        $dep = Department::create(['code' => 'TKJ', 'name' => 'Teknik Komputer dan Jaringan']);
        $ay = AcademicYear::create(['name' => '2025/2026', 'start_date' => '2025-07-01', 'end_date' => '2026-06-30', 'is_active' => true]);
        $sem1 = Semester::create(['academic_year_id' => $ay->id, 'name' => 'Ganjil', 'is_active' => true]);
        $sem2 = Semester::create(['academic_year_id' => $ay->id, 'name' => 'Genap', 'is_active' => false]);
        $cls = SchoolClass::create(['department_id' => $dep->id, 'academic_year_id' => $ay->id, 'name' => 'X TKJ 1', 'level' => 'X']);
        $tch1 = Teacher::create(['nip' => '111', 'name' => 'Guru Satu', 'gender' => 'L']);
        $tch2 = Teacher::create(['nip' => '222', 'name' => 'Guru Dua', 'gender' => 'P']);
        $sub1 = Subject::create(['code' => 'MTK', 'name' => 'Matematika']);
        $sub2 = Subject::create(['code' => 'BIN', 'name' => 'Bahasa Indonesia']);

        return [$ay, $sem1, $sem2, $cls, $tch1, $tch2, $sub1, $sub2];
    }

    private function assign($tch, $sub, $cls, $ay, $sem, $hours = 4): TeachingAssignment
    {
        return TeachingAssignment::create([
            'teacher_id' => $tch->id, 'subject_id' => $sub->id, 'class_id' => $cls->id,
            'academic_year_id' => $ay->id, 'semester_id' => $sem->id, 'hours' => $hours,
        ]);
    }

    public function test_copy_assignments_with_duplicate_skipped(): void
    {
        [$ay, $sem1, $sem2, $cls, $tch1, $tch2, $sub1, $sub2] = $this->seedData();
        $this->assign($tch1, $sub1, $cls, $ay, $sem1);
        $this->assign($tch2, $sub2, $cls, $ay, $sem1);
        // Duplikat: penugasan guru 1 sudah ada di semester tujuan.
        $this->assign($tch1, $sub1, $cls, $ay, $sem2);

        $this->loginAs('admin');

        $res = $this->get('/admin/teaching-assignments/copy');
        $res->assertStatus(200);
        $res->assertSee('Salin Mengajar');

        $res = $this->post('/admin/teaching-assignments/copy', [
            'from_semester_id' => $sem1->id,
            'to_semester_id' => $sem2->id,
        ]);
        $res->assertSessionHas('success');

        $message = session('success');
        $this->assertStringContainsString('1 penugasan tersalin', $message);
        $this->assertStringContainsString('1 dilewati', $message);

        // Semester tujuan kini punya 2 penugasan (1 lama + 1 salinan),
        // tanpa duplikat guru 1.
        $this->assertSame(2, TeachingAssignment::where('semester_id', $sem2->id)->count());
        $this->assertSame(1, TeachingAssignment::where('semester_id', $sem2->id)
            ->where('teacher_id', $tch1->id)->where('subject_id', $sub1->id)->count());
        $this->assertDatabaseHas('teaching_assignments', [
            'teacher_id' => $tch2->id, 'subject_id' => $sub2->id,
            'semester_id' => $sem2->id, 'academic_year_id' => $ay->id,
        ]);
    }

    public function test_copy_same_semester_rejected(): void
    {
        [$ay, $sem1, , $cls, $tch1, , $sub1] = $this->seedData();
        $this->assign($tch1, $sub1, $cls, $ay, $sem1);

        $this->loginAs('admin');

        $res = $this->post('/admin/teaching-assignments/copy', [
            'from_semester_id' => $sem1->id,
            'to_semester_id' => $sem1->id,
        ]);
        $res->assertSessionHasErrors('to_semester_id');
        $this->assertSame(1, TeachingAssignment::count());
    }

    public function test_copy_empty_source_rejected(): void
    {
        [, $sem1, $sem2] = $this->seedData();

        $this->loginAs('admin');

        $res = $this->post('/admin/teaching-assignments/copy', [
            'from_semester_id' => $sem1->id,
            'to_semester_id' => $sem2->id,
        ]);
        $res->assertSessionHasErrors('from_semester_id');
        $this->assertSame(0, TeachingAssignment::count());
    }

    public function test_guru_cannot_copy_assignments(): void
    {
        [$ay, $sem1, $sem2, $cls, $tch1, , $sub1] = $this->seedData();
        $this->assign($tch1, $sub1, $cls, $ay, $sem1);

        $this->loginAs('guru');

        $this->get('/admin/teaching-assignments/copy')->assertForbidden();
        $this->post('/admin/teaching-assignments/copy', [
            'from_semester_id' => $sem1->id,
            'to_semester_id' => $sem2->id,
        ])->assertForbidden();
        $this->assertSame(0, TeachingAssignment::where('semester_id', $sem2->id)->count());
    }
}
