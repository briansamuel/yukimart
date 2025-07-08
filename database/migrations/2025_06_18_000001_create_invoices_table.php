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
        Schema::create('invoices', function (Blueprint $table) {
            // ID hóa đơn
            $table->id();
            // Số hóa đơn
            $table->string('invoice_number', 50)->unique();
            // ID đơn hàng liên kết
            $table->foreignId('order_id')->nullable()->constrained('orders')->onDelete('set null');
            // ID khách hàng
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            // ID chi nhánh
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('set null');

            // Invoice details - Loại hóa đơn: bán hàng, trả hàng, điều chỉnh, khác
            $table->enum('invoice_type', ['sale', 'return', 'adjustment', 'other'])->default('sale');
            // Trạng thái: nháp, đã gửi, đã thanh toán, quá hạn, đã hủy
            $table->enum('status', ['draft', 'sent', 'paid', 'overdue', 'cancelled'])->default('draft');
            // Ngày lập hóa đơn
            $table->date('invoice_date');
            // Ngày đến hạn thanh toán
            $table->date('due_date');

            // Financial information - Tổng tiền trước thuế và giảm giá
            $table->decimal('subtotal', 15, 2)->default(0);
            // Tỷ lệ thuế (%)
            $table->decimal('tax_rate', 5, 2)->default(0);
            // Số tiền thuế
            $table->decimal('tax_amount', 15, 2)->default(0);
            // Tỷ lệ giảm giá (%)
            $table->decimal('discount_rate', 5, 2)->default(0);
            // Số tiền giảm giá
            $table->decimal('discount_amount', 15, 2)->default(0);
            // Tổng tiền cuối cùng
            $table->decimal('total_amount', 15, 2)->default(0);
            // Số tiền đã thanh toán
            $table->decimal('paid_amount', 15, 2)->default(0);
            // Số tiền còn lại
            $table->decimal('remaining_amount', 15, 2)->default(0);

            // Payment information - Phương thức thanh toán
            $table->enum('payment_method', ['cash', 'card', 'transfer', 'check', 'other'])->nullable();
            // Trạng thái thanh toán
            $table->enum('payment_status', ['unpaid', 'partial', 'paid', 'overpaid'])->default('unpaid');
            // Thời gian thanh toán
            $table->timestamp('paid_at')->nullable();

            // Additional information - Ghi chú
            $table->text('notes')->nullable();
            // Điều khoản và điều kiện
            $table->text('terms_conditions')->nullable();
            // Số tham chiếu
            $table->string('reference_number', 100)->nullable();

            // Tracking - Người tạo
            $table->foreignId('created_by')->constrained('users');
            // Người cập nhật cuối
            $table->foreignId('updated_by')->nullable()->constrained('users');
            // Thời gian gửi hóa đơn
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['invoice_date', 'status']);
            $table->index(['customer_id', 'status']);
            $table->index(['due_date', 'payment_status']);
            $table->index(['branch_id', 'invoice_date']);
            $table->index('invoice_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
