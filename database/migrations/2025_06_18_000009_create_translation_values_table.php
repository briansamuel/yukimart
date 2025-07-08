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
        Schema::create('translation_values', function (Blueprint $table) {
            // ID giá trị dịch
            $table->id();
            // ID khóa dịch
            $table->foreignId('translation_key_id')->constrained('translation_keys')->onDelete('cascade');
            // Mã ngôn ngữ
            $table->string('language_code', 5);
            // Giá trị đã dịch
            $table->longText('value');
            // Đã duyệt chưa
            $table->boolean('is_approved')->default(false);
            // Dịch tự động
            $table->boolean('is_auto_translated')->default(false);
            // Ghi chú cho bản dịch
            $table->text('notes')->nullable();
            // Người tạo
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            // Người cập nhật
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            // Unique constraint
            $table->unique(['translation_key_id', 'language_code'], 'translation_values_key_language_unique');
            
            // Indexes
            $table->index(['language_code', 'is_approved'], 'translation_values_language_approved_index');
            $table->index('is_auto_translated', 'translation_values_auto_translated_index');
            $table->foreign('language_code')->references('code')->on('languages')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('translation_values');
    }
};
