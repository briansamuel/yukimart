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
        Schema::table('inventories', function (Blueprint $table) {
            // Add variant_id column
            $table->foreignId('variant_id')->nullable()->after('product_id')->constrained('product_variants')->onDelete('cascade');

            // Update unique constraint to include variant_id
            // Note: We'll keep the original unique constraint for now and add variant support later
            // $table->dropUnique(['product_id', 'warehouse_id']);
            // $table->unique(['product_id', 'variant_id', 'warehouse_id'], 'product_variant_warehouse_unique');

            // Add indexes for performance
            $table->index(['variant_id', 'warehouse_id']);
            $table->index('variant_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventories', function (Blueprint $table) {
            // Drop indexes
            $table->dropIndex(['variant_id', 'warehouse_id']);
            $table->dropIndex(['variant_id']);

            // Drop foreign key and column
            $table->dropForeign(['variant_id']);
            $table->dropColumn('variant_id');
        });
    }
};
