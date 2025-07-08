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
        Schema::create('backup_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Tên lịch backup
            $table->enum('frequency', ['hourly', 'daily', 'weekly', 'monthly']); // Tần suất
            $table->time('time')->nullable(); // Giờ thực hiện (cho daily, weekly, monthly)
            $table->tinyInteger('day_of_week')->nullable(); // Thứ trong tuần (0=CN, 1=T2, ..., 6=T7)
            $table->tinyInteger('day_of_month')->nullable(); // Ngày trong tháng (1-31)
            $table->integer('hour_interval')->nullable(); // Khoảng cách giờ (cho hourly)
            $table->json('tables')->nullable(); // Danh sách bảng cần backup
            $table->boolean('is_active')->default(true); // Trạng thái hoạt động
            $table->integer('retention_days')->default(30); // Số ngày lưu trữ
            $table->timestamp('last_run_at')->nullable(); // Lần chạy cuối
            $table->timestamp('next_run_at')->nullable(); // Lần chạy tiếp theo
            $table->text('description')->nullable(); // Mô tả
            $table->foreignId('created_by')->constrained('users'); // Người tạo
            $table->timestamps();

            // Indexes
            $table->index(['is_active', 'next_run_at']);
            $table->index('frequency');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('backup_schedules');
    }
};
