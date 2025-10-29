<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tenant;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Inventory;
use App\Models\BranchShop;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ComprehensiveProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🛍️ Creating Comprehensive Product Data...');

        // Get all active tenants
        $tenants = Tenant::where('status', 'active')->get();
        
        foreach ($tenants as $tenant) {
            $this->command->info("🏪 Creating products for {$tenant->name}...");
            
            // Create products based on tenant business type
            $this->createProductsForTenant($tenant);
        }

        $this->command->info('✅ Comprehensive product data created successfully!');
        $this->displaySummary();
    }

    /**
     * Create products for tenant based on their business type
     */
    private function createProductsForTenant(Tenant $tenant): void
    {
        $productData = $this->getProductDataByTenant($tenant);
        
        foreach ($productData['categories'] as $categoryData) {
            // Create or get category
            $category = Category::firstOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'name' => $categoryData['name'],
                ],
                [
                    'slug' => Str::slug($categoryData['name']),
                    'description' => $categoryData['description'],
                    'status' => 'active',
                    'sort_order' => $categoryData['sort_order'] ?? 0,
                ]
            );

            // Create products for this category
            foreach ($categoryData['products'] as $productData) {
                $this->createProduct($tenant, $category, $productData);
            }
        }

        // Update tenant product count
        $productCount = Product::where('tenant_id', $tenant->id)->count();
        $tenant->update(['current_products' => $productCount]);
    }

    /**
     * Create individual product with variants and inventory
     */
    private function createProduct(Tenant $tenant, Category $category, array $productData): void
    {
        // Create main product
        $product = Product::firstOrCreate(
            [
                'tenant_id' => $tenant->id,
                'name' => $productData['name'],
            ],
            [
                'category_id' => $category->id,
                'slug' => Str::slug($productData['name']),
                'description' => $productData['description'],
                'short_description' => $productData['short_description'],
                'sku' => $productData['sku'],
                'barcode' => $productData['barcode'],
                'price' => $productData['price'],
                'cost_price' => $productData['cost_price'],
                'stock_quantity' => $productData['stock_quantity'],
                'min_stock_level' => $productData['min_stock_level'],
                'status' => 'active',
                'is_featured' => rand(0, 1),
                'weight' => $productData['weight'] ?? null,
                'dimensions' => $productData['dimensions'] ?? null,
                'meta_title' => $productData['name'],
                'meta_description' => $productData['short_description'],
                'tags' => $productData['tags'] ?? null,
            ]
        );

        // Create variants if specified
        if (isset($productData['variants'])) {
            foreach ($productData['variants'] as $variantData) {
                $this->createProductVariant($product, $variantData);
            }
        }

        // Create inventory for each branch
        $branches = BranchShop::where('tenant_id', $tenant->id)->get();
        foreach ($branches as $branch) {
            Inventory::firstOrCreate(
                [
                    'product_id' => $product->id,
                    'branch_shop_id' => $branch->id,
                ],
                [
                    'quantity' => rand(10, 200),
                    'reserved_quantity' => 0,
                    'reorder_level' => rand(5, 30),
                    'last_updated' => now(),
                ]
            );
        }
    }

    /**
     * Create product variant
     */
    private function createProductVariant(Product $product, array $variantData): void
    {
        ProductVariant::firstOrCreate(
            [
                'product_id' => $product->id,
                'sku' => $variantData['sku'],
            ],
            [
                'name' => $variantData['name'],
                'price' => $variantData['price'],
                'cost_price' => $variantData['cost_price'],
                'stock_quantity' => $variantData['stock_quantity'],
                'barcode' => $variantData['barcode'],
                'attributes' => json_encode($variantData['attributes']),
                'status' => 'active',
                'weight' => $variantData['weight'] ?? null,
                'dimensions' => $variantData['dimensions'] ?? null,
            ]
        );
    }

    /**
     * Get product data based on tenant business type
     */
    private function getProductDataByTenant(Tenant $tenant): array
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
     * Get TechMart products (Electronics & Technology) - 100+ products
     */
    private function getTechMartProducts(): array
    {
        return [
            'categories' => [
                [
                    'name' => 'Điện thoại & Tablet',
                    'description' => 'Điện thoại thông minh và máy tính bảng',
                    'sort_order' => 1,
                    'products' => $this->generateTechPhoneProducts()
                ],
                [
                    'name' => 'Laptop & Máy tính',
                    'description' => 'Laptop, PC và phụ kiện máy tính',
                    'sort_order' => 2,
                    'products' => $this->generateTechLaptopProducts()
                ],
                [
                    'name' => 'Phụ kiện công nghệ',
                    'description' => 'Tai nghe, sạc, ốp lưng và phụ kiện',
                    'sort_order' => 3,
                    'products' => $this->generateTechAccessoryProducts()
                ],
                [
                    'name' => 'Gaming & Esports',
                    'description' => 'Thiết bị gaming và esports',
                    'sort_order' => 4,
                    'products' => $this->generateTechGamingProducts()
                ],
                [
                    'name' => 'Smart Home',
                    'description' => 'Thiết bị nhà thông minh',
                    'sort_order' => 5,
                    'products' => $this->generateTechSmartHomeProducts()
                ]
            ]
        ];
    }

    /**
     * Generate tech phone products (30 products)
     */
    private function generateTechPhoneProducts(): array
    {
        $products = [];
        $brands = ['iPhone', 'Samsung Galaxy', 'Xiaomi', 'OPPO', 'Vivo', 'Realme'];
        $models = ['Pro Max', 'Ultra', 'Plus', 'Note', 'Lite', 'SE'];
        $storages = ['128GB', '256GB', '512GB', '1TB'];
        
        $counter = 1;
        foreach ($brands as $brand) {
            foreach ($models as $model) {
                foreach (array_slice($storages, 0, 2) as $storage) {
                    $name = "{$brand} {$model} {$storage}";
                    $basePrice = rand(5000000, 35000000);
                    
                    $products[] = [
                        'name' => $name,
                        'description' => "Điện thoại {$name} với công nghệ tiên tiến, camera chất lượng cao và hiệu năng mạnh mẽ",
                        'short_description' => "{$name} - Công nghệ tiên tiến",
                        'sku' => 'PHONE' . str_pad($counter, 3, '0', STR_PAD_LEFT),
                        'barcode' => '1' . str_pad($counter, 12, '0', STR_PAD_LEFT),
                        'price' => $basePrice,
                        'cost_price' => $basePrice * 0.7,
                        'stock_quantity' => rand(20, 100),
                        'min_stock_level' => rand(5, 15),
                        'weight' => rand(150, 250),
                        'tags' => 'smartphone,mobile,tech,' . strtolower($brand),
                        'variants' => $this->generatePhoneVariants($name, $basePrice, $counter)
                    ];
                    $counter++;
                    
                    if (count($products) >= 30) break 3;
                }
            }
        }
        
        return $products;
    }

    /**
     * Generate phone variants (colors)
     */
    private function generatePhoneVariants(string $baseName, int $basePrice, int $counter): array
    {
        $colors = ['Đen', 'Trắng', 'Xanh', 'Tím', 'Vàng'];
        $variants = [];
        
        foreach (array_slice($colors, 0, 3) as $index => $color) {
            $variants[] = [
                'name' => "{$baseName} - {$color}",
                'sku' => 'PHONE' . str_pad($counter, 3, '0', STR_PAD_LEFT) . '-' . strtoupper(substr($color, 0, 1)),
                'barcode' => '1' . str_pad($counter, 10, '0', STR_PAD_LEFT) . str_pad($index + 1, 2, '0', STR_PAD_LEFT),
                'price' => $basePrice + ($index * 500000),
                'cost_price' => ($basePrice + ($index * 500000)) * 0.7,
                'stock_quantity' => rand(10, 50),
                'attributes' => ['color' => $color],
                'weight' => rand(150, 250)
            ];
        }
        
        return $variants;
    }

    /**
     * Generate tech laptop products (25 products)
     */
    private function generateTechLaptopProducts(): array
    {
        $products = [];
        $brands = ['MacBook', 'Dell XPS', 'HP Pavilion', 'Asus ROG', 'Lenovo ThinkPad', 'Acer Aspire'];
        $specs = ['i5 8GB', 'i7 16GB', 'i9 32GB', 'M1 16GB', 'M2 32GB'];

        $counter = 1;
        foreach ($brands as $brand) {
            foreach ($specs as $spec) {
                $name = "{$brand} {$spec}";
                $basePrice = rand(15000000, 60000000);

                $products[] = [
                    'name' => $name,
                    'description' => "Laptop {$name} hiệu năng cao, phù hợp cho công việc và giải trí",
                    'short_description' => "{$name} - Hiệu năng cao",
                    'sku' => 'LAPTOP' . str_pad($counter, 3, '0', STR_PAD_LEFT),
                    'barcode' => '2' . str_pad($counter, 12, '0', STR_PAD_LEFT),
                    'price' => $basePrice,
                    'cost_price' => $basePrice * 0.75,
                    'stock_quantity' => rand(10, 50),
                    'min_stock_level' => rand(3, 10),
                    'weight' => rand(1200, 2500),
                    'tags' => 'laptop,computer,tech,' . strtolower(explode(' ', $brand)[0])
                ];
                $counter++;

                if (count($products) >= 25) break 2;
            }
        }

        return $products;
    }

    /**
     * Generate tech accessory products (30 products)
     */
    private function generateTechAccessoryProducts(): array
    {
        $products = [];
        $accessories = [
            ['AirPods Pro', 'Tai nghe không dây', 6000000, 100],
            ['Apple Watch', 'Đồng hồ thông minh', 10000000, 80],
            ['iPad Pro', 'Máy tính bảng', 25000000, 60],
            ['Magic Keyboard', 'Bàn phím không dây', 3000000, 120],
            ['Magic Mouse', 'Chuột không dây', 2000000, 150],
            ['USB-C Hub', 'Hub đa năng', 800000, 200],
            ['Wireless Charger', 'Sạc không dây', 1200000, 180],
            ['Power Bank', 'Pin dự phòng', 800000, 250],
            ['Phone Case', 'Ốp lưng điện thoại', 300000, 500],
            ['Screen Protector', 'Miếng dán màn hình', 150000, 800]
        ];

        $counter = 1;
        foreach ($accessories as $accessory) {
            for ($i = 1; $i <= 3; $i++) {
                $name = $accessory[0] . " Gen {$i}";
                $basePrice = $accessory[2] + ($i * 200000);

                $products[] = [
                    'name' => $name,
                    'description' => "{$accessory[1]} {$name} chất lượng cao, thiết kế hiện đại",
                    'short_description' => "{$name} - {$accessory[1]}",
                    'sku' => 'ACC' . str_pad($counter, 3, '0', STR_PAD_LEFT),
                    'barcode' => '3' . str_pad($counter, 12, '0', STR_PAD_LEFT),
                    'price' => $basePrice,
                    'cost_price' => $basePrice * 0.6,
                    'stock_quantity' => $accessory[3],
                    'min_stock_level' => rand(10, 30),
                    'weight' => rand(50, 500),
                    'tags' => 'accessory,tech,' . strtolower(str_replace(' ', '', $accessory[0]))
                ];
                $counter++;

                if (count($products) >= 30) break 2;
            }
        }

        return $products;
    }

    /**
     * Generate tech gaming products (20 products)
     */
    private function generateTechGamingProducts(): array
    {
        $products = [];
        $gamingItems = [
            ['Gaming Mouse', 'Chuột gaming', 1500000],
            ['Gaming Keyboard', 'Bàn phím gaming', 2500000],
            ['Gaming Headset', 'Tai nghe gaming', 3000000],
            ['Gaming Monitor', 'Màn hình gaming', 8000000],
            ['Gaming Chair', 'Ghế gaming', 5000000],
            ['Gaming Laptop', 'Laptop gaming', 25000000],
            ['Graphics Card', 'Card đồ họa', 15000000],
            ['Gaming Controller', 'Tay cầm gaming', 1200000],
            ['Gaming Mousepad', 'Lót chuột gaming', 500000],
            ['Gaming Webcam', 'Webcam gaming', 2000000]
        ];

        $counter = 1;
        foreach ($gamingItems as $item) {
            for ($i = 1; $i <= 2; $i++) {
                $name = $item[0] . " Pro {$i}";
                $basePrice = $item[2] + ($i * 500000);

                $products[] = [
                    'name' => $name,
                    'description' => "{$item[1]} {$name} chuyên nghiệp cho game thủ",
                    'short_description' => "{$name} - {$item[1]}",
                    'sku' => 'GAME' . str_pad($counter, 3, '0', STR_PAD_LEFT),
                    'barcode' => '4' . str_pad($counter, 12, '0', STR_PAD_LEFT),
                    'price' => $basePrice,
                    'cost_price' => $basePrice * 0.7,
                    'stock_quantity' => rand(15, 80),
                    'min_stock_level' => rand(5, 15),
                    'weight' => rand(200, 3000),
                    'tags' => 'gaming,esports,' . strtolower(str_replace(' ', '', $item[0]))
                ];
                $counter++;

                if (count($products) >= 20) break 2;
            }
        }

        return $products;
    }

    /**
     * Generate tech smart home products (15 products)
     */
    private function generateTechSmartHomeProducts(): array
    {
        $products = [];
        $smartItems = [
            ['Smart Speaker', 'Loa thông minh', 2000000],
            ['Smart Light', 'Đèn thông minh', 800000],
            ['Smart Camera', 'Camera thông minh', 3000000],
            ['Smart Lock', 'Khóa thông minh', 4000000],
            ['Smart Thermostat', 'Điều hòa thông minh', 6000000],
            ['Smart Doorbell', 'Chuông cửa thông minh', 2500000],
            ['Smart Switch', 'Công tắc thông minh', 500000],
            ['Smart Sensor', 'Cảm biến thông minh', 1000000]
        ];

        $counter = 1;
        foreach ($smartItems as $item) {
            $name = $item[0] . " 2024";

            $products[] = [
                'name' => $name,
                'description' => "{$item[1]} {$name} kết nối WiFi, điều khiển từ xa",
                'short_description' => "{$name} - {$item[1]}",
                'sku' => 'SMART' . str_pad($counter, 3, '0', STR_PAD_LEFT),
                'barcode' => '5' . str_pad($counter, 12, '0', STR_PAD_LEFT),
                'price' => $item[2],
                'cost_price' => $item[2] * 0.65,
                'stock_quantity' => rand(20, 100),
                'min_stock_level' => rand(5, 20),
                'weight' => rand(100, 1000),
                'tags' => 'smarthome,iot,' . strtolower(str_replace(' ', '', $item[0]))
            ];
            $counter++;

            if (count($products) >= 15) break;
        }

        return $products;
    }

    /**
     * Get Fashion products (100+ products)
     */
    private function getFashionProducts(): array
    {
        return [
            'categories' => [
                [
                    'name' => 'Thời trang nữ',
                    'description' => 'Quần áo, giày dép thời trang nữ',
                    'sort_order' => 1,
                    'products' => $this->generateFashionWomenProducts()
                ],
                [
                    'name' => 'Thời trang nam',
                    'description' => 'Quần áo, giày dép thời trang nam',
                    'sort_order' => 2,
                    'products' => $this->generateFashionMenProducts()
                ],
                [
                    'name' => 'Phụ kiện thời trang',
                    'description' => 'Túi xách, đồng hồ, trang sức',
                    'sort_order' => 3,
                    'products' => $this->generateFashionAccessoryProducts()
                ],
                [
                    'name' => 'Giày dép',
                    'description' => 'Giày thể thao, giày cao gót, sandal',
                    'sort_order' => 4,
                    'products' => $this->generateFashionShoeProducts()
                ]
            ]
        ];
    }

    /**
     * Generate fashion women products (40 products)
     */
    private function generateFashionWomenProducts(): array
    {
        $products = [];
        $items = [
            ['Váy maxi', 'Váy dài thời trang', 450000, 'dress'],
            ['Áo sơ mi', 'Áo sơ mi công sở', 320000, 'shirt'],
            ['Quần jeans', 'Quần jeans nữ', 380000, 'jeans'],
            ['Áo thun', 'Áo thun basic', 180000, 'tshirt'],
            ['Chân váy', 'Chân váy ngắn', 280000, 'skirt'],
            ['Áo khoác', 'Áo khoác blazer', 650000, 'jacket'],
            ['Đầm công sở', 'Đầm công sở thanh lịch', 520000, 'dress'],
            ['Quần tây', 'Quần tây nữ', 420000, 'pants']
        ];

        $colors = ['Đen', 'Trắng', 'Xanh navy', 'Hồng', 'Xám'];
        $sizes = ['S', 'M', 'L', 'XL'];

        $counter = 1;
        foreach ($items as $item) {
            foreach ($colors as $color) {
                $name = "{$item[0]} {$color}";

                $products[] = [
                    'name' => $name,
                    'description' => "{$item[1]} màu {$color}, chất liệu cao cấp, thiết kế hiện đại",
                    'short_description' => "{$name} - {$item[1]}",
                    'sku' => 'FW' . str_pad($counter, 3, '0', STR_PAD_LEFT),
                    'barcode' => '6' . str_pad($counter, 12, '0', STR_PAD_LEFT),
                    'price' => $item[2] + rand(-50000, 100000),
                    'cost_price' => ($item[2] + rand(-50000, 100000)) * 0.5,
                    'stock_quantity' => rand(30, 150),
                    'min_stock_level' => rand(10, 25),
                    'weight' => rand(200, 800),
                    'tags' => 'fashion,women,' . $item[3] . ',' . strtolower($color),
                    'variants' => $this->generateClothingVariants($name, $item[2], $sizes, $counter)
                ];
                $counter++;

                if (count($products) >= 40) break 2;
            }
        }

        return $products;
    }

    /**
     * Generate clothing variants (sizes)
     */
    private function generateClothingVariants(string $baseName, int $basePrice, array $sizes, int $counter): array
    {
        $variants = [];

        foreach ($sizes as $index => $size) {
            $variants[] = [
                'name' => "{$baseName} - Size {$size}",
                'sku' => 'FW' . str_pad($counter, 3, '0', STR_PAD_LEFT) . '-' . $size,
                'barcode' => '6' . str_pad($counter, 10, '0', STR_PAD_LEFT) . str_pad($index + 1, 2, '0', STR_PAD_LEFT),
                'price' => $basePrice,
                'cost_price' => $basePrice * 0.5,
                'stock_quantity' => rand(10, 40),
                'attributes' => ['size' => $size],
                'weight' => rand(200, 800)
            ];
        }

        return $variants;
    }

    /**
     * Generate fashion men products (30 products)
     */
    private function generateFashionMenProducts(): array
    {
        $products = [];
        $items = [
            ['Áo polo nam', 'Áo polo cao cấp', 380000, 'polo'],
            ['Quần kaki', 'Quần kaki nam', 420000, 'pants'],
            ['Áo sơ mi nam', 'Áo sơ mi công sở', 350000, 'shirt'],
            ['Quần jeans nam', 'Quần jeans nam', 450000, 'jeans'],
            ['Áo thun nam', 'Áo thun basic', 200000, 'tshirt'],
            ['Áo khoác nam', 'Áo khoác jacket', 750000, 'jacket']
        ];

        $colors = ['Đen', 'Trắng', 'Xanh navy', 'Xám', 'Nâu'];
        $sizes = ['M', 'L', 'XL', 'XXL'];

        $counter = 1;
        foreach ($items as $item) {
            foreach ($colors as $color) {
                $name = "{$item[0]} {$color}";

                $products[] = [
                    'name' => $name,
                    'description' => "{$item[1]} màu {$color}, phong cách lịch lãm",
                    'short_description' => "{$name} - {$item[1]}",
                    'sku' => 'FM' . str_pad($counter, 3, '0', STR_PAD_LEFT),
                    'barcode' => '7' . str_pad($counter, 12, '0', STR_PAD_LEFT),
                    'price' => $item[2] + rand(-50000, 100000),
                    'cost_price' => ($item[2] + rand(-50000, 100000)) * 0.5,
                    'stock_quantity' => rand(25, 120),
                    'min_stock_level' => rand(8, 20),
                    'weight' => rand(250, 900),
                    'tags' => 'fashion,men,' . $item[3] . ',' . strtolower($color),
                    'variants' => $this->generateClothingVariants($name, $item[2], $sizes, $counter)
                ];
                $counter++;

                if (count($products) >= 30) break 2;
            }
        }

        return $products;
    }

    /**
     * Display summary
     */
    private function displaySummary(): void
    {
        $this->command->info('📊 Product Data Summary:');
        $this->command->newLine();

        $tenants = Tenant::where('status', 'active')->get();
        
        foreach ($tenants as $tenant) {
            $productCount = Product::where('tenant_id', $tenant->id)->count();
            $categoryCount = Category::where('tenant_id', $tenant->id)->count();
            $variantCount = ProductVariant::whereHas('product', function($q) use ($tenant) {
                $q->where('tenant_id', $tenant->id);
            })->count();
            
            $this->command->line("🏢 <fg=cyan>{$tenant->name}</> ({$tenant->slug})");
            $this->command->line("   Products: <fg=green>{$productCount}</> sản phẩm");
            $this->command->line("   Categories: <fg=green>{$categoryCount}</> danh mục");
            $this->command->line("   Variants: <fg=green>{$variantCount}</> biến thể");
            $this->command->newLine();
        }

        $totalProducts = Product::count();
        $totalCategories = Category::count();
        $totalVariants = ProductVariant::count();
        
        $this->command->info("📈 Total Statistics:");
        $this->command->line("   Total Products: <fg=green>{$totalProducts}</>");
        $this->command->line("   Total Categories: <fg=green>{$totalCategories}</>");
        $this->command->line("   Total Variants: <fg=green>{$totalVariants}</>");
    }
}
