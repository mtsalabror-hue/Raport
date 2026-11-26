<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fase F (RDM): pembina ekstrakurikuler. Kolom supervisor_id
     * menunjuk guru pembina tiap jenis ekstrakurikuler; nullable
     * karena tidak semua ekskul harus punya pembina, dan guru yang
     * dihapus tidak ikut menghapus master ekskul (nullOnDelete).
     */
    public function up(): void
    {
        Schema::table('extracurriculars', function (Blueprint $table) {
            $table->foreignId('supervisor_id')->nullable()->after('description')
                ->constrained('teachers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('extracurriculars', function (Blueprint $table) {
            $table->dropForeign(['supervisor_id']);
            $table->dropColumn('supervisor_id');
        });
    }
};
