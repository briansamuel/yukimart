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
        Schema::table('invoices', function (Blueprint $table) {
            // Remove payment-related fields that will be moved to payments table
            $table->dropColumn([
                'paid_amount',
                'remaining_amount',
                'payment_method',
                'payment_status',
                'paid_at'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Restore payment-related fields
            $table->decimal('paid_amount', 15, 2)->default(0)->after('total_amount');
            $table->decimal('remaining_amount', 15, 2)->default(0)->after('paid_amount');
            $table->enum('payment_method', ['cash', 'card', 'transfer', 'check', 'other'])->nullable()->after('remaining_amount');
            $table->enum('payment_status', ['unpaid', 'partial', 'paid', 'overpaid'])->default('unpaid')->after('payment_method');
            $table->timestamp('paid_at')->nullable()->after('payment_status');
        });
    }
};
