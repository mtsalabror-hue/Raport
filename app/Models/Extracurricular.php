<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Extracurricular extends Model
{
    protected $fillable = [
        'name',
        'description',
    ];

    public function studentRecords()
    {
        return $this->hasMany(StudentExtracurricular::class, 'extracurricular_id');
    }
}
