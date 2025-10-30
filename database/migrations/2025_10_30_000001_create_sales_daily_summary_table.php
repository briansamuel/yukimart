<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_daily_summary', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('branch_shop_id')->nullable();
            $table->date('summary_date');
            
            // Order metrics
            $table->integer('total_orders')->default(0);
            $table->integer('total_return_orders')->default(0);
            
            // Revenue metrics
            $table->decimal('total_revenue', 18, 2)->default(0);
            $table->decimal('total_return_amount', 18, 2)->default(0);
            $table->decimal('net_revenue', 18, 2)->default(0);
            
            // Cost metrics
            $table->decimal('total_cogs', 18, 2)->default(0);
            $table->decimal('total_profit', 18, 2)->default(0);
            
            // Customer metrics
            $table->integer('unique_customers')->default(0);
            $table->integer('new_customers')->default(0);
            $table->integer('returning_customers')->default(0);
            $table->integer('walkin_customers')->default(0);
            
            // Channel metrics
            $table->decimal('offline_revenue', 18, 2)->default(0);
            $table->decimal('online_revenue', 18, 2)->default(0);
            $table->decimal('marketplace_revenue', 18, 2)->default(0);
            
            $table->timestamps();
            
            // Indexes
            $table->index(['tenant_id', 'summary_date']);
            $table->index(['tenant_id', 'branch_shop_id', 'summary_date']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('branch_shop_id')->references('id')->on('branch_shops')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_daily_summary');
    }
};

