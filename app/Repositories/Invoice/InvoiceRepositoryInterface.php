<?php

namespace App\Repositories\Invoice;

use App\Repositories\RepositoryInterface;

interface InvoiceRepositoryInterface extends RepositoryInterface
{
    /**
     * Get invoices by date range and filters
     */
    public function getByDateRange($tenantId, $fromDate, $toDate, $branchShopId = null, $invoiceType = 'sale');

    /**
     * Get invoices grouped by date
     */
    public function groupByDate($tenantId, $fromDate, $toDate, $branchShopId = null);

    /**
     * Get invoices grouped by branch
     */
    public function groupByBranch($tenantId, $fromDate, $toDate);

    /**
     * Get invoices grouped by customer
     */
    public function groupByCustomer($tenantId, $fromDate, $toDate, $limit = 10);

    /**
     * Get invoices grouped by sales channel
     */
    public function groupByChannel($tenantId, $fromDate, $toDate, $limit = 10);

    /**
     * Get invoices grouped by seller
     */
    public function groupBySeller($tenantId, $fromDate, $toDate, $limit = 10);

    /**
     * Get total revenue for period
     */
    public function getTotalRevenue($tenantId, $fromDate, $toDate, $branchShopId = null);

    /**
     * Get total return amount for period
     */
    public function getTotalReturn($tenantId, $fromDate, $toDate, $branchShopId = null);

    /**
     * Count invoices for period
     */
    public function countInvoices($tenantId, $fromDate, $toDate, $branchShopId = null);
}

