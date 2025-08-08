<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('fcm_tokens', function (Blueprint $table) {
            // Check and add missing columns
            if (!Schema::hasColumn('fcm_tokens', 'device_name')) {
                $table->string('device_name')->nullable()->after('device_id');
            }
            
            if (!Schema::hasColumn('fcm_tokens', 'app_version')) {
                $table->string('app_version')->nullable()->after('device_name');
            }
            
            if (!Schema::hasColumn('fcm_tokens', 'platform_version')) {
                $table->string('platform_version')->nullable()->after('app_version');
            }
            
            if (!Schema::hasColumn('fcm_tokens', 'last_used_at')) {
                $table->timestamp('last_used_at')->nullable()->after('is_active');
            }
            
            if (!Schema::hasColumn('fcm_tokens', 'metadata')) {
                $table->json('metadata')->nullable()->after('last_used_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fcm_tokens', function (Blueprint $table) {
            $columnsToRemove = ['device_name', 'app_version', 'platform_version', 'last_used_at', 'metadata'];
            
            foreach ($columnsToRemove as $column) {
                if (Schema::hasColumn('fcm_tokens', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
