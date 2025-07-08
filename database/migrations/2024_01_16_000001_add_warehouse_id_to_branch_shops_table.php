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
        if (Schema::hasTable('branch_shops')) {
            Schema::table('branch_shops', function (Blueprint $table) {
                if (!Schema::hasColumn('branch_shops', 'warehouse_id')) {
                    $table->foreignId('warehouse_id')->nullable()->after('manager_id')->constrained('warehouses')->onDelete('set null');
                    $table->index('warehouse_id');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('branch_shops') && Schema::hasColumn('branch_shops', 'warehouse_id')) {
            Schema::table('branch_shops', function (Blueprint $table) {
                $table->dropForeign(['warehouse_id']);
                $table->dropIndex(['warehouse_id']);
                $table->dropColumn('warehouse_id');
            });
        }
    }
};
