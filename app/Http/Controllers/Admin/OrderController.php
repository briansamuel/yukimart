<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Tenant\BaseTenantController;
use App\Services\OrderService;
use App\Services\ValidationService;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\OrdersExport;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Customer;
use App\Models\BranchShop;
use App\Models\Product;
use App\Models\User;
use App\Services\BranchContextService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class OrderController extends BaseTenantController
{

   


    /**
     * Branch context service instance
     */
    protected BranchContextService $branchContextService;

    protected $request;
    protected $validator;
    protected $orderService;

    /**
     * Create a new controller instance
     */
    public function __construct(BranchContextService $branchContextService, Request $request, ValidationService $validator, OrderService $orderService)
    {
        parent::__construct();
        $this->branchContextService = $branchContextService;
        $this->request = $request;
        $this->validator = $validator;
        $this->orderService = $orderService;
    }

    /**
     * Display a listing of orders.
     */
    public function index(Request $request)
    {
        try {
            // Handle order code search via Code parameter
            $orderCodeSearch = null;
            $searchedOrder = null;
            if ($request->has('Code') && !empty($request->get('Code'))) {
                $orderCode = $request->get('Code');
                $orderCodeSearch = $orderCode;

                // Find order by order_code
                $searchedOrder = Order::where('order_code', $orderCode)->first();

                if ($searchedOrder) {
                    // Order found by order_code
                } else {
                    Log::warning('Order code not found in orders search', ['order_code' => $orderCode]);
                }
            }

            // Get current branch shop context
            $currentBranchShop = $this->branchContextService->getCurrentBranchShop();

            // Get orders with relationships
            $orders = Order::with(['customer', 'branchShop', 'creator', 'seller', 'orderItems.product'])
                ->when($currentBranchShop, function ($query) use ($currentBranchShop) {
                    // Filter by current branch shop if context is available
                    return $query->where('branch_shop_id', $currentBranchShop->id);
                })
                ->when($request->search, function ($query, $search) {
                    return $query->where(function ($q) use ($search) {
                        $q->where('order_code', 'like', "%{$search}%")
                          ->orWhereHas('customer', function ($customerQuery) use ($search) {
                              $customerQuery->where('name', 'like', "%{$search}%")
                                          ->orWhere('phone', 'like', "%{$search}%");
                          });
                    });
                })
                ->when($request->status, function ($query, $status) {
                    return $query->whereIn('status', is_array($status) ? $status : [$status]);
                })
                ->when($request->date_from, function ($query, $dateFrom) {
                    return $query->whereDate('created_at', '>=', $dateFrom);
                })
                ->when($request->date_to, function ($query, $dateTo) {
                    return $query->whereDate('created_at', '<=', $dateTo);
                })
                ->orderBy('created_at', 'desc')
                ->paginate(15);

            // If AJAX request, return JSON
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'data' => $orders->items(),
                    'pagination' => [
                        'current_page' => $orders->currentPage(),
                        'last_page' => $orders->lastPage(),
                        'per_page' => $orders->perPage(),
                        'total' => $orders->total(),
                    ]
                ]);
            }

            return view('admin.orders.index', compact('orders', 'orderCodeSearch', 'searchedOrder'));
        } catch (Exception $e) {
            Log::error('Error loading orders: ' . $e->getMessage());

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi tải danh sách đơn hàng.'
                ], 500);
            }

            return back()->with('error', 'Có lỗi xảy ra khi tải danh sách đơn hàng.');
        }
    }

    /**
     * AJAX endpoint for DataTables - compatible with existing JavaScript
     */
    public function ajaxGetOrders()
    {
        try {
            $params = $this->request->all();

            // Enhanced filters for new interface
            $filters = [
                'status' => $params['status'] ?? null,
                'delivery_status' => $params['delivery_status'] ?? null,
                'payment_status' => $params['payment_status'] ?? null,
                'channel' => $params['channel'] ?? null,
                'payment_method' => $params['payment_method'] ?? null,
                'branch_shop_id' => $params['branch_shop_id'] ?? null,
                'creator_id' => $params['creator_id'] ?? null,
                'seller_id' => $params['seller_id'] ?? null,
                'date_from' => $params['date_from'] ?? null,
                'date_to' => $params['date_to'] ?? null,
                'time_filter' => $params['time_filter'] ?? null,
                'search' => $params['search'] ?? ($params['search']['value'] ?? null),
                'code' => $params['Code'] ?? $params['code'] ?? null, // Order code search (support both Code and code)
            ];

            // Debug log for order code parameter
            if (!empty($filters['code'])) {
                \Log::info('Order AJAX: Filtering by order code', ['code' => $filters['code']]);
            }

            $perPage = $params['per_page'] ?? ($params['length'] ?? 10);
            $page = $params['page'] ?? 1;
            $orders = $this->orderService->getOrders($filters, $perPage, $page);

            // Format data for DataTables
            $data = $orders->map(function ($order) {
                return [
                    'id' => $order->id,
                    'order_code' => $order->order_code ?? 'N/A',
                    'customer_name' => $order->customer_id == 0 ? 'Khách lẻ' : ($order->customer->name ?? 'N/A'),
                    'customer_phone' => $order->customer_id == 0 ? '' : ($order->customer->phone ?? ''),
                    'customer_email' => $order->customer_id == 0 ? '' : ($order->customer->email ?? ''),
                    'branch_shop_name' => $order->branchShop->name ?? 'N/A',
                    'creator_name' => $order->creator->full_name ?? 'N/A',
                    'seller_name' => $order->seller->full_name ?? 'N/A',
                    'total_quantity' => $order->total_quantity ?? 0,
                    'total_amount' => $order->total_amount ?? 0,
                    'total_amount_formatted' => number_format($order->total_amount ?? 0, 0, ',', '.') . ' ₫',
                    'paid_amount' => $order->amount_paid ?? 0,
                    'paid_amount_formatted' => number_format($order->amount_paid ?? 0, 0, ',', '.') . ' ₫',
                    'status' => $order->status ?? 'draft',
                    'status_label' => $this->getStatusLabel($order->status ?? 'draft'),
                    'payment_status' => $order->payment_status ?? 'pending',
                    'payment_status_label' => $this->getPaymentStatusLabel($order->payment_status ?? 'pending'),
                    'delivery_status' => $order->delivery_status ?? 'pending',
                    'delivery_status_label' => $this->getDeliveryStatusLabel($order->delivery_status ?? 'pending'),
                    'sales_channel' => $order->sales_channel ?? 'store',
                    'sales_channel_label' => $this->getSalesChannelLabel($order->sales_channel ?? 'store'),
                    'created_at' => $order->created_at ? $order->created_at->format('d/m/Y H:i') : 'N/A',
                    'actions' => $this->getActionButtons($order),
                ];
            });

            return response()->json([
                'draw' => $params['draw'] ?? 1,
                'recordsTotal' => $orders->total(),
                'recordsFiltered' => $orders->total(),
                'data' => $data,
                'success' => true,
                'message' => 'Orders data loaded successfully'
            ]);

        } catch (Exception $e) {
            Log::error('Error in ajaxGetOrders: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'draw' => intval($params['draw'] ?? 1),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'success' => false,
                'error' => 'Có lỗi xảy ra khi tải dữ liệu: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Show the form for creating a new order.
     */
    public function create()
    {
        $customers = Customer::select('id', 'name', 'phone')->get();
        $branchShops = BranchShop::select('id', 'name')->get();
        $products = Product::where('status', 'active')
                          ->select('id', 'name', 'sku', 'price')
                          ->get();

        return view('admin.orders.create', compact('customers', 'branchShops', 'products'));
    }

    /**
     * Store a newly created order in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'customer_id' => 'nullable|exists:customers,id',
                'branch_shop_id' => 'required|exists:branch_shops,id',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.price' => 'required|numeric|min:0',
                'notes' => 'nullable|string|max:1000',
                'payment_method' => 'required|in:cash,transfer,card,ewallet',
                'discount_amount' => 'nullable|numeric|min:0',
                'other_amount' => 'nullable|numeric|min:0',
            ]);

            // Create order logic here
            // This would involve creating the order and order items
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Đơn hàng đã được tạo thành công.',
                    'redirect' => route('orders.index')
                ]);
            }

            return redirect()->route('orders.index')
                ->with('success', 'Đơn hàng đã được tạo thành công.');

        } catch (Exception $e) {
            Log::error('Error creating order: ' . $e->getMessage());
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi tạo đơn hàng.',
                    'errors' => $e->getMessage()
                ], 500);
            }

            return back()->withInput()
                ->with('error', 'Có lỗi xảy ra khi tạo đơn hàng.');
        }
    }

    /**
     * Display the specified order.
     */
    public function show(Order $order)
    {
        try {
            $order->load(['customer', 'branchShop', 'creator', 'seller', 'orderItems.product']);
            
            return view('admin.orders.show', compact('order'));
        } catch (Exception $e) {
            Log::error('Error loading order: ' . $e->getMessage());
            
            return back()->with('error', 'Có lỗi xảy ra khi tải thông tin đơn hàng.');
        }
    }

    /**
     * Show order detail modal.
     */
    public function detail()
    {
        try {
            // Get order_id from route parameters explicitly to avoid conflict with subdomain parameter
            $order_id = request()->route('order_id');

            $order = Order::with(['customer', 'branchShop', 'creator', 'seller', 'orderItems.product'])
                         ->findOrFail($order_id);

            if (request()->ajax()) {
                return view('admin.orders.partials.detail_panel', compact('order'));
            }

            return view('admin.orders.detail', compact('order'));

        } catch (Exception $e) {
            Log::error('Order Detail Error', ['error' => $e->getMessage(), 'order_id' => $order_id]);
            
            if (request()->ajax()) {
                return response()->json(['error' => 'Đơn hàng không tồn tại'], 404);
            }
            
            return redirect()->route('tenant.orders.index')->with('error', 'Đơn hàng không tồn tại');
        }
    }

    /**
     * Get single order details for AJAX
     */
    public function get()
    {
        try {
            // Get order_id from route parameters explicitly to avoid conflict with subdomain parameter
            $order_id = request()->route('order_id');

            $order = Order::with(['customer', 'branchShop', 'creator', 'seller', 'orderItems.product'])
                          ->findOrFail($order_id);

            // Format order data for detail view
            $orderData = [
                'id' => $order->id,
                'order_code' => $order->order_code ?? 'N/A',
                'customer_name' => $order->customer_id == 0 ? 'Khách lẻ' : ($order->customer->name ?? 'N/A'),
                'customer_phone' => $order->customer_id == 0 ? '' : ($order->customer->phone ?? ''),
                'customer_email' => $order->customer_id == 0 ? '' : ($order->customer->email ?? ''),
                'branch_shop_name' => $order->branchShop->name ?? 'N/A',
                'creator_name' => $order->creator->full_name ?? 'N/A',
                'seller_name' => $order->seller->full_name ?? 'N/A',
                'total_quantity' => $order->total_quantity ?? 0,
                'total_amount' => $order->total_amount ?? 0,
                'total_amount_formatted' => number_format($order->total_amount ?? 0, 0, ',', '.') . ' ₫',
                'paid_amount' => $order->amount_paid ?? 0,
                'paid_amount_formatted' => number_format($order->amount_paid ?? 0, 0, ',', '.') . ' ₫',
                'status' => $order->status ?? 'draft',
                'status_label' => $this->getStatusLabel($order->status ?? 'draft'),
                'payment_status' => $order->payment_status ?? 'pending',
                'payment_status_label' => $this->getPaymentStatusLabel($order->payment_status ?? 'pending'),
                'delivery_status' => $order->delivery_status ?? 'pending',
                'delivery_status_label' => $this->getDeliveryStatusLabel($order->delivery_status ?? 'pending'),
                'sales_channel' => $order->channel ?? 'store',
                'sales_channel_label' => $this->getSalesChannelLabel($order->channel ?? 'store'),
                'created_at' => $order->created_at ? $order->created_at->format('d/m/Y H:i') : 'N/A',
                'notes' => $order->notes ?? '',
                'order_items' => $order->orderItems->map(function ($item) {
                    return [
                        'product_name' => $item->product->name ?? 'N/A',
                        'quantity' => $item->quantity ?? 0,
                        'price' => $item->price ?? 0,
                        'price_formatted' => number_format($item->price ?? 0, 0, ',', '.') . ' ₫',
                        'total' => ($item->quantity ?? 0) * ($item->price ?? 0),
                        'total_formatted' => number_format(($item->quantity ?? 0) * ($item->price ?? 0), 0, ',', '.') . ' ₫',
                    ];
                })
            ];

            return response()->json([
                'success' => true,
                'data' => $orderData,
                'message' => 'Order details loaded successfully'
            ]);

        } catch (Exception $e) {
            Log::error('Error in get order details: ' . $e->getMessage(), [
                'order_id' => $order_id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Có lỗi xảy ra khi tải chi tiết đơn hàng: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get status label for display.
     */
    private function getStatusLabel($status)
    {
        $labels = [
            'draft' => '<span class="badge badge-secondary">Nháp</span>',
            'pending' => '<span class="badge badge-warning">Chờ xử lý</span>',
            'processing' => '<span class="badge badge-info">Đang xử lý</span>',
            'completed' => '<span class="badge badge-success">Hoàn thành</span>',
            'cancelled' => '<span class="badge badge-danger">Đã hủy</span>',
        ];

        return $labels[$status] ?? '<span class="badge badge-secondary">' . ucfirst($status) . '</span>';
    }

    /**
     * Get payment status label for display.
     */
    private function getPaymentStatusLabel($status)
    {
        $labels = [
            'pending' => '<span class="badge badge-warning">Chờ thanh toán</span>',
            'partial' => '<span class="badge badge-info">Thanh toán một phần</span>',
            'paid' => '<span class="badge badge-success">Đã thanh toán</span>',
            'refunded' => '<span class="badge badge-danger">Đã hoàn tiền</span>',
        ];

        return $labels[$status] ?? '<span class="badge badge-secondary">' . ucfirst($status) . '</span>';
    }

    /**
     * Get delivery status label for display.
     */
    private function getDeliveryStatusLabel($status)
    {
        $labels = [
            'pending' => '<span class="badge badge-warning">Chờ xử lý</span>',
            'pickup' => '<span class="badge badge-info">Lấy hàng</span>',
            'shipping' => '<span class="badge badge-primary">Giao hàng</span>',
            'delivered' => '<span class="badge badge-success">Giao thành công</span>',
            'returned' => '<span class="badge badge-danger">Chuyển hoàn</span>',
            'cancelled' => '<span class="badge badge-dark">Đã hủy</span>',
        ];

        return $labels[$status] ?? '<span class="badge badge-secondary">' . ucfirst($status) . '</span>';
    }

    /**
     * Get sales channel label for display.
     */
    private function getSalesChannelLabel($channel)
    {
        $labels = [
            'store' => 'Bán tại cửa hàng',
            'online' => 'Bán online',
            'shopee' => 'Shopee',
            'lazada' => 'Lazada',
            'tiki' => 'Tiki',
            'marketplace' => 'Marketplace',
            'social' => 'Mạng xã hội',
            'phone' => 'Điện thoại',
        ];

        return $labels[$channel] ?? ucfirst($channel);
    }

    /**
     * Generate action buttons for order.
     */
    private function getActionButtons($order)
    {
        return '
            <div class="d-flex justify-content-end flex-shrink-0">
                <div class="btn-group" role="group">
                    <button class="btn btn-icon btn-light btn-active-light-primary btn-sm" onclick="showOrderDetail(' . $order->id . ')" title="Xem chi tiết">
                        <i class="fas fa-eye"></i>
                    </button>
                    <a href="#" onclick="showOrderDetail(' . $order->id . ')" class="btn btn-icon btn-light btn-active-light-info btn-sm" title="Chi tiết">
                        <i class="fas fa-info-circle"></i>
                    </a>
                </div>
            </div>';
    }

    /**
     * Create demo data for current branch shop.
     */
    public function createDemoData()
    {
        try {
            // Get current branch shop
            $currentBranchShop = $this->branchContextService->getCurrentBranchShop();

            if (!$currentBranchShop) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy chi nhánh hiện tại'
                ], 400);
            }

            // Get required data
            $customers = Customer::limit(10)->get();
            $products = Product::where('product_status', 'publish')->limit(10)->get();
            $users = User::limit(5)->get();

            if ($customers->isEmpty() || $products->isEmpty() || $users->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không đủ dữ liệu để tạo orders (cần customers, products, users)'
                ], 400);
            }

            $createdOrders = 0;
            $statuses = ['draft', 'processing', 'completed', 'cancelled'];
            $paymentStatuses = ['unpaid', 'partial', 'paid'];
            $deliveryStatuses = ['pending', 'picking', 'shipping', 'delivered'];

            // Create 10 demo orders for current branch shop
            for ($i = 1; $i <= 10; $i++) {
                $creator = $users->random();
                $seller = $users->random();
                $customer = $customers->random();

                // Random date within last 30 days
                $orderDate = Carbon::now()->subDays(rand(1, 30));

                $status = $statuses[array_rand($statuses)];
                $paymentStatus = $paymentStatuses[array_rand($paymentStatuses)];
                $deliveryStatus = $deliveryStatuses[array_rand($deliveryStatuses)];

                // Generate unique order code
                $orderCode = 'DH' . $orderDate->format('Ymd') . str_pad($i + time(), 6, '0', STR_PAD_LEFT);

                $order = Order::create([
                    'order_code' => $orderCode,
                    'customer_id' => $customer->id,
                    'branch_shop_id' => $currentBranchShop->id, // Use current branch shop
                    'status' => $status,
                    'payment_status' => $paymentStatus,
                    'delivery_status' => $deliveryStatus,
                    'channel' => 'direct',
                    'total_amount' => 0, // Will be calculated
                    'discount_amount' => rand(0, 50000),
                    'final_amount' => 0, // Will be calculated
                    'amount_paid' => 0, // Will be calculated
                    'created_by' => $creator->id,
                    'sold_by' => $seller->id,
                    'created_at' => $orderDate,
                    'updated_at' => $orderDate,
                ]);

                // Create order items
                $itemCount = rand(1, 4);
                $totalAmount = 0;

                for ($j = 1; $j <= $itemCount; $j++) {
                    $product = $products->random();
                    $quantity = rand(1, 3);
                    $unitPrice = $product->sale_price ?? rand(10000, 500000);
                    $discount = rand(0, $unitPrice * $quantity * 0.1); // Up to 10% discount
                    $totalPrice = ($quantity * $unitPrice) - $discount;
                    $totalAmount += $totalPrice;

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'discount' => $discount,
                        'total_price' => $totalPrice,
                        'created_at' => $orderDate,
                        'updated_at' => $orderDate,
                    ]);
                }

                // Update order totals
                $finalAmount = $totalAmount - $order->discount_amount;
                $amountPaid = $paymentStatus === 'paid' ? $finalAmount :
                             ($paymentStatus === 'partial' ? $finalAmount * 0.5 : 0);

                $order->update([
                    'total_amount' => $totalAmount,
                    'final_amount' => $finalAmount,
                    'amount_paid' => $amountPaid,
                ]);

                $createdOrders++;
            }

            return response()->json([
                'success' => true,
                'message' => "Đã tạo thành công {$createdOrders} orders cho chi nhánh: {$currentBranchShop->name}",
                'data' => [
                    'branch_shop' => $currentBranchShop->name,
                    'branch_shop_id' => $currentBranchShop->id,
                    'orders_created' => $createdOrders
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Error creating demo orders: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tạo demo data: ' . $e->getMessage()
            ], 500);
        }
    }
}
