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
        if (Schema::hasTable('order_items')) {
            Schema::table('order_items', function (Blueprint $table) {
            $table->string('marketplace_item_id')->nullable()->after('product_id'); // External platform item ID
            $table->string('marketplace_variation_id')->nullable()->after('marketplace_item_id'); // Variation ID if applicable
            $table->string('marketplace_sku')->nullable()->after('marketplace_variation_id'); // Platform SKU
            $table->json('marketplace_item_data')->nullable()->after('marketplace_sku'); // Platform-specific item data
            $table->decimal('marketplace_price', 10, 2)->nullable()->after('marketplace_item_data'); // Price on platform
            $table->decimal('marketplace_discount', 10, 2)->default(0)->after('marketplace_price'); // Discount amount
            
            $table->index('marketplace_item_id');
            $table->index('marketplace_sku');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('order_items')) {
            Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex(['marketplace_item_id']);
            $table->dropIndex(['marketplace_sku']);
            
            $table->dropColumn([
                'marketplace_item_id',
                'marketplace_variation_id',
                'marketplace_sku',
                'marketplace_item_data',
                'marketplace_price',
                'marketplace_discount'
            ]);
            });
        }
    }
};
