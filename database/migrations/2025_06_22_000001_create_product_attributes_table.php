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
        Schema::create('product_attributes', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Tên thuộc tính (Hương, Size, Màu sắc, etc.)
            $table->string('slug')->unique(); // Slug cho thuộc tính
            $table->string('type')->default('select'); // select, color, text, number
            $table->text('description')->nullable(); // Mô tả thuộc tính
            $table->boolean('is_required')->default(false); // Bắt buộc hay không
            $table->boolean('is_variation')->default(true); // Dùng cho biến thể hay không
            $table->boolean('is_visible')->default(true); // Hiển thị trên frontend
            $table->integer('sort_order')->default(0); // Thứ tự sắp xếp
            $table->json('options')->nullable(); // Các tùy chọn bổ sung (JSON)
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            
            $table->index(['status', 'is_variation']);
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_attributes');
    }
};
