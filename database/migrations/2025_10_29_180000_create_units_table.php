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
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            
            // Tên đơn vị (chai, lốc, thùng, hộp, cái, bộ, etc.)
            $table->string('name');
            
            // Trạng thái (ON/OFF toggle)
            $table->boolean('is_active')->default(true);
            
            // Thứ tự sắp xếp
            $table->integer('sort_order')->default(0);
            
            $table->timestamps();
            
            // Indexes
            $table->index('tenant_id');
            $table->index(['tenant_id', 'is_active']);
            $table->index('sort_order');
            
            // Unique constraint: tên đơn vị phải unique trong 1 tenant
            $table->unique(['tenant_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};

