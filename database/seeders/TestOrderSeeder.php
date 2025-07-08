<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use App\Models\BranchShop;

class TestOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tạo user test nếu chưa có
        $user = User::first();
        if (!$user) {
            $user = User::create([
                'username' => 'admin',
                'email' => 'admin@test.com',
                'password' => bcrypt('password'),
                'full_name' => 'Admin Test',
                'address' => 'Test Address',
                'phone' => '0123456789',
                'birth_date' => now(),
                'active_code' => 'active',
                'group_id' => '1',
                'status' => 'active',
            ]);
        }

        // Tạo branch shop test nếu chưa có
        $branchShop = BranchShop::first();
        if (!$branchShop) {
            $branchShop = BranchShop::create([
                'name' => 'Chi nhánh test',
                'address' => 'Địa chỉ test',
                'phone' => '0123456789',
                'status' => 'active',
            ]);
        }

        // Lấy customer đầu tiên
        $customer = Customer::first();
        if (!$customer) {
            $customer = Customer::create([
                'customer_code' => 'KH000001',
                'name' => 'Test Customer',
                'phone' => '0123456789',
                'email' => 'test@customer.com',
                'status' => 'active',
            ]);
        }

        // Tạo một số đơn hàng test
        $orders = [
            [
                'order_code' => 'HD008076',
                'customer_id' => $customer->id,
                'branch_shop_id' => $branchShop->id,
                'created_by' => $user->id,
                'sold_by' => $user->id,
                'total_amount' => 699000,
                'discount_amount' => 0,
                'final_amount' => 699000,
                'amount_paid' => 699000,
                'shipping_fee' => 0,
                'total_quantity' => 1,
                'status' => 'completed',
                'payment_status' => 'paid',
                'payment_method' => 'cash',
                'payment_date' => '2022-02-21 19:26:00',
                'delivery_status' => 'delivered',
                'created_at' => '2022-02-21 19:26:00',
            ],
            [
                'order_code' => 'HD005156',
                'customer_id' => $customer->id,
                'branch_shop_id' => $branchShop->id,
                'created_by' => $user->id,
                'sold_by' => $user->id,
                'total_amount' => 431000,
                'discount_amount' => 0,
                'final_amount' => 431000,
                'amount_paid' => 431000,
                'shipping_fee' => 0,
                'total_quantity' => 1,
                'status' => 'completed',
                'payment_status' => 'paid',
                'payment_method' => 'cash',
                'payment_date' => '2021-10-30 16:03:00',
                'delivery_status' => 'delivered',
                'created_at' => '2021-10-30 16:03:00',
            ],
            [
                'order_code' => 'HD004380',
                'customer_id' => $customer->id,
                'branch_shop_id' => $branchShop->id,
                'created_by' => $user->id,
                'sold_by' => $user->id,
                'total_amount' => 699000,
                'discount_amount' => 0,
                'final_amount' => 699000,
                'amount_paid' => 699000,
                'shipping_fee' => 0,
                'total_quantity' => 1,
                'status' => 'completed',
                'payment_status' => 'paid',
                'payment_method' => 'cash',
                'payment_date' => '2021-09-24 11:06:00',
                'delivery_status' => 'delivered',
                'created_at' => '2021-09-24 11:06:00',
            ],
            // Thêm đơn hàng có nợ để test
            [
                'order_code' => 'HD000001',
                'customer_id' => $customer->id,
                'branch_shop_id' => $branchShop->id,
                'created_by' => $user->id,
                'sold_by' => $user->id,
                'total_amount' => 500000,
                'discount_amount' => 0,
                'final_amount' => 500000,
                'amount_paid' => 200000,
                'shipping_fee' => 0,
                'total_quantity' => 1,
                'status' => 'completed',
                'payment_status' => 'partial',
                'payment_method' => 'cash',
                'payment_date' => '2025-07-01 10:00:00',
                'delivery_status' => 'delivered',
                'created_at' => '2025-07-01 10:00:00',
            ],
            [
                'order_code' => 'HD000002',
                'customer_id' => $customer->id,
                'branch_shop_id' => $branchShop->id,
                'created_by' => $user->id,
                'sold_by' => $user->id,
                'total_amount' => 300000,
                'discount_amount' => 0,
                'final_amount' => 300000,
                'amount_paid' => 0,
                'shipping_fee' => 0,
                'total_quantity' => 1,
                'status' => 'processing',
                'payment_status' => 'unpaid',
                'payment_method' => 'cash',
                'payment_date' => null,
                'delivery_status' => 'pending',
                'created_at' => '2025-07-02 15:30:00',
            ],
        ];

        foreach ($orders as $orderData) {
            Order::create($orderData);
        }
    }
}
