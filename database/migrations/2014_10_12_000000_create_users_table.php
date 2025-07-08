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
        Schema::create('users', function (Blueprint $table) {
            $table->id(); // ID người dùng
            $table->string('username'); // Tên đăng nhập
            $table->string('email', 100)->unique(); // Email (duy nhất)
            $table->string('password'); // Mật khẩu
            $table->string('full_name'); // Họ và tên
            $table->text('description')->nullable(); // Mô tả
            $table->string('address'); // Địa chỉ
            $table->string('phone', 18); // Số điện thoại
            $table->string('avatar', 100)->nullable(); // Ảnh đại diện
            $table->timestamp('birth_date', 0); // Ngày sinh
            $table->string('active_code', 100); // Mã kích hoạt
            $table->string('group_id', 100); // ID nhóm người dùng
            $table->boolean('is_root')->default(false); // Là quản trị viên gốc
            $table->enum('status', ['inactive', 'deactive', 'active', 'blocked'])->default('inactive'); // Trạng thái tài khoản
            $table->timestamp('email_verified_at')->nullable(); // Thời gian xác thực email
            $table->timestamp('last_visit')->nullable(); // Lần truy cập cuối
            $table->rememberToken(); // Token ghi nhớ đăng nhập
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
        Schema::dropIfExists('users');
    }
};
