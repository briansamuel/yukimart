<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('slow_moving_inventory', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('branch_shop_id')->nullable();
            $table->unsignedBigInteger('product_id');
            
            // Product information
            $table->string('product_name');
            $table->string('product_sku');
            $table->unsignedBigInteger('category_id')->nullable();
            
            // Stock information
            $table->integer('current_stock')->default(0);
            $table->decimal('stock_value', 18, 2)->default(0);
            
            // Movement metrics
            $table->integer('days_without_sale')->default(0);
            $table->integer('last_sale_days_ago')->nullable();
            $table->date('last_sale_date')->nullable();
            
            // Sales history
            $table->integer('sales_last_30_days')->default(0);
            $table->integer('sales_last_60_days')->default(0);
            $table->integer('sales_last_90_days')->default(0);
            
            // Aging classification
            $table->enum('aging_category', ['fast_moving', 'normal', 'slow_moving', 'dead_stock'])->default('normal');
            
            // Recommendation
            $table->text('recommendation')->nullable();
            
            $table->timestamps();
            
            $table->index(['tenant_id', 'aging_category']);
            $table->index(['tenant_id', 'branch_shop_id', 'aging_category']);
            $table->index(['tenant_id', 'days_without_sale']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('branch_shop_id')->references('id')->on('branch_shops')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('slow_moving_inventory');
    }
};

