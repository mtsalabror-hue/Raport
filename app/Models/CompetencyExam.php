<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Satu baris hasil UKK: satu siswa + satu mapel pada satu
 * tanggal ujian. Bila satu mapel diuji lewat beberapa komponen,
 * cara yang dipakai Stage 11 adalah satu baris per siswa+mapel
 * per exam_date; komponen tambahan dibedakan lewat baris
 * "Komponen: ..." di kolom note (lihat STAGE-11-PROGRESS.md).
 *
 * Catatan skema: tabel competency_exams TIDAK punya kolom
 * penguji. Bila perlu mencatat penguji, pakai konvensi baris
 * "Penguji: <nama>" di awal kolom note; helper examiner()
 * membacanya kembali. Jangan membuat migrasi baru untuk ini
 * sebelum sekolah mengonfirmasi.
 */
class CompetencyExam extends Model
{
    protected $fillable = [
        'student_id',
        'subject_id',
        'score',
        'note',
        'exam_date',
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'exam_date' => 'date',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    /**
     * Nama penguji dari konvensi baris "Penguji: <nama>" di note,
     * atau null bila tidak dicatat.
     */
    public function examiner(): ?string
    {
        if (empty($this->note)) {
            return null;
        }

        foreach (explode("\n", $this->note) as $line) {
            if (preg_match('/^\s*Penguji\s*:\s*(.+?)\s*$/i', $line, $m)) {
                return $m[1];
            }
        }

        return null;
    }

    /**
     * Isi note tanpa baris "Penguji: ...", untuk tampilan.
     */
    public function noteWithoutExaminer(): ?string
    {
        if (empty($this->note)) {
            return null;
        }

        $lines = array_values(array_filter(
            explode("\n", $this->note),
            fn ($line) => ! preg_match('/^\s*Penguji\s*:/i', $line)
        ));

        $text = trim(implode("\n", $lines));

        return $text === '' ? null : $text;
    }
}
