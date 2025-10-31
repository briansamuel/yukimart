<?php

namespace App\Console\Commands;

use App\Models\Analytics\SlowMovingInventory;
use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PopulateSlowMovingInventory extends Command
{
    protected $signature = 'analytics:populate-slow-moving {--date= : Date to populate (YYYY-MM-DD)}';
    protected $description = 'Populate slow moving inventory table';

    public function handle()
    {
        $date = $this->option('date') ? Carbon::parse($this->option('date')) : now();
        $dateStr = $date->toDateString();

        $this->info("Populating slow moving inventory for {$dateStr}...");

        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            $this->populateForTenant($tenant->id, $dateStr);
        }

        $this->info('Slow moving inventory populated successfully!');
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
        // Get inventory with last sale date
        $inventoryQuery = DB::table('inventory_stocks as inv')
            ->join('products as p', 'inv.product_id', '=', 'p.id')
            ->where('inv.tenant_id', $tenantId)
            ->select(
                'inv.product_id',
                'p.name as product_name',
                'p.sku',
                'inv.quantity as current_stock',
                'p.cost_price',
                DB::raw('inv.quantity * p.cost_price as stock_value'),
                'inv.last_sale_date'
            );

        if ($branchId) {
            $inventoryQuery->where('inv.branch_shop_id', $branchId);
        }

        $inventory = $inventoryQuery->get();

        foreach ($inventory as $item) {
            // Calculate days without sale
            $lastSaleDate = $item->last_sale_date ? Carbon::parse($item->last_sale_date) : null;
            $daysWithoutSale = $lastSaleDate ? Carbon::parse($dateStr)->diffInDays($lastSaleDate) : 999;

            // Calculate sales history
            $thirtyDaysAgo = Carbon::parse($dateStr)->subDays(30)->toDateString();
            $sixtyDaysAgo = Carbon::parse($dateStr)->subDays(60)->toDateString();
            $ninetyDaysAgo = Carbon::parse($dateStr)->subDays(90)->toDateString();

            $sales30Days = DB::table('invoice_items')
                ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
                ->where('invoices.tenant_id', $tenantId)
                ->where('invoices.invoice_type', 'sale')
                ->where('invoice_items.product_id', $item->product_id)
                ->whereBetween('invoices.invoice_date', [$thirtyDaysAgo, $dateStr])
                ->when($branchId, function ($q) use ($branchId) {
                    return $q->where('invoices.branch_shop_id', $branchId);
                })
                ->sum('invoice_items.quantity') ?? 0;

            $sales60Days = DB::table('invoice_items')
                ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
                ->where('invoices.tenant_id', $tenantId)
                ->where('invoices.invoice_type', 'sale')
                ->where('invoice_items.product_id', $item->product_id)
                ->whereBetween('invoices.invoice_date', [$sixtyDaysAgo, $dateStr])
                ->when($branchId, function ($q) use ($branchId) {
                    return $q->where('invoices.branch_shop_id', $branchId);
                })
                ->sum('invoice_items.quantity') ?? 0;

            $sales90Days = DB::table('invoice_items')
                ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
                ->where('invoices.tenant_id', $tenantId)
                ->where('invoices.invoice_type', 'sale')
                ->where('invoice_items.product_id', $item->product_id)
                ->whereBetween('invoices.invoice_date', [$ninetyDaysAgo, $dateStr])
                ->when($branchId, function ($q) use ($branchId) {
                    return $q->where('invoices.branch_shop_id', $branchId);
                })
                ->sum('invoice_items.quantity') ?? 0;

            // Determine aging category
            $agingCategory = $this->determineAgingCategory($daysWithoutSale, $sales90Days);

            // Determine recommendation
            $recommendation = $this->determineRecommendation($agingCategory, $item->current_stock);

            // Delete existing record
            SlowMovingInventory::where('tenant_id', $tenantId)
                ->where('product_id', $item->product_id)
                ->where('branch_shop_id', $branchId)
                ->delete();

            // Insert new record
            SlowMovingInventory::create([
                'tenant_id' => $tenantId,
                'branch_shop_id' => $branchId,
                'product_id' => $item->product_id,
                'product_name' => $item->product_name,
                'sku' => $item->sku,
                'current_stock' => $item->current_stock,
                'stock_value' => $item->stock_value,
                'last_sale_date' => $lastSaleDate,
                'days_without_sale' => $daysWithoutSale,
                'sales_last_30_days' => $sales30Days,
                'sales_last_60_days' => $sales60Days,
                'sales_last_90_days' => $sales90Days,
                'aging_category' => $agingCategory,
                'recommendation' => $recommendation,
            ]);
        }
    }

    protected function determineAgingCategory($daysWithoutSale, $sales90Days)
    {
        if ($daysWithoutSale > 90 && $sales90Days == 0) {
            return 'dead_stock';
        } elseif ($daysWithoutSale > 60) {
            return 'slow_moving';
        } elseif ($daysWithoutSale > 30) {
            return 'normal';
        } else {
            return 'fast_moving';
        }
    }

    protected function determineRecommendation($agingCategory, $currentStock)
    {
        if ($agingCategory == 'dead_stock') {
            return 'Thanh lý hoặc giảm giá mạnh';
        } elseif ($agingCategory == 'slow_moving') {
            return 'Khuyến mãi hoặc giảm giá';
        } elseif ($agingCategory == 'normal') {
            return 'Theo dõi thường xuyên';
        } else {
            return 'Duy trì tồn kho';
        }
    }
}

