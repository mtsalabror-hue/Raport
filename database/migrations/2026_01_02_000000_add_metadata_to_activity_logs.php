<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->jsonb('metadata')->nullable()->after('user_agent');
            $table->index('metadata', 'activity_logs_metadata_gin', 'gin');
        });
    }

    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropIndex('activity_logs_metadata_gin');
            $table->dropColumn('metadata');
        });
    }
};
