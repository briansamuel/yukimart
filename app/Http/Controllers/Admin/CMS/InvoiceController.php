<?php

namespace App\Http\Controllers\Admin\CMS;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Product;
use App\Models\BranchShop;
use App\Models\Order;
use App\Models\User;
use App\Services\InvoiceService;
use App\Services\InvoicePrintService;
use App\Services\ValidationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class InvoiceController extends Controller
{
    protected $invoiceService;
    protected $validationService;
    protected $printService;
    protected $request;

    public function __construct(InvoiceService $invoiceService, ValidationService $validationService, InvoicePrintService $printService, Request $request)
    {
        $this->invoiceService = $invoiceService;
        $this->validationService = $validationService;
        $this->printService = $printService;
        $this->request = $request;
    }

    /**
     * Display invoice list.
     */
    public function index(Request $request)
    {
        $branchShops = BranchShop::active()->get();

        // Prepare filter data
        $filterData = $this->getFilterData();

        // Handle barcode search via Code parameter
        $barcodeSearch = null;
        $searchedProduct = null;
        if ($request->has('Code') && !empty($request->get('Code'))) {
            $barcode = $request->get('Code');
            $barcodeSearch = $barcode;

            // Find product by barcode
            $searchedProduct = Product::where('barcode', $barcode)->first();

            if ($searchedProduct) {
                Log::info('Barcode search in invoices', [
                    'barcode' => $barcode,
                    'product_id' => $searchedProduct->id,
                    'product_name' => $searchedProduct->name
                ]);
            } else {
                Log::warning('Barcode not found in invoices search', ['barcode' => $barcode]);
            }
        }

        return view('admin.invoice.index', compact('branchShops', 'barcodeSearch', 'searchedProduct') + $filterData);
    }

    /**
     * Get filter data for invoice listing
     */
    private function getFilterData()
    {
        // Status options
        $statuses = [
            ['value' => 'draft', 'label' => 'Nháp', 'checked' => false],
            ['value' => 'processing', 'label' => 'Đang xử lý', 'checked' => true],
            ['value' => 'completed', 'label' => 'Hoàn thành', 'checked' => true],
            ['value' => 'cancelled', 'label' => 'Đã hủy', 'checked' => false],
            ['value' => 'failed', 'label' => 'Không giao được', 'checked' => false],
        ];

        // Delivery status options
        $deliveryStatuses = [
            ['value' => 'pending', 'label' => 'Chờ xử lý', 'has_plus' => false],
            ['value' => 'pickup', 'label' => 'Lấy hàng', 'has_plus' => true],
            ['value' => 'shipping', 'label' => 'Giao hàng', 'has_plus' => true],
            ['value' => 'delivered', 'label' => 'Giao thành công', 'has_plus' => true],
            ['value' => 'returned', 'label' => 'Chuyển hoàn', 'has_plus' => true],
            ['value' => 'return_completed', 'label' => 'Đã chuyển hoàn', 'has_plus' => false],
            ['value' => 'cancelled', 'label' => 'Đã hủy', 'has_plus' => false],
        ];

        // Sales channels
        $salesChannels = [
            ['value' => 'offline', 'label' => 'Bán tại cửa hàng'],
            ['value' => 'online', 'label' => 'Bán online'],
            ['value' => 'shopee', 'label' => 'Shopee'],
            ['value' => 'lazada', 'label' => 'Lazada'],
            ['value' => 'tiki', 'label' => 'Tiki'],
        ];

        // Delivery partners
        $deliveryPartners = [
            ['value' => 'ghn', 'label' => 'Giao Hàng Nhanh'],
            ['value' => 'ghtk', 'label' => 'Giao Hàng Tiết Kiệm'],
            ['value' => 'viettel_post', 'label' => 'Viettel Post'],
            ['value' => 'vnpost', 'label' => 'VN Post'],
            ['value' => 'self', 'label' => 'Tự giao'],
        ];

        // Delivery areas (sample data - should be from database)
        $deliveryAreas = [
            ['value' => 'hanoi', 'label' => 'Hà Nội'],
            ['value' => 'hcm', 'label' => 'TP. Hồ Chí Minh'],
            ['value' => 'danang', 'label' => 'Đà Nẵng'],
            ['value' => 'haiphong', 'label' => 'Hải Phòng'],
        ];

        // Payment methods
        $paymentMethods = [
            ['value' => 'cash', 'label' => 'Tiền mặt'],
            ['value' => 'transfer', 'label' => 'Chuyển khoản'],
            ['value' => 'card', 'label' => 'Thẻ'],
            ['value' => 'e_wallet', 'label' => 'Ví điện tử'],
            ['value' => 'cod', 'label' => 'COD'],
        ];

        // Price lists (sample data)
        $priceLists = [
            ['value' => 'retail', 'label' => 'Giá bán lẻ'],
            ['value' => 'wholesale', 'label' => 'Giá bán sỉ'],
            ['value' => 'vip', 'label' => 'Giá VIP'],
        ];

        // Other income types
        $otherIncomeTypes = [
            ['value' => 'shipping_fee', 'label' => 'Phí vận chuyển'],
            ['value' => 'service_fee', 'label' => 'Phí dịch vụ'],
            ['value' => 'insurance', 'label' => 'Phí bảo hiểm'],
        ];

        // Get users for creator and seller filters (only managers and staff)
        $creators = \App\Models\User::select('id', 'full_name')
            ->whereHas('roles', function($q) {
                $q->whereIn('name', ['shop_manager', 'staff']);
            })
            ->orderBy('full_name')
            ->get();

        $sellers = \App\Models\User::select('id', 'full_name')
            ->whereHas('roles', function($q) {
                $q->whereIn('name', ['shop_manager', 'staff']);
            })
            ->orderBy('full_name')
            ->get();

        return compact(
            'statuses',
            'deliveryStatuses',
            'salesChannels',
            'deliveryPartners',
            'deliveryAreas',
            'paymentMethods',
            'priceLists',
            'otherIncomeTypes',
            'creators',
            'sellers'
        );
    }

    /**
     * Get invoices data for DataTables (AJAX).
     */
    public function getInvoicesAjax()
    {
        try {
            $params = $this->request->all();

            // DataTables parameters
            $draw = $params['draw'] ?? 1;
            $start = $params['start'] ?? 0;
            $length = $params['length'] ?? 10;
            $searchValue = $params['search']['value'] ?? '';

            // Custom search term
            $searchTerm = $params['search_term'] ?? '';

            // Build query with optimized relationships
            $query = Invoice::with([
                'customer:id,name,phone,email,address',
                'branchShop:id,name',
                'creator:id,full_name'
            ]);

            // Apply search
            if (!empty($searchValue) || !empty($searchTerm)) {
                $searchText = !empty($searchTerm) ? $searchTerm : $searchValue;
                $query->search($searchText);
            }

            // Apply filters
            $this->applyFilters($query, $params);
            
            // Get total count before pagination
            $totalRecords = $query->count();
            
            // Apply pagination and ordering
            $invoices = $query->skip($start)
                             ->take($length)
                             ->orderBy('created_at', 'desc')
                             ->get();
            
            // Format data for DataTables
            $data = $invoices->map(function($invoice) {
                return [
                    'id' => $invoice->id, // Add ID field for JavaScript access
                    'checkbox' => '<div class="form-check form-check-sm form-check-custom form-check-solid">
                                    <input class="form-check-input" type="checkbox" value="' . $invoice->id . '" />
                                   </div>',
                    'invoice_number' => '<span class="text-gray-800 fw-bold invoice-number" data-invoice-id="' . $invoice->id . '" data-bs-toggle="collapse" data-bs-target="#invoice-detail-' . $invoice->id . '" style="cursor: pointer;">' . $invoice->invoice_number . '</span>',
                    'customer_display' => $invoice->customer_display ?? 'Khách lẻ',
                    'total_amount' => number_format($invoice->total_amount, 0, ',', '.') . ' ₫',
                    'amount_paid' => number_format($invoice->paid_amount ?? 0, 0, ',', '.') . ' ₫',
                    'payment_status' => $this->getStatusBadge($invoice->status),
                    'payment_method' => $this->getPaymentMethodLabel($invoice->payment_method),
                    'sales_channel' => $this->getSalesChannelLabel($invoice->sales_channel ?? 'offline'),
                    'created_at' => $invoice->created_at->format('d/m/Y H:i'),
                    'seller' => $invoice->creator->full_name ?? 'N/A', // Người bán (người tạo)
                    'creator' => $invoice->creator->full_name ?? 'N/A', // Người tạo
                    'discount' => $invoice->discount_amount ? number_format($invoice->discount_amount, 0, ',', '.') . ' ₫' : '0 ₫',
                    'email' => $invoice->customer->email ?? 'N/A',
                    'phone' => $invoice->customer->phone ?? 'N/A',
                    'address' => $invoice->customer->address ?? 'N/A',
                    'branch_shop' => $invoice->branchShop->name ?? 'N/A',
                    'notes' => $invoice->notes ? substr($invoice->notes, 0, 50) . '...' : 'N/A',
                    'detail_panel' => null // Lazy load when needed
                ];
            });

            // Calculate summary data
            $summaryData = $this->calculateSummaryData($query);

            return response()->json([
                'draw' => intval($draw),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $totalRecords,
                'data' => $data,
                'summary' => $summaryData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'draw' => $params['draw'] ?? 1,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Error loading invoices: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Show form to create new invoice.
     */
    public function create()
    {
        $customers = Customer::orderBy('name')->get();
        $products = Product::where('product_status', 'publish')->orderBy('product_name')->get();
        $branchShops = BranchShop::active()->get();
        $orders = Order::with('customer')->whereDoesntHave('invoice')->orderBy('created_at', 'desc')->take(50)->get();

        return view('admin.invoice.create', compact('customers', 'products', 'branchShops', 'orders'));
    }

    /**
     * Store new invoice.
     */
    public function store()
    {
        try {
            $rules = [
                'customer_id' => 'required|exists:customers,id',
                'invoice_date' => 'required|date',
                'due_date' => 'required|date|after_or_equal:invoice_date',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'nullable|exists:products,id',
                'items.*.product_name' => 'required|string|max:255',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.unit_price' => 'required|numeric|min:0',
            ];

            $validation = $this->validationService->validate($this->request->all(), $rules);
            if (!$validation['success']) {
                return response()->json($validation, 422);
            }

            $result = $this->invoiceService->createInvoice($this->request->all());
            
            if ($result['success']) {
                return response()->json($result);
            } else {
                return response()->json($result, 400);
            }

        } catch (\Exception $e) {
            Log::error('Invoice creation failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tạo hóa đơn'
            ], 500);
        }
    }

    /**
     * Show invoice details.
     */
    public function show($id)
    {
        try {
            $invoice = Invoice::with(['customer', 'branchShop', 'invoiceItems.product', 'creator', 'order'])
                             ->findOrFail($id);
            
            return view('admin.invoice.show', compact('invoice'));

        } catch (\Exception $e) {
            return redirect()->route('invoice.list')->with('error', 'Không tìm thấy hóa đơn');
        }
    }

    /**
     * Show form to edit invoice.
     */
    public function edit($id)
    {
        try {
            $invoice = Invoice::with(['customer', 'branch', 'invoiceItems.product'])
                             ->findOrFail($id);
            
            if ($invoice->status === 'paid' || $invoice->status === 'cancelled') {
                return redirect()->route('admin.invoice.show', $id)->with('error', 'Không thể chỉnh sửa hóa đơn đã thanh toán hoặc đã hủy');
            }
            
            $customers = Customer::orderBy('name')->get();
            $products = Product::where('product_status', 'publish')->orderBy('product_name')->get();
            $branchShops = BranchShop::active()->get();

            return view('admin.invoice.edit', compact('invoice', 'customers', 'products', 'branchShops'));

        } catch (\Exception $e) {
            return redirect()->route('invoice.list')->with('error', 'Không tìm thấy hóa đơn');
        }
    }

    /**
     * Update invoice.
     */
    public function update($id)
    {
        try {
            $invoice = Invoice::findOrFail($id);
            
            if ($invoice->status === 'paid' || $invoice->status === 'cancelled') {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể chỉnh sửa hóa đơn đã thanh toán hoặc đã hủy'
                ], 400);
            }

            $rules = [
                'customer_id' => 'required|exists:customers,id',
                'invoice_date' => 'required|date',
                'due_date' => 'required|date|after_or_equal:invoice_date',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'nullable|exists:products,id',
                'items.*.product_name' => 'required|string|max:255',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.unit_price' => 'required|numeric|min:0',
            ];

            $validation = $this->validationService->validate($this->request->all(), $rules);
            if (!$validation['success']) {
                return response()->json($validation, 422);
            }

            $result = $this->invoiceService->updateInvoice($invoice, $this->request->all());
            
            if ($result['success']) {
                return response()->json($result);
            } else {
                return response()->json($result, 400);
            }

        } catch (\Exception $e) {
            Log::error('Invoice update failed', ['invoice_id' => $id, 'error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi cập nhật hóa đơn'
            ], 500);
        }
    }

    /**
     * Delete invoice.
     */
    public function destroy($id)
    {
        try {
            $invoice = Invoice::findOrFail($id);
            
            if ($invoice->status === 'paid') {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể xóa hóa đơn đã thanh toán'
                ], 400);
            }

            $result = $this->invoiceService->deleteInvoice($invoice);
            
            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Invoice deletion failed', ['invoice_id' => $id, 'error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xóa hóa đơn'
            ], 500);
        }
    }

    /**
     * Record payment for invoice.
     */
    public function recordPayment($id)
    {
        try {
            $invoice = Invoice::findOrFail($id);
            
            $rules = [
                'amount' => 'required|numeric|min:0.01',
                'payment_method' => 'required|in:cash,card,transfer,check,other',
            ];

            $validation = $this->validationService->validate($this->request->all(), $rules);
            if (!$validation['success']) {
                return response()->json($validation, 422);
            }

            $result = $this->invoiceService->recordPayment(
                $invoice, 
                $this->request->input('amount'),
                $this->request->only(['payment_method'])
            );
            
            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Payment recording failed', ['invoice_id' => $id, 'error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi ghi nhận thanh toán'
            ], 500);
        }
    }

    /**
     * Send invoice to customer.
     */
    public function sendInvoice($id)
    {
        try {
            $invoice = Invoice::findOrFail($id);

            $result = $this->invoiceService->sendInvoice($invoice);

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Invoice sending failed', ['invoice_id' => $id, 'error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi gửi hóa đơn'
            ], 500);
        }
    }

    /**
     * Print single invoice.
     */
    public function print($id, Request $request)
    {
        try {
            $invoice = Invoice::with(['customer', 'branchShop', 'invoiceItems.product', 'creator', 'order'])
                             ->findOrFail($id);

            // Get template from request parameter, default to 'standard'
            $template = $request->get('template', 'standard');

            // Map template names to view files
            $templateViews = [
                'standard' => 'admin.invoice.print.standard',
                'retail' => 'admin.invoice.print.retail',
                'sale' => 'admin.invoice.print.sale'
            ];

            // Use standard template if requested template doesn't exist
            $viewName = $templateViews[$template] ?? $templateViews['standard'];

            $data = $this->printService->prepareInvoiceData($invoice);
            $data['template'] = $template;

            return view($viewName, $data);

        } catch (\Exception $e) {
            return redirect()->route('invoice.list')->with('error', 'Không tìm thấy hóa đơn');
        }
    }



    /**
     * Cancel invoice.
     */
    public function cancelInvoice($id)
    {
        try {
            $invoice = Invoice::findOrFail($id);
            
            if ($invoice->status === 'paid') {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể hủy hóa đơn đã thanh toán'
                ], 400);
            }

            $reason = $this->request->input('reason', 'Hủy theo yêu cầu');
            
            $result = $this->invoiceService->cancelInvoice($invoice, $reason);
            
            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Invoice cancellation failed', ['invoice_id' => $id, 'error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi hủy hóa đơn'
            ], 500);
        }
    }

    /**
     * Get invoice statistics.
     */
    public function getStatistics()
    {
        try {
            $filters = $this->request->only(['date_from', 'date_to', 'branch_shop_id']);
            $stats = $this->invoiceService->getStatistics($filters);

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading statistics: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Create invoice from order.
     */
    public function createFromOrder($orderId)
    {
        try {
            $order = Order::with(['customer', 'orderItems.product'])->findOrFail($orderId);
            
            // Check if order already has an invoice
            if ($order->invoice) {
                return response()->json([
                    'success' => false,
                    'message' => 'Đơn hàng này đã có hóa đơn'
                ], 400);
            }

            $result = $this->invoiceService->createInvoiceFromOrder($order, $this->request->all());
            
            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Invoice creation from order failed', ['order_id' => $orderId, 'error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tạo hóa đơn từ đơn hàng'
            ], 500);
        }
    }

    /**
     * Get status badge HTML
     */
    private function getStatusBadge($status)
    {
        $badges = [
            'đang xử lý' => '<span class="badge badge-warning">Đang xử lý</span>',
            'hoàn thành' => '<span class="badge badge-success">Hoàn thành</span>',
            'đã huỷ' => '<span class="badge badge-danger">Đã huỷ</span>',
            'không giao được' => '<span class="badge badge-secondary">Không giao được</span>',
            'processing' => '<span class="badge badge-warning">Processing</span>',
            'completed' => '<span class="badge badge-success">Completed</span>',
            'cancelled' => '<span class="badge badge-danger">Cancelled</span>',
            'pending' => '<span class="badge badge-info">Pending</span>',
        ];

        return $badges[$status] ?? '<span class="badge badge-secondary">' . ucfirst($status) . '</span>';
    }

    /**
     * Get payment status badge HTML
     */
    private function getPaymentStatusBadge($status)
    {
        $badges = [
            'paid' => '<span class="badge badge-light-success">Đã thanh toán</span>',
            'partial' => '<span class="badge badge-light-warning">Thanh toán một phần</span>',
            'unpaid' => '<span class="badge badge-light-danger">Chưa thanh toán</span>',
            'overdue' => '<span class="badge badge-light-dark">Quá hạn</span>',
        ];

        return $badges[$status] ?? '<span class="badge badge-light-secondary">' . ucfirst($status) . '</span>';
    }

    /**
     * Get payment method label
     */
    private function getPaymentMethodLabel($method)
    {
        $methods = [
            'cash' => 'Tiền mặt',
            'transfer' => 'Chuyển khoản',
            'card' => 'Thẻ',
            'e-wallet' => 'Ví điện tử',
        ];

        return $methods[$method] ?? ucfirst($method);
    }

    /**
     * Get invoice type label
     */
    private function getInvoiceTypeLabel($type)
    {
        $types = [
            'sale' => 'Bán hàng',
            'return' => 'Trả hàng',
            'adjustment' => 'Điều chỉnh',
            'other' => 'Khác'
        ];

        return $types[$type] ?? 'Bán hàng';
    }

    /**
     * Get channel label
     */
    /**
     * Get users for filter dropdown
     */
    public function getFilterUsers(Request $request)
    {
        try {
            $currentUser = auth()->user();
            $query = User::with(['branchShops']);

            // Check user role and filter accordingly
            if ($currentUser->hasRole(['SuperAdmin', 'Admin'])) {
                // SuperAdmin and Admin can see all users
                $users = $query->get();
            } else {
                // Manager and Staff can only see users from their branch shops
                $userBranchShopIds = $currentUser->branchShops->pluck('id');
                $users = $query->whereHas('branchShops', function($q) use ($userBranchShopIds) {
                    $q->whereIn('branch_shops.id', $userBranchShopIds);
                })->get();
            }

            $formattedUsers = $users->map(function($user) {
                $branchNames = $user->branchShops->pluck('name')->join(', ');
                $userName = $user->full_name ?? $user->name ?? $user->username;
                return [
                    'id' => $user->id,
                    'text' => $branchNames ? "{$branchNames} - {$userName}" : $userName
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formattedUsers
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tải danh sách người dùng'
            ], 500);
        }
    }

    /**
     * Get sales channel label
     */
    private function getSalesChannelLabel($channel)
    {
        $channels = [
            'offline' => 'Cửa hàng',
            'online' => 'Website',
            'marketplace' => 'Marketplace',
            'social_media' => 'Mạng xã hội',
            'phone_order' => 'Điện thoại',
        ];

        return $channels[$channel] ?? ucfirst($channel);
    }

    private function getChannelLabel($channel)
    {
        $channels = [
            'direct' => 'Trực tiếp',
            'online' => 'Trực tuyến',
            'phone' => 'Điện thoại',
            'pos' => 'POS',
        ];

        return $channels[$channel] ?? ucfirst($channel);
    }

    /**
     * Get action buttons HTML
     */
    private function getActionButtons($invoice)
    {
        $buttons = '<div class="d-flex justify-content-end flex-shrink-0">';

        // Print button
        $buttons .= '<button type="button" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1" onclick="printInvoice(' . $invoice->id . ')" title="In hóa đơn">
                        <i class="ki-duotone ki-printer fs-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                            <span class="path4"></span>
                            <span class="path5"></span>
                        </i>
                     </button>';

        // Send button
        $buttons .= '<button type="button" class="btn btn-icon btn-bg-light btn-active-color-info btn-sm me-1" onclick="sendInvoice(' . $invoice->id . ')" title="Gửi hóa đơn">
                        <i class="ki-duotone ki-send fs-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                     </button>';

        // Payment button (only if not fully paid)
        if ($invoice->payment_status != 'paid') {
            $buttons .= '<button type="button" class="btn btn-icon btn-bg-light btn-active-color-success btn-sm me-1" onclick="recordPayment(' . $invoice->id . ')" title="Ghi nhận thanh toán">
                            <i class="ki-duotone ki-dollar fs-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                                <span class="path3"></span>
                            </i>
                         </button>';
        }

        // Delete button (only if not paid)
        if ($invoice->payment_status != 'paid') {
            $buttons .= '<button type="button" class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm" onclick="deleteInvoice(' . $invoice->id . ')" title="Xóa">
                            <i class="ki-duotone ki-trash fs-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                                <span class="path3"></span>
                                <span class="path4"></span>
                                <span class="path5"></span>
                            </i>
                         </button>';
        }

        $buttons .= '</div>';

        return $buttons;
    }

    /**
     * Apply all filters to query
     */
    private function applyFilters($query, $params)
    {
        // Barcode filter (Code parameter)
        if (!empty($params['Code'])) {
            $barcode = $params['Code'];
            $query->whereHas('invoiceItems.product', function($q) use ($barcode) {
                $q->where('barcode', $barcode);
            });
        }

        // Time filter
        if (!empty($params['time_filter'])) {
            $this->applyTimeFilter($query, $params['time_filter']);
        }

        // Status filters (checkboxes)
        if (!empty($params['status_filters']) && is_array($params['status_filters'])) {
            $query->whereIn('status', $params['status_filters']);
        }

        // Creator filter
        if (!empty($params['creator_id'])) {
            $query->where('created_by', $params['creator_id']);
        }

        // Seller filter (using created_by as seller)
        if (!empty($params['seller_id'])) {
            $query->where('created_by', $params['seller_id']);
        }

        // Delivery status filters
        if (!empty($params['delivery_status']) && is_array($params['delivery_status'])) {
            $query->whereIn('delivery_status', $params['delivery_status']);
        }

        // Sales channel filter
        if (!empty($params['sales_channel'])) {
            $query->whereIn('sales_channel', $params['sales_channel']);
        }

        // Delivery partner filter
        if (!empty($params['delivery_partner'])) {
            $query->where('delivery_partner', $params['delivery_partner']);
        }

        // Delivery area filter
        if (!empty($params['delivery_area'])) {
            $query->where('delivery_area', $params['delivery_area']);
        }

        // Payment method filter
        if (!empty($params['payment_method'])) {
            $query->where('payment_method', $params['payment_method']);
        }

        // Price list filter
        if (!empty($params['price_list'])) {
            $query->where('price_list', $params['price_list']);
        }

        // Other income type filter
        if (!empty($params['other_income_type'])) {
            $query->where('other_income_type', $params['other_income_type']);
        }

        // Legacy filters for backward compatibility
        if (!empty($params['status'])) {
            $query->where('status', $params['status']);
        }

        if (!empty($params['payment_status'])) {
            $query->where('payment_status', $params['payment_status']);
        }

        if (!empty($params['branch_shop_id'])) {
            $query->where('branch_shop_id', $params['branch_shop_id']);
        }

        if (!empty($params['date_from'])) {
            $query->whereDate('invoice_date', '>=', $params['date_from']);
        }

        if (!empty($params['date_to'])) {
            $query->whereDate('invoice_date', '<=', $params['date_to']);
        }
    }

    /**
     * Apply time filter to query
     */
    private function applyTimeFilter($query, $timeFilter)
    {
        $now = now();

        // Debug log
        \Log::info('Time filter applied', [
            'filter' => $timeFilter,
            'current_time' => $now->toDateTimeString()
        ]);

        switch ($timeFilter) {
            // Theo ngày
            case 'today':
                $today = $now->toDateString();
                $query->whereDate('created_at', $today);
                Log::info('Today filter', ['date' => $today]);
                break;
            case 'yesterday':
                $yesterday = $now->copy()->subDay()->toDateString();
                $query->whereDate('created_at', $yesterday);
                Log::info('Yesterday filter', ['date' => $yesterday]);
                break;

            // Theo tuần
            case 'this_week':
                $startOfWeek = $now->copy()->startOfWeek();
                $endOfWeek = $now->copy()->endOfWeek();
                $query->whereBetween('created_at', [$startOfWeek, $endOfWeek]);
                break;
            case 'last_week':
                $startOfLastWeek = $now->copy()->subWeek()->startOfWeek();
                $endOfLastWeek = $now->copy()->subWeek()->endOfWeek();
                $query->whereBetween('created_at', [$startOfLastWeek, $endOfLastWeek]);
                break;
            case '7_days':
                $query->where('created_at', '>=', $now->copy()->subDays(7));
                break;

            // Theo tháng
            case 'this_month':
                $startOfMonth = $now->copy()->startOfMonth();
                $endOfMonth = $now->copy()->endOfMonth();
                $query->whereBetween('created_at', [$startOfMonth, $endOfMonth]);
                Log::info('This month filter', [
                    'start' => $startOfMonth->toDateTimeString(),
                    'end' => $endOfMonth->toDateTimeString()
                ]);
                break;
            case 'last_month':
                $startOfLastMonth = $now->copy()->subMonth()->startOfMonth();
                $endOfLastMonth = $now->copy()->subMonth()->endOfMonth();
                $query->whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth]);
                Log::info('Last month filter', [
                    'start' => $startOfLastMonth->toDateTimeString(),
                    'end' => $endOfLastMonth->toDateTimeString()
                ]);
                break;
            case '30_days':
                $thirtyDaysAgo = $now->copy()->subDays(30);
                $query->where('created_at', '>=', $thirtyDaysAgo);
                Log::info('30 days filter', ['from' => $thirtyDaysAgo->toDateTimeString()]);
                break;

            // Theo quý
            case 'this_quarter':
                $startOfQuarter = $now->copy()->startOfQuarter();
                $endOfQuarter = $now->copy()->endOfQuarter();
                $query->whereBetween('created_at', [$startOfQuarter, $endOfQuarter]);
                break;
            case 'last_quarter':
                $startOfLastQuarter = $now->copy()->subQuarter()->startOfQuarter();
                $endOfLastQuarter = $now->copy()->subQuarter()->endOfQuarter();
                $query->whereBetween('created_at', [$startOfLastQuarter, $endOfLastQuarter]);
                break;

            // Theo năm
            case 'this_year':
                $startOfYear = $now->copy()->startOfYear();
                $endOfYear = $now->copy()->endOfYear();
                $query->whereBetween('created_at', [$startOfYear, $endOfYear]);
                break;
            case 'last_year':
                $startOfLastYear = $now->copy()->subYear()->startOfYear();
                $endOfLastYear = $now->copy()->subYear()->endOfYear();
                $query->whereBetween('created_at', [$startOfLastYear, $endOfLastYear]);
                break;
        }
    }

    /**
     * Apply status filters to query
     */
    private function applyStatusFilters($query, $statusFilters)
    {
        if (in_array('paid', $statusFilters)) {
            $query->orWhere('payment_status', 'paid');
        }
        if (in_array('partial', $statusFilters)) {
            $query->orWhere('payment_status', 'partial');
        }
        if (in_array('unpaid', $statusFilters)) {
            $query->orWhere('payment_status', 'unpaid');
        }
        if (in_array('pending', $statusFilters)) {
            $query->orWhere('status', 'pending');
        }
        if (in_array('processing', $statusFilters)) {
            $query->orWhere('status', 'processing');
        }
        if (in_array('shipped', $statusFilters)) {
            $query->orWhere('status', 'shipped');
        }
        if (in_array('delivered', $statusFilters)) {
            $query->orWhere('status', 'delivered');
        }
        if (in_array('cancelled', $statusFilters)) {
            $query->orWhere('status', 'cancelled');
        }
    }

    /**
     * Calculate summary data for dashboard cards
     */
    private function calculateSummaryData($query)
    {
        // Clone query to avoid affecting main query
        $summaryQuery = clone $query;

        $invoices = $summaryQuery->get();

        $totalAmount = $invoices->sum('total_amount');
        $discountAmount = $invoices->sum('discount_amount');
        $totalPayment = $invoices->sum('paid_amount');
        $customerDebt = $totalAmount - $totalPayment;

        return [
            'total_amount' => $totalAmount,
            'discount_amount' => $discountAmount,
            'total_payment' => $totalPayment,
            'customer_debt' => $customerDebt
        ];
    }

    /**
     * Sync invoice status based on payment status and due date
     */
    private function syncInvoiceStatus($invoice)
    {
        $now = now();
        $dueDate = \Carbon\Carbon::parse($invoice->due_date);

        // Auto-update status based on payment_status and due_date
        switch ($invoice->payment_status) {
            case 'paid':
            case 'overpaid':
                $invoice->status = 'paid';
                if (!$invoice->paid_at) {
                    $invoice->paid_at = $now;
                }
                break;

            case 'unpaid':
                if ($invoice->status === 'draft') {
                    // Keep draft status
                } elseif ($dueDate->isPast()) {
                    $invoice->status = 'overdue';
                } else {
                    $invoice->status = 'sent';
                }
                break;

            case 'partial':
                if ($dueDate->isPast()) {
                    $invoice->status = 'overdue';
                } else {
                    $invoice->status = 'sent';
                }
                break;
        }

        return $invoice;
    }

    /**
     * Get detail panel for invoice row expansion
     */
    public function getDetailPanel($id)
    {
        try {
            Log::info('Loading invoice detail panel', ['invoice_id' => $id]);

            $invoice = Invoice::with(['customer', 'branchShop', 'invoiceItems.product', 'creator'])->findOrFail($id);

            Log::info('Invoice found', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'customer_id' => $invoice->customer_id
            ]);

            $html = view('admin.invoice.partials.detail_panel', compact('invoice'))->render();

            Log::info('Detail panel rendered successfully', ['invoice_id' => $id]);

            return response()->json([
                'success' => true,
                'html' => $html
            ]);

        } catch (\Exception $e) {
            Log::error('Error loading invoice detail panel', [
                'invoice_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'html' => '<div class="alert alert-danger">Không thể tải thông tin chi tiết: ' . $e->getMessage() . '</div>'
            ]);
        }
    }

    /**
     * Render detail panel for invoice row expansion
     */
    private function renderDetailPanel($invoice)
    {
        try {
            return view('admin.invoice.partials.detail_panel', compact('invoice'))->render();
        } catch (\Exception $e) {
            Log::error('Error rendering invoice detail panel: ' . $e->getMessage());
            return '<div class="alert alert-danger">Không thể tải thông tin chi tiết</div>';
        }
    }
}
