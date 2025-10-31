<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tenant;
use App\Models\User;
use App\Models\TenantUser;
use App\Models\Role;
use App\Models\Permission;
use App\Models\BranchShop;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Inventory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ComprehensiveTenantDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🏢 Creating Comprehensive Tenant Data...');

        // Get all active tenants
        $tenants = Tenant::where('status', 'active')->get();

        foreach ($tenants as $tenant) {
            $this->command->info("🏪 Setting up data for {$tenant->name}...");

            // Create roles for this tenant
            $this->createRolesForTenant($tenant);

            // Create users with roles
            $this->createTenantUsers($tenant);

            // Create branch shops
            $this->createBranchShops($tenant);

            // Create categories and products
            $this->createProductsForTenant($tenant);
        }

        $this->command->info('✅ Comprehensive tenant data created successfully!');
        $this->displaySummary();
    }

    /**
     * Create roles system for tenant
     */
    private function createRolesForTenant(Tenant $tenant): void
    {
        $this->command->info("👥 Creating roles for {$tenant->name}...");

        $roles = [
            [
                'name' => 'owner',
                'display_name' => 'Chủ sở hữu',
                'description' => 'Quyền cao nhất, quản lý toàn bộ hệ thống',
                'permissions' => ['*'] // All permissions
            ],
            [
                'name' => 'branch_manager',
                'display_name' => 'Quản lý chi nhánh',
                'description' => 'Quản lý chi nhánh, nhân viên và báo cáo',
                'permissions' => [
                    'view_dashboard', 'manage_branch', 'manage_staff', 
                    'view_reports', 'manage_inventory', 'manage_orders'
                ]
            ],
            [
                'name' => 'cashier',
                'display_name' => 'Nhân viên thu ngân',
                'description' => 'Xử lý đơn hàng, thanh toán và khách hàng',
                'permissions' => [
                    'view_dashboard', 'create_orders', 'process_payments',
                    'manage_customers', 'view_products'
                ]
            ],
            [
                'name' => 'warehouse_staff',
                'display_name' => 'Nhân viên kho',
                'description' => 'Quản lý kho hàng, nhập xuất tồn',
                'permissions' => [
                    'view_dashboard', 'manage_inventory', 'view_products',
                    'manage_stock', 'view_reports'
                ]
            ],
            [
                'name' => 'sales_staff',
                'display_name' => 'Thu Ngân',
                'description' => 'Bán hàng và hỗ trợ khách hàng',
                'permissions' => [
                    'view_dashboard', 'create_orders', 'view_products',
                    'manage_customers', 'view_reports'
                ]
            ]
        ];

        foreach ($roles as $roleData) {
            $role = Role::firstOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'name' => $roleData['name']
                ],
                [
                    'display_name' => $roleData['display_name'],
                    'description' => $roleData['description'],
                ]
            );

            // Create permissions for this tenant
            foreach ($roleData['permissions'] as $permissionName) {
                if ($permissionName === '*') continue;

                // Extract module and action from permission name
                $parts = explode('_', $permissionName, 2);
                $action = $parts[0] ?? 'view';
                $module = $parts[1] ?? 'general';

                Permission::firstOrCreate(
                    [
                        'tenant_id' => $tenant->id,
                        'name' => $permissionName
                    ],
                    [
                        'display_name' => ucwords(str_replace('_', ' ', $permissionName)),
                        'module' => $module,
                        'action' => $action,
                        'description' => "Permission to {$action} {$module}",
                    ]
                );
            }
        }
    }

    /**
     * Create users for tenant with different roles
     */
    private function createTenantUsers(Tenant $tenant): void
    {
        $tenantSlug = $tenant->slug;
        
        $users = [
            [
                'role' => 'owner',
                'role_name' => 'owner', // For roles table
                'username' => "owner_{$tenantSlug}",
                'email' => "owner@{$tenantSlug}.local",
                'full_name' => "Owner {$tenant->name}",
                'phone' => '0901000001',
            ],
            [
                'role' => 'admin',
                'role_name' => 'branch_manager', // For roles table
                'username' => "admin_{$tenantSlug}",
                'email' => "admin@{$tenantSlug}.local",
                'full_name' => "Admin {$tenant->name}",
                'phone' => '0901000002',
            ],
            [
                'role' => 'manager',
                'role_name' => 'branch_manager', // For roles table
                'username' => "manager_{$tenantSlug}",
                'email' => "manager@{$tenantSlug}.local",
                'full_name' => "Manager {$tenant->name}",
                'phone' => '0901000003',
            ],
            [
                'role' => 'staff',
                'role_name' => 'cashier', // For roles table
                'username' => "staff_{$tenantSlug}",
                'email' => "staff@{$tenantSlug}.local",
                'full_name' => "Staff {$tenant->name}",
                'phone' => '0901000004',
            ],
            [
                'role' => 'viewer',
                'role_name' => 'sales_staff', // For roles table
                'username' => "viewer_{$tenantSlug}",
                'email' => "viewer@{$tenantSlug}.local",
                'full_name' => "Viewer {$tenant->name}",
                'phone' => '0901000005',
            ]
        ];

        foreach ($users as $userData) {
            $user = User::firstOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'email' => $userData['email']
                ],
                [
                    'username' => $userData['username'],
                    'email' => $userData['email'],
                    'password' => Hash::make('123456'),
                    'full_name' => $userData['full_name'],
                    'address' => "123 {$tenant->name} Street, City",
                    'phone' => $userData['phone'],
                    'active_code' => 'verified',
                    'status' => 'active',
                    'email_verified_at' => now(),
                ]
            );

            // Create tenant-user relationship
            TenantUser::firstOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'user_id' => $user->id,
                ],
                [
                    'role' => $userData['role'],
                    'is_active' => true,
                    'invitation_status' => 'accepted',
                    'joined_at' => now(),
                ]
            );

            // Assign role to user (find role for this tenant)
            $role = Role::where('tenant_id', $tenant->id)
                       ->where('name', $userData['role_name'])
                       ->first();
            if ($role && !$user->roles()->where('role_id', $role->id)->exists()) {
                $user->roles()->attach($role->id);
            }
        }

        // Update tenant user count
        $userCount = TenantUser::where('tenant_id', $tenant->id)->where('is_active', true)->count();
        $tenant->update(['current_users' => $userCount]);
    }

    /**
     * Create branch shops for tenant
     */
    private function createBranchShops(Tenant $tenant): void
    {
        $tenantSlug = $tenant->slug;
        $branchCount = rand(2, 4);
        
        for ($i = 1; $i <= $branchCount; $i++) {
            BranchShop::firstOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'name' => "{$tenant->name} - Chi nhánh {$i}",
                ],
                [
                    'code' => strtoupper($tenantSlug) . sprintf('%02d', $i),
                    'address' => "Địa chỉ chi nhánh {$i}, Quận {$i}, TP.HCM",
                    'phone' => '028' . rand(1000000, 9999999),
                    'email' => "branch{$i}@{$tenantSlug}.local",
                    'manager_name' => "Quản lý chi nhánh {$i}",
                    'status' => 'active',
                    'opening_hours' => '08:00-22:00',
                    'description' => "Chi nhánh {$i} của {$tenant->name}",
                ]
            );
        }

        // Update tenant branch count
        $branchCount = BranchShop::where('tenant_id', $tenant->id)->count();
        $tenant->update(['current_branch_shops' => $branchCount]);
    }

    /**
     * Create products for tenant based on their business type
     */
    private function createProductsForTenant(Tenant $tenant): void
    {
        $productData = $this->getProductDataByTenant($tenant);
        
        foreach ($productData['categories'] as $categoryData) {
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
                    ]
                );

                // Create inventory for each branch
                $branches = BranchShop::where('tenant_id', $tenant->id)->get();
                foreach ($branches as $branch) {
                    Inventory::firstOrCreate(
                        [
                            'product_id' => $product->id,
                            'branch_shop_id' => $branch->id,
                        ],
                        [
                            'quantity' => rand(10, 100),
                            'reserved_quantity' => 0,
                            'reorder_level' => rand(5, 20),
                            'last_updated' => now(),
                        ]
                    );
                }
            }
        }

        // Update tenant product count
        $productCount = Product::where('tenant_id', $tenant->id)->count();
        $tenant->update(['current_products' => $productCount]);
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
     * Get TechMart products (Electronics & Technology)
     */
    private function getTechMartProducts(): array
    {
        return [
            'categories' => [
                [
                    'name' => 'Điện thoại & Tablet',
                    'description' => 'Điện thoại thông minh và máy tính bảng',
                    'sort_order' => 1,
                    'products' => [
                        [
                            'name' => 'iPhone 15 Pro Max 256GB',
                            'description' => 'iPhone 15 Pro Max với chip A17 Pro, camera 48MP',
                            'short_description' => 'iPhone 15 Pro Max 256GB - Titanium Natural',
                            'sku' => 'IP15PM256',
                            'barcode' => '1234567890001',
                            'price' => 34990000,
                            'cost_price' => 31000000,
                            'stock_quantity' => 50,
                            'min_stock_level' => 5,
                        ],
                        [
                            'name' => 'Samsung Galaxy S24 Ultra 512GB',
                            'description' => 'Galaxy S24 Ultra với S Pen, camera 200MP',
                            'short_description' => 'Samsung Galaxy S24 Ultra 512GB',
                            'sku' => 'SGS24U512',
                            'barcode' => '1234567890002',
                            'price' => 32990000,
                            'cost_price' => 29000000,
                            'stock_quantity' => 40,
                            'min_stock_level' => 5,
                        ],
                        [
                            'name' => 'iPad Pro 12.9 inch M2 256GB',
                            'description' => 'iPad Pro với chip M2, màn hình Liquid Retina XDR',
                            'short_description' => 'iPad Pro 12.9 inch M2 256GB',
                            'sku' => 'IPADPRO129M2',
                            'barcode' => '1234567890003',
                            'price' => 28990000,
                            'cost_price' => 25500000,
                            'stock_quantity' => 30,
                            'min_stock_level' => 3,
                        ],
                    ]
                ],
                [
                    'name' => 'Laptop & Máy tính',
                    'description' => 'Laptop, PC và phụ kiện máy tính',
                    'sort_order' => 2,
                    'products' => [
                        [
                            'name' => 'MacBook Pro 14 inch M3 Pro 512GB',
                            'description' => 'MacBook Pro với chip M3 Pro, 18GB RAM',
                            'short_description' => 'MacBook Pro 14 inch M3 Pro 512GB',
                            'sku' => 'MBP14M3P512',
                            'barcode' => '1234567890004',
                            'price' => 54990000,
                            'cost_price' => 48000000,
                            'stock_quantity' => 25,
                            'min_stock_level' => 3,
                        ],
                        [
                            'name' => 'Dell XPS 13 Plus i7 1TB',
                            'description' => 'Dell XPS 13 Plus với Intel Core i7 Gen 12',
                            'short_description' => 'Dell XPS 13 Plus i7 1TB',
                            'sku' => 'DELLXPS13I7',
                            'barcode' => '1234567890005',
                            'price' => 42990000,
                            'cost_price' => 38000000,
                            'stock_quantity' => 20,
                            'min_stock_level' => 2,
                        ],
                    ]
                ],
                [
                    'name' => 'Phụ kiện công nghệ',
                    'description' => 'Tai nghe, sạc, ốp lưng và phụ kiện',
                    'sort_order' => 3,
                    'products' => [
                        [
                            'name' => 'AirPods Pro 2nd Gen',
                            'description' => 'AirPods Pro thế hệ 2 với chip H2',
                            'short_description' => 'AirPods Pro 2nd Generation',
                            'sku' => 'AIRPODSPRO2',
                            'barcode' => '1234567890006',
                            'price' => 6490000,
                            'cost_price' => 5500000,
                            'stock_quantity' => 100,
                            'min_stock_level' => 10,
                        ],
                        [
                            'name' => 'Apple Watch Series 9 45mm',
                            'description' => 'Apple Watch Series 9 với chip S9',
                            'short_description' => 'Apple Watch Series 9 45mm GPS',
                            'sku' => 'AWS945MM',
                            'barcode' => '1234567890007',
                            'price' => 10990000,
                            'cost_price' => 9500000,
                            'stock_quantity' => 60,
                            'min_stock_level' => 5,
                        ],
                    ]
                ]
            ]
        ];
    }

    /**
     * Get Fashion products
     */
    private function getFashionProducts(): array
    {
        return [
            'categories' => [
                [
                    'name' => 'Thời trang nữ',
                    'description' => 'Quần áo, giày dép thời trang nữ',
                    'sort_order' => 1,
                    'products' => [
                        [
                            'name' => 'Váy maxi hoa nhí',
                            'description' => 'Váy maxi dài tay với họa tiết hoa nhí dễ thương',
                            'short_description' => 'Váy maxi hoa nhí dài tay',
                            'sku' => 'VMHN001',
                            'barcode' => '2234567890001',
                            'price' => 450000,
                            'cost_price' => 300000,
                            'stock_quantity' => 80,
                            'min_stock_level' => 10,
                        ],
                        [
                            'name' => 'Áo sơ mi trắng basic',
                            'description' => 'Áo sơ mi trắng cơ bản, phù hợp đi làm',
                            'short_description' => 'Áo sơ mi trắng basic',
                            'sku' => 'ASMTB001',
                            'barcode' => '2234567890002',
                            'price' => 320000,
                            'cost_price' => 200000,
                            'stock_quantity' => 120,
                            'min_stock_level' => 15,
                        ],
                    ]
                ],
                [
                    'name' => 'Thời trang nam',
                    'description' => 'Quần áo, giày dép thời trang nam',
                    'sort_order' => 2,
                    'products' => [
                        [
                            'name' => 'Áo polo nam cao cấp',
                            'description' => 'Áo polo nam chất liệu cotton cao cấp',
                            'short_description' => 'Áo polo nam cao cấp',
                            'sku' => 'APNCC001',
                            'barcode' => '2234567890003',
                            'price' => 380000,
                            'cost_price' => 250000,
                            'stock_quantity' => 90,
                            'min_stock_level' => 12,
                        ],
                    ]
                ]
            ]
        ];
    }

    /**
     * Get Food & Beverage products
     */
    private function getFoodBevProducts(): array
    {
        return [
            'categories' => [
                [
                    'name' => 'Đồ uống',
                    'description' => 'Nước giải khát, cà phê, trà',
                    'sort_order' => 1,
                    'products' => [
                        [
                            'name' => 'Cà phê Arabica rang xay 500g',
                            'description' => 'Cà phê Arabica nguyên chất rang xay',
                            'short_description' => 'Cà phê Arabica rang xay 500g',
                            'sku' => 'CFARX500',
                            'barcode' => '3234567890001',
                            'price' => 180000,
                            'cost_price' => 120000,
                            'stock_quantity' => 200,
                            'min_stock_level' => 20,
                        ],
                        [
                            'name' => 'Trà xanh Thái Nguyên 200g',
                            'description' => 'Trà xanh Thái Nguyên cao cấp',
                            'short_description' => 'Trà xanh Thái Nguyên 200g',
                            'sku' => 'TXTN200',
                            'barcode' => '3234567890002',
                            'price' => 150000,
                            'cost_price' => 100000,
                            'stock_quantity' => 150,
                            'min_stock_level' => 15,
                        ],
                    ]
                ],
                [
                    'name' => 'Thực phẩm khô',
                    'description' => 'Gạo, mì, bánh kẹo',
                    'sort_order' => 2,
                    'products' => [
                        [
                            'name' => 'Gạo ST25 5kg',
                            'description' => 'Gạo ST25 thơm ngon, chất lượng cao',
                            'short_description' => 'Gạo ST25 5kg',
                            'sku' => 'GST255KG',
                            'barcode' => '3234567890003',
                            'price' => 120000,
                            'cost_price' => 90000,
                            'stock_quantity' => 300,
                            'min_stock_level' => 30,
                        ],
                    ]
                ]
            ]
        ];
    }

    /**
     * Get HelloMart products (General store)
     */
    private function getHelloMartProducts(): array
    {
        return [
            'categories' => [
                [
                    'name' => 'Gia dụng',
                    'description' => 'Đồ gia dụng, dụng cụ nhà bếp',
                    'sort_order' => 1,
                    'products' => [
                        [
                            'name' => 'Nồi cơm điện 1.8L',
                            'description' => 'Nồi cơm điện Panasonic 1.8L',
                            'short_description' => 'Nồi cơm điện 1.8L',
                            'sku' => 'NCD18L',
                            'barcode' => '4234567890001',
                            'price' => 890000,
                            'cost_price' => 650000,
                            'stock_quantity' => 50,
                            'min_stock_level' => 5,
                        ],
                        [
                            'name' => 'Bộ dao thớt inox',
                            'description' => 'Bộ dao thớt inox cao cấp 5 món',
                            'short_description' => 'Bộ dao thớt inox 5 món',
                            'sku' => 'BDTI5M',
                            'barcode' => '4234567890002',
                            'price' => 450000,
                            'cost_price' => 300000,
                            'stock_quantity' => 80,
                            'min_stock_level' => 8,
                        ],
                    ]
                ],
                [
                    'name' => 'Văn phòng phẩm',
                    'description' => 'Bút, giấy, dụng cụ văn phòng',
                    'sort_order' => 2,
                    'products' => [
                        [
                            'name' => 'Bút bi Thiên Long TL-079',
                            'description' => 'Bút bi Thiên Long màu xanh',
                            'short_description' => 'Bút bi Thiên Long TL-079',
                            'sku' => 'BBTL079',
                            'barcode' => '4234567890003',
                            'price' => 5000,
                            'cost_price' => 3000,
                            'stock_quantity' => 500,
                            'min_stock_level' => 50,
                        ],
                    ]
                ]
            ]
        ];
    }

    /**
     * Get BiboMart products (Convenience store)
     */
    private function getBiboMartProducts(): array
    {
        return [
            'categories' => [
                [
                    'name' => 'Đồ ăn vặt',
                    'description' => 'Snack, kẹo, bánh',
                    'sort_order' => 1,
                    'products' => [
                        [
                            'name' => 'Bánh quy Oreo 137g',
                            'description' => 'Bánh quy Oreo vị socola',
                            'short_description' => 'Bánh quy Oreo 137g',
                            'sku' => 'BQO137',
                            'barcode' => '5234567890001',
                            'price' => 25000,
                            'cost_price' => 18000,
                            'stock_quantity' => 200,
                            'min_stock_level' => 20,
                        ],
                        [
                            'name' => 'Kẹo Mentos 37.5g',
                            'description' => 'Kẹo Mentos vị bạc hà',
                            'short_description' => 'Kẹo Mentos 37.5g',
                            'sku' => 'KM375',
                            'barcode' => '5234567890002',
                            'price' => 12000,
                            'cost_price' => 8000,
                            'stock_quantity' => 300,
                            'min_stock_level' => 30,
                        ],
                    ]
                ],
                [
                    'name' => 'Đồ uống',
                    'description' => 'Nước ngọt, nước suối',
                    'sort_order' => 2,
                    'products' => [
                        [
                            'name' => 'Coca Cola 330ml',
                            'description' => 'Nước ngọt Coca Cola lon 330ml',
                            'short_description' => 'Coca Cola 330ml',
                            'sku' => 'CC330',
                            'barcode' => '5234567890003',
                            'price' => 15000,
                            'cost_price' => 10000,
                            'stock_quantity' => 400,
                            'min_stock_level' => 40,
                        ],
                    ]
                ]
            ]
        ];
    }

    /**
     * Get general products (fallback)
     */
    private function getGeneralProducts(): array
    {
        return [
            'categories' => [
                [
                    'name' => 'Sản phẩm chung',
                    'description' => 'Các sản phẩm tổng hợp',
                    'sort_order' => 1,
                    'products' => [
                        [
                            'name' => 'Sản phẩm mẫu',
                            'description' => 'Đây là sản phẩm mẫu',
                            'short_description' => 'Sản phẩm mẫu',
                            'sku' => 'SPM001',
                            'barcode' => '9999999999999',
                            'price' => 100000,
                            'cost_price' => 70000,
                            'stock_quantity' => 100,
                            'min_stock_level' => 10,
                        ],
                    ]
                ]
            ]
        ];
    }

    /**
     * Display summary
     */
    private function displaySummary(): void
    {
        $this->command->info('📊 Comprehensive Data Summary:');
        $this->command->newLine();

        $tenants = Tenant::where('status', 'active')->get();
        
        foreach ($tenants as $tenant) {
            $userCount = TenantUser::where('tenant_id', $tenant->id)->count();
            $branchCount = BranchShop::where('tenant_id', $tenant->id)->count();
            $productCount = Product::where('tenant_id', $tenant->id)->count();
            
            $this->command->line("🏢 <fg=cyan>{$tenant->name}</> ({$tenant->slug})");
            $this->command->line("   Subdomain: <fg=green>{$tenant->subdomain}.yukimart.local</>");
            $this->command->line("   Users: <fg=green>{$userCount}</> (Owner, Manager, Cashier, Warehouse, Sales)");
            $this->command->line("   Branches: <fg=green>{$branchCount}</> chi nhánh");
            $this->command->line("   Products: <fg=green>{$productCount}</> sản phẩm");
            $this->command->newLine();
        }

        $this->command->info('🔑 Login Credentials (Password: 123456):');
        foreach ($tenants as $tenant) {
            $this->command->line("🏢 <fg=cyan>{$tenant->name}</>");
            $this->command->line("   Owner: owner@{$tenant->slug}.local");
            $this->command->line("   Manager: manager@{$tenant->slug}.local");
            $this->command->line("   Cashier: cashier@{$tenant->slug}.local");
            $this->command->line("   Warehouse: warehouse@{$tenant->slug}.local");
            $this->command->line("   Sales: sales@{$tenant->slug}.local");
            $this->command->newLine();
        }

        $this->command->info('🌐 Subdomain URLs for Testing:');
        foreach ($tenants as $tenant) {
            $this->command->line("   {$tenant->name}: <fg=blue>http://{$tenant->subdomain}.yukimart.local</>");
        }
    }
}
