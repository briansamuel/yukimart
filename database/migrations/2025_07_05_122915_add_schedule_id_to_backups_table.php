<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // The schedule_id column and foreign key constraint already exist from the create_backups_table migration
        // This migration is redundant and should be a no-op
        if (!Schema::hasColumn('backups', 'schedule_id')) {
            Schema::table('backups', function (Blueprint $table) {
                $table->foreignId('schedule_id')->nullable()->after('completed_at')->constrained('backup_schedules')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Do not drop the schedule_id column as it was created by the create_backups_table migration
        // This migration is redundant and should be a no-op
    }
};
