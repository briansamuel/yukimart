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
        Schema::create('translation_keys', function (Blueprint $table) {
            // ID khóa dịch
            $table->id();
            // Khóa dịch (app.welcome, product.name)
            $table->string('key', 255)->unique();
            // Nhóm (app, product, order, etc.)
            $table->string('group', 100);
            // Mô tả cho người dịch
            $table->text('description')->nullable();
            // Các placeholder có thể sử dụng
            $table->json('placeholders')->nullable();
            // Khóa hệ thống không được xóa
            $table->boolean('is_system')->default(false);
            $table->timestamps();
            
            // Indexes
            $table->index(['group', 'key'], 'translation_keys_group_key_index');
            $table->index('is_system', 'translation_keys_system_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('translation_keys');
    }
};
