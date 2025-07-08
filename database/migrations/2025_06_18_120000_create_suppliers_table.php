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
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->nullable();             // Mã nhà cung cấp
            $table->string('name');                                   // Tên nhà cung cấp
            $table->string('phone')->nullable();                      // Số điện thoại
            $table->string('email')->nullable();                      // Email
            $table->string('company')->nullable();                    // Tên công ty
            $table->string('tax_code')->nullable();                   // Mã số thuế
            $table->string('address')->nullable();                    // Địa chỉ cụ thể
            $table->string('province')->nullable();                   // Tỉnh/TP
            $table->string('district')->nullable();                   // Quận/Huyện
            $table->string('ward')->nullable();                       // Phường/Xã
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('set null'); // Chi nhánh
            $table->string('group')->nullable();                      // Nhóm NCC
            $table->text('note')->nullable();                         // Ghi chú
            $table->enum('status', ['active', 'inactive'])->default('active'); // Trạng thái
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('suppliers');
    }
};
