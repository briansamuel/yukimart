<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Http\Resources\V1\CustomerResource;
use App\Http\Resources\V1\CustomerListResource;
use App\Http\Traits\ApiOptimizationTrait;
use App\Http\Requests\Api\V1\Customer\CustomerListRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class CustomerController extends Controller
{
    use ApiOptimizationTrait;
    /**
     * Display a listing of customers with pagination and filters
     */
    public function index(CustomerListRequest $request)
    {
        $startTime = microtime(true);

        try {
            // Generate cache key for this request
            $cacheKey = $this->generateCacheKey($request, 'customers_list');

            // Try to get cached response (5 minutes cache)
            $cachedResponse = $this->cacheResponse($cacheKey, null, 5);

            if ($cachedResponse) {
                $response = response()->json($cachedResponse);
                return $this->addPerformanceHeaders($response, $startTime);
            }

            // Parse include parameter for dynamic relationship loading
            $includes = $this->parseCustomerIncludes($request->get('include', 'branchShop'));

            // Base query with optimized eager loading
            $query = Customer::query();

            // Apply dynamic includes
            if (!empty($includes)) {
                $query->with($includes);
            }

            // Apply filters with optimization
            $this->applyCustomerFilters($query, $request);

            // Apply sorting
            $this->applyCustomerSorting($query, $request);

            // Optimized pagination
            $customers = $this->optimizePagination($query, $request);

            // Transform to resource collection
            $resourceCollection = CustomerListResource::collection($customers);

            // Parse fields for field selection
            $fields = $this->parseFields($request->get('fields'));

            // Apply field selection if specified
            if (!empty($fields)) {
                $resourceCollection = $this->applyFieldSelection($resourceCollection, $fields);
            }

            // Prepare response data
            $responseData = [
                'status' => 'success',
                'message' => 'Customers retrieved successfully',
                'data' => $resourceCollection,
                'meta' => [
                    'current_page' => $customers->currentPage(),
                    'last_page' => $customers->lastPage(),
                    'per_page' => $customers->perPage(),
                    'total' => $customers->total(),
                    'from' => $customers->firstItem(),
                    'to' => $customers->lastItem(),
                    'execution_time' => round((microtime(true) - $startTime) * 1000, 2) . 'ms'
                ]
            ];

            // Cache the response
            $this->cacheResponse($cacheKey, $responseData, 5); // 5 minutes cache

            // Create response with optimization headers
            $response = response()->json($responseData, 200);
            $response = $this->addPerformanceHeaders($response, $startTime);
            $response = $this->addRateLimitHeaders($response, 120, 1); // 120 requests per minute

            return $response;
            
        } catch (\Exception $e) {
            Log::error('Customer listing failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve customers',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Display the specified customer with dynamic includes
     */
    public function show(Request $request, $id)
    {
        $startTime = microtime(true);

        try {
            // Generate cache key for this customer
            $cacheKey = "customer_detail_{$id}_" . md5($request->get('include', ''));

            // Try to get cached response (2 minutes cache for customer details)
            $cachedResponse = $this->cacheResponse($cacheKey, null, 2);

            if ($cachedResponse) {
                $response = response()->json($cachedResponse);
                return $this->addPerformanceHeaders($response, $startTime);
            }

            // Parse include parameter for dynamic relationship loading
            $includes = $this->parseCustomerIncludes($request->get('include', 'branchShop'));

            // Find customer with dynamic includes
            $query = Customer::query();
            if (!empty($includes)) {
                $query->with($includes);
            }

            $customer = $query->findOrFail($id);

            // Parse fields for field selection
            $fields = $this->parseFields($request->get('fields'));

            // Transform to resource
            $customerResource = new CustomerResource($customer);

            // Apply field selection if specified
            if (!empty($fields)) {
                $customerResource = $this->applyFieldSelection($customerResource, $fields);
            }

            // Prepare response data
            $responseData = [
                'status' => 'success',
                'message' => 'Customer retrieved successfully',
                'data' => $customerResource,
                'meta' => [
                    'execution_time' => round((microtime(true) - $startTime) * 1000, 2) . 'ms'
                ]
            ];

            // Cache the response
            $this->cacheResponse($cacheKey, $responseData, 2); // 2 minutes cache

            // Create response with optimization headers
            $response = response()->json($responseData, 200);
            $response = $this->addPerformanceHeaders($response, $startTime);

            return $response;

        } catch (\Exception $e) {
            return $this->errorResponse('Customer not found', 404);
        }
    }
    
    /**
     * Store a newly created customer
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'phone' => 'required|string|max:20|unique:customers,phone',
                'email' => 'nullable|email|max:255|unique:customers,email',
                'facebook' => 'nullable|string|max:255',
                'address' => 'nullable|string|max:500',
                'area' => 'nullable|string|max:255',
                'customer_type' => 'required|in:individual,business',
                'customer_group' => 'nullable|string|max:100',
                'tax_code' => 'nullable|string|max:50',
                'status' => 'required|in:active,inactive',
                'notes' => 'nullable|string|max:1000',
                'birthday' => 'nullable|date',
                'points' => 'nullable|integer|min:0',
                'branch_shop_id' => 'required|exists:branch_shops,id',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $customerData = $request->all();
            $customerData['customer_code'] = $this->generateCustomerCode();
            $customerData['created_by'] = auth()->id();
            
            $customer = Customer::create($customerData);
            
            // Load relationships for response
            $customer->load(['branchShop']);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Customer created successfully',
                'data' => new CustomerResource($customer)
            ], 201);
            
        } catch (\Exception $e) {
            Log::error('Customer creation failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create customer',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Update the specified customer
     */
    public function update(Request $request, $id)
    {
        try {
            $customer = Customer::findOrFail($id);
            
            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|string|max:255',
                'phone' => 'sometimes|string|max:20|unique:customers,phone,' . $id,
                'email' => 'nullable|email|max:255|unique:customers,email,' . $id,
                'facebook' => 'nullable|string|max:255',
                'address' => 'nullable|string|max:500',
                'area' => 'nullable|string|max:255',
                'customer_type' => 'sometimes|in:individual,business',
                'customer_group' => 'nullable|string|max:100',
                'tax_code' => 'nullable|string|max:50',
                'status' => 'sometimes|in:active,inactive',
                'notes' => 'nullable|string|max:1000',
                'birthday' => 'nullable|date',
                'points' => 'nullable|integer|min:0',
                'branch_shop_id' => 'sometimes|exists:branch_shops,id',
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
            
            $customer->update($updateData);
            
            // Load relationships for response
            $customer->load(['branchShop']);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Customer updated successfully',
                'data' => new CustomerResource($customer)
            ], 200);
            
        } catch (\Exception $e) {
            Log::error('Customer update failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update customer',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Remove the specified customer
     */
    public function destroy($id)
    {
        try {
            $customer = Customer::findOrFail($id);
            
            // Check if customer has orders or invoices
            if ($customer->orders()->count() > 0 || $customer->invoices()->count() > 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cannot delete customer with existing orders or invoices'
                ], 422);
            }
            
            $customer->delete();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Customer deleted successfully'
            ], 200);
            
        } catch (\Exception $e) {
            Log::error('Customer deletion failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete customer',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get customer statistics with optimized queries and caching
     */
    public function statistics(Request $request)
    {
        $startTime = microtime(true);

        try {
            // Generate cache key for statistics
            $branchShopId = $request->get('branch_shop_id', 'all');
            $cacheKey = "customer_statistics_{$branchShopId}";

            // Try to get cached response (10 minutes cache for statistics)
            $cachedResponse = $this->cacheResponse($cacheKey, null, 10);

            if ($cachedResponse) {
                $response = response()->json($cachedResponse);
                return $this->addPerformanceHeaders($response, $startTime);
            }

            // Build optimized single query for all statistics
            $stats = $this->calculateCustomerStatistics($request);

            // Prepare response data
            $responseData = [
                'status' => 'success',
                'message' => 'Customer statistics retrieved successfully',
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
            Log::error('Customer statistics failed: ' . $e->getMessage());
            return $this->errorResponse('Failed to retrieve customer statistics', 500);
        }
    }
    
    /**
     * Generate unique customer code with optimization
     */
    private function generateCustomerCode()
    {
        // Use cache to avoid race conditions
        $attempts = 0;
        $maxAttempts = 10;

        do {
            $code = 'KH' . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
            $attempts++;

            // Check if code exists
            $exists = Customer::where('customer_code', $code)->exists();

            if (!$exists) {
                return $code;
            }

        } while ($attempts < $maxAttempts);

        // Fallback to timestamp-based code if all attempts fail
        return 'KH' . time() . str_pad(mt_rand(1, 99), 2, '0', STR_PAD_LEFT);
    }

    /**
     * Parse customer includes for dynamic relationship loading
     */
    private function parseCustomerIncludes($includeString)
    {
        if (empty($includeString)) {
            return [];
        }

        $availableIncludes = [
            'branchShop' => 'branchShop:id,name',
            'orders' => 'orders:id,customer_id,order_number,final_amount,status,created_at',
            'invoices' => 'invoices:id,customer_id,invoice_number,total_amount,status,created_at',
            'orders.orderItems' => 'orders.orderItems:id,order_id,product_id,quantity,price',
            'invoices.invoiceItems' => 'invoices.invoiceItems:id,invoice_id,product_id,quantity,price'
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
     * Apply filters to customer query with optimization
     */
    private function applyCustomerFilters($query, $request)
    {
        // Customer type filter
        if ($request->filled('customer_type')) {
            if (is_array($request->customer_type)) {
                $query->whereIn('customer_type', $request->customer_type);
            } else {
                $query->where('customer_type', $request->customer_type);
            }
        }

        // Customer group filter
        if ($request->filled('customer_group')) {
            $query->where('customer_group', $request->customer_group);
        }

        // Status filter
        if ($request->filled('status')) {
            if (is_array($request->status)) {
                $query->whereIn('status', $request->status);
            } else {
                $query->where('status', $request->status);
            }
        }

        // Branch shop filter
        if ($request->filled('branch_shop_id')) {
            $query->where('branch_shop_id', $request->branch_shop_id);
        }

        // Date range filters
        if ($request->filled('created_from')) {
            $query->whereDate('created_at', '>=', $request->created_from);
        }
        if ($request->filled('created_to')) {
            $query->whereDate('created_at', '<=', $request->created_to);
        }

        // Birthday range filters
        if ($request->filled('birthday_from')) {
            $query->whereDate('birthday', '>=', $request->birthday_from);
        }
        if ($request->filled('birthday_to')) {
            $query->whereDate('birthday', '<=', $request->birthday_to);
        }

        // Points range filters
        if ($request->filled('min_points')) {
            $query->where('points', '>=', $request->min_points);
        }
        if ($request->filled('max_points')) {
            $query->where('points', '<=', $request->max_points);
        }

        // Enhanced search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $searchFields = $request->get('search_fields', ['name', 'phone', 'email', 'customer_code']);

            $query->where(function($q) use ($search, $searchFields) {
                foreach ($searchFields as $field) {
                    if (in_array($field, ['name', 'phone', 'email', 'customer_code', 'address'])) {
                        $q->orWhere($field, 'like', "%{$search}%");
                    }
                }
            });
        }
    }

    /**
     * Apply sorting to customer query
     */
    private function applyCustomerSorting($query, $request)
    {
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        // Validate sort field
        $validSortFields = ['id', 'name', 'phone', 'email', 'customer_code', 'customer_type', 'status', 'points', 'created_at', 'updated_at'];

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
     * Calculate customer statistics with optimized single query
     */
    private function calculateCustomerStatistics($request)
    {
        $baseQuery = Customer::query();

        // Apply branch filter if specified
        if ($request->filled('branch_shop_id')) {
            $baseQuery->where('branch_shop_id', $request->branch_shop_id);
        }

        // Get all statistics in optimized queries
        $totalCustomers = $baseQuery->count();
        $activeCustomers = $baseQuery->where('status', 'active')->count();
        $inactiveCustomers = $baseQuery->where('status', 'inactive')->count();

        // Get customer type statistics
        $byType = $baseQuery->groupBy('customer_type')
            ->selectRaw('customer_type, count(*) as count')
            ->get()
            ->keyBy('customer_type')
            ->map(function($item) {
                return [
                    'type' => $item->customer_type,
                    'count' => (int) $item->count
                ];
            });

        // Get customer group statistics
        $byGroup = $baseQuery->whereNotNull('customer_group')
            ->groupBy('customer_group')
            ->selectRaw('customer_group, count(*) as count')
            ->get()
            ->keyBy('customer_group')
            ->map(function($item) {
                return [
                    'group' => $item->customer_group,
                    'count' => (int) $item->count
                ];
            });

        // Get points statistics
        $totalPoints = $baseQuery->sum('points') ?? 0;
        $avgPoints = $totalCustomers > 0 ? round($totalPoints / $totalCustomers, 2) : 0;

        return [
            'total_customers' => $totalCustomers,
            'active_customers' => $activeCustomers,
            'inactive_customers' => $inactiveCustomers,
            'by_type' => $byType,
            'by_group' => $byGroup,
            'points_statistics' => [
                'total_points' => (int) $totalPoints,
                'average_points' => $avgPoints
            ]
        ];
    }
}
