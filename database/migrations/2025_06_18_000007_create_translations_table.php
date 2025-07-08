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
        Schema::create('translations', function (Blueprint $table) {
            // ID bản dịch
            $table->id();

            // Tạo morphs columns riêng biệt - Loại đối tượng cần dịch
            $table->string('translatable_type');
            // ID đối tượng cần dịch
            $table->unsignedBigInteger('translatable_id');

            // Mã ngôn ngữ
            $table->string('language_code', 5);
            // Tên trường cần dịch
            $table->string('field_name', 100);
            // Giá trị đã dịch
            $table->longText('field_value');
            // Đã duyệt chưa
            $table->boolean('is_approved')->default(false);
            // Người tạo
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            // Người cập nhật
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Unique constraint
            $table->unique(['translatable_type', 'translatable_id', 'language_code', 'field_name'], 'translations_unique');

            // Indexes
            $table->index(['translatable_type', 'translatable_id'], 'translations_translatable_index');
            $table->index(['language_code', 'is_approved'], 'translations_language_approved_index');
            $table->foreign('language_code')->references('code')->on('languages')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('translations');
    }
};
