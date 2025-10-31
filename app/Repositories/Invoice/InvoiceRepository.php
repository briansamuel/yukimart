<?php

namespace App\Repositories\Invoice;

use App\Repositories\BaseRepository;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;

class InvoiceRepository extends BaseRepository implements InvoiceRepositoryInterface
{
    public function getModel()
    {
        return Invoice::class;
    }

    /**
     * Get invoices by date range and filters
     */
    public function getByDateRange($tenantId, $fromDate, $toDate, $branchShopId = null, $invoiceType = 'sale')
    {
        $query = $this->model
            ->where('tenant_id', $tenantId)
            ->where('invoice_type', $invoiceType)
            ->whereBetween('invoice_date', [$fromDate, $toDate]);

        if ($branchShopId) {
            $query->where('branch_shop_id', $branchShopId);
        }

        return $query->get();
    }

    /**
     * Get invoices grouped by date
     */
    public function groupByDate($tenantId, $fromDate, $toDate, $branchShopId = null)
    {
        $query = $this->model
            ->where('tenant_id', $tenantId)
            ->whereBetween('invoice_date', [$fromDate, $toDate])
            ->select(
                DB::raw('DATE(invoice_date) as date'),
                DB::raw('SUM(CASE WHEN invoice_type = "sale" THEN total_amount ELSE 0 END) as revenue'),
                DB::raw('SUM(CASE WHEN invoice_type = "return" THEN total_amount ELSE 0 END) as return_amount'),
                DB::raw('COUNT(CASE WHEN invoice_type = "sale" THEN 1 END) as invoice_count')
            )
            ->groupBy(DB::raw('DATE(invoice_date)'));

        if ($branchShopId) {
            $query->where('branch_shop_id', $branchShopId);
        }

        return $query->orderBy('date')->get();
    }

    /**
     * Get invoices grouped by branch
     */
    public function groupByBranch($tenantId, $fromDate, $toDate)
    {
        return $this->model
            ->where('tenant_id', $tenantId)
            ->whereBetween('invoice_date', [$fromDate, $toDate])
            ->select(
                'branch_shop_id',
                DB::raw('SUM(CASE WHEN invoice_type = "sale" THEN total_amount ELSE 0 END) as revenue'),
                DB::raw('SUM(CASE WHEN invoice_type = "return" THEN total_amount ELSE 0 END) as return_amount'),
                DB::raw('COUNT(CASE WHEN invoice_type = "sale" THEN 1 END) as invoice_count')
            )
            ->groupBy('branch_shop_id')
            ->with('branchShop:id,name')
            ->get();
    }

    /**
     * Get invoices grouped by customer
     */
    public function groupByCustomer($tenantId, $fromDate, $toDate, $limit = 10)
    {
        return $this->model
            ->where('tenant_id', $tenantId)
            ->where('invoice_type', 'sale')
            ->whereBetween('invoice_date', [$fromDate, $toDate])
            ->select(
                'customer_id',
                DB::raw('SUM(total_amount) as revenue'),
                DB::raw('COUNT(*) as invoice_count')
            )
            ->groupBy('customer_id')
            ->orderByDesc('revenue')
            ->limit($limit)
            ->with('customer:id,name,customer_group')
            ->get();
    }

    /**
     * Get invoices grouped by sales channel
     */
    public function groupByChannel($tenantId, $fromDate, $toDate, $limit = 10)
    {
        return $this->model
            ->where('tenant_id', $tenantId)
            ->where('invoice_type', 'sale')
            ->whereBetween('invoice_date', [$fromDate, $toDate])
            ->select(
                'sales_channel',
                DB::raw('SUM(total_amount) as revenue'),
                DB::raw('COUNT(*) as invoice_count')
            )
            ->groupBy('sales_channel')
            ->orderByDesc('revenue')
            ->limit($limit)
            ->get();
    }

    /**
     * Get invoices grouped by seller
     */
    public function groupBySeller($tenantId, $fromDate, $toDate, $limit = 10)
    {
        return $this->model
            ->where('tenant_id', $tenantId)
            ->where('invoice_type', 'sale')
            ->whereBetween('invoice_date', [$fromDate, $toDate])
            ->select(
                'sold_by',
                DB::raw('SUM(total_amount) as revenue'),
                DB::raw('COUNT(*) as invoice_count')
            )
            ->groupBy('sold_by')
            ->orderByDesc('revenue')
            ->limit($limit)
            ->with('seller:id,full_name,username')
            ->get();
    }

    /**
     * Get total revenue for period
     */
    public function getTotalRevenue($tenantId, $fromDate, $toDate, $branchShopId = null)
    {
        $query = $this->model
            ->where('tenant_id', $tenantId)
            ->where('invoice_type', 'sale')
            ->whereBetween('invoice_date', [$fromDate, $toDate]);

        if ($branchShopId) {
            $query->where('branch_shop_id', $branchShopId);
        }

        return $query->sum('total_amount') ?? 0;
    }

    /**
     * Get total return amount for period
     */
    public function getTotalReturn($tenantId, $fromDate, $toDate, $branchShopId = null)
    {
        $query = $this->model
            ->where('tenant_id', $tenantId)
            ->where('invoice_type', 'return')
            ->whereBetween('invoice_date', [$fromDate, $toDate]);

        if ($branchShopId) {
            $query->where('branch_shop_id', $branchShopId);
        }

        return $query->sum('total_amount') ?? 0;
    }

    /**
     * Count invoices for period
     */
    public function countInvoices($tenantId, $fromDate, $toDate, $branchShopId = null)
    {
        $query = $this->model
            ->where('tenant_id', $tenantId)
            ->where('invoice_type', 'sale')
            ->whereBetween('invoice_date', [$fromDate, $toDate]);

        if ($branchShopId) {
            $query->where('branch_shop_id', $branchShopId);
        }

        return $query->count();
    }
}

