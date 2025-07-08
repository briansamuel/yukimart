<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            // Mã khách hàng
            $table->string('customer_code')->nullable()->after('id');
            
            // Mã số thuế
            $table->string('tax_code')->nullable()->after('customer_type');
            
            // Facebook
            $table->string('facebook')->nullable()->after('email');
            
            // Khu vực
            $table->string('area')->nullable()->after('address');
            
            // Nhóm khách hàng
            $table->string('customer_group')->nullable()->after('customer_type');
            
            // Ngày sinh
            $table->date('birthday')->nullable()->after('notes');
            
            // Điểm tích lũy
            $table->integer('points')->default(0)->after('birthday');
            
            // Index cho customer_code
            $table->index('customer_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex(['customer_code']);
            $table->dropColumn([
                'customer_code',
                'tax_code', 
                'facebook',
                'area',
                'customer_group',
                'birthday',
                'points'
            ]);
        });
    }
};
