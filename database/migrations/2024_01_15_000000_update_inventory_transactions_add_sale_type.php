<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // // Update the transaction_type enum to include 'sale'
        // DB::statement("ALTER TABLE inventory_transactions MODIFY COLUMN transaction_type ENUM('import', 'export', 'sale', 'transfer', 'adjustment', 'initial') NOT NULL");
        
        // // Add index for better performance on sale transactions
        // Schema::table('inventory_transactions', function (Blueprint $table) {
        //     $table->index(['transaction_type', 'created_at'], 'idx_transaction_type_date');
        //     $table->index(['product_id', 'transaction_type'], 'idx_product_transaction_type');
        //     $table->index(['reference_type', 'reference_id'], 'idx_reference');
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the indexes
        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->dropIndex('idx_transaction_type_date');
            $table->dropIndex('idx_product_transaction_type');
            $table->dropIndex('idx_reference');
        });
        
        // Revert the transaction_type enum (remove 'sale')
        DB::statement("ALTER TABLE inventory_transactions MODIFY COLUMN transaction_type ENUM('import', 'export', 'transfer', 'adjustment', 'initial') NOT NULL");
    }
};
