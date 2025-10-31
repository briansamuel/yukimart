<?php

namespace App\Services\Tenant\Analytics;

use App\Repositories\Invoice\InvoiceRepository;
use App\Repositories\Invoice\InvoiceItemRepository;
use Carbon\Carbon;

class SalesAnalyticsService
{
    protected $invoiceRepo;
    protected $invoiceItemRepo;

    public function __construct(
        InvoiceRepository $invoiceRepo,
        InvoiceItemRepository $invoiceItemRepo
    ) {
        $this->invoiceRepo = $invoiceRepo;
        $this->invoiceItemRepo = $invoiceItemRepo;
    }

    /**
     * Get KPI overview for dashboard
     */
    public function getKpiOverview($tenantId, $fromDate, $toDate, $branchShopId = null)
    {
        $revenue = $this->invoiceRepo->getTotalRevenue($tenantId, $fromDate, $toDate, $branchShopId);
        $returnAmount = $this->invoiceRepo->getTotalReturn($tenantId, $fromDate, $toDate, $branchShopId);
        $invoiceCount = $this->invoiceRepo->countInvoices($tenantId, $fromDate, $toDate, $branchShopId);
        
        $netRevenue = $revenue - $returnAmount;
        $dayCount = $this->getDayCount($fromDate, $toDate);
        $avgPerDay = $dayCount > 0 ? $revenue / $dayCount : 0;

        // Get previous period data for comparison
        $prevFromDate = $this->getPreviousPeriodStart($fromDate, $toDate);
        $prevToDate = $this->getPreviousPeriodEnd($fromDate);
        
        $prevRevenue = $this->invoiceRepo->getTotalRevenue($tenantId, $prevFromDate, $prevToDate, $branchShopId);
        $prevInvoiceCount = $this->invoiceRepo->countInvoices($tenantId, $prevFromDate, $prevToDate, $branchShopId);

        return [
            'invoices' => [
                'total' => $invoiceCount,
                'avg_per_day' => $dayCount > 0 ? $invoiceCount / $dayCount : 0,
                'change_vs_prev' => $prevInvoiceCount > 0 ? (($invoiceCount - $prevInvoiceCount) / $prevInvoiceCount) * 100 : 0,
            ],
            'revenue' => [
                'total' => $revenue,
                'avg_per_day' => $avgPerDay,
                'change_vs_prev' => $prevRevenue > 0 ? (($revenue - $prevRevenue) / $prevRevenue) * 100 : 0,
            ],
            'returns' => [
                'total' => $returnAmount,
                'avg_per_day' => $dayCount > 0 ? $returnAmount / $dayCount : 0,
                'change_vs_prev' => 0, // TODO: Calculate previous period returns
            ],
            'net_revenue' => [
                'total' => $netRevenue,
                'avg_per_day' => $dayCount > 0 ? $netRevenue / $dayCount : 0,
                'change_vs_prev' => 0,
            ],
        ];
    }

    /**
     * Get daily time series data
     */
    public function getDailyTimeSeries($tenantId, $fromDate, $toDate, $branchShopId = null)
    {
        $data = $this->invoiceRepo->groupByDate($tenantId, $fromDate, $toDate, $branchShopId);
        
        return $data->map(function ($item) {
            return [
                'date' => $item->date,
                'revenue' => (float) $item->revenue,
                'return_amount' => (float) $item->return_amount,
                'net_revenue' => (float) ($item->revenue - $item->return_amount),
                'invoice_count' => (int) $item->invoice_count,
            ];
        })->toArray();
    }

    /**
     * Get revenue by channel
     */
    public function getRevenueByChannel($tenantId, $fromDate, $toDate, $limit = 10)
    {
        $data = $this->invoiceRepo->groupByChannel($tenantId, $fromDate, $toDate, $limit);
        
        return $data->map(function ($item) {
            return [
                'channel' => $item->sales_channel,
                'revenue' => (float) $item->revenue,
                'invoice_count' => (int) $item->invoice_count,
                'avg_per_invoice' => (int) $item->invoice_count > 0 ? (float) ($item->revenue / $item->invoice_count) : 0,
            ];
        })->toArray();
    }

    /**
     * Get revenue by seller
     */
    public function getRevenueBySeller($tenantId, $fromDate, $toDate, $limit = 10)
    {
        $data = $this->invoiceRepo->groupBySeller($tenantId, $fromDate, $toDate, $limit);
        
        return $data->map(function ($item) {
            return [
                'seller_id' => $item->sold_by,
                'seller_name' => $item->seller ? $item->seller->full_name : 'Unknown',
                'revenue' => (float) $item->revenue,
                'invoice_count' => (int) $item->invoice_count,
                'avg_per_invoice' => (int) $item->invoice_count > 0 ? (float) ($item->revenue / $item->invoice_count) : 0,
            ];
        })->toArray();
    }

    /**
     * Get day count between two dates
     */
    protected function getDayCount($fromDate, $toDate)
    {
        $from = Carbon::parse($fromDate);
        $to = Carbon::parse($toDate);
        return $to->diffInDays($from) + 1;
    }

    /**
     * Get previous period start date
     */
    protected function getPreviousPeriodStart($fromDate, $toDate)
    {
        $from = Carbon::parse($fromDate);
        $to = Carbon::parse($toDate);
        $dayCount = $to->diffInDays($from) + 1;
        return $from->copy()->subDays($dayCount)->toDateString();
    }

    /**
     * Get previous period end date
     */
    protected function getPreviousPeriodEnd($fromDate)
    {
        return Carbon::parse($fromDate)->subDay()->toDateString();
    }
}

