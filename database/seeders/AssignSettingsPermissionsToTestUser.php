<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Database\Seeder;

class AssignSettingsPermissionsToTestUser extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Find test user
        $user = User::where('email', 'owner@techmart.local')->first();

        if (!$user) {
            $this->command->error("User owner@techmart.local not found!");
            return;
        }

        $this->command->info("Found user: {$user->email} (ID: {$user->id}, Tenant ID: {$user->tenant_id})");

        // Get all settings permissions with tenant guard and matching tenant_id
        $settingsPermissions = Permission::where('module', 'settings')
            ->where('guard_name', 'tenant')
            ->where('tenant_id', $user->tenant_id)
            ->get();

        if ($settingsPermissions->isEmpty()) {
            $this->command->error("No settings permissions found for tenant guard and tenant_id {$user->tenant_id}!");
            return;
        }

        $this->command->info("Found " . $settingsPermissions->count() . " settings permissions (tenant guard, tenant_id: {$user->tenant_id})");

        // Set team ID for Spatie multi-tenant support
        app()[\Spatie\Permission\PermissionRegistrar::class]->setPermissionsTeamId($user->tenant_id);

        // Assign all settings permissions to user
        // Spatie will automatically use the guard from the Permission model
        $user->givePermissionTo($settingsPermissions);

        $this->command->info("✅ Assigned all settings permissions to user: {$user->email}");

        // Verify
        $userPermissions = $user->permissions()->where('module', 'settings')->where('guard_name', 'tenant')->count();
        $this->command->info("✅ User now has {$userPermissions} settings permissions (tenant guard)");
    }
}

