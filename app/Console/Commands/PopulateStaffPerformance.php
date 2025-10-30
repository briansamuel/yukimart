<?php

namespace App\Console\Commands;

use App\Models\Analytics\StaffDailyPerformance;
use App\Models\Invoice;
use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PopulateStaffPerformance extends Command
{
    protected $signature = 'analytics:populate-staff-performance {--date= : Date to populate (YYYY-MM-DD)}';
    protected $description = 'Populate staff daily performance table';

    public function handle()
    {
        $date = $this->option('date') ? Carbon::parse($this->option('date')) : now();
        $dateStr = $date->toDateString();

        $this->info("Populating staff daily performance for {$dateStr}...");

        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            $this->populateForTenant($tenant->id, $dateStr);
        }

        $this->info('Staff daily performance populated successfully!');
    }

    protected function populateForTenant($tenantId, $dateStr)
    {
        // Get all staff who made sales on this date
        $staffIds = Invoice::where('tenant_id', $tenantId)
            ->where('invoice_type', 'sale')
            ->whereDate('invoice_date', $dateStr)
            ->distinct('sold_by')
            ->pluck('sold_by');

        foreach ($staffIds as $staffId) {
            $this->populateForStaff($tenantId, $staffId, $dateStr);
        }
    }

    protected function populateForStaff($tenantId, $staffId, $dateStr)
    {
        $query = Invoice::where('tenant_id', $tenantId)
            ->where('sold_by', $staffId)
            ->where('invoice_type', 'sale')
            ->whereDate('invoice_date', $dateStr);

        // Get branch from first invoice
        $firstInvoice = $query->first();
        $branchId = $firstInvoice ? $firstInvoice->branch_shop_id : null;

        // Calculate metrics
        $totalOrders = $query->count();
        $totalRevenue = $query->sum('total_amount') ?? 0;

        // Calculate COGS
        $totalCogs = DB::table('invoice_items')
            ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->join('products', 'invoice_items.product_id', '=', 'products.id')
            ->where('invoices.tenant_id', $tenantId)
            ->where('invoices.sold_by', $staffId)
            ->where('invoices.invoice_type', 'sale')
            ->whereDate('invoices.invoice_date', $dateStr)
            ->sum(DB::raw('products.cost_price * invoice_items.quantity')) ?? 0;

        $totalProfit = $totalRevenue - $totalCogs;

        // Customer metrics
        $uniqueCustomers = $query->distinct('customer_id')->count('customer_id');
        $newCustomers = $query->where('customer_id', '!=', null)->count();

        // Average metrics
        $avgOrderValue = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;
        $avgProfitPerOrder = $totalOrders > 0 ? $totalProfit / $totalOrders : 0;
        $profitMargin = $totalRevenue > 0 ? ($totalProfit / $totalRevenue) * 100 : 0;

        // Delete existing record
        StaffDailyPerformance::where('tenant_id', $tenantId)
            ->where('staff_id', $staffId)
            ->where('performance_date', $dateStr)
            ->delete();

        // Insert new record
        StaffDailyPerformance::create([
            'tenant_id' => $tenantId,
            'branch_shop_id' => $branchId,
            'staff_id' => $staffId,
            'performance_date' => $dateStr,
            'total_orders' => $totalOrders,
            'total_revenue' => $totalRevenue,
            'total_profit' => $totalProfit,
            'unique_customers' => $uniqueCustomers,
            'new_customers' => $newCustomers,
            'avg_order_value' => $avgOrderValue,
            'avg_profit_per_order' => $avgProfitPerOrder,
            'profit_margin' => $profitMargin,
        ]);
    }
}

