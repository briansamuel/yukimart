<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Customer;
use App\Models\Product;
use App\Models\BranchShop;
use App\Models\User;
use Carbon\Carbon;
use Faker\Factory as Faker;

class InvoiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('vi_VN');
        
        // Get existing data
        $customers = Customer::all();
        $products = Product::all();
        $branchShops = BranchShop::all();
        $users = User::whereHas('roles', function($q) {
            $q->whereIn('name', ['shop_manager', 'staff']);
        })->with('branchShops')->get();

        if ($customers->isEmpty() || $products->isEmpty() || $branchShops->isEmpty() || $users->isEmpty()) {
            $this->command->error('Please run CustomerSeeder, ProductSeeder, BranchShopSeeder, and UserSeeder first!');
            return;
        }

        $paymentMethods = ['cash', 'card', 'transfer', 'check', 'other'];
        $paymentStatuses = ['unpaid', 'partial', 'paid', 'overpaid'];
        $statuses = ['draft', 'sent', 'paid', 'overdue', 'cancelled'];
        $invoiceTypes = ['sale', 'return', 'adjustment', 'other'];

        $this->command->info('Creating invoices from January to now...');

        // Create invoices for each month from January to current month
        $startDate = Carbon::create(2025, 1, 1);
        $endDate = Carbon::now();
        $totalInvoices = 0;

        while ($startDate->lte($endDate)) {
            $monthStart = $startDate->copy()->startOfMonth();
            $monthEnd = $startDate->copy()->endOfMonth();

            // Create 15-25 invoices per month
            $invoicesThisMonth = rand(15, 25);

            $this->command->info("Creating {$invoicesThisMonth} invoices for " . $monthStart->format('F Y'));

            for ($i = 1; $i <= $invoicesThisMonth; $i++) {
                // Random date within the month
                $createdAt = $faker->dateTimeBetween($monthStart, $monthEnd);
                $carbonDate = Carbon::instance($createdAt);

                // Random user (creator)
                $user = $users->random();
                $userBranchShops = $user->branchShops;

                // If user has no branch shops, assign to random branch
                if ($userBranchShops->isEmpty()) {
                    $branchShop = $branchShops->random();
                } else {
                    $branchShop = $userBranchShops->random();
                }

                // Random customer (80% chance) or walk-in customer (20% chance)
                $customer = $faker->boolean(80) ? $customers->random() : null;
                $customerId = $customer ? $customer->id : null;

                // Random payment method and status
                $paymentMethod = $faker->randomElement($paymentMethods);
                $paymentStatus = $faker->randomElement($paymentStatuses);
                $status = $faker->randomElement(['processing', 'completed', 'cancelled', 'failed']);
                $invoiceType = $faker->randomElement($invoiceTypes);

                // Generate unique invoice number
                $invoiceNumber = 'INV-' . $carbonDate->format('Ymd') . '-' . str_pad($totalInvoices + $i + rand(1000, 9999), 4, '0', STR_PAD_LEFT);

            // Invoice and due dates
            $invoiceDate = $carbonDate->format('Y-m-d');
            $dueDate = $carbonDate->copy()->addDays($faker->numberBetween(7, 30))->format('Y-m-d');

            // Calculate amounts
            $subtotal = $faker->numberBetween(50000, 5000000); // 50k - 5M VND
            $discountRate = $faker->randomFloat(2, 0, 20); // 0-20%
            $discountAmount = $subtotal * ($discountRate / 100);
            $taxRate = $faker->randomElement([0, 5, 10]); // 0%, 5%, 10%
            $taxAmount = ($subtotal - $discountAmount) * ($taxRate / 100);
            $totalAmount = $subtotal - $discountAmount + $taxAmount;

            // Amount paid based on payment status
            $paidAmount = match($paymentStatus) {
                'paid' => $totalAmount,
                'partial' => $faker->numberBetween($totalAmount * 0.3, $totalAmount * 0.8),
                'unpaid' => 0,
                'overpaid' => $totalAmount + $faker->numberBetween(10000, 100000),
            };

            $remainingAmount = $totalAmount - $paidAmount;
            
                $invoice = Invoice::create([
                    'invoice_number' => $invoiceNumber,
                    'customer_id' => $customerId,
                    'branch_shop_id' => $branchShop->id,
                    'invoice_type' => $invoiceType,
                    'status' => $status,
                    'invoice_date' => $invoiceDate,
                    'due_date' => $dueDate,
                    'subtotal' => $subtotal,
                    'tax_rate' => $taxRate,
                    'tax_amount' => $taxAmount,
                    'discount_rate' => $discountRate,
                    'discount_amount' => $discountAmount,
                    'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'remaining_amount' => $remainingAmount,
                'payment_method' => $paymentMethod,
                'payment_status' => $paymentStatus,
                'paid_at' => $paymentStatus === 'paid' ? $carbonDate : null,
                    'paid_amount' => $paidAmount,
                    'remaining_amount' => $remainingAmount,
                    'payment_method' => $paymentMethod,
                    'payment_status' => $paymentStatus,
                    'paid_at' => $paymentStatus === 'paid' ? $carbonDate : null,
                    'notes' => $faker->optional(0.3)->sentence(),
                    'created_by' => $user->id, // Use the selected user as creator
                    'created_at' => $carbonDate,
                    'updated_at' => $carbonDate,
                ]);

            // Create 1-5 invoice items for each invoice
            $itemCount = $faker->numberBetween(1, 5);
            $itemSubtotal = 0;

            for ($j = 0; $j < $itemCount; $j++) {
                $product = $products->random();
                $quantity = $faker->numberBetween(1, 10);
                $unitPrice = $faker->numberBetween(10000, 500000); // 10k - 500k VND

                // Item level discount and tax
                $itemDiscountRate = $faker->randomFloat(2, 0, 10); // 0-10%
                $itemDiscountAmount = ($quantity * $unitPrice) * ($itemDiscountRate / 100);
                $itemTaxRate = $faker->randomElement([0, 5, 10]); // 0%, 5%, 10%
                $itemTaxAmount = (($quantity * $unitPrice) - $itemDiscountAmount) * ($itemTaxRate / 100);
                $lineTotal = ($quantity * $unitPrice) - $itemDiscountAmount + $itemTaxAmount;

                $itemSubtotal += $lineTotal;

                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $product->id,
                    'product_name' => $product->product_name,
                    'product_sku' => $product->sku,
                    'product_description' => $faker->optional(0.5)->sentence(),
                    'quantity' => $quantity,
                    'unit' => $faker->randomElement(['cái', 'kg', 'lít', 'hộp', 'gói']),
                    'unit_price' => $unitPrice,
                    'discount_rate' => $itemDiscountRate,
                    'discount_amount' => $itemDiscountAmount,
                    'tax_rate' => $itemTaxRate,
                    'tax_amount' => $itemTaxAmount,
                    'line_total' => $lineTotal,
                    'notes' => $faker->optional(0.2)->sentence(),
                    'sort_order' => $j + 1,
                    'created_at' => $carbonDate,
                    'updated_at' => $carbonDate,
                ]);
            }

                // Update invoice subtotal to match items
                $invoice->update(['subtotal' => $itemSubtotal]);

                if ($i % 10 == 0) {
                    $this->command->info("Created {$i}/{$invoicesThisMonth} invoices for " . $monthStart->format('F Y'));
                }
            }

            $totalInvoices += $invoicesThisMonth;
            $startDate->addMonth();
        }

        $this->command->info("Successfully created {$totalInvoices} invoices with items!");
    }
}
