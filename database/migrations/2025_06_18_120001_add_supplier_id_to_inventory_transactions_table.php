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
        Schema::table('inventory_transactions', function (Blueprint $table) {
            // Thêm trường supplier_id sau warehouse_id - ID nhà cung cấp
            $table->foreignId('supplier_id')->nullable()->after('warehouse_id')->constrained('suppliers')->onDelete('set null'); // Nhà cung cấp

            // Thêm index để tối ưu hiệu suất truy vấn
            $table->index(['supplier_id', 'created_at'], 'idx_supplier_transactions');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('inventory_transactions', function (Blueprint $table) {
            // Drop foreign key and index first
            $table->dropForeign(['supplier_id']);
            $table->dropIndex('idx_supplier_transactions');
            
            // Drop the column
            $table->dropColumn('supplier_id');
        });
    }
};
