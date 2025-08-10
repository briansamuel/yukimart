<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Customer;
use App\Models\BranchShop;
use Illuminate\Support\Facades\DB;

class UpdateCustomerData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'customers:update-data 
                            {--dry-run : Run without making changes}
                            {--limit=50 : Limit number of customers to update}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update customer_code and branch_shop_id for customers that are missing these fields';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        $limit = (int) $this->option('limit');

        $this->info('🔄 Starting customer data update...');
        $this->info('Dry run: ' . ($isDryRun ? 'YES' : 'NO'));
        $this->info('Limit: ' . $limit);
        $this->newLine();

        try {
            DB::beginTransaction();

            // Get available branch shops
            $branchShops = BranchShop::where('status', 'active')->get();
            
            if ($branchShops->isEmpty()) {
                $this->error('❌ No active branch shops found!');
                return 1;
            }

            $this->info('📍 Found ' . $branchShops->count() . ' active branch shops:');
            foreach ($branchShops as $branch) {
                $this->line("   - {$branch->name} (ID: {$branch->id})");
            }
            $this->newLine();

            // Update customer_code for customers without it
            $this->updateCustomerCodes($isDryRun, $limit);

            // Update branch_shop_id for customers without it
            $this->updateBranchShopIds($branchShops, $isDryRun, $limit);

            if (!$isDryRun) {
                DB::commit();
                $this->info('✅ All updates committed successfully!');
            } else {
                DB::rollBack();
                $this->info('🔍 Dry run completed - no changes made');
            }

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('❌ Error: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }

    /**
     * Update customer_code for customers without it
     */
    private function updateCustomerCodes($isDryRun, $limit)
    {
        $customersWithoutCode = Customer::whereNull('customer_code')
            ->limit($limit)
            ->get();

        $this->info('👤 Customers without customer_code: ' . $customersWithoutCode->count());

        if ($customersWithoutCode->isEmpty()) {
            $this->line('   ✅ All customers already have customer_code');
            return;
        }

        $updated = 0;
        foreach ($customersWithoutCode as $customer) {
            // Generate unique code for each customer
            do {
                $newCode = Customer::generateCustomerCode();
                // In dry run, we need to track used codes manually
                $exists = $isDryRun ? false : Customer::where('customer_code', $newCode)->exists();
            } while ($exists);

            $this->line("   📝 Customer ID {$customer->id} ({$customer->name}): {$newCode}");

            if (!$isDryRun) {
                $customer->update(['customer_code' => $newCode]);
            }
            $updated++;
        }

        $this->info("   ✅ Updated customer_code for {$updated} customers");
        $this->newLine();
    }

    /**
     * Update branch_shop_id for customers without it
     */
    private function updateBranchShopIds($branchShops, $isDryRun, $limit)
    {
        $customersWithoutBranch = Customer::whereNull('branch_shop_id')
            ->limit($limit)
            ->get();

        $this->info('🏪 Customers without branch_shop_id: ' . $customersWithoutBranch->count());

        if ($customersWithoutBranch->isEmpty()) {
            $this->line('   ✅ All customers already have branch_shop_id');
            return;
        }

        $updated = 0;
        $branchShopIds = $branchShops->pluck('id')->toArray();
        
        foreach ($customersWithoutBranch as $customer) {
            // Randomly assign to a branch shop (in real scenario, this would be based on business logic)
            $randomBranchId = $branchShopIds[array_rand($branchShopIds)];
            $branchName = $branchShops->find($randomBranchId)->name;
            
            $this->line("   🏪 Customer ID {$customer->id} ({$customer->name}): {$branchName} (ID: {$randomBranchId})");
            
            if (!$isDryRun) {
                $customer->update(['branch_shop_id' => $randomBranchId]);
            }
            $updated++;
        }

        $this->info("   ✅ Updated branch_shop_id for {$updated} customers");
        $this->newLine();
    }
}
