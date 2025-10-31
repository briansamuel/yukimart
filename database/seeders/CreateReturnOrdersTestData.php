<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Tenant;
use App\Models\Customer;
use App\Models\User;
use App\Models\ReturnOrder;
use App\Models\BranchShop;
use Carbon\Carbon;

class CreateReturnOrdersTestData extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get tenant1
        $tenant = Tenant::where('subdomain', 'tenant1')->first();
        if (!$tenant) {
            $this->command->error('Tenant not found');
            return;
        }

        $this->command->info('Found tenant: ' . $tenant->subdomain);

        // Set tenant context
        app()->instance('tenant', $tenant);

        // Get customers
        $customers = Customer::where('tenant_id', $tenant->id)->limit(5)->get();
        $this->command->info('Found ' . $customers->count() . ' customers');

        if ($customers->count() === 0) {
            $this->command->error('No customers found');
            return;
        }

        // Get branch shops
        $branchShops = BranchShop::where('tenant_id', $tenant->id)->get();
        $this->command->info('Found ' . $branchShops->count() . ' branch shops');

        // Get invoices
        $invoices = \App\Models\Invoice::where('tenant_id', $tenant->id)->limit(10)->get();
        $this->command->info('Found ' . $invoices->count() . ' invoices');

        // Get admin user from tenant_users table
        $tenantUser = DB::table('tenant_users')
            ->where('tenant_id', $tenant->id)
            ->where('role', 'admin')
            ->first();

        if (!$tenantUser) {
            $this->command->error('Admin user not found');
            return;
        }

        $admin = User::find($tenantUser->user_id);
        $this->command->info('Admin user: ' . $admin->email);

        // Create return orders with recent dates
        $statuses = ['pending', 'approved', 'rejected', 'completed'];
        $refundMethods = ['cash', 'card', 'transfer', 'store_credit', 'exchange', 'points'];
        $reasons = ['defective', 'wrong_item', 'customer_request', 'damaged', 'expired', 'other'];

        $created = 0;
        foreach (range(1, 100) as $i) {
            // Random date in last 90 days
            $daysAgo = rand(0, 90);
            $createdAt = Carbon::now()->subDays($daysAgo);

            $status = $statuses[array_rand($statuses)];
            $subtotal = rand(100000, 5000000);
            $taxRate = 10; // 10%
            $taxAmount = $subtotal * $taxRate / 100;
            $totalAmount = $subtotal + $taxAmount;

            ReturnOrder::create([
                'tenant_id' => $tenant->id,
                'return_number' => 'TH' . $createdAt->format('Ymd') . str_pad($i, 3, '0', STR_PAD_LEFT),
                'customer_id' => $customers->random()->id,
                'branch_shop_id' => $branchShops->count() > 0 ? $branchShops->random()->id : null,
                'invoice_id' => $invoices->count() > 0 ? $invoices->random()->id : null,
                'subtotal' => $subtotal,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'status' => $status,
                'reason' => $reasons[array_rand($reasons)],
                'refund_method' => $refundMethods[array_rand($refundMethods)],
                'return_date' => $createdAt,
                'created_by' => $admin->id,
                'approved_by' => in_array($status, ['approved', 'completed']) ? $admin->id : null,
                'approved_at' => in_array($status, ['approved', 'completed']) ? $createdAt : null,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            $created++;
        }

        $this->command->info('✅ Created ' . $created . ' return orders with recent dates');
        
        // Show date range
        $oldest = ReturnOrder::where('tenant_id', $tenant->id)->orderBy('created_at', 'asc')->first();
        $newest = ReturnOrder::where('tenant_id', $tenant->id)->orderBy('created_at', 'desc')->first();
        
        $this->command->info('Date range: ' . $oldest->created_at->format('Y-m-d') . ' to ' . $newest->created_at->format('Y-m-d'));
        
        // Show status breakdown
        $this->command->info('Status breakdown:');
        foreach ($statuses as $status) {
            $count = ReturnOrder::where('tenant_id', $tenant->id)->where('status', $status)->count();
            $this->command->info('  - ' . $status . ': ' . $count);
        }
    }
}

