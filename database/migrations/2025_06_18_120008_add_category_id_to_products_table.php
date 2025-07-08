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
        Schema::table('products', function (Blueprint $table) {
            // Danh mục sản phẩm
            $table->foreignId('category_id')->nullable()->constrained('product_categories')->onDelete('set null')->after('id');
            
            // Index cho category_id
            $table->index(['category_id', 'product_status'], 'products_category_status_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropIndex('products_category_status_index');
            $table->dropColumn('category_id');
        });
    }
};
