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
        Schema::create('product_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            
            // Tên đơn vị (chai, lốc, thùng, hộp, etc.)
            $table->string('unit_name');
            
            // Giá bán cho đơn vị này
            $table->decimal('sale_price', 15, 2)->default(0);
            
            // Bán trực tiếp (có thể bán đơn vị này không)
            $table->boolean('is_direct_sale')->default(true);
            
            // Tỷ lệ quy đổi so với đơn vị cơ bản (base unit)
            // Ví dụ: 1 lốc = 4 chai, 1 thùng = 20 lốc
            // Nếu chai là base unit (conversion_rate = 1), thì lốc có conversion_rate = 4, thùng có conversion_rate = 80
            $table->decimal('conversion_rate', 10, 4)->default(1);
            
            // Đơn vị cơ bản (base unit) - chỉ có 1 unit là base unit cho mỗi product
            $table->boolean('is_base_unit')->default(false);
            
            // Thứ tự sắp xếp
            $table->integer('sort_order')->default(0);
            
            // Trạng thái
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            
            // Indexes
            $table->index(['product_id', 'tenant_id']);
            $table->index(['tenant_id', 'is_active']);
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_units');
    }
};

