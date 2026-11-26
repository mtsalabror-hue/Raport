<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Student extends Model { protected $fillable=['nis','nisn','name','gender','birth_date','address','class_id','user_id']; }
