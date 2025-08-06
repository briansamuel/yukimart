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
        Schema::create('customer_point_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->string('reference_type'); // 'invoice', 'return_order', 'payment'
            $table->unsignedBigInteger('reference_id'); // ID của invoice, return_order, payment
            $table->timestamp('transaction_date');
            $table->enum('type', ['purchase', 'return', 'adjustment']); // Loại giao dịch
            $table->decimal('amount', 15, 2)->default(0); // Giá trị giao dịch (tiền)
            $table->integer('points'); // Điểm giao dịch (+/-)
            $table->integer('balance_after'); // Điểm sau giao dịch
            $table->text('notes')->nullable(); // Ghi chú
            $table->timestamps();

            // Indexes
            $table->index(['customer_id', 'transaction_date']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customer_point_transactions');
    }
};
