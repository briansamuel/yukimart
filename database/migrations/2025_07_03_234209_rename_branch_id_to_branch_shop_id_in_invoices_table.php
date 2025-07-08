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
        if (Schema::hasColumn('invoices', 'branch_id')) {
            // Drop the old foreign key constraint first
            Schema::table('invoices', function (Blueprint $table) {
                $table->dropForeign(['branch_id']);
            });

            // Use raw SQL to rename column (avoid Doctrine DBAL issues)
            DB::statement('ALTER TABLE invoices CHANGE branch_id branch_shop_id BIGINT UNSIGNED NULL');

            // Add new foreign key constraint
            Schema::table('invoices', function (Blueprint $table) {
                $table->foreign('branch_shop_id')->references('id')->on('branch_shops')->onDelete('set null');
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
        if (Schema::hasColumn('invoices', 'branch_shop_id')) {
            // Drop foreign key constraint
            Schema::table('invoices', function (Blueprint $table) {
                $table->dropForeign(['branch_shop_id']);
            });

            // Use raw SQL to rename column back
            DB::statement('ALTER TABLE invoices CHANGE branch_shop_id branch_id BIGINT UNSIGNED NULL');

            // Add back old foreign key constraint (if branches table exists)
            if (Schema::hasTable('branches')) {
                Schema::table('invoices', function (Blueprint $table) {
                    $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
                });
            }
        }
    }
};
