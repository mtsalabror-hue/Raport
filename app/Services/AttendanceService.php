<?php

namespace App\Services;

use App\Models\Student;
use App\Models\StudentAttendance;
use Illuminate\Support\Facades\DB;

/**
 * Logika rekap presensi Stage 8.
 *
 * Semua rekap memakai periode akademik eksplisit (academic_year_id +
 * semester_id) dan tidak pernah mencampur lintas semester. Agregasi
 * dilakukan dengan satu query GROUP BY per rekap (tanpa N+1).
 *
 * Bentuk hasil per siswa:
 *   ['student_id' => int, 'hadir' => int, 'sakit' => int, 'izin' => int,
 *    'alpa' => int, 'total' => int, 'persentase_hadir' => float|null]
 * persentase_hadir null bila total 0 (belum ada catatan).
 */
class AttendanceService
{
    /**
     * Agregasi mentah H/S/I/A per siswa untuk satu cakupan.
     * $scope boleh berisi: class_id, academic_year_id, semester_id,
     * date_from, date_to. Kunci hasil: student_id.
     */
    public function aggregate(array $scope): array
    {
        $query = StudentAttendance::query()
            ->select('student_id')
            ->selectRaw("SUM(CASE WHEN status = 'H' THEN 1 ELSE 0 END) as hadir")
            ->selectRaw("SUM(CASE WHEN status = 'S' THEN 1 ELSE 0 END) as sakit")
            ->selectRaw("SUM(CASE WHEN status = 'I' THEN 1 ELSE 0 END) as izin")
            ->selectRaw("SUM(CASE WHEN status = 'A' THEN 1 ELSE 0 END) as alpa")
            ->selectRaw('COUNT(*) as total')
            ->groupBy('student_id');

        foreach (['class_id', 'academic_year_id', 'semester_id'] as $key) {
            if (! empty($scope[$key])) {
                $query->where($key, $scope[$key]);
            }
        }

        if (! empty($scope['date_from'])) {
            $query->whereDate('date', '>=', $scope['date_from']);
        }

        if (! empty($scope['date_to'])) {
            $query->whereDate('date', '<=', $scope['date_to']);
        }

        $rows = $query->get();

        $result = [];

        foreach ($rows as $row) {
            $total = (int) $row->total;

            $result[$row->student_id] = [
                'student_id' => (int) $row->student_id,
                'hadir' => (int) $row->hadir,
                'sakit' => (int) $row->sakit,
                'izin' => (int) $row->izin,
                'alpa' => (int) $row->alpa,
                'total' => $total,
                'persentase_hadir' => $total > 0
                    ? round($row->hadir / $total * 100, 2, PHP_ROUND_HALF_UP)
                    : null,
            ];
        }

        return $result;
    }

    /**
     * Rekap per siswa untuk satu kelas + periode (AY + semester wajib).
     * Siswa tanpa catatan tetap muncul dengan angka 0.
     *
     * @return array<int, array{student: Student, hadir: int, sakit: int, izin: int, alpa: int, total: int, persentase_hadir: float|null}>
     */
    public function recapPerStudent(int $classId, int $academicYearId, int $semesterId): array
    {
        $students = Student::query()
            ->where('class_id', $classId)
            ->orderBy('name')
            ->get();

        $aggregates = $this->aggregate([
            'class_id' => $classId,
            'academic_year_id' => $academicYearId,
            'semester_id' => $semesterId,
        ]);

        $result = [];

        foreach ($students as $student) {
            $agg = $aggregates[$student->id] ?? [
                'hadir' => 0, 'sakit' => 0, 'izin' => 0, 'alpa' => 0,
                'total' => 0, 'persentase_hadir' => null,
            ];

            $result[] = array_merge(['student' => $student], $agg);
        }

        return $result;
    }

    /**
     * Rekap per kelas: total H/S/I/A seluruh siswa pada satu periode.
     */
    public function recapPerClass(int $classId, int $academicYearId, int $semesterId): array
    {
        $row = StudentAttendance::query()
            ->where('class_id', $classId)
            ->where('academic_year_id', $academicYearId)
            ->where('semester_id', $semesterId)
            ->selectRaw("SUM(CASE WHEN status = 'H' THEN 1 ELSE 0 END) as hadir")
            ->selectRaw("SUM(CASE WHEN status = 'S' THEN 1 ELSE 0 END) as sakit")
            ->selectRaw("SUM(CASE WHEN status = 'I' THEN 1 ELSE 0 END) as izin")
            ->selectRaw("SUM(CASE WHEN status = 'A' THEN 1 ELSE 0 END) as alpa")
            ->selectRaw('COUNT(*) as total')
            ->first();

        $total = (int) ($row->total ?? 0);

        return [
            'hadir' => (int) ($row->hadir ?? 0),
            'sakit' => (int) ($row->sakit ?? 0),
            'izin' => (int) ($row->izin ?? 0),
            'alpa' => (int) ($row->alpa ?? 0),
            'total' => $total,
            'persentase_hadir' => $total > 0
                ? round((int) ($row->hadir ?? 0) / $total * 100, 2, PHP_ROUND_HALF_UP)
                : null,
        ];
    }

    /**
     * Rekap per semester: total H/S/I/A per kelas dalam satu periode.
     * Dipakai admin untuk membandingkan kelas dalam semester yang sama.
     *
     * @return array<int, array{class_id: int, hadir: int, sakit: int, izin: int, alpa: int, total: int, persentase_hadir: float|null}>
     */
    public function recapPerSemester(int $academicYearId, int $semesterId): array
    {
        $rows = StudentAttendance::query()
            ->where('academic_year_id', $academicYearId)
            ->where('semester_id', $semesterId)
            ->select('class_id')
            ->selectRaw("SUM(CASE WHEN status = 'H' THEN 1 ELSE 0 END) as hadir")
            ->selectRaw("SUM(CASE WHEN status = 'S' THEN 1 ELSE 0 END) as sakit")
            ->selectRaw("SUM(CASE WHEN status = 'I' THEN 1 ELSE 0 END) as izin")
            ->selectRaw("SUM(CASE WHEN status = 'A' THEN 1 ELSE 0 END) as alpa")
            ->selectRaw('COUNT(*) as total')
            ->groupBy('class_id')
            ->get();

        $result = [];

        foreach ($rows as $row) {
            $total = (int) $row->total;

            $result[(int) $row->class_id] = [
                'class_id' => (int) $row->class_id,
                'hadir' => (int) $row->hadir,
                'sakit' => (int) $row->sakit,
                'izin' => (int) $row->izin,
                'alpa' => (int) $row->alpa,
                'total' => $total,
                'persentase_hadir' => $total > 0
                    ? round((int) $row->hadir / $total * 100, 2, PHP_ROUND_HALF_UP)
                    : null,
            ];
        }

        return $result;
    }
}
