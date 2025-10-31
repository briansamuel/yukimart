<?php

namespace App\Console\Commands;

use App\Models\Analytics\InventoryDailyStats;
use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PopulateInventoryDailyStats extends Command
{
    protected $signature = 'analytics:populate-inventory-stats {--date= : Date to populate (YYYY-MM-DD)}';
    protected $description = 'Populate inventory daily stats table';

    public function handle()
    {
        $date = $this->option('date') ? Carbon::parse($this->option('date')) : now();
        $dateStr = $date->toDateString();

        $this->info("Populating inventory daily stats for {$dateStr}...");

        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            $this->populateForTenant($tenant->id, $dateStr);
        }

        $this->info('Inventory daily stats populated successfully!');
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
        // Get inventory data
        $inventoryQuery = DB::table('inventory_stocks')
            ->where('tenant_id', $tenantId);

        if ($branchId) {
            $inventoryQuery->where('branch_shop_id', $branchId);
        }

        $inventory = $inventoryQuery->get();

        // Calculate metrics
        $totalSkus = $inventory->count();
        $lowStockItems = $inventory->where('quantity', '<', DB::raw('reorder_point'))->count();
        $outOfStockItems = $inventory->where('quantity', 0)->count();
        $overstockItems = $inventory->where('quantity', '>', DB::raw('max_stock'))->count();

        $totalInventoryValue = $inventory->sum(DB::raw('quantity * cost_price'));
        $lowStockValue = $inventory->where('quantity', '<', DB::raw('reorder_point'))
            ->sum(DB::raw('quantity * cost_price'));
        $overstockValue = $inventory->where('quantity', '>', DB::raw('max_stock'))
            ->sum(DB::raw('quantity * cost_price'));

        // Get sales data for turnover calculation
        $salesQuery = DB::table('invoice_items')
            ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->where('invoices.tenant_id', $tenantId)
            ->where('invoices.invoice_type', 'sale')
            ->whereDate('invoices.invoice_date', $dateStr);

        if ($branchId) {
            $salesQuery->where('invoices.branch_shop_id', $branchId);
        }

        $itemsSold = $salesQuery->sum('invoice_items.quantity') ?? 0;
        $itemsReceived = 0; // Would need purchase order data

        // Calculate aging (items not sold in 30/60/90 days)
        $thirtyDaysAgo = Carbon::parse($dateStr)->subDays(30)->toDateString();
        $sixtyDaysAgo = Carbon::parse($dateStr)->subDays(60)->toDateString();
        $ninetyDaysAgo = Carbon::parse($dateStr)->subDays(90)->toDateString();

        $itemsNotSold30 = DB::table('inventory_stocks')
            ->where('tenant_id', $tenantId)
            ->when($branchId, function ($q) use ($branchId) {
                return $q->where('branch_shop_id', $branchId);
            })
            ->where('last_sale_date', '<', $thirtyDaysAgo)
            ->count();

        $itemsNotSold60 = DB::table('inventory_stocks')
            ->where('tenant_id', $tenantId)
            ->when($branchId, function ($q) use ($branchId) {
                return $q->where('branch_shop_id', $branchId);
            })
            ->where('last_sale_date', '<', $sixtyDaysAgo)
            ->count();

        $itemsNotSold90 = DB::table('inventory_stocks')
            ->where('tenant_id', $tenantId)
            ->when($branchId, function ($q) use ($branchId) {
                return $q->where('branch_shop_id', $branchId);
            })
            ->where('last_sale_date', '<', $ninetyDaysAgo)
            ->count();

        $avgInventoryValue = $totalInventoryValue > 0 ? $totalInventoryValue : 1;
        $turnoverRate = $itemsSold > 0 ? $itemsSold / $avgInventoryValue : 0;

        // Delete existing record
        InventoryDailyStats::where('tenant_id', $tenantId)
            ->where('stats_date', $dateStr)
            ->where('branch_shop_id', $branchId)
            ->delete();

        // Insert new record
        InventoryDailyStats::create([
            'tenant_id' => $tenantId,
            'branch_shop_id' => $branchId,
            'stats_date' => $dateStr,
            'total_skus' => $totalSkus,
            'low_stock_items' => $lowStockItems,
            'overstock_items' => $overstockItems,
            'out_of_stock_items' => $outOfStockItems,
            'total_inventory_value' => $totalInventoryValue,
            'low_stock_value' => $lowStockValue,
            'overstock_value' => $overstockValue,
            'items_sold' => $itemsSold,
            'items_received' => $itemsReceived,
            'inventory_turnover_rate' => $turnoverRate,
            'items_not_sold_30_days' => $itemsNotSold30,
            'items_not_sold_60_days' => $itemsNotSold60,
            'items_not_sold_90_days' => $itemsNotSold90,
        ]);
    }
}

