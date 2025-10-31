<?php

namespace App\Console\Commands;

use App\Models\Analytics\SalesDailySummary;
use App\Models\Invoice;
use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PopulateSalesDailySummary extends Command
{
    protected $signature = 'analytics:populate-sales-summary {--date= : Date to populate (YYYY-MM-DD)}';
    protected $description = 'Populate sales daily summary table';

    public function handle()
    {
        $date = $this->option('date') ? Carbon::parse($this->option('date')) : now();
        $dateStr = $date->toDateString();

        $this->info("Populating sales daily summary for {$dateStr}...");

        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            $this->populateForTenant($tenant->id, $dateStr);
        }

        $this->info('Sales daily summary populated successfully!');
    }

    protected function populateForTenant($tenantId, $dateStr)
    {
        // Get all branches for this tenant
        $branches = DB::table('branch_shops')
            ->where('tenant_id', $tenantId)
            ->pluck('id');

        // Populate for each branch
        foreach ($branches as $branchId) {
            $this->populateForBranch($tenantId, $branchId, $dateStr);
        }

        // Populate for all branches combined (branch_id = null)
        $this->populateForBranch($tenantId, null, $dateStr);
    }

    protected function populateForBranch($tenantId, $branchId, $dateStr)
    {
        $query = Invoice::where('tenant_id', $tenantId)
            ->whereDate('invoice_date', $dateStr);

        if ($branchId) {
            $query->where('branch_shop_id', $branchId);
        }

        // Calculate metrics
        $saleInvoices = clone $query;
        $saleInvoices = $saleInvoices->where('invoice_type', 'sale');

        $returnInvoices = clone $query;
        $returnInvoices = $returnInvoices->where('invoice_type', 'return');

        $totalOrders = $saleInvoices->count();
        $totalReturnOrders = $returnInvoices->count();
        $totalRevenue = $saleInvoices->sum('total_amount') ?? 0;
        $totalReturn = $returnInvoices->sum('total_amount') ?? 0;
        $netRevenue = $totalRevenue - $totalReturn;

        // Calculate COGS
        $totalCogs = DB::table('invoice_items')
            ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->join('products', 'invoice_items.product_id', '=', 'products.id')
            ->where('invoices.tenant_id', $tenantId)
            ->where('invoices.invoice_type', 'sale')
            ->whereDate('invoices.invoice_date', $dateStr)
            ->when($branchId, function ($q) use ($branchId) {
                return $q->where('invoices.branch_shop_id', $branchId);
            })
            ->sum(DB::raw('products.cost_price * invoice_items.quantity')) ?? 0;

        $totalProfit = $netRevenue - $totalCogs;

        // Get customer metrics
        $uniqueCustomers = $saleInvoices->distinct('customer_id')->count('customer_id');
        $newCustomers = $saleInvoices->where('customer_id', '!=', null)->count(); // Simplified
        $returningCustomers = 0; // Would need more complex logic
        $walkinCustomers = $saleInvoices->where('customer_id', null)->count();

        // Get channel metrics
        $offlineRevenue = $saleInvoices->where('sales_channel', 'offline')->sum('total_amount') ?? 0;
        $onlineRevenue = $saleInvoices->where('sales_channel', 'online')->sum('total_amount') ?? 0;
        $marketplaceRevenue = $saleInvoices->where('sales_channel', 'marketplace')->sum('total_amount') ?? 0;

        // Delete existing record
        SalesDailySummary::where('tenant_id', $tenantId)
            ->where('summary_date', $dateStr)
            ->where('branch_shop_id', $branchId)
            ->delete();

        // Insert new record
        SalesDailySummary::create([
            'tenant_id' => $tenantId,
            'branch_shop_id' => $branchId,
            'summary_date' => $dateStr,
            'total_orders' => $totalOrders,
            'total_return_orders' => $totalReturnOrders,
            'total_revenue' => $totalRevenue,
            'total_return_amount' => $totalReturn,
            'net_revenue' => $netRevenue,
            'total_cogs' => $totalCogs,
            'total_profit' => $totalProfit,
            'unique_customers' => $uniqueCustomers,
            'new_customers' => $newCustomers,
            'returning_customers' => $returningCustomers,
            'walkin_customers' => $walkinCustomers,
            'offline_revenue' => $offlineRevenue,
            'online_revenue' => $onlineRevenue,
            'marketplace_revenue' => $marketplaceRevenue,
        ]);
    }
}

