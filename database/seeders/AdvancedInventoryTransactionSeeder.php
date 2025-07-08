<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\Warehouse;
use App\Models\User;
use App\Services\WarehouseInventoryService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdvancedInventoryTransactionSeeder extends Seeder
{
    protected $inventoryService;

    public function __construct()
    {
        $this->inventoryService = app(WarehouseInventoryService::class);
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating advanced inventory transactions...');

        // Lấy các kho
        $mainWarehouse = Warehouse::where('code', 'MAIN')->first();
        $hanoiWarehouse = Warehouse::where('code', 'HN01')->first();
        $hcmWarehouse = Warehouse::where('code', 'HCM01')->first();
        $danangWarehouse = Warehouse::where('code', 'DN01')->first();

        if (!$mainWarehouse || !$hanoiWarehouse || !$hcmWarehouse) {
            $this->command->error('Required warehouses not found! Please run WarehouseSeeder first.');
            return;
        }

        // Lấy một số sản phẩm có tồn kho
        $products = Product::join('inventories', 'products.id', '=', 'inventories.product_id')
            ->where('inventories.quantity', '>', 10)
            ->select('products.*', 'inventories.quantity as current_stock')
            ->limit(10)
            ->get();

        if ($products->isEmpty()) {
            $this->command->error('No products with stock found! Please run InventoryTransactionSeeder first.');
            return;
        }

        $user = User::first();
        $currentDate = Carbon::now()->subDays(30);

        DB::transaction(function () use ($products, $mainWarehouse, $hanoiWarehouse, $hcmWarehouse, $danangWarehouse, $user, $currentDate) {
            
            $this->command->info('Creating transfer transactions...');

            // 1. Chuyển hàng từ kho chính đến các chi nhánh
            foreach ($products->take(5) as $index => $product) {
                $transferQuantity = min(5, floor($product->current_stock / 3));
                
                if ($transferQuantity > 0) {
                    // Chuyển đến Hà Nội
                    $result = $this->inventoryService->transferInventory(
                        $product->id,
                        $mainWarehouse->id,
                        $hanoiWarehouse->id,
                        $transferQuantity,
                        "Phân phối hàng đến chi nhánh Hà Nội - {$product->product_name}"
                    );

                    if ($result['success']) {
                        $this->command->info("Transferred {$transferQuantity} {$product->product_name} to Hanoi warehouse");
                    }

                    // Chuyển đến TP.HCM
                    $result = $this->inventoryService->transferInventory(
                        $product->id,
                        $mainWarehouse->id,
                        $hcmWarehouse->id,
                        $transferQuantity,
                        "Phân phối hàng đến chi nhánh TP.HCM - {$product->product_name}"
                    );

                    if ($result['success']) {
                        $this->command->info("Transferred {$transferQuantity} {$product->product_name} to HCM warehouse");
                    }
                }
            }

            // 2. Tạo một số giao dịch xuất kho từ chi nhánh
            $this->command->info('Creating export transactions from branches...');
            
            $branchWarehouses = [$hanoiWarehouse, $hcmWarehouse];
            foreach ($branchWarehouses as $warehouse) {
                // Lấy sản phẩm có tồn kho trong kho này
                $warehouseProducts = Inventory::where('warehouse_id', $warehouse->id)
                    ->where('quantity', '>', 0)
                    ->with('product')
                    ->get();

                foreach ($warehouseProducts->take(3) as $inventory) {
                    $exportQuantity = min(2, $inventory->quantity);
                    
                    if ($exportQuantity > 0) {
                        $result = $this->inventoryService->exportInventory(
                            $inventory->product_id,
                            $warehouse->id,
                            $exportQuantity,
                            "Bán hàng tại {$warehouse->name} - Đơn hàng #" . rand(10000, 99999)
                        );

                        if ($result['success']) {
                            $this->command->info("Exported {$exportQuantity} {$inventory->product->product_name} from {$warehouse->name}");
                        }
                    }
                }
            }

            // 3. Tạo một số giao dịch nhập kho trực tiếp vào chi nhánh
            $this->command->info('Creating direct import transactions to branches...');
            
            foreach ($products->take(3) as $product) {
                $importQuantity = rand(10, 30);
                
                // Nhập trực tiếp vào kho Hà Nội
                $result = $this->inventoryService->importInventory(
                    $product->id,
                    $hanoiWarehouse->id,
                    $importQuantity,
                    $product->cost_price * 0.95, // Giá nhập thấp hơn 5%
                    "Nhập hàng trực tiếp từ nhà cung cấp địa phương - {$product->product_name}"
                );

                if ($result['success']) {
                    $this->command->info("Imported {$importQuantity} {$product->product_name} directly to Hanoi warehouse");
                }
            }

            // 4. Tạo một số giao dịch điều chỉnh
            $this->command->info('Creating adjustment transactions...');
            
            $adjustmentReasons = [
                'Kiểm kê định kỳ - phát hiện sai lệch',
                'Hàng bị hỏng trong quá trình vận chuyển',
                'Điều chỉnh sau khi xử lý hàng trả về',
                'Cập nhật sau kiểm tra chất lượng',
                'Điều chỉnh do lỗi nhập liệu trước đó'
            ];

            foreach ($products->take(4) as $product) {
                // Lấy tồn kho hiện tại từ kho chính
                $currentQuantity = Inventory::getProductQuantityInWarehouse($product->id, $mainWarehouse->id);
                
                if ($currentQuantity > 5) {
                    $adjustmentChange = rand(-3, 2); // Có thể tăng hoặc giảm
                    $newQuantity = max(0, $currentQuantity + $adjustmentChange);
                    
                    if ($newQuantity != $currentQuantity) {
                        $reason = $adjustmentReasons[array_rand($adjustmentReasons)];
                        
                        $result = $this->inventoryService->adjustInventory(
                            $product->id,
                            $mainWarehouse->id,
                            $newQuantity,
                            $reason
                        );

                        if ($result['success']) {
                            $action = $adjustmentChange > 0 ? 'increased' : 'decreased';
                            $this->command->info("Adjusted {$product->product_name} stock {$action} by " . abs($adjustmentChange));
                        }
                    }
                }
            }

            // 5. Tạo một số transaction chuyển kho giữa các chi nhánh
            $this->command->info('Creating inter-branch transfers...');
            
            // Chuyển từ Hà Nội sang Đà Nẵng (nếu có kho Đà Nẵng)
            if ($danangWarehouse) {
                $hanoiProducts = Inventory::where('warehouse_id', $hanoiWarehouse->id)
                    ->where('quantity', '>', 2)
                    ->with('product')
                    ->take(2)
                    ->get();

                foreach ($hanoiProducts as $inventory) {
                    $transferQuantity = min(2, floor($inventory->quantity / 2));
                    
                    if ($transferQuantity > 0) {
                        $result = $this->inventoryService->transferInventory(
                            $inventory->product_id,
                            $hanoiWarehouse->id,
                            $danangWarehouse->id,
                            $transferQuantity,
                            "Hỗ trợ hàng hóa cho kho trung chuyển Đà Nẵng"
                        );

                        if ($result['success']) {
                            $this->command->info("Transferred {$transferQuantity} {$inventory->product->product_name} from Hanoi to Danang");
                        }
                    }
                }
            }

            // 6. Tạo một số transaction với timestamps khác nhau để mô phỏng hoạt động theo thời gian
            $this->command->info('Creating historical transactions...');
            
            $dates = [
                $currentDate->copy()->subDays(20),
                $currentDate->copy()->subDays(15),
                $currentDate->copy()->subDays(10),
                $currentDate->copy()->subDays(5),
                $currentDate->copy()->subDays(2),
            ];

            foreach ($dates as $dateIndex => $date) {
                $product = $products->random();
                $warehouse = collect([$mainWarehouse, $hanoiWarehouse, $hcmWarehouse])->random();
                
                // Random transaction type
                $transactionTypes = ['import', 'export'];
                $type = $transactionTypes[array_rand($transactionTypes)];
                
                if ($type === 'import') {
                    $quantity = rand(5, 15);
                    $result = $this->inventoryService->importInventory(
                        $product->id,
                        $warehouse->id,
                        $quantity,
                        $product->cost_price,
                        "Giao dịch lịch sử ngày " . $date->format('d/m/Y')
                    );
                } else {
                    $currentStock = Inventory::getProductQuantityInWarehouse($product->id, $warehouse->id);
                    if ($currentStock > 0) {
                        $quantity = min(rand(1, 3), $currentStock);
                        $result = $this->inventoryService->exportInventory(
                            $product->id,
                            $warehouse->id,
                            $quantity,
                            "Giao dịch lịch sử ngày " . $date->format('d/m/Y')
                        );
                    }
                }
            }
        });

        // Hiển thị thống kê cuối cùng
        $this->displayFinalStatistics();
    }

    private function displayFinalStatistics()
    {
        $this->command->info("\n=== FINAL INVENTORY STATISTICS ===");
        
        // Thống kê theo kho
        $warehouses = Warehouse::with(['inventories.product'])->get();
        
        foreach ($warehouses as $warehouse) {
            $totalProducts = $warehouse->inventories->count();
            $totalQuantity = $warehouse->inventories->sum('quantity');
            $totalValue = $warehouse->inventories->sum(function($inventory) {
                return $inventory->quantity * $inventory->product->cost_price;
            });
            
            $this->command->info("\n{$warehouse->name} ({$warehouse->code}):");
            $this->command->info("  - Products: {$totalProducts}");
            $this->command->info("  - Total Quantity: {$totalQuantity}");
            $this->command->info("  - Total Value: " . number_format($totalValue, 0, ',', '.') . " VND");
        }
        
        // Thống kê transaction
        $totalTransactions = InventoryTransaction::count();
        $transactionsByType = InventoryTransaction::select('transaction_type', DB::raw('count(*) as count'))
            ->groupBy('transaction_type')
            ->get();
        
        $this->command->info("\n=== TRANSACTION SUMMARY ===");
        $this->command->info("Total Transactions: {$totalTransactions}");
        
        foreach ($transactionsByType as $type) {
            $this->command->info("- {$type->transaction_type}: {$type->count}");
        }
        
        $this->command->info("\nAdvanced inventory transactions created successfully!");
    }
}
