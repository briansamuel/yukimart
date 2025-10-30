<?php

namespace App\Services\Tenant\Analytics;

use App\Repositories\Invoice\InvoiceRepository;
use Illuminate\Support\Facades\DB;

class BranchAnalyticsService
{
    protected $invoiceRepo;
    protected $profitService;

    public function __construct(
        InvoiceRepository $invoiceRepo,
        ProfitAnalyticsService $profitService
    ) {
        $this->invoiceRepo = $invoiceRepo;
        $this->profitService = $profitService;
    }

    /**
     * Get branch breakdown for period
     */
    public function getBranchBreakdown($tenantId, $fromDate, $toDate)
    {
        $data = $this->invoiceRepo->groupByBranch($tenantId, $fromDate, $toDate);
        
        return $data->map(function ($item) use ($tenantId, $fromDate, $toDate) {
            $revenue = (float) $item->revenue;
            $returnAmount = (float) $item->return_amount;
            $netRevenue = $revenue - $returnAmount;
            
            // Calculate cost of goods for this branch
            $costOfGoods = $this->getBranchCostOfGoods($tenantId, $fromDate, $toDate, $item->branch_shop_id);
            $grossProfit = $netRevenue - $costOfGoods;
            
            return [
                'branch_id' => $item->branch_shop_id,
                'branch_name' => $item->branchShop ? $item->branchShop->name : 'Unknown',
                'revenue' => $revenue,
                'return_amount' => $returnAmount,
                'net_revenue' => $netRevenue,
                'total_cost' => $costOfGoods,
                'gross_profit' => $grossProfit,
                'profit_margin' => $netRevenue > 0 ? ($grossProfit / $netRevenue) * 100 : 0,
                'invoice_count' => (int) $item->invoice_count,
            ];
        })->toArray();
    }

    /**
     * Get cost of goods for specific branch
     */
    protected function getBranchCostOfGoods($tenantId, $fromDate, $toDate, $branchShopId)
    {
        return DB::table('invoice_items')
            ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->join('products', 'invoice_items.product_id', '=', 'products.id')
            ->where('invoices.tenant_id', $tenantId)
            ->where('invoices.branch_shop_id', $branchShopId)
            ->where('invoices.invoice_type', 'sale')
            ->whereBetween('invoices.invoice_date', [$fromDate, $toDate])
            ->sum(DB::raw('products.cost_price * invoice_items.quantity')) ?? 0;
    }

    /**
     * Get branch performance comparison
     */
    public function getBranchComparison($tenantId, $fromDate, $toDate)
    {
        $breakdown = $this->getBranchBreakdown($tenantId, $fromDate, $toDate);
        
        // Sort by revenue descending
        usort($breakdown, function ($a, $b) {
            return $b['revenue'] <=> $a['revenue'];
        });
        
        return $breakdown;
    }

    /**
     * Get top performing branch
     */
    public function getTopBranch($tenantId, $fromDate, $toDate)
    {
        $breakdown = $this->getBranchBreakdown($tenantId, $fromDate, $toDate);
        
        if (empty($breakdown)) {
            return null;
        }
        
        usort($breakdown, function ($a, $b) {
            return $b['revenue'] <=> $a['revenue'];
        });
        
        return $breakdown[0];
    }
}

