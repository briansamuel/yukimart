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
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            
            // Basic Information
            $table->string('voucher_code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            
            // Voucher Type: percentage, fixed_amount, freeship
            $table->enum('type', ['percentage', 'fixed_amount', 'freeship'])->default('percentage');
            
            // Discount Value
            $table->decimal('discount_value', 15, 2)->default(0);
            $table->decimal('max_discount_amount', 15, 2)->nullable();
            
            // Conditions
            $table->decimal('min_order_value', 15, 2)->default(0);
            
            // Time Period
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            
            // Usage Limits
            $table->integer('total_quantity')->nullable();
            $table->integer('used_quantity')->default(0);
            $table->integer('usage_limit_per_customer')->default(1);
            
            // Apply To
            $table->enum('apply_to', ['all', 'specific_customers', 'customer_groups'])->default('all');
            $table->json('applicable_customer_ids')->nullable();
            $table->json('applicable_customer_groups')->nullable();
            
            // Public or Private
            $table->boolean('is_public')->default(true);
            
            // Status: active, inactive, expired, used_up
            $table->enum('status', ['active', 'inactive', 'expired', 'used_up'])->default('active');
            
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
            $table->index('voucher_code');
            $table->index('is_public');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};

