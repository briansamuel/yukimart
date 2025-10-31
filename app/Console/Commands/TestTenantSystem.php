<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TenantContextService;
use App\Models\Tenant;
use App\Models\User;

class TestTenantSystem extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'tenant:test';

    /**
     * The console command description.
     */
    protected $description = 'Test tenant system functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🏢 Testing Tenant System...');
        $this->newLine();

        try {
            // Test 1: TenantContextService
            $this->info('1. Testing TenantContextService...');
            $service = app(TenantContextService::class);
            $this->info('   ✅ TenantContextService loaded successfully');

            // Test 2: Load tenant
            $this->info('2. Testing tenant loading...');
            $tenant = Tenant::first();
            if ($tenant) {
                $this->info("   ✅ Tenant found: {$tenant->name} (ID: {$tenant->id})");
                
                // Test 3: Set current tenant
                $this->info('3. Testing tenant context setting...');
                $service->setCurrentTenant($tenant);
                $currentTenant = $service->getCurrentTenant();
                if ($currentTenant && $currentTenant->id === $tenant->id) {
                    $this->info("   ✅ Current tenant set successfully: {$currentTenant->name}");
                } else {
                    $this->error('   ❌ Failed to set current tenant');
                    return 1;
                }
            } else {
                $this->error('   ❌ No tenant found in database');
                return 1;
            }

            // Test 4: Load user
            $this->info('4. Testing user loading...');
            $user = User::first();
            if ($user) {
                $this->info("   ✅ User found: {$user->username} ({$user->email})");
            } else {
                $this->error('   ❌ No user found in database');
                return 1;
            }

            // Test 5: Available tenants for user
            $this->info('5. Testing available tenants for user...');
            $availableTenants = $service->getAvailableTenantsForUser($user);
            $this->info("   ✅ Available tenants for user: {$availableTenants->count()}");

            // Test 6: User role in tenant
            $this->info('6. Testing user role in current tenant...');
            $role = $service->getUserRoleInCurrentTenant($user);
            if ($role) {
                $this->info("   ✅ User role in current tenant: {$role}");
            } else {
                $this->warn('   ⚠️  No role found for user in current tenant');
            }

            // Test 7: User permissions
            $this->info('7. Testing user permissions...');
            $canManageUsers = $service->userCanPerformAction('users.manage', $user);
            $this->info("   ✅ User can manage users: " . ($canManageUsers ? 'Yes' : 'No'));

            // Test 8: Tenant statistics
            $this->info('8. Testing tenant statistics...');
            $statistics = $service->getTenantStatistics();
            if ($statistics) {
                $this->info("   ✅ Tenant statistics loaded:");
                $this->info("      - Users: {$statistics['users']['current']}/{$statistics['users']['max']}");
                $this->info("      - Products: {$statistics['products']['current']}/{$statistics['products']['max']}");
                $this->info("      - Storage: " . $this->formatBytes($statistics['storage']['current']) . "/" . $this->formatBytes($statistics['storage']['max']));
            } else {
                $this->warn('   ⚠️  No statistics available');
            }

            // Test 9: Tenant settings
            $this->info('9. Testing tenant settings...');
            $testKey = 'test_setting_' . time();
            $testValue = 'test_value_' . rand(1000, 9999);
            
            $setResult = $service->setTenantSetting($testKey, $testValue);
            if ($setResult) {
                $this->info("   ✅ Setting saved: {$testKey} = {$testValue}");
                
                $getValue = $service->getTenantSetting($testKey);
                if ($getValue === $testValue) {
                    $this->info("   ✅ Setting retrieved correctly: {$getValue}");
                } else {
                    $this->error("   ❌ Setting value mismatch. Expected: {$testValue}, Got: {$getValue}");
                }
            } else {
                $this->error('   ❌ Failed to save setting');
            }

            // Test 10: Tenant switching
            $this->info('10. Testing tenant switching...');
            $switchResult = $service->switchToTenant($tenant->id, $user);
            if ($switchResult) {
                $this->info("   ✅ Tenant switching successful");
            } else {
                $this->error('   ❌ Tenant switching failed');
            }

            // Test 11: DashboardService with tenant context
            $this->info('11. Testing DashboardService with tenant context...');
            try {
                $totalProducts = \App\Services\DashboardService::totalProducts();
                $this->info("   ✅ Total products: {$totalProducts}");

                $totalCustomers = \App\Services\DashboardService::totalCustomers();
                $this->info("   ✅ Total customers: {$totalCustomers}");

                $totalOrders = \App\Services\DashboardService::totalOrders();
                $this->info("   ✅ Total orders: {$totalOrders}");

                $totalUsers = \App\Services\DashboardService::totalUsers();
                $this->info("   ✅ Total users: {$totalUsers}");

                $todayStats = \App\Services\DashboardService::getTodaySalesStats();
                $this->info("   ✅ Today's sales stats loaded");
                $this->info("      - Orders today: {$todayStats['orders_count']}");
                $this->info("      - Revenue today: " . number_format($todayStats['revenue']) . " VNĐ");

            } catch (\Exception $e) {
                $this->error("   ❌ DashboardService test failed: {$e->getMessage()}");
            }

            $this->newLine();
            $this->info('🎉 All tenant system tests completed successfully!');
            $this->newLine();

            // Summary
            $this->info('📊 Test Summary:');
            $this->info("   - Tenant: {$tenant->name} (Status: {$tenant->status})");
            $this->info("   - User: {$user->username} (Role: {$role})");
            $this->info("   - Available Tenants: {$availableTenants->count()}");
            $this->info("   - User Permissions: " . ($canManageUsers ? 'Admin level' : 'Limited'));
            
            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Test failed with exception:');
            $this->error("   {$e->getMessage()}");
            $this->error("   File: {$e->getFile()}:{$e->getLine()}");
            return 1;
        }
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes($bytes)
    {
        if ($bytes === 0) return '0 Bytes';
        
        $k = 1024;
        $sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        $i = floor(log($bytes) / log($k));
        
        return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
    }
}
