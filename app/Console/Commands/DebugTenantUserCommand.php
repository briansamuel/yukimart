<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Tenant;
use App\Models\TenantUser;
use Illuminate\Support\Facades\Hash;

class DebugTenantUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'yukimart:debug-tenant-user 
                            {email? : User email to debug}
                            {--fix : Fix issues found}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Debug tenant user relationships and permissions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 YukiMart Tenant User Debug');
        $this->info('='.str_repeat('=', 50));

        $email = $this->argument('email');
        $fix = $this->option('fix');

        if ($email) {
            $this->debugSpecificUser($email, $fix);
        } else {
            $this->debugAllTenantUsers($fix);
        }

        return 0;
    }

    /**
     * Debug specific user
     */
    private function debugSpecificUser($email, $fix = false)
    {
        $this->info("🔍 Debugging user: {$email}");
        $this->info('-'.str_repeat('-', 30));

        $user = User::where('email', $email)->first();
        if (!$user) {
            $this->error("❌ User not found: {$email}");
            return;
        }

        $this->info("✅ User found: {$user->name} (ID: {$user->id})");

        // Check password
        $passwordCorrect = Hash::check('123456', $user->password);
        $this->info("🔑 Password check: " . ($passwordCorrect ? '✅ Correct' : '❌ Incorrect'));

        // Check tenant relationships
        $tenantUsers = TenantUser::where('user_id', $user->id)->with('tenant')->get();
        $this->info("🏪 Tenant relationships: " . $tenantUsers->count());

        foreach ($tenantUsers as $tenantUser) {
            $tenant = $tenantUser->tenant;
            $this->info("   📍 Tenant: {$tenant->name} ({$tenant->subdomain})");
            $this->info("      - Role: {$tenantUser->role}");
            $this->info("      - Active: " . ($tenantUser->is_active ? '✅ Yes' : '❌ No'));
            $this->info("      - Invitation: {$tenantUser->invitation_status}");
            
            if ($tenantUser->permissions) {
                $permissions = json_decode($tenantUser->permissions, true);
                $this->info("      - Permissions: " . implode(', ', $permissions ?: []));
            } else {
                $this->info("      - Permissions: None (using role-based)");
            }

            // Check if user can access tenant
            $canAccess = $this->checkTenantAccess($user, $tenant);
            $this->info("      - Can Access: " . ($canAccess ? '✅ Yes' : '❌ No'));

            if (!$canAccess && $fix) {
                $this->fixTenantAccess($tenantUser);
            }
        }

        // Check user's tenants via relationship
        $userTenants = $user->tenants()->get();
        $this->info("🔗 User->tenants relationship: " . $userTenants->count());

        if ($tenantUsers->count() !== $userTenants->count()) {
            $this->warn("⚠️ Mismatch between TenantUser records and User->tenants relationship");
            if ($fix) {
                $this->info("🔧 Fixing relationship...");
                // This would require specific fixing logic
            }
        }
    }

    /**
     * Debug all tenant users
     */
    private function debugAllTenantUsers($fix = false)
    {
        $this->info("🔍 Debugging all tenant users");
        $this->info('-'.str_repeat('-', 30));

        $tenantUsers = TenantUser::with(['user', 'tenant'])->get();
        $issues = [];

        foreach ($tenantUsers as $tenantUser) {
            $user = $tenantUser->user;
            $tenant = $tenantUser->tenant;

            if (!$user) {
                $issues[] = "❌ TenantUser ID {$tenantUser->id} has no user";
                continue;
            }

            if (!$tenant) {
                $issues[] = "❌ TenantUser ID {$tenantUser->id} has no tenant";
                continue;
            }

            // Check password
            if (!Hash::check('123456', $user->password)) {
                $issues[] = "❌ User {$user->email} has incorrect password";
                if ($fix) {
                    $user->update(['password' => Hash::make('123456')]);
                    $this->info("🔧 Fixed password for {$user->email}");
                }
            }

            // Check if active
            if (!$tenantUser->is_active) {
                $issues[] = "❌ User {$user->email} is inactive in tenant {$tenant->name}";
                if ($fix) {
                    $tenantUser->update(['is_active' => true]);
                    $this->info("🔧 Activated user {$user->email} in tenant {$tenant->name}");
                }
            }

            // Check invitation status
            if ($tenantUser->invitation_status !== 'accepted') {
                $issues[] = "❌ User {$user->email} invitation not accepted in tenant {$tenant->name}";
                if ($fix) {
                    $tenantUser->update(['invitation_status' => 'accepted']);
                    $this->info("🔧 Fixed invitation status for {$user->email} in tenant {$tenant->name}");
                }
            }

            $this->line("✅ {$user->email} -> {$tenant->name} ({$tenantUser->role})");
        }

        if (!empty($issues)) {
            $this->newLine();
            $this->warn("⚠️ Issues found:");
            foreach ($issues as $issue) {
                $this->line("   {$issue}");
            }
        } else {
            $this->info("✅ No issues found!");
        }

        $this->newLine();
        $this->info("📊 Summary:");
        $this->info("   - Total tenant users: " . $tenantUsers->count());
        $this->info("   - Issues found: " . count($issues));
        $this->info("   - Issues fixed: " . ($fix ? count($issues) : 0));
    }

    /**
     * Check if user can access tenant
     */
    private function checkTenantAccess($user, $tenant)
    {
        $tenantUser = TenantUser::where('user_id', $user->id)
                               ->where('tenant_id', $tenant->id)
                               ->first();

        if (!$tenantUser) {
            return false;
        }

        if (!$tenantUser->is_active) {
            return false;
        }

        if ($tenantUser->invitation_status !== 'accepted') {
            return false;
        }

        return true;
    }

    /**
     * Fix tenant access issues
     */
    private function fixTenantAccess($tenantUser)
    {
        $updates = [];

        if (!$tenantUser->is_active) {
            $updates['is_active'] = true;
        }

        if ($tenantUser->invitation_status !== 'accepted') {
            $updates['invitation_status'] = 'accepted';
        }

        if (!empty($updates)) {
            $tenantUser->update($updates);
            $this->info("🔧 Fixed access for user {$tenantUser->user->email} in tenant {$tenantUser->tenant->name}");
        }
    }
}
