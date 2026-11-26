<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Extracurricular extends Model
{
    protected $fillable = [
        'name',
        'description',
        // Fase F: guru pembina (nullable; ditetapkan admin).
        'supervisor_id',
    ];

    public function studentRecords()
    {
        return $this->hasMany(StudentExtracurricular::class, 'extracurricular_id');
    }

    /**
     * Guru pembina ekstrakurikuler ini (Fase F). Null bila belum
     * ditetapkan admin.
     */
    public function supervisor()
    {
        return $this->belongsTo(Teacher::class, 'supervisor_id');
    }
}
