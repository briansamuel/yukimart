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
        Schema::create('product_categories', function (Blueprint $table) {
            // ID danh mục
            $table->id();
            // Tên danh mục
            $table->string('name');
            // Slug cho URL
            $table->string('slug')->unique();
            // Mô tả danh mục
            $table->text('description')->nullable();
            // Hình ảnh danh mục
            $table->string('image')->nullable();
            // Icon danh mục
            $table->string('icon')->nullable();
            // Màu sắc đại diện
            $table->string('color', 7)->nullable();
            // Danh mục cha
            $table->foreignId('parent_id')->nullable()->constrained('product_categories')->onDelete('cascade');
            // Thứ tự sắp xếp
            $table->integer('sort_order')->default(0);
            // Trạng thái hoạt động
            $table->boolean('is_active')->default(true);
            // Hiển thị trên menu
            $table->boolean('show_in_menu')->default(true);
            // Hiển thị trên trang chủ
            $table->boolean('show_on_homepage')->default(false);
            // Meta title cho SEO
            $table->string('meta_title')->nullable();
            // Meta description cho SEO
            $table->text('meta_description')->nullable();
            // Meta keywords cho SEO
            $table->text('meta_keywords')->nullable();
            // Người tạo
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            // Người cập nhật cuối
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            // Người xóa
            $table->foreignId('deleted_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['parent_id', 'is_active', 'sort_order'], 'categories_parent_active_sort_index');
            $table->index(['is_active', 'show_in_menu'], 'categories_active_menu_index');
            $table->index(['show_on_homepage', 'is_active'], 'categories_homepage_active_index');
            $table->index('slug', 'categories_slug_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_categories');
    }
};
