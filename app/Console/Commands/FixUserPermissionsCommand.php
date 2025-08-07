<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;

class FixUserPermissionsCommand extends Command
{
    protected $signature = 'fix:user-permissions {email=yukimart@gmail.com}';
    protected $description = 'Fix user permissions for dashboard access';

    public function handle()
    {
        $email = $this->argument('email');
        
        $this->info('🔧 Fixing user permissions for: ' . $email);
        $this->newLine();

        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error('User not found: ' . $email);
            return;
        }

        $this->info('👤 User found: ' . $user->full_name . ' (ID: ' . $user->id . ')');

        // Check if user is root
        if ($user->is_root) {
            $this->info('✅ User is already root - should have all permissions');
        } else {
            $this->info('🔧 Setting user as root...');
            $user->update(['is_root' => 1]);
            $this->info('✅ User set as root');
        }

        // Check roles
        $this->info('🔍 Checking roles...');
        $roles = $user->roles;
        
        if ($roles->count() > 0) {
            $this->info('✅ User has ' . $roles->count() . ' roles:');
            foreach ($roles as $role) {
                $this->line('  - ' . $role->name);
            }
        } else {
            $this->info('⚠️  User has no roles. Creating admin role...');
            
            // Create admin role if not exists
            $adminRole = Role::firstOrCreate([
                'name' => 'admin'
            ], [
                'display_name' => 'Administrator',
                'description' => 'Full system access',
                'status' => 'active'
            ]);

            // Assign admin role to user
            $user->roles()->attach($adminRole->id);
            $this->info('✅ Admin role assigned to user');
        }

        // Check permissions
        $this->info('🔍 Checking permissions...');
        
        // Create basic permissions if not exist
        $permissions = [
            'dashboard.view' => 'View Dashboard',
            'dashboard.stats' => 'View Dashboard Statistics',
            'orders.view' => 'View Orders',
            'customers.view' => 'View Customers',
            'products.view' => 'View Products',
        ];

        foreach ($permissions as $name => $description) {
            Permission::firstOrCreate([
                'name' => $name
            ], [
                'display_name' => $description,
                'description' => $description,
                'status' => 'active'
            ]);
        }

        $this->info('✅ Basic permissions created');

        // Assign all permissions to admin role
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $allPermissions = Permission::all();
            $adminRole->permissions()->sync($allPermissions->pluck('id'));
            $this->info('✅ All permissions assigned to admin role');
        }

        $this->newLine();
        $this->info('🎉 User permissions fixed successfully!');
        
        // Test user permissions
        $this->info('🧪 Testing user permissions...');
        $user = $user->fresh(['roles.permissions']);
        
        $this->line('User roles: ' . $user->roles->pluck('name')->join(', '));
        $this->line('User permissions: ' . $user->roles->flatMap->permissions->pluck('name')->unique()->count() . ' permissions');
        $this->line('Is root: ' . ($user->is_root ? 'Yes' : 'No'));
        
        $this->info('✅ Permission check completed!');
    }
}
