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
        Schema::create('orders', function (Blueprint $table) {
            $table->id(); // ID đơn hàng
            $table->string('order_code')->unique(); // Mã đơn hàng (duy nhất)
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('set null'); // ID khách hàng
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('set null'); // ID chi nhánh
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null'); // Người tạo đơn
            $table->foreignId('sold_by')->nullable()->constrained('users')->onDelete('set null'); // Nhân viên bán hàng
            $table->enum('channel', ['direct', 'online', 'pos', 'other', 'shopee', 'tiktok', 'facebook'])->default('direct'); // Kênh bán hàng
            $table->integer('total_quantity')->default(0); // Tổng số lượng sản phẩm
            $table->decimal('total_amount', 15, 2); // Tổng tiền hàng
            $table->decimal('discount_amount', 15, 2)->default(0); // Số tiền giảm giá
            $table->decimal('final_amount', 15, 2); // Tổng tiền phải thanh toán
            $table->decimal('amount_paid', 15, 2)->default(0); // Số tiền đã thanh toán
            $table->decimal('shipping_fee', 15, 2)->default(0); // Phí vận chuyển
            $table->decimal('tax_amount', 15, 2)->default(0); // Số tiền thuế

            $table->enum('status', ['processing', 'completed', 'cancelled', 'failed', 'returned', 'confirmed',])->default('processing'); // Trạng thái đơn hàng
            $table->enum('delivery_status', ['pending', 'picking', 'delivering', 'delivered', 'returning', 'returned'])->default('pending'); // Trạng thái giao hàng
            $table->text('note')->nullable(); // Ghi chú đơn hàng
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
        Schema::dropIfExists('orders');
    }
};
