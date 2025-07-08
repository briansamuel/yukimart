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
        Schema::create('notification_templates', function (Blueprint $table) {
            // ID template thông báo
            $table->id();
            // Loại template (order_created, invoice_overdue, etc.)
            $table->string('type');
            // Kênh gửi
            $table->enum('channel', ['web', 'email', 'sms']);
            // Tiêu đề template
            $table->string('subject');
            // Nội dung template
            $table->text('content');
            // Biến có thể sử dụng trong template
            $table->json('variables')->nullable();
            // Template có hoạt động không
            $table->boolean('is_active')->default(true);
            // Mô tả template
            $table->text('description')->nullable();
            $table->timestamps();
            
            // Unique constraint
            $table->unique(['type', 'channel'], 'notification_templates_type_channel_unique');
            
            // Indexes
            $table->index(['type', 'is_active'], 'notification_templates_type_active_index');
            $table->index('channel', 'notification_templates_channel_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_templates');
    }
};
