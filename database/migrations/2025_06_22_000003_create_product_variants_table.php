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
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_product_id')->constrained('products')->onDelete('cascade');
            $table->string('variant_name'); // Tên biến thể (Sản phẩm A - Hương Nhài - Size L)
            $table->string('sku')->unique(); // SKU riêng cho biến thể
            $table->string('barcode')->nullable(); // Mã vạch riêng
            $table->decimal('cost_price', 15, 2); // Giá vốn riêng
            $table->decimal('sale_price', 15, 2); // Giá bán riêng
            $table->decimal('regular_price', 15, 2)->nullable(); // Giá gốc (trước khuyến mãi)
            $table->string('image')->nullable(); // Hình ảnh riêng cho biến thể
            $table->json('images')->nullable(); // Nhiều hình ảnh (JSON array)
            $table->integer('weight')->nullable(); // Trọng lượng riêng
            $table->string('dimensions')->nullable(); // Kích thước (JSON: length x width x height)
            $table->integer('points')->default(0); // Điểm tích lũy riêng
            $table->integer('reorder_point')->default(0); // Định mức tồn riêng
            $table->boolean('is_default')->default(false); // Biến thể mặc định
            $table->boolean('is_active')->default(true); // Trạng thái hoạt động
            $table->integer('sort_order')->default(0); // Thứ tự sắp xếp
            $table->json('meta_data')->nullable(); // Dữ liệu bổ sung (JSON)
            $table->timestamps();
            
            $table->index(['parent_product_id', 'is_active']);
            $table->index(['parent_product_id', 'is_default']);
            $table->index('sort_order');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
