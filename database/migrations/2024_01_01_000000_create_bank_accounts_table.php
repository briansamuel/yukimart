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
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('bank_name'); // Tên ngân hàng
            $table->string('bank_code')->nullable(); // Mã ngân hàng (VCB, TCB, BIDV, etc.)
            $table->string('account_number'); // Số tài khoản
            $table->string('account_holder'); // Tên chủ tài khoản
            $table->string('branch_name')->nullable(); // Tên chi nhánh
            $table->text('qr_code')->nullable(); // QR code cho chuyển khoản
            $table->boolean('is_active')->default(true); // Trạng thái hoạt động
            $table->boolean('is_default')->default(false); // Tài khoản mặc định
            $table->integer('sort_order')->default(0); // Thứ tự hiển thị
            $table->text('notes')->nullable(); // Ghi chú
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_accounts');
    }
};
