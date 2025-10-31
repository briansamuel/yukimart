<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

class ProductAttributeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all tenants
        $tenants = Tenant::all();

        if ($tenants->isEmpty()) {
            $this->command->warn('No tenants found. Please create tenants first.');
            return;
        }

        // Delete old data
        $this->command->info('Deleting old product attributes data...');

        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        ProductAttributeValue::truncate();
        ProductAttribute::truncate();

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Create attributes for each tenant
        foreach ($tenants as $tenant) {
            $this->createAttributesForTenant($tenant);
            $this->command->info("Created product attributes for tenant: {$tenant->name}");
        }

        $this->command->info('Product attributes seeder completed successfully!');
    }

    /**
     * Create attributes for a specific tenant
     */
    private function createAttributesForTenant(Tenant $tenant): void
    {
        // Tạo thuộc tính Hương vị
        $flavorAttribute = ProductAttribute::create([
            'tenant_id' => $tenant->id,
            'name' => 'Hương vị',
            'slug' => 'huong-vi',
            'type' => 'select',
            'description' => 'Các loại hương vị khác nhau của sản phẩm',
            'is_required' => true,
            'is_variation' => true,
            'is_visible' => true,
            'sort_order' => 1,
            'status' => 'active'
        ]);

        // Tạo các giá trị hương vị
        $flavors = [
            ['value' => 'Hương Nhài', 'slug' => 'huong-nhai', 'sort_order' => 1],
            ['value' => 'Hương Xoài', 'slug' => 'huong-xoai', 'sort_order' => 2],
            ['value' => 'Hương Dâu', 'slug' => 'huong-dau', 'sort_order' => 3],
            ['value' => 'Hương Cam', 'slug' => 'huong-cam', 'sort_order' => 4],
            ['value' => 'Hương Chanh', 'slug' => 'huong-chanh', 'sort_order' => 5],
            ['value' => 'Hương Dừa', 'slug' => 'huong-dua', 'sort_order' => 6],
        ];

        foreach ($flavors as $flavor) {
            ProductAttributeValue::create([
                'tenant_id' => $tenant->id,
                'attribute_id' => $flavorAttribute->id,
                'value' => $flavor['value'],
                'slug' => $flavor['slug'],
                'sort_order' => $flavor['sort_order'],
                'status' => 'active'
            ]);
        }

        // Tạo thuộc tính Kích thước
        $sizeAttribute = ProductAttribute::create([
            'tenant_id' => $tenant->id,
            'name' => 'Kích thước',
            'slug' => 'kich-thuoc',
            'type' => 'select',
            'description' => 'Các kích thước khác nhau của sản phẩm',
            'is_required' => false,
            'is_variation' => true,
            'is_visible' => true,
            'sort_order' => 2,
            'status' => 'active'
        ]);

        // Tạo các giá trị kích thước
        $sizes = [
            ['value' => 'Gói nhỏ (100g)', 'slug' => 'goi-nho', 'sort_order' => 1, 'price_adjustment' => 0],
            ['value' => 'Gói vừa (250g)', 'slug' => 'goi-vua', 'sort_order' => 2, 'price_adjustment' => 15000],
            ['value' => 'Gói lớn (500g)', 'slug' => 'goi-lon', 'sort_order' => 3, 'price_adjustment' => 35000],
            ['value' => 'Gói gia đình (1kg)', 'slug' => 'goi-gia-dinh', 'sort_order' => 4, 'price_adjustment' => 65000],
        ];

        foreach ($sizes as $size) {
            ProductAttributeValue::create([
                'tenant_id' => $tenant->id,
                'attribute_id' => $sizeAttribute->id,
                'value' => $size['value'],
                'slug' => $size['slug'],
                'sort_order' => $size['sort_order'],
                'price_adjustment' => $size['price_adjustment'],
                'status' => 'active'
            ]);
        }

        // Tạo thuộc tính Màu sắc
        $colorAttribute = ProductAttribute::create([
            'tenant_id' => $tenant->id,
            'name' => 'Màu sắc',
            'slug' => 'mau-sac',
            'type' => 'color',
            'description' => 'Các màu sắc khác nhau của sản phẩm',
            'is_required' => false,
            'is_variation' => true,
            'is_visible' => true,
            'sort_order' => 3,
            'status' => 'active'
        ]);

        // Tạo các giá trị màu sắc
        $colors = [
            ['value' => 'Đỏ', 'slug' => 'do', 'color_code' => '#FF0000', 'sort_order' => 1],
            ['value' => 'Xanh lá', 'slug' => 'xanh-la', 'color_code' => '#00FF00', 'sort_order' => 2],
            ['value' => 'Xanh dương', 'slug' => 'xanh-duong', 'color_code' => '#0000FF', 'sort_order' => 3],
            ['value' => 'Vàng', 'slug' => 'vang', 'color_code' => '#FFFF00', 'sort_order' => 4],
            ['value' => 'Tím', 'slug' => 'tim', 'color_code' => '#800080', 'sort_order' => 5],
            ['value' => 'Hồng', 'slug' => 'hong', 'color_code' => '#FFC0CB', 'sort_order' => 6],
        ];

        foreach ($colors as $color) {
            ProductAttributeValue::create([
                'tenant_id' => $tenant->id,
                'attribute_id' => $colorAttribute->id,
                'value' => $color['value'],
                'slug' => $color['slug'],
                'color_code' => $color['color_code'],
                'sort_order' => $color['sort_order'],
                'status' => 'active'
            ]);
        }

        // Tạo thuộc tính Chất liệu
        $materialAttribute = ProductAttribute::create([
            'tenant_id' => $tenant->id,
            'name' => 'Chất liệu',
            'slug' => 'chat-lieu',
            'type' => 'select',
            'description' => 'Chất liệu sản phẩm',
            'is_required' => false,
            'is_variation' => false,
            'is_visible' => true,
            'sort_order' => 4,
            'status' => 'active'
        ]);

        $materials = [
            ['value' => 'Cotton', 'slug' => 'cotton', 'sort_order' => 1],
            ['value' => 'Polyester', 'slug' => 'polyester', 'sort_order' => 2],
            ['value' => 'Nhựa', 'slug' => 'nhua', 'sort_order' => 3],
            ['value' => 'Thủy tinh', 'slug' => 'thuy-tinh', 'sort_order' => 4],
            ['value' => 'Kim loại', 'slug' => 'kim-loai', 'sort_order' => 5],
        ];

        foreach ($materials as $material) {
            ProductAttributeValue::create([
                'tenant_id' => $tenant->id,
                'attribute_id' => $materialAttribute->id,
                'value' => $material['value'],
                'slug' => $material['slug'],
                'sort_order' => $material['sort_order'],
                'status' => 'active'
            ]);
        }

        // Tạo thuộc tính Xuất xứ
        $originAttribute = ProductAttribute::create([
            'tenant_id' => $tenant->id,
            'name' => 'Xuất xứ',
            'slug' => 'xuat-xu',
            'type' => 'select',
            'description' => 'Xuất xứ sản phẩm',
            'is_required' => false,
            'is_variation' => false,
            'is_visible' => true,
            'sort_order' => 5,
            'status' => 'active'
        ]);

        $origins = [
            ['value' => 'Việt Nam', 'slug' => 'viet-nam', 'sort_order' => 1],
            ['value' => 'Trung Quốc', 'slug' => 'trung-quoc', 'sort_order' => 2],
            ['value' => 'Nhật Bản', 'slug' => 'nhat-ban', 'sort_order' => 3],
            ['value' => 'Hàn Quốc', 'slug' => 'han-quoc', 'sort_order' => 4],
            ['value' => 'Thái Lan', 'slug' => 'thai-lan', 'sort_order' => 5],
            ['value' => 'Mỹ', 'slug' => 'my', 'sort_order' => 6],
        ];

        foreach ($origins as $origin) {
            ProductAttributeValue::create([
                'tenant_id' => $tenant->id,
                'attribute_id' => $originAttribute->id,
                'value' => $origin['value'],
                'slug' => $origin['slug'],
                'sort_order' => $origin['sort_order'],
                'status' => 'active'
            ]);
        }

        // Tạo thuộc tính Thương hiệu
        $brandAttribute = ProductAttribute::create([
            'tenant_id' => $tenant->id,
            'name' => 'Thương hiệu',
            'slug' => 'thuong-hieu',
            'type' => 'select',
            'description' => 'Thương hiệu sản phẩm',
            'is_required' => false,
            'is_variation' => false,
            'is_visible' => true,
            'sort_order' => 6,
            'status' => 'active'
        ]);

        $brands = [
            ['value' => 'Vinamilk', 'slug' => 'vinamilk', 'sort_order' => 1],
            ['value' => 'TH True Milk', 'slug' => 'th-true-milk', 'sort_order' => 2],
            ['value' => 'Coca Cola', 'slug' => 'coca-cola', 'sort_order' => 3],
            ['value' => 'Pepsi', 'slug' => 'pepsi', 'sort_order' => 4],
            ['value' => 'Unilever', 'slug' => 'unilever', 'sort_order' => 5],
        ];

        foreach ($brands as $brand) {
            ProductAttributeValue::create([
                'tenant_id' => $tenant->id,
                'attribute_id' => $brandAttribute->id,
                'value' => $brand['value'],
                'slug' => $brand['slug'],
                'sort_order' => $brand['sort_order'],
                'status' => 'active'
            ]);
        }

        // Tạo thuộc tính Dung tích
        $capacityAttribute = ProductAttribute::create([
            'tenant_id' => $tenant->id,
            'name' => 'Dung tích',
            'slug' => 'dung-tich',
            'type' => 'select',
            'description' => 'Dung tích sản phẩm',
            'is_required' => false,
            'is_variation' => true,
            'is_visible' => true,
            'sort_order' => 7,
            'status' => 'active'
        ]);

        $capacities = [
            ['value' => '100ml', 'slug' => '100ml', 'sort_order' => 1],
            ['value' => '250ml', 'slug' => '250ml', 'sort_order' => 2],
            ['value' => '500ml', 'slug' => '500ml', 'sort_order' => 3],
            ['value' => '1L', 'slug' => '1l', 'sort_order' => 4],
            ['value' => '1.5L', 'slug' => '1-5l', 'sort_order' => 5],
            ['value' => '2L', 'slug' => '2l', 'sort_order' => 6],
        ];

        foreach ($capacities as $capacity) {
            ProductAttributeValue::create([
                'tenant_id' => $tenant->id,
                'attribute_id' => $capacityAttribute->id,
                'value' => $capacity['value'],
                'slug' => $capacity['slug'],
                'sort_order' => $capacity['sort_order'],
                'status' => 'active'
            ]);
        }
    }
}
