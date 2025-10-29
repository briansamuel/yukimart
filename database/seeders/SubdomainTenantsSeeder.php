<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tenant;
use App\Models\User;
use App\Models\TenantUser;
use Illuminate\Support\Facades\Hash;

class SubdomainTenantsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🌐 Creating Subdomain Tenants...');

        // Create HelloMart tenant
        $this->createHelloMart();
        
        // Create BiboMart tenant
        $this->createBiboMart();

        $this->command->info('✅ Subdomain tenants created successfully!');
        $this->displaySummary();
    }

    /**
     * Create HelloMart tenant
     */
    private function createHelloMart(): void
    {
        $this->command->info('🏪 Creating HelloMart tenant...');

        $tenant = Tenant::firstOrCreate(
            ['slug' => 'hellomart'],
            [
                'name' => 'HelloMart Store',
                'slug' => 'hellomart',
                'subdomain' => 'hellomart',
                'email' => 'admin@hellomart.local',
                'status' => 'active',
                'plan_type' => 'premium',
                'max_users' => 30,
                'max_products' => 3000,
                'max_branch_shops' => 8,
                'storage_limit' => 8589934592, // 8GB
                'api_rate_limit' => 3000,
                'current_users' => 0,
                'current_products' => 0,
                'current_branch_shops' => 0,
                'current_storage_used' => rand(1000000, 3000000),
            ]
        );

        // Create admin user
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@hellomart.local'],
            [
                'username' => 'admin_hellomart',
                'email' => 'admin@hellomart.local',
                'password' => Hash::make('123456'),
                'full_name' => 'Admin HelloMart',
                'address' => '123 Hello Street, HelloMart City',
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
            ['email' => 'manager@hellomart.local'],
            [
                'username' => 'manager_hellomart',
                'email' => 'manager@hellomart.local',
                'password' => Hash::make('123456'),
                'full_name' => 'Manager HelloMart',
                'address' => '456 Hello Avenue, HelloMart City',
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

        // Update tenant user count
        $userCount = TenantUser::where('tenant_id', $tenant->id)->where('is_active', true)->count();
        $tenant->update(['current_users' => $userCount]);
    }

    /**
     * Create BiboMart tenant
     */
    private function createBiboMart(): void
    {
        $this->command->info('🏪 Creating BiboMart tenant...');

        $tenant = Tenant::firstOrCreate(
            ['slug' => 'bibomart'],
            [
                'name' => 'BiboMart Store',
                'slug' => 'bibomart',
                'subdomain' => 'bibomart',
                'email' => 'admin@bibomart.local',
                'status' => 'active',
                'plan_type' => 'basic',
                'max_users' => 15,
                'max_products' => 800,
                'max_branch_shops' => 3,
                'storage_limit' => 3221225472, // 3GB
                'api_rate_limit' => 1500,
                'current_users' => 0,
                'current_products' => 0,
                'current_branch_shops' => 0,
                'current_storage_used' => rand(500000, 1500000),
            ]
        );

        // Create admin user
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@bibomart.local'],
            [
                'username' => 'admin_bibomart',
                'email' => 'admin@bibomart.local',
                'password' => Hash::make('123456'),
                'full_name' => 'Admin BiboMart',
                'address' => '123 Bibo Street, BiboMart City',
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
            ['email' => 'manager@bibomart.local'],
            [
                'username' => 'manager_bibomart',
                'email' => 'manager@bibomart.local',
                'password' => Hash::make('123456'),
                'full_name' => 'Manager BiboMart',
                'address' => '456 Bibo Avenue, BiboMart City',
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

        // Update tenant user count
        $userCount = TenantUser::where('tenant_id', $tenant->id)->where('is_active', true)->count();
        $tenant->update(['current_users' => $userCount]);
    }

    /**
     * Display summary
     */
    private function displaySummary(): void
    {
        $this->command->info('📈 Subdomain Tenants Summary:');
        $this->command->newLine();

        $newTenants = Tenant::whereIn('slug', ['hellomart', 'bibomart'])->get();
        
        foreach ($newTenants as $tenant) {
            $this->command->line("🏢 <fg=cyan>{$tenant->name}</> ({$tenant->slug})");
            $this->command->line("   Subdomain: <fg=green>{$tenant->subdomain}.yukimart.local</>");
            $this->command->line("   Status: <fg=green>{$tenant->status}</>");
            $this->command->line("   Plan: <fg=yellow>{$tenant->plan_type}</>");
            $this->command->line("   Users: <fg=green>{$tenant->current_users}</> / {$tenant->max_users}");
            $this->command->newLine();
        }

        $this->command->info('🔑 New Login Credentials:');
        $this->command->newLine();

        foreach ($newTenants as $tenant) {
            $this->command->line("🏢 <fg=cyan>{$tenant->name}</>");
            $this->command->line("   Admin: admin@{$tenant->slug}.local / 123456");
            $this->command->line("   Manager: manager@{$tenant->slug}.local / 123456");
            $this->command->newLine();
        }

        $this->command->info('🌐 Subdomain Access URLs:');
        $this->command->line("   HelloMart: <fg=blue>http://hellomart.yukimart.local</>");
        $this->command->line("   BiboMart: <fg=blue>http://bibomart.yukimart.local</>");
        $this->command->newLine();

        $this->command->info('📋 All Subdomain Mappings:');
        $allTenants = Tenant::all();
        foreach ($allTenants as $tenant) {
            $subdomain = $tenant->subdomain ?: $tenant->slug;
            $this->command->line("   {$tenant->name}: <fg=blue>http://{$subdomain}.yukimart.local</>");
        }
        $this->command->newLine();
    }
}
