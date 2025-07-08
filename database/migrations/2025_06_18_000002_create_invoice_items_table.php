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
        Schema::create('invoice_items', function (Blueprint $table) {
            // ID chi tiết hóa đơn
            $table->id();
            // ID hóa đơn
            $table->foreignId('invoice_id')->constrained('invoices')->onDelete('cascade');
            // ID sản phẩm
            $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('set null');

            // Product information (stored for historical purposes) - Tên sản phẩm
            $table->string('product_name');
            // Mã SKU sản phẩm
            $table->string('product_sku', 100)->nullable();
            // Mô tả sản phẩm
            $table->text('product_description')->nullable();

            // Quantity and pricing - Số lượng
            $table->integer('quantity');
            // Đơn vị tính
            $table->string('unit', 50)->default('cái');
            // Đơn giá
            $table->decimal('unit_price', 15, 2);
            // Tỷ lệ giảm giá (%)
            $table->decimal('discount_rate', 5, 2)->default(0);
            // Số tiền giảm giá
            $table->decimal('discount_amount', 15, 2)->default(0);
            // Tỷ lệ thuế (%)
            $table->decimal('tax_rate', 5, 2)->default(0);
            // Số tiền thuế
            $table->decimal('tax_amount', 15, 2)->default(0);
            // Tổng tiền dòng
            $table->decimal('line_total', 15, 2);

            // Additional information - Ghi chú cho dòng sản phẩm
            $table->text('notes')->nullable();
            // Thứ tự sắp xếp
            $table->integer('sort_order')->default(0);

            $table->timestamps();
            
            // Indexes
            $table->index(['invoice_id', 'sort_order']);
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};
