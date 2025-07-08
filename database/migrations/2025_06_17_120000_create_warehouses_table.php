<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWarehousesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Tên kho
            $table->string('code')->unique(); // Mã kho
            $table->text('description')->nullable(); // Mô tả
            $table->string('address')->nullable(); // Địa chỉ
            $table->string('phone')->nullable(); // Số điện thoại
            $table->string('email')->nullable(); // Email
            $table->string('manager_name')->nullable(); // Tên quản lý
            $table->enum('status', ['active', 'inactive'])->default('active'); // Trạng thái
            $table->boolean('is_default')->default(false); // Kho mặc định
            $table->timestamps();
            
            $table->index(['status', 'is_default']);
        });
        
        // Tạo kho mặc định
        DB::table('warehouses')->insert([
            'name' => 'Kho Chính',
            'code' => 'MAIN',
            'description' => 'Kho chính của hệ thống',
            'status' => 'active',
            'is_default' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('warehouses');
    }
}
