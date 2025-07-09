<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use App\Models\BranchShop;
use Carbon\Carbon;
use Faker\Factory as Faker;

class RefreshOrdersInvoicesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🗑️ Xóa dữ liệu cũ...');
        $this->clearOldData();
        
        $this->command->info('📊 Tạo dữ liệu mới...');
        $this->createNewData();
        
        $this->command->info('✅ Hoàn thành!');
    }

    /**
     * Clear old data
     */
    private function clearOldData(): void
    {
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Clear invoice items first
        DB::table('invoice_items')->truncate();
        $this->command->info('   - Đã xóa invoice_items');
        
        // Clear invoices
        DB::table('invoices')->truncate();
        $this->command->info('   - Đã xóa invoices');
        
        // Clear order items first
        DB::table('order_items')->truncate();
        $this->command->info('   - Đã xóa order_items');
        
        // Clear orders
        DB::table('orders')->truncate();
        $this->command->info('   - Đã xóa orders');
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * Create new data
     */
    private function createNewData(): void
    {
        $faker = Faker::create('vi_VN');
        
        // Get available data
        $customers = Customer::all();
        $products = Product::where('product_status', 'publish')->take(100)->get();
        $users = User::all();
        $branchShops = BranchShop::all();
        
        if ($customers->isEmpty() || $products->isEmpty() || $users->isEmpty() || $branchShops->isEmpty()) {
            $this->command->error('❌ Thiếu dữ liệu cơ bản (customers, products, users, branch_shops)');
            return;
        }

        // Define date range: August 2024 to now
        $startDate = Carbon::create(2024, 8, 1);
        $endDate = Carbon::now();
        
        $this->command->info("📅 Tạo dữ liệu từ {$startDate->format('d/m/Y')} đến {$endDate->format('d/m/Y')}");
        
        // Create orders and invoices month by month
        $currentDate = $startDate->copy();
        $totalOrders = 0;
        $totalInvoices = 0;
        
        while ($currentDate->lte($endDate)) {
            $monthStart = $currentDate->copy()->startOfMonth();
            $monthEnd = $currentDate->copy()->endOfMonth();
            
            // Don't go beyond current date
            if ($monthEnd->gt($endDate)) {
                $monthEnd = $endDate->copy();
            }
            
            $this->command->info("   📆 Tháng {$currentDate->format('m/Y')}...");
            
            // Generate 100-200 orders per month
            $ordersThisMonth = rand(100, 200);
            $invoicesThisMonth = rand(100, 200);
            
            // Create orders for this month
            for ($i = 0; $i < $ordersThisMonth; $i++) {
                $this->createOrder($faker, $customers, $products, $users, $branchShops, $monthStart, $monthEnd);
                $totalOrders++;
            }
            
            // Create invoices for this month
            for ($i = 0; $i < $invoicesThisMonth; $i++) {
                $this->createInvoice($faker, $customers, $products, $users, $branchShops, $monthStart, $monthEnd);
                $totalInvoices++;
            }
            
            $currentDate->addMonth();
        }
        
        $this->command->info("✅ Đã tạo {$totalOrders} đơn hàng và {$totalInvoices} hóa đơn");
    }

    /**
     * Create a single order
     */
    private function createOrder($faker, $customers, $products, $users, $branchShops, $monthStart, $monthEnd): void
    {
        // Random date within the month
        $orderDate = $faker->dateTimeBetween($monthStart, $monthEnd);
        $carbonDate = Carbon::instance($orderDate);
        
        // Random customer (10% chance of walk-in customer)
        $customerId = $faker->boolean(90) ? $customers->random()->id : null;
        
        // Random user and branch shop
        $user = $users->random();
        $branchShop = $branchShops->random();
        
        // Order status (processing, completed, cancelled)
        $statuses = ['processing', 'completed', 'cancelled'];
        $status = $faker->randomElement($statuses);
        
        // Payment status (paid, unpaid)
        $paymentStatuses = ['paid', 'unpaid'];
        $paymentStatus = $faker->randomElement($paymentStatuses);
        
        // Payment method (for orders)
        $orderPaymentMethods = ['cash', 'card', 'transfer', 'e_wallet'];
        $orderPaymentMethod = $faker->randomElement($orderPaymentMethods);
        
        // Generate order code
        $orderCode = 'ORD-' . $carbonDate->format('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        // Calculate totals
        $itemCount = rand(1, 5);
        $totalAmount = 0;
        $totalQuantity = 0;
        
        // Create order using DB insert to avoid model events
        $orderId = DB::table('orders')->insertGetId([
            'order_code' => $orderCode,
            'customer_id' => $customerId,
            'branch_shop_id' => $branchShop->id,
            'created_by' => $user->id,
            'sold_by' => $user->id,
            'status' => $status,
            'payment_method' => $orderPaymentMethod,
            'payment_status' => $paymentStatus,
            'payment_date' => $paymentStatus === 'paid' ? $carbonDate : null,
            'total_amount' => 0, // Will update after creating items
            'final_amount' => 0,
            'total_quantity' => 0,
            'discount_amount' => 0,
            'other_amount' => 0,
            'amount_paid' => 0,
            'note' => $faker->optional(0.3)->sentence(),
            'created_at' => $carbonDate,
            'updated_at' => $carbonDate,
        ]);
        
        // Create order items
        for ($i = 0; $i < $itemCount; $i++) {
            $product = $products->random();
            $quantity = rand(1, 3);
            $unitPrice = $product->sale_price;
            $totalPrice = $quantity * $unitPrice;
            
            OrderItem::create([
                'order_id' => $orderId,
                'product_id' => $product->id,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'discount' => 0,
                'total_price' => $totalPrice,
                'created_at' => $carbonDate,
                'updated_at' => $carbonDate,
            ]);
            
            $totalAmount += $totalPrice;
            $totalQuantity += $quantity;
        }
        
        // Update order totals
        $amountPaid = $paymentStatus === 'paid' ? $totalAmount : 0;

        DB::table('orders')->where('id', $orderId)->update([
            'total_amount' => $totalAmount,
            'final_amount' => $totalAmount,
            'total_quantity' => $totalQuantity,
            'amount_paid' => $amountPaid,
        ]);
    }

    /**
     * Create a single invoice
     */
    private function createInvoice($faker, $customers, $products, $users, $branchShops, $monthStart, $monthEnd): void
    {
        // Random date within the month
        $invoiceDate = $faker->dateTimeBetween($monthStart, $monthEnd);
        $carbonDate = Carbon::instance($invoiceDate);
        
        // Random customer (10% chance of walk-in customer)
        $customerId = $faker->boolean(90) ? $customers->random()->id : null;
        
        // Random user and branch shop
        $user = $users->random();
        $branchShop = $branchShops->random();
        
        // Invoice status (completed, processing)
        $statuses = ['completed', 'processing'];
        $status = $faker->randomElement($statuses);
        
        // Payment status (paid, unpaid)
        $paymentStatuses = ['paid', 'unpaid'];
        $paymentStatus = $faker->randomElement($paymentStatuses);
        
        // Sales channel (80% offline/direct)
        $salesChannels = ['offline', 'online', 'marketplace', 'social_media', 'phone_order'];
        $salesChannelWeights = [80, 5, 5, 5, 5]; // 80% offline
        $salesChannel = $faker->randomElement(
            array_merge(
                array_fill(0, 80, 'offline'),
                array_fill(0, 5, 'online'),
                array_fill(0, 5, 'marketplace'),
                array_fill(0, 5, 'social_media'),
                array_fill(0, 5, 'phone_order')
            )
        );
        
        // Payment method (for invoices - must match enum)
        $invoicePaymentMethods = ['cash', 'card', 'transfer', 'check', 'other'];
        $invoicePaymentMethod = $faker->randomElement($invoicePaymentMethods);
        
        // Generate invoice number
        $invoiceNumber = 'INV-' . $carbonDate->format('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        // Calculate totals
        $itemCount = rand(1, 5);
        $subtotal = 0;
        
        // Create invoice using DB insert to avoid model events
        $invoiceId = DB::table('invoices')->insertGetId([
            'invoice_number' => $invoiceNumber,
            'customer_id' => $customerId,
            'branch_shop_id' => $branchShop->id,
            'invoice_type' => 'sale',
            'sales_channel' => $salesChannel,
            'status' => $status,
            'invoice_date' => $carbonDate->toDateString(),
            'due_date' => $carbonDate->addDays(rand(7, 30))->toDateString(),
            'subtotal' => 0, // Will update after creating items
            'tax_rate' => 10,
            'tax_amount' => 0,
            'discount_rate' => 0,
            'discount_amount' => 0,
            'total_amount' => 0,
            'paid_amount' => 0,
            'remaining_amount' => 0,
            'payment_method' => $invoicePaymentMethod,
            'payment_status' => $paymentStatus,
            'paid_at' => $paymentStatus === 'paid' ? $carbonDate : null,
            'notes' => $faker->optional(0.3)->sentence(),
            'created_by' => $user->id,
            'created_at' => $carbonDate,
            'updated_at' => $carbonDate,
        ]);
        
        // Create invoice items
        for ($i = 0; $i < $itemCount; $i++) {
            $product = $products->random();
            $quantity = rand(1, 3);
            $unitPrice = $product->sale_price;
            $lineTotal = $quantity * $unitPrice;
            
            InvoiceItem::create([
                'invoice_id' => $invoiceId,
                'product_id' => $product->id,
                'product_name' => $product->product_name,
                'product_sku' => $product->sku,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'discount_rate' => 0,
                'discount_amount' => 0,
                'tax_rate' => 10,
                'tax_amount' => $lineTotal * 0.1,
                'line_total' => $lineTotal,
                'sort_order' => $i + 1,
                'created_at' => $carbonDate,
                'updated_at' => $carbonDate,
            ]);
            
            $subtotal += $lineTotal;
        }
        
        // Update invoice totals
        $taxAmount = $subtotal * 0.1;
        $totalAmount = $subtotal + $taxAmount;
        $paidAmount = $paymentStatus === 'paid' ? $totalAmount : 0;
        $remainingAmount = $totalAmount - $paidAmount;
        
        DB::table('invoices')->where('id', $invoiceId)->update([
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'paid_amount' => $paidAmount,
            'remaining_amount' => $remainingAmount,
        ]);
    }
}
