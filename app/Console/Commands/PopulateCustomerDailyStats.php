<?php

namespace App\Console\Commands;

use App\Models\Analytics\CustomerDailyStats;
use App\Models\Invoice;
use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PopulateCustomerDailyStats extends Command
{
    protected $signature = 'analytics:populate-customer-stats {--date= : Date to populate (YYYY-MM-DD)}';
    protected $description = 'Populate customer daily stats table';

    public function handle()
    {
        $date = $this->option('date') ? Carbon::parse($this->option('date')) : now();
        $dateStr = $date->toDateString();

        $this->info("Populating customer daily stats for {$dateStr}...");

        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            $this->populateForTenant($tenant->id, $dateStr);
        }

        $this->info('Customer daily stats populated successfully!');
    }

    protected function populateForTenant($tenantId, $dateStr)
    {
        $branches = DB::table('branch_shops')
            ->where('tenant_id', $tenantId)
            ->pluck('id');

        foreach ($branches as $branchId) {
            $this->populateForBranch($tenantId, $branchId, $dateStr);
        }

        $this->populateForBranch($tenantId, null, $dateStr);
    }

    protected function populateForBranch($tenantId, $branchId, $dateStr)
    {
        $query = Invoice::where('tenant_id', $tenantId)
            ->where('invoice_type', 'sale')
            ->whereDate('invoice_date', $dateStr);

        if ($branchId) {
            $query->where('branch_shop_id', $branchId);
        }

        // Count customers by type
        $totalCustomers = $query->distinct('customer_id')->count('customer_id');
        $newCustomers = $query->where('customer_id', '!=', null)->count(); // Simplified
        $returningCustomers = 0; // Would need more complex logic
        $walkinCustomers = $query->where('customer_id', null)->count();
        $vipCustomers = 0; // Would need customer group logic

        // Revenue by customer type
        $newCustomerRevenue = $query->where('customer_id', '!=', null)->sum('total_amount') ?? 0;
        $returningCustomerRevenue = 0;
        $walkinRevenue = $query->where('customer_id', null)->sum('total_amount') ?? 0;
        $vipRevenue = 0;

        // Average metrics
        $avgOrderValue = $query->count() > 0 ? $query->sum('total_amount') / $query->count() : 0;
        $avgCustomerLifetimeValue = $totalCustomers > 0 ? $query->sum('total_amount') / $totalCustomers : 0;

        // Delete existing record
        CustomerDailyStats::where('tenant_id', $tenantId)
            ->where('stats_date', $dateStr)
            ->where('branch_shop_id', $branchId)
            ->delete();

        // Insert new record
        CustomerDailyStats::create([
            'tenant_id' => $tenantId,
            'branch_shop_id' => $branchId,
            'stats_date' => $dateStr,
            'total_customers' => $totalCustomers,
            'new_customers' => $newCustomers,
            'returning_customers' => $returningCustomers,
            'walkin_customers' => $walkinCustomers,
            'vip_customers' => $vipCustomers,
            'new_customer_revenue' => $newCustomerRevenue,
            'returning_customer_revenue' => $returningCustomerRevenue,
            'walkin_revenue' => $walkinRevenue,
            'vip_revenue' => $vipRevenue,
            'avg_order_value' => $avgOrderValue,
            'avg_customer_lifetime_value' => $avgCustomerLifetimeValue,
        ]);
    }
}

