<?php

namespace App\Http\Controllers\Admin\CMS;

use App\Http\Controllers\Controller;
use App\Models\ReturnOrder;
use App\Models\Invoice;
use App\Services\ReturnOrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;



class ReturnOrderController extends Controller
{
    protected $returnOrderService;

    public function __construct(ReturnOrderService $returnOrderService)
    {
        $this->returnOrderService = $returnOrderService;
    }

    /**
     * Display a listing of return orders.
     */
    public function index()
    {
        return view('admin.return-order.index');
    }

    /**
     * Get return orders data for AJAX (similar to InvoiceController).
     */
    public function getData(Request $request)
    {
        try {
            $params = $request->all();

            // DataTables parameters
            $draw = $params['draw'] ?? 1;
            $start = $params['start'] ?? 0;
            $length = $params['length'] ?? 10;
            $searchValue = $params['search']['value'] ?? '';

            // Build query
            $query = ReturnOrder::with(['invoice', 'customer', 'creator', 'branchShop']);

            // Apply search
            if (!empty($searchValue)) {
                $query->where(function($q) use ($searchValue) {
                    $q->where('return_number', 'like', "%{$searchValue}%")
                      ->orWhere('reason', 'like', "%{$searchValue}%")
                      ->orWhere('notes', 'like', "%{$searchValue}%")
                      ->orWhereHas('customer', function($customerQuery) use ($searchValue) {
                          $customerQuery->where('name', 'like', "%{$searchValue}%")
                                       ->orWhere('phone', 'like', "%{$searchValue}%");
                      })
                      ->orWhereHas('invoice', function($invoiceQuery) use ($searchValue) {
                          $invoiceQuery->where('invoice_number', 'like', "%{$searchValue}%");
                      });
                });
            }

            // Debug log
            Log::info('Return Order Filter Params:', $params);

            // Apply filters
            $this->applyFilters($query, $params);

            // Get total count
            $totalRecords = $query->count();

            // Get paginated results
            $returnOrders = $query->skip($start)
                                 ->take($length)
                                 ->orderBy('created_at', 'desc')
                                 ->get();

            // Format data for DataTables
            $data = $returnOrders->map(function($returnOrder) {
                return [
                    'id' => $returnOrder->id,
                    'checkbox' => '<div class="form-check form-check-sm form-check-custom form-check-solid">
                                    <input class="form-check-input" type="checkbox" value="' . $returnOrder->id . '" />
                                   </div>',
                    'return_number' => '<span class="text-gray-800 fw-bold return-number" data-return-id="' . $returnOrder->id . '" style="cursor: pointer;">' . $returnOrder->return_number . '</span>',
                    'invoice_number' => $returnOrder->invoice ? $returnOrder->invoice->invoice_number : 'N/A',
                    'customer_name' => $returnOrder->customer ? $returnOrder->customer->name : 'Khách lẻ',
                    'return_date' => $returnOrder->return_date ? $returnOrder->return_date->format('d/m/Y') : 'N/A',
                    'reason_display' => $this->getReasonDisplay($returnOrder->reason),
                    'total_amount' => number_format($returnOrder->total_amount, 0, ',', '.') . ' ₫',
                    'status_badge' => $this->getStatusBadge($returnOrder->status),
                    'creator_name' => $returnOrder->creator->name ?? 'N/A'
                ];
            });

            return response()->json([
                'draw' => intval($draw),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $totalRecords,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'draw' => $params['draw'] ?? 1,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Error loading return orders: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Apply filters to the query
     */
    private function applyFilters($query, $params)
    {
        // Status filter
        if (!empty($params['status'])) {
            $statuses = is_array($params['status']) ? $params['status'] : [$params['status']];
            $query->whereIn('status', $statuses);
        }

        // Reason filter
        if (!empty($params['reason'])) {
            $reasons = is_array($params['reason']) ? $params['reason'] : [$params['reason']];
            $query->whereIn('reason', $reasons);
        }

        // Creator filter
        if (!empty($params['creator_id'])) {
            $query->where('created_by', $params['creator_id']);
        }

        // Customer filter
        if (!empty($params['customer_id'])) {
            $query->where('customer_id', $params['customer_id']);
        }

        // Amount range filter
        if (!empty($params['amount_from'])) {
            $query->where('total_amount', '>=', $params['amount_from']);
        }
        if (!empty($params['amount_to'])) {
            $query->where('total_amount', '<=', $params['amount_to']);
        }

        // Time filter
        if (!empty($params['time_filter'])) {
            $this->applyTimeFilter($query, $params['time_filter'], $params);
        }
    }

    /**
     * Apply time filter to the query
     */
    private function applyTimeFilter($query, $timeFilter, $params = [])
    {
        // Debug log
        Log::info('Return Order Time Filter Applied', [
            'filter' => $timeFilter,
            'current_time' => now()->toDateTimeString()
        ]);

        // If 'all' or empty, don't apply any time filter
        if (empty($timeFilter) || $timeFilter === 'all') {
            Log::info('No time filter applied - showing all records');
            return;
        }

        $now = now();

        switch ($timeFilter) {
            case 'today':
                $today = $now->toDateString();
                $query->whereDate('return_date', $today);
                \Log::info('Today filter applied', ['date' => $today]);
                break;

            case 'yesterday':
                $yesterday = $now->copy()->subDay()->toDateString();
                $query->whereDate('return_date', $yesterday);
                \Log::info('Yesterday filter applied', ['date' => $yesterday]);
                break;

            case 'this_week':
                $startOfWeek = $now->copy()->startOfWeek()->toDateString();
                $endOfWeek = $now->copy()->endOfWeek()->toDateString();
                $query->whereBetween('return_date', [$startOfWeek, $endOfWeek]);
                \Log::info('This week filter applied', ['start' => $startOfWeek, 'end' => $endOfWeek]);
                break;

            case 'last_week':
                $startOfLastWeek = $now->copy()->subWeek()->startOfWeek()->toDateString();
                $endOfLastWeek = $now->copy()->subWeek()->endOfWeek()->toDateString();
                $query->whereBetween('return_date', [$startOfLastWeek, $endOfLastWeek]);
                \Log::info('Last week filter applied', ['start' => $startOfLastWeek, 'end' => $endOfLastWeek]);
                break;

            case 'this_month':
                $startOfMonth = $now->copy()->startOfMonth()->toDateString();
                $endOfMonth = $now->copy()->endOfMonth()->toDateString();
                $query->whereBetween('return_date', [$startOfMonth, $endOfMonth]);
                \Log::info('This month filter applied', ['start' => $startOfMonth, 'end' => $endOfMonth]);
                break;

            case 'last_month':
                $startOfLastMonth = $now->copy()->subMonth()->startOfMonth()->toDateString();
                $endOfLastMonth = $now->copy()->subMonth()->endOfMonth()->toDateString();
                $query->whereBetween('return_date', [$startOfLastMonth, $endOfLastMonth]);
                \Log::info('Last month filter applied', ['start' => $startOfLastMonth, 'end' => $endOfLastMonth]);
                break;

            case 'this_year':
                $startOfYear = $now->copy()->startOfYear()->toDateString();
                $endOfYear = $now->copy()->endOfYear()->toDateString();
                $query->whereBetween('return_date', [$startOfYear, $endOfYear]);
                \Log::info('This year filter applied', ['start' => $startOfYear, 'end' => $endOfYear]);
                break;

            case 'last_year':
                $startOfLastYear = $now->copy()->subYear()->startOfYear()->toDateString();
                $endOfLastYear = $now->copy()->subYear()->endOfYear()->toDateString();
                $query->whereBetween('return_date', [$startOfLastYear, $endOfLastYear]);
                \Log::info('Last year filter applied', ['start' => $startOfLastYear, 'end' => $endOfLastYear]);
                break;

            case '7_days':
                $sevenDaysAgo = $now->copy()->subDays(7)->toDateString();
                $today = $now->toDateString();
                $query->whereBetween('return_date', [$sevenDaysAgo, $today]);
                \Log::info('7 days filter applied', ['start' => $sevenDaysAgo, 'end' => $today]);
                break;

            case '30_days':
                $thirtyDaysAgo = $now->copy()->subDays(30)->toDateString();
                $today = $now->toDateString();
                $query->whereBetween('return_date', [$thirtyDaysAgo, $today]);
                \Log::info('30 days filter applied', ['start' => $thirtyDaysAgo, 'end' => $today]);
                break;

            case 'this_quarter':
                $startOfQuarter = $now->copy()->startOfQuarter()->toDateString();
                $endOfQuarter = $now->copy()->endOfQuarter()->toDateString();
                $query->whereBetween('return_date', [$startOfQuarter, $endOfQuarter]);
                \Log::info('This quarter filter applied', ['start' => $startOfQuarter, 'end' => $endOfQuarter]);
                break;

            case 'last_quarter':
                $startOfLastQuarter = $now->copy()->subQuarter()->startOfQuarter()->toDateString();
                $endOfLastQuarter = $now->copy()->subQuarter()->endOfQuarter()->toDateString();
                $query->whereBetween('return_date', [$startOfLastQuarter, $endOfLastQuarter]);
                \Log::info('Last quarter filter applied', ['start' => $startOfLastQuarter, 'end' => $endOfLastQuarter]);
                break;

            case 'custom':
                // Handle custom date range
                if (!empty($params['date_from'])) {
                    $query->whereDate('return_date', '>=', $params['date_from']);
                    \Log::info('Custom date from applied', ['date_from' => $params['date_from']]);
                }
                if (!empty($params['date_to'])) {
                    $query->whereDate('return_date', '<=', $params['date_to']);
                    \Log::info('Custom date to applied', ['date_to' => $params['date_to']]);
                }
                break;

            default:
                \Log::warning('Unknown time filter', ['filter' => $timeFilter]);
                break;
        }
    }

    /**
     * Get reason display text
     */
    private function getReasonDisplay($reason)
    {
        $reasons = [
            'defective' => 'Hàng lỗi',
            'wrong_item' => 'Giao sai hàng',
            'customer_request' => 'Khách hàng yêu cầu',
            'damaged' => 'Hàng bị hỏng',
            'expired' => 'Hết hạn',
            'other' => 'Khác'
        ];

        return $reasons[$reason] ?? $reason;
    }

    /**
     * Get status badge HTML
     */
    private function getStatusBadge($status)
    {
        $badges = [
            'pending' => '<span class="badge badge-warning">Chờ duyệt</span>',
            'approved' => '<span class="badge badge-success">Đã duyệt</span>',
            'rejected' => '<span class="badge badge-danger">Từ chối</span>',
            'completed' => '<span class="badge badge-primary">Hoàn thành</span>',
            'returned' => '<span class="badge badge-info">Đã trả</span>',
            'cancelled' => '<span class="badge badge-secondary">Đã hủy</span>'
        ];

        return $badges[$status] ?? '<span class="badge badge-secondary">' . $status . '</span>';
    }





    /**
     * Show the form for creating a new return order.
     */
    public function create(Request $request)
    {
        $invoiceId = $request->get('invoice_id');
        $invoice = null;
        
        if ($invoiceId) {
            $invoice = Invoice::with(['customer', 'invoiceItems.product'])->find($invoiceId);
        }
        
        return view('admin.return-order.create', compact('invoice'));
    }

    /**
     * Store a newly created return order.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'invoice_id' => 'required|exists:invoices,id',
            'return_date' => 'required|date',
            'reason' => 'required|in:defective,wrong_item,customer_request,damaged,expired,other',
            'refund_method' => 'required|in:cash,card,transfer,store_credit,exchange,points',
            'items' => 'required|array|min:1',
            'items.*.invoice_item_id' => 'required|exists:invoice_items,id',
            'items.*.quantity_returned' => 'required|integer|min:1',
            'items.*.condition' => 'required|in:new,used,damaged,expired',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        $result = $this->returnOrderService->createReturnOrder($request->all());

        return response()->json($result, $result['success'] ? 201 : 400);
    }

    /**
     * Display the specified return order.
     */
    public function show(ReturnOrder $returnOrder)
    {
        $returnOrder->load([
            'customer', 
            'invoice.invoiceItems.product', 
            'returnOrderItems.product', 
            'branchShop', 
            'creator', 
            'approver',
            'payments'
        ]);
        
        return view('admin.return-order.show', compact('returnOrder'));
    }

    /**
     * Show the form for editing the specified return order.
     */
    public function edit(ReturnOrder $returnOrder)
    {
        if ($returnOrder->status !== 'pending') {
            return redirect()->route('return-order.show', $returnOrder)
                           ->with('error', 'Chỉ có thể sửa đơn trả hàng ở trạng thái chờ duyệt');
        }
        
        $returnOrder->load([
            'customer', 
            'invoice.invoiceItems.product', 
            'returnOrderItems.product'
        ]);
        
        return view('admin.return-order.edit', compact('returnOrder'));
    }

    /**
     * Update the specified return order.
     */
    public function update(Request $request, ReturnOrder $returnOrder)
    {
        try {
            // Simple update for receiver and return date from detail panel
            if ($request->has('receiver_id') || $request->has('return_date')) {
                $updateData = [];

                if ($request->has('receiver_id')) {
                    $updateData['receiver_id'] = $request->receiver_id;
                }

                if ($request->has('return_date')) {
                    $updateData['return_date'] = $request->return_date;
                }

                $returnOrder->update($updateData);

                return response()->json([
                    'success' => true,
                    'message' => 'Cập nhật thành công!'
                ]);
            }

            // Original complex update logic for full form
            if ($returnOrder->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Chỉ có thể sửa đơn trả hàng ở trạng thái chờ duyệt'
                ], 400);
            }

            $validator = Validator::make($request->all(), [
                'return_date' => 'required|date',
                'reason' => 'required|in:defective,wrong_item,customer_request,damaged,expired,other',
                'refund_method' => 'required|in:cash,card,transfer,store_credit,exchange,points',
                'items' => 'required|array|min:1',
                'items.*.invoice_item_id' => 'required|exists:invoice_items,id',
                'items.*.quantity_returned' => 'required|integer|min:1',
                'items.*.condition' => 'required|in:new,used,damaged,expired',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dữ liệu không hợp lệ',
                    'errors' => $validator->errors()
                ], 422);
            }

            $result = $this->returnOrderService->updateReturnOrder($returnOrder, $request->all());

            return response()->json($result, $result['success'] ? 200 : 400);
        } catch (\Exception $e) {
            Log::error('Error updating return order: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi cập nhật'
            ], 500);
        }
    }

    /**
     * Approve return order.
     */
    public function approve(Request $request, ReturnOrder $returnOrder)
    {
        if ($returnOrder->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Chỉ có thể duyệt đơn trả hàng ở trạng thái chờ duyệt'
            ], 400);
        }

        $result = $this->returnOrderService->approveReturnOrder($returnOrder, $request->all());

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Complete return order.
     */
    public function complete(Request $request, ReturnOrder $returnOrder)
    {
        if ($returnOrder->status !== 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Chỉ có thể hoàn thành đơn trả hàng đã được duyệt'
            ], 400);
        }

        $result = $this->returnOrderService->completeReturnOrder($returnOrder, $request->all());

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Reject return order.
     */
    public function reject(Request $request, ReturnOrder $returnOrder)
    {
        if ($returnOrder->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Chỉ có thể từ chối đơn trả hàng ở trạng thái chờ duyệt'
            ], 400);
        }

        $result = $this->returnOrderService->rejectReturnOrder($returnOrder, $request->reason);

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Get return order details for API.
     */
    public function getDetails(ReturnOrder $returnOrder)
    {
        $returnOrder->load([
            'customer',
            'invoice',
            'returnOrderItems.product',
            'branchShop',
            'creator',
            'approver'
        ]);

        return response()->json([
            'success' => true,
            'data' => $returnOrder
        ]);
    }

    /**
     * Get return order detail panel for expansion row.
     */
    public function getDetailPanel(ReturnOrder $returnOrder)
    {
        $returnOrder->load([
            'customer',
            'invoice.creator',
            'returnOrderItems.product',
            'branchShop',
            'creator',
            'receiver',
            'approver'
        ]);

        // Get current user's branch shops
        $currentUser = auth()->user();
        $userBranchShops = $currentUser->currentBranchShops()->pluck('branch_shops.id');

        // Get users from the same branch shops as current user
        $branchUsers = collect();
        if ($userBranchShops->isNotEmpty()) {
            $branchUsers = \App\Models\User::whereHas('currentBranchShops', function($query) use ($userBranchShops) {
                $query->whereIn('branch_shops.id', $userBranchShops);
            })->select('id', 'full_name', 'email')->get();
        }

        return view('admin.return-order.partials.detail-panel', compact('returnOrder', 'branchUsers'));
    }

    /**
     * Cancel return order.
     */
    public function cancel(ReturnOrder $returnOrder)
    {
        try {
            if ($returnOrder->status === 'cancelled') {
                return response()->json([
                    'success' => false,
                    'message' => 'Đơn trả hàng đã được hủy trước đó'
                ], 400);
            }

            $returnOrder->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancelled_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Hủy đơn trả hàng thành công'
            ]);
        } catch (\Exception $e) {
            Log::error('Error cancelling return order: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi hủy đơn trả hàng'
            ], 500);
        }
    }

    /**
     * Copy return order.
     */
    public function copy(ReturnOrder $returnOrder)
    {
        try {
            $newReturnOrder = $this->returnOrderService->copyReturnOrder($returnOrder);

            return response()->json([
                'success' => true,
                'message' => 'Sao chép đơn trả hàng thành công',
                'redirect_url' => route('admin.return-order.show', $newReturnOrder->id)
            ]);
        } catch (\Exception $e) {
            Log::error('Error copying return order: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi sao chép đơn trả hàng'
            ], 500);
        }
    }

    /**
     * Export return order to PDF.
     */
    public function export(ReturnOrder $returnOrder)
    {
        try {
            $returnOrder->load([
                'customer',
                'invoice',
                'returnOrderItems.product',
                'branchShop',
                'creator',
                'receiver'
            ]);

            $pdf = \PDF::loadView('admin.return-order.export', compact('returnOrder'));

            return $pdf->download("return-order-{$returnOrder->return_order_code}.pdf");
        } catch (\Exception $e) {
            Log::error('Error exporting return order: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xuất file'
            ], 500);
        }
    }

    /**
     * Print return order.
     */
    public function print(ReturnOrder $returnOrder)
    {
        try {
            $returnOrder->load([
                'customer',
                'invoice',
                'returnOrderItems.product',
                'branchShop',
                'creator',
                'receiver'
            ]);

            return view('admin.return-order.print', compact('returnOrder'));
        } catch (\Exception $e) {
            Log::error('Error printing return order: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi in đơn trả hàng'
            ], 500);
        }
    }
}
