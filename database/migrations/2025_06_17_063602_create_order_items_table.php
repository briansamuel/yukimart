<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id(); // ID chi tiết đơn hàng
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade'); // ID đơn hàng
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade'); // ID sản phẩm
            $table->integer('quantity'); // Số lượng
            $table->decimal('unit_price', 15, 2); // Đơn giá
            $table->decimal('discount', 15, 2)->default(0); // Giảm giá
            $table->decimal('total_price', 15, 2); // Thành tiền
            $table->timestamps(); // Thời gian tạo và cập nhật
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_items');
    }
};
