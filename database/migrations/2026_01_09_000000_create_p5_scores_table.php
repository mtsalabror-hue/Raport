<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fase C (RDM): capaian P5 per siswa per proyek.
     * Satu baris per (proyek, siswa).
     */
    public function up(): void
    {
        Schema::create('p5_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('p5_project_id')->constrained('p5_projects')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->restrictOnDelete();
            $table->text('capaian')->nullable();
            $table->text('deskripsi')->nullable();
            $table->timestamps();
            $table->unique(['p5_project_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('p5_scores');
    }
};
