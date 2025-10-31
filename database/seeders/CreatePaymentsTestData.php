<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CreatePaymentsTestData extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenantId = 3; // tenant1 has tenant_id = 3
        $branchShopId = 1; // Default branch

        // Get random users for tenant
        $userIds = DB::table('users')->where('tenant_id', $tenantId)->pluck('id')->toArray();

        if (empty($userIds)) {
            echo "ERROR: No users found for tenant_id = {$tenantId}\n";
            return;
        }

        echo "Found " . count($userIds) . " users for tenant {$tenantId}\n";
        
        // Get date ranges
        $now = Carbon::now();
        
        // This month: from start of month to now
        $thisMonthStart = $now->copy()->startOfMonth();
        $thisMonthEnd = $now->copy();
        
        // This quarter: from start of quarter to now
        $thisQuarterStart = $now->copy()->startOfQuarter();
        $thisQuarterEnd = $now->copy();
        
        echo "Creating payment test data...\n";
        echo "This month: {$thisMonthStart->format('Y-m-d')} to {$thisMonthEnd->format('Y-m-d')}\n";
        echo "This quarter: {$thisQuarterStart->format('Y-m-d')} to {$thisQuarterEnd->format('Y-m-d')}\n";
        
        $payments = [];
        $paymentNumber = 1;
        
        // Create 50 payments in this month
        echo "Creating 50 payments for this month...\n";
        for ($i = 0; $i < 50; $i++) {
            $randomDays = rand(0, $thisMonthEnd->diffInDays($thisMonthStart));
            $paymentDate = $thisMonthStart->copy()->addDays($randomDays);
            
            // Random payment type (receipt or payment - NOT disbursement!)
            $paymentType = rand(0, 1) === 0 ? 'receipt' : 'payment';
            $prefix = $paymentType === 'receipt' ? 'PT' : 'PC';

            // Random amount between 100,000 and 5,000,000
            $amount = rand(100, 5000) * 1000;

            // Random payment method (use correct enum values)
            $paymentMethods = ['cash', 'transfer', 'card', 'check'];
            $paymentMethod = $paymentMethods[array_rand($paymentMethods)];

            // Random status (use correct enum values)
            $statuses = ['pending', 'completed', 'cancelled'];
            $status = $statuses[array_rand($statuses)];

            // Random reference type
            $referenceTypes = ['invoice', 'return_order', 'manual'];
            $referenceType = $referenceTypes[array_rand($referenceTypes)];

            // Random user from tenant
            $randomUserId = $userIds[array_rand($userIds)];

            $payments[] = [
                'tenant_id' => $tenantId,
                'branch_shop_id' => $branchShopId,
                'customer_id' => null, // Set to null for now
                'payment_number' => $prefix . str_pad($paymentNumber++, 6, '0', STR_PAD_LEFT),
                'payment_type' => $paymentType,
                'payment_date' => $paymentDate->format('Y-m-d H:i:s'),
                'amount' => $amount,
                'payment_method' => $paymentMethod,
                'status' => $status,
                'reference_type' => $referenceType,
                'reference_id' => rand(1, 100),
                'description' => 'Test payment ' . ($i + 1) . ' - ' . $paymentType,
                'created_by' => $randomUserId,
                'collector_id' => $randomUserId,
                'bank_account_id' => null, // Set to null for now
                'created_at' => $paymentDate,
                'updated_at' => $paymentDate,
            ];
        }
        
        // Create 50 more payments in this quarter (but not in this month)
        echo "Creating 50 payments for this quarter (excluding this month)...\n";
        for ($i = 0; $i < 50; $i++) {
            // Random date in quarter but before this month
            $randomDays = rand(0, $thisMonthStart->diffInDays($thisQuarterStart));
            $paymentDate = $thisQuarterStart->copy()->addDays($randomDays);
            
            // Skip if date falls in this month
            if ($paymentDate->month === $thisMonthStart->month) {
                continue;
            }
            
            // Random payment type (receipt or payment - NOT disbursement!)
            $paymentType = rand(0, 1) === 0 ? 'receipt' : 'payment';
            $prefix = $paymentType === 'receipt' ? 'PT' : 'PC';

            // Random amount between 100,000 and 5,000,000
            $amount = rand(100, 5000) * 1000;

            // Random payment method (use correct enum values)
            $paymentMethods = ['cash', 'transfer', 'card', 'check'];
            $paymentMethod = $paymentMethods[array_rand($paymentMethods)];

            // Random status (use correct enum values)
            $statuses = ['pending', 'completed', 'cancelled'];
            $status = $statuses[array_rand($statuses)];

            // Random reference type
            $referenceTypes = ['invoice', 'return_order', 'manual'];
            $referenceType = $referenceTypes[array_rand($referenceTypes)];

            // Random user from tenant
            $randomUserId = $userIds[array_rand($userIds)];

            $payments[] = [
                'tenant_id' => $tenantId,
                'branch_shop_id' => $branchShopId,
                'customer_id' => null, // Set to null for now
                'payment_number' => $prefix . str_pad($paymentNumber++, 6, '0', STR_PAD_LEFT),
                'payment_type' => $paymentType,
                'payment_date' => $paymentDate->format('Y-m-d H:i:s'),
                'amount' => $amount,
                'payment_method' => $paymentMethod,
                'status' => $status,
                'reference_type' => $referenceType,
                'reference_id' => rand(1, 100),
                'description' => 'Test payment Q' . $paymentDate->quarter . ' - ' . $paymentType,
                'created_by' => $randomUserId,
                'collector_id' => $randomUserId,
                'bank_account_id' => null, // Set to null for now
                'created_at' => $paymentDate,
                'updated_at' => $paymentDate,
            ];
        }
        
        // Insert all payments
        DB::table('payments')->insert($payments);
        
        echo "Created " . count($payments) . " payment records\n";
        
        // Show summary
        $thisMonthCount = DB::table('payments')
            ->where('tenant_id', $tenantId)
            ->whereBetween('payment_date', [$thisMonthStart->format('Y-m-d'), $thisMonthEnd->format('Y-m-d')])
            ->count();
            
        $thisQuarterCount = DB::table('payments')
            ->where('tenant_id', $tenantId)
            ->whereBetween('payment_date', [$thisQuarterStart->format('Y-m-d'), $thisQuarterEnd->format('Y-m-d')])
            ->count();
            
        echo "\nSummary:\n";
        echo "- This month payments: {$thisMonthCount}\n";
        echo "- This quarter payments: {$thisQuarterCount}\n";
        
        $receiptCount = DB::table('payments')
            ->where('tenant_id', $tenantId)
            ->where('payment_type', 'receipt')
            ->count();

        $paymentCount = DB::table('payments')
            ->where('tenant_id', $tenantId)
            ->where('payment_type', 'payment')
            ->count();

        echo "- Receipt payments: {$receiptCount}\n";
        echo "- Payment (disbursement) payments: {$paymentCount}\n";
    }
}

