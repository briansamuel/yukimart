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
        // Update the status enum to include draft
        DB::statement("ALTER TABLE invoices MODIFY COLUMN status ENUM('draft', 'processing', 'completed', 'cancelled', 'failed', 'sent', 'paid', 'overdue') DEFAULT 'draft'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revert back to original enum values
        DB::statement("ALTER TABLE invoices MODIFY COLUMN status ENUM('processing', 'completed', 'cancelled', 'failed', 'sent', 'paid', 'overdue') DEFAULT 'processing'");
    }
};
