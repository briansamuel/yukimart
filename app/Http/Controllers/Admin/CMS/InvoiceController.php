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
use App\Traits\FilterableTrait;
use App\Traits\HandlesApiErrors;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class InvoiceController extends Controller
{
    use FilterableTrait, HandlesApiErrors;

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

        // Handle invoice code search via Code parameter
        $invoiceCodeSearch = null;
        $searchedInvoice = null;
        if ($request->has('code') && !empty($request->get('code'))) {
            $invoiceCode = $request->get('code');
            $invoiceCodeSearch = $invoiceCode;

            // Find invoice by invoice_number
            $searchedInvoice = Invoice::where('invoice_number', $invoiceCode)->first();

            if ($searchedInvoice) {
                Log::info('Invoice code search in invoices', [
                    'invoice_code' => $invoiceCode,
                    'invoice_id' => $searchedInvoice->id,
                    'customer_name' => $searchedInvoice->customer_name
                ]);
            } else {
                Log::warning('Invoice code not found in invoices search', ['invoice_code' => $invoiceCode]);
            }
        }

        return view('admin.invoice.index', compact('branchShops', 'invoiceCodeSearch', 'searchedInvoice') + $filterData);
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

            // Debug log to see what parameters are received
            Log::info('Invoice AJAX request parameters:', $params);

            // DataTables parameters
            $draw = $params['draw'] ?? 1;
            $page = $params['page'] ?? 1;
            $start = ($page - 1) * ($params['per_page'] ?? 10);
            $length = $params['per_page'] ?? 10;
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

            // Apply filters using FilterableTrait
            $filterConfig = [
                'searchColumns' => ['invoice_number', 'customer_name', 'customer_phone'],
                'statusColumn' => 'status',
                'userColumns' => [
                    'creator_id' => 'created_by',
                    'seller_id' => 'created_by'  // Invoices don't have seller_id, use created_by for both
                ],
                'dateRangeColumns' => ['created_at']
            ];

            $this->applyCommonFilters($query, $this->request, $filterConfig);

            // Apply additional custom filters
            $this->applyCustomFilters($query, $params);
            
            // Get total count before pagination
            $totalRecords = $query->count();
            
            // Apply pagination and ordering
            $invoices = $query->skip($start)
                             ->take($length)
                             ->orderBy('created_at', 'desc')
                             ->get();
            
            // Format data for simple table
            $data = $invoices->map(function($invoice) {
                return [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'customer_display' => $invoice->customer_display ?? 'Khách lẻ',
                    'total_amount' => $invoice->total_amount ?? 0,
                    'amount_paid' => $invoice->paid_amount ?? 0,
                    'status' => $invoice->status ?? 'processing',
                    'payment_method' => $invoice->payment_method ?? 'cash',
                    'sales_channel' => $invoice->sales_channel ?? 'offline',
                    'created_at' => $invoice->created_at ? $invoice->created_at->toISOString() : null,
                    'seller' => $invoice->creator->full_name ?? '',
                    'creator' => $invoice->creator->full_name ?? '',
                    'discount' => $invoice->discount_amount ?? 0,
                    'email' => $invoice->customer->email ?? '',
                    'phone' => $invoice->customer->phone ?? '',
                    'address' => $invoice->customer->address ?? '',
                    'branch_shop' => $invoice->branchShop->name ?? '',
                    'notes' => $invoice->notes ?? ''
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
            return $this->handleDataTablesException($e, $params);
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
            $invoice = Invoice::with(['customer', 'branchShop', 'invoiceItems.product', 'creator', 'order', 'payments'])
                             ->findOrFail($id);

            return view('admin.invoice.show', compact('invoice'));

        } catch (\Exception) {
            return redirect()->route('invoice.list')->with('error', 'Không tìm thấy hóa đơn');
        }
    }

    /**
     * Get payment history for invoice via AJAX.
     */
    public function getPaymentHistory($id)
    {
        try {
            $invoice = Invoice::findOrFail($id);

            $payments = $invoice->payments()
                ->with(['creator', 'bankAccount'])
                ->orderBy('payment_date', 'desc')
                ->get();

            $data = [];
            foreach ($payments as $payment) {
                $data[] = [
                    'payment_number' => $payment->payment_number,
                    'payment_date' => $payment->payment_date->format('d/m/Y H:i'),
                    'creator_name' => $payment->creator->name ?? 'N/A',
                    'amount' => $payment->actual_amount,
                    'formatted_amount' => number_format($payment->actual_amount, 0, ',', '.'),
                    'payment_method' => $payment->payment_method_display,
                    'status' => $payment->status,
                    'status_badge' => $payment->status_badge,
                    'description' => $payment->description ?? '',
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể tải lịch sử thanh toán'
            ], 500);
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

        } catch (\Exception) {
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

        } catch (\Exception) {
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
           
            'processing' => '<span class="badge badge-warning">Đang xử lý</span>',
            'completed' => '<span class="badge badge-success">Hoàn thành</span>',
            'cancelled' => '<span class="badge badge-danger">Đã huỷ</span>',
            'undeliverable' => '<span class="badge badge-info">Không giao được</span>',
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
    public function getFilterUsers()
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
        } catch (\Exception) {
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
     * Apply custom filters specific to invoices (not covered by FilterableTrait)
     */
    private function applyCustomFilters($query, $params)
    {
        // Invoice code filter (Code or code parameter)
        $invoiceCode = $params['Code'] ?? $params['code'] ?? null;
        if (!empty($invoiceCode)) {
            $query->where('invoice_number', $invoiceCode);
            Log::info('Invoice AJAX: Filtering by invoice code', ['code' => $invoiceCode]);
        }

        // Delivery status filters
        if (!empty($params['delivery_status']) && is_array($params['delivery_status'])) {
            $query->whereIn('delivery_status', $params['delivery_status']);
        }

        // Sales channel filter (multiple values)
        if (!empty($params['sales_channel'])) {
            if (is_array($params['sales_channel'])) {
                $query->whereIn('sales_channel', $params['sales_channel']);
            } else {
                // Handle single value or comma-separated string
                $channels = is_string($params['sales_channel']) ? explode(',', $params['sales_channel']) : [$params['sales_channel']];
                $query->whereIn('sales_channel', array_filter($channels));
            }
        }

        // Delivery partner filter
        if (!empty($params['delivery_partner'])) {
            $query->where('delivery_partner', $params['delivery_partner']);
        }

        // Delivery area filter
        if (!empty($params['delivery_area'])) {
            $query->where('delivery_area', $params['delivery_area']);
        }

        // Payment method filter - join with payments table
        if (!empty($params['payment_method'])) {
            $query->whereHas('payments', function($q) use ($params) {
                $q->where('payment_method', $params['payment_method'])
                  ->where('status', 'completed');
            });
        }

        // Creator filter (multiple values)
        if (!empty($params['creator_id'])) {
            if (is_array($params['creator_id'])) {
                $query->whereIn('created_by', $params['creator_id']);
            } else {
                // Handle single value or comma-separated string
                $creatorIds = is_string($params['creator_id']) ? explode(',', $params['creator_id']) : [$params['creator_id']];
                $query->whereIn('created_by', array_filter($creatorIds));
            }
        }

        // Seller filter (for invoices, this is the same as creator since invoices don't have separate seller)
        if (!empty($params['seller_id'])) {
            if (is_array($params['seller_id'])) {
                $query->whereIn('created_by', $params['seller_id']);
            } else {
                // Handle single value or comma-separated string
                $sellerIds = is_string($params['seller_id']) ? explode(',', $params['seller_id']) : [$params['seller_id']];
                $query->whereIn('created_by', array_filter($sellerIds));
            }
        }

        // Price list filter
        if (!empty($params['price_list'])) {
            $query->where('price_list', $params['price_list']);
        }

        // Other income type filter
        if (!empty($params['other_income_type'])) {
            $query->where('other_income_type', $params['other_income_type']);
        }

        // Payment status filter
        if (!empty($params['payment_status'])) {
            $query->where('payment_status', $params['payment_status']);
        }

        // Branch shop filter
        if (!empty($params['branch_shop_id'])) {
            $query->where('branch_shop_id', $params['branch_shop_id']);
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

            $invoice = Invoice::with([
                'customer',
                'branchShop',
                'invoiceItems.product',
                'creator',
                'payments' => function($query) {
                    $query->where('payment_type', 'receipt')
                          ->orderBy('payment_date', 'desc');
                }
            ])->findOrFail($id);

            Log::info('Invoice found', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'customer_id' => $invoice->customer_id,
                'payments_count' => $invoice->payments->count()
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
     * Bulk cancel invoices
     */
    public function bulkCancel(Request $request)
    {
        try {
            // Validate request
            $request->validate([
                'invoice_ids' => 'required|array|min:1',
                'invoice_ids.*' => 'required|integer|exists:invoices,id'
            ]);

            $invoiceIds = $request->input('invoice_ids');
            $cancelledCount = 0;
            $errors = [];

            Log::info('Bulk cancel invoices request', [
                'invoice_ids' => $invoiceIds,
                'user_id' => auth()->id()
            ]);

            // Process each invoice
            foreach ($invoiceIds as $invoiceId) {
                try {
                    $invoice = Invoice::findOrFail($invoiceId);

                    // Check if invoice can be cancelled
                    if ($invoice->status === 'cancelled') {
                        $errors[] = "Hóa đơn {$invoice->invoice_number} đã được huỷ trước đó.";
                        continue;
                    }

                    if ($invoice->status === 'completed' && $invoice->amount_paid > 0) {
                        $errors[] = "Hóa đơn {$invoice->invoice_number} đã thanh toán, không thể huỷ.";
                        continue;
                    }

                    // Update invoice status to cancelled using soft delete
                    $invoice->update([
                        'status' => 'cancelled',
                        'cancelled_at' => now(),
                        'cancelled_by' => auth()->id(),
                        'deleted_at' => now(),
                        'updated_by' => auth()->id(),
                        'notes' => ($invoice->notes ? $invoice->notes . "\n" : '') .
                                  'Hóa đơn được huỷ hàng loạt vào ' . now()->format('d/m/Y H:i:s') .
                                  ' bởi ' . auth()->user()->full_name
                    ]);

                    $cancelledCount++;

                    Log::info('Invoice cancelled successfully', [
                        'invoice_id' => $invoiceId,
                        'invoice_number' => $invoice->invoice_number,
                        'user_id' => auth()->id()
                    ]);

                } catch (\Exception $e) {
                    Log::error('Error cancelling invoice', [
                        'invoice_id' => $invoiceId,
                        'error' => $e->getMessage(),
                        'user_id' => auth()->id()
                    ]);
                    $errors[] = "Lỗi khi huỷ hóa đơn ID {$invoiceId}: " . $e->getMessage();
                }
            }

            // Prepare response
            $response = [
                'success' => $cancelledCount > 0,
                'cancelled_count' => $cancelledCount,
                'total_requested' => count($invoiceIds),
                'errors' => $errors
            ];

            if ($cancelledCount > 0) {
                $response['message'] = "Đã huỷ thành công {$cancelledCount} hóa đơn.";
                if (!empty($errors)) {
                    $response['message'] .= " Có " . count($errors) . " lỗi xảy ra.";
                }
            } else {
                $response['message'] = "Không thể huỷ hóa đơn nào. " . implode(' ', $errors);
            }

            Log::info('Bulk cancel invoices completed', $response);

            return response()->json($response);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Bulk cancel validation failed', [
                'errors' => $e->errors(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ.',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Bulk cancel invoices failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi huỷ hóa đơn: ' . $e->getMessage()
            ], 500);
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

    /**
     * Export invoices to Excel
     */
    public function exportExcel(Request $request)
    {
        try {
            // Get filtered invoices
            $invoices = $this->getFilteredInvoices($request);

            // Create Excel export
            $filename = 'invoices_' . date('Y-m-d_H-i-s') . '.xlsx';

            // Headers for Excel
            $headers = [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Cache-Control' => 'max-age=0',
            ];

            // Create CSV content (simple implementation)
            $csvContent = $this->generateCsvContent($invoices);

            return response($csvContent, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="invoices_' . date('Y-m-d_H-i-s') . '.csv"',
                'Cache-Control' => 'max-age=0',
            ]);

        } catch (\Exception $e) {
            Log::error('Error exporting invoices to Excel: ' . $e->getMessage());
            return response()->json(['error' => 'Lỗi khi xuất file Excel'], 500);
        }
    }

    /**
     * Export invoices to PDF
     */
    public function exportPdf(Request $request)
    {
        try {
            // Get filtered invoices
            $invoices = $this->getFilteredInvoices($request);

            // Create PDF content
            $html = $this->generatePdfContent($invoices);

            // For now, return HTML (can be enhanced with PDF library later)
            return response($html, 200, [
                'Content-Type' => 'text/html',
                'Content-Disposition' => 'attachment; filename="invoices_' . date('Y-m-d_H-i-s') . '.html"',
            ]);

        } catch (\Exception $e) {
            Log::error('Error exporting invoices to PDF: ' . $e->getMessage());
            return response()->json(['error' => 'Lỗi khi xuất file PDF'], 500);
        }
    }

    /**
     * Get filtered invoices for export
     */
    private function getFilteredInvoices(Request $request)
    {
        $query = Invoice::with(['customer', 'creator', 'seller', 'branchShop']);

        // Apply filters (reuse existing filter logic)
        $filters = $this->getFiltersFromRequest($request);
        $query = $this->applyFilters($query, $filters);

        return $query->get();
    }

    /**
     * Generate CSV content
     */
    private function generateCsvContent($invoices)
    {
        $csv = "Mã hóa đơn,Khách hàng,Tổng tiền,Đã thanh toán,Trạng thái,Phương thức TT,Kênh bán,Ngày tạo,Người bán,Người tạo,Chi nhánh\n";

        foreach ($invoices as $invoice) {
            $csv .= sprintf(
                '"%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s"' . "\n",
                $invoice->invoice_code,
                $invoice->customer ? $invoice->customer->full_name : 'Khách lẻ',
                number_format($invoice->final_amount, 0, ',', '.') . ' ₫',
                number_format($invoice->paid_amount, 0, ',', '.') . ' ₫',
                $this->getStatusText($invoice->status),
                $invoice->payment_method ?? 'N/A',
                $invoice->channel ?? 'N/A',
                $invoice->created_at ? $invoice->created_at->format('d/m/Y H:i') : '',
                $invoice->seller ? $invoice->seller->full_name : 'N/A',
                $invoice->creator ? $invoice->creator->full_name : 'N/A',
                $invoice->branchShop ? $invoice->branchShop->name : 'N/A'
            );
        }

        return $csv;
    }

    /**
     * Generate PDF content
     */
    private function generatePdfContent($invoices)
    {
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Danh sách hóa đơn</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .header { text-align: center; margin-bottom: 20px; }
        .total { margin-top: 20px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>DANH SÁCH HÓA ĐƠN</h1>
        <p>Ngày xuất: ' . date('d/m/Y H:i') . '</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Mã hóa đơn</th>
                <th>Khách hàng</th>
                <th>Tổng tiền</th>
                <th>Đã thanh toán</th>
                <th>Trạng thái</th>
                <th>Phương thức TT</th>
                <th>Kênh bán</th>
                <th>Ngày tạo</th>
                <th>Người bán</th>
                <th>Người tạo</th>
                <th>Chi nhánh</th>
            </tr>
        </thead>
        <tbody>';

        $totalAmount = 0;
        $totalPaid = 0;

        foreach ($invoices as $invoice) {
            $totalAmount += $invoice->final_amount;
            $totalPaid += $invoice->paid_amount;

            $html .= '<tr>
                <td>' . $invoice->invoice_code . '</td>
                <td>' . ($invoice->customer ? $invoice->customer->full_name : 'Khách lẻ') . '</td>
                <td>' . number_format($invoice->final_amount, 0, ',', '.') . ' ₫</td>
                <td>' . number_format($invoice->paid_amount, 0, ',', '.') . ' ₫</td>
                <td>' . $this->getStatusText($invoice->status) . '</td>
                <td>' . ($invoice->payment_method ?? 'N/A') . '</td>
                <td>' . ($invoice->channel ?? 'N/A') . '</td>
                <td>' . ($invoice->created_at ? $invoice->created_at->format('d/m/Y H:i') : '') . '</td>
                <td>' . ($invoice->seller ? $invoice->seller->full_name : 'N/A') . '</td>
                <td>' . ($invoice->creator ? $invoice->creator->full_name : 'N/A') . '</td>
                <td>' . ($invoice->branchShop ? $invoice->branchShop->name : 'N/A') . '</td>
            </tr>';
        }

        $html .= '</tbody>
    </table>

    <div class="total">
        <p>Tổng số hóa đơn: ' . count($invoices) . '</p>
        <p>Tổng tiền: ' . number_format($totalAmount, 0, ',', '.') . ' ₫</p>
        <p>Tổng đã thanh toán: ' . number_format($totalPaid, 0, ',', '.') . ' ₫</p>
    </div>
</body>
</html>';

        return $html;
    }

    /**
     * Get status text
     */
    private function getStatusText($status)
    {
        $statusMap = [
            'processing' => 'Đang xử lý',
            'completed' => 'Hoàn thành',
            'cancelled' => 'Đã hủy',
            'undeliverable' => 'Không giao được'
        ];

        return $statusMap[$status] ?? $status;
    }

    /**
     * Get invoices for return order selection (API)
     */
    public function getInvoicesForReturn(Request $request)
    {
        try {
            $page = $request->get('page', 1);
            $perPage = $request->get('per_page', 10);
            $search = $request->get('search', '');
            $timeFilter = $request->get('time_filter', 'this_month');
            $customerFilter = $request->get('customer_filter', '');
            $status = $request->get('status', ['paid', 'completed']);

            $query = Invoice::with(['customer:id,name,phone', 'creator:id,full_name'])
                ->whereIn('status', $status);

            // Apply search filter
            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('invoice_number', 'like', "%{$search}%")
                      ->orWhereHas('customer', function($customerQuery) use ($search) {
                          $customerQuery->where('name', 'like', "%{$search}%")
                                      ->orWhere('phone', 'like', "%{$search}%");
                      });
                });
            }

            // Apply customer filter
            if (!empty($customerFilter)) {
                $query->whereHas('customer', function($customerQuery) use ($customerFilter) {
                    $customerQuery->where('name', 'like', "%{$customerFilter}%");
                });
            }

            // Apply time filter
            switch ($timeFilter) {
                case 'this_month':
                    $query->whereMonth('created_at', now()->month)
                          ->whereYear('created_at', now()->year);
                    break;
                case 'last_month':
                    $query->whereMonth('created_at', now()->subMonth()->month)
                          ->whereYear('created_at', now()->subMonth()->year);
                    break;
                case 'this_year':
                    $query->whereYear('created_at', now()->year);
                    break;
            }

            // Get paginated results
            $invoices = $query->orderBy('created_at', 'desc')
                            ->paginate($perPage, ['*'], 'page', $page);

            // Format data for response
            $data = $invoices->map(function($invoice) {
                return [
                    'id' => $invoice->id,
                    'invoice_code' => $invoice->invoice_number,
                    'created_at' => $invoice->created_at,
                    'total_amount' => $invoice->total_amount,
                    'customer_name' => $invoice->customer ? $invoice->customer->name : null,
                    'customer_phone' => $invoice->customer ? $invoice->customer->phone : null,
                    'creator_name' => $invoice->creator ? $invoice->creator->full_name : null,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $data,
                'pagination' => [
                    'current_page' => $invoices->currentPage(),
                    'last_page' => $invoices->lastPage(),
                    'per_page' => $invoices->perPage(),
                    'total' => $invoices->total(),
                    'from' => $invoices->firstItem(),
                    'to' => $invoices->lastItem(),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Get invoices for return failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tải danh sách hóa đơn'
            ], 500);
        }
    }

    /**
     * Get invoice details for return order (API)
     */
    public function getInvoiceDetails($id)
    {
        try {
            \Log::info("Getting invoice details for ID: " . $id);

            $invoice = Invoice::with(['customer', 'creator', 'branchShop'])
                             ->find($id);

            if (!$invoice) {
                \Log::error("Invoice not found with ID: " . $id);
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy hóa đơn'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'customer_name' => $invoice->customer ? $invoice->customer->name : 'Khách lẻ',
                    'customer_phone' => $invoice->customer ? $invoice->customer->phone : '',
                    'seller_name' => $invoice->creator ? $invoice->creator->name : 'N/A',
                    'creator_name' => $invoice->creator ? $invoice->creator->name : 'N/A',
                    'total_amount' => $invoice->total_amount,
                    'status' => $invoice->status
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error("Error getting invoice details: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tải thông tin hóa đơn'
            ], 500);
        }
    }

    /**
     * Get invoice items for return order (API)
     */
    public function getInvoiceItems($id)
    {
        try {
            \Log::info("Getting invoice items for ID: " . $id);

            // First check if invoice exists
            $invoice = Invoice::find($id);
            \Log::info("Invoice found: " . ($invoice ? 'Yes' : 'No'));

            if (!$invoice) {
                \Log::error("Invoice not found with ID: " . $id);
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy hóa đơn'
                ], 404);
            }

            // Load invoice with items
            $invoice = Invoice::with(['invoiceItems.product:id,product_name,product_thumbnail,sku'])
                             ->find($id);

            \Log::info("Invoice items count: " . $invoice->invoiceItems->count());

            if ($invoice->invoiceItems->count() == 0) {
                \Log::warning("Invoice has no items", ['invoice_id' => $id]);
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'message' => 'Hóa đơn không có sản phẩm nào'
                ]);
            }

            $items = $invoice->invoiceItems->map(function($item) {
                return [
                    'product_id' => $item->product_id,
                    'product_sku' => $item->product ? $item->product->sku : null,
                    'product_name' => $item->product_name,
                    'product_image' => $item->product && $item->product->product_thumbnail
                                     ? $item->product->product_thumbnail
                                     : null,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'stock_quantity' => $item->product ? $item->product->stock_quantity : 0,
                ];
            });

            \Log::info("Successfully mapped items", ['items_count' => $items->count()]);

            return response()->json([
                'success' => true,
                'data' => $items
            ]);

        } catch (\Exception $e) {
            \Log::error('Get invoice items failed', ['error' => $e->getMessage(), 'invoice_id' => $id, 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy hóa đơn hoặc có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }
}
