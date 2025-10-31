<?php

namespace App\Services\Tenant\Analytics;

use App\Models\Product;
use Illuminate\Support\Facades\DB;

class ProfitAnalyticsService
{
    /**
     * Calculate cost of goods for period
     * Using product cost_price * quantity from invoice items
     */
    public function getTotalCostOfGoods($tenantId, $fromDate, $toDate, $branchShopId = null)
    {
        $query = DB::table('invoice_items')
            ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->join('products', 'invoice_items.product_id', '=', 'products.id')
            ->where('invoices.tenant_id', $tenantId)
            ->where('invoices.invoice_type', 'sale')
            ->whereBetween('invoices.invoice_date', [$fromDate, $toDate])
            ->select(DB::raw('SUM(products.cost_price * invoice_items.quantity) as total_cost'));

        if ($branchShopId) {
            $query->where('invoices.branch_shop_id', $branchShopId);
        }

        $result = $query->first();
        return $result->total_cost ?? 0;
    }

    /**
     * Calculate gross profit
     */
    public function getGrossProfit($tenantId, $fromDate, $toDate, $branchShopId = null, $netRevenue = 0)
    {
        $costOfGoods = $this->getTotalCostOfGoods($tenantId, $fromDate, $toDate, $branchShopId);
        return $netRevenue - $costOfGoods;
    }

    /**
     * Get profit margin percentage
     */
    public function getProfitMargin($netRevenue, $grossProfit)
    {
        if ($netRevenue <= 0) {
            return 0;
        }
        return ($grossProfit / $netRevenue) * 100;
    }

    /**
     * Get cost breakdown by category
     */
    public function getCostByCategory($tenantId, $fromDate, $toDate, $limit = 10)
    {
        return DB::table('invoice_items')
            ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->join('products', 'invoice_items.product_id', '=', 'products.id')
            ->where('invoices.tenant_id', $tenantId)
            ->where('invoices.invoice_type', 'sale')
            ->whereBetween('invoices.invoice_date', [$fromDate, $toDate])
            ->select(
                'products.category_id',
                DB::raw('SUM(products.cost_price * invoice_items.quantity) as total_cost'),
                DB::raw('SUM(invoice_items.line_total) as revenue'),
                DB::raw('SUM(invoice_items.line_total) - SUM(products.cost_price * invoice_items.quantity) as gross_profit')
            )
            ->groupBy('products.category_id')
            ->orderByDesc('gross_profit')
            ->limit($limit)
            ->get();
    }

    /**
     * Get cost breakdown by product
     */
    public function getCostByProduct($tenantId, $fromDate, $toDate, $limit = 10)
    {
        return DB::table('invoice_items')
            ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->join('products', 'invoice_items.product_id', '=', 'products.id')
            ->where('invoices.tenant_id', $tenantId)
            ->where('invoices.invoice_type', 'sale')
            ->whereBetween('invoices.invoice_date', [$fromDate, $toDate])
            ->select(
                'invoice_items.product_id',
                'invoice_items.product_name',
                DB::raw('SUM(products.cost_price * invoice_items.quantity) as total_cost'),
                DB::raw('SUM(invoice_items.line_total) as revenue'),
                DB::raw('SUM(invoice_items.line_total) - SUM(products.cost_price * invoice_items.quantity) as gross_profit')
            )
            ->groupBy('invoice_items.product_id')
            ->orderByDesc('gross_profit')
            ->limit($limit)
            ->get();
    }
}

