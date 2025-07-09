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
        Schema::create('backups', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Tên backup
            $table->string('filename'); // Tên file backup
            $table->string('path'); // Đường dẫn file
            $table->enum('type', ['manual', 'auto'])->default('manual'); // Loại backup
            $table->enum('status', ['pending', 'running', 'completed', 'failed'])->default('pending'); // Trạng thái
            $table->bigInteger('file_size')->nullable(); // Kích thước file (bytes)
            $table->json('tables')->nullable(); // Danh sách bảng được backup
            $table->text('description')->nullable(); // Mô tả
            $table->text('error_message')->nullable(); // Thông báo lỗi nếu có
            $table->timestamp('started_at')->nullable(); // Thời gian bắt đầu
            $table->timestamp('completed_at')->nullable(); // Thời gian hoàn thành
            $table->foreignId('schedule_id')->nullable()->constrained('backup_schedules')->nullOnDelete(); // Lịch backup (nếu có)
            $table->foreignId('created_by')->nullable()->constrained('users'); // Người tạo
            $table->timestamps();

            // Indexes
            $table->index(['type', 'status']);
            $table->index('created_at');
            $table->index('filename');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('backups');
    }
};
