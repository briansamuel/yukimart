<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get existing roles
        $managerRole = \App\Models\Role::where('name', 'shop_manager')->first();
        $staffRole = \App\Models\Role::where('name', 'staff')->first();

        if (!$managerRole || !$staffRole) {
            $this->command->error('Shop Manager or Staff role not found. Please ensure roles exist in the database.');
            $this->command->info('Available roles: ' . \App\Models\Role::pluck('name')->implode(', '));
            return;
        }

        // Get users created by UserSeeder (those with emails containing 'manager_' or 'staff_')
        $managers = \App\Models\User::where('email', 'like', 'manager_%@yukimart.com')->get();
        $staff = \App\Models\User::where('email', 'like', 'staff_%@yukimart.com')->get();

        $this->command->info("Found {$managers->count()} managers and {$staff->count()} staff to assign roles.");

        // Assign manager role to managers
        foreach ($managers as $manager) {
            // Check if user already has this role
            if (!$manager->roles()->where('role_id', $managerRole->id)->exists()) {
                $manager->roles()->attach($managerRole->id, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $this->command->info("Assigned manager role to: {$manager->full_name}");
            } else {
                $this->command->info("Manager role already assigned to: {$manager->full_name}");
            }
        }

        // Assign staff role to staff
        foreach ($staff as $staffMember) {
            // Check if user already has this role
            if (!$staffMember->roles()->where('role_id', $staffRole->id)->exists()) {
                $staffMember->roles()->attach($staffRole->id, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $this->command->info("Assigned staff role to: {$staffMember->full_name}");
            } else {
                $this->command->info("Staff role already assigned to: {$staffMember->full_name}");
            }
        }

        $this->command->info('User roles assignment completed successfully!');
        $this->command->info("Total managers with role: " . \App\Models\User::whereHas('roles', function($q) use ($managerRole) {
            $q->where('role_id', $managerRole->id);
        })->count());
        $this->command->info("Total staff with role: " . \App\Models\User::whereHas('roles', function($q) use ($staffRole) {
            $q->where('role_id', $staffRole->id);
        })->count());
    }
}
