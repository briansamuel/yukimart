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
        // This migration is for creating a complete branches table with all fields
        // If you need to create the table from scratch, uncomment the code below
        // Otherwise, use the add_new_fields_to_branches_table migration to add fields to existing table

        /*
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('name');                    // Tên chi nhánh
            $table->string('code')->unique()->nullable(); // Mã chi nhánh
            $table->string('address')->nullable();     // Địa chỉ
            $table->string('phone')->nullable();       // Số điện thoại
            $table->string('email')->nullable();       // Email
            $table->string('manager')->nullable();     // Người quản lý
            $table->enum('status', ['active', 'inactive'])->default('active'); // Trạng thái
            $table->text('description')->nullable();   // Mô tả
            $table->timestamps();

            // Add indexes for performance
            $table->index('code', 'idx_branches_code');
            $table->index('status', 'idx_branches_status');
            $table->index(['status', 'code'], 'idx_branches_status_code');
        });
        */
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('branches');
    }
};
