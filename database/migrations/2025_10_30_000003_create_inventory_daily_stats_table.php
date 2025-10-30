<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_daily_stats', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('branch_shop_id')->nullable();
            $table->date('stats_date');
            
            // Stock metrics
            $table->integer('total_skus')->default(0);
            $table->integer('low_stock_items')->default(0);
            $table->integer('overstock_items')->default(0);
            $table->integer('out_of_stock_items')->default(0);
            
            // Inventory value
            $table->decimal('total_inventory_value', 18, 2)->default(0);
            $table->decimal('low_stock_value', 18, 2)->default(0);
            $table->decimal('overstock_value', 18, 2)->default(0);
            
            // Movement metrics
            $table->integer('items_sold')->default(0);
            $table->integer('items_received')->default(0);
            $table->decimal('inventory_turnover_rate', 8, 4)->default(0);
            
            // Aging metrics
            $table->integer('items_not_sold_30_days')->default(0);
            $table->integer('items_not_sold_60_days')->default(0);
            $table->integer('items_not_sold_90_days')->default(0);
            
            $table->timestamps();
            
            $table->index(['tenant_id', 'stats_date']);
            $table->index(['tenant_id', 'branch_shop_id', 'stats_date']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('branch_shop_id')->references('id')->on('branch_shops')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_daily_stats');
    }
};

