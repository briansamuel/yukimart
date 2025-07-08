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
        // Drop existing foreign key constraint
        DB::statement('ALTER TABLE invoices DROP FOREIGN KEY invoices_customer_id_foreign');

        // Modify column to be nullable
        DB::statement('ALTER TABLE invoices MODIFY customer_id BIGINT UNSIGNED NULL');

        // Add foreign key constraint back with nullable support
        DB::statement('ALTER TABLE invoices ADD CONSTRAINT invoices_customer_id_foreign FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Drop foreign key constraint
        DB::statement('ALTER TABLE invoices DROP FOREIGN KEY invoices_customer_id_foreign');

        // Modify column back to not nullable
        DB::statement('ALTER TABLE invoices MODIFY customer_id BIGINT UNSIGNED NOT NULL');

        // Add foreign key constraint back
        DB::statement('ALTER TABLE invoices ADD CONSTRAINT invoices_customer_id_foreign FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE');
    }
};
