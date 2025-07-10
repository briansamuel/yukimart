<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\ReturnOrder;
use App\Models\ReturnOrderItem;
use App\Models\Invoice;
use App\Models\User;
use App\Models\BranchShop;
use Faker\Factory as Faker;
use Carbon\Carbon;

class ReturnOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        echo "🗑️ Xóa dữ liệu return orders cũ...\n";

        // Clear existing data
        DB::table('return_order_items')->delete();
        DB::table('return_orders')->delete();

        $faker = Faker::create('vi_VN');

        echo "📊 Tạo dữ liệu return orders mới...\n";

        // Get existing data - only completed invoices from current month
        $invoices = Invoice::with(['invoiceItems.product', 'customer'])
                          ->where('status', 'completed')
                          ->where('total_amount', '>', 0)
                          ->where('created_at', '>=', Carbon::now()->startOfMonth())
                          ->get();
        $users = User::all();
        $branchShops = BranchShop::all();

        if ($invoices->isEmpty() || $users->isEmpty() || $branchShops->isEmpty()) {
            echo "⚠️ Không tìm thấy hóa đơn hoàn thành, users, hoặc branch shops. Vui lòng chạy seeders khác trước.\n";
            return;
        }

        $reasons = ['defective', 'wrong_item', 'customer_request', 'damaged', 'expired', 'other'];
        $refundMethods = ['cash', 'card', 'transfer', 'store_credit', 'exchange', 'points'];
        $statuses = ['pending', 'approved', 'rejected', 'completed', 'returned', 'cancelled'];
        $conditions = ['new', 'used', 'damaged', 'expired'];

        echo "📋 Tạo return orders cho " . $invoices->count() . " hóa đơn...\n";

        // Create return orders for about 30% of completed invoices
        $invoicesToProcess = $invoices->random(min(50, $invoices->count()));

        foreach ($invoicesToProcess as $invoice) {
            $user = $faker->randomElement($users);
            $status = $faker->randomElement($statuses);
            
            // Create return date within last 6 months
            $returnDate = $faker->dateTimeBetween('-6 months', 'now');
            
            $returnOrder = ReturnOrder::create([
                'return_number' => $this->generateReturnNumber($returnDate),
                'invoice_id' => $invoice->id,
                'customer_id' => $invoice->customer_id,
                'branch_shop_id' => $invoice->branch_shop_id ?? $faker->randomElement($branchShops)->id,
                'return_date' => $returnDate,
                'reason' => $faker->randomElement($reasons),
                'reason_detail' => $faker->optional(0.6)->sentence(),
                'status' => $status,
                'refund_method' => $faker->randomElement($refundMethods),
                'notes' => $faker->optional(0.4)->sentence(),
                'approved_by' => in_array($status, ['approved', 'rejected', 'completed']) ? $user->id : null,
                'approved_at' => in_array($status, ['approved', 'rejected', 'completed']) ? $returnDate : null,
                'created_by' => $user->id,
                'created_at' => $returnDate,
                'updated_at' => $returnDate,
            ]);

            // Create return order items (1-3 items per return)
            $itemCount = $faker->numberBetween(1, min(3, $invoice->invoiceItems->count()));
            $selectedItems = $invoice->invoiceItems->random($itemCount);
            
            $subtotal = 0;
            
            foreach ($selectedItems as $index => $invoiceItem) {
                // Return 1-50% of original quantity
                $maxReturnQty = max(1, intval($invoiceItem->quantity * 0.5));
                $quantityReturned = $faker->numberBetween(1, $maxReturnQty);
                $lineTotal = $quantityReturned * $invoiceItem->unit_price;
                $subtotal += $lineTotal;
                
                ReturnOrderItem::create([
                    'return_order_id' => $returnOrder->id,
                    'invoice_item_id' => $invoiceItem->id,
                    'product_id' => $invoiceItem->product_id,
                    'product_name' => $invoiceItem->product_name,
                    'product_sku' => $invoiceItem->product_sku,
                    'quantity_returned' => $quantityReturned,
                    'unit_price' => $invoiceItem->unit_price,
                    'line_total' => $lineTotal,
                    'condition' => $faker->randomElement($conditions),
                    'notes' => $faker->optional(0.3)->sentence(),
                    'sort_order' => $index,
                ]);
            }
            
            // Calculate totals
            $taxRate = $invoice->tax_rate ?? 0;
            $taxAmount = $subtotal * ($taxRate / 100);
            $totalAmount = $subtotal + $taxAmount;
            
            $returnOrder->update([
                'subtotal' => $subtotal,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
            ]);
        }

        $this->command->info('Return orders seeded successfully!');
    }

    /**
     * Generate return number based on date.
     */
    private function generateReturnNumber($date)
    {
        $prefix = 'TH';
        $dateStr = $date->format('Ymd');

        // Get count of returns for this date
        $count = ReturnOrder::where('return_number', 'like', $prefix . $dateStr . '%')->count();
        $sequence = str_pad($count + 1, 4, '0', STR_PAD_LEFT);

        return $prefix . $dateStr . $sequence;
    }
}
