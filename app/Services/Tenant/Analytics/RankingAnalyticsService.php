<?php

namespace App\Services\Tenant\Analytics;

use App\Repositories\Invoice\InvoiceRepository;
use App\Repositories\Invoice\InvoiceItemRepository;
use Illuminate\Support\Facades\DB;

class RankingAnalyticsService
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
     * Get top product categories
     */
    public function topProductCategories($tenantId, $fromDate, $toDate, $limit = 10)
    {
        $data = DB::table('invoice_items')
            ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->join('products', 'invoice_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->where('invoices.tenant_id', $tenantId)
            ->where('invoices.invoice_type', 'sale')
            ->whereBetween('invoices.invoice_date', [$fromDate, $toDate])
            ->select(
                'categories.id',
                'categories.name as category_name',
                DB::raw('SUM(invoice_items.line_total) as revenue'),
                DB::raw('COUNT(DISTINCT invoices.id) as invoice_count'),
                DB::raw('AVG(invoice_items.line_total) as avg_per_invoice')
            )
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('revenue')
            ->limit($limit)
            ->get();

        return $data->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->category_name,
                'revenue' => (float) $item->revenue,
                'invoice_count' => (int) $item->invoice_count,
                'avg_per_invoice' => (float) $item->avg_per_invoice,
            ];
        })->toArray();
    }

    /**
     * Get top products
     */
    public function topProducts($tenantId, $fromDate, $toDate, $limit = 10)
    {
        $data = DB::table('invoice_items')
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
            ->groupBy('invoice_items.product_id', 'invoice_items.product_name', 'invoice_items.product_sku')
            ->orderByDesc('revenue')
            ->limit($limit)
            ->get();

        return $data->map(function ($item) {
            return [
                'id' => $item->product_id,
                'name' => $item->product_name,
                'sku' => $item->product_sku,
                'revenue' => (float) $item->revenue,
                'quantity' => (int) $item->total_quantity,
                'invoice_count' => (int) $item->invoice_count,
                'avg_per_invoice' => (float) $item->avg_per_invoice,
            ];
        })->toArray();
    }

    /**
     * Get top customers
     */
    public function topCustomers($tenantId, $fromDate, $toDate, $limit = 10)
    {
        $data = DB::table('invoices')
            ->join('customers', 'invoices.customer_id', '=', 'customers.id')
            ->where('invoices.tenant_id', $tenantId)
            ->where('invoices.invoice_type', 'sale')
            ->whereBetween('invoices.invoice_date', [$fromDate, $toDate])
            ->select(
                'customers.id',
                'customers.name',
                'customers.customer_group',
                DB::raw('SUM(invoices.total_amount) as revenue'),
                DB::raw('COUNT(invoices.id) as invoice_count'),
                DB::raw('AVG(invoices.total_amount) as avg_per_invoice')
            )
            ->groupBy('customers.id', 'customers.name', 'customers.customer_group')
            ->orderByDesc('revenue')
            ->limit($limit)
            ->get();

        return $data->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'group' => $item->customer_group,
                'revenue' => (float) $item->revenue,
                'invoice_count' => (int) $item->invoice_count,
                'avg_per_invoice' => (float) $item->avg_per_invoice,
            ];
        })->toArray();
    }

    /**
     * Get top sales channels
     */
    public function topChannels($tenantId, $fromDate, $toDate, $limit = 10)
    {
        $data = DB::table('invoices')
            ->where('tenant_id', $tenantId)
            ->where('invoice_type', 'sale')
            ->whereBetween('invoice_date', [$fromDate, $toDate])
            ->select(
                'sales_channel',
                DB::raw('SUM(total_amount) as revenue'),
                DB::raw('COUNT(*) as invoice_count'),
                DB::raw('AVG(total_amount) as avg_per_invoice')
            )
            ->groupBy('sales_channel')
            ->orderByDesc('revenue')
            ->limit($limit)
            ->get();

        return $data->map(function ($item) {
            return [
                'channel' => $item->sales_channel,
                'revenue' => (float) $item->revenue,
                'invoice_count' => (int) $item->invoice_count,
                'avg_per_invoice' => (float) $item->avg_per_invoice,
            ];
        })->toArray();
    }

    /**
     * Get top sellers
     */
    public function topSellers($tenantId, $fromDate, $toDate, $limit = 10)
    {
        $data = DB::table('invoices')
            ->leftJoin('users', 'invoices.sold_by', '=', 'users.id')
            ->where('invoices.tenant_id', $tenantId)
            ->where('invoices.invoice_type', 'sale')
            ->whereBetween('invoices.invoice_date', [$fromDate, $toDate])
            ->select(
                'invoices.sold_by',
                DB::raw('COALESCE(users.full_name, users.username, "Unknown") as seller_name'),
                DB::raw('SUM(invoices.total_amount) as revenue'),
                DB::raw('COUNT(invoices.id) as invoice_count'),
                DB::raw('AVG(invoices.total_amount) as avg_per_invoice')
            )
            ->groupBy('invoices.sold_by')
            ->orderByDesc('revenue')
            ->limit($limit)
            ->get();

        return $data->map(function ($item) {
            return [
                'id' => $item->sold_by,
                'name' => $item->seller_name,
                'revenue' => (float) $item->revenue,
                'invoice_count' => (int) $item->invoice_count,
                'avg_per_invoice' => (float) $item->avg_per_invoice,
            ];
        })->toArray();
    }
}

