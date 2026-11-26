<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Fase C (RDM): capaian P5 per siswa per proyek.
 * Satu baris per (proyek, siswa).
 */
class P5Score extends Model
{
    protected $fillable = [
        'p5_project_id',
        'student_id',
        'capaian',
        'deskripsi',
    ];

    public function project()
    {
        return $this->belongsTo(P5Project::class, 'p5_project_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
}
