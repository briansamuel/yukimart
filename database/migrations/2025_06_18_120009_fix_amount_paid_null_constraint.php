<?php

use Illuminate\Database\Migrations\Migration;
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
        // First, update any existing null values to 0
        if (Schema::hasTable('orders')) {
            DB::table('orders')
                ->whereNull('amount_paid')
                ->update(['amount_paid' => 0]);
        }

        // Also check invoices table if it has amount_paid column
        if (Schema::hasTable('invoices')) {
            // Check if paid_amount column exists and has null values
            if (Schema::hasColumn('invoices', 'paid_amount')) {
                DB::table('invoices')
                    ->whereNull('paid_amount')
                    ->update(['paid_amount' => 0]);
            }
        }

        // Use raw SQL to modify columns without requiring doctrine/dbal
        if (Schema::hasTable('orders')) {
            // Check if amount_paid column allows null
            $columnInfo = DB::select("SHOW COLUMNS FROM orders LIKE 'amount_paid'");
            if (!empty($columnInfo) && $columnInfo[0]->Null === 'YES') {
                DB::statement('ALTER TABLE orders MODIFY COLUMN amount_paid DECIMAL(15,2) NOT NULL DEFAULT 0');
            }
        }

        if (Schema::hasTable('invoices')) {
            if (Schema::hasColumn('invoices', 'paid_amount')) {
                $columnInfo = DB::select("SHOW COLUMNS FROM invoices LIKE 'paid_amount'");
                if (!empty($columnInfo) && $columnInfo[0]->Null === 'YES') {
                    DB::statement('ALTER TABLE invoices MODIFY COLUMN paid_amount DECIMAL(15,2) NOT NULL DEFAULT 0');
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revert changes - allow null values again using raw SQL
        if (Schema::hasTable('orders')) {
            DB::statement('ALTER TABLE orders MODIFY COLUMN amount_paid DECIMAL(15,2) NULL DEFAULT 0');
        }

        if (Schema::hasTable('invoices')) {
            if (Schema::hasColumn('invoices', 'paid_amount')) {
                DB::statement('ALTER TABLE invoices MODIFY COLUMN paid_amount DECIMAL(15,2) NULL DEFAULT 0');
            }
        }
    }
};
