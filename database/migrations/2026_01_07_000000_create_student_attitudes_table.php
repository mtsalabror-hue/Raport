<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fase B (RDM): sikap sosial & spiritual per siswa per semester.
     * Satu baris per (siswa, tahun ajaran, semester, jenis).
     */
    public function up(): void
    {
        Schema::create('student_attitudes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->restrictOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->restrictOnDelete();
            $table->foreignId('semester_id')->constrained('semesters')->restrictOnDelete();
            $table->string('jenis'); // sosial | spiritual
            $table->string('predikat'); // Sangat Baik | Baik | Cukup | Kurang
            $table->text('deskripsi')->nullable();
            $table->timestamps();
            $table->unique(['student_id', 'academic_year_id', 'semester_id', 'jenis']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_attitudes');
    }
};
