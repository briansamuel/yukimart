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
        // Update the status enum to include draft and pending
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('draft', 'pending', 'confirmed', 'processing', 'shipped', 'delivered', 'completed', 'cancelled', 'returned') NOT NULL DEFAULT 'draft'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revert back to original enum values
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('confirmed', 'processing', 'shipped', 'delivered', 'completed', 'cancelled', 'returned') NOT NULL DEFAULT 'confirmed'");
    }
};
