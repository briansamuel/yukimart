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
        Schema::table('customers', function (Blueprint $table) {
            // Thêm branch_shop_id để liên kết customer với chi nhánh tạo
            $table->foreignId('branch_shop_id')
                  ->nullable()
                  ->after('status')
                  ->constrained('branch_shops')
                  ->onDelete('set null');
            
            // Thêm index cho performance
            $table->index('branch_shop_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['branch_shop_id']);
            $table->dropIndex(['branch_shop_id']);
            $table->dropColumn('branch_shop_id');
        });
    }
};
