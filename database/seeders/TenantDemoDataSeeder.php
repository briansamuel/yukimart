<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tenant;
use App\Models\User;
use App\Models\TenantUser;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Warehouse;
use App\Models\Inventory;
use App\Models\BranchShop;
use App\Services\TenantContextService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TenantDemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🏢 Creating Tenant Demo Data...');

        // Create additional tenants
        $this->createTenants();
        
        // Create users for each tenant
        $this->createUsers();
        
        // Create demo data for each tenant
        $tenants = Tenant::all();
        foreach ($tenants as $tenant) {
            $this->command->info("📊 Creating demo data for tenant: {$tenant->name}");
            $this->createTenantData($tenant);
        }

        $this->command->info('✅ Tenant demo data created successfully!');
    }

    /**
     * Create additional tenants
     */
    private function createTenants(): void
    {
        $this->command->info('🏪 Creating additional tenants...');

        $tenants = [
            [
                'name' => 'TechMart Store',
                'slug' => 'techmart',
                'subdomain' => 'techmart',
                'email' => 'admin@techmart.local',
                'status' => 'active',
                'plan_type' => 'premium',
                'max_users' => 50,
                'max_products' => 5000,
                'max_branch_shops' => 10,
                'storage_limit' => 10737418240, // 10GB
                'api_rate_limit' => 5000,
            ],
            [
                'name' => 'Fashion Boutique',
                'slug' => 'fashion',
                'subdomain' => 'fashion',
                'email' => 'admin@fashion.local',
                'status' => 'active',
                'plan_type' => 'basic',
                'max_users' => 20,
                'max_products' => 1000,
                'max_branch_shops' => 5,
                'storage_limit' => 5368709120, // 5GB
                'api_rate_limit' => 2000,
            ],
            [
                'name' => 'Food & Beverage Co',
                'slug' => 'foodbev',
                'subdomain' => 'foodbev',
                'email' => 'admin@foodbev.local',
                'status' => 'active',
                'plan_type' => 'enterprise',
                'max_users' => 100,
                'max_products' => 15000,
                'max_branch_shops' => 25,
                'storage_limit' => 53687091200, // 50GB
                'api_rate_limit' => 15000,
            ]
        ];

        foreach ($tenants as $tenantData) {
            Tenant::firstOrCreate(
                ['slug' => $tenantData['slug']],
                $tenantData
            );
        }
    }

    /**
     * Create users for each tenant
     */
    private function createUsers(): void
    {
        $this->command->info('👥 Creating users for tenants...');

        $tenants = Tenant::all();
        
        foreach ($tenants as $tenant) {
            // Create admin user for each tenant
            $adminUser = User::firstOrCreate(
                ['email' => "admin@{$tenant->slug}.local"],
                [
                    'username' => "admin_{$tenant->slug}",
                    'email' => "admin@{$tenant->slug}.local",
                    'password' => Hash::make('123456'),
                    'full_name' => "Admin {$tenant->name}",
                    'address' => "123 Admin Street, {$tenant->name}",
                    'phone' => '0123456789',
                    'active_code' => 'verified',
                    'status' => 'active',
                    'email_verified_at' => now(),
                ]
            );

            // Create tenant-user relationship
            TenantUser::firstOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'user_id' => $adminUser->id,
                ],
                [
                    'role' => 'admin',
                    'is_active' => true,
                    'invitation_status' => 'accepted',
                    'joined_at' => now(),
                ]
            );

            // Create manager user
            $managerUser = User::firstOrCreate(
                ['email' => "manager@{$tenant->slug}.local"],
                [
                    'username' => "manager_{$tenant->slug}",
                    'email' => "manager@{$tenant->slug}.local",
                    'password' => Hash::make('123456'),
                    'full_name' => "Manager {$tenant->name}",
                    'address' => "456 Manager Avenue, {$tenant->name}",
                    'phone' => '0987654321',
                    'active_code' => 'verified',
                    'status' => 'active',
                    'email_verified_at' => now(),
                ]
            );

            TenantUser::firstOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'user_id' => $managerUser->id,
                ],
                [
                    'role' => 'manager',
                    'is_active' => true,
                    'invitation_status' => 'accepted',
                    'joined_at' => now(),
                ]
            );
        }
    }

    /**
     * Create demo data for a specific tenant
     */
    private function createTenantData(Tenant $tenant): void
    {
        // Set tenant context
        $tenantService = app(TenantContextService::class);
        $tenantService->setCurrentTenant($tenant);

        // Create categories
        $categories = $this->createCategories($tenant);
        
        // Create customers
        $customers = $this->createCustomers($tenant);
        
        // Create warehouses
        $warehouses = $this->createWarehouses($tenant);
        
        // Create products
        $products = $this->createProducts($tenant, $categories, $warehouses);
        
        // Create orders
        $orders = $this->createOrders($tenant, $customers, $products);
        
        // Create invoices
        $this->createInvoices($tenant, $customers, $products);

        // Update tenant statistics
        $this->updateTenantStatistics($tenant);
    }

    /**
     * Create categories for tenant
     */
    private function createCategories(Tenant $tenant): array
    {
        $categoryData = [
            'Electronics' => ['Laptops', 'Smartphones', 'Tablets', 'Accessories'],
            'Fashion' => ['Men Clothing', 'Women Clothing', 'Shoes', 'Bags'],
            'Food & Beverage' => ['Snacks', 'Beverages', 'Fresh Food', 'Frozen'],
            'Home & Garden' => ['Furniture', 'Decoration', 'Kitchen', 'Garden Tools'],
            'Sports' => ['Fitness', 'Outdoor', 'Team Sports', 'Water Sports']
        ];

        $categories = [];
        
        foreach ($categoryData as $parentName => $children) {
            $parent = Category::create([
                'tenant_id' => $tenant->id,
                'category_name' => $parentName,
                'category_slug' => Str::slug($parentName),
                'category_parent' => 0,
                'category_description' => "Category for {$parentName} products",
                'category_type' => 'product',
                'language' => 'vi',
            ]);
            
            $categories[] = $parent;
            
            foreach ($children as $childName) {
                $child = Category::create([
                    'tenant_id' => $tenant->id,
                    'category_name' => $childName,
                    'category_slug' => Str::slug($childName),
                    'category_parent' => $parent->id,
                    'category_description' => "Subcategory for {$childName}",
                    'category_type' => 'product',
                    'language' => 'vi',
                ]);
                
                $categories[] = $child;
            }
        }

        return $categories;
    }

    /**
     * Create customers for tenant
     */
    private function createCustomers(Tenant $tenant): array
    {
        $customers = [];
        
        for ($i = 1; $i <= 20; $i++) {
            $customers[] = Customer::create([
                'tenant_id' => $tenant->id,
                'name' => "Customer {$i} - {$tenant->name}",
                'email' => "customer{$i}@{$tenant->slug}.local",
                'phone' => '09' . str_pad($i, 8, '0', STR_PAD_LEFT),
                'address' => "Address {$i}, {$tenant->name} City",
                'birthday' => now()->subYears(rand(20, 60))->subDays(rand(1, 365)),
                'status' => 'active',
                'points' => rand(0, 1000),
                'customer_code' => 'CUST' . str_pad($i, 4, '0', STR_PAD_LEFT),
            ]);
        }

        return $customers;
    }

    /**
     * Create warehouses for tenant
     */
    private function createWarehouses(Tenant $tenant): array
    {
        $warehouses = [];
        
        // Main warehouse
        $warehouses[] = Warehouse::firstOrCreate(
            [
                'tenant_id' => $tenant->id,
                'warehouse_name' => 'Main Warehouse'
            ],
            [
                'warehouse_address' => "Main Warehouse Address - {$tenant->name}",
                'warehouse_phone' => '0123456789',
                'warehouse_email' => "warehouse@{$tenant->slug}.local",
                'is_active' => true,
            ]
        );

        // Branch warehouse
        $warehouses[] = Warehouse::create([
            'tenant_id' => $tenant->id,
            'warehouse_name' => "Branch Warehouse - {$tenant->name}",
            'warehouse_address' => "Branch Address - {$tenant->name}",
            'warehouse_phone' => '0987654321',
            'warehouse_email' => "branch@{$tenant->slug}.local",
            'is_active' => true,
        ]);

        return $warehouses;
    }

    /**
     * Create products for tenant
     */
    private function createProducts(Tenant $tenant, array $categories, array $warehouses): array
    {
        $products = [];
        
        for ($i = 1; $i <= 50; $i++) {
            $category = $categories[array_rand($categories)];
            $warehouse = $warehouses[array_rand($warehouses)];
            
            $product = Product::create([
                'tenant_id' => $tenant->id,
                'product_name' => "Product {$i} - {$tenant->name}",
                'product_slug' => Str::slug("product-{$i}-{$tenant->slug}"),
                'sku' => strtoupper($tenant->slug) . '-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'barcode' => '123456789' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'category_id' => $category->id,
                'product_description' => "Description for Product {$i} in {$tenant->name}",
                'product_content' => "Content for Product {$i}",
                'cost_price' => rand(5000, 300000),
                'sale_price' => rand(8000, 400000),
                'product_status' => 'publish',
                'product_type' => 'simple',
                'product_feature' => rand(0, 1),
                'weight' => rand(100, 5000), // grams
                'points' => rand(0, 100),
                'reorder_point' => 5,
                'language' => 'vi',
                'created_by_user' => 1,
                'updated_by_user' => 1,
            ]);
            
            // Create inventory record
            Inventory::create([
                'tenant_id' => $tenant->id,
                'product_id' => $product->id,
                'warehouse_id' => $warehouse->id,
                'quantity' => $product->stock_quantity,
                'reserved_quantity' => 0,
                'cost_price' => $product->cost_price,
                'last_updated' => now(),
            ]);
            
            $products[] = $product;
        }

        return $products;
    }

    /**
     * Create orders for tenant
     */
    private function createOrders(Tenant $tenant, array $customers, array $products): array
    {
        $orders = [];
        
        for ($i = 1; $i <= 30; $i++) {
            $customer = $customers[array_rand($customers)];
            $orderDate = now()->subDays(rand(1, 30));
            
            $order = Order::create([
                'tenant_id' => $tenant->id,
                'order_number' => 'ORD-' . strtoupper($tenant->slug) . '-' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'customer_id' => $customer->id,
                'order_date' => $orderDate,
                'status' => ['draft', 'processing', 'completed', 'cancelled'][rand(0, 3)],
                'payment_status' => ['pending', 'paid', 'failed'][rand(0, 2)],
                'subtotal' => 0, // Will be calculated
                'tax_amount' => 0,
                'discount_amount' => rand(0, 50000),
                'shipping_amount' => rand(20000, 50000),
                'total_amount' => 0, // Will be calculated
                'final_amount' => 0, // Will be calculated
                'notes' => "Order notes for order {$i}",
                'shipping_address' => $customer->address,
                'billing_address' => $customer->address,
            ]);
            
            // Create order items
            $itemCount = rand(1, 5);
            $subtotal = 0;
            
            for ($j = 1; $j <= $itemCount; $j++) {
                $product = $products[array_rand($products)];
                $quantity = rand(1, 3);
                $price = $product->sale_price ?: $product->price;
                $itemTotal = $price * $quantity;
                $subtotal += $itemTotal;
                
                OrderItem::create([
                    'tenant_id' => $tenant->id,
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $price,
                    'total_price' => $itemTotal,
                    'product_name' => $product->product_name,
                    'product_sku' => $product->product_sku,
                ]);
            }
            
            // Update order totals
            $order->update([
                'subtotal' => $subtotal,
                'tax_amount' => $subtotal * 0.1, // 10% tax
                'total_amount' => $subtotal + ($subtotal * 0.1) + $order->shipping_amount - $order->discount_amount,
                'final_amount' => $subtotal + ($subtotal * 0.1) + $order->shipping_amount - $order->discount_amount,
            ]);
            
            $orders[] = $order;
        }

        return $orders;
    }

    /**
     * Create invoices for tenant
     */
    private function createInvoices(Tenant $tenant, array $customers, array $products): void
    {
        for ($i = 1; $i <= 20; $i++) {
            $customer = $customers[array_rand($customers)];
            $invoiceDate = now()->subDays(rand(1, 20));
            
            $invoice = Invoice::create([
                'tenant_id' => $tenant->id,
                'invoice_number' => 'INV-' . strtoupper($tenant->slug) . '-' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'customer_id' => $customer->id,
                'invoice_date' => $invoiceDate,
                'due_date' => $invoiceDate->copy()->addDays(30),
                'status' => ['draft', 'sent', 'paid', 'overdue'][rand(0, 3)],
                'subtotal' => 0, // Will be calculated
                'tax_amount' => 0,
                'discount_amount' => rand(0, 30000),
                'total_amount' => 0, // Will be calculated
                'paid_amount' => 0,
                'balance_amount' => 0,
                'notes' => "Invoice notes for invoice {$i}",
                'terms_conditions' => 'Standard payment terms apply',
            ]);
            
            // Create invoice items
            $itemCount = rand(1, 4);
            $subtotal = 0;
            
            for ($j = 1; $j <= $itemCount; $j++) {
                $product = $products[array_rand($products)];
                $quantity = rand(1, 2);
                $price = $product->sale_price ?: $product->price;
                $itemTotal = $price * $quantity;
                $subtotal += $itemTotal;
                
                InvoiceItem::create([
                    'tenant_id' => $tenant->id,
                    'invoice_id' => $invoice->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $price,
                    'total_price' => $itemTotal,
                    'description' => $product->product_name,
                ]);
            }
            
            // Update invoice totals
            $totalAmount = $subtotal + ($subtotal * 0.1) - $invoice->discount_amount;
            $paidAmount = $invoice->status === 'paid' ? $totalAmount : rand(0, $totalAmount);
            
            $invoice->update([
                'subtotal' => $subtotal,
                'tax_amount' => $subtotal * 0.1,
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'balance_amount' => $totalAmount - $paidAmount,
            ]);
        }
    }

    /**
     * Update tenant statistics
     */
    private function updateTenantStatistics(Tenant $tenant): void
    {
        $userCount = TenantUser::where('tenant_id', $tenant->id)->where('is_active', true)->count();
        $productCount = Product::where('tenant_id', $tenant->id)->count();
        $customerCount = Customer::where('tenant_id', $tenant->id)->count();
        $orderCount = Order::where('tenant_id', $tenant->id)->count();
        
        $tenant->update([
            'current_users' => $userCount,
            'current_products' => $productCount,
            'current_customers' => $customerCount,
            'current_orders' => $orderCount,
            'current_storage_used' => rand(1000000, 5000000), // Random storage usage
        ]);
    }
}
