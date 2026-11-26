<?php

namespace App\Services;

use App\Models\Internship;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

/**
 * Logika PKL Stage 11.
 *
 * - Tumpang tindih penempatan dicegah di kode (bukan migrasi):
 *   penempatan baru/ubah untuk siswa yang sama ditolak bila
 *   rentang tanggalnya beririsan dengan penempatan lain siswa itu.
 * - Nilai PKL TIDAK masuk ke rapor (belum ada keputusan desain
 *   eksplisit): service ini tidak menyentuh AssessmentService
 *   sama sekali; pengujian StageElevenTest membuktikannya.
 */
class InternshipService
{
    /**
     * Periksa apakah rentang [start, end] beririsan dengan
     * penempatan lain milik siswa yang sama. Mengembalikan
     * penempatan yang bertabrakan pertama, atau null.
     */
    public function findOverlap(int $studentId, string $startDate, string $endDate, ?int $excludeId = null): ?Internship
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->startOfDay();

        return Internship::query()
            ->where('student_id', $studentId)
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->where('start_date', '<=', $end->toDateString())
            ->where('end_date', '>=', $start->toDateString())
            ->orderBy('start_date')
            ->first();
    }

    /**
     * Validasi rentang + tumpang tindih. Melempar
     * ValidationException bila tanggal akhir sebelum tanggal awal
     * atau bila ada penempatan yang beririsan.
     *
     * @throws ValidationException
     */
    public function assertNoOverlap(int $studentId, string $startDate, string $endDate, ?int $excludeId = null): void
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->startOfDay();

        if ($end->lt($start)) {
            throw ValidationException::withMessages([
                'end_date' => 'Tanggal selesai PKL tidak boleh sebelum tanggal mulai.',
            ]);
        }

        $clash = $this->findOverlap($studentId, $startDate, $endDate, $excludeId);

        if ($clash) {
            throw ValidationException::withMessages([
                'start_date' => "Periode PKL {$start->toDateString()} s.d. {$end->toDateString()} tumpang tindih dengan penempatan {$clash->company_name} ({$clash->start_date->toDateString()} s.d. {$clash->end_date->toDateString()}).",
            ]);
        }
    }

    /**
     * Daftar nama mitra industri yang sudah dipakai (distinct),
     * untuk daftar perusahaan + isian tambah penempatan.
     *
     * @return array<int, string>
     */
    public function companies(): array
    {
        return Internship::query()
            ->distinct()
            ->orderBy('company_name')
            ->pluck('company_name')
            ->values()
            ->all();
    }

    /**
     * Rekap satu penempatan: rata-rata per aspek + rata-rata
     * keseluruhan + status periode.
     *
     * @return array{internship: Internship, status: string, average: float|null, aspects: array<int, array{aspect: string, score: float|null, note: string|null}>}
     */
    public function recapFor(Internship $internship): array
    {
        $internship->loadMissing(['assessments', 'student']);

        $aspects = $internship->assessments->map(fn ($a) => [
            'aspect' => $a->aspect,
            'score' => $a->score === null ? null : (float) $a->score,
            'note' => $a->note,
        ])->values()->all();

        return [
            'internship' => $internship,
            'status' => $internship->status(),
            'average' => $internship->averageScore(),
            'aspects' => $aspects,
        ];
    }

    /**
     * Rekap PKL per siswa: daftar penempatan + rata-rata gabungan
     * seluruh aspek bernilai di semua penempatannya.
     *
     * @return array{student_id: int, placements: array, average: float|null, total: int, finished: int, active: int}
     */
    public function recapForStudent(int $studentId): array
    {
        $placements = Internship::query()
            ->with('assessments')
            ->where('student_id', $studentId)
            ->orderBy('start_date')
            ->get();

        $scores = $placements->flatMap(fn ($p) => $p->assessments->whereNotNull('score')->pluck('score'));

        return [
            'student_id' => $studentId,
            'placements' => $placements->map(fn ($p) => $this->recapFor($p))->values()->all(),
            'average' => $scores->isEmpty() ? null : round($scores->avg(), 2, PHP_ROUND_HALF_UP),
            'total' => $placements->count(),
            'finished' => $placements->where(fn ($p) => $p->status() === 'selesai')->count(),
            'active' => $placements->where(fn ($p) => $p->status() === 'berjalan')->count(),
        ];
    }

    /**
     * Monitoring: penempatan dikelompokkan menurut status periode.
     *
     * @return array{berjalan: \Illuminate\Database\Eloquent\Collection, mendatang: \Illuminate\Database\Eloquent\Collection, selesai: \Illuminate\Database\Eloquent\Collection}
     */
    public function monitoring($query = null): array
    {
        $all = ($query ?? Internship::query()->with(['student', 'assessments']))
            ->orderBy('start_date')
            ->get();

        return [
            'berjalan' => $all->filter(fn ($p) => $p->status() === 'berjalan')->values(),
            'mendatang' => $all->filter(fn ($p) => $p->status() === 'mendatang')->values(),
            'selesai' => $all->filter(fn ($p) => $p->status() === 'selesai')->values(),
        ];
    }
}
