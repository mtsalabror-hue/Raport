<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Penempatan PKL seorang siswa di sebuah perusahaan/mitra industri.
 *
 * Catatan skema (Stage 11): tabel internships TIDAK punya kolom
 * tahun ajaran/semester — periode PKL ditentukan dari
 * start_date/end_date. Kolom supervisor menampung nama guru
 * pembimbing (string bebas, bukan FK ke teachers); pembimbing
 * industri tidak punya kolom terpisah — bila perlu dicatat,
 * cantumkan di supervisor atau di penilaian (lihat PROGRESS).
 */
class Internship extends Model
{
    protected $fillable = [
        'student_id',
        'company_name',
        'start_date',
        'end_date',
        'supervisor',
        'grade',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function assessments()
    {
        return $this->hasMany(InternshipAssessment::class, 'internship_id');
    }

    /**
     * Status penempatan relatif terhadap tanggal hari ini:
     * mendatang | berjalan | selesai.
     */
    public function status(string|\DateTimeInterface|null $today = null): string
    {
        $today = $today ? \Illuminate\Support\Carbon::parse($today)->startOfDay() : now()->startOfDay();

        if ($today->lt($this->start_date)) {
            return 'mendatang';
        }

        if ($today->gt($this->end_date)) {
            return 'selesai';
        }

        return 'berjalan';
    }

    public function isActive(string|\DateTimeInterface|null $today = null): bool
    {
        return $this->status($today) === 'berjalan';
    }

    /**
     * Rata-rata nilai per aspek yang sudah terisi (2 desimal),
     * atau null bila belum ada aspek yang bernilai.
     */
    public function averageScore(): ?float
    {
        $scores = $this->assessments()->whereNotNull('score')->pluck('score');

        if ($scores->isEmpty()) {
            return null;
        }

        return round($scores->avg(), 2, PHP_ROUND_HALF_UP);
    }
}
