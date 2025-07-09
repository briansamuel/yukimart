<?php

namespace App\Http\Controllers\Admin\CMS;

use App\Http\Controllers\Controller;
use App\Models\ReturnOrder;
use App\Models\Invoice;
use App\Services\ReturnOrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

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
     * Get return orders data for DataTables.
     */
    public function getData(Request $request)
    {
        $query = ReturnOrder::with(['customer', 'invoice', 'branchShop', 'creator'])
                           ->select('return_orders.*');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('reason')) {
            $query->where('reason', $request->reason);
        }

        if ($request->filled('branch_shop_id')) {
            $query->where('branch_shop_id', $request->branch_shop_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('return_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('return_date', '<=', $request->date_to);
        }

        return DataTables::of($query)
            ->addColumn('customer_name', function ($returnOrder) {
                return $returnOrder->customer ? $returnOrder->customer->name : 'Khách lẻ';
            })
            ->addColumn('invoice_number', function ($returnOrder) {
                return $returnOrder->invoice->invoice_number ?? 'N/A';
            })
            ->addColumn('branch_name', function ($returnOrder) {
                return $returnOrder->branchShop->name ?? 'N/A';
            })
            ->addColumn('creator_name', function ($returnOrder) {
                return $returnOrder->creator->name ?? 'N/A';
            })
            ->addColumn('formatted_total', function ($returnOrder) {
                return number_format($returnOrder->total_amount, 0, ',', '.') . '₫';
            })
            ->addColumn('status_badge', function ($returnOrder) {
                return $returnOrder->status_badge;
            })
            ->addColumn('reason_display', function ($returnOrder) {
                return $returnOrder->reason_display;
            })
            ->addColumn('actions', function ($returnOrder) {
                $actions = '<div class="btn-group" role="group">';
                $actions .= '<button type="button" class="btn btn-sm btn-info" onclick="viewReturnOrder(' . $returnOrder->id . ')" title="Xem chi tiết"><i class="fa fa-eye"></i></button>';
                
                if ($returnOrder->status === 'pending') {
                    $actions .= '<button type="button" class="btn btn-sm btn-warning" onclick="editReturnOrder(' . $returnOrder->id . ')" title="Sửa"><i class="fa fa-edit"></i></button>';
                    $actions .= '<button type="button" class="btn btn-sm btn-success" onclick="approveReturnOrder(' . $returnOrder->id . ')" title="Duyệt"><i class="fa fa-check"></i></button>';
                    $actions .= '<button type="button" class="btn btn-sm btn-danger" onclick="rejectReturnOrder(' . $returnOrder->id . ')" title="Từ chối"><i class="fa fa-times"></i></button>';
                }
                
                if ($returnOrder->status === 'approved') {
                    $actions .= '<button type="button" class="btn btn-sm btn-primary" onclick="completeReturnOrder(' . $returnOrder->id . ')" title="Hoàn thành"><i class="fa fa-check-circle"></i></button>';
                }
                
                $actions .= '</div>';
                return $actions;
            })
            ->rawColumns(['status_badge', 'actions'])
            ->make(true);
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
}
