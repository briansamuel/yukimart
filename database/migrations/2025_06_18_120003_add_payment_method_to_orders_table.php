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
        Schema::table('orders', function (Blueprint $table) {
            // Phương thức thanh toán - Tiền mặt, thẻ, chuyển khoản, COD, ví điện tử, trả góp, công nợ, voucher, điểm, hỗn hợp
            $table->enum('payment_method', [
                'cash', 'card', 'transfer', 'cod', 'e_wallet',
                'installment', 'credit', 'voucher', 'points', 'mixed'
            ])->default('cash')->after('channel');

            // Trạng thái thanh toán - Chưa thanh toán, một phần, đã thanh toán, thừa, hoàn tiền
            $table->enum('payment_status', [
                'unpaid', 'partial', 'paid', 'overpaid', 'refunded'
            ])->default('unpaid')->after('payment_method');

            // Mã tham chiếu thanh toán
            $table->string('payment_reference')->nullable()->after('payment_status');

            // Ngày thanh toán
            $table->timestamp('payment_date')->nullable()->after('payment_reference');

            // Ghi chú về thanh toán
            $table->text('payment_notes')->nullable()->after('payment_date');

            // Ngày đến hạn cho thanh toán công nợ
            $table->date('due_date')->nullable()->after('payment_notes');

            // Ghi chú nội bộ
            $table->text('internal_notes')->nullable()->after('note');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'payment_method',
                'payment_status',
                'payment_reference',
                'payment_date',
                'payment_notes',
                'due_date',
                'internal_notes'
            ]);
        });
    }
};
