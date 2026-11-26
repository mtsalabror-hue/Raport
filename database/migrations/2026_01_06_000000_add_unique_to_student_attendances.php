<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Stage 8: cegah duplikasi presensi — satu catatan per siswa per tanggal.
 * Keputusan desain (STAGE-08-ATTENDANCE.md + STAGE-08-PROGRESS.md):
 * tidak ada kolom sesi pada tabel, jadi kombinasi uniknya adalah
 * (student_id, date).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_attendances', function (Blueprint $table) {
            $table->unique(['student_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::table('student_attendances', function (Blueprint $table) {
            $table->dropUnique(['student_id', 'date']);
        });
    }
};
