<?php

namespace App\Http\Controllers\Admin\CMS;

use App\Http\Controllers\Controller;
use App\Services\OrderService;
use App\Services\ValidationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\OrdersExport;

class OrderController extends Controller
{
    protected $request;
    protected $validator;
    protected $orderService;

    public function __construct(Request $request, ValidationService $validator, OrderService $orderService)
    {
        $this->request = $request;
        $this->validator = $validator;
        $this->orderService = $orderService;
    }

    /**
     * Display orders list.
     */
    public function index(Request $request)
    {
        // Handle order code search via Code parameter
        $orderCodeSearch = null;
        $searchedOrder = null;
        if ($request->has('Code') && !empty($request->get('Code'))) {
            $orderCode = $request->get('Code');
            $orderCodeSearch = $orderCode;

            // Find order by order_code
            $searchedOrder = \App\Models\Order::where('order_code', $orderCode)->first();

            if ($searchedOrder) {
                \Log::info('Order code search in orders', [
                    'order_code' => $orderCode,
                    'order_id' => $searchedOrder->id,
                    'customer_name' => $searchedOrder->customer_id == 0 ? 'Khách lẻ' : ($searchedOrder->customer->name ?? 'N/A')
                ]);
            } else {
                \Log::warning('Order code not found in orders search', ['order_code' => $orderCode]);
            }
        }

        return view('admin.orders.index', compact('orderCodeSearch', 'searchedOrder'));
    }

    /**
     * Get orders via AJAX for DataTables.
     */
    public function ajaxGetOrders()
    {
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

        return response()->json([
            'draw' => $params['draw'] ?? 1,
            'recordsTotal' => $orders->total(),
            'recordsFiltered' => $orders->total(),
            'data' => $orders->getCollection()->map(function($order) {
                return [
                    'id' => $order->id,
                    'order_code' => $order->order_code,
                    'customer_name' => $order->customer_id == 0 ? 'Khách lẻ' : ($order->customer->name ?? 'N/A'),
                    'customer_phone' => $order->customer_id == 0 ? '' : ($order->customer->phone ?? 'N/A'),
                    'customer_email' => $order->customer_id == 0 ? '' : ($order->customer->email ?? 'N/A'),
                    'branch_shop_name' => $order->branchShop->name ?? 'N/A',
                    'creator_name' => $order->creator->full_name ?? 'N/A',
                    'seller_name' => $order->seller->full_name ?? 'N/A',
                    'total_quantity' => $order->total_quantity,
                    'total_amount' => $order->final_amount,
                    'amount_paid' => $order->amount_paid ?? 0,
                    'status' => $order->status,
                    'payment_status' => $order->payment_status,
                    'delivery_status' => $order->delivery_status,
                    'sales_channel' => $order->channel,
                    'payment_method' => $order->payment_method,
                    'created_at' => $order->created_at ? $order->created_at->toISOString() : null,
                    'actions' => $this->getActionButtons($order),
                ];
            })->toArray(),
            'success' => true
        ]);
    }

    /**
     * Show create order form.
     */
    public function add()
    {
        // Get user's branch shops
        $userBranchShops = auth()->user()->currentBranchShops()->get();

        // Get default branch shop
        $defaultBranchShop = auth()->user()->primaryBranchShop();
        if (!$defaultBranchShop && $userBranchShops->isNotEmpty()) {
            $defaultBranchShop = $userBranchShops->first();
        }

        return view('admin.orders.add', compact('userBranchShops', 'defaultBranchShop'));
    }

    /**
     * Store new order.
     */
    public function addAction()
    {
        try {
            $params = $this->request->all();
            
            // Validate order data
            $validator = $this->validator->make($params, 'order_fields');
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()->all()
                ], 422);
            }

            $result = $this->orderService->createOrder($params);
            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Order creation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tạo đơn hàng: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show edit order form.
     */
    public function edit($orderId)
    {
        try {
            $order = $this->orderService->getOrderById($orderId);
            return view('admin.orders.edit', compact('order'));

        } catch (\Exception $e) {
            return redirect()->route('admin.order.list')->with('error', 'Đơn hàng không tồn tại');
        }
    }

    /**
     * Update order.
     */
    public function editAction($orderId)
    {
        try {
            $params = $this->request->all();
            
            // Validate order data
            $validator = $this->validator->make($params, 'order_fields');
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()->all()
                ], 422);
            }

            $result = $this->orderService->updateOrder($orderId, $params);
            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Order update failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi cập nhật đơn hàng: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show order detail page.
     */
    public function show($id)
    {
        try {
            $order = $this->orderService->getOrderDetail($id);

            if (!$order) {
                abort(404, 'Order not found');
            }

            return view('admin.orders.show', compact('order'));

        } catch (\Exception $e) {
            Log::error('Order detail failed: ' . $e->getMessage());
            return redirect()->route('admin.order.list')->with('error', 'Đơn hàng không tồn tại');
        }
    }

    /**
     * Show order detail.
     */
    public function detail($orderId)
    {
        try {
            $order = $this->orderService->getOrderById($orderId);



            // If it's an AJAX request, return partial view for row expansion
            if (request()->ajax()) {
                return view('admin.orders.partials.detail_panel', compact('order'));
            }

            // Otherwise return full detail page
            return view('admin.orders.detail', compact('order'));

        } catch (\Exception $e) {
            \Log::error('Order Detail Error', ['error' => $e->getMessage(), 'order_id' => $orderId]);
            if (request()->ajax()) {
                return response()->json(['error' => 'Đơn hàng không tồn tại'], 404);
            }
            return redirect()->route('admin.order.list')->with('error', 'Đơn hàng không tồn tại');
        }
    }

    /**
     * Delete order.
     */
    public function delete($orderId)
    {
        try {
            $result = $this->orderService->deleteOrder($orderId);
            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi xóa đơn hàng: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete multiple orders.
     */
    public function deleteMany()
    {
        try {
            $orderIds = $this->request->input('ids', []);

            if (empty($orderIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không có đơn hàng nào được chọn để xóa.'
                ], 400);
            }

            $result = $this->orderService->deleteMultipleOrders($orderIds);
            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi xóa đơn hàng: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk delete orders (alias for deleteMany).
     */
    public function bulkDelete()
    {
        return $this->deleteMany();
    }

    /**
     * Update order status.
     */
    public function updateStatus($orderId)
    {
        try {
            $params = $this->request->all();
            $result = $this->orderService->updateOrderStatus(
                $orderId,
                $params['status'],
                $params['delivery_status'] ?? null,
                $params['payment_status'] ?? null,
                $params['internal_notes'] ?? null
            );
            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi cập nhật trạng thái: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Record payment for order.
     */
    public function recordPayment($orderId)
    {
        try {
            $params = $this->request->all();
            $result = $this->orderService->recordPayment($orderId, [
                'amount' => $params['amount'],
                'payment_method' => $params['payment_method'],
                'payment_reference' => $params['payment_reference'] ?? null,
                'payment_notes' => $params['payment_notes'] ?? null,
            ]);
            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi ghi nhận thanh toán: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Quick update order (for quick actions).
     */
    public function quickUpdate($orderId)
    {
        try {
            $params = $this->request->all();
            $result = $this->orderService->quickUpdateOrder($orderId, $params);
            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi cập nhật đơn hàng: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get order detail for modal.
     */
    public function getOrderDetail($orderId)
    {
        try {
            $order = $this->orderService->getOrderById($orderId);
            return view('admin.orders.partials.detail_panel', compact('order'));

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đơn hàng không tồn tại'
            ], 404);
        }
    }

    /**
     * Get order invoices for detail panel.
     */
    public function getOrderInvoices($orderId)
    {
        try {
            $order = $this->orderService->getOrderById($orderId);

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Đơn hàng không tồn tại'
                ], 404);
            }

            $invoices = $order->invoices()->with(['creator', 'payments'])->get();

            return response()->json([
                'success' => true,
                'data' => $invoices->map(function($invoice) {
                    return [
                        'id' => $invoice->id,
                        'invoice_number' => $invoice->invoice_number,
                        'invoice_date' => $invoice->invoice_date ? $invoice->invoice_date->format('d/m/Y H:i') : 'N/A',
                        'creator_name' => $invoice->creator->name ?? 'N/A',
                        'total_amount' => $invoice->total_amount,
                        'total_amount_formatted' => number_format($invoice->total_amount, 0, ',', '.') . ' ₫',
                        'status' => $invoice->status,
                        'status_badge' => $this->getInvoiceStatusBadge($invoice->status),
                        'detail_url' => route('admin.invoice.show', $invoice->id)
                    ];
                })
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tải danh sách hóa đơn: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get invoice status badge HTML.
     */
    private function getInvoiceStatusBadge($status)
    {
        return match($status) {
            'draft' => '<span class="badge badge-light-secondary">Nháp</span>',
            'sent' => '<span class="badge badge-light-primary">Đã gửi</span>',
            'paid' => '<span class="badge badge-light-success">Đã thanh toán</span>',
            'overdue' => '<span class="badge badge-light-danger">Quá hạn</span>',
            'cancelled' => '<span class="badge badge-dark">Đã hủy</span>',
            default => '<span class="badge badge-secondary">Đang xử lý</span>'
        };
    }

    /**
     * Get order data for JSON response.
     */
    public function getOrder($orderId)
    {
        try {
            $order = $this->orderService->getOrderById($orderId);
            return response()->json([
                'success' => true,
                'order' => [
                    'id' => $order->id,
                    'order_code' => $order->order_code,
                    'final_amount' => $order->final_amount,
                    'amount_paid' => $order->amount_paid,
                    'status' => $order->status,
                    'payment_status' => $order->payment_status,
                    'delivery_status' => $order->delivery_status,
                    'internal_notes' => $order->internal_notes,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đơn hàng không tồn tại'
            ], 404);
        }
    }

    /**
     * Get customers for dropdown.
     */
    public function getCustomers()
    {
        try {
            $search = $this->request->get('search', '');
            $customers = $this->orderService->getCustomersForDropdown($search);

            return response()->json([
                'success' => true,
                'data' => $customers
            ]);

        } catch (\Exception $e) {
         
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tải danh sách khách hàng'
            ], 500);
        }
    }

    /**
     * Get products for order.
     */
    public function getProducts()
    {
        try {
            $search = $this->request->get('search', '');
            $products = $this->orderService->getProductsForOrder($search);

            return response()->json([
                'success' => true,
                'data' => $products
            ]);

        } catch (\Exception $e) {
          
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tải danh sách sản phẩm'
            ], 500);
        }
    }

    /**
     * Get order statistics.
     */
    public function getStatistics()
    {
        try {
            $filters = $this->request->all();
            $statistics = $this->orderService->getOrderStatistics($filters);

            return response()->json([
                'success' => true,
                'data' => $statistics
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tải thống kê'
            ], 500);
        }
    }

    /**
     * Get initial data for order creation (recent customers and popular products).
     */
    public function getInitialData()
    {
        try {
            $result = $this->orderService->getInitialOrderData();
            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tải dữ liệu ban đầu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get product details for order.
     */
    public function getProductDetails($productId)
    {
        try {
            $result = $this->orderService->getProductDetails($productId);
            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tải thông tin sản phẩm: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create new customer during order creation.
     */
    public function createNewCustomer()
    {
        try {
            $customerData = $this->request->all();

            // Validate customer data
            $validation = $this->orderService->validateCustomerData($customerData);
            if (!$validation['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dữ liệu khách hàng không hợp lệ',
                    'errors' => $validation['errors']
                ], 422);
            }

            $result = $this->orderService->createNewCustomer($customerData);
            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tạo khách hàng mới: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if phone number exists.
     */
    public function checkPhoneExists()
    {
        try {
            $phone = $this->request->get('phone');

            if (empty($phone)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Số điện thoại không được để trống'
                ], 400);
            }

            $customer = \App\Models\Customer::where('phone', $phone)->first();

            if ($customer) {
                return response()->json([
                    'success' => true,
                    'exists' => true,
                    'customer' => [
                        'id' => $customer->id,
                        'name' => $customer->name,
                        'phone' => $customer->phone,
                        'email' => $customer->email,
                        'address' => $customer->address,
                        'customer_type' => $customer->customer_type
                    ]
                ]);
            }

            return response()->json([
                'success' => true,
                'exists' => false
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi kiểm tra số điện thoại: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Print order.
     */
    public function print($orderId)
    {
        try {
            $order = $this->orderService->getOrderById($orderId);
            return view('admin.orders.print', compact('order'));

        } catch (\Exception $e) {
            return redirect()->route('admin.order.list')->with('error', 'Đơn hàng không tồn tại');
        }
    }

    /**
     * Export single order to PDF.
     */
    public function exportOrder($orderId)
    {
        try {
            $order = $this->orderService->getOrderById($orderId);

            // Generate PDF using DomPDF or similar
            $pdf = app('dompdf.wrapper');
            $pdf->loadView('admin.orders.pdf', compact('order'));

            return $pdf->download('order-' . $order->order_code . '.pdf');

        } catch (\Exception $e) {
            return redirect()->route('admin.order.list')->with('error', 'Không thể xuất đơn hàng');
        }
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
                    <button class="btn btn-icon btn-light btn-active-light-success btn-sm" onclick="showPaymentModal(' . $order->id . ')" title="Ghi nhận thanh toán">
                        <i class="fas fa-wallet"></i>
                    </button>
                    <button class="btn btn-icon btn-light btn-active-light-warning btn-sm" onclick="showStatusModal(' . $order->id . ')" title="Cập nhật trạng thái">
                        <i class="fas fa-cog"></i>
                    </button>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-icon btn-light btn-active-light-info btn-sm dropdown-toggle" data-bs-toggle="dropdown" title="Thêm thao tác">
                            <i class="ki-duotone ki-dots-vertical fs-5">
                                <span class="path1"></span>
                                <span class="path2"></span>
                                <span class="path3"></span>
                            </i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="' . route('admin.order.edit', $order->id) . '">
                                <i class="fas fa-edit me-2"></i>Chỉnh sửa
                            </a></li>
                            <li><a class="dropdown-item" href="#" onclick="duplicateOrder(' . $order->id . ')">
                                <i class="fas fa-copy me-2"></i>Nhân bản
                            </a></li>
                            <li><a class="dropdown-item" href="' . route('admin.order.print', ['id' => $order->id, 'type' => 'invoice']) . '" target="_blank">
                                <i class="fas fa-print me-2"></i>In đơn hàng
                            </a></li>
                            <li><a class="dropdown-item" href="#" onclick="exportOrder(' . $order->id . ')">
                                <i class="fas fa-download me-2"></i>Xuất PDF
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#" onclick="showQuickActions(' . $order->id . ')">
                                <i class="fas fa-rocket me-2"></i>Thao tác nhanh
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="#" onclick="deleteOrder(' . $order->id . ')">
                                <i class="fas fa-trash me-2"></i>Xóa đơn hàng
                            </a></li>
                        </ul>
                    </div>
                </div>
            </div>';
    }

    /**
     * Bulk export orders to Excel.
     */
    public function bulkExport(Request $request)
    {
        try {
            $orderIds = $request->input('order_ids', []);

            if (empty($orderIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vui lòng chọn ít nhất một đơn hàng để xuất Excel.'
                ], 400);
            }

            // Get orders data
            $orders = $this->orderService->getOrdersByIds($orderIds);

            if ($orders->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy đơn hàng nào để xuất.'
                ], 404);
            }

            // Generate filename with timestamp
            $filename = 'orders_export_' . date('Y-m-d_H-i-s') . '.xlsx';

            // Export to Excel
            return Excel::download(new OrdersExport($orders), $filename);

        } catch (\Exception $e) {
            Log::error('Bulk export orders failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xuất file Excel: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk status update for orders.
     */
    public function bulkStatusUpdate(Request $request)
    {
        try {
            $orderIds = $request->input('order_ids', []);
            $orderStatus = $request->input('order_status');
            $paymentStatus = $request->input('payment_status');
            $deliveryStatus = $request->input('delivery_status');

            if (empty($orderIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vui lòng chọn ít nhất một đơn hàng để cập nhật.'
                ], 400);
            }

            if (empty($orderStatus) && empty($paymentStatus) && empty($deliveryStatus)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vui lòng chọn ít nhất một trạng thái để cập nhật.'
                ], 400);
            }

            $result = $this->orderService->bulkUpdateStatus($orderIds, [
                'order_status' => $orderStatus,
                'payment_status' => $paymentStatus,
                'delivery_status' => $deliveryStatus
            ]);

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Bulk status update failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi cập nhật trạng thái: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test export functionality.
     */
    public function testExport()
    {
        try {
            // Create mock order data for testing
            $mockOrders = collect([
                (object) [
                    'order_code' => 'HD001',
                    'customer_name' => 'Nguyễn Văn A',
                    'customer' => (object) [
                        'phone' => '0123456789',
                        'email' => 'test@example.com',
                        'address' => '123 Test Street, Hà Nội'
                    ],
                    'final_amount' => 500000,
                    'amount_paid' => 300000,
                    'status' => 'processing',
                    'payment_status' => 'partial',
                    'delivery_status' => 'preparing',
                    'channel' => 'direct',
                    'branchShop' => (object) ['name' => 'Chi nhánh Hà Nội'],
                    'creator' => (object) ['name' => 'Admin User'],
                    'seller' => (object) ['name' => 'Seller 1'],
                    'created_at' => now(),
                    'updated_at' => now(),
                    'notes' => 'Đơn hàng test export Excel'
                ],
                (object) [
                    'order_code' => 'HD002',
                    'customer_name' => 'Trần Thị B',
                    'customer' => (object) [
                        'phone' => '0987654321',
                        'email' => 'customer2@example.com',
                        'address' => '456 Another Street, TP.HCM'
                    ],
                    'final_amount' => 750000,
                    'amount_paid' => 750000,
                    'status' => 'completed',
                    'payment_status' => 'paid',
                    'delivery_status' => 'delivered',
                    'channel' => 'online',
                    'branchShop' => (object) ['name' => 'Chi nhánh TP.HCM'],
                    'creator' => (object) ['name' => 'Admin User'],
                    'seller' => (object) ['name' => 'Seller 2'],
                    'created_at' => now()->subDays(1),
                    'updated_at' => now()->subHours(2),
                    'notes' => 'Đơn hàng hoàn thành'
                ]
            ]);

            // Generate filename with timestamp
            $filename = 'orders_test_export_' . date('Y-m-d_H-i-s') . '.xlsx';

            // Export to Excel
            return Excel::download(new OrdersExport($mockOrders), $filename);

        } catch (\Exception $e) {
            Log::error('Test export failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi test export: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create test data for testing bulk actions.
     */
    public function createTestData()
    {
        try {
            // Create test customers first
            $testCustomers = [
                [
                    'name' => 'Nguyễn Văn A',
                    'phone' => '0123456789',
                    'email' => 'nguyenvana@example.com',
                    'address' => '123 Test Street, Hà Nội',
                    'customer_type' => 'individual',
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Trần Thị B',
                    'phone' => '0987654321',
                    'email' => 'tranthib@example.com',
                    'address' => '456 Another Street, TP.HCM',
                    'customer_type' => 'individual',
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Lê Văn C',
                    'phone' => '0369852147',
                    'email' => 'levanc@example.com',
                    'address' => '789 Third Street, Đà Nẵng',
                    'customer_type' => 'individual',
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            ];

            $customerIds = [];
            foreach ($testCustomers as $customerData) {
                $customerId = \DB::table('customers')->insertGetId($customerData);
                $customerIds[] = $customerId;
            }

            // Create test orders with correct column names
            $testOrders = [
                [
                    'order_code' => 'HD' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
                    'customer_id' => $customerIds[0],
                    'total_amount' => 500000,
                    'discount_amount' => 0,
                    'other_amount' => 0,
                    'final_amount' => 500000,
                    'amount_paid' => 300000,
                    'status' => 'processing',
                    'payment_status' => 'partial',
                    'delivery_status' => 'pending',
                    'channel' => 'direct',
                    'note' => 'Đơn hàng test cho bulk actions',
                    'created_by' => auth()->id(),
                    'sold_by' => auth()->id(),
                ],
                [
                    'order_code' => 'HD' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
                    'customer_id' => $customerIds[1],
                    'total_amount' => 750000,
                    'discount_amount' => 0,
                    'other_amount' => 0,
                    'final_amount' => 750000,
                    'amount_paid' => 750000,
                    'status' => 'completed',
                    'payment_status' => 'paid',
                    'delivery_status' => 'delivered',
                    'channel' => 'online',
                    'note' => 'Đơn hàng hoàn thành',
                    'created_by' => auth()->id(),
                    'sold_by' => auth()->id(),
                ],
                [
                    'order_code' => 'DH' . date('Ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
                    'customer_id' => $customerIds[2],
                    'total_amount' => 1200000,
                    'discount_amount' => 0,
                    'other_amount' => 0,
                    'final_amount' => 1200000,
                    'amount_paid' => 0,
                    'status' => 'pending',
                    'payment_status' => 'unpaid',
                    'delivery_status' => 'pending',
                    'channel' => 'other',
                    'note' => 'Đơn hàng chờ xử lý',
                    'created_by' => auth()->id(),
                    'sold_by' => auth()->id(),
                ]
            ];

            $createdOrders = [];
            foreach ($testOrders as $orderData) {
                $orderId = \DB::table('orders')->insertGetId(array_merge($orderData, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));

                $createdOrders[] = $orderId;
            }

            return response()->json([
                'success' => true,
                'message' => 'Đã tạo ' . count($createdOrders) . ' đơn hàng và ' . count($customerIds) . ' khách hàng test thành công!',
                'order_ids' => $createdOrders,
                'customer_ids' => $customerIds
            ]);

        } catch (\Exception $e) {
            Log::error('Create test data failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tạo test data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test bulk status update functionality.
     */
    public function testBulkStatusUpdate()
    {
        try {
            // Get some test orders (latest 3 orders)
            $testOrders = \DB::table('orders')
                ->orderBy('id', 'desc')
                ->limit(3)
                ->pluck('id')
                ->toArray();

            if (empty($testOrders)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không có đơn hàng nào để test. Vui lòng tạo test data trước.'
                ]);
            }

            // Test bulk status update with correct enum values
            $result = $this->orderService->bulkUpdateStatus($testOrders, [
                'order_status' => 'processing',
                'payment_status' => 'partial',
                'delivery_status' => 'picking'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Test bulk status update thành công!',
                'test_order_ids' => $testOrders,
                'update_result' => $result
            ]);

        } catch (\Exception $e) {
            Log::error('Test bulk status update failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi test bulk status update: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Debug orders in database.
     */
    public function debugOrders()
    {
        try {
            // Get latest 10 orders
            $orders = \DB::table('orders')
                ->select('id', 'order_code', 'status', 'created_at')
                ->orderBy('id', 'desc')
                ->limit(10)
                ->get();

            // Get orders created today
            $todayOrders = \DB::table('orders')
                ->select('id', 'order_code', 'status', 'created_at')
                ->whereDate('created_at', today())
                ->orderBy('id', 'desc')
                ->get();

            // Get total orders count
            $totalOrders = \DB::table('orders')->count();

            return response()->json([
                'success' => true,
                'total_orders' => $totalOrders,
                'latest_orders' => $orders,
                'today_orders' => $todayOrders,
                'today_date' => today()->format('Y-m-d'),
                'current_time' => now()->format('Y-m-d H:i:s')
            ]);

        } catch (\Exception $e) {
            Log::error('Debug orders failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi debug orders: ' . $e->getMessage()
            ], 500);
        }
    }
}
