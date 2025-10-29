<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class SimplePlatformUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 Creating Platform Users (Simple Version)...');

        // Create or get platform tenant
        $platformTenant = $this->createPlatformTenant();

        // Create platform users without roles first
        $platformUsers = [
            [
                'full_name' => 'Super Administrator',
                'username' => 'superadmin',
                'email' => 'superadmin@yukimart.local',
                'phone' => '+84 901 234 567',
                'address' => '123 Admin Street, District 1, Ho Chi Minh City',
                'status' => 'active'
            ],
            [
                'full_name' => 'Platform Administrator',
                'username' => 'admin',
                'email' => 'admin@yukimart.local',
                'phone' => '+84 901 234 568',
                'address' => '124 Admin Street, District 1, Ho Chi Minh City',
                'status' => 'active'
            ],
            [
                'full_name' => 'Development Manager',
                'username' => 'dev',
                'email' => 'dev@yukimart.local',
                'phone' => '+84 901 234 569',
                'address' => '125 Admin Street, District 1, Ho Chi Minh City',
                'status' => 'active'
            ],
            [
                'full_name' => 'Platform Manager',
                'username' => 'manager',
                'email' => 'manager@yukimart.local',
                'phone' => '+84 901 234 570',
                'address' => '126 Admin Street, District 1, Ho Chi Minh City',
                'status' => 'active'
            ],
            [
                'full_name' => 'System Support',
                'username' => 'support',
                'email' => 'support@yukimart.local',
                'phone' => '+84 901 234 571',
                'address' => '127 Admin Street, District 1, Ho Chi Minh City',
                'status' => 'active'
            ]
        ];

        foreach ($platformUsers as $userData) {
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'tenant_id' => $platformTenant->id, // Platform users belong to platform tenant
                    'username' => $userData['username'],
                    'full_name' => $userData['full_name'],
                    'email' => $userData['email'],
                    'email_verified_at' => now(),
                    'password' => Hash::make('123456'),
                    'phone' => $userData['phone'],
                    'address' => $userData['address'],
                    'avatar' => null,
                    'status' => $userData['status'],
                    'birth_date' => now(),
                    'active_code' => '',
                    'is_root' => false,
                    'last_visit' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            $this->command->info("✅ Created platform user: {$userData['full_name']} ({$userData['email']})");
        }

        // Create platform roles directly in database to avoid foreign key issues
        $this->createPlatformRoles($platformTenant);

        // Assign roles to users
        $this->assignRolesToUsers();

        $this->command->info('🎉 Platform users creation completed!');
    }

    /**
     * Create or get platform tenant
     */
    private function createPlatformTenant()
    {
        $platformTenant = DB::table('tenants')
                           ->where('subdomain', 'platform')
                           ->first();

        if (!$platformTenant) {
            $tenantId = DB::table('tenants')->insertGetId([
                'name' => 'Platform Management',
                'slug' => 'platform-management',
                'subdomain' => 'platform',
                'domain' => 'platform.yukimart.local',
                'database_name' => 'yukimart_platform',
                'status' => 'active',
                'settings' => json_encode([
                    'is_platform' => true,
                    'description' => 'Platform management tenant for system administrators'
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $platformTenant = (object) [
                'id' => $tenantId,
                'name' => 'Platform Management',
                'subdomain' => 'platform'
            ];

            $this->command->info("✅ Created platform tenant: Platform Management");
        } else {
            $this->command->info("ℹ️  Platform tenant already exists");
        }

        return $platformTenant;
    }

    /**
     * Create platform roles directly in database
     */
    private function createPlatformRoles($platformTenant): void
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
            // Check if role exists
            $existingRole = DB::table('roles')
                             ->where('name', $roleData['name'])
                             ->where('tenant_id', $platformTenant->id)
                             ->first();

            if (!$existingRole) {
                DB::table('roles')->insert([
                    'name' => $roleData['name'],
                    'display_name' => $roleData['display_name'],
                    'description' => $roleData['description'],
                    'tenant_id' => $platformTenant->id,
                    'is_active' => true,
                    'sort_order' => 0,
                    'settings' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $this->command->info("✅ Created platform role: {$roleData['display_name']}");
            } else {
                $this->command->info("ℹ️  Platform role already exists: {$roleData['display_name']}");
            }
        }
    }

    /**
     * Assign roles to users
     */
    private function assignRolesToUsers(): void
    {
        $userRoleMap = [
            'superadmin@yukimart.local' => 'superadmin',
            'admin@yukimart.local' => 'admin',
            'dev@yukimart.local' => 'dev',
            'manager@yukimart.local' => 'manager',
            'support@yukimart.local' => 'support',
        ];

        foreach ($userRoleMap as $email => $roleName) {
            $user = User::where('email', $email)->first();
            $role = DB::table('roles')
                     ->where('name', $roleName)
                     ->where('tenant_id', function($query) {
                         $query->select('id')
                               ->from('tenants')
                               ->where('subdomain', 'platform')
                               ->limit(1);
                     })
                     ->first();

            if ($user && $role) {
                // Check if role assignment exists
                $existingAssignment = DB::table('user_roles')
                                       ->where('user_id', $user->id)
                                       ->where('role_id', $role->id)
                                       ->first();

                if (!$existingAssignment) {
                    DB::table('user_roles')->insert([
                        'user_id' => $user->id,
                        'role_id' => $role->id,
                        'assigned_at' => now(),
                        'assigned_by' => $user->id, // Self-assigned for platform users
                        'expires_at' => null,
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $this->command->info("✅ Assigned role {$roleName} to {$email}");
                } else {
                    $this->command->info("ℹ️  Role {$roleName} already assigned to {$email}");
                }
            }
        }
    }
}
