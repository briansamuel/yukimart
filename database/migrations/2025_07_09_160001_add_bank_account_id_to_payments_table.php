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
        Schema::table('payments', function (Blueprint $table) {
            // Add bank account relationship
            $table->foreignId('bank_account_id')->nullable()
                  ->after('branch_shop_id')
                  ->constrained('bank_accounts')
                  ->onDelete('set null');
            
            // Add index for better performance
            $table->index(['bank_account_id', 'payment_date']);
            $table->index(['bank_account_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['bank_account_id']);
            $table->dropIndex(['bank_account_id', 'payment_date']);
            $table->dropIndex(['bank_account_id', 'status']);
            $table->dropColumn('bank_account_id');
        });
    }
};
