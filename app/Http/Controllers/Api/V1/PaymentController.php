<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Http\Resources\V1\PaymentResource;
use App\Http\Traits\ApiOptimizationTrait;
use App\Http\Requests\Api\V1\Payment\PaymentListRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class PaymentController extends Controller
{
    use ApiOptimizationTrait;
    /**
     * Display a listing of payments with pagination and filters
     */
    public function index(PaymentListRequest $request)
    {
        $startTime = microtime(true);

        try {
            // Generate cache key for this request
            $cacheKey = $this->generateCacheKey($request, 'payments_list');

            // Try to get cached response (3 minutes cache for payments)
            $cachedResponse = $this->cacheResponse($cacheKey, null, 3);

            if ($cachedResponse) {
                $response = response()->json($cachedResponse);
                return $this->addPerformanceHeaders($response, $startTime);
            }

            // Parse include parameter for dynamic relationship loading
            $includes = $this->parsePaymentIncludes($request->get('include', 'bankAccount,creator'));

            // Base query with optimized eager loading
            $query = Payment::query();

            // Apply dynamic includes
            if (!empty($includes)) {
                $query->with($includes);
            }

            // Apply filters with optimization
            $this->applyPaymentFilters($query, $request);

            // Apply sorting
            $this->applyPaymentSorting($query, $request);

            // Optimized pagination
            $payments = $this->optimizePagination($query, $request);

            // Transform to resource collection
            $resourceCollection = PaymentResource::collection($payments);

            // Parse fields for field selection
            $fields = $this->parseFields($request->get('fields'));

            // Apply field selection if specified
            if (!empty($fields)) {
                $resourceCollection = $this->applyFieldSelection($resourceCollection, $fields);
            }

            // Prepare response data
            $responseData = [
                'status' => 'success',
                'message' => 'Payments retrieved successfully',
                'data' => $resourceCollection,
                'meta' => [
                    'current_page' => $payments->currentPage(),
                    'last_page' => $payments->lastPage(),
                    'per_page' => $payments->perPage(),
                    'total' => $payments->total(),
                    'from' => $payments->firstItem(),
                    'to' => $payments->lastItem(),
                    'execution_time' => round((microtime(true) - $startTime) * 1000, 2) . 'ms'
                ]
            ];

            // Cache the response
            $this->cacheResponse($cacheKey, $responseData, 3); // 3 minutes cache

            // Create response with optimization headers
            $response = response()->json($responseData, 200);
            $response = $this->addPerformanceHeaders($response, $startTime);
            $response = $this->addRateLimitHeaders($response, 120, 1); // 120 requests per minute

            return $response;
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
     * Get payment statistics with optimized queries and caching
     */
    public function statistics(Request $request)
    {
        $startTime = microtime(true);

        try {
            // Generate cache key for statistics
            $dateFrom = $request->get('date_from', 'all');
            $dateTo = $request->get('date_to', 'all');
            $bankAccountId = $request->get('bank_account_id', 'all');
            $cacheKey = "payment_statistics_{$dateFrom}_{$dateTo}_{$bankAccountId}";

            // Try to get cached response (10 minutes cache for statistics)
            $cachedResponse = $this->cacheResponse($cacheKey, null, 10);

            if ($cachedResponse) {
                $response = response()->json($cachedResponse);
                return $this->addPerformanceHeaders($response, $startTime);
            }

            // Build optimized single query for all statistics
            $stats = $this->calculatePaymentStatistics($request);

            // Prepare response data
            $responseData = [
                'status' => 'success',
                'message' => 'Payment statistics retrieved successfully',
                'data' => $stats,
                'meta' => [
                    'execution_time' => round((microtime(true) - $startTime) * 1000, 2) . 'ms'
                ]
            ];

            // Cache the response
            $this->cacheResponse($cacheKey, $responseData, 10); // 10 minutes cache

            // Create response with optimization headers
            $response = response()->json($responseData, 200);
            $response = $this->addPerformanceHeaders($response, $startTime);

            return $response;

        } catch (\Exception $e) {
            Log::error('Payment statistics failed: ' . $e->getMessage());
            return $this->errorResponse('Failed to retrieve payment statistics', 500);
        }
    }
    
    /**
     * Generate unique payment code with optimization
     */
    private function generatePaymentCode($type)
    {
        $prefix = $type === 'receipt' ? 'TT' : 'CT';

        // Use cache to avoid race conditions
        $attempts = 0;
        $maxAttempts = 10;

        do {
            $code = $prefix . date('Ymd') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $attempts++;

            // Check if code exists
            $exists = Payment::where('payment_code', $code)->exists();

            if (!$exists) {
                return $code;
            }

        } while ($attempts < $maxAttempts);

        // Fallback to timestamp-based code if all attempts fail
        return $prefix . time() . str_pad(mt_rand(1, 99), 2, '0', STR_PAD_LEFT);
    }

    /**
     * Parse payment includes for dynamic relationship loading
     */
    private function parsePaymentIncludes($includeString)
    {
        if (empty($includeString)) {
            return [];
        }

        $availableIncludes = [
            'bankAccount' => 'bankAccount:id,account_name,account_number',
            'creator' => 'creator:id,full_name,username',
            'reference' => 'reference', // Polymorphic relationship
            'customer' => 'customer:id,name,phone,email',
            'branchShop' => 'branchShop:id,name'
        ];

        $requestedIncludes = array_map('trim', explode(',', $includeString));
        $validIncludes = [];

        foreach ($requestedIncludes as $include) {
            if (isset($availableIncludes[$include])) {
                $validIncludes[] = $availableIncludes[$include];
            }
        }

        return $validIncludes;
    }

    /**
     * Apply filters to payment query with optimization
     */
    private function applyPaymentFilters($query, $request)
    {
        // Payment type filter
        if ($request->filled('payment_type')) {
            if (is_array($request->payment_type)) {
                $query->whereIn('payment_type', $request->payment_type);
            } else {
                $query->where('payment_type', $request->payment_type);
            }
        }

        // Payment method filter
        if ($request->filled('payment_method')) {
            if (is_array($request->payment_method)) {
                $query->whereIn('payment_method', $request->payment_method);
            } else {
                $query->where('payment_method', $request->payment_method);
            }
        }

        // Status filter
        if ($request->filled('status')) {
            if (is_array($request->status)) {
                $query->whereIn('status', $request->status);
            } else {
                $query->where('status', $request->status);
            }
        }

        // Reference type filter
        if ($request->filled('reference_type')) {
            if (is_array($request->reference_type)) {
                $query->whereIn('reference_type', $request->reference_type);
            } else {
                $query->where('reference_type', $request->reference_type);
            }
        }

        // Reference ID filter
        if ($request->filled('reference_id')) {
            $query->where('reference_id', $request->reference_id);
        }

        // Bank account filter
        if ($request->filled('bank_account_id')) {
            $query->where('bank_account_id', $request->bank_account_id);
        }

        // Amount range filters
        if ($request->filled('min_amount')) {
            $query->where('amount', '>=', $request->min_amount);
        }
        if ($request->filled('max_amount')) {
            $query->where('amount', '<=', $request->max_amount);
        }

        // Date range filters
        if ($request->filled('payment_date_from')) {
            $query->whereDate('payment_date', '>=', $request->payment_date_from);
        }
        if ($request->filled('payment_date_to')) {
            $query->whereDate('payment_date', '<=', $request->payment_date_to);
        }

        // Created date range filters
        if ($request->filled('created_from')) {
            $query->whereDate('created_at', '>=', $request->created_from);
        }
        if ($request->filled('created_to')) {
            $query->whereDate('created_at', '<=', $request->created_to);
        }

        // Enhanced search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $searchFields = $request->get('search_fields', ['payment_code', 'description', 'reference_number']);

            $query->where(function($q) use ($search, $searchFields) {
                foreach ($searchFields as $field) {
                    if (in_array($field, ['payment_code', 'description', 'reference_number', 'notes'])) {
                        $q->orWhere($field, 'like', "%{$search}%");
                    }
                }
            });
        }
    }

    /**
     * Apply sorting to payment query
     */
    private function applyPaymentSorting($query, $request)
    {
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        // Validate sort field
        $validSortFields = ['id', 'payment_code', 'payment_type', 'payment_method', 'amount', 'payment_date', 'status', 'created_at', 'updated_at'];

        if (!in_array($sortBy, $validSortFields)) {
            $sortBy = 'created_at';
        }

        // Validate sort order
        if (!in_array(strtolower($sortOrder), ['asc', 'desc'])) {
            $sortOrder = 'desc';
        }

        $query->orderBy($sortBy, $sortOrder);
    }

    /**
     * Calculate payment statistics with optimized single query
     */
    private function calculatePaymentStatistics($request)
    {
        $baseQuery = Payment::query();

        // Apply filters if specified
        if ($request->filled('date_from')) {
            $baseQuery->whereDate('payment_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $baseQuery->whereDate('payment_date', '<=', $request->date_to);
        }
        if ($request->filled('bank_account_id')) {
            $baseQuery->where('bank_account_id', $request->bank_account_id);
        }

        // Get all statistics in optimized queries
        $totalPayments = $baseQuery->count();

        // Get income and expense totals
        $incomeTotal = $baseQuery->where('payment_type', 'receipt')->sum('amount') ?? 0;
        $expenseTotal = $baseQuery->where('payment_type', 'payment')->sum('amount') ?? 0;
        $netAmount = $incomeTotal - $expenseTotal;

        // Get statistics by type
        $byType = $baseQuery->groupBy('payment_type')
            ->selectRaw('payment_type, count(*) as count, sum(amount) as total')
            ->get()
            ->keyBy('payment_type')
            ->map(function($item) {
                return [
                    'type' => $item->payment_type,
                    'count' => (int) $item->count,
                    'total' => (float) $item->total
                ];
            });

        // Get statistics by method
        $byMethod = $baseQuery->groupBy('payment_method')
            ->selectRaw('payment_method, count(*) as count, sum(amount) as total')
            ->get()
            ->keyBy('payment_method')
            ->map(function($item) {
                return [
                    'method' => $item->payment_method,
                    'count' => (int) $item->count,
                    'total' => (float) $item->total
                ];
            });

        // Get statistics by status
        $byStatus = $baseQuery->groupBy('status')
            ->selectRaw('status, count(*) as count, sum(amount) as total')
            ->get()
            ->keyBy('status')
            ->map(function($item) {
                return [
                    'status' => $item->status,
                    'count' => (int) $item->count,
                    'total' => (float) $item->total
                ];
            });

        return [
            'total_payments' => $totalPayments,
            'total_income' => (float) $incomeTotal,
            'total_expense' => (float) $expenseTotal,
            'net_amount' => (float) $netAmount,
            'by_type' => $byType,
            'by_method' => $byMethod,
            'by_status' => $byStatus,
            'summary' => [
                'average_payment' => $totalPayments > 0 ? round(($incomeTotal + $expenseTotal) / $totalPayments, 2) : 0,
                'income_percentage' => ($incomeTotal + $expenseTotal) > 0 ? round(($incomeTotal / ($incomeTotal + $expenseTotal)) * 100, 2) : 0,
                'expense_percentage' => ($incomeTotal + $expenseTotal) > 0 ? round(($expenseTotal / ($incomeTotal + $expenseTotal)) * 100, 2) : 0
            ]
        ];
    }
}
