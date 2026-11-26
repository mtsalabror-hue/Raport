<?php

namespace Tests\Feature\DataManagement;

use App\Models\AcademicYear;
use App\Models\Department;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

/**
 * Fase E (RDM): Impor CSV siswa dan guru (admin saja).
 * - Template bisa diunduh dengan header yang benar.
 * - Baris valid masuk, baris gagal dilaporkan dengan nomor baris
 *   + alasan (duplikat NIS/NIP, gender salah, kelas tidak ada).
 * - Header tidak sesuai template ditolak; peran lain 403.
 */
class CsvImportTest extends TestCase
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

    private function seedClass(): SchoolClass
    {
        $dep = Department::create(['code' => 'TKJ', 'name' => 'Teknik Komputer dan Jaringan']);
        $ay = AcademicYear::create(['name' => '2025/2026', 'start_date' => '2025-07-01', 'end_date' => '2026-06-30', 'is_active' => true]);
        Semester::create(['academic_year_id' => $ay->id, 'name' => 'Ganjil', 'is_active' => true]);

        return SchoolClass::create(['department_id' => $dep->id, 'academic_year_id' => $ay->id, 'name' => 'X TKJ 1', 'level' => 'X']);
    }

    private function csvFile(string $name, string $content): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($name, $content);
    }

    public function test_admin_can_download_templates(): void
    {
        $this->loginAs('admin');

        $res = $this->get('/admin/import');
        $res->assertStatus(200);
        $res->assertSee('Impor Data');

        $res = $this->get('/admin/import/template/siswa');
        $res->assertStatus(200);
        $this->assertStringContainsString('nis,nisn,nama,gender,kelas,tanggal_lahir,alamat', $res->getContent());

        $res = $this->get('/admin/import/template/guru');
        $res->assertStatus(200);
        $this->assertStringContainsString('nip,nama,gender,telepon,alamat', $res->getContent());
    }

    public function test_import_students_valid_rows_created_failed_rows_reported(): void
    {
        $this->seedClass();
        $this->loginAs('admin');

        $csv = implode("\n", [
            'nis,nisn,nama,gender,kelas,tanggal_lahir,alamat',
            '001,N001,Budi Santoso,L,X TKJ 1,2010-01-15,Jl. Merdeka 1',
            '001,N002,Duplikat NIS,L,X TKJ 1,2010-02-02,Jl. Merdeka 2',
            '002,N003,Gender Salah,X,X TKJ 1,2010-03-03,Jl. Merdeka 3',
            '003,N004,Kelas Tidak Ada,L,X RPL 9,2010-04-04,Jl. Merdeka 4',
            '004,,NISN Kosong,L,X TKJ 1,2010-05-05,Jl. Merdeka 5',
            '005,N005,Siti Aminah,P,X TKJ 1,,',
        ]);

        $res = $this->post('/admin/import/siswa', [
            'file' => $this->csvFile('siswa.csv', $csv),
        ]);
        $res->assertSessionHas('import_result');

        $result = session('import_result');
        $this->assertSame(2, $result['created']);
        $this->assertSame(4, $result['failed']);
        $this->assertSame([3, 4, 5, 6], array_column($result['errors'], 'row'));

        $messages = implode(' ', array_column($result['errors'], 'message'));
        $this->assertStringContainsString('NIS 001 sudah digunakan', $messages);
        $this->assertStringContainsString('Gender harus L atau P', $messages);
        $this->assertStringContainsString('Kelas "X RPL 9" tidak ditemukan', $messages);
        $this->assertStringContainsString('NISN wajib diisi', $messages);

        $this->assertDatabaseHas('students', ['nis' => '001', 'name' => 'Budi Santoso', 'gender' => 'L']);
        $this->assertDatabaseHas('students', ['nis' => '005', 'name' => 'Siti Aminah', 'gender' => 'P']);
        $this->assertDatabaseCount('students', 2);
    }

    public function test_import_teachers_valid_rows_created_failed_rows_reported(): void
    {
        $this->loginAs('admin');

        $csv = implode("\n", [
            'nip,nama,gender,telepon,alamat',
            '19800101,Guru Satu,L,081234567890,Jl. Guru 1',
            '19800101,Guru Duplikat,L,081234567891,Jl. Guru 2',
            '19800202,Guru Tanpa Nama,,081234567892,Jl. Guru 3',
            '19800303,Guru Tiga,P,,',
        ]);

        $res = $this->post('/admin/import/guru', [
            'file' => $this->csvFile('guru.csv', $csv),
        ]);
        $res->assertSessionHas('import_result');

        $result = session('import_result');
        $this->assertSame(2, $result['created']);
        $this->assertSame(2, $result['failed']);
        $this->assertSame([3, 4], array_column($result['errors'], 'row'));

        $messages = implode(' ', array_column($result['errors'], 'message'));
        $this->assertStringContainsString('NIP 19800101 sudah digunakan', $messages);
        $this->assertStringContainsString('Gender harus L atau P', $messages);

        $this->assertDatabaseHas('teachers', ['nip' => '19800101', 'name' => 'Guru Satu']);
        $this->assertDatabaseHas('teachers', ['nip' => '19800303', 'name' => 'Guru Tiga']);
        $this->assertDatabaseCount('teachers', 2);
    }

    public function test_import_wrong_header_rejected(): void
    {
        $this->seedClass();
        $this->loginAs('admin');

        $res = $this->post('/admin/import/siswa', [
            'file' => $this->csvFile('siswa.csv', "nis,nama\n001,Budi\n"),
        ]);
        $res->assertSessionHasErrors('file');
        $this->assertDatabaseCount('students', 0);
    }

    public function test_guru_cannot_import(): void
    {
        $this->seedClass();
        $this->loginAs('guru');

        $this->get('/admin/import')->assertForbidden();
        $this->get('/admin/import/template/siswa')->assertForbidden();
        $this->post('/admin/import/siswa', [
            'file' => $this->csvFile('siswa.csv', "nis,nisn,nama,gender,kelas,tanggal_lahir,alamat\n001,N001,Budi,L,X TKJ 1,2010-01-01,\n"),
        ])->assertForbidden();
        $this->assertDatabaseCount('students', 0);
    }
}
