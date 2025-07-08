<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get all branch shops
        $branchShops = \App\Models\BranchShop::all();

        if ($branchShops->isEmpty()) {
            $this->command->error('No branch shops found. Please create branch shops first.');
            return;
        }

        // Create managers and staff for each branch
        foreach ($branchShops as $branchShop) {
            // Create 1 manager per branch
            $manager = \App\Models\User::create([
                'username' => 'manager_' . strtolower(str_replace(' ', '_', $branchShop->name)),
                'full_name' => 'Quản lý ' . $branchShop->name,
                'email' => 'manager_' . strtolower(str_replace(' ', '_', $branchShop->name)) . '@yukimart.com',
                'password' => \Illuminate\Support\Facades\Hash::make('password123'),
                'phone' => '0' . rand(900000000, 999999999),
                'address' => 'Địa chỉ quản lý ' . $branchShop->name,
                // Role will be assigned by UserRoleSeeder
                'active_code' => '',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Assign manager to branch
            \Illuminate\Support\Facades\DB::table('user_branch_shops')->insert([
                'user_id' => $manager->id,
                'branch_shop_id' => $branchShop->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Create 2-3 staff per branch
            $staffCount = rand(2, 3);
            for ($i = 1; $i <= $staffCount; $i++) {
                $staff = \App\Models\User::create([
                    'username' => 'staff_' . $i . '_' . strtolower(str_replace(' ', '_', $branchShop->name)),
                    'full_name' => 'Nhân viên ' . $i . ' - ' . $branchShop->name,
                    'email' => 'staff_' . $i . '_' . strtolower(str_replace(' ', '_', $branchShop->name)) . '@yukimart.com',
                    'password' => \Illuminate\Support\Facades\Hash::make('password123'),
                    'phone' => '0' . rand(900000000, 999999999),
                    'address' => 'Địa chỉ nhân viên ' . $i . ' - ' . $branchShop->name,
                    // Role will be assigned by UserRoleSeeder
                    'active_code' => '',
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Assign staff to branch
                \Illuminate\Support\Facades\DB::table('user_branch_shops')->insert([
                    'user_id' => $staff->id,
                    'branch_shop_id' => $branchShop->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $this->command->info('Users created successfully!');
        $this->command->info('Total managers: ' . \App\Models\User::where('email', 'like', 'manager_%@yukimart.com')->count());
        $this->command->info('Total staff: ' . \App\Models\User::where('email', 'like', 'staff_%@yukimart.com')->count());
        $this->command->info('Run UserRoleSeeder to assign roles to these users.');
    }
}
