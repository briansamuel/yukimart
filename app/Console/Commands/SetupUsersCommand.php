<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Database\Seeders\UserManagementSeeder;

class SetupUsersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'yukimart:setup-users 
                            {--force : Force update passwords even if users exist}
                            {--platform-only : Only create platform users}
                            {--tenant-only : Only update tenant users passwords}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup platform users and update tenant users passwords for YukiMart system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 YukiMart User Setup Command');
        $this->info('='.str_repeat('=', 50));

        // Check options
        $platformOnly = $this->option('platform-only');
        $tenantOnly = $this->option('tenant-only');
        $force = $this->option('force');

        if ($platformOnly && $tenantOnly) {
            $this->error('❌ Cannot use both --platform-only and --tenant-only options together');
            return 1;
        }

        // Confirmation
        if (!$force) {
            $this->warn('⚠️  This command will:');
            if (!$tenantOnly) {
                $this->warn('   - Create 5 platform users with default password: 123456');
            }
            if (!$platformOnly) {
                $this->warn('   - Update all tenant users passwords to: 123456');
            }
            
            if (!$this->confirm('Do you want to continue?')) {
                $this->info('❌ Operation cancelled');
                return 0;
            }
        }

        try {
            if ($platformOnly) {
                $this->info('📋 Creating Platform Users Only...');
                $this->call('db:seed', ['--class' => 'PlatformUsersSeeder']);
            } elseif ($tenantOnly) {
                $this->info('📋 Updating Tenant Users Passwords Only...');
                $this->call('db:seed', ['--class' => 'UpdateTenantUsersPasswordSeeder']);
            } else {
                $this->info('📋 Running Complete User Management Setup...');
                $this->call('db:seed', ['--class' => 'UserManagementSeeder']);
            }

            $this->newLine();
            $this->info('✅ User setup completed successfully!');
            
            // Display login information
            $this->displayLoginInfo($platformOnly, $tenantOnly);

            return 0;

        } catch (\Exception $e) {
            $this->error('❌ Error during user setup: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            return 1;
        }
    }

    /**
     * Display login information
     */
    private function displayLoginInfo($platformOnly, $tenantOnly)
    {
        $this->newLine();
        $this->info('🔐 Login Information:');
        $this->info('-'.str_repeat('-', 30));

        if (!$tenantOnly) {
            $this->info('🏢 Platform Users (Access: /admin/login):');
            $this->table(
                ['Email', 'Role', 'Password'],
                [
                    ['superadmin@yukimart.local', 'Super Administrator', '123456'],
                    ['admin@yukimart.local', 'Platform Administrator', '123456'],
                    ['dev@yukimart.local', 'Development Manager', '123456'],
                    ['manager@yukimart.local', 'Platform Manager', '123456'],
                    ['support@yukimart.local', 'System Support', '123456'],
                ]
            );
        }

        if (!$platformOnly) {
            $this->newLine();
            $this->info('🏪 Tenant Users (Access: /admin/login):');
            $this->info('   All tenant users now have password: 123456');
            $this->info('   Examples:');
            $this->table(
                ['Email', 'Tenant', 'Role', 'Password'],
                [
                    ['owner@techmart.local', 'TechMart', 'Owner', '123456'],
                    ['admin@techmart.local', 'TechMart', 'Admin', '123456'],
                    ['owner@fashion.local', 'Fashion', 'Owner', '123456'],
                    ['admin@fashion.local', 'Fashion', 'Admin', '123456'],
                ]
            );
        }

        $this->newLine();
        $this->info('🌐 Access URLs:');
        $this->info('   Platform: http://yukimart.local/admin/login');
        $this->info('   TechMart: http://tenant1.yukimart.local/admin/login');
        $this->info('   Fashion:  http://tenant2.yukimart.local/admin/login');

        $this->newLine();
        $this->info('💡 Tips:');
        $this->info('   - Platform users can switch between tenants');
        $this->info('   - Tenant users can only access their own tenant');
        $this->info('   - Use superadmin for full system access');
        $this->info('   - Change passwords after first login for security');
    }
}
