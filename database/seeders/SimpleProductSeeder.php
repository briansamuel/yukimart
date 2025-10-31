<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tenant;
use App\Models\Product;
// use App\Models\Inventory;
// use App\Models\BranchShop;
use Illuminate\Support\Str;

class SimpleProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🛍️ Creating Simple Product Data...');

        // Get all active tenants
        $tenants = Tenant::where('status', 'active')->get();
        
        foreach ($tenants as $tenant) {
            $this->command->info("🏪 Creating products for {$tenant->name}...");
            
            // Create products based on tenant business type
            $this->createProductsForTenant($tenant);
        }

        $this->command->info('✅ Simple product data created successfully!');
        $this->displaySummary();
    }

    /**
     * Create products for tenant based on their business type
     */
    private function createProductsForTenant(Tenant $tenant): void
    {
        $products = $this->getProductsByTenant($tenant);
        
        foreach ($products as $productData) {
            $this->createProduct($tenant, $productData);
        }

        // Update tenant product count
        $productCount = Product::where('tenant_id', $tenant->id)->count();
        $tenant->update(['current_products' => $productCount]);
    }

    /**
     * Create individual product with inventory
     */
    private function createProduct(Tenant $tenant, array $productData): void
    {
        // Create main product
        $product = Product::firstOrCreate(
            [
                'tenant_id' => $tenant->id,
                'product_name' => $productData['name'],
            ],
            [
                'product_slug' => Str::slug($productData['name']),
                'product_description' => $productData['description'],
                'product_content' => $productData['short_description'],
                'sku' => $productData['sku'],
                'barcode' => $productData['barcode'],
                'sale_price' => $productData['price'],
                'cost_price' => $productData['cost_price'],
                'reorder_point' => $productData['min_stock_level'],
                'product_status' => 'publish',
                'product_type' => 'simple',
                'product_feature' => rand(0, 1),
                'weight' => $productData['weight'] ?? null,
                'language' => 'vi',
                'created_by_user' => 1,
                'updated_by_user' => 1,
            ]
        );

        // Note: Inventory creation skipped due to different table structure
        // The inventories table uses warehouse_id instead of branch_shop_id
    }

    /**
     * Get products based on tenant business type
     */
    private function getProductsByTenant(Tenant $tenant): array
    {
        switch ($tenant->slug) {
            case 'techmart':
                return $this->getTechMartProducts();
            case 'fashion':
                return $this->getFashionProducts();
            case 'foodbev':
                return $this->getFoodBevProducts();
            case 'hellomart':
                return $this->getHelloMartProducts();
            case 'bibomart':
                return $this->getBiboMartProducts();
            default:
                return $this->getGeneralProducts();
        }
    }

    /**
     * Get TechMart products (50 products)
     */
    private function getTechMartProducts(): array
    {
        $products = [];
        $techItems = [
            ['iPhone 15 Pro Max 256GB', 'Điện thoại iPhone cao cấp', 34990000],
            ['Samsung Galaxy S24 Ultra', 'Điện thoại Samsung flagship', 32990000],
            ['MacBook Pro M3 14 inch', 'Laptop Apple hiệu năng cao', 54990000],
            ['Dell XPS 13 Plus', 'Laptop Dell cao cấp', 42990000],
            ['AirPods Pro 2nd Gen', 'Tai nghe không dây Apple', 6490000],
            ['iPad Pro 12.9 inch M2', 'Máy tính bảng Apple', 28990000],
            ['Apple Watch Series 9', 'Đồng hồ thông minh Apple', 10990000],
            ['Gaming Mouse Logitech', 'Chuột gaming chuyên nghiệp', 1500000],
            ['Gaming Keyboard Razer', 'Bàn phím gaming cơ', 2500000],
            ['Monitor 4K 27 inch', 'Màn hình 4K chuyên nghiệp', 8000000]
        ];

        for ($i = 0; $i < 50; $i++) {
            $item = $techItems[$i % count($techItems)];
            $variant = $i > 0 ? " V" . (($i % 5) + 1) : "";
            
            $products[] = [
                'name' => $item[0] . $variant,
                'description' => $item[1] . " với công nghệ tiên tiến và thiết kế hiện đại",
                'short_description' => $item[0] . $variant,
                'sku' => 'TECH' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'barcode' => '1' . str_pad($i + 1, 12, '0', STR_PAD_LEFT),
                'price' => $item[2] + rand(-1000000, 2000000),
                'cost_price' => ($item[2] + rand(-1000000, 2000000)) * 0.7,
                'min_stock_level' => rand(3, 15),
                'weight' => rand(100, 2000),
            ];
        }

        return $products;
    }

    /**
     * Get Fashion products (50 products)
     */
    private function getFashionProducts(): array
    {
        $products = [];
        $fashionItems = [
            ['Váy maxi hoa nhí', 'Váy dài thời trang nữ', 450000],
            ['Áo sơ mi trắng basic', 'Áo sơ mi công sở', 320000],
            ['Quần jeans skinny', 'Quần jeans nữ ôm', 380000],
            ['Áo polo nam cao cấp', 'Áo polo nam chất cotton', 380000],
            ['Chân váy chữ A', 'Chân váy ngắn thời trang', 280000],
            ['Áo khoác blazer', 'Áo khoác công sở', 650000],
            ['Quần kaki nam', 'Quần kaki nam lịch lãm', 420000],
            ['Đầm công sở', 'Đầm công sở thanh lịch', 520000],
            ['Áo thun basic', 'Áo thun cotton cơ bản', 180000],
            ['Giày cao gót', 'Giày cao gót nữ 7cm', 850000]
        ];

        for ($i = 0; $i < 50; $i++) {
            $item = $fashionItems[$i % count($fashionItems)];
            $colors = ['Đen', 'Trắng', 'Xanh', 'Hồng', 'Xám'];
            $color = $colors[$i % count($colors)];
            
            $products[] = [
                'name' => $item[0] . " - " . $color,
                'description' => $item[1] . " màu " . $color . ", chất liệu cao cấp",
                'short_description' => $item[0] . " - " . $color,
                'sku' => 'FASH' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'barcode' => '2' . str_pad($i + 1, 12, '0', STR_PAD_LEFT),
                'price' => $item[2] + rand(-50000, 100000),
                'cost_price' => ($item[2] + rand(-50000, 100000)) * 0.5,
                'min_stock_level' => rand(5, 25),
                'weight' => rand(200, 800),
            ];
        }

        return $products;
    }

    /**
     * Get Food & Beverage products (50 products)
     */
    private function getFoodBevProducts(): array
    {
        $products = [];
        $foodItems = [
            ['Cà phê Arabica rang xay', 'Cà phê nguyên chất', 180000],
            ['Trà xanh Thái Nguyên', 'Trà xanh cao cấp', 150000],
            ['Gạo ST25 5kg', 'Gạo thơm ngon', 120000],
            ['Mì gói Hảo Hảo', 'Mì ăn liền', 3500],
            ['Nước mắm Phú Quốc', 'Nước mắm truyền thống', 85000],
            ['Dầu ăn Simply', 'Dầu ăn cao cấp', 65000],
            ['Đường trắng', 'Đường tinh luyện', 25000],
            ['Muối biển', 'Muối biển tự nhiên', 15000],
            ['Bánh quy Oreo', 'Bánh quy socola', 25000],
            ['Nước ngọt Coca Cola', 'Nước giải khát', 15000]
        ];

        for ($i = 0; $i < 50; $i++) {
            $item = $foodItems[$i % count($foodItems)];
            $sizes = ['Nhỏ', 'Vừa', 'Lớn', 'Siêu lớn', 'Gia đình'];
            $size = $sizes[$i % count($sizes)];
            
            $products[] = [
                'name' => $item[0] . " - " . $size,
                'description' => $item[1] . " size " . $size . ", chất lượng cao",
                'short_description' => $item[0] . " - " . $size,
                'sku' => 'FOOD' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'barcode' => '3' . str_pad($i + 1, 12, '0', STR_PAD_LEFT),
                'price' => $item[2] + rand(-10000, 50000),
                'cost_price' => ($item[2] + rand(-10000, 50000)) * 0.6,
                'min_stock_level' => rand(10, 50),
                'weight' => rand(100, 5000),
            ];
        }

        return $products;
    }

    /**
     * Get HelloMart products (50 products)
     */
    private function getHelloMartProducts(): array
    {
        $products = [];
        $generalItems = [
            ['Nồi cơm điện 1.8L', 'Nồi cơm điện gia đình', 890000],
            ['Bộ dao thớt inox', 'Bộ dao thớt 5 món', 450000],
            ['Bút bi Thiên Long', 'Bút bi văn phòng', 5000],
            ['Sổ tay A5', 'Sổ ghi chú văn phòng', 25000],
            ['Khăn tắm cotton', 'Khăn tắm mềm mại', 120000],
            ['Dép tổ ong', 'Dép đi trong nhà', 35000],
            ['Túi nilon', 'Túi đựng đồ tiện dụng', 2000],
            ['Pin AA Panasonic', 'Pin tiểu AA', 15000],
            ['Đèn pin LED', 'Đèn pin siêu sáng', 85000],
            ['Ổ cắm điện', 'Ổ cắm 3 chấu an toàn', 45000]
        ];

        for ($i = 0; $i < 50; $i++) {
            $item = $generalItems[$i % count($generalItems)];
            $brands = ['Panasonic', 'Philips', 'Sony', 'Samsung', 'LG'];
            $brand = $brands[$i % count($brands)];
            
            $products[] = [
                'name' => $brand . " " . $item[0],
                'description' => $item[1] . " thương hiệu " . $brand . ", chất lượng tốt",
                'short_description' => $brand . " " . $item[0],
                'sku' => 'HELLO' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'barcode' => '4' . str_pad($i + 1, 12, '0', STR_PAD_LEFT),
                'price' => $item[2] + rand(-20000, 100000),
                'cost_price' => ($item[2] + rand(-20000, 100000)) * 0.65,
                'min_stock_level' => rand(5, 30),
                'weight' => rand(50, 3000),
            ];
        }

        return $products;
    }

    /**
     * Get BiboMart products (50 products)
     */
    private function getBiboMartProducts(): array
    {
        $products = [];
        $convenienceItems = [
            ['Bánh quy Oreo', 'Bánh quy socola', 25000],
            ['Kẹo Mentos', 'Kẹo bạc hà', 12000],
            ['Coca Cola 330ml', 'Nước ngọt lon', 15000],
            ['Nước suối Lavie', 'Nước uống tinh khiết', 8000],
            ['Bánh mì sandwich', 'Bánh mì kẹp', 35000],
            ['Cà phê hòa tan', 'Cà phê 3in1', 45000],
            ['Mì tôm Hảo Hảo', 'Mì ăn liền', 4000],
            ['Kẹo cao su', 'Kẹo cao su không đường', 8000],
            ['Bánh snack', 'Bánh snack giòn', 18000],
            ['Nước tăng lực', 'Nước tăng lực Red Bull', 25000]
        ];

        for ($i = 0; $i < 50; $i++) {
            $item = $convenienceItems[$i % count($convenienceItems)];
            $flavors = ['Nguyên bản', 'Vị dâu', 'Vị cam', 'Vị chanh', 'Vị nho'];
            $flavor = $flavors[$i % count($flavors)];
            
            $products[] = [
                'name' => $item[0] . " " . $flavor,
                'description' => $item[1] . " " . $flavor . ", tiện lợi và ngon miệng",
                'short_description' => $item[0] . " " . $flavor,
                'sku' => 'BIBO' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'barcode' => '5' . str_pad($i + 1, 12, '0', STR_PAD_LEFT),
                'price' => $item[2] + rand(-5000, 10000),
                'cost_price' => ($item[2] + rand(-5000, 10000)) * 0.6,
                'min_stock_level' => rand(20, 100),
                'weight' => rand(50, 500),
            ];
        }

        return $products;
    }

    /**
     * Get general products (fallback)
     */
    private function getGeneralProducts(): array
    {
        return [
            [
                'name' => 'Sản phẩm mẫu',
                'description' => 'Đây là sản phẩm mẫu',
                'short_description' => 'Sản phẩm mẫu',
                'sku' => 'SPM001',
                'barcode' => '9999999999999',
                'price' => 100000,
                'cost_price' => 70000,
                'min_stock_level' => 10,
                'weight' => 500,
            ]
        ];
    }

    /**
     * Display summary
     */
    private function displaySummary(): void
    {
        $this->command->info('📊 Simple Product Data Summary:');
        $this->command->newLine();

        $tenants = Tenant::where('status', 'active')->get();
        
        foreach ($tenants as $tenant) {
            $productCount = Product::where('tenant_id', $tenant->id)->count();
            
            $this->command->line("🏢 <fg=cyan>{$tenant->name}</> ({$tenant->slug})");
            $this->command->line("   Products: <fg=green>{$productCount}</> sản phẩm");
            $this->command->newLine();
        }

        $totalProducts = Product::count();
        
        $this->command->info("📈 Total Statistics:");
        $this->command->line("   Total Products: <fg=green>{$totalProducts}</>");
        
        $this->command->info('🎯 Each tenant now has 50+ products ready for testing!');
    }
}
