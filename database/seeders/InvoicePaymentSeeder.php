<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\BankAccount;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class InvoicePaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Starting invoice payment seeder...');

        // Get all invoices that don't have payments yet
        $invoices = Invoice::whereDoesntHave('payments')->get();
        
        $this->command->info("Found {$invoices->count()} invoices without payments");

        if ($invoices->isEmpty()) {
            $this->command->info('No invoices found without payments. Exiting...');
            return;
        }

        // Get default bank account or create one if not exists
        $defaultBankAccount = BankAccount::where('is_default', true)->first();
        if (!$defaultBankAccount) {
            // Create a default cash account
            $defaultBankAccount = BankAccount::create([
                'bank_name' => 'Tiền mặt',
                'account_number' => 'CASH001',
                'account_holder' => 'Cửa hàng',
                'is_active' => true,
                'is_default' => true,
                'sort_order' => 1,
                'notes' => 'Tài khoản tiền mặt mặc định'
            ]);
            $this->command->info('Created default cash bank account');
        }

        $paymentMethods = ['cash', 'card', 'transfer'];
        $createdPayments = 0;
        $completedInvoices = 0;

        DB::beginTransaction();

        try {
            foreach ($invoices as $invoice) {
                $this->command->info("Processing invoice: {$invoice->invoice_number}");

                // Determine payment scenario (70% full payment, 20% partial, 10% no payment)
                $scenario = $this->getPaymentScenario();
                
                if ($scenario === 'no_payment') {
                    // Skip this invoice - no payment
                    continue;
                }

                // Calculate payment amount
                $totalAmount = $invoice->total_amount;
                $paymentAmount = $scenario === 'full_payment' 
                    ? $totalAmount 
                    : $totalAmount * rand(30, 80) / 100; // 30-80% for partial

                // Random payment method
                $paymentMethod = $paymentMethods[array_rand($paymentMethods)];

                // Random payment date (within last 30 days)
                $paymentDate = Carbon::parse($invoice->created_at)
                    ->addDays(rand(0, min(30, Carbon::now()->diffInDays($invoice->created_at))));

                // Generate unique payment number for invoice
                $paymentNumber = $this->generateUniquePaymentNumber($invoice->id);

                // Create payment record
                Payment::create([
                    'payment_number' => $paymentNumber,
                    'payment_type' => 'receipt',
                    'reference_type' => 'invoice',
                    'reference_id' => $invoice->id,
                    'customer_id' => $invoice->customer_id,
                    'branch_shop_id' => $invoice->branch_shop_id,
                    'bank_account_id' => $defaultBankAccount->id,
                    'payment_date' => $paymentDate,
                    'amount' => $paymentAmount,
                    'actual_amount' => $paymentAmount,
                    'payment_method' => $paymentMethod,
                    'status' => 'completed',
                    'description' => "Thanh toán hóa đơn {$invoice->invoice_number}",
                    'created_by' => $invoice->created_by,
                    'created_at' => $paymentDate,
                    'updated_at' => $paymentDate,
                ]);

                $createdPayments++;

                // Update invoice status if fully paid
                if ($scenario === 'full_payment') {
                    $invoice->update([
                        'status' => 'completed'
                    ]);
                    $completedInvoices++;
                    $this->command->info("  → Created full payment, updated status to completed");
                } else {
                    $this->command->info("  → Created partial payment: " . number_format($paymentAmount, 0, ',', '.') . " VND");
                }
            }

            DB::commit();

            $this->command->info("\n=== Payment Seeder Completed ===");
            $this->command->info("Total payments created: {$createdPayments}");
            $this->command->info("Invoices marked as completed: {$completedInvoices}");
            $this->command->info("Invoices with partial payments: " . ($createdPayments - $completedInvoices));

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("Error occurred: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get payment scenario based on probability
     */
    private function getPaymentScenario(): string
    {
        $rand = rand(1, 100);

        if ($rand <= 70) {
            return 'full_payment';  // 70% chance
        } elseif ($rand <= 90) {
            return 'partial_payment'; // 20% chance
        } else {
            return 'no_payment';     // 10% chance
        }
    }

    /**
     * Generate unique payment number for invoice
     */
    private function generateUniquePaymentNumber(int $invoiceId): string
    {
        $baseNumber = 'TT' . $invoiceId;

        // Check if payment number already exists
        $existingPayment = Payment::where('payment_number', $baseNumber)->first();

        if (!$existingPayment) {
            return $baseNumber;
        }

        // If exists, add suffix
        $counter = 1;
        do {
            $paymentNumber = $baseNumber . '-' . $counter;
            $existingPayment = Payment::where('payment_number', $paymentNumber)->first();
            $counter++;
        } while ($existingPayment);

        return $paymentNumber;
    }
}
