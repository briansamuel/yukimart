<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\TenantUser;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UpdateTenantUsersPasswordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🔐 Updating Tenant Users Passwords...');

        // Get all users who have tenant relationships
        $tenantUsers = TenantUser::with('user')->get();
        
        $updatedCount = 0;
        $skippedCount = 0;

        foreach ($tenantUsers as $tenantUser) {
            $user = $tenantUser->user;
            
            if ($user) {
                // Check if password is already 123456
                if (Hash::check('123456', $user->password)) {
                    $this->command->info("ℹ️  User {$user->email} already has correct password");
                    $skippedCount++;
                    continue;
                }

                // Update password to 123456
                $user->update([
                    'password' => Hash::make('123456'),
                    'updated_at' => now()
                ]);

                $this->command->info("✅ Updated password for tenant user: {$user->name} ({$user->email})");
                $updatedCount++;
            }
        }

        // Also update any existing test users that might not be in tenant_users table
        $testUsers = [
            'owner@techmart.local',
            'admin@techmart.local', 
            'manager@techmart.local',
            'staff@techmart.local',
            'owner@fashion.local',
            'admin@fashion.local',
            'manager@fashion.local', 
            'staff@fashion.local',
            'owner@foodbev.local',
            'admin@foodbev.local',
            'owner@hellomart.local',
            'admin@hellomart.local',
            'owner@bibomart.local',
            'admin@bibomart.local'
        ];

        foreach ($testUsers as $email) {
            $user = User::where('email', $email)->first();
            if ($user) {
                if (!Hash::check('123456', $user->password)) {
                    $user->update([
                        'password' => Hash::make('123456'),
                        'updated_at' => now()
                    ]);
                    $this->command->info("✅ Updated password for test user: {$user->name} ({$user->email})");
                    $updatedCount++;
                } else {
                    $this->command->info("ℹ️  Test user {$user->email} already has correct password");
                    $skippedCount++;
                }
            }
        }

        $this->command->info("🎉 Password update completed!");
        $this->command->info("📊 Summary:");
        $this->command->info("   - Updated: {$updatedCount} users");
        $this->command->info("   - Skipped: {$skippedCount} users (already correct)");
        $this->command->info("   - Default password: 123456");
    }
}
