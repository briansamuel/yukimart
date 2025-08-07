<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Models\Customer;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;

class SimpleDashboardSeedCommand extends Command
{
    protected $signature = 'seed:simple-dashboard {--days=7 : Number of days to seed data for}';
    protected $description = 'Seed simple sample data for dashboard testing';

    public function handle()
    {
        $days = $this->option('days');
        
        $this->info('🌱 Seeding simple dashboard data for ' . $days . ' days...');
        $this->newLine();

        // Create customers first
        $this->createCustomers();
        
        // Create sample orders
        $this->createSampleOrders($days);
        
        $this->info('✅ Simple dashboard data seeded successfully!');
        $this->call('debug:dashboard');
    }

    private function createCustomers()
    {
        $this->info('👥 Creating customers...');

        if (Customer::count() == 0) {
            $customers = [
                ['name' => 'Nguyễn Văn A', 'phone' => '0901234567'],
                ['name' => 'Trần Thị B', 'phone' => '0901234568'],
                ['name' => 'Lê Văn C', 'phone' => '0901234569'],
                ['name' => 'Phạm Thị D', 'phone' => '0901234570'],
                ['name' => 'Hoàng Văn E', 'phone' => '0901234571'],
                ['name' => 'Vũ Thị F', 'phone' => '0901234572'],
                ['name' => 'Đặng Văn G', 'phone' => '0901234573'],
                ['name' => 'Bùi Thị H', 'phone' => '0901234574'],
            ];

            foreach ($customers as $customerData) {
                Customer::create([
                    'name' => $customerData['name'],
                    'phone' => $customerData['phone'],
                    'email' => Str::slug($customerData['name']) . '@example.com',
                    'address' => 'Địa chỉ của ' . $customerData['name'],
                    'customer_type' => 'individual',
                    'status' => 'active',
                ]);
            }
            
            $this->line('✅ ' . count($customers) . ' customers created');
        } else {
            $this->line('✅ Customers already exist (' . Customer::count() . ')');
        }
    }

    private function createSampleOrders($days)
    {
        $this->info('📋 Creating sample orders for ' . $days . ' days...');

        $user = User::first();
        $customers = Customer::all();

        if (!$user) {
            $this->error('No users found. Please create a user first.');
            return;
        }

        if ($customers->count() == 0) {
            $this->error('No customers found. Cannot create orders.');
            return;
        }

        for ($i = 0; $i < $days; $i++) {
            $date = Carbon::today()->subDays($i);
            
            // Create 2-8 orders per day
            $ordersPerDay = rand(2, 8);
            
            for ($j = 0; $j < $ordersPerDay; $j++) {
                $customer = $customers->random();
                $orderTime = $date->copy()->addHours(rand(8, 20))->addMinutes(rand(0, 59));
                
                // Random order amounts
                $subtotal = rand(100000, 5000000); // 100k to 5M VND
                $discount = rand(0, $subtotal * 0.1); // Up to 10% discount
                $shipping = rand(0, 50000); // Up to 50k shipping
                $finalAmount = $subtotal - $discount + $shipping;
                
                // Create order
                Order::create([
                    'order_code' => 'ORD' . $orderTime->format('Ymd') . str_pad(($j + 1), 3, '0', STR_PAD_LEFT),
                    'customer_id' => $customer->id,
                    'channel' => collect(['direct', 'online', 'pos'])->random(),
                    'total_quantity' => rand(1, 10),
                    'total_amount' => $subtotal,
                    'discount_amount' => $discount,
                    'final_amount' => $finalAmount,
                    'amount_paid' => collect([0, $finalAmount, $finalAmount])->random(), // Some unpaid, most paid
                    'shipping_fee' => $shipping,
                    'tax_amount' => 0,
                    'status' => collect(['processing', 'completed', 'completed', 'completed'])->random(), // More completed orders
                    'delivery_status' => collect(['pending', 'picking', 'delivered', 'delivered'])->random(),
                    'note' => 'Đơn hàng mẫu cho ngày ' . $orderTime->format('d/m/Y'),
                    'created_by' => $user->id,
                    'created_at' => $orderTime,
                    'updated_at' => $orderTime,
                ]);
            }
            
            $this->line('📅 ' . $date->format('d/m/Y') . ': ' . $ordersPerDay . ' orders created');
        }

        $this->line('✅ Sample orders created');
    }
}
