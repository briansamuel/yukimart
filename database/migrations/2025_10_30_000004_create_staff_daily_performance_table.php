<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_daily_performance', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('branch_shop_id')->nullable();
            $table->unsignedBigInteger('staff_id');
            $table->date('performance_date');
            
            // Sales metrics
            $table->integer('total_orders')->default(0);
            $table->decimal('total_revenue', 18, 2)->default(0);
            $table->decimal('total_profit', 18, 2)->default(0);
            
            // Customer metrics
            $table->integer('unique_customers')->default(0);
            $table->integer('new_customers')->default(0);
            
            // Average metrics
            $table->decimal('avg_order_value', 18, 2)->default(0);
            $table->decimal('avg_profit_per_order', 18, 2)->default(0);
            
            // Performance indicators
            $table->decimal('profit_margin', 8, 4)->default(0);
            $table->integer('customer_satisfaction_score')->nullable();
            
            $table->timestamps();
            
            $table->index(['tenant_id', 'performance_date']);
            $table->index(['tenant_id', 'staff_id', 'performance_date']);
            $table->index(['tenant_id', 'branch_shop_id', 'performance_date']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('branch_shop_id')->references('id')->on('branch_shops')->onDelete('cascade');
            $table->foreign('staff_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_daily_performance');
    }
};

