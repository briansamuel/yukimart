<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BranchShop;
use App\Models\User;

class BranchShopSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create flagship stores in major cities
        $this->createFlagshipStores();

        // Create standard stores
        $this->createStandardStores();

        // Create mini stores
        $this->createMiniStores();

        // Create kiosks
        $this->createKiosks();

        $this->command->info('Created ' . BranchShop::count() . ' branch shops successfully!');
        $this->displayStatistics();
    }

    /**
     * Create flagship stores in major cities
     */
    private function createFlagshipStores()
    {
        $flagshipData = [
            [
                'code' => 'YM-HN-001',
                'name' => 'YukiMart Hà Nội Flagship Store',
                'address' => '123 Phố Huế',
                'province' => 'Hà Nội',
                'district' => 'Quận Hai Bà Trưng',
                'ward' => 'Phường Phố Huế',
                'phone' => '024-3825-1234',
                'email' => 'hanoi.flagship@yukimart.vn',
                'shop_type' => 'flagship',
                'area' => 450.00,
                'staff_count' => 25,
                'has_delivery' => true,
                'delivery_radius' => 15.00,
                'delivery_fee' => 25000.00,
                'latitude' => 21.0285,
                'longitude' => 105.8542,
                'opening_time' => '08:00',
                'closing_time' => '22:00',
                'working_days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
                'status' => 'active',
                'sort_order' => 1,
            ],
            [
                'code' => 'YM-HCM-001',
                'name' => 'YukiMart TP.HCM Flagship Store',
                'address' => '456 Nguyễn Huệ',
                'province' => 'TP. Hồ Chí Minh',
                'district' => 'Quận 1',
                'ward' => 'Phường Bến Nghé',
                'phone' => '028-3825-5678',
                'email' => 'hcm.flagship@yukimart.vn',
                'shop_type' => 'flagship',
                'area' => 500.00,
                'staff_count' => 30,
                'has_delivery' => true,
                'delivery_radius' => 20.00,
                'delivery_fee' => 30000.00,
                'latitude' => 10.7769,
                'longitude' => 106.7009,
                'opening_time' => '08:00',
                'closing_time' => '23:00',
                'working_days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
                'status' => 'active',
                'sort_order' => 2,
            ],
            [
                'code' => 'YM-DN-001',
                'name' => 'YukiMart Đà Nẵng Flagship Store',
                'address' => '789 Trần Phú',
                'province' => 'Đà Nẵng',
                'district' => 'Quận Hải Châu',
                'ward' => 'Phường Thạch Thang',
                'phone' => '0236-3825-9012',
                'email' => 'danang.flagship@yukimart.vn',
                'shop_type' => 'flagship',
                'area' => 380.00,
                'staff_count' => 20,
                'has_delivery' => true,
                'delivery_radius' => 12.00,
                'delivery_fee' => 20000.00,
                'latitude' => 16.0544,
                'longitude' => 108.2022,
                'opening_time' => '08:30',
                'closing_time' => '22:00',
                'working_days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
                'status' => 'active',
                'sort_order' => 3,
            ],
        ];

        // Get available warehouses
        $warehouses = \App\Models\Warehouse::all();
        $warehouseIds = $warehouses->pluck('id')->toArray();

        foreach ($flagshipData as $index => $data) {
            $data['manager_id'] = User::inRandomOrder()->first()?->id;
            $data['warehouse_id'] = $warehouseIds[$index % count($warehouseIds)] ?? null;
            $data['created_by'] = 1;
            $data['updated_by'] = 1;
            $data['description'] = 'Cửa hàng chính của YukiMart tại ' . $data['province'];

            BranchShop::create($data);
        }
    }

    /**
     * Create standard stores
     */
    private function createStandardStores()
    {
        $standardStores = [
            [
                'province' => 'Hà Nội',
                'district' => 'Quận Cầu Giấy',
                'ward' => 'Phường Dịch Vọng',
                'address' => '321 Cầu Giấy',
                'code' => 'YM-HN-002',
                'name' => 'YukiMart Cầu Giấy Store',
            ],
            [
                'province' => 'TP. Hồ Chí Minh',
                'district' => 'Quận 7',
                'ward' => 'Phường Tân Phú',
                'address' => '654 Nguyễn Thị Thập',
                'code' => 'YM-HCM-002',
                'name' => 'YukiMart Quận 7 Store',
            ],
            [
                'province' => 'Hải Phòng',
                'district' => 'Quận Hồng Bàng',
                'ward' => 'Phường Sở Dầu',
                'address' => '987 Lạch Tray',
                'code' => 'YM-HP-001',
                'name' => 'YukiMart Hải Phòng Store',
            ],
            [
                'province' => 'Cần Thơ',
                'district' => 'Quận Ninh Kiều',
                'ward' => 'Phường Xuân Khánh',
                'address' => '147 Trần Hưng Đạo',
                'code' => 'YM-CT-001',
                'name' => 'YukiMart Cần Thơ Store',
            ],
        ];

        // Get available warehouses
        $warehouses = \App\Models\Warehouse::all();
        $warehouseIds = $warehouses->pluck('id')->toArray();

        foreach ($standardStores as $index => $store) {
            $data = array_merge($store, [
                'phone' => '0' . rand(200000000, 999999999),
                'email' => strtolower(str_replace(' ', '.', $store['name'])) . '@yukimart.vn',
                'manager_id' => User::inRandomOrder()->first()?->id,
                'warehouse_id' => $warehouseIds[$index % count($warehouseIds)] ?? null,
                'shop_type' => 'standard',
                'area' => rand(150, 300),
                'staff_count' => rand(10, 18),
                'has_delivery' => true,
                'delivery_radius' => rand(8, 12),
                'delivery_fee' => rand(15000, 25000),
                'opening_time' => '08:30',
                'closing_time' => '21:30',
                'working_days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
                'status' => 'active',
                'sort_order' => rand(10, 20),
                'created_by' => 1,
                'updated_by' => 1,
                'description' => 'Cửa hàng tiêu chuẩn của YukiMart',
            ]);

            BranchShop::create($data);
        }
    }

    /**
     * Create mini stores
     */
    private function createMiniStores()
    {
        // Create 8 mini stores using factory
        BranchShop::factory()
            ->count(8)
            ->mini()
            ->create([
                'created_by' => 1,
                'updated_by' => 1,
                'status' => 'active',
            ]);
    }

    /**
     * Create kiosks
     */
    private function createKiosks()
    {
        // Create 5 kiosks using factory
        BranchShop::factory()
            ->count(5)
            ->kiosk()
            ->create([
                'created_by' => 1,
                'updated_by' => 1,
                'status' => 'active',
            ]);
    }

    /**
     * Display statistics
     */
    private function displayStatistics()
    {
        $stats = [
            'Total' => BranchShop::count(),
            'Active' => BranchShop::where('status', 'active')->count(),
            'Flagship' => BranchShop::where('shop_type', 'flagship')->count(),
            'Standard' => BranchShop::where('shop_type', 'standard')->count(),
            'Mini' => BranchShop::where('shop_type', 'mini')->count(),
            'Kiosk' => BranchShop::where('shop_type', 'kiosk')->count(),
            'With Delivery' => BranchShop::where('has_delivery', true)->count(),
        ];

        $this->command->info('Branch Shop Statistics:');
        foreach ($stats as $label => $count) {
            $this->command->info("  {$label}: {$count}");
        }

        // Display by province
        $provinces = BranchShop::selectRaw('province, COUNT(*) as count')
            ->groupBy('province')
            ->orderBy('count', 'desc')
            ->get();

        $this->command->info('By Province:');
        foreach ($provinces as $province) {
            $this->command->info("  {$province->province}: {$province->count}");
        }
    }
}
