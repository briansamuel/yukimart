<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Tenant;
use App\Models\TenantUser;
use App\Services\TenantContextService;
use Illuminate\Support\Facades\Route;

class PermissionAuditCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'yukimart:permission-audit 
                            {--fix : Fix permission issues found}
                            {--detailed : Show detailed audit information}
                            {--export : Export audit results to file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Comprehensive permission system audit for YukiMart';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 YukiMart Permission System Audit');
        $this->info('='.str_repeat('=', 50));

        $fix = $this->option('fix');
        $detailed = $this->option('detailed');
        $export = $this->option('export');

        $auditResults = [];

        // 1. Audit tenant users
        $auditResults['tenant_users'] = $this->auditTenantUsers($fix, $detailed);

        // 2. Audit platform users
        $auditResults['platform_users'] = $this->auditPlatformUsers($fix, $detailed);

        // 3. Audit permission mappings
        $auditResults['permission_mappings'] = $this->auditPermissionMappings($detailed);

        // 4. Audit middleware configuration
        $auditResults['middleware'] = $this->auditMiddleware($detailed);

        // 5. Audit route protection
        $auditResults['route_protection'] = $this->auditRouteProtection($detailed);

        // Generate summary
        $this->generateAuditSummary($auditResults);

        // Export results if requested
        if ($export) {
            $this->exportAuditResults($auditResults);
        }

        return 0;
    }

    /**
     * Audit tenant users
     */
    private function auditTenantUsers($fix, $detailed)
    {
        $this->newLine();
        $this->info('👥 Auditing Tenant Users');
        $this->info('-'.str_repeat('-', 30));

        $tenantUsers = TenantUser::with(['user', 'tenant'])->get();
        $issues = [];
        $fixed = 0;

        foreach ($tenantUsers as $tenantUser) {
            $user = $tenantUser->user;
            $tenant = $tenantUser->tenant;

            if (!$user || !$tenant) {
                $issues[] = [
                    'type' => 'missing_relationship',
                    'tenant_user_id' => $tenantUser->id,
                    'message' => 'Missing user or tenant relationship'
                ];
                continue;
            }

            // Check if user is active
            if (!$tenantUser->is_active) {
                $issues[] = [
                    'type' => 'inactive_user',
                    'user' => $user->email,
                    'tenant' => $tenant->name,
                    'message' => 'User is inactive'
                ];

                if ($fix) {
                    $tenantUser->update(['is_active' => true]);
                    $fixed++;
                }
            }

            // Check invitation status
            if ($tenantUser->invitation_status !== 'accepted') {
                $issues[] = [
                    'type' => 'invitation_pending',
                    'user' => $user->email,
                    'tenant' => $tenant->name,
                    'message' => 'Invitation not accepted'
                ];

                if ($fix) {
                    $tenantUser->update(['invitation_status' => 'accepted']);
                    $fixed++;
                }
            }

            // Check role validity
            $validRoles = ['owner', 'admin', 'manager', 'staff', 'viewer'];
            if (!in_array($tenantUser->role, $validRoles)) {
                $issues[] = [
                    'type' => 'invalid_role',
                    'user' => $user->email,
                    'tenant' => $tenant->name,
                    'role' => $tenantUser->role,
                    'message' => 'Invalid role assigned'
                ];
            }

            if ($detailed) {
                $this->line("✅ {$user->email} -> {$tenant->name} ({$tenantUser->role})");
            }
        }

        $this->info("📊 Tenant Users: " . $tenantUsers->count() . " total, " . count($issues) . " issues");
        if ($fix && $fixed > 0) {
            $this->info("🔧 Fixed: {$fixed} issues");
        }

        return [
            'total' => $tenantUsers->count(),
            'issues' => $issues,
            'fixed' => $fixed
        ];
    }

    /**
     * Audit platform users
     */
    private function auditPlatformUsers($fix, $detailed)
    {
        $this->newLine();
        $this->info('🏢 Auditing Platform Users');
        $this->info('-'.str_repeat('-', 30));

        $platformRoles = ['superadmin', 'admin', 'dev', 'manager', 'support'];
        $platformUsers = User::whereHas('roles', function($q) use ($platformRoles) {
            $q->whereIn('name', $platformRoles);
        })->with('roles')->get();

        $issues = [];
        $fixed = 0;

        foreach ($platformUsers as $user) {
            $userRoles = $user->roles->pluck('name')->toArray();
            
            // Check if user has valid platform role
            $hasValidRole = !empty(array_intersect($platformRoles, $userRoles));
            if (!$hasValidRole) {
                $issues[] = [
                    'type' => 'invalid_platform_role',
                    'user' => $user->email,
                    'roles' => $userRoles,
                    'message' => 'No valid platform role'
                ];
            }

            // Check if user is active
            if ($user->status !== 'active') {
                $issues[] = [
                    'type' => 'inactive_platform_user',
                    'user' => $user->email,
                    'status' => $user->status,
                    'message' => 'Platform user is inactive'
                ];

                if ($fix) {
                    $user->update(['status' => 'active']);
                    $fixed++;
                }
            }

            if ($detailed) {
                $this->line("✅ {$user->email} (" . implode(', ', $userRoles) . ")");
            }
        }

        $this->info("📊 Platform Users: " . $platformUsers->count() . " total, " . count($issues) . " issues");
        if ($fix && $fixed > 0) {
            $this->info("🔧 Fixed: {$fixed} issues");
        }

        return [
            'total' => $platformUsers->count(),
            'issues' => $issues,
            'fixed' => $fixed
        ];
    }

    /**
     * Audit permission mappings
     */
    private function auditPermissionMappings($detailed)
    {
        $this->newLine();
        $this->info('🔐 Auditing Permission Mappings');
        $this->info('-'.str_repeat('-', 30));

        $tenantContext = app(TenantContextService::class);
        $testPermissions = [
            'products.view', 'products.create', 'products.edit', 'products.delete',
            'orders.view', 'orders.create', 'orders.edit', 'orders.delete',
            'invoices.view', 'invoices.create', 'invoices.edit', 'invoices.delete',
            'customers.view', 'customers.create', 'customers.edit', 'customers.delete',
            'reports.view', 'settings.view'
        ];

        $roleTests = ['owner', 'admin', 'manager', 'staff', 'viewer'];
        $issues = [];

        foreach ($roleTests as $role) {
            foreach ($testPermissions as $permission) {
                $hasPermission = $tenantContext->checkRoleBasedPermission($role, $permission);
                
                if ($detailed) {
                    $status = $hasPermission ? '✅' : '❌';
                    $this->line("   {$status} {$role} -> {$permission}");
                }

                // Check for expected permissions
                if ($role === 'owner' && !$hasPermission) {
                    $issues[] = [
                        'type' => 'missing_owner_permission',
                        'role' => $role,
                        'permission' => $permission,
                        'message' => 'Owner should have all permissions'
                    ];
                }
            }
        }

        $this->info("📊 Permission Mappings: " . (count($roleTests) * count($testPermissions)) . " tested, " . count($issues) . " issues");

        return [
            'total_tested' => count($roleTests) * count($testPermissions),
            'issues' => $issues
        ];
    }

    /**
     * Audit middleware configuration
     */
    private function auditMiddleware($detailed)
    {
        $this->newLine();
        $this->info('🛡️ Auditing Middleware Configuration');
        $this->info('-'.str_repeat('-', 30));

        $requiredMiddleware = [
            'tenant.resolve',
            'tenant.auth',
            'tenant.permission'
        ];

        $issues = [];

        // Check if middleware are registered
        $kernel = app(\Illuminate\Contracts\Http\Kernel::class);
        $routeMiddleware = $kernel->getRouteMiddleware();

        foreach ($requiredMiddleware as $middleware) {
            if (!isset($routeMiddleware[$middleware])) {
                $issues[] = [
                    'type' => 'missing_middleware',
                    'middleware' => $middleware,
                    'message' => 'Required middleware not registered'
                ];
            } else {
                if ($detailed) {
                    $this->line("✅ {$middleware} -> {$routeMiddleware[$middleware]}");
                }
            }
        }

        $this->info("📊 Middleware: " . count($requiredMiddleware) . " checked, " . count($issues) . " issues");

        return [
            'total_checked' => count($requiredMiddleware),
            'issues' => $issues
        ];
    }

    /**
     * Audit route protection
     */
    private function auditRouteProtection($detailed)
    {
        $this->newLine();
        $this->info('🛣️ Auditing Route Protection');
        $this->info('-'.str_repeat('-', 30));

        $adminRoutes = collect(Route::getRoutes())->filter(function($route) {
            return str_starts_with($route->uri(), 'admin/') && 
                   !str_starts_with($route->uri(), 'admin/login') &&
                   !str_starts_with($route->uri(), 'admin/register');
        });

        $issues = [];
        $protectedCount = 0;

        foreach ($adminRoutes as $route) {
            $middleware = $route->middleware();
            $hasAuth = in_array('auth:admin', $middleware);
            $hasTenantResolve = in_array('tenant.resolve', $middleware);
            $hasTenantAuth = in_array('tenant.auth', $middleware);

            if (!$hasAuth || !$hasTenantResolve || !$hasTenantAuth) {
                $issues[] = [
                    'type' => 'unprotected_route',
                    'uri' => $route->uri(),
                    'methods' => implode(',', $route->methods()),
                    'middleware' => $middleware,
                    'message' => 'Route missing required protection middleware'
                ];
            } else {
                $protectedCount++;
                if ($detailed) {
                    $this->line("✅ {$route->uri()} (" . implode(',', $route->methods()) . ")");
                }
            }
        }

        $this->info("📊 Routes: " . $adminRoutes->count() . " admin routes, {$protectedCount} protected, " . count($issues) . " issues");

        return [
            'total_routes' => $adminRoutes->count(),
            'protected_routes' => $protectedCount,
            'issues' => $issues
        ];
    }

    /**
     * Generate audit summary
     */
    private function generateAuditSummary($results)
    {
        $this->newLine();
        $this->info('📋 Audit Summary');
        $this->info('='.str_repeat('=', 30));

        $totalIssues = 0;
        $totalFixed = 0;

        foreach ($results as $category => $data) {
            $issues = count($data['issues'] ?? []);
            $fixed = $data['fixed'] ?? 0;
            $totalIssues += $issues;
            $totalFixed += $fixed;

            $status = $issues === 0 ? '✅' : '⚠️';
            $this->line("{$status} " . ucwords(str_replace('_', ' ', $category)) . ": {$issues} issues" . ($fixed > 0 ? ", {$fixed} fixed" : ""));
        }

        $this->newLine();
        if ($totalIssues === 0) {
            $this->info('🎉 No issues found! Permission system is healthy.');
        } else {
            $this->warn("⚠️ Total issues found: {$totalIssues}" . ($totalFixed > 0 ? ", {$totalFixed} fixed" : ""));
            $this->info('💡 Run with --fix to automatically fix issues where possible.');
        }
    }

    /**
     * Export audit results
     */
    private function exportAuditResults($results)
    {
        $filename = 'permission_audit_' . date('Y-m-d_H-i-s') . '.json';
        $filepath = storage_path('logs/' . $filename);

        file_put_contents($filepath, json_encode($results, JSON_PRETTY_PRINT));

        $this->info("📄 Audit results exported to: {$filepath}");
    }
}
