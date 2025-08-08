<?php

namespace App\Http\Controllers\Admin\CMS;

use App\Http\Controllers\Controller;
use App\Models\ReturnOrder;
use App\Models\ReturnOrderItem;
use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Product;
use App\Models\BranchShop;
use App\Models\User;
use App\Models\Payment;
use App\Traits\FilterableTrait;
use App\Traits\HandlesApiErrors;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ReturnController extends Controller
{
    use FilterableTrait, HandlesApiErrors;

    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Display return orders list.
     */
    public function index(Request $request)
    {
        $branchShops = BranchShop::active()->get();

        // Prepare filter data
        $filterData = $this->getFilterData();

        // Handle return code search via Code parameter
        $returnCodeSearch = null;
        $searchedReturn = null;
        if ($request->has('code') && !empty($request->get('code'))) {
            $returnCode = $request->get('code');
            $returnCodeSearch = $returnCode;

            // Find return by return_number
            $searchedReturn = ReturnOrder::where('return_number', $returnCode)->first();

            if ($searchedReturn) {
                Log::info('Return code search in returns', [
                    'return_code' => $returnCode,
                    'return_id' => $searchedReturn->id,
                    'customer_name' => $searchedReturn->customer_name ?? 'Khách lẻ'
                ]);
            } else {
                Log::warning('Return code not found in returns search', ['return_code' => $returnCode]);
            }
        }

        return view('admin.returns.index', compact('branchShops', 'returnCodeSearch', 'searchedReturn') + $filterData);
    }

    /**
     * Get filter data for return listing
     */
    private function getFilterData()
    {
        // Status options
        $statuses = [
            ['value' => 'pending', 'label' => 'Chờ duyệt', 'checked' => true],
            ['value' => 'approved', 'label' => 'Đã duyệt', 'checked' => true],
            ['value' => 'rejected', 'label' => 'Từ chối', 'checked' => false],
            ['value' => 'completed', 'label' => 'Hoàn thành', 'checked' => true],
        ];

        // Return reasons
        $reasons = [
            ['value' => 'defective', 'label' => 'Hàng lỗi'],
            ['value' => 'wrong_item', 'label' => 'Giao sai hàng'],
            ['value' => 'customer_request', 'label' => 'Khách yêu cầu'],
            ['value' => 'damaged', 'label' => 'Hàng bị hỏng'],
            ['value' => 'expired', 'label' => 'Hàng hết hạn'],
            ['value' => 'other', 'label' => 'Khác'],
        ];

        // Refund methods
        $refundMethods = [
            ['value' => 'cash', 'label' => 'Tiền mặt'],
            ['value' => 'card', 'label' => 'Thẻ'],
            ['value' => 'transfer', 'label' => 'Chuyển khoản'],
            ['value' => 'store_credit', 'label' => 'Tín dụng cửa hàng'],
            ['value' => 'exchange', 'label' => 'Đổi hàng'],
            ['value' => 'points', 'label' => 'Điểm thưởng'],
        ];

        // Get users for creator and approver filters
        $creators = User::select('id', 'full_name')
            ->whereHas('roles', function($q) {
                $q->whereIn('name', ['shop_manager', 'staff']);
            })
            ->orderBy('full_name')
            ->get();

        $approvers = User::select('id', 'full_name')
            ->whereHas('roles', function($q) {
                $q->whereIn('name', ['shop_manager', 'admin']);
            })
            ->orderBy('full_name')
            ->get();

        return compact(
            'statuses',
            'reasons',
            'refundMethods',
            'creators',
            'approvers'
        );
    }

    /**
     * Get returns data for DataTables (AJAX).
     */
    public function getReturnsAjax()
    {
        try {
            $params = $this->request->all();

            // Debug log to see what parameters are received
            Log::info('Return AJAX request parameters:', $params);

            // DataTables parameters
            $draw = $params['draw'] ?? 1;
            $page = $params['page'] ?? 1;
            $start = ($page - 1) * ($params['per_page'] ?? 10);
            $length = $params['per_page'] ?? 10;
            $searchValue = $params['search']['value'] ?? '';

            // Custom search term
            $searchTerm = $params['search_term'] ?? '';

            // Build query with optimized relationships
            $query = ReturnOrder::with([
                'customer:id,name,phone,email,address',
                'branchShop:id,name',
                'creator:id,full_name',
                'approver:id,full_name',
                'invoice:id,invoice_number'
            ]);

            // Apply search
            if (!empty($searchValue) || !empty($searchTerm)) {
                $searchText = !empty($searchTerm) ? $searchTerm : $searchValue;
                $query->search($searchText);
            }

            // Apply filters using FilterableTrait
            $filterConfig = [
                'searchColumns' => ['return_number'],
                'statusColumn' => 'status',
                'userColumns' => [
                    'creator_id' => 'created_by',
                    'approver_id' => 'approved_by'
                ],
                'dateRangeColumns' => ['return_date', 'created_at']
            ];

            $this->applyCommonFilters($query, $this->request, $filterConfig);

            // Apply additional custom filters
            $this->applyCustomFilters($query, $params);
            
            // Get total count before pagination
            $totalRecords = $query->count();
            
            // Apply pagination and ordering
            $returns = $query->skip($start)
                             ->take($length)
                             ->orderBy('created_at', 'desc')
                             ->get();
            
            // Format data for simple table
            $data = $returns->map(function($return) {
                return [
                    'id' => $return->id,
                    'return_number' => $return->return_number,
                    'invoice_number' => $return->invoice->invoice_number ?? '',
                    'customer_display' => $return->customer ? $return->customer->name : 'Khách lẻ',
                    'total_amount' => $return->total_amount ?? 0,
                    'status' => $return->status ?? 'pending',
                    'reason' => $return->reason ?? '',
                    'refund_method' => $return->refund_method ?? 'cash',
                    'return_date' => $return->return_date ? $return->return_date->toISOString() : null,
                    'created_at' => $return->created_at ? $return->created_at->toISOString() : null,
                    'creator' => $return->creator->full_name ?? '',
                    'approver' => $return->approver->full_name ?? '',
                    'customer_phone' => $return->customer->phone ?? '',
                    'customer_email' => $return->customer->email ?? '',
                    'branch_shop' => $return->branchShop->name ?? '',
                ];
            });

            // Return response in expected format
            return response()->json([
                'draw' => intval($draw),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $totalRecords,
                'data' => $data,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $length,
                    'total' => $totalRecords,
                    'last_page' => ceil($totalRecords / $length)
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error in getReturnsAjax: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'params' => $params ?? []
            ]);

            return response()->json([
                'draw' => intval($params['draw'] ?? 1),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Có lỗi xảy ra khi tải dữ liệu'
            ], 500);
        }
    }

    /**
     * Apply custom filters specific to returns
     */
    private function applyCustomFilters($query, $params)
    {
        // Filter by reason
        if (!empty($params['reason'])) {
            $query->where('reason', $params['reason']);
        }

        // Filter by refund method
        if (!empty($params['refund_method'])) {
            $query->where('refund_method', $params['refund_method']);
        }

        // Filter by branch shop
        if (!empty($params['branch_shop_id'])) {
            $query->where('branch_shop_id', $params['branch_shop_id']);
        }

        // Filter by invoice
        if (!empty($params['invoice_id'])) {
            $query->where('invoice_id', $params['invoice_id']);
        }
    }

    /**
     * Show the form for creating a new return order.
     */
    public function create()
    {
        $branchShops = BranchShop::active()->get();
        $customers = Customer::select('id', 'name', 'phone', 'email')->get();
        
        return view('admin.returns.create', compact('branchShops', 'customers'));
    }

    /**
     * Store a newly created return order.
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'invoice_id' => 'required|exists:invoices,id',
                'customer_id' => 'nullable|exists:customers,id',
                'branch_shop_id' => 'nullable|exists:branch_shops,id',
                'return_date' => 'required|date',
                'reason' => 'required|in:defective,wrong_item,customer_request,damaged,expired,other',
                'reason_detail' => 'nullable|string|max:1000',
                'refund_method' => 'required|in:cash,card,transfer,store_credit,exchange,points',
                'notes' => 'nullable|string|max:1000',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity_returned' => 'required|integer|min:1',
                'items.*.unit_price' => 'required|numeric|min:0',
                'items.*.condition' => 'nullable|string|max:255',
                'items.*.notes' => 'nullable|string|max:500',
            ]);

            DB::beginTransaction();

            // Generate return number
            $returnNumber = $this->generateReturnNumber();

            // Calculate totals
            $subtotal = 0;
            foreach ($validatedData['items'] as $item) {
                $subtotal += $item['quantity_returned'] * $item['unit_price'];
            }

            $taxRate = 0; // You can implement tax calculation if needed
            $taxAmount = $subtotal * ($taxRate / 100);
            $totalAmount = $subtotal + $taxAmount;

            // Create return order
            $returnOrder = ReturnOrder::create([
                'return_number' => $returnNumber,
                'invoice_id' => $validatedData['invoice_id'],
                'customer_id' => $validatedData['customer_id'] ?: 0,
                'branch_shop_id' => $validatedData['branch_shop_id'],
                'return_date' => $validatedData['return_date'],
                'reason' => $validatedData['reason'],
                'reason_detail' => $validatedData['reason_detail'],
                'status' => 'pending',
                'subtotal' => $subtotal,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'refund_method' => $validatedData['refund_method'],
                'notes' => $validatedData['notes'],
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);

            // Create return order items
            foreach ($validatedData['items'] as $index => $item) {
                ReturnOrderItem::create([
                    'return_order_id' => $returnOrder->id,
                    'product_id' => $item['product_id'],
                    'quantity_returned' => $item['quantity_returned'],
                    'unit_price' => $item['unit_price'],
                    'line_total' => $item['quantity_returned'] * $item['unit_price'],
                    'condition' => $item['condition'],
                    'notes' => $item['notes'],
                    'sort_order' => $index + 1,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Đơn trả hàng đã được tạo thành công',
                'return_id' => $returnOrder->id,
                'return_number' => $returnOrder->return_number
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating return order: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tạo đơn trả hàng: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate unique return number
     */
    private function generateReturnNumber()
    {
        $date = Carbon::now()->format('ymd');
        $prefix = 'TH' . $date;

        // Get the last return number for today
        $lastReturn = ReturnOrder::where('return_number', 'like', $prefix . '%')
            ->orderBy('return_number', 'desc')
            ->first();

        if ($lastReturn) {
            $lastNumber = intval(substr($lastReturn->return_number, -3));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Display the specified return order.
     */
    public function show($id)
    {
        $returnOrder = ReturnOrder::with([
            'customer',
            'branchShop',
            'invoice',
            'creator',
            'approver',
            'returnOrderItems.product'
        ])->findOrFail($id);

        return view('admin.returns.show', compact('returnOrder'));
    }

    /**
     * Show the form for editing the specified return order.
     */
    public function edit($id)
    {
        $returnOrder = ReturnOrder::with([
            'customer',
            'branchShop',
            'invoice',
            'returnOrderItems.product'
        ])->findOrFail($id);

        // Only allow editing if status is pending
        if ($returnOrder->status !== 'pending') {
            return redirect()->route('admin.return.show', $id)
                ->with('error', 'Chỉ có thể chỉnh sửa đơn trả hàng ở trạng thái chờ duyệt');
        }

        $branchShops = BranchShop::active()->get();
        $customers = Customer::select('id', 'name', 'phone', 'email')->get();

        return view('admin.returns.edit', compact('returnOrder', 'branchShops', 'customers'));
    }

    /**
     * Update the specified return order.
     */
    public function update(Request $request, $id)
    {
        try {
            $returnOrder = ReturnOrder::findOrFail($id);

            // Only allow updating if status is pending
            if ($returnOrder->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Chỉ có thể chỉnh sửa đơn trả hàng ở trạng thái chờ duyệt'
                ], 400);
            }

            $validatedData = $request->validate([
                'customer_id' => 'nullable|exists:customers,id',
                'branch_shop_id' => 'nullable|exists:branch_shops,id',
                'return_date' => 'required|date',
                'reason' => 'required|in:defective,wrong_item,customer_request,damaged,expired,other',
                'reason_detail' => 'nullable|string|max:1000',
                'refund_method' => 'required|in:cash,card,transfer,store_credit,exchange,points',
                'notes' => 'nullable|string|max:1000',
                'items' => 'required|array|min:1',
                'items.*.id' => 'nullable|exists:return_order_items,id',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity_returned' => 'required|integer|min:1',
                'items.*.unit_price' => 'required|numeric|min:0',
                'items.*.condition' => 'nullable|string|max:255',
                'items.*.notes' => 'nullable|string|max:500',
            ]);

            DB::beginTransaction();

            // Calculate totals
            $subtotal = 0;
            foreach ($validatedData['items'] as $item) {
                $subtotal += $item['quantity_returned'] * $item['unit_price'];
            }

            $taxRate = $returnOrder->tax_rate ?? 0;
            $taxAmount = $subtotal * ($taxRate / 100);
            $totalAmount = $subtotal + $taxAmount;

            // Update return order
            $returnOrder->update([
                'customer_id' => $validatedData['customer_id'] ?: 0,
                'branch_shop_id' => $validatedData['branch_shop_id'],
                'return_date' => $validatedData['return_date'],
                'reason' => $validatedData['reason'],
                'reason_detail' => $validatedData['reason_detail'],
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'refund_method' => $validatedData['refund_method'],
                'notes' => $validatedData['notes'],
                'updated_by' => Auth::id(),
            ]);

            // Delete existing items and create new ones
            $returnOrder->returnOrderItems()->delete();

            foreach ($validatedData['items'] as $index => $item) {
                ReturnOrderItem::create([
                    'return_order_id' => $returnOrder->id,
                    'product_id' => $item['product_id'],
                    'quantity_returned' => $item['quantity_returned'],
                    'unit_price' => $item['unit_price'],
                    'line_total' => $item['quantity_returned'] * $item['unit_price'],
                    'condition' => $item['condition'],
                    'notes' => $item['notes'],
                    'sort_order' => $index + 1,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Đơn trả hàng đã được cập nhật thành công'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating return order: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'return_id' => $id,
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi cập nhật đơn trả hàng: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified return order.
     */
    public function destroy($id)
    {
        try {
            $returnOrder = ReturnOrder::findOrFail($id);

            // Only allow deleting if status is pending
            if ($returnOrder->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Chỉ có thể xóa đơn trả hàng ở trạng thái chờ duyệt'
                ], 400);
            }

            DB::beginTransaction();

            // Delete return order items first
            $returnOrder->returnOrderItems()->delete();

            // Delete return order
            $returnOrder->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Đơn trả hàng đã được xóa thành công'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting return order: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'return_id' => $id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xóa đơn trả hàng: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get detail panel for return order (AJAX).
     */
    public function getDetailPanel($id)
    {
        try {
            $returnOrder = ReturnOrder::with([
                'customer',
                'branchShop',
                'invoice',
                'creator',
                'approver',
                'returnOrderItems.product'
            ])->findOrFail($id);

            return view('admin.returns.partials.detail-panel', compact('returnOrder'))->render();

        } catch (\Exception $e) {
            Log::error('Error getting return detail panel: ' . $e->getMessage(), [
                'return_id' => $id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tải thông tin chi tiết'
            ], 500);
        }
    }

    /**
     * Get payment history for return order (AJAX).
     */
    public function getPaymentHistory($id)
    {
        try {
            $returnOrder = ReturnOrder::findOrFail($id);

            $payments = Payment::where('reference_type', 'return_order')
                ->where('reference_id', $id)
                ->with(['bankAccount', 'creator'])
                ->orderBy('created_at', 'desc')
                ->get();

            return view('admin.returns.partials.payment-history', compact('returnOrder', 'payments'))->render();

        } catch (\Exception $e) {
            Log::error('Error getting return payment history: ' . $e->getMessage(), [
                'return_id' => $id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tải lịch sử thanh toán'
            ], 500);
        }
    }

    /**
     * Record payment for return order.
     */
    public function recordPayment(Request $request, $id)
    {
        try {
            $returnOrder = ReturnOrder::findOrFail($id);

            // Only allow payment for approved returns
            if ($returnOrder->status !== 'approved') {
                return response()->json([
                    'success' => false,
                    'message' => 'Chỉ có thể ghi nhận thanh toán cho đơn trả hàng đã được duyệt'
                ], 400);
            }

            $validatedData = $request->validate([
                'amount' => 'required|numeric|min:0',
                'payment_method' => 'required|in:cash,card,transfer,store_credit,exchange,points',
                'bank_account_id' => 'nullable|exists:bank_accounts,id',
                'reference_number' => 'nullable|string|max:255',
                'notes' => 'nullable|string|max:1000',
            ]);

            DB::beginTransaction();

            // Create payment record
            $payment = Payment::create([
                'reference_type' => 'return_order',
                'reference_id' => $returnOrder->id,
                'payment_type' => 'refund',
                'amount' => $validatedData['amount'],
                'payment_method' => $validatedData['payment_method'],
                'bank_account_id' => $validatedData['bank_account_id'],
                'reference_number' => $validatedData['reference_number'],
                'notes' => $validatedData['notes'],
                'status' => 'completed',
                'payment_date' => Carbon::now(),
                'created_by' => Auth::id(),
            ]);

            // Update return order status to completed if fully refunded
            $totalRefunded = Payment::where('reference_type', 'return_order')
                ->where('reference_id', $returnOrder->id)
                ->where('status', 'completed')
                ->sum('amount');

            if ($totalRefunded >= $returnOrder->total_amount) {
                $returnOrder->update(['status' => 'completed']);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Thanh toán đã được ghi nhận thành công',
                'payment_id' => $payment->id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error recording return payment: ' . $e->getMessage(), [
                'return_id' => $id,
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi ghi nhận thanh toán: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve return order.
     */
    public function sendReturn(Request $request, $id)
    {
        try {
            $returnOrder = ReturnOrder::findOrFail($id);

            // Only allow approving if status is pending
            if ($returnOrder->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Chỉ có thể duyệt đơn trả hàng ở trạng thái chờ duyệt'
                ], 400);
            }

            $returnOrder->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => Carbon::now(),
                'updated_by' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Đơn trả hàng đã được duyệt thành công'
            ]);

        } catch (\Exception $e) {
            Log::error('Error approving return order: ' . $e->getMessage(), [
                'return_id' => $id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi duyệt đơn trả hàng: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel return order.
     */
    public function cancelReturn(Request $request, $id)
    {
        try {
            $returnOrder = ReturnOrder::findOrFail($id);

            // Only allow canceling if status is pending or approved
            if (!in_array($returnOrder->status, ['pending', 'approved'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể hủy đơn trả hàng ở trạng thái này'
                ], 400);
            }

            $returnOrder->update([
                'status' => 'rejected',
                'updated_by' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Đơn trả hàng đã được hủy thành công'
            ]);

        } catch (\Exception $e) {
            Log::error('Error canceling return order: ' . $e->getMessage(), [
                'return_id' => $id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi hủy đơn trả hàng: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Print return order.
     */
    public function print($id)
    {
        try {
            $returnOrder = ReturnOrder::with([
                'customer',
                'branchShop',
                'invoice',
                'creator',
                'approver',
                'returnOrderItems.product'
            ])->findOrFail($id);

            return view('admin.returns.print', compact('returnOrder'));

        } catch (\Exception $e) {
            Log::error('Error printing return order: ' . $e->getMessage(), [
                'return_id' => $id
            ]);

            return redirect()->back()->with('error', 'Có lỗi xảy ra khi in đơn trả hàng');
        }
    }

    /**
     * Get statistics for returns.
     */
    public function getStatistics(Request $request)
    {
        try {
            $dateFrom = $request->get('date_from', Carbon::now()->startOfMonth());
            $dateTo = $request->get('date_to', Carbon::now()->endOfMonth());

            $stats = [
                'total_returns' => ReturnOrder::whereBetween('return_date', [$dateFrom, $dateTo])->count(),
                'total_amount' => ReturnOrder::whereBetween('return_date', [$dateFrom, $dateTo])->sum('total_amount'),
                'pending_returns' => ReturnOrder::where('status', 'pending')->whereBetween('return_date', [$dateFrom, $dateTo])->count(),
                'approved_returns' => ReturnOrder::where('status', 'approved')->whereBetween('return_date', [$dateFrom, $dateTo])->count(),
                'completed_returns' => ReturnOrder::where('status', 'completed')->whereBetween('return_date', [$dateFrom, $dateTo])->count(),
                'rejected_returns' => ReturnOrder::where('status', 'rejected')->whereBetween('return_date', [$dateFrom, $dateTo])->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting return statistics: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tải thống kê'
            ], 500);
        }
    }

    /**
     * Export returns to Excel.
     */
    public function exportExcel(Request $request)
    {
        try {
            // This would typically use Laravel Excel package
            // For now, return a simple response
            return response()->json([
                'success' => true,
                'message' => 'Xuất Excel thành công',
                'download_url' => '#'
            ]);

        } catch (\Exception $e) {
            Log::error('Error exporting returns to Excel: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xuất Excel'
            ], 500);
        }
    }

    /**
     * Export return to PDF.
     */
    public function exportPdf($id)
    {
        try {
            $returnOrder = ReturnOrder::with([
                'customer',
                'branchShop',
                'invoice',
                'creator',
                'approver',
                'returnOrderItems.product'
            ])->findOrFail($id);

            // This would typically use a PDF library like DomPDF
            // For now, return the print view
            return view('admin.returns.pdf', compact('returnOrder'));

        } catch (\Exception $e) {
            Log::error('Error exporting return to PDF: ' . $e->getMessage(), [
                'return_id' => $id
            ]);

            return redirect()->back()->with('error', 'Có lỗi xảy ra khi xuất PDF');
        }
    }

    /**
     * Get filter users for dropdown.
     */
    public function getFilterUsers(Request $request)
    {
        try {
            $search = $request->get('search', '');

            $users = User::select('id', 'full_name')
                ->whereHas('roles', function($q) {
                    $q->whereIn('name', ['shop_manager', 'staff', 'admin']);
                })
                ->when($search, function($q) use ($search) {
                    $q->where('full_name', 'like', "%{$search}%");
                })
                ->orderBy('full_name')
                ->limit(20)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $users
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting filter users: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tải danh sách người dùng'
            ], 500);
        }
    }

    /**
     * Bulk update status for multiple returns.
     */
    public function bulkUpdateStatus(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'return_ids' => 'required|array|min:1',
                'return_ids.*' => 'exists:return_orders,id',
                'status' => 'required|in:pending,approved,rejected,completed'
            ]);

            $updatedCount = 0;
            $errors = [];

            foreach ($validatedData['return_ids'] as $returnId) {
                try {
                    $returnOrder = ReturnOrder::findOrFail($returnId);

                    // Check if status change is allowed
                    if ($this->canUpdateStatus($returnOrder->status, $validatedData['status'])) {
                        $updateData = [
                            'status' => $validatedData['status'],
                            'updated_by' => Auth::id(),
                        ];

                        // Add approval data if approving
                        if ($validatedData['status'] === 'approved') {
                            $updateData['approved_by'] = Auth::id();
                            $updateData['approved_at'] = Carbon::now();
                        }

                        $returnOrder->update($updateData);
                        $updatedCount++;
                    } else {
                        $errors[] = "Đơn trả hàng {$returnOrder->return_number} không thể chuyển sang trạng thái này";
                    }
                } catch (\Exception $e) {
                    $errors[] = "Lỗi cập nhật đơn trả hàng ID {$returnId}: " . $e->getMessage();
                }
            }

            $message = "Đã cập nhật {$updatedCount} đơn trả hàng thành công";
            if (!empty($errors)) {
                $message .= ". Có " . count($errors) . " lỗi xảy ra.";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'updated_count' => $updatedCount,
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            Log::error('Error in bulk update status: ' . $e->getMessage(), [
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi cập nhật hàng loạt: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk cancel multiple returns.
     */
    public function bulkCancel(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'return_ids' => 'required|array|min:1',
                'return_ids.*' => 'exists:return_orders,id'
            ]);

            $canceledCount = 0;
            $errors = [];

            foreach ($validatedData['return_ids'] as $returnId) {
                try {
                    $returnOrder = ReturnOrder::findOrFail($returnId);

                    // Only allow canceling if status is pending or approved
                    if (in_array($returnOrder->status, ['pending', 'approved'])) {
                        $returnOrder->update([
                            'status' => 'rejected',
                            'updated_by' => Auth::id(),
                        ]);
                        $canceledCount++;
                    } else {
                        $errors[] = "Đơn trả hàng {$returnOrder->return_number} không thể hủy ở trạng thái hiện tại";
                    }
                } catch (\Exception $e) {
                    $errors[] = "Lỗi hủy đơn trả hàng ID {$returnId}: " . $e->getMessage();
                }
            }

            $message = "Đã hủy {$canceledCount} đơn trả hàng thành công";
            if (!empty($errors)) {
                $message .= ". Có " . count($errors) . " lỗi xảy ra.";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'canceled_count' => $canceledCount,
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            Log::error('Error in bulk cancel: ' . $e->getMessage(), [
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi hủy hàng loạt: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk delete multiple returns.
     */
    public function bulkDelete(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'return_ids' => 'required|array|min:1',
                'return_ids.*' => 'exists:return_orders,id'
            ]);

            $deletedCount = 0;
            $errors = [];

            DB::beginTransaction();

            foreach ($validatedData['return_ids'] as $returnId) {
                try {
                    $returnOrder = ReturnOrder::findOrFail($returnId);

                    // Only allow deleting if status is pending
                    if ($returnOrder->status === 'pending') {
                        // Delete return order items first
                        $returnOrder->returnOrderItems()->delete();

                        // Delete return order
                        $returnOrder->delete();
                        $deletedCount++;
                    } else {
                        $errors[] = "Đơn trả hàng {$returnOrder->return_number} chỉ có thể xóa ở trạng thái chờ duyệt";
                    }
                } catch (\Exception $e) {
                    $errors[] = "Lỗi xóa đơn trả hàng ID {$returnId}: " . $e->getMessage();
                }
            }

            DB::commit();

            $message = "Đã xóa {$deletedCount} đơn trả hàng thành công";
            if (!empty($errors)) {
                $message .= ". Có " . count($errors) . " lỗi xảy ra.";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'deleted_count' => $deletedCount,
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in bulk delete: ' . $e->getMessage(), [
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xóa hàng loạt: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create return from invoice.
     */
    public function createFromInvoice(Request $request, $invoiceId)
    {
        try {
            $invoice = Invoice::with(['invoiceItems.product', 'customer', 'branchShop'])
                ->findOrFail($invoiceId);

            $validatedData = $request->validate([
                'reason' => 'required|in:defective,wrong_item,customer_request,damaged,expired,other',
                'reason_detail' => 'nullable|string|max:1000',
                'refund_method' => 'required|in:cash,card,transfer,store_credit,exchange,points',
                'notes' => 'nullable|string|max:1000',
                'items' => 'required|array|min:1',
                'items.*.invoice_item_id' => 'required|exists:invoice_items,id',
                'items.*.quantity_returned' => 'required|integer|min:1',
                'items.*.condition' => 'nullable|string|max:255',
                'items.*.notes' => 'nullable|string|max:500',
            ]);

            DB::beginTransaction();

            // Generate return number
            $returnNumber = $this->generateReturnNumber();

            // Calculate totals
            $subtotal = 0;
            foreach ($validatedData['items'] as $item) {
                $invoiceItem = $invoice->invoiceItems->firstWhere('id', $item['invoice_item_id']);
                if ($invoiceItem) {
                    $subtotal += $item['quantity_returned'] * $invoiceItem->unit_price;
                }
            }

            $taxRate = 0;
            $taxAmount = $subtotal * ($taxRate / 100);
            $totalAmount = $subtotal + $taxAmount;

            // Create return order
            $returnOrder = ReturnOrder::create([
                'return_number' => $returnNumber,
                'invoice_id' => $invoice->id,
                'customer_id' => $invoice->customer_id ?: 0,
                'branch_shop_id' => $invoice->branch_shop_id,
                'return_date' => Carbon::now()->toDateString(),
                'reason' => $validatedData['reason'],
                'reason_detail' => $validatedData['reason_detail'],
                'status' => 'pending',
                'subtotal' => $subtotal,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'refund_method' => $validatedData['refund_method'],
                'notes' => $validatedData['notes'],
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);

            // Create return order items
            foreach ($validatedData['items'] as $index => $item) {
                $invoiceItem = $invoice->invoiceItems->firstWhere('id', $item['invoice_item_id']);
                if ($invoiceItem) {
                    ReturnOrderItem::create([
                        'return_order_id' => $returnOrder->id,
                        'invoice_item_id' => $invoiceItem->id,
                        'product_id' => $invoiceItem->product_id,
                        'product_name' => $invoiceItem->product_name,
                        'product_sku' => $invoiceItem->product_sku,
                        'quantity_returned' => $item['quantity_returned'],
                        'unit_price' => $invoiceItem->unit_price,
                        'line_total' => $item['quantity_returned'] * $invoiceItem->unit_price,
                        'condition' => $item['condition'],
                        'notes' => $item['notes'],
                        'sort_order' => $index + 1,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Đơn trả hàng từ hóa đơn đã được tạo thành công',
                'return_id' => $returnOrder->id,
                'return_number' => $returnOrder->return_number
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating return from invoice: ' . $e->getMessage(), [
                'invoice_id' => $invoiceId,
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tạo đơn trả hàng từ hóa đơn: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if status change is allowed.
     */
    private function canUpdateStatus($currentStatus, $newStatus)
    {
        $allowedTransitions = [
            'pending' => ['approved', 'rejected'],
            'approved' => ['completed', 'rejected'],
            'rejected' => [], // Cannot change from rejected
            'completed' => [], // Cannot change from completed
        ];

        return in_array($newStatus, $allowedTransitions[$currentStatus] ?? []);
    }
}
