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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable(); // Người thực hiện hành động
            $table->string('action', 50); // Loại hành động (created, updated, deleted, login, logout, etc.)
            $table->string('model_type')->nullable(); // Tên model bị tác động
            $table->unsignedBigInteger('model_id')->nullable(); // ID của model bị tác động
            $table->json('old_values')->nullable(); // Giá trị cũ trước khi thay đổi
            $table->json('new_values')->nullable(); // Giá trị mới sau khi thay đổi
            $table->string('ip_address', 45)->nullable(); // Địa chỉ IP của người thực hiện
            $table->text('user_agent')->nullable(); // Thông tin trình duyệt
            $table->string('url')->nullable(); // URL được truy cập
            $table->string('method', 10)->nullable(); // HTTP method (GET, POST, PUT, DELETE)
            $table->text('description')->nullable(); // Mô tả chi tiết hành động
            $table->json('tags')->nullable(); // Các thẻ để phân loại log
            $table->timestamps();

            // Indexes for better performance
            $table->index('user_id');
            $table->index('action');
            $table->index('model_type');
            $table->index(['model_type', 'model_id']);
            $table->index('created_at');
            $table->index(['user_id', 'created_at']);
            $table->index(['action', 'created_at']);

            // Foreign key constraint
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
