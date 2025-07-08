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
        Schema::create('notifications', function (Blueprint $table) {
            // ID thông báo (UUID)
            $table->uuid('id')->primary();
            // Loại thông báo (order, invoice, inventory, system, user)
            $table->string('type');

            // Tạo morphs columns riêng biệt - Loại đối tượng nhận thông báo
            $table->string('notifiable_type');
            // ID đối tượng nhận thông báo
            $table->unsignedBigInteger('notifiable_id');

            // Tiêu đề thông báo
            $table->string('title');
            // Nội dung thông báo
            $table->text('message');
            // Dữ liệu bổ sung (JSON)
            $table->json('data')->nullable();
            // Độ ưu tiên
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            // Kênh gửi thông báo
            $table->json('channels')->nullable();
            // Thời gian đọc thông báo
            $table->timestamp('read_at')->nullable();
            // Thời gian hết hạn
            $table->timestamp('expires_at')->nullable();
            // Người tạo thông báo
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Indexes for performance
            $table->index(['notifiable_type', 'notifiable_id'], 'notifications_notifiable_index');
            $table->index(['notifiable_type', 'notifiable_id', 'read_at'], 'notifications_notifiable_read_index');
            $table->index(['type', 'created_at'], 'notifications_type_created_index');
            $table->index(['priority', 'created_at'], 'notifications_priority_created_index');
            $table->index('expires_at', 'notifications_expires_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
