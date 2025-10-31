<?php

namespace App\Repositories\Invoice;

use App\Repositories\BaseRepository;
use App\Models\InvoiceItem;
use Illuminate\Support\Facades\DB;

class InvoiceItemRepository extends BaseRepository
{
    public function getModel()
    {
        return InvoiceItem::class;
    }

    /**
     * Get invoice items grouped by product category
     */
    public function groupByCategory($tenantId, $fromDate, $toDate, $limit = 10)
    {
        return $this->model
            ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->join('products', 'invoice_items.product_id', '=', 'products.id')
            ->where('invoices.tenant_id', $tenantId)
            ->where('invoices.invoice_type', 'sale')
            ->whereBetween('invoices.invoice_date', [$fromDate, $toDate])
            ->select(
                'products.category_id',
                DB::raw('SUM(invoice_items.line_total) as revenue'),
                DB::raw('COUNT(DISTINCT invoices.id) as invoice_count'),
                DB::raw('AVG(invoice_items.line_total) as avg_per_invoice')
            )
            ->groupBy('products.category_id')
            ->orderByDesc('revenue')
            ->limit($limit)
            ->with('product.category:id,name')
            ->get();
    }

    /**
     * Get invoice items grouped by product
     */
    public function groupByProduct($tenantId, $fromDate, $toDate, $limit = 10)
    {
        return $this->model
            ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->where('invoices.tenant_id', $tenantId)
            ->where('invoices.invoice_type', 'sale')
            ->whereBetween('invoices.invoice_date', [$fromDate, $toDate])
            ->select(
                'invoice_items.product_id',
                'invoice_items.product_name',
                'invoice_items.product_sku',
                DB::raw('SUM(invoice_items.line_total) as revenue'),
                DB::raw('SUM(invoice_items.quantity) as total_quantity'),
                DB::raw('COUNT(DISTINCT invoices.id) as invoice_count'),
                DB::raw('AVG(invoice_items.line_total) as avg_per_invoice')
            )
            ->groupBy('invoice_items.product_id')
            ->orderByDesc('revenue')
            ->limit($limit)
            ->get();
    }

    /**
     * Get total line amount for period
     */
    public function getTotalLineAmount($tenantId, $fromDate, $toDate, $branchShopId = null)
    {
        $query = $this->model
            ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->where('invoices.tenant_id', $tenantId)
            ->where('invoices.invoice_type', 'sale')
            ->whereBetween('invoices.invoice_date', [$fromDate, $toDate]);

        if ($branchShopId) {
            $query->where('invoices.branch_shop_id', $branchShopId);
        }

        return $query->sum('invoice_items.line_total') ?? 0;
    }

    /**
     * Get items by invoice
     */
    public function getByInvoice($invoiceId)
    {
        return $this->model
            ->where('invoice_id', $invoiceId)
            ->with('product:id,name,sku')
            ->orderBy('sort_order')
            ->get();
    }
}

