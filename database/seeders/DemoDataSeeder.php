<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\AssessmentComponent;
use App\Models\AssessmentScore;
use App\Models\Department;
use App\Models\HomeroomAssignment;
use App\Models\ReportCard;
use App\Models\ReportCardSubject;
use App\Models\SchoolClass;
use App\Models\Semester;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingAssignment;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $depTkj = Department::firstOrCreate(['code' => 'TKJ'], ['name' => 'Teknik Komputer dan Jaringan']);
        $depRpl = Department::firstOrCreate(['code' => 'RPL'], ['name' => 'Rekayasa Perangkat Lunak']);

        $ay = AcademicYear::firstOrCreate(
            ['name' => '2025/2026'],
            ['start_date' => '2025-07-01', 'end_date' => '2026-06-30', 'is_active' => true]
        );
        $sem = Semester::firstOrCreate(
            ['academic_year_id' => $ay->id, 'name' => 'Ganjil'],
            ['is_active' => true]
        );

        $kelasX = SchoolClass::firstOrCreate(
            ['name' => 'X TKJ 1'],
            ['department_id' => $depTkj->id, 'academic_year_id' => $ay->id, 'level' => 'X']
        );
        $kelasXI = SchoolClass::firstOrCreate(
            ['name' => 'XI RPL 1'],
            ['department_id' => $depRpl->id, 'academic_year_id' => $ay->id, 'level' => 'XI']
        );

        $guru1 = Teacher::firstOrCreate(['nip' => '198001011'], ['name' => 'Budi Santoso', 'gender' => 'L']);
        $guru2 = Teacher::firstOrCreate(['nip' => '198502022'], ['name' => 'Siti Aminah', 'gender' => 'P']);
        $guru3 = Teacher::firstOrCreate(['nip' => '199003033'], ['name' => 'Agus Wijaya', 'gender' => 'L']);

        $mtk = Subject::firstOrCreate(['code' => 'MTK'], ['name' => 'Matematika', 'department_id' => $depTkj->id]);
        $inf = Subject::firstOrCreate(['code' => 'INF'], ['name' => 'Informatika', 'department_id' => $depTkj->id]);
        $bind = Subject::firstOrCreate(['code' => 'BIND'], ['name' => 'Bahasa Indonesia', 'department_id' => $depTkj->id]);

        // Hubungkan akun demo guru/wali ke data guru.
        User::where('email', 'guru@smk.test')->update(['teacher_id' => $guru1->id]);
        User::where('email', 'wali@smk.test')->update(['teacher_id' => $guru2->id]);

        // Penugasan mengajar.
        foreach ([
            [$guru1, $mtk, $kelasX], [$guru1, $inf, $kelasX],
            [$guru2, $bind, $kelasX], [$guru3, $mtk, $kelasXI],
        ] as [$t, $s, $c]) {
            TeachingAssignment::firstOrCreate([
                'teacher_id' => $t->id, 'subject_id' => $s->id, 'class_id' => $c->id,
                'academic_year_id' => $ay->id, 'semester_id' => $sem->id,
            ], ['hours' => 4]);
        }

        // Wali kelas X TKJ 1.
        HomeroomAssignment::firstOrCreate([
            'class_id' => $kelasX->id, 'academic_year_id' => $ay->id, 'semester_id' => $sem->id,
        ], ['teacher_id' => $guru2->id]);

        // Siswa X TKJ 1.
        $names = [
            ['24001', '240001', 'Ahmad Fauzi', 'L'],
            ['24002', '240002', 'Dewi Lestari', 'P'],
            ['24003', '240003', 'Rizky Pratama', 'L'],
            ['24004', '240004', 'Putri Ayu', 'P'],
            ['24005', '240005', 'Budi Hartono', 'L'],
            ['24006', '240006', 'Sari Melati', 'P'],
        ];
        $students = [];
        foreach ($names as [$nis, $nisn, $name, $gender]) {
            $students[] = Student::firstOrCreate(
                ['nis' => $nis],
                ['nisn' => $nisn, 'name' => $name, 'gender' => $gender, 'class_id' => $kelasX->id]
            );
        }

        // Komponen penilaian per mapel (total bobot 100).
        $components = [];
        foreach ([$mtk, $inf, $bind] as $subject) {
            foreach ([
                ['Tugas Harian', 'formatif', 30],
                ['Ujian Tengah Semester', 'sumatif', 40],
                ['Praktik', 'praktik', 20],
                ['Proyek', 'proyek', 10],
            ] as [$cname, $type, $weight]) {
                $components[$subject->id][] = AssessmentComponent::firstOrCreate([
                    'name' => $cname, 'type' => $type, 'subject_id' => $subject->id,
                ], ['weight' => $weight, 'kktp' => 75, 'is_active' => true]);
            }
        }

        // Nilai: MTK terkirim, INF draft, BIND terkirim.
        $scores = [
            '24001' => [82, 78, 85, 80],
            '24002' => [90, 88, 92, 85],
            '24003' => [70, 65, 75, 72],
            '24004' => [88, 84, 80, 86],
            '24005' => [60, 68, 62, 65],
            '24006' => [95, 92, 90, 94],
        ];
        foreach ($students as $student) {
            foreach ([$mtk, $bind] as $subject) {
                $status = 'terkirim';
                foreach ($components[$subject->id] as $i => $comp) {
                    AssessmentScore::firstOrCreate([
                        'student_id' => $student->id, 'subject_id' => $subject->id,
                        'component_id' => $comp->id, 'academic_year_id' => $ay->id,
                        'semester_id' => $sem->id,
                    ], [
                        'teacher_id' => $subject->id === $mtk->id ? $guru1->id : $guru2->id,
                        'class_id' => $kelasX->id, 'score' => $scores[$student->nis][$i], 'status' => $status,
                    ]);
                }
            }
            foreach ($components[$inf->id] as $i => $comp) {
                AssessmentScore::firstOrCreate([
                    'student_id' => $student->id, 'subject_id' => $inf->id,
                    'component_id' => $comp->id, 'academic_year_id' => $ay->id,
                    'semester_id' => $sem->id,
                ], [
                    'teacher_id' => $guru1->id, 'class_id' => $kelasX->id,
                    'score' => $scores[$student->nis][$i], 'status' => 'draft',
                ]);
            }
        }

        // Presensi 5 hari terakhir untuk X TKJ 1.
        $statuses = ['H', 'H', 'H', 'S', 'H', 'I'];
        for ($d = 0; $d < 5; $d++) {
            $date = now()->subDays($d)->format('Y-m-d');
            foreach ($students as $i => $student) {
                $status = ($d === 2 && $i === 3) ? 'S' : (($d === 3 && $i === 4) ? 'I' : 'H');
                StudentAttendance::firstOrCreate([
                    'student_id' => $student->id, 'date' => $date,
                ], [
                    'class_id' => $kelasX->id, 'academic_year_id' => $ay->id,
                    'semester_id' => $sem->id, 'status' => $status,
                ]);
            }
        }

        // Rapor: satu diajukan, satu diterbitkan (dengan snapshot).
        $card1 = ReportCard::firstOrCreate([
            'student_id' => $students[0]->id, 'academic_year_id' => $ay->id, 'semester_id' => $sem->id,
        ], ['class_id' => $kelasX->id, 'status' => 'diajukan']);
        $card2 = ReportCard::firstOrCreate([
            'student_id' => $students[1]->id, 'academic_year_id' => $ay->id, 'semester_id' => $sem->id,
        ], [
            'class_id' => $kelasX->id, 'status' => 'diterbitkan',
            'issued_at' => now(), 'issued_by' => User::where('email', 'admin@smk.test')->first()->id,
        ]);
        foreach ([$mtk, $bind] as $subject) {
            ReportCardSubject::firstOrCreate([
                'report_card_id' => $card2->id, 'subject_id' => $subject->id,
            ], [
                'final_score' => $subject->id === $mtk->id ? 81.50 : 86.20,
                'description' => 'Mencapai KKTP',
            ]);
        }
    }
}
