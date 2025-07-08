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
        Schema::create('languages', function (Blueprint $table) {
            // ID ngôn ngữ
            $table->id();
            // Mã ngôn ngữ (vi, en, ja)
            $table->string('code', 5)->unique();
            // Tên ngôn ngữ (Tiếng Việt, English)
            $table->string('name', 100);
            // Tên bản địa (Tiếng Việt, English, 日本語)
            $table->string('native_name', 100);
            // Icon cờ quốc gia
            $table->string('flag_icon', 50)->nullable();
            // Ngôn ngữ có hoạt động không
            $table->boolean('is_active')->default(true);
            // Ngôn ngữ mặc định
            $table->boolean('is_default')->default(false);
            // Ngôn ngữ viết từ phải sang trái
            $table->boolean('is_rtl')->default(false);
            // Thứ tự sắp xếp
            $table->integer('sort_order')->default(0);
            // Định dạng ngày tháng
            $table->json('date_format')->nullable();
            // Định dạng số
            $table->json('number_format')->nullable();
            // Mã tiền tệ (VND, USD, JPY)
            $table->string('currency_code', 3)->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['is_active', 'sort_order'], 'languages_active_sort_index');
            $table->index('is_default', 'languages_default_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('languages');
    }
};
