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
        if (!Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
            // ID thông báo
            $table->id();
            
            // Loại thông báo
            $table->string('type', 50);
            
            // Tiêu đề thông báo
            $table->string('title');
            
            // Nội dung thông báo
            $table->text('message');
            
            // Dữ liệu bổ sung (JSON)
            $table->json('data')->nullable();
            
            // Người nhận thông báo
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            
            // Thông báo cho tất cả (null = tất cả, user_id = cá nhân)
            $table->boolean('is_global')->default(false);
            
            // Trạng thái đã đọc
            $table->boolean('is_read')->default(false);
            
            // Thời gian đọc
            $table->timestamp('read_at')->nullable();
            
            // Mức độ ưu tiên
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            
            // Icon thông báo
            $table->string('icon', 50)->nullable();
            
            // Màu sắc thông báo
            $table->string('color', 20)->nullable();
            
            // URL liên kết
            $table->string('action_url')->nullable();
            
            // Text nút hành động
            $table->string('action_text', 50)->nullable();
            
            // Thông báo có thể đóng
            $table->boolean('is_dismissible')->default(true);
            
            // Thời gian hết hạn
            $table->timestamp('expires_at')->nullable();
            
            // Model liên quan
            $table->string('notifiable_type')->nullable();
            $table->unsignedBigInteger('notifiable_id')->nullable();
            
            // Người tạo thông báo
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            
            // Indexes để tối ưu hiệu suất
            $table->index(['user_id', 'is_read'], 'notifications_user_read_index');
            $table->index(['type', 'created_at'], 'notifications_type_created_index');
            $table->index(['is_global', 'created_at'], 'notifications_global_created_index');
            $table->index(['priority', 'created_at'], 'notifications_priority_created_index');
            $table->index(['notifiable_type', 'notifiable_id'], 'notifications_notifiable_index');
            $table->index(['expires_at'], 'notifications_expires_index');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notifications');
    }
};
