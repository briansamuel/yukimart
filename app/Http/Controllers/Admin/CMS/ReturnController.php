<?php

namespace App\Http\Controllers\Admin\CMS;

use App\Http\Controllers\Controller;
use App\Models\ReturnOrder;
use App\Models\Customer;
use App\Models\User;
use App\Models\Payment;
use App\Models\BranchShop;
use App\Traits\FilterableTrait;
use App\Exports\ReturnsExport;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class ReturnController extends Controller
{
    use FilterableTrait;

    /**
     * Display a listing of returns.
     */
    public function index(Request $request)
    {
        try {
            // Get filter data for the view
            $customers = Customer::select('id', 'name', 'phone')
                ->orderBy('name')
                ->get();

            // Payment methods
            $paymentMethods = [
                ['value' => 'cash', 'label' => 'Tiền mặt'],
                ['value' => 'bank_transfer', 'label' => 'Chuyển khoản'],
                ['value' => 'credit_card', 'label' => 'Thẻ tín dụng'],
                ['value' => 'e_wallet', 'label' => 'Ví điện tử'],
                ['value' => 'cod', 'label' => 'Thu hộ COD'],
                ['value' => 'other', 'label' => 'Khác']
            ];

            // Return statuses
            $returnStatuses = [
                ['value' => 'draft', 'label' => 'Nháp'],
                ['value' => 'processing', 'label' => 'Đang xử lý'],
                ['value' => 'completed', 'label' => 'Hoàn thành'],
                ['value' => 'cancelled', 'label' => 'Đã hủy']
            ];

            // Delivery statuses
            $deliveryStatuses = [
                ['value' => 'pending', 'label' => 'Chờ xử lý'],
                ['value' => 'picked_up', 'label' => 'Đã lấy hàng'],
                ['value' => 'in_transit', 'label' => 'Đang vận chuyển'],
                ['value' => 'delivered', 'label' => 'Đã giao hàng'],
                ['value' => 'returned', 'label' => 'Chuyển hoàn'],
                ['value' => 'cancelled', 'label' => 'Đã hủy']
            ];

            // Sale channels
            $saleChannels = [
                ['value' => 'direct', 'label' => 'Direct'],
                ['value' => 'marketplace', 'label' => 'Marketplace'],
                ['value' => 'store', 'label' => 'Bán tại cửa hàng'],
                ['value' => 'online', 'label' => 'Bán online'],
                ['value' => 'other', 'label' => 'Khác'],
                ['value' => 'phone', 'label' => 'Điện thoại'],
                ['value' => 'social', 'label' => 'Mạng xã hội']
            ];

            // Get users for creator and seller filters (only managers and staff)
            $creators = User::select('id', 'full_name')
                ->whereHas('roles', function($q) {
                    $q->whereIn('name', ['shop_manager', 'staff']);
                })
                ->orderBy('full_name')
                ->get();

            $sellers = User::select('id', 'full_name')
                ->whereHas('roles', function($q) {
                    $q->whereIn('name', ['shop_manager', 'staff']);
                })
                ->orderBy('full_name')
                ->get();

            // Get branch shops
            $branchShops = BranchShop::select('id', 'name')
                ->where('status', 'active')
                ->orderBy('name')
                ->get();

            return view('admin.returns.index', compact(
                'customers',
                'paymentMethods',
                'returnStatuses',
                'deliveryStatuses',
                'saleChannels',
                'creators',
                'sellers',
                'branchShops'
            ));

        } catch (\Exception $e) {
            Log::error('Return index error: ' . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra khi tải trang: ' . $e->getMessage());
        }
    }

    /**
     * Get returns data via AJAX for DataTable.
     */
    public function getReturnsAjax(Request $request): JsonResponse
    {
        try {
            Log::info('Return AJAX request received', $request->all());

            // Get search parameters
            $searchValue = $request->get('search')['value'] ?? '';
            $searchTerm = $request->get('searchTerm', '');

            // Get pagination parameters
            $start = (int) $request->get('start', 0);
            $length = (int) $request->get('length', 10);
            $page = ($start / $length) + 1;

            // Build base query
            $query = ReturnOrder::with(['customer', 'creator', 'branchShop']);

            // Apply filters using FilterableTrait (but exclude search to avoid conflict)
            $filterConfig = [
                'searchColumns' => [], // Empty to avoid conflict with our custom search scope
                'statusColumn' => 'status',
                'userColumns' => [
                    'creator_id' => 'created_by',
                    'seller_id' => 'created_by'  // Returns don't have seller_id, use created_by for both
                ],
                'dateRangeColumns' => ['created_at']
            ];

            $query = $this->applyCommonFilters($query, $request, $filterConfig);

            // Apply custom search after other filters to avoid conflicts
            if (!empty($searchValue) || !empty($searchTerm)) {
                $searchText = !empty($searchTerm) ? $searchTerm : $searchValue;
                $query->search($searchText);
            }

            // Apply custom filters specific to returns
            $query = $this->applyCustomFilters($query, $request->all());

            // Get total count before pagination
            $totalRecords = $query->count();

            // Apply pagination
            $returns = $query->orderBy('created_at', 'desc')
                ->skip($start)
                ->take($length)
                ->get();

            // Format data for DataTable
            $data = [];
            foreach ($returns as $return) {
                $data[] = [
                    'id' => $return->id,
                    'return_number' => $return->return_number,
                    'customer_name' => $return->customer_name,
                    'customer_phone' => $return->customer_phone ?? '',
                    'total_amount' => $return->total_amount,
                    'formatted_total' => number_format($return->total_amount, 0, ',', '.') . ' ₫',
                    'paid_amount' => $return->paid_amount,
                    'formatted_paid' => number_format($return->paid_amount, 0, ',', '.') . ' ₫',
                    'status' => $return->status,
                    'status_badge' => $return->status_badge,
                    'payment_status' => $return->payment_status,
                    'payment_status_badge' => $return->payment_status_badge,
                    'delivery_status' => $return->delivery_status,
                    'delivery_status_badge' => $return->delivery_status_badge,
                    'sale_channel' => $return->sale_channel_display,
                    'created_at' => $return->created_at->format('d/m/Y H:i'),
                    'creator_name' => $return->creator->full_name ?? 'N/A',
                    'branch_shop_name' => $return->branchShop->name ?? 'N/A',
                    'actions' => $this->generateActionButtons($return)
                ];
            }

            return response()->json([
                'draw' => (int) $request->get('draw'),
                'recordsTotal' => ReturnOrder::count(),
                'recordsFiltered' => $totalRecords,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            Log::error('Return AJAX error: ' . $e->getMessage());
            return response()->json([
                'draw' => (int) $request->get('draw'),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Generate action buttons for return row.
     */
    private function generateActionButtons($return): string
    {
        $buttons = [];

        // View button
        $buttons[] = '<a href="' . route('admin.return.show', $return->id) . '" class="btn btn-sm btn-light-primary" title="Xem chi tiết">
            <i class="fas fa-eye"></i>
        </a>';

        // Edit button (only for pending status)
        if (in_array($return->status, ['pending'])) {
            $buttons[] = '<a href="' . route('admin.return.edit', $return->id) . '" class="btn btn-sm btn-light-warning" title="Chỉnh sửa">
                <i class="fas fa-edit"></i>
            </a>';
        }

        // Print button
        $buttons[] = '<a href="' . route('admin.return.print', $return->id) . '" target="_blank" class="btn btn-sm btn-light-info" title="In">
            <i class="fas fa-print"></i>
        </a>';

        // Export button
        $buttons[] = '<button type="button" class="btn btn-sm btn-light-success" onclick="exportReturn(' . $return->id . ')" title="Xuất file">
            <i class="fas fa-file-export"></i>
        </button>';

        // Delete button (only for pending status)
        if ($return->status === 'pending') {
            $buttons[] = '<button type="button" class="btn btn-sm btn-light-danger" onclick="deleteReturn(' . $return->id . ')" title="Xóa">
                <i class="fas fa-trash"></i>
            </button>';
        }

        return '<div class="btn-group">' . implode('', $buttons) . '</div>';
    }

    /**
     * Apply custom filters specific to returns (not covered by FilterableTrait)
     */
    private function applyCustomFilters($query, $params)
    {
        // Return code filter (Code or code parameter)
        $returnCode = $params['Code'] ?? $params['code'] ?? null;
        if (!empty($returnCode)) {
            $query->where('return_number', $returnCode);
            Log::info('Return AJAX: Filtering by return code', ['code' => $returnCode]);
        }

        // Delivery status filters
        if (!empty($params['delivery_status']) && is_array($params['delivery_status'])) {
            $query->whereIn('delivery_status', $params['delivery_status']);
        }

        // Sale channel filter
        if (!empty($params['sale_channel'])) {
            $query->where('sale_channel', $params['sale_channel']);
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

        return $query;
    }

    /**
     * Show the form for creating a new return.
     */
    public function create()
    {
        return view('admin.returns.create');
    }

    /**
     * Get filtered returns for export.
     */
    private function getFilteredReturns($params = [])
    {
        $query = ReturnOrder::with(['customer', 'creator', 'branchShop']);

        // Apply filters similar to getReturnsAjax
        if (!empty($params['status_filter'])) {
            $query->whereIn('status', $params['status_filter']);
        }

        if (!empty($params['creator_filter'])) {
            $query->whereIn('created_by', $params['creator_filter']);
        }

        if (!empty($params['time_filter'])) {
            $timeFilter = $params['time_filter'];
            $now = Carbon::now();

            switch ($timeFilter) {
                case 'today':
                    $query->whereDate('created_at', $now->toDateString());
                    break;
                case 'yesterday':
                    $query->whereDate('created_at', $now->subDay()->toDateString());
                    break;
                case 'this_week':
                    $query->whereBetween('created_at', [$now->startOfWeek(), $now->endOfWeek()]);
                    break;
                case 'this_month':
                    $query->whereMonth('created_at', $now->month)
                          ->whereYear('created_at', $now->year);
                    break;
                case 'this_year':
                    $query->whereYear('created_at', $now->year);
                    break;
                case 'custom':
                    if (!empty($params['date_from'])) {
                        $query->whereDate('created_at', '>=', $params['date_from']);
                    }
                    if (!empty($params['date_to'])) {
                        $query->whereDate('created_at', '<=', $params['date_to']);
                    }
                    break;
            }
        }

        if (!empty($params['search'])) {
            $search = $params['search'];
            $query->where(function($q) use ($search) {
                $q->where('return_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($customerQuery) use ($search) {
                      $customerQuery->where('name', 'like', "%{$search}%")
                                   ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Export returns to Excel.
     */
    public function exportExcel(Request $request)
    {
        try {
            $params = $request->all();

            // Get filtered returns
            $returns = $this->getFilteredReturns($params);

            $filename = 'don-tra-hang-' . date('Y-m-d-H-i-s') . '.xlsx';

            return Excel::download(new ReturnsExport($returns), $filename);

        } catch (\Exception $e) {
            Log::error('Return export error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xuất dữ liệu.'
            ], 500);
        }
    }

    /**
     * Export single return to PDF.
     */
    public function exportPdf($id)
    {
        try {
            $return = ReturnOrder::with(['customer', 'returnOrderItems.product', 'creator', 'branchShop'])
                                ->findOrFail($id);

            $pdf = PDF::loadView('admin.returns.pdf.return_detail', compact('return'));

            $filename = 'don-tra-hang-' . $return->return_number . '.pdf';

            return $pdf->download($filename);

        } catch (\Exception $e) {
            Log::error('Return PDF export error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xuất PDF.'
            ], 500);
        }
    }

    /**
     * Store a newly created return in storage.
     */
    public function store(Request $request)
    {
        // Check if this is a Quick Order return (has return_items field)
        if ($request->has('return_items')) {
            return $this->storeQuickOrderReturn($request);
        }

        // Regular return order creation logic here
        return redirect()->route('admin.return.list')->with('success', 'Đơn trả hàng đã được tạo thành công.');
    }

    /**
     * Store return order from Quick Order.
     */
    public function storeQuickOrderReturn(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'invoice_code' => 'required|string',
            'branch_shop_id' => 'required|exists:branch_shops,id',
            'return_items' => 'required|array|min:1',
            'return_items.*.product_sku' => 'required|string',
            'return_items.*.quantity' => 'required|integer|min:1',
            'return_items.*.price' => 'required|numeric|min:0',
            'return_subtotal' => 'required|numeric|min:0',
            'refund_amount' => 'required|numeric|min:0',
            'exchange_items' => 'sometimes|array',
            'exchange_items.*.product_sku' => 'required_with:exchange_items|string',
            'exchange_items.*.quantity' => 'required_with:exchange_items|integer|min:1',
            'exchange_items.*.price' => 'required_with:exchange_items|numeric|min:0',
            'exchange_total' => 'sometimes|numeric|min:0',
            'net_payable' => 'sometimes|numeric|min:0',
            'payment_method' => 'sometimes|in:cash,transfer,card,ewallet',
            'bank_account_id' => 'required_if:payment_method,transfer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Use ReturnOrderService for now
            $returnOrderService = app(\App\Services\ReturnOrderService::class);
            $result = $returnOrderService->createQuickOrderReturn($request->all());
            return response()->json($result, $result['success'] ? 201 : 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tạo đơn trả hàng: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified return.
     */
    public function show($id)
    {
        $return = ReturnOrder::with(['customer', 'returnOrderItems.product', 'creator', 'branchShop'])->findOrFail($id);
        return view('admin.returns.show', compact('return'));
    }

    /**
     * Show the form for editing the specified return.
     */
    public function edit($id)
    {
        $return = ReturnOrder::findOrFail($id);
        return view('admin.returns.edit', compact('return'));
    }

    /**
     * Update the specified return in storage.
     */
    public function update(Request $request, $id)
    {
        // Implementation for updating return
        return redirect()->route('admin.return.list')->with('success', 'Đơn trả hàng đã được cập nhật thành công.');
    }

    /**
     * Remove the specified return from storage.
     */
    public function destroy($id)
    {
        // Implementation for deleting return
        return response()->json(['success' => true, 'message' => 'Đơn trả hàng đã được xóa thành công.']);
    }

    /**
     * Get filter users for returns.
     */
    public function getFilterUsers(Request $request)
    {
        $users = User::select('id', 'full_name')
            ->whereHas('roles', function($q) {
                $q->whereIn('name', ['shop_manager', 'staff']);
            })
            ->orderBy('full_name')
            ->get();

        return response()->json($users);
    }



    /**
     * Get payment history for return.
     */
    public function getPaymentHistory($id)
    {
        try {
            $return = ReturnOrder::findOrFail($id);
            $payments = $return->payments()->orderBy('created_at', 'desc')->get();

            return response()->json([
                'success' => true,
                'data' => $payments
            ]);

        } catch (\Exception $e) {
            Log::error('Return payment history error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tải lịch sử thanh toán.'
            ], 500);
        }
    }

    /**
     * Record payment for return.
     */
    public function recordPayment(Request $request, $id)
    {
        // Implementation for recording payment
        return response()->json(['success' => true, 'message' => 'Thanh toán đã được ghi nhận.']);
    }

    /**
     * Send return notification.
     */
    public function sendReturn(Request $request, $id)
    {
        // Implementation for sending return notification
        return response()->json(['success' => true, 'message' => 'Thông báo đơn trả hàng đã được gửi.']);
    }

    /**
     * Cancel return.
     */
    public function cancelReturn(Request $request, $id)
    {
        // Implementation for canceling return
        return response()->json(['success' => true, 'message' => 'Đơn trả hàng đã được hủy.']);
    }



    /**
     * Print return.
     */
    public function print($id)
    {
        $return = ReturnOrder::with(['customer', 'returnOrderItems.product'])->findOrFail($id);
        return view('admin.returns.print', compact('return'));
    }

    /**
     * Get statistics for returns.
     */
    public function getStatistics(Request $request)
    {
        // Implementation for getting return statistics
        return response()->json([
            'total_returns' => 0,
            'total_amount' => 0,
            'pending_returns' => 0,
            'completed_returns' => 0
        ]);
    }

    /**
     * Create return from invoice.
     */
    public function createFromInvoice(Request $request, $invoice_id)
    {
        try {
            // Validate request data
            $validator = \Validator::make($request->all(), [
                'invoice_number' => 'required|string',
                'customer_name' => 'nullable|string',
                'customer_phone' => 'nullable|string',
                'return_items' => 'required|array|min:1',
                'return_items.*.product_id' => 'required|integer|exists:products,id',
                'return_items.*.product_name' => 'required|string',
                'return_items.*.quantity' => 'required|integer|min:1',
                'return_items.*.price' => 'required|numeric|min:0',
                'exchange_items' => 'sometimes|array',
                'exchange_items.*.product_id' => 'required_with:exchange_items|integer|exists:products,id',
                'exchange_items.*.product_name' => 'required_with:exchange_items|string',
                'exchange_items.*.quantity' => 'required_with:exchange_items|integer|min:1',
                'exchange_items.*.price' => 'required_with:exchange_items|numeric|min:0',
                'payment_method' => 'sometimes|string|in:cash,transfer,card,ewallet',
                'notes' => 'nullable|string',
                'return_subtotal' => 'sometimes|numeric|min:0',
                'exchange_subtotal' => 'sometimes|numeric|min:0',
                'refund_amount' => 'sometimes|numeric',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dữ liệu không hợp lệ',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Get user's branch shops
            $userBranchShops = \Auth::user()->branchShops->pluck('id')->toArray();
            if (empty($userBranchShops)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn không có quyền truy cập chi nhánh nào'
                ], 403);
            }

            // Calculate net_payable for exchange orders
            $exchangeSubtotal = $request->exchange_subtotal ?? 0;
            $refundAmount = $request->refund_amount ?? 0;
            $netPayable = max(0, $exchangeSubtotal - $refundAmount);

            // Prepare data for ReturnOrderService
            $returnOrderData = [
                'invoice_id' => $invoice_id, // Add invoice_id for service
                'invoice_code' => $request->invoice_number,
                'branch_shop_id' => $userBranchShops[0], // Use first available branch shop
                'return_items' => $request->return_items,
                'exchange_items' => $request->exchange_items ?? [],
                'payment_method' => $request->payment_method ?? 'cash',
                'notes' => $request->notes,
                'return_subtotal' => $request->return_subtotal ?? 0,
                'exchange_subtotal' => $exchangeSubtotal,
                'refund_amount' => $refundAmount,
                'net_payable' => $netPayable, // Add net_payable for exchange order processing
            ];

            // Create return order using ReturnOrderService
            $returnOrderService = app(\App\Services\ReturnOrderService::class);
            $result = $returnOrderService->createQuickOrderReturn($returnOrderData);

            if (!$result['success']) {
                \Log::error('Return order creation failed in createFromInvoice', [
                    'user_id' => \Auth::id(),
                    'invoice_id' => $invoice_id,
                    'error_message' => $result['message'] ?? 'Unknown error',
                    'request_data' => $request->all(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'Không thể tạo đơn trả hàng. Vui lòng thử lại sau.'
                ], 500);
            }

            $returnOrder = $result['data']['return_order'];

            \Log::info('Return order created successfully from invoice', [
                'user_id' => \Auth::id(),
                'invoice_id' => $invoice_id,
                'return_order_id' => $returnOrder['id'],
                'return_number' => $returnOrder['return_number'] ?? 'N/A',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Đơn trả hàng đã được tạo thành công',
                'return_order_number' => $returnOrder['return_number'],
                'data' => [
                    'return_order_id' => $returnOrder['id'],
                    'return_number' => $returnOrder['return_number'],
                    'total_amount' => $returnOrder['total_amount'],
                    'formatted_total' => number_format($returnOrder->total_amount, 0, ',', '.') . ' VND',
                    'redirect_url' => route('admin.return.list') . '?code=' . $returnOrder->return_number,
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Exception in createFromInvoice', [
                'user_id' => \Auth::id(),
                'invoice_id' => $invoice_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tạo đơn trả hàng: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk update return status.
     */
    public function bulkUpdateStatus(Request $request)
    {
        try {
            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'integer|exists:return_orders,id',
                'status' => 'required|in:pending,approved,rejected,completed,cancelled'
            ]);

            $updated = ReturnOrder::whereIn('id', $request->ids)
                                 ->update(['status' => $request->status]);

            return response()->json([
                'success' => true,
                'message' => "Đã cập nhật trạng thái cho {$updated} đơn trả hàng."
            ]);

        } catch (\Exception $e) {
            Log::error('Bulk update return status error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi cập nhật trạng thái.'
            ], 500);
        }
    }

    /**
     * Bulk cancel returns.
     */
    public function bulkCancel(Request $request)
    {
        try {
            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'integer|exists:return_orders,id'
            ]);

            $updated = ReturnOrder::whereIn('id', $request->ids)
                                 ->whereIn('status', ['pending', 'approved'])
                                 ->update(['status' => 'cancelled']);

            return response()->json([
                'success' => true,
                'message' => "Đã hủy {$updated} đơn trả hàng."
            ]);

        } catch (\Exception $e) {
            Log::error('Bulk cancel returns error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi hủy đơn trả hàng.'
            ], 500);
        }
    }

    /**
     * Bulk delete returns.
     */
    public function bulkDelete(Request $request)
    {
        try {
            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'integer|exists:return_orders,id'
            ]);

            // Only allow deleting pending returns
            $deleted = ReturnOrder::whereIn('id', $request->ids)
                                 ->where('status', 'pending')
                                 ->delete();

            return response()->json([
                'success' => true,
                'message' => "Đã xóa {$deleted} đơn trả hàng."
            ]);

        } catch (\Exception $e) {
            Log::error('Bulk delete returns error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xóa đơn trả hàng.'
            ], 500);
        }
    }

    /**
     * Get return detail panel content.
     */
    public function getDetailPanel($id)
    {
        try {
            $return = ReturnOrder::with(['customer', 'creator', 'branchShop', 'returnOrderItems.product', 'payments'])
                                 ->findOrFail($id);

            return view('admin.returns.partials.detail-panel', compact('return'));

        } catch (\Exception $e) {
            Log::error('Return detail panel error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tải thông tin chi tiết.'
            ], 500);
        }
    }
}
