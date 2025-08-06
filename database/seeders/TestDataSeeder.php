<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\ReturnOrder;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 Starting Test Data Generation...');
        
        DB::beginTransaction();
        
        try {
            // Create invoices for different time periods
            $this->createCurrentMonthInvoices();
            $this->createPreviousMonthsInvoices();
            $this->createCancelledInvoices();
            
            DB::commit();
            $this->command->info('✅ Test data generated successfully!');
            $this->printSummary();
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('❌ Error generating test data: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create invoices for current month.
     */
    private function createCurrentMonthInvoices(): void
    {
        $this->command->info('📅 Creating current month invoices...');
        
        // Create 15 invoices for current month
        $invoices = Invoice::factory()
            ->count(15)
            ->currentMonth()
            ->create();

        foreach ($invoices as $invoice) {
            // Create 1-5 items per invoice
            InvoiceItem::factory()
                ->count(rand(1, 5))
                ->forInvoice($invoice)
                ->create();

            // Update invoice totals based on items
            $this->updateInvoiceTotals($invoice);

            // 70% chance to have payments
            if (rand(1, 100) <= 70) {
                $this->createPaymentsForInvoice($invoice);
            }

            // 30% chance to have return orders
            if (rand(1, 100) <= 30) {
                ReturnOrder::factory()
                    ->forInvoice($invoice)
                    ->create();
            }
        }
    }

    /**
     * Create invoices for previous months.
     */
    private function createPreviousMonthsInvoices(): void
    {
        $this->command->info('📅 Creating previous months invoices...');
        
        // Create 25 invoices for last 6 months
        $invoices = Invoice::factory()
            ->count(25)
            ->createdBetween('-6 months', '-1 month')
            ->create();

        foreach ($invoices as $invoice) {
            // Create 1-3 items per invoice
            InvoiceItem::factory()
                ->count(rand(1, 3))
                ->forInvoice($invoice)
                ->create();

            // Update invoice totals
            $this->updateInvoiceTotals($invoice);

            // 80% chance to have payments for older invoices
            if (rand(1, 100) <= 80) {
                $this->createPaymentsForInvoice($invoice);
            }

            // 20% chance to have return orders
            if (rand(1, 100) <= 20) {
                ReturnOrder::factory()
                    ->forInvoice($invoice)
                    ->create();
            }
        }
    }

    /**
     * Create cancelled invoices with cancellation data.
     */
    private function createCancelledInvoices(): void
    {
        $this->command->info('❌ Creating cancelled invoices...');
        
        // Create 8 cancelled invoices
        $cancelledInvoices = Invoice::factory()
            ->count(8)
            ->cancelled()
            ->currentMonth()
            ->create();

        foreach ($cancelledInvoices as $invoice) {
            // Create items for cancelled invoices too
            InvoiceItem::factory()
                ->count(rand(1, 3))
                ->forInvoice($invoice)
                ->create();

            // Update totals
            $this->updateInvoiceTotals($invoice);

            // Some cancelled invoices might have partial payments
            if (rand(1, 100) <= 40) {
                $this->createPaymentsForInvoice($invoice, true);
            }
        }
    }

    /**
     * Create payments for an invoice.
     */
    private function createPaymentsForInvoice(Invoice $invoice, bool $partial = false): void
    {
        $remainingAmount = $invoice->total_amount;
        $paymentCount = $partial ? rand(1, 2) : rand(1, 3);
        
        for ($i = 0; $i < $paymentCount && $remainingAmount > 0; $i++) {
            $isLastPayment = ($i == $paymentCount - 1);
            
            if ($isLastPayment && !$partial) {
                $paymentAmount = $remainingAmount; // Pay full remaining amount
            } else {
                $maxPayment = $partial ? $remainingAmount * 0.5 : $remainingAmount * 0.8;
                $paymentAmount = rand($remainingAmount * 0.2, $maxPayment);
            }
            
            Payment::factory()
                ->forInvoice($invoice)
                ->create([
                    'amount' => $paymentAmount,
                ]);
            
            $remainingAmount -= $paymentAmount;
        }
        
        // Note: Invoice payment tracking is handled through payments table
        // No need to update invoice directly as it doesn't have paid_amount column
    }

    /**
     * Update invoice totals based on items.
     */
    private function updateInvoiceTotals(Invoice $invoice): void
    {
        $items = $invoice->invoiceItems;

        $subtotal = $items->sum(function ($item) {
            return $item->quantity * $item->unit_price;
        });

        $totalDiscountAmount = $items->sum('discount_amount');
        $totalTaxAmount = $items->sum('tax_amount');
        $totalAmount = $items->sum('line_total');

        $invoice->update([
            'subtotal' => $subtotal,
            'discount_amount' => $totalDiscountAmount,
            'tax_amount' => $totalTaxAmount,
            'total_amount' => $totalAmount,
        ]);
    }

    /**
     * Print summary of generated data.
     */
    private function printSummary(): void
    {
        $totalInvoices = Invoice::count();
        $currentMonthInvoices = Invoice::whereMonth('created_at', Carbon::now()->month)
                                      ->whereYear('created_at', Carbon::now()->year)
                                      ->count();
        $cancelledInvoices = Invoice::where('status', 'cancelled')->count();
        $totalPayments = Payment::count();
        $totalReturns = ReturnOrder::count();
        
        $this->command->info('');
        $this->command->info('📊 SUMMARY:');
        $this->command->info("📋 Total Invoices: {$totalInvoices}");
        $this->command->info("📅 Current Month Invoices: {$currentMonthInvoices}");
        $this->command->info("❌ Cancelled Invoices: {$cancelledInvoices}");
        $this->command->info("💰 Total Payments: {$totalPayments}");
        $this->command->info("🔄 Total Return Orders: {$totalReturns}");
        $this->command->info('');
    }
}
