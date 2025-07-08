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
        Schema::create('marketplace_product_links', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->string('platform'); // 'shopee', 'tiki', 'lazada', 'sendo', etc.
            $table->string('marketplace_item_id'); // External platform item ID
            $table->string('sku')->nullable();
            $table->string('name');
            $table->text('image_url')->nullable();
            $table->string('shop_name')->nullable();
            $table->string('shop_id')->nullable();
            $table->decimal('price', 15, 2)->nullable();
            $table->integer('stock_quantity')->nullable();
            $table->string('status')->default('active'); // active, inactive, deleted
            $table->json('platform_data')->nullable(); // Store platform-specific data
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            // Only add foreign key if products table exists
            if (Schema::hasTable('products')) {
                $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            }
            $table->unique(['product_id', 'platform', 'marketplace_item_id'], 'unique_product_platform_item');
            $table->index(['platform', 'marketplace_item_id']);
            $table->index(['sku', 'platform']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marketplace_product_links');
    }
};
