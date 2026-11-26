<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Semester extends Model { protected $table='semesters'; protected $fillable=['academic_year_id','name','is_active']; }
