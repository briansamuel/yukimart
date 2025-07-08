<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoryTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->bigIncrements('id'); // ID giao dịch tồn kho
            $table->bigInteger('product_id')->unsigned(); // ID sản phẩm
            $table->bigInteger('warehouse_id')->unsigned(); // ID kho hàng
            $table->enum('transaction_type', [
                'import', 'export', 'sale', 'adjustment', 'return', 'damage', 'transfer', 'initial'
            ]); // Loại giao dịch: nhập/xuất/điều chỉnh/trả hàng/hỏng/chuyển kho/khởi tạo
            $table->integer('old_quantity')->default(0); // Số lượng tồn kho trước giao dịch
            $table->integer('quantity'); // Số lượng thay đổi (dương: tăng, âm: giảm)
            $table->integer('new_quantity')->default(0); // Số lượng tồn kho sau giao dịch
            $table->decimal('unit_cost', 15, 2)->nullable(); // Giá vốn đơn vị tại thời điểm giao dịch
            $table->decimal('total_value', 15, 2)->nullable(); // Tổng giá trị giao dịch
            $table->string('reference_type')->nullable(); // Loại tham chiếu (Order, Purchase, etc.)
            $table->bigInteger('reference_id')->nullable()->unsigned(); // ID tham chiếu
            $table->text('notes')->nullable(); // Ghi chú bổ sung
            $table->string('location_from')->nullable(); // Vị trí nguồn
            $table->string('location_to')->nullable(); // Vị trí đích
            $table->bigInteger('created_by_user')->unsigned(); // Người thực hiện giao dịch
            $table->timestamps(); // Thời gian tạo và cập nhật

            // Ràng buộc khóa ngoại
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');

            // Only add warehouse foreign key if warehouses table exists
            if (Schema::hasTable('warehouses')) {
                $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('cascade');
            }
            // Các index để tối ưu hiệu suất
            $table->index(['product_id', 'created_at'], 'idx_product_transactions');
            $table->index(['warehouse_id', 'created_at'], 'idx_warehouse_transactions');
            $table->index(['transaction_type', 'created_at'], 'idx_transaction_type');
            $table->index(['reference_type', 'reference_id'], 'idx_reference');
            $table->index(['created_by_user'], 'idx_user');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inventory_transactions');
    }
}
