<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_bundle_suggestion', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('product_id_1');
            $table->unsignedBigInteger('product_id_2');
            
            // Product information
            $table->string('product_name_1');
            $table->string('product_sku_1');
            $table->string('product_name_2');
            $table->string('product_sku_2');
            
            // Co-purchase metrics
            $table->integer('co_purchase_count')->default(0);
            $table->decimal('co_purchase_frequency', 8, 4)->default(0);
            $table->decimal('average_bundle_value', 18, 2)->default(0);
            
            // Performance metrics
            $table->integer('bundle_orders')->default(0);
            $table->decimal('bundle_revenue', 18, 2)->default(0);
            $table->decimal('bundle_profit', 18, 2)->default(0);
            
            // Recommendation score (0-100)
            $table->integer('recommendation_score')->default(0);
            
            // Date range for analysis
            $table->date('analysis_start_date');
            $table->date('analysis_end_date');
            
            $table->timestamps();
            
            $table->index(['tenant_id', 'recommendation_score']);
            $table->index(['tenant_id', 'product_id_1']);
            $table->index(['tenant_id', 'product_id_2']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('product_id_1')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('product_id_2')->references('id')->on('products')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_bundle_suggestion');
    }
};

