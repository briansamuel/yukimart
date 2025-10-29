<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Role;
use App\Models\TenantUser;
use App\Models\Tenant;
use Illuminate\Support\Facades\Hash;

class VerifyUserSetupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'yukimart:verify-users 
                            {--detailed : Show detailed user information}
                            {--test-login : Test login functionality}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify YukiMart user setup and test login functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 YukiMart User Setup Verification');
        $this->info('='.str_repeat('=', 50));

        $detailed = $this->option('detailed');
        $testLogin = $this->option('test-login');

        // Verify platform users
        $this->verifyPlatformUsers($detailed);

        // Verify tenant users
        $this->verifyTenantUsers($detailed);

        // Verify roles and permissions
        $this->verifyRolesAndPermissions($detailed);

        // Test login functionality if requested
        if ($testLogin) {
            $this->testLoginFunctionality();
        }

        $this->newLine();
        $this->info('✅ User setup verification completed!');

        return 0;
    }

    /**
     * Verify platform users
     */
    private function verifyPlatformUsers($detailed = false)
    {
        $this->newLine();
        $this->info('🏢 Platform Users Verification');
        $this->info('-'.str_repeat('-', 30));

        $expectedPlatformUsers = [
            'superadmin@yukimart.local' => 'superadmin',
            'admin@yukimart.local' => 'admin',
            'dev@yukimart.local' => 'dev',
            'manager@yukimart.local' => 'manager',
            'support@yukimart.local' => 'support',
        ];

        $platformUsers = [];
        $issues = [];

        foreach ($expectedPlatformUsers as $email => $expectedRole) {
            $user = User::where('email', $email)->first();
            
            if (!$user) {
                $issues[] = "❌ User not found: {$email}";
                continue;
            }

            $hasCorrectPassword = Hash::check('123456', $user->password);
            $userRoles = $user->roles()->pluck('name')->toArray();
            $hasExpectedRole = in_array($expectedRole, $userRoles);

            $status = '✅';
            if (!$hasCorrectPassword) {
                $status = '⚠️';
                $issues[] = "Password incorrect for: {$email}";
            }
            if (!$hasExpectedRole) {
                $status = '⚠️';
                $issues[] = "Role missing for: {$email} (expected: {$expectedRole})";
            }

            $platformUsers[] = [
                'status' => $status,
                'email' => $email,
                'name' => $user->name,
                'roles' => implode(', ', $userRoles),
                'password_ok' => $hasCorrectPassword ? '✅' : '❌',
                'last_login' => $user->last_login_at ? $user->last_login_at->format('Y-m-d H:i') : 'Never'
            ];
        }

        if ($detailed) {
            $this->table(
                ['Status', 'Email', 'Name', 'Roles', 'Password', 'Last Login'],
                array_map(function($user) {
                    return [
                        $user['status'],
                        $user['email'],
                        $user['name'],
                        $user['roles'],
                        $user['password_ok'],
                        $user['last_login']
                    ];
                }, $platformUsers)
            );
        } else {
            foreach ($platformUsers as $user) {
                $this->line("{$user['status']} {$user['email']} ({$user['roles']})");
            }
        }

        if (!empty($issues)) {
            $this->newLine();
            $this->warn('⚠️ Platform User Issues:');
            foreach ($issues as $issue) {
                $this->line("   {$issue}");
            }
        } else {
            $this->info('✅ All platform users verified successfully');
        }
    }

    /**
     * Verify tenant users
     */
    private function verifyTenantUsers($detailed = false)
    {
        $this->newLine();
        $this->info('🏪 Tenant Users Verification');
        $this->info('-'.str_repeat('-', 30));

        $tenantUsers = TenantUser::with(['user', 'tenant'])->get();
        $tenantUserData = [];
        $issues = [];

        foreach ($tenantUsers as $tenantUser) {
            $user = $tenantUser->user;
            $tenant = $tenantUser->tenant;

            if (!$user || !$tenant) {
                $issues[] = "❌ Invalid tenant user relationship: ID {$tenantUser->id}";
                continue;
            }

            $hasCorrectPassword = Hash::check('123456', $user->password);
            $status = $hasCorrectPassword ? '✅' : '⚠️';

            if (!$hasCorrectPassword) {
                $issues[] = "Password incorrect for: {$user->email}";
            }

            $tenantUserData[] = [
                'status' => $status,
                'email' => $user->email,
                'name' => $user->name,
                'tenant' => $tenant->name,
                'role' => $tenantUser->role,
                'active' => $tenantUser->is_active ? '✅' : '❌',
                'password_ok' => $hasCorrectPassword ? '✅' : '❌'
            ];
        }

        if ($detailed) {
            $this->table(
                ['Status', 'Email', 'Name', 'Tenant', 'Role', 'Active', 'Password'],
                array_map(function($user) {
                    return [
                        $user['status'],
                        $user['email'],
                        $user['name'],
                        $user['tenant'],
                        $user['role'],
                        $user['active'],
                        $user['password_ok']
                    ];
                }, $tenantUserData)
            );
        } else {
            $tenantGroups = collect($tenantUserData)->groupBy('tenant');
            foreach ($tenantGroups as $tenantName => $users) {
                $this->line("📍 {$tenantName}:");
                foreach ($users as $user) {
                    $this->line("   {$user['status']} {$user['email']} ({$user['role']})");
                }
            }
        }

        if (!empty($issues)) {
            $this->newLine();
            $this->warn('⚠️ Tenant User Issues:');
            foreach ($issues as $issue) {
                $this->line("   {$issue}");
            }
        } else {
            $this->info('✅ All tenant users verified successfully');
        }

        $this->info("📊 Total tenant users: " . count($tenantUserData));
    }

    /**
     * Verify roles and permissions
     */
    private function verifyRolesAndPermissions($detailed = false)
    {
        $this->newLine();
        $this->info('🔐 Roles and Permissions Verification');
        $this->info('-'.str_repeat('-', 30));

        $expectedRoles = ['superadmin', 'admin', 'dev', 'manager', 'support'];
        $roles = Role::all();
        $issues = [];

        foreach ($expectedRoles as $expectedRole) {
            $role = $roles->where('name', $expectedRole)->first();
            if (!$role) {
                $issues[] = "❌ Role not found: {$expectedRole}";
            } else {
                $userCount = $role->users()->count();
                $this->line("✅ {$role->display_name} ({$userCount} users)");
                
                if ($detailed) {
                    $permissions = $role->permissions()->count();
                    $this->line("   📋 Permissions: {$permissions}");
                }
            }
        }

        if (!empty($issues)) {
            $this->newLine();
            $this->warn('⚠️ Role Issues:');
            foreach ($issues as $issue) {
                $this->line("   {$issue}");
            }
        } else {
            $this->info('✅ All roles verified successfully');
        }
    }

    /**
     * Test login functionality
     */
    private function testLoginFunctionality()
    {
        $this->newLine();
        $this->info('🧪 Testing Login Functionality');
        $this->info('-'.str_repeat('-', 30));

        // Test platform user login
        $superadmin = User::where('email', 'superadmin@yukimart.local')->first();
        if ($superadmin && Hash::check('123456', $superadmin->password)) {
            $this->info('✅ Platform login test: PASSED');
        } else {
            $this->error('❌ Platform login test: FAILED');
        }

        // Test tenant user login
        $tenantUser = TenantUser::with('user')->first();
        if ($tenantUser && $tenantUser->user && Hash::check('123456', $tenantUser->user->password)) {
            $this->info('✅ Tenant login test: PASSED');
        } else {
            $this->error('❌ Tenant login test: FAILED');
        }

        // Test role assignments
        $superadminRoles = $superadmin ? $superadmin->roles()->count() : 0;
        if ($superadminRoles > 0) {
            $this->info('✅ Role assignment test: PASSED');
        } else {
            $this->error('❌ Role assignment test: FAILED');
        }
    }
}
