<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tenant;
use App\Models\User;
use App\Models\TenantUser;
use App\Models\BranchShop;
use Illuminate\Support\Facades\Hash;

class SimpleTenantDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🏢 Creating Simple Tenant Data...');

        // Get all active tenants
        $tenants = Tenant::where('status', 'active')->get();
        
        foreach ($tenants as $tenant) {
            $this->command->info("🏪 Setting up data for {$tenant->name}...");
            
            // Create basic users
            $this->createBasicUsers($tenant);
            
            // Create branch shops
            $this->createBranchShops($tenant);
        }

        $this->command->info('✅ Simple tenant data created successfully!');
        $this->displaySummary();
    }

    /**
     * Create basic users for tenant
     */
    private function createBasicUsers(Tenant $tenant): void
    {
        $tenantSlug = $tenant->slug;
        
        $users = [
            [
                'role' => 'owner',
                'username' => "owner_{$tenantSlug}",
                'email' => "owner@{$tenantSlug}.local",
                'full_name' => "Owner {$tenant->name}",
                'phone' => '0901000001',
            ],
            [
                'role' => 'admin',
                'username' => "admin_{$tenantSlug}",
                'email' => "admin@{$tenantSlug}.local",
                'full_name' => "Admin {$tenant->name}",
                'phone' => '0901000002',
            ],
            [
                'role' => 'manager',
                'username' => "manager_{$tenantSlug}",
                'email' => "manager@{$tenantSlug}.local",
                'full_name' => "Manager {$tenant->name}",
                'phone' => '0901000003',
            ],
            [
                'role' => 'staff',
                'username' => "staff_{$tenantSlug}",
                'email' => "staff@{$tenantSlug}.local",
                'full_name' => "Staff {$tenant->name}",
                'phone' => '0901000004',
            ],
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
                    'province' => 'TP.HCM',
                    'district' => "Quận {$i}",
                    'ward' => "Phường {$i}",
                    'phone' => '028' . rand(1000000, 9999999),
                    'email' => "branch{$i}@{$tenantSlug}.local",
                    'status' => 'active',
                    'description' => "Chi nhánh {$i} của {$tenant->name}",
                    'opening_time' => '08:00:00',
                    'closing_time' => '22:00:00',
                    'shop_type' => 'standard',
                    'staff_count' => rand(5, 15),
                ]
            );
        }

        // Update tenant branch count
        $branchCount = BranchShop::where('tenant_id', $tenant->id)->count();
        $tenant->update(['current_branch_shops' => $branchCount]);
    }

    /**
     * Display summary
     */
    private function displaySummary(): void
    {
        $this->command->info('📊 Simple Data Summary:');
        $this->command->newLine();

        $tenants = Tenant::where('status', 'active')->get();
        
        foreach ($tenants as $tenant) {
            $userCount = TenantUser::where('tenant_id', $tenant->id)->count();
            $branchCount = BranchShop::where('tenant_id', $tenant->id)->count();
            
            $this->command->line("🏢 <fg=cyan>{$tenant->name}</> ({$tenant->slug})");
            $this->command->line("   Subdomain: <fg=green>{$tenant->subdomain}.yukimart.local</>");
            $this->command->line("   Users: <fg=green>{$userCount}</> (Owner, Admin, Manager, Staff)");
            $this->command->line("   Branches: <fg=green>{$branchCount}</> chi nhánh");
            $this->command->newLine();
        }

        $this->command->info('🔑 Login Credentials (Password: 123456):');
        foreach ($tenants as $tenant) {
            $this->command->line("🏢 <fg=cyan>{$tenant->name}</>");
            $this->command->line("   Owner: owner@{$tenant->slug}.local");
            $this->command->line("   Admin: admin@{$tenant->slug}.local");
            $this->command->line("   Manager: manager@{$tenant->slug}.local");
            $this->command->line("   Staff: staff@{$tenant->slug}.local");
            $this->command->newLine();
        }

        $this->command->info('🌐 Subdomain URLs for Testing:');
        foreach ($tenants as $tenant) {
            $this->command->line("   {$tenant->name}: <fg=blue>http://{$tenant->subdomain}.yukimart.local</>");
        }
        $this->command->newLine();

        $this->command->info('🎯 Next Steps:');
        $this->command->line('   1. Test login on each subdomain');
        $this->command->line('   2. Use Playwright for automated testing');
        $this->command->line('   3. Verify tenant data isolation');
        $this->command->line('   4. Test admin panel functionality');
    }
}
