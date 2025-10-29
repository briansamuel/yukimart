<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class UserManagementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 Starting User Management Seeding...');
        $this->command->info('='.str_repeat('=', 50));

        // 1. Create Platform Users
        $this->command->info('');
        $this->command->info('📋 Step 1: Creating Platform Users');
        $this->command->info('-'.str_repeat('-', 30));
        $this->call(PlatformUsersSeeder::class);

        // 2. Update Tenant Users Passwords
        $this->command->info('');
        $this->command->info('📋 Step 2: Updating Tenant Users Passwords');
        $this->command->info('-'.str_repeat('-', 30));
        $this->call(UpdateTenantUsersPasswordSeeder::class);

        $this->command->info('');
        $this->command->info('='.str_repeat('=', 50));
        $this->command->info('🎉 User Management Seeding Completed Successfully!');
        $this->command->info('');
        $this->command->info('📋 Platform Users Created:');
        $this->command->info('   🔑 superadmin@yukimart.local (Super Administrator)');
        $this->command->info('   🔑 admin@yukimart.local (Platform Administrator)');
        $this->command->info('   🔑 dev@yukimart.local (Development Manager)');
        $this->command->info('   🔑 manager@yukimart.local (Platform Manager)');
        $this->command->info('   🔑 support@yukimart.local (System Support)');
        $this->command->info('');
        $this->command->info('🔐 All users have default password: 123456');
        $this->command->info('');
        $this->command->info('🏢 Platform Users Access:');
        $this->command->info('   - Can access Platform Dashboard');
        $this->command->info('   - Can manage tenants');
        $this->command->info('   - Can switch to any tenant');
        $this->command->info('   - Have system-wide permissions');
        $this->command->info('');
        $this->command->info('🏪 Tenant Users Access:');
        $this->command->info('   - Can access their tenant dashboard');
        $this->command->info('   - Role-based permissions within tenant');
        $this->command->info('   - Cannot access other tenants');
        $this->command->info('');
        $this->command->info('🚀 Ready to login and test the system!');
    }
}
