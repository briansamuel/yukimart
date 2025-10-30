<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_retention_stats', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('branch_shop_id')->nullable();
            $table->yearMonth('cohort_month'); // Format: YYYY-MM
            
            // Cohort size (customers acquired in this month)
            $table->integer('cohort_size')->default(0);
            
            // Retention by month (0-indexed, 0 = acquisition month)
            $table->integer('month_0_retained')->default(0);
            $table->integer('month_1_retained')->default(0);
            $table->integer('month_2_retained')->default(0);
            $table->integer('month_3_retained')->default(0);
            $table->integer('month_6_retained')->default(0);
            $table->integer('month_12_retained')->default(0);
            
            // Retention percentages
            $table->decimal('month_0_retention_rate', 8, 4)->default(100);
            $table->decimal('month_1_retention_rate', 8, 4)->default(0);
            $table->decimal('month_2_retention_rate', 8, 4)->default(0);
            $table->decimal('month_3_retention_rate', 8, 4)->default(0);
            $table->decimal('month_6_retention_rate', 8, 4)->default(0);
            $table->decimal('month_12_retention_rate', 8, 4)->default(0);
            
            // Revenue metrics
            $table->decimal('cohort_revenue', 18, 2)->default(0);
            $table->decimal('avg_revenue_per_customer', 18, 2)->default(0);
            
            $table->timestamps();
            
            $table->index(['tenant_id', 'cohort_month']);
            $table->index(['tenant_id', 'branch_shop_id', 'cohort_month']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('branch_shop_id')->references('id')->on('branch_shops')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_retention_stats');
    }
};

