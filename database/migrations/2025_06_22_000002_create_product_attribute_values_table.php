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
        Schema::create('product_attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attribute_id')->constrained('product_attributes')->onDelete('cascade');
            $table->string('value'); // Giá trị thuộc tính (Nhài, Xoài, Size S, Size M, etc.)
            $table->string('slug'); // Slug cho giá trị
            $table->string('color_code')->nullable(); // Mã màu nếu là thuộc tính màu sắc
            $table->string('image')->nullable(); // Hình ảnh cho giá trị (nếu cần)
            $table->text('description')->nullable(); // Mô tả giá trị
            $table->integer('sort_order')->default(0); // Thứ tự sắp xếp
            $table->decimal('price_adjustment', 15, 2)->default(0); // Điều chỉnh giá (+/-)
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            
            $table->unique(['attribute_id', 'slug']);
            $table->index(['attribute_id', 'status']);
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_attribute_values');
    }
};
