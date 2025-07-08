<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\Customer;
use App\Models\BranchShop;
use App\Models\User;

class OrderWithPaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure we have required data
        if (Customer::count() === 0) {
            Customer::factory(20)->create();
        }
        
        if (BranchShop::count() === 0) {
            BranchShop::factory(3)->create();
        }
        
        if (User::count() === 0) {
            User::factory(5)->create();
        }

        // Create orders with different payment methods and statuses
        
        // 1. Cash orders (mostly completed and paid)
        Order::factory(15)
            ->direct()
            ->paid()
            ->create(['payment_method' => 'cash']);

        // 2. Card orders (mix of paid and processing)
        Order::factory(10)
            ->online()
            ->paid()
            ->create(['payment_method' => 'card']);
            
        Order::factory(5)
            ->processing()
            ->create(['payment_method' => 'card']);

        // 3. Transfer orders (mostly paid)
        Order::factory(8)
            ->online()
            ->paid()
            ->create(['payment_method' => 'transfer']);

        // 4. COD orders (mix of statuses)
        Order::factory(12)
            ->online()
            ->create([
                'payment_method' => 'cod',
                'payment_status' => 'unpaid'
            ]);
            
        Order::factory(8)
            ->completed()
            ->paid()
            ->create(['payment_method' => 'cod']);

        // 5. E-wallet orders
        Order::factory(6)
            ->online()
            ->paid()
            ->create(['payment_method' => 'e_wallet']);

        // 6. Credit orders (unpaid and overdue)
        Order::factory(10)
            ->credit()
            ->create();

        // 7. Installment orders
        Order::factory(5)
            ->create([
                'payment_method' => 'installment',
                'payment_status' => 'partial',
                'due_date' => now()->addDays(rand(15, 45))
            ]);

        // 8. Mixed payment orders
        Order::factory(3)
            ->highValue()
            ->create([
                'payment_method' => 'mixed',
                'payment_status' => 'partial'
            ]);

        // 9. Voucher orders
        Order::factory(4)
            ->create([
                'payment_method' => 'voucher',
                'payment_status' => 'paid',
                'amount_paid' => 0 // Paid with voucher
            ]);

        // 10. Points orders
        Order::factory(3)
            ->create([
                'payment_method' => 'points',
                'payment_status' => 'paid',
                'amount_paid' => 0 // Paid with points
            ]);

        // 11. Recent orders with various payment methods
        Order::factory(20)
            ->recent()
            ->create();

        // 12. High value orders with different payment methods
        Order::factory(8)
            ->highValue()
            ->create();

        // 13. Cancelled orders (should be unpaid)
        Order::factory(5)
            ->cancelled()
            ->create();

        // 14. POS orders
        Order::factory(12)
            ->pos()
            ->create();

        // 15. Overdue credit orders
        Order::factory(6)
            ->create([
                'payment_method' => 'credit',
                'payment_status' => 'unpaid',
                'due_date' => now()->subDays(rand(1, 30)),
                'amount_paid' => 0
            ]);

        // 16. Partially paid orders
        Order::factory(8)
            ->processing()
            ->create()
            ->each(function ($order) {
                $partialAmount = $order->final_amount * 0.5; // 50% paid
                $order->update([
                    'amount_paid' => $partialAmount,
                    'payment_status' => 'partial',
                    'payment_date' => now()->subDays(rand(1, 10)),
                    'payment_reference' => 'PARTIAL_' . strtoupper(uniqid())
                ]);
            });

        // 17. Overpaid orders (rare cases)
        Order::factory(2)
            ->completed()
            ->create()
            ->each(function ($order) {
                $overpaidAmount = $order->final_amount * 1.1; // 110% paid
                $order->update([
                    'amount_paid' => $overpaidAmount,
                    'payment_status' => 'overpaid',
                    'payment_date' => now()->subDays(rand(1, 5)),
                    'payment_reference' => 'OVERPAID_' . strtoupper(uniqid()),
                    'payment_notes' => 'Customer overpaid, refund pending'
                ]);
            });

        // 18. Refunded orders
        Order::factory(3)
            ->create([
                'status' => 'cancelled',
                'payment_status' => 'refunded',
                'payment_method' => 'card',
                'payment_notes' => 'Order cancelled, payment refunded'
            ]);

        $this->command->info('Created orders with various payment methods and statuses:');
        $this->command->info('- Cash orders: 15');
        $this->command->info('- Card orders: 15');
        $this->command->info('- Transfer orders: 8');
        $this->command->info('- COD orders: 20');
        $this->command->info('- E-wallet orders: 6');
        $this->command->info('- Credit orders: 16');
        $this->command->info('- Installment orders: 5');
        $this->command->info('- Mixed payment orders: 3');
        $this->command->info('- Voucher orders: 4');
        $this->command->info('- Points orders: 3');
        $this->command->info('- Recent orders: 20');
        $this->command->info('- High value orders: 8');
        $this->command->info('- Cancelled orders: 8');
        $this->command->info('- POS orders: 12');
        $this->command->info('- Partially paid orders: 8');
        $this->command->info('- Overpaid orders: 2');
        $this->command->info('- Refunded orders: 3');
        $this->command->info('Total orders created: ' . Order::count());
    }
}
