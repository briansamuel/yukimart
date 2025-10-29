<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class PlatformUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 Creating Platform Users...');

        // Ensure roles exist
        $this->createRolesIfNotExist();

        // Create platform users
        $platformUsers = [
            [
                'name' => 'Super Administrator',
                'email' => 'superadmin@yukimart.local',
                'role' => 'superadmin',
                'phone' => '+84 901 234 567',
                'address' => '123 Admin Street, District 1, Ho Chi Minh City',
                'status' => 'active'
            ],
            [
                'name' => 'Platform Administrator',
                'email' => 'admin@yukimart.local',
                'role' => 'admin',
                'phone' => '+84 901 234 568',
                'address' => '124 Admin Street, District 1, Ho Chi Minh City',
                'status' => 'active'
            ],
            [
                'name' => 'Development Manager',
                'email' => 'dev@yukimart.local',
                'role' => 'dev',
                'phone' => '+84 901 234 569',
                'address' => '125 Admin Street, District 1, Ho Chi Minh City',
                'status' => 'active'
            ],
            [
                'name' => 'Platform Manager',
                'email' => 'manager@yukimart.local',
                'role' => 'manager',
                'phone' => '+84 901 234 570',
                'address' => '126 Admin Street, District 1, Ho Chi Minh City',
                'status' => 'active'
            ],
            [
                'name' => 'System Support',
                'email' => 'support@yukimart.local',
                'role' => 'support',
                'phone' => '+84 901 234 571',
                'address' => '127 Admin Street, District 1, Ho Chi Minh City',
                'status' => 'active'
            ]
        ];

        foreach ($platformUsers as $userData) {
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'email' => $userData['email'],
                    'email_verified_at' => now(),
                    'password' => Hash::make('123456'),
                    'phone' => $userData['phone'],
                    'address' => $userData['address'],
                    'avatar' => null,
                    'status' => $userData['status'],
                    'last_login_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            // Assign role to user
            $role = Role::where('name', $userData['role'])->first();
            if ($role && !$user->hasRole($userData['role'])) {
                $user->assignRole($userData['role']);
                $this->command->info("✅ Created platform user: {$userData['name']} ({$userData['email']}) with role: {$userData['role']}");
            } else {
                $this->command->info("ℹ️  Platform user already exists: {$userData['email']}");
            }
        }

        $this->command->info('🎉 Platform users creation completed!');
    }

    /**
     * Create roles if they don't exist
     */
    private function createRolesIfNotExist(): void
    {
        $roles = [
            [
                'name' => 'superadmin',
                'display_name' => 'Super Administrator',
                'description' => 'Has complete access to all platform and tenant features'
            ],
            [
                'name' => 'admin',
                'display_name' => 'Platform Administrator',
                'description' => 'Has administrative access to platform features'
            ],
            [
                'name' => 'dev',
                'display_name' => 'Developer',
                'description' => 'Has development and technical access to platform'
            ],
            [
                'name' => 'manager',
                'display_name' => 'Platform Manager',
                'description' => 'Has management access to platform operations'
            ],
            [
                'name' => 'support',
                'display_name' => 'Support Staff',
                'description' => 'Has support access to help tenants and users'
            ]
        ];

        foreach ($roles as $roleData) {
            Role::firstOrCreate(
                ['name' => $roleData['name'], 'tenant_id' => null],
                [
                    'name' => $roleData['name'],
                    'display_name' => $roleData['display_name'],
                    'description' => $roleData['description'],
                    'tenant_id' => null, // Platform roles don't belong to any tenant
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        $this->command->info('✅ Platform roles ensured');
    }
}
