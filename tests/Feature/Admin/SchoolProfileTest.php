<?php

namespace Tests\Feature\Admin;

use App\Models\AcademicYear;
use App\Models\Department;
use App\Models\ReportCard;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Student;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\ReportCardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SchoolProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function user(string $email): User
    {
        return User::where('email', $email)->firstOrFail();
    }

    public function test_profile_page_open_by_admin_only(): void
    {
        $this->actingAs($this->user('admin@smk.test'))
            ->get('/admin/school-profile')->assertStatus(200);

        $this->actingAs($this->user('guru@smk.test'))
            ->get('/admin/school-profile')->assertStatus(403);

        $this->actingAs($this->user('wali@smk.test'))
            ->get('/admin/school-profile')->assertStatus(403);

        $this->actingAs($this->user('kepala@smk.test'))
            ->get('/admin/school-profile')->assertStatus(403);
    }

    public function test_save_profile(): void
    {
        $this->actingAs($this->user('admin@smk.test'))->put('/admin/school-profile', [
            'school_name' => 'SMK IT AL-ABROR',
            'school_npsn' => '12345678',
            'school_address' => 'Jl. Raya No. 1',
            'school_city' => 'Bandung',
            'school_phone' => '022123456',
            'principal_name' => 'Budi Santoso',
            'principal_nip' => '198001012005011001',
        ])->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame('12345678', SystemSetting::where('key', 'school_npsn')->first()->value['value']);
        $this->assertSame('Bandung', SystemSetting::where('key', 'school_city')->first()->value['value']);
        $this->assertSame('Budi Santoso', SystemSetting::where('key', 'principal_name')->first()->value['value']);
        $this->assertSame('198001012005011001', SystemSetting::where('key', 'principal_nip')->first()->value['value']);
    }

    public function test_save_profile_requires_school_name(): void
    {
        $this->actingAs($this->user('admin@smk.test'))->put('/admin/school-profile', [
            'school_name' => '',
        ])->assertSessionHasErrors('school_name');
    }

    public function test_logo_must_be_image_max_2mb(): void
    {
        Storage::fake('public');

        $this->actingAs($this->user('admin@smk.test'))->put('/admin/school-profile', [
            'school_name' => 'SMK IT AL-ABROR',
            'logo' => UploadedFile::fake()->create('logo.txt', 100, 'text/plain'),
        ])->assertSessionHasErrors('logo');

        $this->actingAs($this->user('admin@smk.test'))->put('/admin/school-profile', [
            'school_name' => 'SMK IT AL-ABROR',
            'logo' => UploadedFile::fake()->image('logo.png'),
        ])->assertRedirect()->assertSessionHas('success');

        $path = SystemSetting::where('key', 'school_logo')->first()->value['value'];
        $this->assertNotEmpty($path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_print_settings_page_open_by_admin_only(): void
    {
        $this->actingAs($this->user('admin@smk.test'))
            ->get('/admin/print-settings')->assertStatus(200);

        $this->actingAs($this->user('guru@smk.test'))
            ->get('/admin/print-settings')->assertStatus(403);

        $this->actingAs($this->user('waka@smk.test'))
            ->get('/admin/print-settings')->assertStatus(403);
    }

    public function test_save_print_settings(): void
    {
        $this->actingAs($this->user('admin@smk.test'))->put('/admin/print-settings', [
            'print_city' => 'Bandung',
            'print_date' => '2026-06-20',
            'principal_name' => 'Budi Santoso',
            'principal_nip' => '198001012005011001',
            'print_watermark' => '1',
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertSame('Bandung', SystemSetting::where('key', 'print_city')->first()->value['value']);
        $this->assertSame('2026-06-20', SystemSetting::where('key', 'print_date')->first()->value['value']);
        $this->assertTrue(SystemSetting::where('key', 'print_watermark')->first()->value['value']);
        $this->assertSame('Budi Santoso', SystemSetting::where('key', 'principal_name')->first()->value['value']);
    }

    public function test_pdf_uses_profile_data(): void
    {
        $dep = Department::create(['code' => 'TKJ', 'name' => 'Teknik Komputer dan Jaringan']);
        $ay = AcademicYear::create(['name' => '2025/2026', 'start_date' => '2025-07-01', 'end_date' => '2026-06-30', 'is_active' => true]);
        $sem = Semester::create(['academic_year_id' => $ay->id, 'name' => 'Ganjil', 'is_active' => true]);
        $cls = SchoolClass::create(['department_id' => $dep->id, 'academic_year_id' => $ay->id, 'name' => 'X TKJ 1', 'level' => 'X']);
        $stu = Student::create(['nis' => '001', 'nisn' => 'N001', 'name' => 'Andi', 'class_id' => $cls->id, 'gender' => 'L']);
        $card = ReportCard::create([
            'student_id' => $stu->id, 'class_id' => $cls->id,
            'academic_year_id' => $ay->id, 'semester_id' => $sem->id,
            'status' => 'diterbitkan', 'issued_at' => now(),
        ]);

        SystemSetting::updateOrCreate(['key' => 'principal_name'], ['value' => ['value' => 'Budi Santoso']]);
        SystemSetting::updateOrCreate(['key' => 'principal_nip'], ['value' => ['value' => '198001012005011001']]);
        SystemSetting::updateOrCreate(['key' => 'print_city'], ['value' => ['value' => 'Bandung']]);
        SystemSetting::updateOrCreate(['key' => 'school_npsn'], ['value' => ['value' => '12345678']]);

        $data = app(ReportCardService::class)->pdfData($card->fresh());
        $this->assertSame('Budi Santoso', $data['principal_name']);
        $this->assertSame('198001012005011001', $data['principal_nip']);
        $this->assertSame('Bandung', $data['print_city']);
        $this->assertSame('12345678', $data['school_npsn']);
        $this->assertSame('SMK IT AL-ABROR', $data['school_name']);

        $this->actingAs($this->user('admin@smk.test'))
            ->get("/admin/report-cards/{$card->id}/pdf")
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'application/pdf');
    }
}
