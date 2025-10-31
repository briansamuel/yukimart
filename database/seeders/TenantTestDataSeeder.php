<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tenant;
use App\Models\User;
use App\Models\TenantUser;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\BranchShop;
use Illuminate\Support\Facades\Hash;

class TenantTestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 Starting Tenant Test Data Seeding...');

        // Create test tenants
        $tenants = $this->createTestTenants();
        
        foreach ($tenants as $tenant) {
            $this->command->info("📦 Seeding data for {$tenant->name}...");
            
            // Create users for this tenant
            $users = $this->createTenantUsers($tenant);
            
            // Create business data
            $this->createBusinessData($tenant);
            
            $this->command->info("✅ Completed seeding for {$tenant->name}");
        }

        $this->command->info('🎉 Tenant test data seeding completed!');
    }

    /**
     * Create test tenants
     */
    private function createTestTenants(): array
    {
        $tenants = [];

        // TechMart Store
        $tenants[] = Tenant::firstOrCreate(
            ['subdomain' => 'techmart'],
            [
                'name' => 'TechMart Store',
                'slug' => 'techmart-store',
                'description' => 'Leading electronics and technology retailer',
                'contact_email' => 'contact@techmart.local',
                'contact_phone' => '+84 123 456 789',
                'address' => '123 Tech Street, District 1',
                'city' => 'Ho Chi Minh City',
                'state' => 'Ho Chi Minh',
                'country' => 'Vietnam',
                'postal_code' => '70000',
                'website' => 'https://techmart.local',
                'status' => 'active',
                'settings' => json_encode([
                    'timezone' => 'Asia/Ho_Chi_Minh',
                    'currency' => 'VND',
                    'language' => 'vi',
                    'date_format' => 'd/m/Y',
                    'time_format' => 'H:i'
                ]),
            ]
        );

        // Fashion Boutique
        $tenants[] = Tenant::firstOrCreate(
            ['subdomain' => 'fashion'],
            [
                'name' => 'Fashion Boutique',
                'slug' => 'fashion-boutique',
                'description' => 'Premium fashion and clothing retailer',
                'contact_email' => 'contact@fashion.local',
                'contact_phone' => '+84 987 654 321',
                'address' => '456 Fashion Avenue, District 3',
                'city' => 'Ho Chi Minh City',
                'state' => 'Ho Chi Minh',
                'country' => 'Vietnam',
                'postal_code' => '70000',
                'website' => 'https://fashion.local',
                'status' => 'active',
                'settings' => json_encode([
                    'timezone' => 'Asia/Ho_Chi_Minh',
                    'currency' => 'VND',
                    'language' => 'vi',
                    'date_format' => 'd/m/Y',
                    'time_format' => 'H:i'
                ]),
            ]
        );

        return $tenants;
    }

    /**
     * Create users for tenant
     */
    private function createTenantUsers(Tenant $tenant): array
    {
        $users = [];
        $tenantPrefix = strtolower($tenant->subdomain);

        // Create different role users
        $roles = [
            'owner' => ['Owner', 'owner'],
            'admin' => ['Admin', 'admin'],
            'manager' => ['Manager', 'manager'],
            'staff' => ['Staff', 'staff']
        ];

        foreach ($roles as $role => [$name, $username]) {
            $email = "{$username}@{$tenantPrefix}.local";
            
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => "{$name} {$tenant->name}",
                    'email' => $email,
                    'email_verified_at' => now(),
                    'password' => Hash::make('123456'),
                    'phone' => '+84 ' . rand(100000000, 999999999),
                    'address' => fake()->address(),
                    'avatar' => null,
                    'status' => 'active',
                    'last_login_at' => now(),
                ]
            );

            // Create tenant-user relationship
            TenantUser::firstOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'user_id' => $user->id,
                ],
                [
                    'role' => $role,
                    'is_active' => true,
                    'joined_at' => now(),
                    'permissions' => json_encode($this->getRolePermissions($role)),
                    'settings' => json_encode([
                        'notifications' => true,
                        'email_alerts' => true,
                        'dashboard_layout' => 'default',
                        'language' => 'vi',
                    ]),
                ]
            );

            $users[] = $user;
        }

        return $users;
    }

    /**
     * Get permissions for role
     */
    private function getRolePermissions(string $role): array
    {
        return match($role) {
            'owner' => ['*'],
            'admin' => [
                'products.*', 'orders.*', 'customers.*', 'invoices.*', 
                'returns.*', 'payments.*', 'inventory.*', 'reports.*', 'settings.*'
            ],
            'manager' => [
                'products.view', 'products.create', 'products.edit',
                'orders.*', 'customers.*', 'invoices.*', 'returns.*', 'payments.*',
                'inventory.view', 'reports.view'
            ],
            'staff' => [
                'products.view', 'orders.create', 'orders.edit', 'orders.view',
                'customers.view', 'customers.create', 'customers.edit',
                'invoices.create', 'invoices.view'
            ],
            default => ['products.view', 'orders.view', 'customers.view', 'invoices.view']
        };
    }

    /**
     * Create business data for tenant
     */
    private function createBusinessData(Tenant $tenant): void
    {
        // Set tenant context
        config(['app.current_tenant_id' => $tenant->id]);

        // Create branch shops
        $branchShop = BranchShop::firstOrCreate(
            [
                'tenant_id' => $tenant->id,
                'name' => 'Main Store'
            ],
            [
                'address' => $tenant->address,
                'phone' => $tenant->contact_phone,
                'email' => $tenant->contact_email,
                'manager_name' => 'Store Manager',
                'is_active' => true,
            ]
        );

        // Create categories based on tenant type
        $categories = $this->createTenantCategories($tenant);
        
        // Create brands
        $brands = $this->createTenantBrands($tenant);
        
        // Create products
        $products = $this->createTenantProducts($tenant, $categories, $brands);
        
        // Create customers
        $customers = $this->createTenantCustomers($tenant);
        
        // Create orders
        $this->createTenantOrders($tenant, $customers, $products, $branchShop);
    }

    /**
     * Create categories for tenant
     */
    private function createTenantCategories(Tenant $tenant): array
    {
        $categoriesData = match($tenant->subdomain) {
            'techmart' => [
                'Smartphones', 'Laptops', 'Tablets', 'Accessories', 'Gaming',
                'Audio', 'Cameras', 'Smart Home', 'Wearables', 'Components'
            ],
            'fashion' => [
                'Men\'s Clothing', 'Women\'s Clothing', 'Shoes', 'Bags', 'Accessories',
                'Jewelry', 'Watches', 'Sunglasses', 'Perfumes', 'Underwear'
            ],
            default => [
                'Electronics', 'Clothing', 'Home & Garden', 'Sports', 'Books',
                'Toys', 'Health', 'Beauty', 'Automotive', 'Food'
            ]
        };

        $categories = [];
        foreach ($categoriesData as $index => $categoryName) {
            $categories[] = Category::firstOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'category_name' => $categoryName
                ],
                [
                    'category_slug' => \Str::slug($categoryName),
                    'category_description' => "Premium {$categoryName} collection",
                    'category_image' => null,
                    'category_status' => 'active',
                    'sort_order' => $index + 1,
                ]
            );
        }

        return $categories;
    }

    /**
     * Create brands for tenant
     */
    private function createTenantBrands(Tenant $tenant): array
    {
        $brandsData = match($tenant->subdomain) {
            'techmart' => [
                'Apple', 'Samsung', 'Sony', 'LG', 'Asus', 'Dell', 'HP', 'Xiaomi', 'Huawei', 'Canon'
            ],
            'fashion' => [
                'Nike', 'Adidas', 'Zara', 'H&M', 'Uniqlo', 'Gucci', 'Prada', 'Louis Vuitton', 'Chanel', 'Dior'
            ],
            default => [
                'Generic Brand', 'Premium', 'Standard', 'Economy', 'Luxury'
            ]
        };

        $brands = [];
        foreach ($brandsData as $brandName) {
            $brands[] = Brand::firstOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'brand_name' => $brandName
                ],
                [
                    'brand_slug' => \Str::slug($brandName),
                    'brand_description' => "Official {$brandName} products",
                    'brand_image' => null,
                    'brand_status' => 'active',
                ]
            );
        }

        return $brands;
    }

    /**
     * Create products for tenant
     */
    private function createTenantProducts(Tenant $tenant, array $categories, array $brands): array
    {
        $products = [];
        $productCount = 50; // Create 50 products per tenant

        for ($i = 1; $i <= $productCount; $i++) {
            $category = fake()->randomElement($categories);
            $brand = fake()->randomElement($brands);
            
            $productName = $this->generateProductName($tenant->subdomain, $category->category_name, $i);
            $sku = strtoupper($tenant->subdomain) . '-' . str_pad($i, 4, '0', STR_PAD_LEFT);
            
            $costPrice = fake()->numberBetween(100000, 5000000);
            $salePrice = $costPrice * fake()->randomFloat(2, 1.2, 2.5);

            $products[] = Product::firstOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'sku' => $sku
                ],
                [
                    'product_name' => $productName,
                    'product_slug' => \Str::slug($productName),
                    'barcode' => fake()->ean13(),
                    'category_id' => $category->id,
                    'brand_id' => $brand->id,
                    'product_description' => fake()->paragraph(),
                    'product_content' => fake()->paragraphs(3, true),
                    'cost_price' => $costPrice,
                    'sale_price' => $salePrice,
                    'min_price' => $costPrice * 1.1,
                    'max_price' => $salePrice * 1.2,
                    'weight' => fake()->randomFloat(2, 0.1, 10),
                    'product_status' => fake()->randomElement(['publish', 'draft']),
                    'product_feature' => fake()->boolean(20),
                    'product_type' => 'simple',
                    'created_by' => 1,
                ]
            );
        }

        return $products;
    }

    /**
     * Generate product name based on tenant type
     */
    private function generateProductName(string $tenantType, string $category, int $index): string
    {
        return match($tenantType) {
            'techmart' => fake()->randomElement([
                'iPhone 15 Pro Max', 'Samsung Galaxy S24', 'MacBook Pro M3', 'Dell XPS 13',
                'Sony WH-1000XM5', 'iPad Air', 'Apple Watch Series 9', 'AirPods Pro',
                'Canon EOS R5', 'Nintendo Switch OLED'
            ]) . " #{$index}",
            'fashion' => fake()->randomElement([
                'Premium Cotton T-Shirt', 'Designer Jeans', 'Leather Handbag', 'Running Shoes',
                'Silk Dress', 'Wool Coat', 'Sneakers', 'Watch', 'Sunglasses', 'Perfume'
            ]) . " #{$index}",
            default => "{$category} Product #{$index}"
        };
    }

    /**
     * Create customers for tenant
     */
    private function createTenantCustomers(Tenant $tenant): array
    {
        $customers = [];
        $customerCount = 30;

        for ($i = 1; $i <= $customerCount; $i++) {
            $customers[] = Customer::firstOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'customer_phone' => fake()->phoneNumber()
                ],
                [
                    'customer_name' => fake()->name(),
                    'customer_email' => fake()->email(),
                    'customer_address' => fake()->address(),
                    'customer_city' => fake()->city(),
                    'customer_state' => fake()->state(),
                    'customer_country' => 'Vietnam',
                    'customer_postal_code' => fake()->postcode(),
                    'customer_birthday' => fake()->date(),
                    'customer_gender' => fake()->randomElement(['male', 'female', 'other']),
                    'customer_status' => 'active',
                    'loyalty_points' => fake()->numberBetween(0, 1000),
                    'total_spent' => fake()->numberBetween(0, 10000000),
                    'last_order_date' => fake()->dateTimeBetween('-6 months', 'now'),
                ]
            );
        }

        return $customers;
    }

    /**
     * Create orders for tenant
     */
    private function createTenantOrders(Tenant $tenant, array $customers, array $products, BranchShop $branchShop): void
    {
        $orderCount = 20;

        for ($i = 1; $i <= $orderCount; $i++) {
            $customer = fake()->randomElement($customers);
            $orderDate = fake()->dateTimeBetween('-3 months', 'now');
            
            $order = Order::create([
                'tenant_id' => $tenant->id,
                'order_code' => 'ORD' . date('Ymd', $orderDate->getTimestamp()) . str_pad($i, 4, '0', STR_PAD_LEFT),
                'customer_id' => $customer->id,
                'customer_name' => $customer->customer_name,
                'customer_phone' => $customer->customer_phone,
                'customer_email' => $customer->customer_email,
                'branch_shop_id' => $branchShop->id,
                'order_date' => $orderDate,
                'delivery_date' => fake()->dateTimeBetween($orderDate, '+1 week'),
                'delivery_address' => $customer->customer_address,
                'payment_method' => fake()->randomElement(['cash', 'card', 'transfer', 'cod']),
                'payment_status' => fake()->randomElement(['pending', 'paid', 'partial']),
                'order_status' => fake()->randomElement(['pending', 'processing', 'shipped', 'delivered']),
                'subtotal' => 0,
                'discount_amount' => 0,
                'tax_amount' => 0,
                'total_amount' => 0,
                'notes' => fake()->sentence(),
                'created_by' => 1,
                'created_at' => $orderDate,
                'updated_at' => $orderDate,
            ]);

            // Create order items
            $itemCount = fake()->numberBetween(1, 5);
            $subtotal = 0;

            for ($j = 1; $j <= $itemCount; $j++) {
                $product = fake()->randomElement($products);
                $quantity = fake()->numberBetween(1, 3);
                $unitPrice = $product->sale_price;
                $totalAmount = $quantity * $unitPrice;
                $subtotal += $totalAmount;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'discount_amount' => 0,
                    'tax_amount' => 0,
                    'total_amount' => $totalAmount,
                ]);
            }

            // Update order totals
            $order->update([
                'subtotal' => $subtotal,
                'total_amount' => $subtotal,
            ]);
        }
    }
}
