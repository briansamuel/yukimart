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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            
            // Payment identification
            $table->string('payment_number', 50)->unique(); // Mã phiếu như TTHD040592-1
            $table->enum('payment_type', [
                'receipt', // Phiếu thu
                'payment'  // Phiếu chi
            ]);
            
            // Reference to source document (polymorphic)
            $table->string('reference_type'); // invoice, return_order, order, manual
            $table->unsignedBigInteger('reference_id')->nullable(); // ID của đối tượng tham chiếu
            
            // Customer and branch information
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('set null');
            $table->foreignId('branch_shop_id')->nullable()->constrained('branch_shops')->onDelete('set null');
            
            // Payment details
            $table->datetime('payment_date'); // Thời gian thanh toán
            $table->decimal('amount', 15, 2); // Giá trị phiếu
            $table->enum('payment_method', [
                'cash',     // Tiền mặt
                'card',     // Thẻ
                'transfer', // Chuyển khoản
                'check',    // Séc
                'points',   // Điểm thưởng
                'other'     // Khác
            ]);
            
            // Status and actual amount
            $table->enum('status', [
                'pending',   // Chờ xử lý
                'completed', // Đã hoàn thành
                'cancelled'  // Đã hủy
            ])->default('pending');
            
            $table->decimal('actual_amount', 15, 2)->nullable(); // Tiền thực thu/chi
            
            // Additional information
            $table->string('description')->nullable(); // Mô tả
            $table->text('notes')->nullable(); // Ghi chú
            
            // Bank/Card information (for non-cash payments)
            $table->string('bank_name')->nullable(); // Tên ngân hàng
            $table->string('account_number')->nullable(); // Số tài khoản
            $table->string('transaction_reference')->nullable(); // Mã giao dịch
            
            // Approval information
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            
            // Audit fields
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            // Indexes
            $table->index(['reference_type', 'reference_id']);
            $table->index(['customer_id', 'payment_date']);
            $table->index(['branch_shop_id', 'payment_type']);
            $table->index(['payment_date', 'status']);
            $table->index('payment_method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
