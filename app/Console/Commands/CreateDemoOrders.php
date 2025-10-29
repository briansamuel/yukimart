<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Customer;
use App\Models\BranchShop;
use App\Models\Product;
use App\Models\User;
use App\Models\Tenant;
use App\Services\TenantContextService;
use Carbon\Carbon;
use Exception;

class CreateDemoOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'demo:orders {--branch-id= : ID của chi nhánh (mặc định: tất cả chi nhánh)} {--count=10 : Số lượng orders cần tạo} {--date-from= : Ngày bắt đầu (Y-m-d)} {--date-to= : Ngày kết thúc (Y-m-d)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tạo demo orders cho chi nhánh';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            $branchId = $this->option('branch-id');
            $count = (int) $this->option('count');
            $dateFrom = $this->option('date-from') ?: '2025-07-01';
            $dateTo = $this->option('date-to') ?: Carbon::now()->format('Y-m-d');

            $this->info("Bắt đầu tạo {$count} demo orders từ {$dateFrom} đến {$dateTo}...");

            // Validate dates
            try {
                $startDate = Carbon::createFromFormat('Y-m-d', $dateFrom);
                $endDate = Carbon::createFromFormat('Y-m-d', $dateTo);

                if ($startDate->gt($endDate)) {
                    $this->error("Ngày bắt đầu không thể lớn hơn ngày kết thúc");
                    return Command::FAILURE;
                }
            } catch (\Exception $e) {
                $this->error("Định dạng ngày không hợp lệ. Sử dụng định dạng Y-m-d (ví dụ: 2025-07-01)");
                return Command::FAILURE;
            }

            // Get branch shops
            if ($branchId) {
                $branchShops = BranchShop::where('id', $branchId)->where('status', 'active')->get();
                if ($branchShops->isEmpty()) {
                    $this->error("Không tìm thấy chi nhánh với ID: {$branchId}");
                    return Command::FAILURE;
                }
            } else {
                $branchShops = BranchShop::where('status', 'active')->get();
                if ($branchShops->isEmpty()) {
                    $this->error("Không tìm thấy chi nhánh nào đang hoạt động");
                    return Command::FAILURE;
                }
            }

            // Get required data
            $customers = Customer::limit(20)->get();
            $products = Product::where('product_status', 'publish')->limit(20)->get();
            $users = User::limit(10)->get();

            if ($customers->isEmpty()) {
                $this->error("Không có customers nào trong hệ thống");
                return Command::FAILURE;
            }

            if ($products->isEmpty()) {
                $this->error("Không có products nào đang publish");
                return Command::FAILURE;
            }

            if ($users->isEmpty()) {
                $this->error("Không có users nào trong hệ thống");
                return Command::FAILURE;
            }

            $this->info("Tìm thấy:");
            $this->line("- {$branchShops->count()} chi nhánh");
            $this->line("- {$customers->count()} customers");
            $this->line("- {$products->count()} products");
            $this->line("- {$users->count()} users");

            $totalCreated = 0;
            $statuses = ['draft', 'processing', 'completed', 'cancelled'];
            $paymentStatuses = ['unpaid', 'partial', 'paid'];
            $deliveryStatuses = ['pending', 'picking', 'delivering', 'delivered'];

            // Get tenant_id from branch shop
            $tenantId = $branchShops->first()->tenant_id ?? null;
            if (!$tenantId) {
                $this->error("Không thể xác định tenant_id từ chi nhánh");
                return Command::FAILURE;
            }

            // Create orders for each branch shop
            foreach ($branchShops as $branchShop) {
                $this->info("\nTạo orders cho chi nhánh: {$branchShop->name} (ID: {$branchShop->id})");

                $progressBar = $this->output->createProgressBar($count);
                $progressBar->start();

                for ($i = 1; $i <= $count; $i++) {
                    $creator = $users->random();
                    $seller = $users->random();
                    $customer = $customers->random();

                    // Random date within specified range
                    $daysDiff = $startDate->diffInDays($endDate);
                    $randomDays = rand(0, $daysDiff);
                    $orderDate = $startDate->copy()->addDays($randomDays);

                    $status = $statuses[array_rand($statuses)];
                    $paymentStatus = $paymentStatuses[array_rand($paymentStatuses)];
                    $deliveryStatus = $deliveryStatuses[array_rand($deliveryStatuses)];

                    // Generate unique order code
                    $orderCode = 'DH' . $orderDate->format('Ymd') . str_pad($i + time() + $branchShop->id + rand(1000, 9999), 8, '0', STR_PAD_LEFT);

                    $order = Order::withoutGlobalScopes()->create([
                        'order_code' => $orderCode,
                        'customer_id' => $customer->id,
                        'branch_shop_id' => $branchShop->id,
                        'tenant_id' => $tenantId, // Explicitly set tenant_id
                        'status' => $status,
                        'payment_status' => $paymentStatus,
                        'delivery_status' => $deliveryStatus,
                        'channel' => 'direct',
                        'total_amount' => 0, // Will be calculated
                        'discount_amount' => rand(0, 50000),
                        'final_amount' => 0, // Will be calculated
                        'amount_paid' => 0, // Will be calculated
                        'created_by' => $creator->id,
                        'sold_by' => $seller->id,
                        'created_at' => $orderDate,
                        'updated_at' => $orderDate,
                    ]);

                    // Create order items
                    $itemCount = rand(1, 4);
                    $totalAmount = 0;

                    for ($j = 1; $j <= $itemCount; $j++) {
                        $product = $products->random();
                        $quantity = rand(1, 3);
                        $unitPrice = $product->sale_price ?? rand(10000, 500000);
                        $discount = rand(0, $unitPrice * $quantity * 0.1); // Up to 10% discount
                        $totalPrice = ($quantity * $unitPrice) - $discount;
                        $totalAmount += $totalPrice;

                        OrderItem::create([
                            'order_id' => $order->id,
                            'product_id' => $product->id,
                            'quantity' => $quantity,
                            'unit_price' => $unitPrice,
                            'discount' => $discount,
                            'total_price' => $totalPrice,
                            'created_at' => $orderDate,
                            'updated_at' => $orderDate,
                        ]);
                    }

                    // Update order totals
                    $finalAmount = $totalAmount - $order->discount_amount;
                    $amountPaid = $paymentStatus === 'paid' ? $finalAmount :
                                 ($paymentStatus === 'partial' ? $finalAmount * 0.5 : 0);

                    $order->update([
                        'total_amount' => $totalAmount,
                        'final_amount' => $finalAmount,
                        'amount_paid' => $amountPaid,
                    ]);

                    $totalCreated++;
                    $progressBar->advance();
                }

                $progressBar->finish();
                $this->line("\n✓ Đã tạo {$count} orders cho {$branchShop->name}");
            }

            $this->info("\n🎉 Hoàn thành! Đã tạo tổng cộng {$totalCreated} orders cho {$branchShops->count()} chi nhánh.");

            return Command::SUCCESS;

        } catch (Exception $e) {
            $this->error("Có lỗi xảy ra: " . $e->getMessage());
            $this->error("Stack trace: " . $e->getTraceAsString());
            return Command::FAILURE;
        }
    }
}
