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
        Schema::create('notification_settings', function (Blueprint $table) {
            // ID cài đặt thông báo
            $table->id();
            // ID người dùng
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            // Loại thông báo (order_created, invoice_overdue, etc.)
            $table->string('notification_type');
            // Kênh nhận thông báo
            $table->json('channels')->nullable();
            // Bật/tắt thông báo
            $table->boolean('is_enabled')->default(true);
            // Giờ bắt đầu không nhận thông báo
            $table->time('quiet_hours_start')->nullable();
            // Giờ kết thúc không nhận thông báo
            $table->time('quiet_hours_end')->nullable();
            // Ngày trong tuần không nhận thông báo [0-6]
            $table->json('quiet_days')->nullable();
            $table->timestamps();
            
            // Unique constraint
            $table->unique(['user_id', 'notification_type'], 'notification_settings_user_type_unique');
            
            // Indexes
            $table->index(['user_id', 'is_enabled'], 'notification_settings_user_enabled_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_settings');
    }
};
