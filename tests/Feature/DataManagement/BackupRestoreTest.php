<?php

namespace Tests\Feature\DataManagement;

use App\Models\Department;
use App\Models\AcademicYear;
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
 * Fase E (RDM): Backup & Restore (admin saja).
 * - Unduh backup: 200 admin / 403 guru, JSON berisi penanda +
 *   tabel kunci, tanpa tabel infrastruktur.
 * - Restore round-trip: backup, hapus sebagian data, restore,
 *   data kembali. File tidak valid / tanpa admin aktif ditolak
 *   dan data tidak berubah.
 */
class BackupRestoreTest extends TestCase
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
        $sem = Semester::create(['academic_year_id' => $ay->id, 'name' => 'Ganjil', 'is_active' => true]);
        $cls = SchoolClass::create(['department_id' => $dep->id, 'academic_year_id' => $ay->id, 'name' => 'X TKJ 1', 'level' => 'X']);
        $tch = Teacher::create(['nip' => '19800101', 'name' => 'Guru Satu', 'gender' => 'L']);
        $stu = Student::create([
            'nis' => '001', 'nisn' => 'N001', 'name' => 'Siswa Satu',
            'gender' => 'L', 'class_id' => $cls->id, 'birth_date' => '2010-05-01',
        ]);

        return [$dep, $ay, $sem, $cls, $tch, $stu];
    }

    private function downloadBackup(): array
    {
        $res = $this->get('/admin/backup/download');
        $res->assertStatus(200);

        return json_decode($res->getContent(), true);
    }

    public function test_admin_can_download_backup_with_marker_and_key_tables(): void
    {
        [, , , , $tch, $stu] = $this->seedData();
        $this->loginAs('admin');

        $res = $this->get('/admin/backup');
        $res->assertStatus(200);
        $res->assertSee('Backup & Restore');

        $data = $this->downloadBackup();

        $this->assertSame('e-rapor-smk', $data['app']);
        $this->assertSame(1, $data['version']);
        $this->assertNotEmpty($data['exported_at']);
        $this->assertArrayHasKey('tables', $data);

        foreach (['roles', 'users', 'students', 'teachers', 'classes', 'semesters', 'system_settings', 'activity_logs'] as $table) {
            $this->assertArrayHasKey($table, $data['tables'], "Tabel {$table} tidak ada di cadangan.");
        }

        // Tabel infrastruktur Laravel tidak ikut dicadangkan.
        foreach (['cache', 'jobs', 'sessions', 'password_reset_tokens'] as $table) {
            $this->assertArrayNotHasKey($table, $data['tables']);
        }

        // Data ikut tercadangkan (hash password apa adanya).
        $this->assertContains($stu->nis, array_column($data['tables']['students'], 'nis'));
        $this->assertContains($tch->nip, array_column($data['tables']['teachers'], 'nip'));
        $this->assertNotEmpty($data['tables']['users'][0]['password']);
    }

    public function test_guru_cannot_access_backup(): void
    {
        $this->seedData();
        $this->loginAs('guru');

        $this->get('/admin/backup')->assertForbidden();
        $this->get('/admin/backup/download')->assertForbidden();
        $this->post('/admin/backup/restore', [
            'backup' => UploadedFile::fake()->createWithContent('backup.json', '{}'),
        ])->assertForbidden();
    }

    public function test_restore_round_trip_brings_data_back(): void
    {
        [, , , , $tch, $stu] = $this->seedData();
        $this->loginAs('admin');

        $data = $this->downloadBackup();

        // Hapus sebagian data.
        $stu->delete();
        $tch->delete();
        $this->assertDatabaseCount('students', 0);
        $this->assertDatabaseCount('teachers', 0);

        $res = $this->post('/admin/backup/restore', [
            'backup' => UploadedFile::fake()->createWithContent('backup.json', json_encode($data)),
        ]);
        $res->assertSessionHas('success');

        $this->assertDatabaseHas('students', ['nis' => '001', 'name' => 'Siswa Satu']);
        $this->assertDatabaseHas('teachers', ['nip' => '19800101', 'name' => 'Guru Satu']);
    }

    public function test_restore_invalid_file_rejected_and_data_unchanged(): void
    {
        [, , , , , $stu] = $this->seedData();
        $this->loginAs('admin');

        // Bukan JSON.
        $res = $this->post('/admin/backup/restore', [
            'backup' => UploadedFile::fake()->createWithContent('backup.json', 'bukan json {'),
        ]);
        $res->assertSessionHasErrors('backup');
        $this->assertDatabaseHas('students', ['id' => $stu->id]);

        // JSON valid tapi bukan cadangan aplikasi ini.
        $res = $this->post('/admin/backup/restore', [
            'backup' => UploadedFile::fake()->createWithContent('backup.json', json_encode(['app' => 'lain', 'version' => 1, 'tables' => []])),
        ]);
        $res->assertSessionHasErrors('backup');
        $this->assertDatabaseHas('students', ['id' => $stu->id]);

        // Penanda benar tapi ada tabel tidak dikenal.
        $res = $this->post('/admin/backup/restore', [
            'backup' => UploadedFile::fake()->createWithContent('backup.json', json_encode([
                'app' => 'e-rapor-smk', 'version' => 1,
                'tables' => ['tabel_asing' => []],
            ])),
        ]);
        $res->assertSessionHasErrors('backup');
        $this->assertDatabaseHas('students', ['id' => $stu->id]);
    }

    public function test_restore_without_active_admin_rejected(): void
    {
        [, , , , , $stu] = $this->seedData();
        $this->loginAs('admin');

        $data = $this->downloadBackup();

        // Nonaktifkan semua admin di cadangan.
        $adminRoleId = collect($data['tables']['roles'])->firstWhere('slug', 'admin')['id'];
        foreach ($data['tables']['users'] as &$user) {
            if ((int) $user['role_id'] === (int) $adminRoleId) {
                $user['is_active'] = false;
            }
        }
        unset($user);

        $res = $this->post('/admin/backup/restore', [
            'backup' => UploadedFile::fake()->createWithContent('backup.json', json_encode($data)),
        ]);
        $res->assertSessionHasErrors('backup');
        $this->assertDatabaseHas('students', ['id' => $stu->id]);
        $this->assertDatabaseHas('users', ['is_active' => true]);
    }
}
