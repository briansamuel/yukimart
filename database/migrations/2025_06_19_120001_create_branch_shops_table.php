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
        Schema::create('branch_shops', function (Blueprint $table) {
            // ID chi nhánh cửa hàng
            $table->id();
            
            // Mã chi nhánh
            $table->string('code', 20)->unique();
            
            // Tên chi nhánh cửa hàng
            $table->string('name');
            
            // Địa chỉ chi nhánh
            $table->text('address');
            
            // Tỉnh/Thành phố
            $table->string('province');
            
            // Quận/Huyện
            $table->string('district');
            
            // Phường/Xã
            $table->string('ward');
            
            // Số điện thoại
            $table->string('phone', 20)->nullable();
            
            // Email
            $table->string('email')->nullable();
            
            // Quản lý chi nhánh
            $table->foreignId('manager_id')->nullable()->constrained('users')->onDelete('set null');
            
            // Trạng thái hoạt động
            $table->enum('status', ['active', 'inactive', 'maintenance'])->default('active');
            
            // Mô tả chi nhánh
            $table->text('description')->nullable();
            
            // Giờ mở cửa
            $table->time('opening_time')->nullable();
            
            // Giờ đóng cửa
            $table->time('closing_time')->nullable();
            
            // Ngày trong tuần hoạt động (JSON)
            $table->json('working_days')->nullable();
            
            // Diện tích cửa hàng (m2)
            $table->decimal('area', 8, 2)->nullable();
            
            // Số lượng nhân viên
            $table->integer('staff_count')->default(0);
            
            // Loại cửa hàng
            $table->enum('shop_type', ['flagship', 'standard', 'mini', 'kiosk'])->default('standard');
            
            // Có giao hàng không
            $table->boolean('has_delivery')->default(false);
            
            // Bán kính giao hàng (km)
            $table->decimal('delivery_radius', 5, 2)->nullable();
            
            // Phí giao hàng cơ bản
            $table->decimal('delivery_fee', 10, 2)->nullable();
            
            // Tọa độ GPS - Latitude
            $table->decimal('latitude', 10, 8)->nullable();
            
            // Tọa độ GPS - Longitude
            $table->decimal('longitude', 11, 8)->nullable();
            
            // Hình ảnh cửa hàng
            $table->string('image')->nullable();
            
            // Thứ tự sắp xếp
            $table->integer('sort_order')->default(0);
            
            // Người tạo
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            
            // Người cập nhật cuối
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            
            // Người xóa
            $table->foreignId('deleted_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes để tối ưu hiệu suất
            $table->index(['status', 'sort_order'], 'branch_shops_status_sort_index');
            $table->index(['province', 'district'], 'branch_shops_location_index');
            $table->index(['manager_id'], 'branch_shops_manager_index');
            $table->index(['shop_type', 'status'], 'branch_shops_type_status_index');
            $table->index(['has_delivery', 'status'], 'branch_shops_delivery_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('branch_shops');
    }
};
