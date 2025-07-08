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
        Schema::create('categories', function (Blueprint $table) {
            $table->bigIncrements('id'); // ID danh mục
            $table->string('category_name'); // Tên danh mục
            $table->string('category_slug'); // Đường dẫn thân thiện
            $table->longText('category_description'); // Mô tả danh mục
            $table->string('category_seo_title')->nullable(); // Tiêu đề SEO
            $table->string('category_seo_keyword')->nullable(); // Từ khóa SEO
            $table->string('category_seo_description')->nullable(); // Mô tả SEO
            $table->integer('category_parent')->default(0); // ID danh mục cha
            $table->text('category_thumbnail')->nullable(); // Ảnh đại diện danh mục
            $table->string('category_type'); // Loại danh mục
            $table->string('language', 10)->default('vi'); // Ngôn ngữ
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
        Schema::dropIfExists('categories');
    }
};
