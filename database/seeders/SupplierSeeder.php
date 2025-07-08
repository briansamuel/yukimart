<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Supplier;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        echo "Creating suppliers...\n";

        // Create specific suppliers for different categories
        $specificSuppliers = [
            [
                'code' => 'SUP001',
                'name' => 'Công ty Điện tử Samsung Việt Nam',
                'company' => 'Samsung Electronics Vietnam Co., Ltd.',
                'phone' => '024-3936-0000',
                'email' => 'contact@samsung.vn',
                'tax_code' => '0123456789',
                'address' => 'Tầng 8, Tòa nhà Lotte Center',
                'province' => 'Hà Nội',
                'district' => 'Quận Ba Đình',
                'ward' => 'Phường Kim Mã',
                'group' => 'Điện tử',
                'status' => 'active',
                'note' => 'Nhà cung cấp thiết bị điện tử hàng đầu'
            ],
            [
                'code' => 'SUP002',
                'name' => 'Công ty Thời trang Zara Vietnam',
                'company' => 'Zara Vietnam Co., Ltd.',
                'phone' => '028-3825-6789',
                'email' => 'info@zara.vn',
                'tax_code' => '0987654321',
                'address' => '123 Nguyễn Huệ',
                'province' => 'TP. Hồ Chí Minh',
                'district' => 'Quận 1',
                'ward' => 'Phường Bến Nghé',
                'group' => 'Thời trang',
                'status' => 'active',
                'note' => 'Thương hiệu thời trang quốc tế'
            ],
            [
                'code' => 'SUP003',
                'name' => 'Công ty Thực phẩm Vinamilk',
                'company' => 'Vietnam Dairy Products Joint Stock Company',
                'phone' => '028-5413-3333',
                'email' => 'contact@vinamilk.com.vn',
                'tax_code' => '0300100973',
                'address' => '10 Tân Trào, Phường Tân Phú',
                'province' => 'TP. Hồ Chí Minh',
                'district' => 'Quận 7',
                'ward' => 'Phường Tân Phú',
                'group' => 'Thực phẩm',
                'status' => 'active',
                'note' => 'Nhà sản xuất sữa và thực phẩm hàng đầu Việt Nam'
            ],
            [
                'code' => 'SUP004',
                'name' => 'Công ty Mỹ phẩm L\'Oreal Vietnam',
                'company' => 'L\'Oreal Vietnam Co., Ltd.',
                'phone' => '028-3827-4567',
                'email' => 'contact@loreal.vn',
                'tax_code' => '0123789456',
                'address' => 'Tầng 15, Tòa nhà Bitexco Financial Tower',
                'province' => 'TP. Hồ Chí Minh',
                'district' => 'Quận 1',
                'ward' => 'Phường Bến Nghé',
                'group' => 'Mỹ phẩm',
                'status' => 'active',
                'note' => 'Thương hiệu mỹ phẩm cao cấp'
            ],
            [
                'code' => 'SUP005',
                'name' => 'Công ty Gia dụng Panasonic Vietnam',
                'company' => 'Panasonic Vietnam Co., Ltd.',
                'phone' => '024-3936-1111',
                'email' => 'info@panasonic.vn',
                'tax_code' => '0456789123',
                'address' => 'Tầng 12, Tòa nhà Keangnam',
                'province' => 'Hà Nội',
                'district' => 'Quận Cầu Giấy',
                'ward' => 'Phường Mễ Trì',
                'group' => 'Gia dụng',
                'status' => 'active',
                'note' => 'Thiết bị gia dụng chất lượng cao'
            ],
            [
                'code' => 'SUP006',
                'name' => 'Nhà cung cấp ABC',
                'company' => null,
                'phone' => '0987654321',
                'email' => 'abc@example.com',
                'tax_code' => null,
                'address' => '456 Lê Lợi',
                'province' => 'Đà Nẵng',
                'district' => 'Quận Hải Châu',
                'ward' => 'Phường Hải Châu 1',
                'group' => 'Văn phòng phẩm',
                'status' => 'active',
                'note' => 'Nhà cung cấp cá nhân'
            ],
            [
                'code' => 'SUP007',
                'name' => 'Công ty Đồ chơi Lego Vietnam',
                'company' => 'Lego Vietnam Co., Ltd.',
                'phone' => '028-3456-7890',
                'email' => 'contact@lego.vn',
                'tax_code' => '0789123456',
                'address' => '789 Võ Văn Tần',
                'province' => 'TP. Hồ Chí Minh',
                'district' => 'Quận 3',
                'ward' => 'Phường 6',
                'group' => 'Đồ chơi',
                'status' => 'inactive',
                'note' => 'Tạm ngưng hợp tác'
            ]
        ];

        foreach ($specificSuppliers as $supplierData) {
            Supplier::create($supplierData);
            echo "Created supplier: {$supplierData['name']}\n";
        }

        // Create additional random suppliers
        echo "Creating additional random suppliers...\n";
        
        // Create 15 more random suppliers
        Supplier::factory(15)->create();

        // Create some suppliers with specific characteristics
        Supplier::factory(3)->active()->hoChiMinhCity()->group('Điện tử')->create();
        Supplier::factory(2)->active()->hanoi()->group('Thời trang')->create();
        Supplier::factory(2)->inactive()->individual()->create();

        $totalSuppliers = Supplier::count();
        $activeSuppliers = Supplier::where('status', 'active')->count();
        $inactiveSuppliers = Supplier::where('status', 'inactive')->count();

        echo "\n=== Supplier Seeder Summary ===\n";
        echo "Total Suppliers Created: {$totalSuppliers}\n";
        echo "Active Suppliers: {$activeSuppliers}\n";
        echo "Inactive Suppliers: {$inactiveSuppliers}\n";
        echo "Groups: " . Supplier::whereNotNull('group')->distinct()->count('group') . "\n";
        echo "With Branch Shops: " . Supplier::whereNotNull('branch_shop_id')->count() . "\n";
        echo "Companies: " . Supplier::whereNotNull('company')->count() . "\n";
        echo "Individuals: " . Supplier::whereNull('company')->count() . "\n";
        
        // Show group distribution
        echo "\n=== Group Distribution ===\n";
        $groups = Supplier::whereNotNull('group')
            ->selectRaw('`group`, COUNT(*) as count')
            ->groupBy('group')
            ->orderBy('count', 'desc')
            ->get();
            
        foreach ($groups as $group) {
            echo "- {$group->group}: {$group->count} suppliers\n";
        }

        echo "\n=== Location Distribution ===\n";
        $provinces = Supplier::whereNotNull('province')
            ->selectRaw('province, COUNT(*) as count')
            ->groupBy('province')
            ->orderBy('count', 'desc')
            ->get();
            
        foreach ($provinces as $province) {
            echo "- {$province->province}: {$province->count} suppliers\n";
        }

        echo "\nSupplier seeding completed successfully!\n";
    }
}
