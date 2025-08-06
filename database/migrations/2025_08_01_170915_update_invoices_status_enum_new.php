<?php

use Illuminate\Database\Migrations\Migration;
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
        // First, update existing invalid statuses to 'processing'
        DB::statement("UPDATE invoices SET status = 'processing' WHERE status NOT IN ('draft', 'processing', 'completed', 'cancelled')");

        // Update the status enum to new values
        DB::statement("ALTER TABLE invoices MODIFY COLUMN status ENUM('draft', 'pending', 'confirmed', 'processing', 'completed', 'cancelled', 'returned_partial', 'returned_full') DEFAULT 'processing'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revert back to previous enum values
        DB::statement("ALTER TABLE invoices MODIFY COLUMN status ENUM('draft', 'processing', 'completed', 'cancelled', 'failed', 'sent', 'paid', 'overdue') DEFAULT 'draft'");
    }
};
