<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fase C (RDM): proyek P5 (Projek Penguatan Profil Pelajar
     * Pancasila) per kelas per semester. Satu proyek = satu kelas +
     * satu koordinator (guru, nullable).
     */
    public function up(): void
    {
        Schema::create('p5_projects', function (Blueprint $table) {
            $table->id();
            $table->string('tema');
            $table->string('dimensi'); // salah satu dimensi Profil Pelajar Pancasila
            $table->foreignId('academic_year_id')->constrained('academic_years')->restrictOnDelete();
            $table->foreignId('semester_id')->constrained('semesters')->restrictOnDelete();
            $table->foreignId('class_id')->constrained('classes')->restrictOnDelete();
            $table->foreignId('coordinator_id')->nullable()->constrained('teachers')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('p5_projects');
    }
};
