<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Tenant;
use App\Services\TenantContextService;
use Illuminate\Support\Facades\Auth;

class TestPermissionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'yukimart:test-permission 
                            {email : User email to test}
                            {tenant_subdomain : Tenant subdomain}
                            {permission? : Permission to test (default: products.view)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test permission system for specific user and tenant';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $tenantSubdomain = $this->argument('tenant_subdomain');
        $permission = $this->argument('permission') ?? 'products.view';

        $this->info('🧪 YukiMart Permission Test');
        $this->info('='.str_repeat('=', 50));

        // Find user
        $user = User::where('email', $email)->first();
        if (!$user) {
            $this->error("❌ User not found: {$email}");
            return 1;
        }

        // Find tenant
        $tenant = Tenant::where('subdomain', $tenantSubdomain)->first();
        if (!$tenant) {
            $this->error("❌ Tenant not found: {$tenantSubdomain}");
            return 1;
        }

        $this->info("👤 User: {$user->name} ({$user->email})");
        $this->info("🏪 Tenant: {$tenant->name} ({$tenant->subdomain})");
        $this->info("🔐 Testing permission: {$permission}");
        $this->info('-'.str_repeat('-', 30));

        // Set tenant context
        $tenantContext = app(TenantContextService::class);
        $tenantContext->setCurrentTenant($tenant);

        // Test permission
        $canPerform = $tenantContext->userCanPerformAction($permission, $user);
        $this->info("✅ Can perform '{$permission}': " . ($canPerform ? '✅ YES' : '❌ NO'));

        // Get user roles
        $userRoles = $user->roles()->pluck('name')->toArray();
        $this->info("👑 User roles: " . (empty($userRoles) ? 'None' : implode(', ', $userRoles)));

        // Check if platform user
        $platformRoles = ['superadmin', 'admin', 'dev', 'manager', 'support'];
        $hasPlatformRole = !empty(array_intersect($platformRoles, $userRoles));
        $this->info("🏢 Platform user: " . ($hasPlatformRole ? '✅ YES' : '❌ NO'));

        // Get tenant user info
        $tenantUser = \App\Models\TenantUser::where('user_id', $user->id)
                                           ->where('tenant_id', $tenant->id)
                                           ->first();

        if ($tenantUser) {
            $this->info("🏪 Tenant role: {$tenantUser->role}");
            $this->info("🔓 Active in tenant: " . ($tenantUser->is_active ? '✅ YES' : '❌ NO'));
            $this->info("📧 Invitation status: {$tenantUser->invitation_status}");
            
            $permissions = $tenantUser->permissions ? json_decode($tenantUser->permissions, true) : [];
            $this->info("🔑 Explicit permissions: " . (empty($permissions) ? 'None' : implode(', ', $permissions)));
        } else {
            $this->warn("⚠️ No tenant user relationship found");
        }

        // Test specific permissions
        $testPermissions = [
            'products.view',
            'products.create',
            'products.edit',
            'products.delete',
            'orders.view',
            'orders.create',
            'invoices.view',
            'invoices.create'
        ];

        $this->newLine();
        $this->info("🧪 Testing multiple permissions:");
        $this->info('-'.str_repeat('-', 30));

        foreach ($testPermissions as $testPerm) {
            $canDo = $tenantContext->userCanPerformAction($testPerm, $user);
            $status = $canDo ? '✅' : '❌';
            $this->line("   {$status} {$testPerm}");
        }

        return 0;
    }
}
