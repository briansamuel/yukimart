<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Http\Resources\V1\PaymentResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    /**
     * Display a listing of payments with pagination and filters
     */
    public function index(Request $request)
    {
        try {
            $query = Payment::with(['bankAccount', 'creator']);
            
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
            
            if ($request->filled('reference_type')) {
                $query->where('reference_type', $request->reference_type);
            }
            
            if ($request->filled('reference_id')) {
                $query->where('reference_id', $request->reference_id);
            }
            
            if ($request->filled('bank_account_id')) {
                $query->where('bank_account_id', $request->bank_account_id);
            }
            
            if ($request->filled('date_from')) {
                $query->whereDate('payment_date', '>=', $request->date_from);
            }
            
            if ($request->filled('date_to')) {
                $query->whereDate('payment_date', '<=', $request->date_to);
            }
            
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('payment_code', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhere('reference_number', 'like', "%{$search}%");
                });
            }
            
            // Amount range filter
            if ($request->filled('min_amount')) {
                $query->where('amount', '>=', $request->min_amount);
            }
            
            if ($request->filled('max_amount')) {
                $query->where('amount', '<=', $request->max_amount);
            }
            
            // Sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);
            
            // Pagination
            $perPage = $request->get('per_page', 15);
            $payments = $query->paginate($perPage);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Payments retrieved successfully',
                'data' => PaymentResource::collection($payments),
                'meta' => [
                    'current_page' => $payments->currentPage(),
                    'last_page' => $payments->lastPage(),
                    'per_page' => $payments->perPage(),
                    'total' => $payments->total(),
                    'from' => $payments->firstItem(),
                    'to' => $payments->lastItem(),
                ]
            ], 200);
            
        } catch (\Exception $e) {
            Log::error('Payment listing failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve payments',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Display the specified payment
     */
    public function show($id)
    {
        try {
            $payment = Payment::with(['bankAccount', 'creator'])->findOrFail($id);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Payment retrieved successfully',
                'data' => new PaymentResource($payment)
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Payment not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }
    
    /**
     * Store a newly created payment
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'payment_type' => 'required|in:income,expense',
                'payment_method' => 'required|in:cash,card,transfer,check,other',
                'amount' => 'required|numeric|min:0.01',
                'payment_date' => 'required|date',
                'description' => 'required|string|max:500',
                'notes' => 'nullable|string|max:1000',
                'reference_type' => 'nullable|in:invoice,order,return_order,other',
                'reference_id' => 'nullable|integer',
                'reference_number' => 'nullable|string|max:100',
                'bank_account_id' => 'required|exists:bank_accounts,id',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            DB::beginTransaction();
            
            $paymentData = $request->all();
            $paymentData['payment_code'] = $this->generatePaymentCode($request->payment_type);
            $paymentData['status'] = 'completed';
            $paymentData['created_by'] = auth()->id();
            
            $payment = Payment::create($paymentData);
            
            DB::commit();
            
            // Load relationships for response
            $payment->load(['bankAccount', 'creator']);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Payment created successfully',
                'data' => new PaymentResource($payment)
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment creation failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Update the specified payment
     */
    public function update(Request $request, $id)
    {
        try {
            $payment = Payment::findOrFail($id);
            
            // Check if payment can be updated
            if ($payment->status === 'cancelled') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cannot update cancelled payment'
                ], 422);
            }
            
            $validator = Validator::make($request->all(), [
                'payment_type' => 'sometimes|in:income,expense',
                'payment_method' => 'sometimes|in:cash,card,transfer,check,other',
                'amount' => 'sometimes|numeric|min:0.01',
                'payment_date' => 'sometimes|date',
                'description' => 'sometimes|string|max:500',
                'notes' => 'nullable|string|max:1000',
                'reference_type' => 'nullable|in:invoice,order,return_order,other',
                'reference_id' => 'nullable|integer',
                'reference_number' => 'nullable|string|max:100',
                'bank_account_id' => 'sometimes|exists:bank_accounts,id',
                'status' => 'sometimes|in:pending,completed,cancelled',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $updateData = $request->all();
            $updateData['updated_by'] = auth()->id();
            
            $payment->update($updateData);
            
            // Load relationships for response
            $payment->load(['bankAccount', 'creator']);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Payment updated successfully',
                'data' => new PaymentResource($payment)
            ], 200);
            
        } catch (\Exception $e) {
            Log::error('Payment update failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Remove the specified payment
     */
    public function destroy($id)
    {
        try {
            $payment = Payment::findOrFail($id);
            
            // Check if payment can be deleted
            if ($payment->status === 'completed') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cannot delete completed payment. Cancel it instead.'
                ], 422);
            }
            
            $payment->delete();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Payment deleted successfully'
            ], 200);
            
        } catch (\Exception $e) {
            Log::error('Payment deletion failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get payment statistics
     */
    public function statistics(Request $request)
    {
        try {
            $query = Payment::query();
            
            // Apply date filters
            if ($request->filled('date_from')) {
                $query->whereDate('payment_date', '>=', $request->date_from);
            }
            
            if ($request->filled('date_to')) {
                $query->whereDate('payment_date', '<=', $request->date_to);
            }
            
            if ($request->filled('bank_account_id')) {
                $query->where('bank_account_id', $request->bank_account_id);
            }
            
            $stats = [
                'total_payments' => $query->count(),
                'total_income' => $query->where('payment_type', 'income')->sum('amount'),
                'total_expense' => $query->where('payment_type', 'expense')->sum('amount'),
                'net_amount' => $query->where('payment_type', 'income')->sum('amount') - 
                              $query->where('payment_type', 'expense')->sum('amount'),
                'by_type' => $query->groupBy('payment_type')
                    ->selectRaw('payment_type, count(*) as count, sum(amount) as total')
                    ->get()
                    ->keyBy('payment_type'),
                'by_method' => $query->groupBy('payment_method')
                    ->selectRaw('payment_method, count(*) as count, sum(amount) as total')
                    ->get()
                    ->keyBy('payment_method'),
                'by_status' => $query->groupBy('status')
                    ->selectRaw('status, count(*) as count, sum(amount) as total')
                    ->get()
                    ->keyBy('status'),
            ];
            
            return response()->json([
                'status' => 'success',
                'message' => 'Payment statistics retrieved successfully',
                'data' => $stats
            ], 200);
            
        } catch (\Exception $e) {
            Log::error('Payment statistics failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve payment statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Generate unique payment code
     */
    private function generatePaymentCode($type)
    {
        $prefix = $type === 'income' ? 'TT' : 'CT';
        
        do {
            $code = $prefix . date('Ymd') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (Payment::where('payment_code', $code)->exists());
        
        return $code;
    }
}
