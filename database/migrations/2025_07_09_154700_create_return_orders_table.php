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
        Schema::create('return_orders', function (Blueprint $table) {
            $table->id();
            
            // Return order information
            $table->string('return_number', 50)->unique(); // RTN + date + sequence
            $table->foreignId('invoice_id')->constrained('invoices')->onDelete('cascade');
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('set null');
            $table->foreignId('branch_shop_id')->nullable()->constrained('branch_shops')->onDelete('set null');
            
            // Return details
            $table->date('return_date');
            $table->enum('reason', [
                'defective',        // Hàng lỗi
                'wrong_item',       // Giao sai hàng
                'customer_request', // Khách hàng yêu cầu
                'damaged',          // Hàng bị hỏng
                'expired',          // Hết hạn
                'other'             // Khác
            ])->default('customer_request');
            
            $table->enum('status', [
                'pending',    // Chờ duyệt
                'approved',   // Đã duyệt
                'rejected',   // Từ chối
                'completed'   // Hoàn thành
            ])->default('pending');
            
            // Financial information
            $table->decimal('subtotal', 15, 2)->default(0); // Tổng tiền hàng trả
            $table->decimal('tax_rate', 5, 2)->default(0);  // Thuế suất %
            $table->decimal('tax_amount', 15, 2)->default(0); // Tiền thuế
            $table->decimal('total_amount', 15, 2)->default(0); // Tổng tiền trả
            
            // Refund information
            $table->enum('refund_method', [
                'cash',         // Tiền mặt
                'card',         // Thẻ
                'transfer',     // Chuyển khoản
                'store_credit', // Tín dụng cửa hàng
                'exchange',     // Đổi hàng
                'points'        // Điểm thưởng
            ])->nullable();
            
            // Additional information
            $table->text('notes')->nullable(); // Ghi chú
            $table->text('reason_detail')->nullable(); // Chi tiết lý do
            
            // Approval information
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            
            // Audit fields
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            // Indexes
            $table->index(['invoice_id', 'status']);
            $table->index(['customer_id', 'return_date']);
            $table->index(['branch_shop_id', 'status']);
            $table->index('return_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('return_orders');
    }
};
