<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_daily_stats', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('branch_shop_id')->nullable();
            $table->date('stats_date');
            
            // Customer counts
            $table->integer('total_customers')->default(0);
            $table->integer('new_customers')->default(0);
            $table->integer('returning_customers')->default(0);
            $table->integer('walkin_customers')->default(0);
            $table->integer('vip_customers')->default(0);
            
            // Revenue by customer type
            $table->decimal('new_customer_revenue', 18, 2)->default(0);
            $table->decimal('returning_customer_revenue', 18, 2)->default(0);
            $table->decimal('walkin_revenue', 18, 2)->default(0);
            $table->decimal('vip_revenue', 18, 2)->default(0);
            
            // Average metrics
            $table->decimal('avg_order_value', 18, 2)->default(0);
            $table->decimal('avg_customer_lifetime_value', 18, 2)->default(0);
            
            $table->timestamps();
            
            $table->index(['tenant_id', 'stats_date']);
            $table->index(['tenant_id', 'branch_shop_id', 'stats_date']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('branch_shop_id')->references('id')->on('branch_shops')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_daily_stats');
    }
};

