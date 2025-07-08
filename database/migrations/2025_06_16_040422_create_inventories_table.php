<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoriesTable extends Migration
{
    public function up(): void
    {
        Schema::create('inventories', function (Blueprint $table) {
            $table->id(); // ID tồn kho
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade'); // ID sản phẩm

            // Only add warehouse foreign key if warehouses table exists
            if (Schema::hasTable('warehouses')) {
                $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('cascade'); // ID kho hàng
            } else {
                $table->unsignedBigInteger('warehouse_id'); // ID kho hàng
            }
            $table->integer('quantity')->default(0); // Số lượng tồn kho hiện tại
            $table->timestamps(); // Thời gian tạo và cập nhật
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
}
