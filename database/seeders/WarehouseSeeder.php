<?php

namespace Database\Seeders;

use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating additional warehouses...');

        // Tạo thêm một số kho
        $warehouses = [
            [
                'name' => 'Kho Chi Nhánh Hà Nội',
                'code' => 'HN01',
                'description' => 'Kho phân phối khu vực Hà Nội và miền Bắc',
                'address' => '123 Đường ABC, Quận Cầu Giấy, Hà Nội',
                'phone' => '024-1234-5678',
                'email' => 'kho.hanoi@yukimart.com',
                'manager_name' => 'Nguyễn Văn A',
                'status' => 'active',
                'is_default' => false,
            ],
            [
                'name' => 'Kho Chi Nhánh TP.HCM',
                'code' => 'HCM01',
                'description' => 'Kho phân phối khu vực TP.HCM và miền Nam',
                'address' => '456 Đường XYZ, Quận 1, TP.HCM',
                'phone' => '028-9876-5432',
                'email' => 'kho.hcm@yukimart.com',
                'manager_name' => 'Trần Thị B',
                'status' => 'active',
                'is_default' => false,
            ],
            [
                'name' => 'Kho Trung Chuyển Đà Nẵng',
                'code' => 'DN01',
                'description' => 'Kho trung chuyển khu vực miền Trung',
                'address' => '789 Đường DEF, Quận Hải Châu, Đà Nẵng',
                'phone' => '0236-1111-2222',
                'email' => 'kho.danang@yukimart.com',
                'manager_name' => 'Lê Văn C',
                'status' => 'active',
                'is_default' => false,
            ],
            [
                'name' => 'Kho Hàng Lỗi',
                'code' => 'DEFECT',
                'description' => 'Kho chứa hàng lỗi, hàng trả về cần xử lý',
                'address' => 'Khu vực riêng biệt',
                'phone' => null,
                'email' => null,
                'manager_name' => 'Phòng QC',
                'status' => 'active',
                'is_default' => false,
            ]
        ];

        foreach ($warehouses as $warehouseData) {
            Warehouse::create($warehouseData);
            $this->command->info("Created warehouse: {$warehouseData['name']}");
        }

        $this->command->info('Successfully created additional warehouses!');
    }
}
