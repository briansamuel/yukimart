<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use Illuminate\Support\Str;

class ProductAttributeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tạo thuộc tính Hương vị
        $flavorAttribute = ProductAttribute::create([
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
                'attribute_id' => $flavorAttribute->id,
                'value' => $flavor['value'],
                'slug' => $flavor['slug'],
                'sort_order' => $flavor['sort_order'],
                'status' => 'active'
            ]);
        }

        // Tạo thuộc tính Kích thước
        $sizeAttribute = ProductAttribute::create([
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
                'attribute_id' => $colorAttribute->id,
                'value' => $color['value'],
                'slug' => $color['slug'],
                'color_code' => $color['color_code'],
                'sort_order' => $color['sort_order'],
                'status' => 'active'
            ]);
        }

        $this->command->info('Created product attributes and values successfully!');
    }
}
