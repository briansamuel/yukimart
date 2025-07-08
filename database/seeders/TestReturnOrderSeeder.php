<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use App\Models\BranchShop;

class TestReturnOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first();
        $branchShop = BranchShop::first();
        $customer = Customer::first();

        // Tạo đơn trả hàng để test
        $returnOrders = [
            [
                'order_code' => 'RT000001',
                'customer_id' => $customer->id,
                'branch_shop_id' => $branchShop->id,
                'created_by' => $user->id,
                'sold_by' => $user->id,
                'total_amount' => 200000,
                'discount_amount' => 0,
                'final_amount' => 200000,
                'amount_paid' => 200000,
                'shipping_fee' => 0,
                'total_quantity' => 1,
                'status' => 'returned', // Đơn trả hàng
                'payment_status' => 'refunded',
                'payment_method' => 'cash',
                'payment_date' => '2025-06-15 14:00:00',
                'delivery_status' => 'returned',
                'created_at' => '2025-06-15 14:00:00',
            ],
            [
                'order_code' => 'RT000002',
                'customer_id' => $customer->id,
                'branch_shop_id' => $branchShop->id,
                'created_by' => $user->id,
                'sold_by' => $user->id,
                'total_amount' => 150000,
                'discount_amount' => 0,
                'final_amount' => 150000,
                'amount_paid' => 150000,
                'shipping_fee' => 0,
                'total_quantity' => 1,
                'status' => 'returned', // Đơn trả hàng
                'payment_status' => 'refunded',
                'payment_method' => 'cash',
                'payment_date' => '2025-05-20 16:30:00',
                'delivery_status' => 'returned',
                'created_at' => '2025-05-20 16:30:00',
            ],
        ];

        foreach ($returnOrders as $orderData) {
            Order::create($orderData);
        }

        $this->command->info('Created return orders for testing');
    }
}
