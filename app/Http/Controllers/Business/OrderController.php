<?php

namespace App\Http\Controllers\Business;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Customer;
use App\Models\Product;
use App\Models\BranchShop;
use App\Services\TenantContextService;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * Business Order Controller
 * 
 * Handles all tenant-specific order operations including CRUD operations,
 * order processing, status management, and order fulfillment.
 */
class OrderController extends BaseBusinessController
{
    /**
     * Order service instance
     */
    protected OrderService $orderService;

    /**
     * Create a new controller instance
     */
    public function __construct(TenantContextService $tenantContext, OrderService $orderService)
    {
        parent::__construct($tenantContext);
        $this->orderService = $orderService;
    }

    /**
     * Display a listing of orders
     */
    public function index(Request $request)
    {
        $this->authorizeTenantOperation('orders.view');

        $searchParams = $this->getSearchParams();
        $paginationParams = $this->getPaginationParams();

        // Get tenant-scoped orders
        $query = Order::query()
            ->with(['customer', 'branchShop', 'items.product'])
            ->when($searchParams['search'], function ($q, $search) {
                $q->where(function ($query) use ($search) {
                    $query->where('order_code', 'like', "%{$search}%")
                          ->orWhere('customer_name', 'like', "%{$search}%")
                          ->orWhere('customer_phone', 'like', "%{$search}%");
                });
            })
            ->when($searchParams['status'], function ($q, $status) {
                $q->where('order_status', $status);
            })
            ->when($searchParams['branch_id'], function ($q, $branchId) {
                $q->where('branch_shop_id', $branchId);
            })
            ->when($searchParams['date_from'], function ($q, $dateFrom) {
                $q->whereDate('order_date', '>=', $dateFrom);
            })
            ->when($searchParams['date_to'], function ($q, $dateTo) {
                $q->whereDate('order_date', '<=', $dateTo);
            })
            ->orderBy($paginationParams['sort'], $paginationParams['direction']);

        $orders = $query->paginate($paginationParams['per_page']);

        // Get filter options
        $branchShops = BranchShop::select('id', 'name')->get();
        $orderStatuses = ['draft', 'pending', 'processing', 'shipped', 'delivered', 'cancelled'];

        if ($request->ajax()) {
            return $this->businessResponse([
                'orders' => $orders->items(),
                'pagination' => [
                    'current_page' => $orders->currentPage(),
                    'last_page' => $orders->lastPage(),
                    'per_page' => $orders->perPage(),
                    'total' => $orders->total()
                ]
            ]);
        }

        return view('admin.business.orders.index', compact('orders', 'branchShops', 'orderStatuses', 'searchParams'));
    }

    /**
     * Show the form for creating a new order
     */
    public function create()
    {
        $this->authorizeTenantOperation('orders.create');

        $customers = Customer::select('id', 'customer_name', 'customer_phone')->get();
        $branchShops = BranchShop::select('id', 'name')->get();
        $products = Product::where('product_status', 'publish')
                          ->select('id', 'product_name', 'sku', 'sale_price')
                          ->get();

        return view('admin.business.orders.create', compact('customers', 'branchShops', 'products'));
    }

    /**
     * Store a newly created order
     */
    public function store(Request $request)
    {
        $this->authorizeTenantOperation('orders.create');

        $validator = Validator::make($request->all(), [
            'customer_id' => 'nullable|exists:customers,id',
            'customer_name' => 'required_if:customer_id,null|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'customer_email' => 'nullable|email|max:255',
            'branch_shop_id' => 'required|exists:branch_shops,id',
            'order_date' => 'required|date',
            'delivery_date' => 'nullable|date|after_or_equal:order_date',
            'delivery_address' => 'nullable|string|max:500',
            'delivery_notes' => 'nullable|string|max:500',
            'payment_method' => 'required|in:cash,card,transfer,cod,other',
            'payment_status' => 'required|in:pending,paid,partial,refunded',
            'order_status' => 'required|in:draft,pending,processing,shipped,delivered,cancelled',
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

            // Generate order code
            $orderCode = $this->generateOrderCode();

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

            // Create order
            $orderData = $validator->validated();
            $orderData['tenant_id'] = $this->getCurrentTenant()->id;
            $orderData['order_code'] = $orderCode;
            $orderData['subtotal'] = $subtotal;
            $orderData['discount_amount'] = $totalDiscount;
            $orderData['tax_amount'] = $totalTax;
            $orderData['total_amount'] = $total;
            $orderData['created_by'] = $this->getCurrentUser()->id;

            // Remove items from order data
            unset($orderData['items']);

            $order = Order::create($orderData);

            // Create order items
            foreach ($request->items as $item) {
                $orderItem = new OrderItem([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount_amount' => $item['discount_amount'] ?? 0,
                    'tax_amount' => $item['tax_amount'] ?? 0,
                    'total_amount' => ($item['quantity'] * $item['unit_price']) - ($item['discount_amount'] ?? 0) + ($item['tax_amount'] ?? 0)
                ]);

                $order->items()->save($orderItem);
            }

            DB::commit();

            if ($request->ajax()) {
                return $this->businessResponse($order->load('items.product'), 'Order created successfully', 201);
            }

            return redirect()->route('admin.business.orders.show', $order)
                           ->with('success', 'Order created successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order creation failed', [
                'tenant_id' => $this->getCurrentTenant()->id,
                'user_id' => $this->getCurrentUser()->id,
                'error' => $e->getMessage()
            ]);

            if ($request->ajax()) {
                return $this->businessError('Failed to create order', 500);
            }

            return back()->with('error', 'Failed to create order')->withInput();
        }
    }

    /**
     * Display the specified order
     */
    public function show(Order $order)
    {
        $this->authorizeTenantOperation('orders.view');

        $order->load(['customer', 'branchShop', 'items.product', 'payments', 'invoices']);

        if (request()->ajax()) {
            return $this->businessResponse($order);
        }

        return view('admin.business.orders.show', compact('order'));
    }

    /**
     * Show the form for editing the specified order
     */
    public function edit(Order $order)
    {
        $this->authorizeTenantOperation('orders.edit');

        // Only allow editing of draft and pending orders
        if (!in_array($order->order_status, ['draft', 'pending'])) {
            if (request()->ajax()) {
                return $this->businessError('Cannot edit order in current status', 400);
            }
            return back()->with('error', 'Cannot edit order in current status');
        }

        $customers = Customer::select('id', 'customer_name', 'customer_phone')->get();
        $branchShops = BranchShop::select('id', 'name')->get();
        $products = Product::where('product_status', 'publish')
                          ->select('id', 'product_name', 'sku', 'sale_price')
                          ->get();

        $order->load('items.product');

        return view('admin.business.orders.edit', compact('order', 'customers', 'branchShops', 'products'));
    }

    /**
     * Update the specified order
     */
    public function update(Request $request, Order $order)
    {
        $this->authorizeTenantOperation('orders.edit');

        // Only allow editing of draft and pending orders
        if (!in_array($order->order_status, ['draft', 'pending'])) {
            if ($request->ajax()) {
                return $this->businessError('Cannot edit order in current status', 400);
            }
            return back()->with('error', 'Cannot edit order in current status');
        }

        $validator = Validator::make($request->all(), [
            'customer_id' => 'nullable|exists:customers,id',
            'customer_name' => 'required_if:customer_id,null|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'customer_email' => 'nullable|email|max:255',
            'branch_shop_id' => 'required|exists:branch_shops,id',
            'order_date' => 'required|date',
            'delivery_date' => 'nullable|date|after_or_equal:order_date',
            'delivery_address' => 'nullable|string|max:500',
            'delivery_notes' => 'nullable|string|max:500',
            'payment_method' => 'required|in:cash,card,transfer,cod,other',
            'payment_status' => 'required|in:pending,paid,partial,refunded',
            'order_status' => 'required|in:draft,pending,processing,shipped,delivered,cancelled',
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

            // Update order
            $orderData = $validator->validated();
            $orderData['subtotal'] = $subtotal;
            $orderData['discount_amount'] = $totalDiscount;
            $orderData['tax_amount'] = $totalTax;
            $orderData['total_amount'] = $total;
            $orderData['updated_by'] = $this->getCurrentUser()->id;

            // Remove items from order data
            unset($orderData['items']);

            $order->update($orderData);

            // Delete existing items and create new ones
            $order->items()->delete();

            foreach ($request->items as $item) {
                $orderItem = new OrderItem([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount_amount' => $item['discount_amount'] ?? 0,
                    'tax_amount' => $item['tax_amount'] ?? 0,
                    'total_amount' => ($item['quantity'] * $item['unit_price']) - ($item['discount_amount'] ?? 0) + ($item['tax_amount'] ?? 0)
                ]);

                $order->items()->save($orderItem);
            }

            DB::commit();

            if ($request->ajax()) {
                return $this->businessResponse($order->load('items.product'), 'Order updated successfully');
            }

            return redirect()->route('admin.business.orders.show', $order)
                           ->with('success', 'Order updated successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order update failed', [
                'order_id' => $order->id,
                'tenant_id' => $this->getCurrentTenant()->id,
                'user_id' => $this->getCurrentUser()->id,
                'error' => $e->getMessage()
            ]);

            if ($request->ajax()) {
                return $this->businessError('Failed to update order', 500);
            }

            return back()->with('error', 'Failed to update order')->withInput();
        }
    }

    /**
     * Update order status
     */
    public function updateStatus(Request $request, Order $order)
    {
        $this->authorizeTenantOperation('orders.edit');

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:draft,pending,processing,shipped,delivered,cancelled',
            'notes' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return $this->businessError('Validation failed', 422, $validator->errors());
        }

        try {
            $order->update([
                'order_status' => $request->status,
                'status_notes' => $request->notes,
                'updated_by' => $this->getCurrentUser()->id
            ]);

            return $this->businessResponse($order, 'Order status updated successfully');

        } catch (\Exception $e) {
            Log::error('Order status update failed', [
                'order_id' => $order->id,
                'tenant_id' => $this->getCurrentTenant()->id,
                'user_id' => $this->getCurrentUser()->id,
                'error' => $e->getMessage()
            ]);

            return $this->businessError('Failed to update order status', 500);
        }
    }

    /**
     * Generate unique order code
     */
    private function generateOrderCode(): string
    {
        $prefix = 'ORD';
        $date = now()->format('Ymd');
        $tenant = $this->getCurrentTenant();
        
        // Get last order number for today
        $lastOrder = Order::where('tenant_id', $tenant->id)
                         ->whereDate('created_at', now())
                         ->orderBy('id', 'desc')
                         ->first();

        $number = $lastOrder ? (int)substr($lastOrder->order_code, -4) + 1 : 1;
        
        return $prefix . $date . str_pad($number, 4, '0', STR_PAD_LEFT);
    }
}
