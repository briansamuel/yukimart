<?php

namespace App\Http\Controllers\Business;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Product;
use App\Services\TenantContextService;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * Business Invoice Controller
 * 
 * Handles all tenant-specific invoice operations including CRUD operations,
 * invoice generation from orders, payment tracking, and invoice management.
 */
class InvoiceController extends BaseBusinessController
{
    /**
     * Invoice service instance
     */
    protected InvoiceService $invoiceService;

    /**
     * Create a new controller instance
     */
    public function __construct(TenantContextService $tenantContext, InvoiceService $invoiceService)
    {
        parent::__construct($tenantContext);
        $this->invoiceService = $invoiceService;
    }

    /**
     * Display a listing of invoices
     */
    public function index(Request $request)
    {
        $this->authorizeTenantOperation('invoices.view');

        $searchParams = $this->getSearchParams();
        $paginationParams = $this->getPaginationParams();

        // Get tenant-scoped invoices
        $query = Invoice::query()
            ->with(['customer', 'order', 'items.product'])
            ->when($searchParams['search'], function ($q, $search) {
                $q->where(function ($query) use ($search) {
                    $query->where('invoice_code', 'like', "%{$search}%")
                          ->orWhere('customer_name', 'like', "%{$search}%")
                          ->orWhere('customer_phone', 'like', "%{$search}%");
                });
            })
            ->when($searchParams['status'], function ($q, $status) {
                $q->where('status', $status);
            })
            ->when($searchParams['date_from'], function ($q, $dateFrom) {
                $q->whereDate('invoice_date', '>=', $dateFrom);
            })
            ->when($searchParams['date_to'], function ($q, $dateTo) {
                $q->whereDate('invoice_date', '<=', $dateTo);
            })
            ->orderBy($paginationParams['sort'], $paginationParams['direction']);

        $invoices = $query->paginate($paginationParams['per_page']);

        // Get filter options
        $invoiceStatuses = ['đang xử lý', 'hoàn thành', 'đã huỷ', 'không giao được'];

        if ($request->ajax()) {
            return $this->businessResponse([
                'invoices' => $invoices->items(),
                'pagination' => [
                    'current_page' => $invoices->currentPage(),
                    'last_page' => $invoices->lastPage(),
                    'per_page' => $invoices->perPage(),
                    'total' => $invoices->total()
                ]
            ]);
        }

        return view('admin.business.invoices.index', compact('invoices', 'invoiceStatuses', 'searchParams'));
    }

    /**
     * Show the form for creating a new invoice
     */
    public function create()
    {
        $this->authorizeTenantOperation('invoices.create');

        $customers = Customer::select('id', 'customer_name', 'customer_phone')->get();
        $products = Product::where('product_status', 'publish')
                          ->select('id', 'product_name', 'sku', 'sale_price')
                          ->get();

        return view('admin.business.invoices.create', compact('customers', 'products'));
    }

    /**
     * Store a newly created invoice
     */
    public function store(Request $request)
    {
        $this->authorizeTenantOperation('invoices.create');

        $validator = Validator::make($request->all(), [
            'customer_id' => 'nullable|exists:customers,id',
            'customer_name' => 'required_if:customer_id,null|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'customer_email' => 'nullable|email|max:255',
            'customer_address' => 'nullable|string|max:500',
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:invoice_date',
            'payment_method' => 'required|in:cash,card,transfer,cod,other',
            'status' => 'required|in:đang xử lý,hoàn thành,đã huỷ,không giao được',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_amount' => 'nullable|numeric|min:0',
            'items.*.tax_amount' => 'nullable|numeric|min:0'
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return $this->businessError('Validation failed', 422, $validator->errors());
            }
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            // Generate invoice code
            $invoiceCode = $this->generateInvoiceCode();

            // Calculate totals
            $subtotal = 0;
            $totalDiscount = 0;
            $totalTax = 0;

            foreach ($request->items as $item) {
                $itemTotal = $item['quantity'] * $item['unit_price'];
                $subtotal += $itemTotal;
                $totalDiscount += $item['discount_amount'] ?? 0;
                $totalTax += $item['tax_amount'] ?? 0;
            }

            $total = $subtotal - $totalDiscount + $totalTax;

            // Create invoice
            $invoiceData = $validator->validated();
            $invoiceData['tenant_id'] = $this->getCurrentTenant()->id;
            $invoiceData['invoice_code'] = $invoiceCode;
            $invoiceData['subtotal'] = $subtotal;
            $invoiceData['discount_amount'] = $totalDiscount;
            $invoiceData['tax_amount'] = $totalTax;
            $invoiceData['total_amount'] = $total;
            $invoiceData['created_by'] = $this->getCurrentUser()->id;

            // Remove items from invoice data
            unset($invoiceData['items']);

            $invoice = Invoice::create($invoiceData);

            // Create invoice items
            foreach ($request->items as $item) {
                $invoiceItem = new InvoiceItem([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount_amount' => $item['discount_amount'] ?? 0,
                    'tax_amount' => $item['tax_amount'] ?? 0,
                    'total_amount' => ($item['quantity'] * $item['unit_price']) - ($item['discount_amount'] ?? 0) + ($item['tax_amount'] ?? 0)
                ]);

                $invoice->items()->save($invoiceItem);
            }

            DB::commit();

            if ($request->ajax()) {
                return $this->businessResponse($invoice->load('items.product'), 'Invoice created successfully', 201);
            }

            return redirect()->route('admin.business.invoices.show', $invoice)
                           ->with('success', 'Invoice created successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Invoice creation failed', [
                'tenant_id' => $this->getCurrentTenant()->id,
                'user_id' => $this->getCurrentUser()->id,
                'error' => $e->getMessage()
            ]);

            if ($request->ajax()) {
                return $this->businessError('Failed to create invoice', 500);
            }

            return back()->with('error', 'Failed to create invoice')->withInput();
        }
    }

    /**
     * Display the specified invoice
     */
    public function show(Invoice $invoice)
    {
        $this->authorizeTenantOperation('invoices.view');

        $invoice->load(['customer', 'order', 'items.product', 'payments']);

        if (request()->ajax()) {
            return $this->businessResponse($invoice);
        }

        return view('admin.business.invoices.show', compact('invoice'));
    }

    /**
     * Show the form for editing the specified invoice
     */
    public function edit(Invoice $invoice)
    {
        $this->authorizeTenantOperation('invoices.edit');

        // Only allow editing of draft and pending invoices
        if (!in_array($invoice->status, ['đang xử lý'])) {
            if (request()->ajax()) {
                return $this->businessError('Cannot edit invoice in current status', 400);
            }
            return back()->with('error', 'Cannot edit invoice in current status');
        }

        $customers = Customer::select('id', 'customer_name', 'customer_phone')->get();
        $products = Product::where('product_status', 'publish')
                          ->select('id', 'product_name', 'sku', 'sale_price')
                          ->get();

        $invoice->load('items.product');

        return view('admin.business.invoices.edit', compact('invoice', 'customers', 'products'));
    }

    /**
     * Create invoice from order
     */
    public function createFromOrder(Request $request, Order $order)
    {
        $this->authorizeTenantOperation('invoices.create');

        try {
            DB::beginTransaction();

            // Check if order already has an invoice
            if ($order->invoices()->count() > 0) {
                return $this->businessError('Order already has an invoice', 400);
            }

            // Generate invoice code
            $invoiceCode = $this->generateInvoiceCode();

            // Create invoice from order
            $invoice = Invoice::create([
                'tenant_id' => $this->getCurrentTenant()->id,
                'invoice_code' => $invoiceCode,
                'order_id' => $order->id,
                'customer_id' => $order->customer_id,
                'customer_name' => $order->customer_name,
                'customer_phone' => $order->customer_phone,
                'customer_email' => $order->customer_email,
                'customer_address' => $order->delivery_address,
                'invoice_date' => now(),
                'due_date' => now()->addDays(30),
                'payment_method' => $order->payment_method,
                'status' => 'đang xử lý',
                'subtotal' => $order->subtotal,
                'discount_amount' => $order->discount_amount,
                'tax_amount' => $order->tax_amount,
                'total_amount' => $order->total_amount,
                'notes' => $order->notes,
                'created_by' => $this->getCurrentUser()->id
            ]);

            // Create invoice items from order items
            foreach ($order->items as $orderItem) {
                $invoiceItem = new InvoiceItem([
                    'product_id' => $orderItem->product_id,
                    'quantity' => $orderItem->quantity,
                    'unit_price' => $orderItem->unit_price,
                    'discount_amount' => $orderItem->discount_amount,
                    'tax_amount' => $orderItem->tax_amount,
                    'total_amount' => $orderItem->total_amount
                ]);

                $invoice->items()->save($invoiceItem);
            }

            DB::commit();

            return $this->businessResponse($invoice->load('items.product'), 'Invoice created from order successfully', 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Invoice creation from order failed', [
                'order_id' => $order->id,
                'tenant_id' => $this->getCurrentTenant()->id,
                'user_id' => $this->getCurrentUser()->id,
                'error' => $e->getMessage()
            ]);

            return $this->businessError('Failed to create invoice from order', 500);
        }
    }

    /**
     * Generate unique invoice code
     */
    private function generateInvoiceCode(): string
    {
        $prefix = 'HD';
        $date = now()->format('Ymd');
        $tenant = $this->getCurrentTenant();
        
        // Get last invoice number for today
        $lastInvoice = Invoice::where('tenant_id', $tenant->id)
                             ->whereDate('created_at', now())
                             ->orderBy('id', 'desc')
                             ->first();

        $number = $lastInvoice ? (int)substr($lastInvoice->invoice_code, -4) + 1 : 1;
        
        return $prefix . $date . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Print invoice
     */
    public function print(Invoice $invoice)
    {
        $this->authorizeTenantOperation('invoices.view');

        $invoice->load(['customer', 'items.product']);

        return view('admin.business.invoices.print', compact('invoice'));
    }

    /**
     * Generate PDF invoice
     */
    public function pdf(Invoice $invoice)
    {
        $this->authorizeTenantOperation('invoices.view');

        $invoice->load(['customer', 'items.product']);

        // Generate PDF using DomPDF or similar
        // Implementation depends on PDF library choice
        
        return response()->json(['message' => 'PDF generation not implemented yet']);
    }
}
