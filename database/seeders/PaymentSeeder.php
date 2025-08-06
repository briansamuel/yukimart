<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Payment;
use App\Models\Invoice;
use App\Models\BankAccount;
use Carbon\Carbon;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing payment data
        $this->command->info('Clearing existing payment data...');
        Payment::truncate();

        // Get all invoices that have been paid
        $invoices = Invoice::whereIn('status', ['completed', 'processing'])
                          ->where('total_amount', '>', 0)
                          ->get();

        // Get default bank account
        $defaultBankAccount = BankAccount::getDefault();
        $bankAccounts = BankAccount::getActive();

        // Get branch employees for creator and collector assignment
        $branchEmployees = \App\Models\User::whereHas('branchShops')->get();

        if ($branchEmployees->isEmpty()) {
            $this->command->error('No branch employees found. Please ensure users are assigned to branch shops.');
            return;
        }

        $this->command->info('Creating payments for ' . $invoices->count() . ' invoices...');

        foreach ($invoices as $invoice) {
            // Get employees from the same branch as the invoice
            $branchEmployeesForInvoice = $branchEmployees->filter(function($user) use ($invoice) {
                return $user->branchShops->contains('id', $invoice->branch_shop_id);
            });

            // If no employees in the same branch, use any branch employee
            if ($branchEmployeesForInvoice->isEmpty()) {
                $branchEmployeesForInvoice = $branchEmployees;
            }

            // Select random creator and collector (can be the same person)
            $creator = $branchEmployeesForInvoice->random();
            $collector = $branchEmployeesForInvoice->random();

            // Determine payment method and bank account
            $paymentMethods = ['cash', 'transfer', 'card'];
            $paymentMethod = $paymentMethods[array_rand($paymentMethods)];

            $bankAccountId = null;
            if (in_array($paymentMethod, ['transfer', 'card']) && $bankAccounts->count() > 0) {
                $bankAccountId = $bankAccounts->random()->id;
            }

            // Create payment for invoice
            $payment = Payment::create([
                'payment_number' => 'TT' . $invoice->id, // TT{invoice_id}
                'payment_type' => 'receipt', // Phiếu thu
                'reference_type' => 'invoice',
                'reference_id' => $invoice->id,
                'customer_id' => $invoice->customer_id,
                'branch_shop_id' => $invoice->branch_shop_id,
                'bank_account_id' => $bankAccountId,
                'payment_date' => $invoice->invoice_date,
                'amount' => $invoice->total_amount,
                'payment_method' => $paymentMethod,
                'status' => 'completed',
                'actual_amount' => $invoice->total_amount,
                'description' => 'Thanh toán hóa đơn ' . $invoice->invoice_number,
                'notes' => 'Phiếu thu từ hóa đơn',
                'created_by' => $creator->id, // Branch employee as creator
                'collector_id' => $collector->id, // Branch employee as collector
                'created_at' => $invoice->created_at,
                'updated_at' => $invoice->updated_at,
            ]);

            // Randomly create some partial payments for some invoices
            if (rand(1, 10) <= 3 && $invoice->total_amount > 100000) { // 30% chance for invoices > 100k
                $partialAmount = round($invoice->total_amount * 0.3, -3); // 30% of total, rounded to thousands
                
                Payment::create([
                    'payment_number' => 'TT' . $invoice->id . '-2',
                    'payment_type' => 'receipt',
                    'reference_type' => 'invoice',
                    'reference_id' => $invoice->id,
                    'customer_id' => $invoice->customer_id,
                    'branch_shop_id' => $invoice->branch_shop_id,
                    'bank_account_id' => $bankAccountId,
                    'payment_date' => $invoice->invoice_date->addDays(rand(1, 7)),
                    'amount' => $partialAmount,
                    'payment_method' => $paymentMethod,
                    'status' => 'completed',
                    'actual_amount' => $partialAmount,
                    'description' => 'Thanh toán bổ sung hóa đơn ' . $invoice->invoice_number,
                    'notes' => 'Thanh toán từng phần',
                    'created_by' => 1,
                    'created_at' => $invoice->created_at->addDays(rand(1, 7)),
                    'updated_at' => $invoice->updated_at->addDays(rand(1, 7)),
                ]);
            }
        }

        // Create some manual payments (not related to invoices)
        $this->createManualPayments();

        $this->command->info('Payment seeder completed successfully!');
    }

    /**
     * Create manual payments (receipts and payments).
     */
    private function createManualPayments()
    {
        $bankAccounts = BankAccount::getActive();
        $defaultBankAccount = BankAccount::getDefault();

        // Create manual receipts
        for ($i = 1; $i <= 20; $i++) {
            $paymentMethod = ['cash', 'transfer', 'card'][array_rand(['cash', 'transfer', 'card'])];
            $bankAccountId = null;
            
            if (in_array($paymentMethod, ['transfer', 'card']) && $bankAccounts->count() > 0) {
                $bankAccountId = $bankAccounts->random()->id;
            }

            Payment::create([
                'payment_number' => Payment::generatePaymentNumber('receipt', null, 'manual'),
                'payment_type' => 'receipt',
                'reference_type' => 'manual',
                'reference_id' => null,
                'customer_id' => null,
                'branch_shop_id' => 1,
                'bank_account_id' => $bankAccountId,
                'payment_date' => Carbon::now()->subDays(rand(1, 90)),
                'amount' => rand(50000, 2000000),
                'payment_method' => $paymentMethod,
                'status' => 'completed',
                'actual_amount' => rand(50000, 2000000),
                'description' => 'Thu khác - ' . ['Tiền đặt cọc', 'Hoàn tiền', 'Thu nợ', 'Thu khác'][array_rand(['Tiền đặt cọc', 'Hoàn tiền', 'Thu nợ', 'Thu khác'])],
                'notes' => 'Phiếu thu thủ công',
                'created_by' => 1,
                'created_at' => Carbon::now()->subDays(rand(1, 90)),
                'updated_at' => Carbon::now()->subDays(rand(1, 90)),
            ]);
        }

        // Create manual payments (chi)
        for ($i = 1; $i <= 15; $i++) {
            $paymentMethod = ['cash', 'transfer', 'card'][array_rand(['cash', 'transfer', 'card'])];
            $bankAccountId = null;
            
            if (in_array($paymentMethod, ['transfer', 'card']) && $bankAccounts->count() > 0) {
                $bankAccountId = $bankAccounts->random()->id;
            }

            Payment::create([
                'payment_number' => Payment::generatePaymentNumber('payment', null, 'manual'),
                'payment_type' => 'payment',
                'reference_type' => 'manual',
                'reference_id' => null,
                'customer_id' => null,
                'branch_shop_id' => 1,
                'bank_account_id' => $bankAccountId,
                'payment_date' => Carbon::now()->subDays(rand(1, 90)),
                'amount' => rand(100000, 5000000),
                'payment_method' => $paymentMethod,
                'status' => 'completed',
                'actual_amount' => rand(100000, 5000000),
                'description' => 'Chi khác - ' . ['Mua hàng', 'Trả lương', 'Chi phí vận hành', 'Chi khác'][array_rand(['Mua hàng', 'Trả lương', 'Chi phí vận hành', 'Chi khác'])],
                'notes' => 'Phiếu chi thủ công',
                'created_by' => 1,
                'created_at' => Carbon::now()->subDays(rand(1, 90)),
                'updated_at' => Carbon::now()->subDays(rand(1, 90)),
            ]);
        }
    }
}
