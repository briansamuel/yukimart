<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductCategory;
use Illuminate\Support\Str;

class ProductCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing categories
        ProductCategory::truncate();

        // Root categories
        $rootCategories = [
            [
                'name' => 'Điện tử',
                'description' => 'Các sản phẩm điện tử, công nghệ',
                'icon' => 'ki-duotone ki-technology',
                'color' => '#3699FF',
                'sort_order' => 1,
                'show_in_menu' => true,
                'show_on_homepage' => true,
                'meta_title' => 'Sản phẩm điện tử - Công nghệ hiện đại',
                'meta_description' => 'Khám phá các sản phẩm điện tử, công nghệ hiện đại với chất lượng cao',
                'meta_keywords' => 'điện tử, công nghệ, smartphone, laptop, tablet',
            ],
            [
                'name' => 'Thời trang',
                'description' => 'Quần áo, phụ kiện thời trang',
                'icon' => 'ki-duotone ki-shirt',
                'color' => '#F64E60',
                'sort_order' => 2,
                'show_in_menu' => true,
                'show_on_homepage' => true,
                'meta_title' => 'Thời trang - Xu hướng mới nhất',
                'meta_description' => 'Cập nhật xu hướng thời trang mới nhất với các sản phẩm chất lượng',
                'meta_keywords' => 'thời trang, quần áo, phụ kiện, giày dép',
            ],
            [
                'name' => 'Gia dụng',
                'description' => 'Đồ gia dụng, nội thất',
                'icon' => 'ki-duotone ki-home',
                'color' => '#1BC5BD',
                'sort_order' => 3,
                'show_in_menu' => true,
                'show_on_homepage' => true,
                'meta_title' => 'Gia dụng - Nội thất hiện đại',
                'meta_description' => 'Các sản phẩm gia dụng, nội thất chất lượng cho ngôi nhà của bạn',
                'meta_keywords' => 'gia dụng, nội thất, đồ dùng nhà bếp, trang trí',
            ],
            [
                'name' => 'Sức khỏe & Làm đẹp',
                'description' => 'Sản phẩm chăm sóc sức khỏe và làm đẹp',
                'icon' => 'ki-duotone ki-heart',
                'color' => '#8950FC',
                'sort_order' => 4,
                'show_in_menu' => true,
                'show_on_homepage' => true,
                'meta_title' => 'Sức khỏe & Làm đẹp - Chăm sóc toàn diện',
                'meta_description' => 'Các sản phẩm chăm sóc sức khỏe và làm đẹp chất lượng cao',
                'meta_keywords' => 'sức khỏe, làm đẹp, mỹ phẩm, chăm sóc da',
            ],
            [
                'name' => 'Thể thao & Du lịch',
                'description' => 'Đồ thể thao, dụng cụ du lịch',
                'icon' => 'ki-duotone ki-soccer',
                'color' => '#FFA800',
                'sort_order' => 5,
                'show_in_menu' => true,
                'show_on_homepage' => false,
                'meta_title' => 'Thể thao & Du lịch - Phong cách sống năng động',
                'meta_description' => 'Các sản phẩm thể thao và du lịch cho lối sống năng động',
                'meta_keywords' => 'thể thao, du lịch, dụng cụ tập luyện, phụ kiện du lịch',
            ],
        ];

        $createdCategories = [];

        foreach ($rootCategories as $categoryData) {
            $categoryData['slug'] = Str::slug($categoryData['name']);
            $categoryData['is_active'] = true;
            $categoryData['created_by'] = 1; // Admin user
            $categoryData['updated_by'] = 1;
            
            $category = ProductCategory::create($categoryData);
            $createdCategories[$categoryData['name']] = $category;
        }

        // Sub categories for Điện tử
        $electronicsSubCategories = [
            [
                'name' => 'Điện thoại',
                'description' => 'Smartphone, điện thoại di động',
                'sort_order' => 1,
            ],
            [
                'name' => 'Laptop',
                'description' => 'Máy tính xách tay, laptop gaming',
                'sort_order' => 2,
            ],
            [
                'name' => 'Tablet',
                'description' => 'Máy tính bảng, iPad',
                'sort_order' => 3,
            ],
            [
                'name' => 'Phụ kiện điện tử',
                'description' => 'Tai nghe, sạc, ốp lưng',
                'sort_order' => 4,
            ],
        ];

        foreach ($electronicsSubCategories as $subCategoryData) {
            $subCategoryData['parent_id'] = $createdCategories['Điện tử']->id;
            $subCategoryData['slug'] = Str::slug($subCategoryData['name']);
            $subCategoryData['is_active'] = true;
            $subCategoryData['show_in_menu'] = true;
            $subCategoryData['show_on_homepage'] = false;
            $subCategoryData['created_by'] = 1;
            $subCategoryData['updated_by'] = 1;
            
            ProductCategory::create($subCategoryData);
        }

        // Sub categories for Thời trang
        $fashionSubCategories = [
            [
                'name' => 'Quần áo nam',
                'description' => 'Thời trang nam, áo sơ mi, quần jean',
                'sort_order' => 1,
            ],
            [
                'name' => 'Quần áo nữ',
                'description' => 'Thời trang nữ, váy, áo dài',
                'sort_order' => 2,
            ],
            [
                'name' => 'Giày dép',
                'description' => 'Giày thể thao, giày cao gót, dép',
                'sort_order' => 3,
            ],
            [
                'name' => 'Phụ kiện thời trang',
                'description' => 'Túi xách, đồng hồ, trang sức',
                'sort_order' => 4,
            ],
        ];

        foreach ($fashionSubCategories as $subCategoryData) {
            $subCategoryData['parent_id'] = $createdCategories['Thời trang']->id;
            $subCategoryData['slug'] = Str::slug($subCategoryData['name']);
            $subCategoryData['is_active'] = true;
            $subCategoryData['show_in_menu'] = true;
            $subCategoryData['show_on_homepage'] = false;
            $subCategoryData['created_by'] = 1;
            $subCategoryData['updated_by'] = 1;
            
            ProductCategory::create($subCategoryData);
        }

        // Sub categories for Gia dụng
        $homeSubCategories = [
            [
                'name' => 'Đồ dùng nhà bếp',
                'description' => 'Nồi, chảo, dao, thớt',
                'sort_order' => 1,
            ],
            [
                'name' => 'Đồ nội thất',
                'description' => 'Bàn, ghế, tủ, giường',
                'sort_order' => 2,
            ],
            [
                'name' => 'Đồ trang trí',
                'description' => 'Tranh, đèn, cây cảnh',
                'sort_order' => 3,
            ],
            [
                'name' => 'Thiết bị gia đình',
                'description' => 'Máy giặt, tủ lạnh, điều hòa',
                'sort_order' => 4,
            ],
        ];

        foreach ($homeSubCategories as $subCategoryData) {
            $subCategoryData['parent_id'] = $createdCategories['Gia dụng']->id;
            $subCategoryData['slug'] = Str::slug($subCategoryData['name']);
            $subCategoryData['is_active'] = true;
            $subCategoryData['show_in_menu'] = true;
            $subCategoryData['show_on_homepage'] = false;
            $subCategoryData['created_by'] = 1;
            $subCategoryData['updated_by'] = 1;
            
            ProductCategory::create($subCategoryData);
        }

        // Sub categories for Sức khỏe & Làm đẹp
        $healthBeautySubCategories = [
            [
                'name' => 'Mỹ phẩm',
                'description' => 'Son, phấn, kem dưỡng da',
                'sort_order' => 1,
            ],
            [
                'name' => 'Chăm sóc cá nhân',
                'description' => 'Dầu gội, sữa tắm, kem đánh răng',
                'sort_order' => 2,
            ],
            [
                'name' => 'Thực phẩm chức năng',
                'description' => 'Vitamin, thực phẩm bổ sung',
                'sort_order' => 3,
            ],
            [
                'name' => 'Thiết bị y tế',
                'description' => 'Máy đo huyết áp, nhiệt kế',
                'sort_order' => 4,
            ],
        ];

        foreach ($healthBeautySubCategories as $subCategoryData) {
            $subCategoryData['parent_id'] = $createdCategories['Sức khỏe & Làm đẹp']->id;
            $subCategoryData['slug'] = Str::slug($subCategoryData['name']);
            $subCategoryData['is_active'] = true;
            $subCategoryData['show_in_menu'] = true;
            $subCategoryData['show_on_homepage'] = false;
            $subCategoryData['created_by'] = 1;
            $subCategoryData['updated_by'] = 1;
            
            ProductCategory::create($subCategoryData);
        }

        // Sub categories for Thể thao & Du lịch
        $sportsSubCategories = [
            [
                'name' => 'Dụng cụ thể thao',
                'description' => 'Bóng đá, cầu lông, tennis',
                'sort_order' => 1,
            ],
            [
                'name' => 'Quần áo thể thao',
                'description' => 'Áo thun, quần short, giày thể thao',
                'sort_order' => 2,
            ],
            [
                'name' => 'Đồ du lịch',
                'description' => 'Vali, balo, túi du lịch',
                'sort_order' => 3,
            ],
            [
                'name' => 'Thiết bị outdoor',
                'description' => 'Lều, túi ngủ, đèn pin',
                'sort_order' => 4,
            ],
        ];

        foreach ($sportsSubCategories as $subCategoryData) {
            $subCategoryData['parent_id'] = $createdCategories['Thể thao & Du lịch']->id;
            $subCategoryData['slug'] = Str::slug($subCategoryData['name']);
            $subCategoryData['is_active'] = true;
            $subCategoryData['show_in_menu'] = true;
            $subCategoryData['show_on_homepage'] = false;
            $subCategoryData['created_by'] = 1;
            $subCategoryData['updated_by'] = 1;
            
            ProductCategory::create($subCategoryData);
        }

        $this->command->info('Product categories seeded successfully!');
        $this->command->info('Created ' . ProductCategory::count() . ' categories');
    }
}
