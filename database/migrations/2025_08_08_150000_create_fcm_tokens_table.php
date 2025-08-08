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
        Schema::create('fcm_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('token', 500); // FCM token có thể dài
            $table->enum('device_type', ['android', 'ios', 'web'])->default('android');
            $table->string('device_id')->nullable(); // Unique device identifier
            $table->string('device_name')->nullable(); // Device name/model
            $table->string('app_version')->nullable(); // App version
            $table->string('platform_version')->nullable(); // OS version
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_used_at')->nullable();
            $table->json('metadata')->nullable(); // Additional device info
            $table->timestamps();

            // Indexes
            $table->unique(['user_id', 'device_id']); // One token per device per user
            $table->index(['user_id', 'is_active']);
            $table->index('token');
            $table->index('device_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fcm_tokens');
    }
};
