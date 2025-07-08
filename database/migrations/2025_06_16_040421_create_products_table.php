<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->bigIncrements('id'); // ID sản phẩm
            // Begin Các trường trong trang bán hàng
            $table->string('product_name'); // Tên sản phẩm
            $table->string('product_slug'); // Đường dẫn thân thiện
            $table->text('product_description'); // Mô tả ngắn sản phẩm
            $table->longText('product_content'); // Nội dung chi tiết sản phẩm
            $table->string('product_thumbnail')->nullable(); // Ảnh đại diện sản phẩm
            $table->enum('product_status', ['trash', 'pending', 'draft', 'publish']); // Trạng thái sản phẩm
            $table->string('product_type', 20); // Loại sản phẩm
            $table->tinyInteger('product_feature')->default(0); // Sản phẩm nổi bật
            // End Các trường bán hàng
            // Begin Các trường trong quản lý hàng hóa
            $table->string('sku', 20)->unique(); // Mã hàng (mã nội bộ)
            $table->string('barcode', 50)->nullable(); // Mã vạch

            $table->string('brand')->nullable(); // Thương hiệu
            $table->decimal('cost_price', 15, 2); // Giá vốn
            $table->decimal('sale_price', 15, 2); // Giá bán
            $table->integer('reorder_point')->default(0); // Định mức tồn kho tối thiểu
            $table->integer('weight')->nullable(); // Trọng lượng (gram)
            $table->integer('points')->default(0); // Điểm tích lũy
            $table->string('location')->nullable(); // Vị trí trong kho
            // End Các trường quản lý hàng hóa
            $table->string('language', 10)->default('vi'); // Ngôn ngữ
            $table->bigInteger('created_by_user')->default(0)->unsigned(); // Người tạo
            $table->bigInteger('updated_by_user')->default(0)->unsigned(); // Người cập nhật
            $table->softDeletes(); // Xóa mềm
            $table->timestamps(); // Thời gian tạo và cập nhật

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
}
