<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fase D (RDM): keputusan kenaikan kelas / kelulusan per siswa
     * per semester. Satu baris per (siswa, tahun ajaran, semester).
     * Ditetapkan wali kelas hanya pada semester Genap.
     */
    public function up(): void
    {
        Schema::create('promotion_decisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->restrictOnDelete();
            $table->foreignId('class_id')->constrained('classes')->restrictOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->restrictOnDelete();
            $table->foreignId('semester_id')->constrained('semesters')->restrictOnDelete();
            $table->string('status'); // naik | tidak_naik
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['student_id', 'academic_year_id', 'semester_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotion_decisions');
    }
};
