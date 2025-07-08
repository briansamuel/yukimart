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
        Schema::create('user_branch_shops', function (Blueprint $table) {
            $table->id();
            
            // User ID
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // Branch Shop ID
            $table->foreignId('branch_shop_id')->constrained('branch_shops')->onDelete('cascade');
            
            // Vai trò của user trong branch shop này
            $table->enum('role_in_shop', ['manager', 'staff', 'cashier', 'sales', 'warehouse_keeper'])->default('staff');
            
            // Ngày bắt đầu làm việc tại chi nhánh này
            $table->date('start_date')->nullable();
            
            // Ngày kết thúc làm việc (null = vẫn đang làm)
            $table->date('end_date')->nullable();
            
            // Trạng thái hoạt động
            $table->boolean('is_active')->default(true);
            
            // Có phải là chi nhánh chính của user không
            $table->boolean('is_primary')->default(false);
            
            // Ghi chú
            $table->text('notes')->nullable();
            
            // Người gán user vào chi nhánh này
            $table->foreignId('assigned_by')->nullable()->constrained('users')->onDelete('set null');
            
            // Thời gian gán
            $table->timestamp('assigned_at')->nullable();
            
            $table->timestamps();
            
            // Unique constraint để đảm bảo user không bị duplicate trong cùng 1 branch shop
            $table->unique(['user_id', 'branch_shop_id'], 'unique_user_branch_shop');
            
            // Indexes để tối ưu hiệu suất
            $table->index(['user_id', 'is_active'], 'idx_user_active_branches');
            $table->index(['branch_shop_id', 'is_active'], 'idx_branch_active_users');
            $table->index(['user_id', 'is_primary'], 'idx_user_primary_branch');
            $table->index(['role_in_shop', 'is_active'], 'idx_role_active');
            $table->index(['start_date', 'end_date'], 'idx_work_period');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_branch_shops');
    }
};
