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
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            
            // Basic Information
            $table->string('promotion_code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            
            // Promotion Type: percentage, fixed_amount, buy_x_get_y
            $table->enum('type', ['percentage', 'fixed_amount', 'buy_x_get_y'])->default('percentage');
            
            // Discount Value
            $table->decimal('discount_value', 15, 2)->default(0);
            $table->decimal('max_discount_amount', 15, 2)->nullable();
            
            // Conditions
            $table->decimal('min_order_value', 15, 2)->default(0);
            $table->integer('min_quantity')->default(0);
            
            // Buy X Get Y specific fields
            $table->integer('buy_quantity')->nullable();
            $table->integer('get_quantity')->nullable();
            $table->json('applicable_product_ids')->nullable();
            $table->json('applicable_category_ids')->nullable();
            
            // Time Period
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            
            // Usage Limits
            $table->integer('usage_limit')->nullable();
            $table->integer('usage_count')->default(0);
            $table->integer('usage_limit_per_customer')->nullable();
            
            // Apply To
            $table->enum('apply_to', ['all', 'products', 'categories', 'orders'])->default('all');
            
            // Priority (higher number = higher priority)
            $table->integer('priority')->default(0);
            
            // Status: active, inactive, expired
            $table->enum('status', ['active', 'inactive', 'expired'])->default('active');
            
            // Branch Shop
            $table->foreignId('branch_shop_id')->nullable()->constrained('branch_shops')->onDelete('set null');
            
            // Tracking
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'start_date', 'end_date']);
            $table->index('promotion_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};

