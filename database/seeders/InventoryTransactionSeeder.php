<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\Warehouse;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InventoryTransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating inventory transactions...');

        // Lấy kho mặc định
        $defaultWarehouse = Warehouse::where('is_default', true)->first();
        if (!$defaultWarehouse) {
            $this->command->error('Default warehouse not found! Please run warehouse migration first.');
            return;
        }

        // Lấy user mặc định
        $user = User::first();
        if (!$user) {
            $user = User::factory()->create([
                'name' => 'Admin User',
                'email' => 'admin@yukimart.com',
                'password' => bcrypt('password'),
            ]);
        }

        // Lấy tất cả products
        $products = Product::all();
        
        if ($products->isEmpty()) {
            $this->command->error('No products found! Please run ProductSeeder first.');
            return;
        }

        DB::transaction(function () use ($products, $defaultWarehouse, $user) {
            
            // Dữ liệu stock cho từng sản phẩm (theo thứ tự trong ProductSeeder)
            $stockData = [
                // Electronics (8 products)
                ['initial' => 50, 'imports' => [30, 20], 'exports' => [5, 3, 2]],   // iPhone
                ['initial' => 35, 'imports' => [25, 15], 'exports' => [4, 2, 1]],   // Samsung
                ['initial' => 15, 'imports' => [10, 8], 'exports' => [2, 1]],       // MacBook
                ['initial' => 80, 'imports' => [50, 30], 'exports' => [10, 8, 5]],  // Sony Headphones
                ['initial' => 25, 'imports' => [20, 10], 'exports' => [3, 2]],      // iPad
                ['initial' => 12, 'imports' => [8, 5], 'exports' => [1, 1]],        // LG TV
                ['initial' => 8, 'imports' => [5, 3], 'exports' => [1]],            // Canon Camera
                ['initial' => 200, 'imports' => [100, 80], 'exports' => [20, 15, 10]], // Xiaomi Band

                // Fashion (7 products)
                ['initial' => 45, 'imports' => [30, 20], 'exports' => [8, 5, 3]],   // Nike
                ['initial' => 38, 'imports' => [25, 15], 'exports' => [6, 4, 2]],   // Adidas
                ['initial' => 150, 'imports' => [100, 50], 'exports' => [25, 20, 15]], // Uniqlo
                ['initial' => 60, 'imports' => [40, 25], 'exports' => [10, 8, 5]],  // Zara
                ['initial' => 75, 'imports' => [50, 30], 'exports' => [12, 10, 8]], // H&M
                ['initial' => 40, 'imports' => [25, 20], 'exports' => [7, 5, 3]],   // Levi's
                ['initial' => 30, 'imports' => [20, 15], 'exports' => [5, 4, 2]],   // Champion

                // Home & Garden (5 products)
                ['initial' => 20, 'imports' => [15, 10], 'exports' => [3, 2, 1]],   // IKEA
                ['initial' => 8, 'imports' => [5, 3], 'exports' => [1]],            // Dyson
                ['initial' => 25, 'imports' => [20, 10], 'exports' => [4, 3, 2]],   // Philips
                ['initial' => 50, 'imports' => [30, 25], 'exports' => [8, 6, 4]],   // Muji
                ['initial' => 12, 'imports' => [8, 5], 'exports' => [2, 1]],        // Xiaomi Vacuum
            ];

            $currentDate = Carbon::now()->subDays(90); // Bắt đầu từ 3 tháng trước

            foreach ($products as $index => $product) {
                $data = $stockData[$index] ?? ['initial' => 10, 'imports' => [5], 'exports' => [1]];
                $currentQuantity = 0;

                $this->command->info("Creating transactions for: {$product->product_name}");

                // 1. Tạo transaction tồn đầu kỳ (initial)
                $initialQuantity = $data['initial'];
                $currentQuantity = $initialQuantity;

                InventoryTransaction::create([
                    'product_id' => $product->id,
                    'warehouse_id' => $defaultWarehouse->id,
                    'transaction_type' => InventoryTransaction::TYPE_INITIAL,
                    'quantity' => $initialQuantity,
                    'old_quantity' => 0,
                    'new_quantity' => $currentQuantity,
                    'unit_cost' => $product->cost_price,
                    'total_value' => $initialQuantity * $product->cost_price,
                    'notes' => "Tồn đầu kỳ - {$product->product_name}",
                    'created_by_user' => $user->id,
                    'created_at' => $currentDate->copy(),
                    'updated_at' => $currentDate->copy(),
                ]);

                // 2. Tạo các transaction nhập kho
                foreach ($data['imports'] as $importIndex => $importQuantity) {
                    $transactionDate = $currentDate->copy()->addDays(10 + ($importIndex * 20));
                    $oldQuantity = $currentQuantity;
                    $currentQuantity += $importQuantity;

                    InventoryTransaction::create([
                        'product_id' => $product->id,
                        'warehouse_id' => $defaultWarehouse->id,
                        'transaction_type' => InventoryTransaction::TYPE_IMPORT,
                        'quantity' => $importQuantity,
                        'old_quantity' => $oldQuantity,
                        'new_quantity' => $currentQuantity,
                        'unit_cost' => $product->cost_price * (1 + rand(-10, 10) / 100), // Biến động giá ±10%
                        'total_value' => $importQuantity * $product->cost_price,
                        'notes' => "Nhập kho lần " . ($importIndex + 1) . " - {$product->product_name}",
                        'created_by_user' => $user->id,
                        'created_at' => $transactionDate,
                        'updated_at' => $transactionDate,
                    ]);
                }

                // 3. Tạo các transaction xuất kho
                foreach ($data['exports'] as $exportIndex => $exportQuantity) {
                    $transactionDate = $currentDate->copy()->addDays(25 + ($exportIndex * 15));
                    
                    // Đảm bảo không xuất quá số lượng hiện có
                    $actualExportQuantity = min($exportQuantity, $currentQuantity);
                    if ($actualExportQuantity <= 0) continue;

                    $oldQuantity = $currentQuantity;
                    $currentQuantity -= $actualExportQuantity;

                    InventoryTransaction::create([
                        'product_id' => $product->id,
                        'warehouse_id' => $defaultWarehouse->id,
                        'transaction_type' => InventoryTransaction::TYPE_EXPORT,
                        'quantity' => -$actualExportQuantity, // Số âm cho xuất kho
                        'old_quantity' => $oldQuantity,
                        'new_quantity' => $currentQuantity,
                        'unit_cost' => $product->cost_price,
                        'total_value' => $actualExportQuantity * $product->cost_price,
                        'notes' => "Xuất kho lần " . ($exportIndex + 1) . " - Đơn hàng #" . rand(1000, 9999),
                        'created_by_user' => $user->id,
                        'created_at' => $transactionDate,
                        'updated_at' => $transactionDate,
                    ]);
                }

                // 4. Tạo một vài transaction điều chỉnh ngẫu nhiên
                if (rand(1, 100) <= 30) { // 30% chance có điều chỉnh
                    $adjustmentDate = $currentDate->copy()->addDays(70);
                    $adjustmentQuantity = rand(-5, 5);
                    
                    if ($adjustmentQuantity != 0 && ($currentQuantity + $adjustmentQuantity) >= 0) {
                        $oldQuantity = $currentQuantity;
                        $currentQuantity += $adjustmentQuantity;

                        InventoryTransaction::create([
                            'product_id' => $product->id,
                            'warehouse_id' => $defaultWarehouse->id,
                            'transaction_type' => InventoryTransaction::TYPE_ADJUSTMENT,
                            'quantity' => $adjustmentQuantity,
                            'old_quantity' => $oldQuantity,
                            'new_quantity' => $currentQuantity,
                            'unit_cost' => $product->cost_price,
                            'total_value' => abs($adjustmentQuantity) * $product->cost_price,
                            'notes' => $adjustmentQuantity > 0 ? 
                                "Điều chỉnh tăng - Kiểm kê phát hiện thêm {$adjustmentQuantity} sản phẩm" :
                                "Điều chỉnh giảm - Kiểm kê phát hiện thiếu " . abs($adjustmentQuantity) . " sản phẩm",
                            'created_by_user' => $user->id,
                            'created_at' => $adjustmentDate,
                            'updated_at' => $adjustmentDate,
                        ]);
                    }
                }

                // 5. Cập nhật inventory với số lượng cuối cùng
                Inventory::updateOrCreate(
                    [
                        'product_id' => $product->id,
                        'warehouse_id' => $defaultWarehouse->id
                    ],
                    [
                        'quantity' => $currentQuantity
                    ]
                );
            }
        });

        // Hiển thị thống kê
        $totalTransactions = InventoryTransaction::count();
        $totalInventory = Inventory::sum('quantity');
        $totalValue = InventoryTransaction::sum('total_value');

        $this->command->info('Successfully created inventory transactions!');
        $this->command->info("Summary:");
        $this->command->info("- Total Transactions: {$totalTransactions}");
        $this->command->info("- Total Current Inventory: {$totalInventory} units");
        $this->command->info("- Total Transaction Value: " . number_format($totalValue, 0, ',', '.') . " VND");

        // Hiển thị breakdown theo loại transaction
        $transactionTypes = InventoryTransaction::select('transaction_type', DB::raw('count(*) as count'))
            ->groupBy('transaction_type')
            ->get();

        $this->command->info("\nTransaction Types:");
        foreach ($transactionTypes as $type) {
            $this->command->info("- {$type->transaction_type}: {$type->count} transactions");
        }
    }
}
