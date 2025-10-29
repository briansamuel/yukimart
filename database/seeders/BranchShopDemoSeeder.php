<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BranchShop;
use App\Models\User;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BranchShopDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get current tenant
        $tenant = Tenant::where('subdomain', 'tenant2')->first();
        if (!$tenant) {
            $this->command->error('Tenant2 not found!');
            return;
        }

        $this->command->info('Creating demo branch shops for tenant: ' . $tenant->name);

        // Create branch shops
        $branchShops = [
            [
                'code' => 'FB-CN1',
                'name' => 'Fashion Boutique - Chi nhánh 1',
                'address' => '123 Nguyễn Huệ',
                'province' => 'TP.HCM',
                'district' => 'Quận 1',
                'ward' => 'Phường Bến Nghé',
                'phone' => '028-3822-1234',
                'email' => 'chinhanh1@fashionboutique.com',
                'shop_type' => 'flagship',
                'status' => 'active',
                'sort_order' => 1,
                'opening_time' => '08:00',
                'closing_time' => '22:00',
                'working_days' => json_encode(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']),
                'area' => 450.00,
                'staff_count' => 25,
                'has_delivery' => true,
                'delivery_radius' => 15.00,
                'delivery_fee' => 25000.00,
                'latitude' => 10.7769,
                'longitude' => 106.7009,
                'description' => 'Chi nhánh flagship chính tại trung tâm thành phố'
            ],
            [
                'code' => 'FB-CN2',
                'name' => 'Fashion Boutique - Chi nhánh 2',
                'address' => '456 Lê Lợi',
                'province' => 'TP.HCM',
                'district' => 'Quận 3',
                'ward' => 'Phường Võ Thị Sáu',
                'phone' => '028-3822-5678',
                'email' => 'chinhanh2@fashionboutique.com',
                'shop_type' => 'standard',
                'status' => 'active',
                'sort_order' => 2,
                'opening_time' => '09:00',
                'closing_time' => '21:00',
                'working_days' => json_encode(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']),
                'area' => 300.00,
                'staff_count' => 15,
                'has_delivery' => true,
                'delivery_radius' => 10.00,
                'delivery_fee' => 20000.00,
                'latitude' => 10.7891,
                'longitude' => 106.6917,
                'description' => 'Chi nhánh tiêu chuẩn tại khu vực Quận 3'
            ],
            [
                'code' => 'FB-CN3',
                'name' => 'Fashion Boutique - Chi nhánh 3',
                'address' => '789 Võ Văn Tần',
                'province' => 'TP.HCM',
                'district' => 'Quận 10',
                'ward' => 'Phường 6',
                'phone' => '028-3822-9012',
                'email' => 'chinhanh3@fashionboutique.com',
                'shop_type' => 'mini',
                'status' => 'active',
                'sort_order' => 3,
                'opening_time' => '10:00',
                'closing_time' => '20:00',
                'working_days' => json_encode(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']),
                'area' => 150.00,
                'staff_count' => 8,
                'has_delivery' => false,
                'delivery_radius' => 0.00,
                'delivery_fee' => 0.00,
                'latitude' => 10.7743,
                'longitude' => 106.6669,
                'description' => 'Chi nhánh mini tại khu vực Quận 10'
            ],
            [
                'code' => 'FB-CN4',
                'name' => 'Fashion Boutique - Chi nhánh 4',
                'address' => '321 Cách Mạng Tháng 8',
                'province' => 'TP.HCM',
                'district' => 'Quận Tân Bình',
                'ward' => 'Phường 12',
                'phone' => '028-3822-3456',
                'email' => 'chinhanh4@fashionboutique.com',
                'shop_type' => 'kiosk',
                'status' => 'maintenance',
                'sort_order' => 4,
                'opening_time' => '10:00',
                'closing_time' => '18:00',
                'working_days' => json_encode(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']),
                'area' => 50.00,
                'staff_count' => 3,
                'has_delivery' => false,
                'delivery_radius' => 0.00,
                'delivery_fee' => 0.00,
                'latitude' => 10.8012,
                'longitude' => 106.6525,
                'description' => 'Kiosk tại sân bay Tân Sơn Nhất'
            ]
        ];

        $createdBranchShops = [];
        foreach ($branchShops as $branchShopData) {
            // Add tenant_id and timestamps
            $branchShopData['tenant_id'] = $tenant->id;
            $branchShopData['created_at'] = Carbon::now();
            $branchShopData['updated_at'] = Carbon::now();

            $branchShop = BranchShop::create($branchShopData);
            $createdBranchShops[] = $branchShop;

            $this->command->info("Created branch shop: {$branchShop->name} ({$branchShop->code})");
        }

        // Get users to assign to branch shops
        $users = User::where('tenant_id', $tenant->id)->get();
        
        if ($users->isEmpty()) {
            $this->command->warn('No users found for tenant. Skipping user assignments.');
            return;
        }

        $this->command->info('Assigning users to branch shops...');

        // Assign users to branch shops
        foreach ($users as $index => $user) {
            $branchShopIndex = $index % count($createdBranchShops);
            $branchShop = $createdBranchShops[$branchShopIndex];
            
            // Skip maintenance branch shop
            if ($branchShop->status === 'maintenance') {
                $branchShopIndex = 0; // Assign to first branch shop instead
                $branchShop = $createdBranchShops[$branchShopIndex];
            }
            
            // Determine role based on user email
            $role = 'staff';
            if (str_contains($user->email, 'admin')) {
                $role = 'manager';
            } elseif (str_contains($user->email, 'manager')) {
                $role = 'manager';
            } elseif (str_contains($user->email, 'owner')) {
                $role = 'manager';
            }
            
            // Set primary branch shop for first assignment
            $isPrimary = !DB::table('user_branch_shops')
                ->where('user_id', $user->id)
                ->exists();
            
            // Create user-branch shop relationship
            DB::table('user_branch_shops')->insert([
                'user_id' => $user->id,
                'branch_shop_id' => $branchShop->id,
                'role_in_shop' => $role,
                'start_date' => Carbon::now()->subDays(rand(1, 30)),
                'is_active' => true,
                'is_primary' => $isPrimary,
                'assigned_by' => $users->first()->id, // Assign by first user (admin)
                'assigned_at' => Carbon::now(),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
            
            $this->command->info("Assigned {$user->name} ({$user->email}) to {$branchShop->name} as {$role}" . ($isPrimary ? ' (PRIMARY)' : ''));
            
            // Assign admin users to multiple branch shops
            if ($role === 'manager' && count($createdBranchShops) > 1) {
                $secondBranchShop = $createdBranchShops[($branchShopIndex + 1) % count($createdBranchShops)];
                
                // Skip maintenance branch shop
                if ($secondBranchShop->status === 'maintenance') {
                    $secondBranchShop = $createdBranchShops[($branchShopIndex + 2) % count($createdBranchShops)];
                }
                
                if ($secondBranchShop->id !== $branchShop->id) {
                    DB::table('user_branch_shops')->insert([
                        'user_id' => $user->id,
                        'branch_shop_id' => $secondBranchShop->id,
                        'role_in_shop' => 'manager',
                        'start_date' => Carbon::now()->subDays(rand(1, 15)),
                        'is_active' => true,
                        'is_primary' => false,
                        'assigned_by' => $users->first()->id,
                        'assigned_at' => Carbon::now(),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now()
                    ]);
                    
                    $this->command->info("Also assigned {$user->name} to {$secondBranchShop->name} as manager (SECONDARY)");
                }
            }
        }

        $this->command->info('Branch shop demo data created successfully!');
        $this->command->info('Summary:');
        $this->command->info('- Created ' . count($createdBranchShops) . ' branch shops');
        $this->command->info('- Assigned ' . $users->count() . ' users to branch shops');
        $this->command->info('- Active branch shops: ' . collect($createdBranchShops)->where('status', 'active')->count());
    }
}
