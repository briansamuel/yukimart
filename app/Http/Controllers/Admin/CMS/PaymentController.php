<?php

namespace App\Http\Controllers\Admin\CMS;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Invoice;
use App\Models\ReturnOrder;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

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
        return view('admin.payment.index');
    }

    /**
     * Get payments data for DataTables.
     */
    public function getData(Request $request)
    {
        $query = Payment::with(['customer', 'branchShop', 'creator', 'reference'])
                       ->select('payments.*');

        // Apply filters
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

        if ($request->filled('date_from')) {
            $query->whereDate('payment_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('payment_date', '<=', $request->date_to);
        }

        return DataTables::of($query)
            ->addColumn('customer_name', function ($payment) {
                return $payment->customer ? $payment->customer->name : 'N/A';
            })
            ->addColumn('branch_name', function ($payment) {
                return $payment->branchShop->name ?? 'N/A';
            })
            ->addColumn('creator_name', function ($payment) {
                return $payment->creator->name ?? 'N/A';
            })
            ->addColumn('reference_display', function ($payment) {
                if ($payment->reference) {
                    switch ($payment->reference_type) {
                        case 'invoice':
                            return 'HĐ: ' . $payment->reference->invoice_number;
                        case 'return_order':
                            return 'TH: ' . $payment->reference->return_number;
                        case 'order':
                            return 'ĐH: ' . $payment->reference->order_code;
                        default:
                            return 'Thủ công';
                    }
                }
                return 'Thủ công';
            })
            ->addColumn('formatted_amount', function ($payment) {
                return number_format($payment->amount, 0, ',', '.') . '₫';
            })
            ->addColumn('formatted_actual_amount', function ($payment) {
                return number_format($payment->actual_amount ?? 0, 0, ',', '.') . '₫';
            })
            ->addColumn('payment_type_display', function ($payment) {
                return $payment->payment_type_display;
            })
            ->addColumn('payment_method_display', function ($payment) {
                return $payment->payment_method_display;
            })
            ->addColumn('status_badge', function ($payment) {
                return $payment->status_badge;
            })
            ->addColumn('actions', function ($payment) {
                $actions = '<div class="btn-group" role="group">';
                $actions .= '<button type="button" class="btn btn-sm btn-info" onclick="viewPayment(' . $payment->id . ')" title="Xem chi tiết"><i class="fa fa-eye"></i></button>';
                
                if ($payment->status === 'pending') {
                    $actions .= '<button type="button" class="btn btn-sm btn-warning" onclick="editPayment(' . $payment->id . ')" title="Sửa"><i class="fa fa-edit"></i></button>';
                    $actions .= '<button type="button" class="btn btn-sm btn-success" onclick="approvePayment(' . $payment->id . ')" title="Duyệt"><i class="fa fa-check"></i></button>';
                    $actions .= '<button type="button" class="btn btn-sm btn-danger" onclick="cancelPayment(' . $payment->id . ')" title="Hủy"><i class="fa fa-times"></i></button>';
                }
                
                $actions .= '</div>';
                return $actions;
            })
            ->rawColumns(['status_badge', 'actions'])
            ->make(true);
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
     * Get payment details for API.
     */
    public function getDetails(Payment $payment)
    {
        $payment->load([
            'customer', 
            'branchShop', 
            'creator', 
            'approver',
            'reference'
        ]);

        return response()->json([
            'success' => true,
            'data' => $payment
        ]);
    }
}
