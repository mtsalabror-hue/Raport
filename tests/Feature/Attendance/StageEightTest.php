<?php

namespace Tests\Feature\Attendance;

use App\Models\AcademicYear;
use App\Models\ActivityLog;
use App\Models\AssessmentComponent;
use App\Models\AssessmentScore;
use App\Models\Department;
use App\Models\HomeroomAssignment;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StageEightTest extends TestCase
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
        $tch = Teacher::create(['nip' => '123', 'name' => 'Guru 1']);

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

    private function seedAttendance($stu, $cls, $ay, $sem, string $date, string $status): StudentAttendance
    {
        return StudentAttendance::create([
            'student_id' => $stu->id,
            'class_id' => $cls->id,
            'academic_year_id' => $ay->id,
            'semester_id' => $sem->id,
            'date' => $date,
            'status' => $status,
        ]);
    }

    private function bulkPayload($cls, $ay, $sem, string $date, array $records): array
    {
        return [
            'class_id' => $cls->id,
            'date' => $date,
            'academic_year_id' => $ay->id,
            'semester_id' => $sem->id,
            'records' => $records,
        ];
    }

    public function test_bulk_attendance_recorded_with_audit_log(): void
    {
        [, $ay, $sem1, , $cls, , $tch] = $this->seedParents();
        $stu1 = $this->makeStudent('001', 'Siswa 1', $cls->id);
        $stu2 = $this->makeStudent('002', 'Siswa 2', $cls->id);

        $this->loginAs('guru', $tch->id);
        $res = $this->post('/guru/attendance', $this->bulkPayload($cls, $ay, $sem1, '2025-08-04', [
            ['student_id' => $stu1->id, 'status' => 'H'],
            ['student_id' => $stu2->id, 'status' => 'S', 'note' => 'Demam'],
        ]));

        $res->assertSessionHas('success');
        // Cast `date` pada model menyimpan 'Y-m-d H:i:s' ke kolom date di
        // SQLite, jadi periksa lewat whereDate, bukan assertDatabaseHas.
        $this->assertTrue(StudentAttendance::where('student_id', $stu1->id)->whereDate('date', '2025-08-04')->where('status', 'H')->exists());
        $this->assertTrue(StudentAttendance::where('student_id', $stu2->id)->whereDate('date', '2025-08-04')->where('status', 'S')->where('note', 'Demam')->exists());
        $this->assertDatabaseHas('activity_logs', ['action' => 'attendance.created']);
        $this->assertEquals(2, ActivityLog::where('action', 'attendance.created')->count());
    }

    public function test_duplicate_attendance_rejected_by_validation(): void
    {
        [, $ay, $sem1, , $cls, , $tch] = $this->seedParents();
        $stu1 = $this->makeStudent('001', 'Siswa 1', $cls->id);
        $stu2 = $this->makeStudent('002', 'Siswa 2', $cls->id);
        $this->seedAttendance($stu1, $cls, $ay, $sem1, '2025-08-04', 'H');

        $this->loginAs('guru', $tch->id);
        $res = $this->post('/guru/attendance', $this->bulkPayload($cls, $ay, $sem1, '2025-08-04', [
            ['student_id' => $stu1->id, 'status' => 'S'],
            ['student_id' => $stu2->id, 'status' => 'H'],
        ]));

        $res->assertSessionHasErrors('records');
        // Tidak ada satu pun catatan baru yang tersimpan (transaksi utuh).
        $this->assertEquals(1, StudentAttendance::count());
        $this->assertDatabaseMissing('student_attendances', ['student_id' => $stu2->id, 'date' => '2025-08-04']);
    }

    public function test_duplicate_blocked_by_unique_constraint(): void
    {
        [, $ay, $sem1, , $cls, , $tch] = $this->seedParents();
        $stu1 = $this->makeStudent('001', 'Siswa 1', $cls->id);
        $this->seedAttendance($stu1, $cls, $ay, $sem1, '2025-08-04', 'H');

        $this->expectException(QueryException::class);
        StudentAttendance::create([
            'student_id' => $stu1->id,
            'class_id' => $cls->id,
            'academic_year_id' => $ay->id,
            'semester_id' => $sem1->id,
            'date' => '2025-08-04',
            'status' => 'I',
        ]);
    }

    public function test_correction_updates_status_and_logs_audit_trail(): void
    {
        [, $ay, $sem1, , $cls, , $tch] = $this->seedParents();
        $stu1 = $this->makeStudent('001', 'Siswa 1', $cls->id);
        $record = $this->seedAttendance($stu1, $cls, $ay, $sem1, '2025-08-04', 'A');

        $this->loginAs('guru', $tch->id);
        $res = $this->post("/guru/attendance/{$record->id}/correct", ['status' => 'H', 'note' => 'Ternyata hadir']);

        $res->assertSessionHas('success');
        $this->assertEquals('H', $record->fresh()->status);
        $this->assertEquals('Ternyata hadir', $record->fresh()->note);

        $log = ActivityLog::where('action', 'attendance.corrected')->first();
        $this->assertNotNull($log);
        $this->assertEquals(StudentAttendance::class, $log->model_type);
        $this->assertEquals($record->id, $log->model_id);
        $this->assertEquals('A', $log->metadata['old_status']);
        $this->assertEquals('H', $log->metadata['new_status']);
    }

    public function test_recap_counts_and_percentage(): void
    {
        [, $ay, $sem1, , $cls, , $tch] = $this->seedParents();
        $stu1 = $this->makeStudent('001', 'Siswa 1', $cls->id);
        $stu2 = $this->makeStudent('002', 'Siswa 2', $cls->id);

        // Siswa 1: 2H, 1S, 1A dari 4 hari -> 50%.
        $this->seedAttendance($stu1, $cls, $ay, $sem1, '2025-08-04', 'H');
        $this->seedAttendance($stu1, $cls, $ay, $sem1, '2025-08-05', 'H');
        $this->seedAttendance($stu1, $cls, $ay, $sem1, '2025-08-06', 'S');
        $this->seedAttendance($stu1, $cls, $ay, $sem1, '2025-08-07', 'A');
        // Siswa 2: 3H dari 3 hari -> 100%.
        $this->seedAttendance($stu2, $cls, $ay, $sem1, '2025-08-04', 'H');
        $this->seedAttendance($stu2, $cls, $ay, $sem1, '2025-08-05', 'H');
        $this->seedAttendance($stu2, $cls, $ay, $sem1, '2025-08-06', 'H');

        $this->loginAs('guru', $tch->id);
        $res = $this->get("/guru/attendance/recap?class_id={$cls->id}&academic_year_id={$ay->id}&semester_id={$sem1->id}");
        $res->assertStatus(200);

        $res->assertViewHas('rows', function ($rows) use ($stu1, $stu2) {
            $byId = collect($rows)->keyBy(fn ($r) => $r['student']->id);

            return $byId[$stu1->id]['hadir'] === 2
                && $byId[$stu1->id]['sakit'] === 1
                && $byId[$stu1->id]['alpa'] === 1
                && $byId[$stu1->id]['total'] === 4
                && $byId[$stu1->id]['persentase_hadir'] === 50.0
                && $byId[$stu2->id]['hadir'] === 3
                && $byId[$stu2->id]['persentase_hadir'] === 100.0;
        });

        $res->assertViewHas('classTotal', function ($total) {
            return $total['hadir'] === 5 && $total['sakit'] === 1
                && $total['alpa'] === 1 && $total['total'] === 7
                && $total['persentase_hadir'] === 71.43;
        });
    }

    public function test_history_date_filter(): void
    {
        [, $ay, $sem1, , $cls, , $tch] = $this->seedParents();
        $stu1 = $this->makeStudent('001', 'Siswa 1', $cls->id);
        $this->seedAttendance($stu1, $cls, $ay, $sem1, '2025-08-04', 'H');
        $this->seedAttendance($stu1, $cls, $ay, $sem1, '2025-08-11', 'S');
        $this->seedAttendance($stu1, $cls, $ay, $sem1, '2025-08-18', 'I');

        $this->loginAs('guru', $tch->id);
        $res = $this->get('/guru/attendance/history?date_from=2025-08-10&date_to=2025-08-12');
        $res->assertStatus(200);
        $res->assertViewHas('records', function ($records) {
            return $records->total() === 1
                && $records->first()->date->toDateString() === '2025-08-11';
        });
    }

    public function test_other_teacher_forbidden_when_assignment_exists(): void
    {
        [, $ay, $sem1, , $cls, $sub, $tch] = $this->seedParents();
        $tch2 = Teacher::create(['nip' => '124', 'name' => 'Guru 2']);
        $stu1 = $this->makeStudent('001', 'Siswa 1', $cls->id);

        TeachingAssignment::create([
            'teacher_id' => $tch->id, 'subject_id' => $sub->id, 'class_id' => $cls->id,
            'academic_year_id' => $ay->id, 'semester_id' => $sem1->id,
        ]);

        // Guru 2 bukan pemegang penugasan kelas ini -> 403.
        $this->loginAs('guru', $tch2->id);
        $res = $this->post('/guru/attendance', $this->bulkPayload($cls, $ay, $sem1, '2025-08-04', [
            ['student_id' => $stu1->id, 'status' => 'H'],
        ]));
        $res->assertForbidden();

        $res = $this->get("/guru/attendance/recap?class_id={$cls->id}&academic_year_id={$ay->id}&semester_id={$sem1->id}");
        $res->assertForbidden();

        // Guru 1 (pemegang penugasan) boleh.
        $this->loginAs('guru', $tch->id);
        $res = $this->post('/guru/attendance', $this->bulkPayload($cls, $ay, $sem1, '2025-08-04', [
            ['student_id' => $stu1->id, 'status' => 'H'],
        ]));
        $res->assertSessionHas('success');
    }

    public function test_wali_only_own_homeroom_class(): void
    {
        [$dep, $ay, $sem1, , $cls, , $tch] = $this->seedParents();
        $cls2 = SchoolClass::create(['department_id' => $dep->id, 'academic_year_id' => $ay->id, 'name' => 'X TKJ 2', 'level' => 'X']);
        $stu1 = $this->makeStudent('001', 'Siswa 1', $cls->id);
        $this->seedAttendance($stu1, $cls, $ay, $sem1, '2025-08-04', 'H');

        HomeroomAssignment::create([
            'teacher_id' => $tch->id, 'class_id' => $cls->id,
            'academic_year_id' => $ay->id, 'semester_id' => $sem1->id,
        ]);

        // Wali kelas X TKJ 1 melihat rekap kelasnya sendiri -> 200.
        $this->loginAs('wali_kelas', $tch->id);
        $res = $this->get("/wali/attendance/recap?class_id={$cls->id}&academic_year_id={$ay->id}&semester_id={$sem1->id}");
        $res->assertStatus(200);
        $res->assertViewHas('readOnly', true);
        $res->assertViewHas('rows', fn ($rows) => count($rows) === 1 && $rows[0]['hadir'] === 1);

        // Wali kelas lain -> 403.
        $res = $this->get("/wali/attendance/recap?class_id={$cls2->id}&academic_year_id={$ay->id}&semester_id={$sem1->id}");
        $res->assertForbidden();
    }

    public function test_recap_does_not_mix_semesters(): void
    {
        [, $ay, $sem1, $sem2, $cls, , $tch] = $this->seedParents();
        $stu1 = $this->makeStudent('001', 'Siswa 1', $cls->id);

        $this->seedAttendance($stu1, $cls, $ay, $sem1, '2025-08-04', 'H');
        $this->seedAttendance($stu1, $cls, $ay, $sem1, '2025-08-05', 'A');
        $this->seedAttendance($stu1, $cls, $ay, $sem2, '2026-01-05', 'H');
        $this->seedAttendance($stu1, $cls, $ay, $sem2, '2026-01-06', 'H');
        $this->seedAttendance($stu1, $cls, $ay, $sem2, '2026-01-07', 'H');

        $this->loginAs('guru', $tch->id);

        $res = $this->get("/guru/attendance/recap?class_id={$cls->id}&academic_year_id={$ay->id}&semester_id={$sem1->id}");
        $res->assertViewHas('rows', fn ($rows) => $rows[0]['total'] === 2 && $rows[0]['persentase_hadir'] === 50.0);

        $res = $this->get("/guru/attendance/recap?class_id={$cls->id}&academic_year_id={$ay->id}&semester_id={$sem2->id}");
        $res->assertViewHas('rows', fn ($rows) => $rows[0]['total'] === 3 && $rows[0]['persentase_hadir'] === 100.0);
    }

    public function test_academic_recap_kktp_status(): void
    {
        [, $ay, $sem1, , $cls, $sub, $tch] = $this->seedParents();
        $stu1 = $this->makeStudent('001', 'Siswa 1', $cls->id);
        $stu2 = $this->makeStudent('002', 'Siswa 2', $cls->id);

        $component = AssessmentComponent::create([
            'name' => 'Sumatif', 'type' => 'sumatif', 'weight' => 100,
            'subject_id' => $sub->id, 'kktp' => 75, 'is_active' => true,
        ]);

        foreach ([[$stu1, 80, 'terkirim'], [$stu2, 60, 'terkirim']] as [$stu, $score, $status]) {
            AssessmentScore::create([
                'student_id' => $stu->id, 'subject_id' => $sub->id, 'component_id' => $component->id,
                'class_id' => $cls->id, 'academic_year_id' => $ay->id, 'semester_id' => $sem1->id,
                'teacher_id' => $tch->id, 'score' => $score, 'status' => $status,
            ]);
        }

        $this->loginAs('guru', $tch->id);
        $res = $this->get("/guru/attendance/academic-recap?class_id={$cls->id}&subject_id={$sub->id}&academic_year_id={$ay->id}&semester_id={$sem1->id}");
        $res->assertStatus(200);
        $res->assertViewHas('kktp', 75.0);
        $res->assertViewHas('finals', function ($finals) use ($stu1, $stu2) {
            return $finals[$stu1->id]['final_score'] === 80.0
                && $finals[$stu1->id]['passed'] === true
                && $finals[$stu2->id]['final_score'] === 60.0
                && $finals[$stu2->id]['passed'] === false;
        });
        $res->assertViewHas('stats', function ($stats) {
            return $stats['tuntas'] === 1 && $stats['belum_tuntas'] === 1 && $stats['total_siswa'] === 2;
        });
    }

    public function test_wali_academic_recap_only_submitted_scores(): void
    {
        [, $ay, $sem1, , $cls, $sub, $tch] = $this->seedParents();
        $stu1 = $this->makeStudent('001', 'Siswa 1', $cls->id);
        $stu2 = $this->makeStudent('002', 'Siswa 2', $cls->id);

        $component = AssessmentComponent::create([
            'name' => 'Sumatif', 'type' => 'sumatif', 'weight' => 100,
            'subject_id' => $sub->id, 'kktp' => 75, 'is_active' => true,
        ]);

        foreach ([[$stu1, 80, 'draft'], [$stu2, 90, 'terkirim']] as [$stu, $score, $status]) {
            AssessmentScore::create([
                'student_id' => $stu->id, 'subject_id' => $sub->id, 'component_id' => $component->id,
                'class_id' => $cls->id, 'academic_year_id' => $ay->id, 'semester_id' => $sem1->id,
                'teacher_id' => $tch->id, 'score' => $score, 'status' => $status,
            ]);
        }

        HomeroomAssignment::create([
            'teacher_id' => $tch->id, 'class_id' => $cls->id,
            'academic_year_id' => $ay->id, 'semester_id' => $sem1->id,
        ]);

        $this->loginAs('wali_kelas', $tch->id);
        $res = $this->get("/wali/attendance/academic-recap?class_id={$cls->id}&subject_id={$sub->id}&academic_year_id={$ay->id}&semester_id={$sem1->id}");
        $res->assertStatus(200);
        $res->assertViewHas('students', function ($students) use ($stu1, $stu2) {
            $ids = $students->pluck('id')->all();

            return $ids === [$stu2->id];
        });
    }

    public function test_admin_monitoring_shows_draft_scores(): void
    {
        [, $ay, $sem1, , $cls, $sub, $tch] = $this->seedParents();
        $stu1 = $this->makeStudent('001', 'Siswa 1', $cls->id);
        $stu2 = $this->makeStudent('002', 'Siswa 2', $cls->id);

        $component = AssessmentComponent::create([
            'name' => 'Sumatif', 'type' => 'sumatif', 'weight' => 100,
            'subject_id' => $sub->id, 'kktp' => 75, 'is_active' => true,
        ]);

        foreach ([[$stu1, 'draft'], [$stu2, 'draft']] as [$stu, $status]) {
            AssessmentScore::create([
                'student_id' => $stu->id, 'subject_id' => $sub->id, 'component_id' => $component->id,
                'class_id' => $cls->id, 'academic_year_id' => $ay->id, 'semester_id' => $sem1->id,
                'teacher_id' => $tch->id, 'score' => 80, 'status' => $status,
            ]);
        }

        // Satu nilai sudah terkirim (komponen berbeda agar tidak bentrok
        // dengan unique student/subject/component/ay/sem) -> tidak masuk
        // monitoring draft.
        $component2 = AssessmentComponent::create([
            'name' => 'Formatif', 'type' => 'formatif', 'weight' => 0,
            'subject_id' => $sub->id, 'kktp' => 75, 'is_active' => true,
        ]);
        AssessmentScore::create([
            'student_id' => $stu1->id, 'subject_id' => $sub->id, 'component_id' => $component2->id,
            'class_id' => $cls->id, 'academic_year_id' => $ay->id, 'semester_id' => $sem1->id,
            'teacher_id' => $tch->id, 'score' => 70, 'status' => 'terkirim',
        ]);

        $this->loginAs('admin');
        $res = $this->get("/admin/attendance/monitoring?academic_year_id={$ay->id}&semester_id={$sem1->id}");
        $res->assertStatus(200);
        $res->assertViewHas('totalDrafts', 2);
        $res->assertViewHas('drafts', function ($drafts) {
            return $drafts->count() === 1 && (int) $drafts->first()->draft_count === 2;
        });
    }
}
