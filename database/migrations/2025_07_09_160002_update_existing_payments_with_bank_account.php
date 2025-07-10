<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update existing payments with bank account for transfer/card payments
        $defaultBankAccount = DB::table('bank_accounts')
            ->where('is_active', true)
            ->where('is_default', true)
            ->first();

        if ($defaultBankAccount) {
            // Update transfer payments
            DB::table('payments')
                ->where('payment_method', 'transfer')
                ->whereNull('bank_account_id')
                ->update(['bank_account_id' => $defaultBankAccount->id]);

            // Update card payments
            DB::table('payments')
                ->where('payment_method', 'card')
                ->whereNull('bank_account_id')
                ->update(['bank_account_id' => $defaultBankAccount->id]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove bank_account_id from payments
        DB::table('payments')
            ->whereIn('payment_method', ['transfer', 'card'])
            ->update(['bank_account_id' => null]);
    }
};
