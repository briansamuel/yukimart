<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tenant;
use App\Models\User;
use App\Models\TenantUser;
use App\Services\TenantContextService;
use Illuminate\Support\Facades\Hash;

class SimpleTenantDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🏢 Creating Simple Tenant Demo Data...');

        // Create additional tenants
        $this->createTenants();
        
        // Create users for each tenant
        $this->createUsers();

        $this->command->info('✅ Simple tenant demo data created successfully!');
        $this->displaySummary();
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

            // Update tenant statistics
            $userCount = TenantUser::where('tenant_id', $tenant->id)->where('is_active', true)->count();
            $tenant->update([
                'current_users' => $userCount,
                'current_products' => 0,
                'current_customers' => 0,
                'current_orders' => 0,
                'current_storage_used' => rand(1000000, 5000000), // Random storage usage
            ]);
        }
    }

    /**
     * Display summary of created data
     */
    private function displaySummary(): void
    {
        $this->command->info('📈 Demo Data Summary:');
        $this->command->newLine();

        $tenants = Tenant::all();
        
        foreach ($tenants as $tenant) {
            $this->command->line("🏢 <fg=cyan>{$tenant->name}</> ({$tenant->slug})");
            $this->command->line("   Status: <fg=green>{$tenant->status}</>");
            $this->command->line("   Plan: <fg=yellow>{$tenant->plan_type}</>");
            
            // Get statistics
            $userCount = TenantUser::where('tenant_id', $tenant->id)->count();
            
            $this->command->line("   Users: <fg=green>{$userCount}</> / {$tenant->max_users}");
            $this->command->line("   Products: <fg=green>0</> / {$tenant->max_products}");
            $this->command->newLine();
        }

        // Overall statistics
        $totalTenants = $tenants->count();
        $totalUsers = TenantUser::count();

        $this->command->info('🌍 Overall Statistics:');
        $this->command->line("   Total Tenants: <fg=cyan>{$totalTenants}</>");
        $this->command->line("   Total Users: <fg=green>{$totalUsers}</>");
        $this->command->newLine();

        $this->command->info('🔑 Demo Login Credentials:');
        $this->command->newLine();

        foreach ($tenants as $tenant) {
            $this->command->line("🏢 <fg=cyan>{$tenant->name}</>");
            $this->command->line("   Admin: admin@{$tenant->slug}.local / 123456");
            $this->command->line("   Manager: manager@{$tenant->slug}.local / 123456");
            $this->command->newLine();
        }

        $this->command->info('🌐 Access URLs:');
        $this->command->line("   Admin Panel: <fg=blue>http://yukimart.local/admin/login</>");
        $this->command->line("   Tenant Test: <fg=blue>http://yukimart.local/tenant/test</>");
        $this->command->line("   Dashboard: <fg=blue>http://yukimart.local/admin/dashboard</>");
        $this->command->newLine();

        $this->command->info('💡 Demo Features:');
        $this->command->line("   ✅ Multi-tenant data isolation");
        $this->command->line("   ✅ Tenant switching functionality");
        $this->command->line("   ✅ Role-based access control");
        $this->command->line("   ✅ Comprehensive statistics");
        $this->command->newLine();

        $this->command->info('🎯 Next Steps:');
        $this->command->line("   1. Login with any of the credentials above");
        $this->command->line("   2. Test tenant switching in the admin header");
        $this->command->line("   3. Explore different tenant data");
        $this->command->line("   4. Check statistics and reports");
        $this->command->line("   5. Test API endpoints at /tenant/test");
        $this->command->newLine();
    }
}
