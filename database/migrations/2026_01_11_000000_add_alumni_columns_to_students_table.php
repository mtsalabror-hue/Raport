<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fase D (RDM): penanda alumni pada siswa. Siswa yang lulus
     * (kelas XII, keputusan naik) mendapat class_id = null,
     * is_alumni = true, dan alumni_year = tahun ajaran yang berakhir.
     * Data siswa tidak dihapus.
     */
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->boolean('is_alumni')->default(false)->after('class_id');
            $table->string('alumni_year')->nullable()->after('is_alumni');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['is_alumni', 'alumni_year']);
        });
    }
};
