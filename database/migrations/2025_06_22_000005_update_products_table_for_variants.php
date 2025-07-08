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
        Schema::table('products', function (Blueprint $table) {
            // Thêm các trường hỗ trợ biến thể
            $table->boolean('has_variants')->default(false)->after('product_type'); // Có biến thể hay không
            $table->integer('variants_count')->default(0)->after('has_variants'); // Số lượng biến thể
            $table->json('variant_attributes')->nullable()->after('variants_count'); // Thuộc tính được sử dụng cho biến thể
            $table->decimal('min_price', 15, 2)->nullable()->after('sale_price'); // Giá thấp nhất của các biến thể
            $table->decimal('max_price', 15, 2)->nullable()->after('min_price'); // Giá cao nhất của các biến thể
            
            // Index cho hiệu suất
            $table->index(['product_type', 'has_variants']);
            $table->index('has_variants');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['product_type', 'has_variants']);
            $table->dropIndex(['has_variants']);
            $table->dropColumn([
                'has_variants',
                'variants_count', 
                'variant_attributes',
                'min_price',
                'max_price'
            ]);
        });
    }
};
