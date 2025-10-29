<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\TenantUser;
use App\Models\Tenant;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Customer;
use App\Models\Product;
use App\Models\BranchShop;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class DemoOrdersForTenant1Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 Creating demo orders for available tenant...');

        // Get available tenants and use the first one
        $tenants = Tenant::all();
        $this->command->info("Available tenants: " . $tenants->pluck('slug')->implode(', '));

        $tenant = $tenants->first();
        if (!$tenant) {
            $this->command->error('❌ No tenants found');
            return;
        }

        $this->command->info("✅ Found tenant: {$tenant->name}");

        // Create additional users if needed
        $users = $this->createAdditionalUsers($tenant);
        $this->command->info("👥 Created/found {$users->count()} users");

        // Get existing data
        $customers = Customer::where('tenant_id', $tenant->id)->get();
        $products = Product::where('tenant_id', $tenant->id)->get();
        $branchShops = BranchShop::where('tenant_id', $tenant->id)->get();

        if ($customers->isEmpty()) {
            $this->command->error("❌ No customers found in {$tenant->slug}");
            return;
        }

        if ($products->isEmpty()) {
            $this->command->error("❌ No products found in {$tenant->slug}");
            return;
        }

        if ($branchShops->isEmpty()) {
            $this->command->error("❌ No branch shops found in {$tenant->slug}");
            return;
        }

        $this->command->info("📊 Found {$customers->count()} customers, {$products->count()} products, {$branchShops->count()} branch shops");

        // Create demo orders with different creators and sellers
        $this->createDemoOrders($tenant, $users, $customers, $products, $branchShops);

        $this->command->info('🎉 Demo orders created successfully!');
    }

    /**
     * Create additional users for tenant
     */
    private function createAdditionalUsers(Tenant $tenant): \Illuminate\Support\Collection
    {
        $additionalUsers = [
            [
                'username' => "manager_{$tenant->slug}",
                'email' => "manager@{$tenant->slug}.local",
                'full_name' => "Manager {$tenant->name}",
                'role' => 'manager',
                'phone' => '0901234567'
            ],
            [
                'username' => "staff1_{$tenant->slug}",
                'email' => "staff1@{$tenant->slug}.local",
                'full_name' => "Staff 1 {$tenant->name}",
                'role' => 'staff',
                'phone' => '0901234568'
            ],
            [
                'username' => "staff2_{$tenant->slug}",
                'email' => "staff2@{$tenant->slug}.local",
                'full_name' => "Staff 2 {$tenant->name}",
                'role' => 'staff',
                'phone' => '0901234569'
            ],
            [
                'username' => "admin_{$tenant->slug}",
                'email' => "admin@{$tenant->slug}.local",
                'full_name' => "Admin {$tenant->name}",
                'role' => 'admin',
                'phone' => '0901234570'
            ],
            [
                'username' => "viewer_{$tenant->slug}",
                'email' => "viewer@{$tenant->slug}.local",
                'full_name' => "Viewer {$tenant->name}",
                'role' => 'viewer',
                'phone' => '0901234571'
            ]
        ];

        foreach ($additionalUsers as $userData) {
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'username' => $userData['username'],
                    'email' => $userData['email'],
                    'password' => Hash::make('123456'),
                    'full_name' => $userData['full_name'],
                    'address' => "123 {$userData['full_name']} Street, {$tenant->name} City",
                    'phone' => $userData['phone'],
                    'active_code' => 'verified',
                    'status' => 'active',
                    'email_verified_at' => now(),
                    'birth_date' => now()->subYears(25),
                ]
            );

            // Create tenant-user relationship
            TenantUser::firstOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'user_id' => $user->id,
                ],
                [
                    'role' => $userData['role'],
                    'is_active' => true,
                    'invitation_status' => 'accepted',
                    'joined_at' => now(),
                ]
            );
        }

        // Return all users in tenant1
        return TenantUser::where('tenant_id', $tenant->id)
            ->with('user')
            ->get()
            ->pluck('user');
    }

    /**
     * Create demo orders with different creators and sellers
     */
    private function createDemoOrders(Tenant $tenant, $users, $customers, $products, $branchShops): void
    {
        $statuses = ['draft', 'pending', 'confirmed', 'processing', 'shipped', 'delivered', 'completed', 'cancelled', 'returned'];
        $deliveryStatuses = ['pending', 'picking', 'delivering', 'delivered', 'returning', 'returned'];
        $channels = ['direct', 'online', 'pos', 'other', 'shopee', 'tiktok', 'facebook'];
        $paymentMethods = ['cash', 'card', 'transfer', 'cod', 'e_wallet', 'installment', 'credit', 'voucher', 'points', 'mixed'];
        $paymentStatuses = ['unpaid', 'partial', 'paid', 'overpaid', 'refunded'];

        // Create 50 demo orders
        for ($i = 1; $i <= 50; $i++) {
            $creator = $users->random();
            $seller = $users->random();
            $customer = $customers->random();
            $branchShop = $branchShops->random();
            
            // Random date within last 3 months
            $orderDate = Carbon::now()->subDays(rand(1, 90));
            
            $status = $statuses[array_rand($statuses)];
            $deliveryStatus = $deliveryStatuses[array_rand($deliveryStatuses)];
            $channel = $channels[array_rand($channels)];
            $paymentMethod = $paymentMethods[array_rand($paymentMethods)];
            $paymentStatus = $paymentStatuses[array_rand($paymentStatuses)];

            // Generate unique order code with tenant prefix and timestamp
            $orderCode = 'DH' . $orderDate->format('Ymd') . str_pad($i + time(), 6, '0', STR_PAD_LEFT);

            $order = Order::create([
                'tenant_id' => $tenant->id,
                'order_code' => $orderCode,
                'customer_id' => $customer->id,
                'branch_shop_id' => $branchShop->id,
                'status' => $status,
                'delivery_status' => $deliveryStatus,
                'channel' => $channel,
                'payment_method' => $paymentMethod,
                'payment_status' => $paymentStatus,
                'payment_reference' => $paymentStatus !== 'unpaid' ? 'REF' . rand(100000, 999999) : null,
                'payment_date' => $paymentStatus !== 'unpaid' ? $orderDate->addDays(rand(0, 5)) : null,
                'total_amount' => 0, // Will be calculated
                'discount_amount' => rand(0, 50000),
                'other_amount' => 0,
                'final_amount' => 0, // Will be calculated
                'amount_paid' => 0, // Will be calculated
                'created_by' => $creator->id,
                'sold_by' => $seller->id,
                'created_at' => $orderDate,
                'updated_at' => $orderDate,
            ]);

            // Create order items
            $itemCount = rand(1, 5);
            $totalAmount = 0;

            for ($j = 1; $j <= $itemCount; $j++) {
                $product = $products->random();
                $quantity = rand(1, 3);
                $unitPrice = $product->sale_price ?? rand(10000, 500000);
                $discount = rand(0, $unitPrice * $quantity * 0.1); // Up to 10% discount
                $totalPrice = ($quantity * $unitPrice) - $discount;
                $totalAmount += $totalPrice;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'discount' => $discount,
                    'total_price' => $totalPrice,
                    'created_at' => $orderDate,
                    'updated_at' => $orderDate,
                ]);
            }

            // Update order totals
            $finalAmount = $totalAmount - $order->discount_amount;
            $amountPaid = match($paymentStatus) {
                'paid' => $finalAmount,
                'partial' => $finalAmount * 0.5,
                default => 0
            };

            $order->update([
                'total_amount' => $totalAmount,
                'final_amount' => $finalAmount,
                'amount_paid' => $amountPaid,
            ]);

            if ($i % 10 == 0) {
                $this->command->info("📦 Created {$i} orders...");
            }
        }

        $this->command->info("✅ Created 50 demo orders with different creators and sellers");
    }
}
