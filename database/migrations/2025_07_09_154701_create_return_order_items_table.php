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
        Schema::create('return_order_items', function (Blueprint $table) {
            $table->id();
            
            // Relationships
            $table->foreignId('return_order_id')->constrained('return_orders')->onDelete('cascade');
            $table->foreignId('invoice_item_id')->constrained('invoice_items')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            
            // Product information at time of return
            $table->string('product_name'); // Tên sản phẩm tại thời điểm trả
            $table->string('product_sku', 100)->nullable(); // SKU sản phẩm
            
            // Quantity and pricing
            $table->integer('quantity_returned'); // Số lượng trả
            $table->decimal('unit_price', 15, 2); // Giá đơn vị
            $table->decimal('line_total', 15, 2); // Thành tiền = quantity_returned * unit_price
            
            // Item condition
            $table->enum('condition', [
                'new',     // Mới
                'used',    // Đã sử dụng
                'damaged', // Hỏng
                'expired'  // Hết hạn
            ])->default('new');
            
            // Additional information
            $table->text('notes')->nullable(); // Ghi chú cho item
            $table->integer('sort_order')->default(0); // Thứ tự hiển thị
            
            $table->timestamps();
            
            // Indexes
            $table->index(['return_order_id', 'sort_order']);
            $table->index('invoice_item_id');
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('return_order_items');
    }
};
