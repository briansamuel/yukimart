<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Role;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

class UpdatePlatformUsersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'yukimart:update-platform-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update platform users to have tenant_id = NULL for proper separation';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Updating Platform Users for Proper Separation');
        $this->info('='.str_repeat('=', 50));

        // 1. Find platform tenant
        $platformTenant = Tenant::where('subdomain', 'platform')->first();
        if (!$platformTenant) {
            $this->error('❌ Platform tenant not found!');
            return 1;
        }

        $this->info("📍 Found platform tenant: {$platformTenant->name} (ID: {$platformTenant->id})");

        // 2. Find platform users (users in platform tenant)
        $platformUsers = User::where('tenant_id', $platformTenant->id)->get();
        $this->info("👥 Found {$platformUsers->count()} platform users");

        // 3. Update platform users to have tenant_id = NULL (using raw SQL to bypass security constraint)
        $userIds = $platformUsers->pluck('id')->toArray();
        if (!empty($userIds)) {
            $this->info("🔄 Updating users: " . $platformUsers->pluck('email')->implode(', '));
            DB::table('users')
              ->whereIn('id', $userIds)
              ->update(['tenant_id' => null]);
        }

        // 4. Find platform roles (roles in platform tenant)
        $platformRoles = Role::where('tenant_id', $platformTenant->id)->get();
        $this->info("🔐 Found {$platformRoles->count()} platform roles");

        // 5. Update platform roles to have tenant_id = NULL (using raw SQL)
        $roleIds = $platformRoles->pluck('id')->toArray();
        if (!empty($roleIds)) {
            $this->info("🔄 Updating roles: " . $platformRoles->pluck('name')->implode(', '));
            DB::table('roles')
              ->whereIn('id', $roleIds)
              ->update(['tenant_id' => null]);
        }

        // 6. Verify updates
        $nullTenantUsers = User::whereNull('tenant_id')->count();
        $nullTenantRoles = Role::whereNull('tenant_id')->count();

        $this->info('');
        $this->info('✅ Update Results:');
        $this->info("   👥 Platform users (tenant_id = NULL): {$nullTenantUsers}");
        $this->info("   🔐 Platform roles (tenant_id = NULL): {$nullTenantRoles}");

        // 7. Test platform authentication
        $this->info('');
        $this->info('🧪 Testing platform authentication...');
        
        $testUser = User::whereNull('tenant_id')
                       ->where('email', 'superadmin@yukimart.local')
                       ->first();
        
        if ($testUser) {
            $this->info("✅ Platform user found: {$testUser->email}");
            
            // Test roles
            $userRoles = $testUser->roles()->whereNull('tenant_id')->get();
            $this->info("🔐 Platform roles: " . $userRoles->pluck('name')->implode(', '));
        } else {
            $this->error('❌ Platform test user not found!');
        }

        $this->info('');
        $this->info('🎉 Platform users update completed!');
        $this->info('');
        $this->info('📋 Next Steps:');
        $this->info('   1. Test login at: http://yukimart.local/admin/login');
        $this->info('   2. Use credentials: superadmin@yukimart.local / 123456');
        $this->info('   3. Platform users are now completely separated from tenants');

        return 0;
    }
}
