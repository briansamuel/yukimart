<?php

namespace App\Http\Controllers\Admin\CMS;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Invoice;
use App\Models\ReturnOrder;
use App\Models\User;
use App\Models\Customer;
use App\Models\BranchShop;
use App\Models\BankAccount;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Display a listing of payments.
     */
    public function index()
    {
        // Get filter data
        $creators = User::select('id', 'full_name as name')->get();
        $staff = User::whereHas('branchShops')->select('id', 'full_name as name')->get(); // Staff with branch assignments
        $customers = Customer::select('id', 'name')->get();
        $branchShops = BranchShop::select('id', 'name')->get();
        $bankAccounts = BankAccount::select('id', 'bank_name', 'account_number')->get();

        return view('admin.payment.index', compact(
            'creators',
            'staff',
            'customers',
            'branchShops',
            'bankAccounts'
        ));
    }

    /**
     * Get payments data for AJAX.
     */
    public function getPaymentsAjax(Request $request)
    {
        $query = Payment::with(['customer', 'branchShop', 'creator', 'collector', 'bankAccount']);

        // Apply time filter first (this will override date_from/date_to if present)
        if ($request->filled('time_filter')) {
            $timeFilter = $request->time_filter;
            $now = now();

            // Debug log
            Log::info('PaymentController getPaymentsAjax - Time filter applied', [
                'filter' => $timeFilter,
                'current_time' => $now->toDateTimeString()
            ]);

            switch ($timeFilter) {
                case 'today':
                    $dateFrom = $now->copy()->startOfDay();
                    $dateTo = $now->copy()->endOfDay();
                    break;
                case 'yesterday':
                    $dateFrom = $now->copy()->subDay()->startOfDay();
                    $dateTo = $now->copy()->subDay()->endOfDay();
                    break;
                case 'this_week':
                    $dateFrom = $now->copy()->startOfWeek();
                    $dateTo = $now->copy()->endOfWeek();
                    break;
                case 'last_week':
                    $dateFrom = $now->copy()->subWeek()->startOfWeek();
                    $dateTo = $now->copy()->subWeek()->endOfWeek();
                    break;
                case '7_days':
                case 'last_7_days':
                    $dateFrom = $now->copy()->subDays(6)->startOfDay();
                    $dateTo = $now->copy()->endOfDay();
                    break;
                case 'this_month':
                    $dateFrom = $now->copy()->startOfMonth();
                    $dateTo = $now->copy()->endOfMonth();
                    break;
                case 'last_month':
                    $dateFrom = $now->copy()->subMonth()->startOfMonth();
                    $dateTo = $now->copy()->subMonth()->endOfMonth();
                    break;
                case '30_days':
                case 'last_30_days':
                    $dateFrom = $now->copy()->subDays(29)->startOfDay();
                    $dateTo = $now->copy()->endOfDay();
                    break;
                case 'this_quarter':
                    $dateFrom = $now->copy()->startOfQuarter();
                    $dateTo = $now->copy()->endOfQuarter();
                    break;
                case 'last_quarter':
                    $dateFrom = $now->copy()->subQuarter()->startOfQuarter();
                    $dateTo = $now->copy()->subQuarter()->endOfQuarter();
                    break;
                case 'this_year':
                    $dateFrom = $now->copy()->startOfYear();
                    $dateTo = $now->copy()->endOfYear();
                    break;
                case 'last_year':
                    $dateFrom = $now->copy()->subYear()->startOfYear();
                    $dateTo = $now->copy()->subYear()->endOfYear();
                    break;
                case 'custom':
                    // For custom, use the provided date_from and date_to
                    $dateFrom = $request->filled('date_from') ? \Carbon\Carbon::parse($request->date_from)->startOfDay() : null;
                    $dateTo = $request->filled('date_to') ? \Carbon\Carbon::parse($request->date_to)->endOfDay() : null;
                    break;
                default:
                    $dateFrom = null;
                    $dateTo = null;
                    break;
            }

            // Apply the calculated date range
            if ($dateFrom) {
                $query->where('payment_date', '>=', $dateFrom);
            }
            if ($dateTo) {
                $query->where('payment_date', '<=', $dateTo);
            }

            // Debug log for calculated date range
            Log::info('PaymentController getPaymentsAjax - Date range calculated', [
                'time_filter' => $timeFilter,
                'date_from' => $dateFrom ? $dateFrom->format('Y-m-d H:i:s') : null,
                'date_to' => $dateTo ? $dateTo->format('Y-m-d H:i:s') : null
            ]);
        } else {
            // Fallback to manual date filters if no time_filter is provided
            if ($request->filled('date_from')) {
                $query->whereDate('payment_date', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('payment_date', '<=', $request->date_to);
            }
        }

        // Apply other filters
        if ($request->filled('payment_type')) {
            $query->where('payment_type', $request->payment_type);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('branch_shop_id')) {
            $query->where('branch_shop_id', $request->branch_shop_id);
        }

        if ($request->filled('creator_id')) {
            $query->where('created_by', $request->creator_id);
        }

        if ($request->filled('staff_id')) {
            $query->where('collector_id', $request->staff_id);
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('payment_number', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($customerQuery) use ($search) {
                      $customerQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Pagination
        $perPage = $request->get('per_page', 10);
        $payments = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $payments->items(),
            'pagination' => [
                'current_page' => $payments->currentPage(),
                'last_page' => $payments->lastPage(),
                'total_pages' => $payments->lastPage(), // Add for compatibility
                'per_page' => $payments->perPage(),
                'total' => $payments->total(),
                'total_items' => $payments->total(), // Add for compatibility
                'from' => $payments->firstItem(),
                'to' => $payments->lastItem(),
            ]
        ]);
    }

    /**
     * Show the form for creating a new payment.
     */
    public function create(Request $request)
    {
        $referenceType = $request->get('reference_type');
        $referenceId = $request->get('reference_id');
        $reference = null;
        
        if ($referenceType && $referenceId) {
            switch ($referenceType) {
                case 'invoice':
                    $reference = Invoice::with('customer')->find($referenceId);
                    break;
                case 'return_order':
                    $reference = ReturnOrder::with('customer')->find($referenceId);
                    break;
            }
        }
        
        return view('admin.payment.create', compact('reference', 'referenceType'));
    }

    /**
     * Store a newly created payment.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'payment_type' => 'required|in:receipt,payment',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,card,transfer,check,points,other',
            'payment_date' => 'required|date',
            'reference_type' => 'nullable|in:invoice,return_order,order,manual',
            'reference_id' => 'nullable|integer',
            'customer_id' => 'nullable|exists:customers,id',
            'branch_shop_id' => 'nullable|exists:branch_shops,id',
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        $result = $this->paymentService->createPayment($request->all());

        return response()->json($result, $result['success'] ? 201 : 400);
    }

    /**
     * Display the specified payment.
     */
    public function show(Payment $payment)
    {
        $payment->load([
            'customer', 
            'branchShop', 
            'creator', 
            'approver',
            'reference'
        ]);
        
        return view('admin.payment.show', compact('payment'));
    }

    /**
     * Show the form for editing the specified payment.
     */
    public function edit(Payment $payment)
    {
        if ($payment->status !== 'pending') {
            return redirect()->route('payment.show', $payment)
                           ->with('error', 'Chỉ có thể sửa phiếu thu/chi ở trạng thái chờ xử lý');
        }
        
        $payment->load(['customer', 'branchShop', 'reference']);
        
        return view('admin.payment.edit', compact('payment'));
    }

    /**
     * Update the specified payment.
     */
    public function update(Request $request, Payment $payment)
    {
        if ($payment->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Chỉ có thể sửa phiếu thu/chi ở trạng thái chờ xử lý'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,card,transfer,check,points,other',
            'payment_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        $result = $this->paymentService->updatePayment($payment, $request->all());

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Approve payment.
     */
    public function approve(Request $request, Payment $payment)
    {
        if ($payment->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Chỉ có thể duyệt phiếu thu/chi ở trạng thái chờ xử lý'
            ], 400);
        }

        $result = $this->paymentService->approvePayment($payment, $request->all());

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Cancel payment.
     */
    public function cancel(Request $request, Payment $payment)
    {
        if ($payment->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Chỉ có thể hủy phiếu thu/chi ở trạng thái chờ xử lý'
            ], 400);
        }

        $result = $this->paymentService->cancelPayment($payment, $request->reason);

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Create payment for invoice.
     */
    public function createInvoicePayment(Request $request, Invoice $invoice)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,card,transfer,check,points,other',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        $result = $this->paymentService->createInvoicePayment($invoice, $request->all());

        return response()->json($result, $result['success'] ? 201 : 400);
    }

    /**
     * Get payment statistics.
     */
    public function getStatistics(Request $request)
    {
        $stats = $this->paymentService->getPaymentStatistics(
            $request->branch_shop_id,
            $request->date_from,
            $request->date_to
        );

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Get payment summary for dashboard cards.
     */
    public function getSummary(Request $request)
    {
        try {
            // Determine date range based on time filter
            $dateFrom = null;
            $dateTo = null;

            if ($request->filled('time_filter')) {
                $timeFilter = $request->time_filter;
                $now = now();

                switch ($timeFilter) {
                    case 'today':
                        $dateFrom = $now->copy()->startOfDay();
                        $dateTo = $now->copy()->endOfDay();
                        break;
                    case 'yesterday':
                        $dateFrom = $now->copy()->subDay()->startOfDay();
                        $dateTo = $now->copy()->subDay()->endOfDay();
                        break;
                    case 'this_week':
                        $dateFrom = $now->copy()->startOfWeek();
                        $dateTo = $now->copy()->endOfWeek();
                        break;
                    case 'last_week':
                        $dateFrom = $now->copy()->subWeek()->startOfWeek();
                        $dateTo = $now->copy()->subWeek()->endOfWeek();
                        break;
                    case '7_days':
                    case 'last_7_days':
                        $dateFrom = $now->copy()->subDays(6)->startOfDay();
                        $dateTo = $now->copy()->endOfDay();
                        break;
                    case 'this_month':
                        $dateFrom = $now->copy()->startOfMonth();
                        $dateTo = $now->copy()->endOfMonth();
                        break;
                    case 'last_month':
                        $dateFrom = $now->copy()->subMonth()->startOfMonth();
                        $dateTo = $now->copy()->subMonth()->endOfMonth();
                        break;
                    case '30_days':
                    case 'last_30_days':
                        $dateFrom = $now->copy()->subDays(29)->startOfDay();
                        $dateTo = $now->copy()->endOfDay();
                        break;
                    case 'this_quarter':
                        $dateFrom = $now->copy()->startOfQuarter();
                        $dateTo = $now->copy()->endOfQuarter();
                        break;
                    case 'last_quarter':
                        $dateFrom = $now->copy()->subQuarter()->startOfQuarter();
                        $dateTo = $now->copy()->subQuarter()->endOfQuarter();
                        break;
                    case 'this_year':
                        $dateFrom = $now->copy()->startOfYear();
                        $dateTo = $now->copy()->endOfYear();
                        break;
                    case 'last_year':
                        $dateFrom = $now->copy()->subYear()->startOfYear();
                        $dateTo = $now->copy()->subYear()->endOfYear();
                        break;
                    case 'custom':
                        if ($request->filled('date_from')) {
                            $dateFrom = \Carbon\Carbon::parse($request->date_from)->startOfDay();
                        }
                        if ($request->filled('date_to')) {
                            $dateTo = \Carbon\Carbon::parse($request->date_to)->endOfDay();
                        }
                        break;
                }
            }

            // Build base query for current period (with date filter)
            $currentPeriodQuery = Payment::query();

            // Apply non-date filters to both current period and opening balance
            $baseFilters = [];
            if ($request->filled('payment_method')) {
                $baseFilters['payment_method'] = $request->payment_method;
            }
            if ($request->filled('status')) {
                $baseFilters['status'] = $request->status;
            }
            if ($request->filled('branch_shop_id')) {
                $baseFilters['branch_shop_id'] = $request->branch_shop_id;
            }
            if ($request->filled('creator_id')) {
                $baseFilters['created_by'] = $request->creator_id;
            }
            if ($request->filled('staff_id')) {
                $baseFilters['collector_id'] = $request->staff_id;
            }

            // Apply base filters to current period query
            foreach ($baseFilters as $field => $value) {
                $currentPeriodQuery->where($field, $value);
            }

            // Apply date range to current period
            if ($dateFrom) {
                $currentPeriodQuery->whereDate('payment_date', '>=', $dateFrom->format('Y-m-d'));
            }
            if ($dateTo) {
                $currentPeriodQuery->whereDate('payment_date', '<=', $dateTo->format('Y-m-d'));
            }

            // Calculate current period income and expense
            $totalIncome = (clone $currentPeriodQuery)->where('payment_type', 'receipt')->sum('amount') ?: 0;
            $totalExpense = (clone $currentPeriodQuery)->where('payment_type', 'payment')->sum('amount') ?: 0;

            // Calculate opening balance (before date_from)
            $openingBalance = 0;
            if ($dateFrom) {
                $openingQuery = Payment::query();

                // Apply same base filters to opening balance calculation
                foreach ($baseFilters as $field => $value) {
                    $openingQuery->where($field, $value);
                }

                // Only payments before the start date
                $openingQuery->whereDate('payment_date', '<', $dateFrom->format('Y-m-d'));

                $openingIncome = (clone $openingQuery)->where('payment_type', 'receipt')->sum('amount') ?: 0;
                $openingExpense = (clone $openingQuery)->where('payment_type', 'payment')->sum('amount') ?: 0;
                $openingBalance = $openingIncome - $openingExpense;
            }

            // Tồn quỹ = Quỹ đầu kỳ + Tổng Thu - Tổng Chi
            $closingBalance = $openingBalance + $totalIncome - $totalExpense;

            return response()->json([
                'success' => true,
                'data' => [
                    'opening_balance' => $openingBalance,
                    'total_income' => $totalIncome,
                    'total_expense' => $totalExpense,
                    'closing_balance' => $closingBalance,
                    'net_change' => $totalIncome - $totalExpense,
                    'debug' => [
                        'date_from' => $dateFrom ? $dateFrom->format('Y-m-d') : null,
                        'date_to' => $dateTo ? $dateTo->format('Y-m-d') : null,
                        'time_filter' => $request->time_filter
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get payment details for API.
     */
    public function getDetails(Payment $payment)
    {
        $payment->load([
            'customer',
            'branchShop',
            'creator',
            'collector',
            'approver',
            'reference',
            'bankAccount'
        ]);

        // Prepare detailed data for frontend
        $data = [
            'id' => $payment->id,
            'payment_number' => $payment->payment_number,
            'payment_type' => $payment->payment_type,
            'reference_type' => $payment->reference_type,
            'reference_id' => $payment->reference_id,
            'status' => $payment->status,
            'accounting_status' => 'not_accounted', // TODO: Add this field to database
            'creator' => $payment->creator ? $payment->creator->full_name : 'N/A',
            'collector' => $payment->collector ? $payment->collector->full_name : ($payment->creator ? $payment->creator->full_name : 'N/A'),
            'payment_date' => $payment->payment_date->format('Y-m-d'),
            'payment_time' => $payment->payment_date->format('H:i'),
            'branch' => $payment->branchShop ? $payment->branchShop->name : 'N/A',
            'amount' => $payment->amount,
            'payment_method' => $payment->payment_method_display,
            'recipient' => $payment->customer ? $payment->customer->name : 'Khách lẻ',
            'description' => $payment->description,
            'notes' => $payment->notes,
            'payer_type' => $payment->customer ? 'Khách hàng' : 'Khách lẻ',
            'bank_account' => $payment->bankAccount ? [
                'bank_name' => $payment->bankAccount->bank_name,
                'account_number' => $payment->bankAccount->account_number,
                'account_holder' => $payment->bankAccount->account_holder,
            ] : null,
        ];

        // Add income type based on reference_type
        if ($payment->reference_type === 'invoice') {
            $data['income_type'] = 'Tiền khách trả';
        } elseif ($payment->reference_type === 'return_order') {
            $data['income_type'] = 'Tiền hoàn trả';
        } else {
            $data['income_type'] = 'Thu khác';
        }

        // Add related reference data
        if ($payment->reference) {
            switch ($payment->reference_type) {
                case 'invoice':
                    $data['related_invoice'] = [
                        'code' => $payment->reference->invoice_number,
                        'datetime' => $payment->reference->created_at->format('d/m/Y H:i'),
                        'amount' => $payment->reference->total_amount,
                        'paid_before' => 0, // TODO: Calculate from other payments
                        'collected_amount' => $payment->amount,
                        'status' => $payment->reference->payment_status === 'paid' ? 'paid' : 'unpaid'
                    ];
                    $data['reference_code'] = $payment->reference->invoice_number;
                    break;
                case 'return_order':
                    $data['related_return_order'] = [
                        'code' => $payment->reference->return_order_number,
                        'datetime' => $payment->reference->created_at->format('d/m/Y H:i'),
                        'amount' => $payment->reference->total_amount,
                        'status' => $payment->reference->status
                    ];
                    $data['reference_code'] = $payment->reference->return_order_number;
                    break;
            }
        }

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Test print function.
     */
    public function testPrint($id)
    {
        try {
            $payment = Payment::findOrFail($id);

            return response()->json([
                'success' => true,
                'payment_id' => $payment->id,
                'payment_code' => $payment->payment_code,
                'amount' => $payment->amount,
                'message' => 'Payment found successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Print payment receipt.
     */
    public function print($id, Request $request)
    {
        try {
            $payment = Payment::with(['bankAccount', 'branchShop', 'creator', 'reference'])
                             ->findOrFail($id);

            // Get template from request parameter, default to 'standard'
            $template = $request->get('template', 'standard');

            $data = [
                'payment' => $payment,
                'template' => $template
            ];

            return view('admin.payment.print.standard', $data);
        } catch (\Exception $e) {
            Log::error('Payment print error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }
}
