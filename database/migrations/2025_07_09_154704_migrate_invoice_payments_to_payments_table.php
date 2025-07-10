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
        // Check if paid_amount column still exists
        if (!Schema::hasColumn('invoices', 'paid_amount')) {
            return; // Migration already completed or column doesn't exist
        }

        // Migrate existing payment data from invoices to payments table
        $invoices = DB::table('invoices')
            ->where('paid_amount', '>', 0)
            ->get();

        foreach ($invoices as $invoice) {
            // Generate payment number
            $paymentNumber = $this->generatePaymentNumber($invoice->invoice_number);
            
            // Create payment record
            DB::table('payments')->insert([
                'payment_number' => $paymentNumber,
                'payment_type' => 'receipt', // Phiếu thu
                'reference_type' => 'invoice',
                'reference_id' => $invoice->id,
                'customer_id' => $invoice->customer_id,
                'branch_shop_id' => $invoice->branch_shop_id,
                'payment_date' => $invoice->paid_at ?? $invoice->created_at,
                'amount' => $invoice->paid_amount,
                'payment_method' => $invoice->payment_method ?? 'cash',
                'status' => 'completed',
                'actual_amount' => $invoice->paid_amount,
                'description' => 'Thanh toán hóa đơn ' . $invoice->invoice_number,
                'created_by' => $invoice->created_by,
                'created_at' => $invoice->created_at,
                'updated_at' => $invoice->updated_at,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove migrated payment records
        DB::table('payments')
            ->where('reference_type', 'invoice')
            ->delete();
    }

    /**
     * Generate payment number based on invoice number
     */
    private function generatePaymentNumber($invoiceNumber)
    {
        // Convert INV20250709001 to TTH20250709001
        $paymentNumber = str_replace('INV', 'TTH', $invoiceNumber);
        
        // Check if payment number already exists
        $count = DB::table('payments')
            ->where('payment_number', 'like', $paymentNumber . '%')
            ->count();
            
        if ($count > 0) {
            $paymentNumber .= '-' . ($count + 1);
        }
        
        return $paymentNumber;
    }
};
