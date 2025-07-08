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
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
            $table->string('marketplace_platform')->nullable()->after('status'); // 'shopee', 'tiki', etc.
            $table->string('marketplace_order_id')->nullable()->after('marketplace_platform'); // External order ID
            $table->string('marketplace_order_sn')->nullable()->after('marketplace_order_id'); // Order serial number
            $table->json('marketplace_data')->nullable()->after('marketplace_order_sn'); // Platform-specific data
            $table->timestamp('marketplace_created_at')->nullable()->after('marketplace_data'); // Order date on platform
            $table->string('marketplace_status')->nullable()->after('marketplace_created_at'); // Platform order status
            $table->decimal('marketplace_shipping_fee', 10, 2)->nullable()->after('marketplace_status');
            $table->string('marketplace_payment_method')->nullable()->after('marketplace_shipping_fee');
            $table->boolean('is_marketplace_order')->default(false)->after('marketplace_payment_method');
            
            $table->index(['marketplace_platform', 'marketplace_order_id']);
            $table->index('marketplace_order_sn');
            $table->index('is_marketplace_order');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['marketplace_platform', 'marketplace_order_id']);
            $table->dropIndex(['marketplace_order_sn']);
            $table->dropIndex(['is_marketplace_order']);
            
            $table->dropColumn([
                'marketplace_platform',
                'marketplace_order_id', 
                'marketplace_order_sn',
                'marketplace_data',
                'marketplace_created_at',
                'marketplace_status',
                'marketplace_shipping_fee',
                'marketplace_payment_method',
                'is_marketplace_order'
            ]);
            });
        }
    }
};
