<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Fix notifications table JSON columns
        if (Schema::hasTable('notifications')) {
            Schema::table('notifications', function (Blueprint $table) {
                // Drop and recreate channels column without default value
                $table->dropColumn('channels');
            });
            
            Schema::table('notifications', function (Blueprint $table) {
                // Kênh gửi thông báo
                $table->json('channels')->nullable()->after('priority');
            });
        }

        // Fix notification_settings table JSON columns
        if (Schema::hasTable('notification_settings')) {
            Schema::table('notification_settings', function (Blueprint $table) {
                // Drop and recreate channels column without default value
                $table->dropColumn('channels');
            });
            
            Schema::table('notification_settings', function (Blueprint $table) {
                // Kênh nhận thông báo
                $table->json('channels')->nullable()->after('notification_type');
            });
        }

        // Update existing records to have default values
        $this->updateExistingRecords();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Note: We cannot restore default values for JSON columns in MySQL
        // This is intentionally left empty as the change is irreversible
        // The application logic will handle default values
    }

    /**
     * Update existing records to have default values.
     */
    protected function updateExistingRecords()
    {
        try {
            // Update notifications with null channels
            if (Schema::hasTable('notifications')) {
                DB::table('notifications')
                    ->whereNull('channels')
                    ->update(['channels' => json_encode(['web'])]);
            }

            // Update notification_settings with null channels
            if (Schema::hasTable('notification_settings')) {
                DB::table('notification_settings')
                    ->whereNull('channels')
                    ->update(['channels' => json_encode(['web'])]);
            }

        } catch (\Exception $e) {
            // Log error but don't fail migration
            \Log::warning('Failed to update existing JSON records: ' . $e->getMessage());
        }
    }
};
