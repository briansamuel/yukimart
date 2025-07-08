<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\QuickOrderService;
use App\Services\InvoiceService;
use App\Models\Customer;
use App\Models\BranchShop;
use App\Models\BankAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class QuickInvoiceController extends Controller
{
    protected $quickOrderService;
    protected $invoiceService;

    public function __construct(QuickOrderService $quickOrderService, InvoiceService $invoiceService)
    {
        $this->quickOrderService = $quickOrderService;
        $this->invoiceService = $invoiceService;
    }

    /**
     * Process quick invoice submission
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            // Validate request
            $validator = Validator::make($request->all(), [
                'customer_id' => 'nullable|integer',
                'branch_shop_id' => 'required|exists:branch_shops,id',
                'sold_by' => 'required|exists:users,id',
                'channel' => 'required|string',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.price' => 'required|numeric|min:0',
                'payment_method' => 'nullable|string',
                'discount_amount' => 'nullable|numeric|min:0',
                'other_amount' => 'nullable|numeric|min:0',
                'amount_paid' => 'nullable|numeric|min:0',
                'notes' => 'nullable|string|max:1000',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dữ liệu không hợp lệ. Vui lòng kiểm tra lại.',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Additional validation for customer_id
            if (!empty($request->customer_id) && $request->customer_id != 0) {
                $customer = \App\Models\Customer::find($request->customer_id);
                if (!$customer) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Khách hàng không tồn tại.'
                    ], 422);
                }
            }

            // Process quick order data
            $invoiceData = $this->quickOrderService->processQuickOrder($request->all());

            // Override status for quick invoice - always set to paid
            $invoiceData['status'] = 'paid';
            $invoiceData['payment_status'] = 'paid';

            // Create invoice using InvoiceService
            $invoiceResult = $this->invoiceService->createInvoice($invoiceData);

            if (!$invoiceResult['success']) {
                // Log detailed error for debugging
                Log::error('Invoice creation failed in service', [
                    'user_id' => Auth::id(),
                    'error_message' => $invoiceResult['message'] ?? 'Unknown error',
                    'request_data' => $request->all(),
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể tạo hóa đơn. Vui lòng thử lại sau.'
                ], 500);
            }

            $invoice = $invoiceResult['data'];

            return response()->json([
                'success' => true,
                'message' => 'Hóa đơn đã được tạo thành công!',
                'data' => [
                    'invoice' => $invoice,
                    'redirect_url' => route('admin.invoice.show', $invoice->id)
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Quick invoice creation failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
            ]);

            // Return user-friendly message without exposing technical details
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tạo hóa đơn. Vui lòng thử lại sau.',
                'data' => null
            ], 500);
        }
    }
}
