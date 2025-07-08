<?php

namespace App\Http\Controllers\Admin\CMS;

use App\Http\Controllers\Controller;
use App\Services\OrderService;
use App\Services\ValidationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
        // Handle barcode search via Code parameter
        $barcodeSearch = null;
        $searchedProduct = null;
        if ($request->has('Code') && !empty($request->get('Code'))) {
            $barcode = $request->get('Code');
            $barcodeSearch = $barcode;

            // Find product by barcode
            $searchedProduct = \App\Models\Product::where('barcode', $barcode)->first();

            if ($searchedProduct) {
                \Log::info('Barcode search in orders', [
                    'barcode' => $barcode,
                    'product_id' => $searchedProduct->id,
                    'product_name' => $searchedProduct->name
                ]);
            } else {
                \Log::warning('Barcode not found in orders search', ['barcode' => $barcode]);
            }
        }

        return view('admin.orders.index', compact('barcodeSearch', 'searchedProduct'));
    }

    /**
     * Get orders via AJAX for DataTables.
     */
    public function ajaxGetOrders()
    {
        $params = $this->request->all();
        
        $filters = [
            'status' => $params['status'] ?? null,
            'delivery_status' => $params['delivery_status'] ?? null,
            'payment_status' => $params['payment_status'] ?? null,
            'channel' => $params['channel'] ?? null,
            'branch_shop_id' => $params['branch_shop_id'] ?? null,
            'date_from' => $params['date_from'] ?? null,
            'date_to' => $params['date_to'] ?? null,
            'search' => $params['search']['value'] ?? null,
            'Code' => $params['Code'] ?? null, // Barcode search
        ];

        $perPage = $params['length'] ?? 25;
        $orders = $this->orderService->getOrders($filters, $perPage);

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
                    'branch_name' => $order->branch->name ?? 'N/A',
                    'total_quantity' => $order->total_quantity,
                    'final_amount' => $order->final_amount,
                    'amount_paid' => $order->amount_paid,
                    'status' => $order->status,
                    'payment_status' => $order->payment_status,
                    'delivery_status' => $order->delivery_status,
                    'channel' => $order->channel,
                    'created_at' => $order->created_at->format('d/m/Y H:i'),
                    'actions' => $this->getActionButtons($order),
                ];
            })->toArray()
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
            return view('admin.orders.detail', compact('order'));

        } catch (\Exception $e) {
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
            return view('admin.orders.partials.detail', compact('order'));

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đơn hàng không tồn tại'
            ], 404);
        }
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
                            <li><a class="dropdown-item" href="' . route('admin.order.print', $order->id) . '" target="_blank">
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
}
