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
        Schema::create('user_settings', function (Blueprint $table) {
            // ID setting
            $table->id();
            // ID người dùng
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            // Khóa setting (theme, language, timezone, etc.)
            $table->string('key', 100);
            // Giá trị setting
            $table->text('value')->nullable();
            // Loại dữ liệu (string, boolean, integer, json)
            $table->enum('type', ['string', 'boolean', 'integer', 'float', 'json'])->default('string');
            // Mô tả setting
            $table->string('description')->nullable();
            // Setting có thể được public không
            $table->boolean('is_public')->default(false);
            // Setting có thể được cache không
            $table->boolean('is_cacheable')->default(true);
            $table->timestamps();
            
            // Unique constraint
            $table->unique(['user_id', 'key'], 'user_settings_unique');
            
            // Indexes
            $table->index(['user_id', 'is_public'], 'user_settings_user_public_index');
            $table->index(['key', 'is_cacheable'], 'user_settings_key_cache_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_settings');
    }
};
