<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Teacher extends Model { protected $fillable=['nip','name','gender','phone','address','user_id','photo'];

    /**
     * Akun login yang terhubung ke data guru ini (bila ada).
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Ekstrakurikuler yang dibina guru ini (Fase F).
     */
    public function supervisedExtracurriculars()
    {
        return $this->hasMany(Extracurricular::class, 'supervisor_id');
    }
}
