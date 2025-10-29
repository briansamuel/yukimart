<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Http\Resources\V1\OrderResource;
use App\Http\Resources\V1\OrderListResource;
use App\Http\Traits\ApiOptimizationTrait;
use App\Http\Requests\Api\V1\Order\OrderListRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    use ApiOptimizationTrait;
    /**
     * Display a listing of orders with pagination and filters
     */
    public function index(OrderListRequest $request)
    {
        $startTime = microtime(true);

        try {
            // Generate cache key for this request
            $cacheKey = $this->generateCacheKey($request, 'orders_list');

            // Try to get cached response (short cache for orders due to frequent updates)
            $cachedResponse = $this->cacheResponse($cacheKey, null, 1); // 1 minute cache

            if ($cachedResponse) {
                $response = response()->json($cachedResponse);
                return $this->addPerformanceHeaders($response, $startTime);
            }

            // Parse include parameter for dynamic relationship loading
            $includes = $this->parseOrderIncludes($request->get('include', 'customer,branchShop,creator,seller'));

            // Base query with optimized eager loading
            $query = Order::query();

            // Apply dynamic includes
            if (!empty($includes)) {
                $query->with($includes);
            }

            // Apply filters with optimization
            $this->applyOrderFilters($query, $request);

            // Apply sorting
            $this->applyOrderSorting($query, $request);

            // Optimized pagination
            $orders = $this->optimizePagination($query, $request);

            // Transform to resource collection
            $resourceCollection = OrderListResource::collection($orders);

            // Parse fields for field selection
            $fields = $this->parseFields($request->get('fields'));

            // Prepare response data
            $responseData = [
                'status' => 'success',
                'message' => 'Orders retrieved successfully',
                'data' => $resourceCollection,
                'meta' => [
                    'current_page' => $orders->currentPage(),
                    'last_page' => $orders->lastPage(),
                    'per_page' => $orders->perPage(),
                    'total' => $orders->total(),
                    'from' => $orders->firstItem(),
                    'to' => $orders->lastItem(),
                    'execution_time' => round((microtime(true) - $startTime) * 1000, 2) . 'ms'
                ]
            ];

            // Apply field filtering if requested
            if (!empty($fields)) {
                $responseData = $this->filterFields($responseData, $fields);
            }

            // Cache the response
            $this->cacheResponse($cacheKey, $responseData, 1);

            // Create response with optimization headers
            $response = response()->json($responseData, 200);
            $response = $this->addPerformanceHeaders($response, $startTime);
            $response = $this->addRateLimitHeaders($response);

            return $response;
            
        } catch (\Exception $e) {
            Log::error('Order listing failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve orders',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Display the specified order
     */
    public function show($id)
    {
        try {
            $order = Order::with([
                'customer', 
                'branchShop', 
                'orderItems.product', 
                'creator', 
                'seller'
            ])->findOrFail($id);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Order retrieved successfully',
                'data' => new OrderResource($order)
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Order not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }
    
    /**
     * Store a newly created order
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'customer_id' => 'nullable|exists:customers,id',
                'customer_name' => 'required_if:customer_id,null|string|max:255',
                'customer_phone' => 'nullable|string|max:20',
                'customer_email' => 'nullable|email|max:255',
                'branch_shop_id' => 'required|exists:branch_shops,id',
                'order_type' => 'required|in:sale,return,exchange,service',
                'order_date' => 'required|date',
                'delivery_date' => 'nullable|date|after_or_equal:order_date',
                'priority' => 'required|in:low,normal,high,urgent',
                'payment_method' => 'required|in:cash,card,transfer,cod,other',
                'delivery_address' => 'nullable|string|max:500',
                'delivery_notes' => 'nullable|string|max:500',
                'notes' => 'nullable|string|max:1000',
                'reference_number' => 'nullable|string|max:100',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'nullable|exists:products,id',
                'items.*.product_name' => 'required|string|max:255',
                'items.*.product_sku' => 'nullable|string|max:100',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.unit' => 'required|string|max:50',
                'items.*.unit_price' => 'required|numeric|min:0',
                'items.*.discount_rate' => 'nullable|numeric|min:0|max:100',
                'items.*.discount_amount' => 'nullable|numeric|min:0',
                'items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
                'items.*.notes' => 'nullable|string|max:500',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            DB::beginTransaction();
            
            $orderData = $request->only([
                'customer_id', 'customer_name', 'customer_phone', 'customer_email',
                'branch_shop_id', 'order_type', 'order_date', 'delivery_date',
                'priority', 'payment_method', 'delivery_address', 'delivery_notes',
                'notes', 'reference_number'
            ]);
            
            // Set defaults
            $orderData['order_number'] = $this->generateOrderNumber();
            $orderData['status'] = 'draft';
            $orderData['payment_status'] = 'unpaid';
            $orderData['delivery_status'] = 'pending';
            $orderData['created_by'] = auth()->id();
            $orderData['sold_by'] = auth()->id();
            
            // Calculate totals
            $subtotal = 0;
            $totalTax = 0;
            $totalDiscount = 0;
            
            foreach ($request->items as $item) {
                $lineSubtotal = $item['quantity'] * $item['unit_price'];
                $lineDiscount = $item['discount_amount'] ?? (($item['discount_rate'] ?? 0) / 100 * $lineSubtotal);
                $lineTax = ($item['tax_rate'] ?? 0) / 100 * ($lineSubtotal - $lineDiscount);
                
                $subtotal += $lineSubtotal;
                $totalDiscount += $lineDiscount;
                $totalTax += $lineTax;
            }
            
            $orderData['subtotal'] = $subtotal;
            $orderData['discount_amount'] = $totalDiscount;
            $orderData['tax_amount'] = $totalTax;
            $orderData['final_amount'] = $subtotal - $totalDiscount + $totalTax;
            
            $order = Order::create($orderData);
            
            // Create order items
            foreach ($request->items as $index => $item) {
                $lineSubtotal = $item['quantity'] * $item['unit_price'];
                $lineDiscount = $item['discount_amount'] ?? (($item['discount_rate'] ?? 0) / 100 * $lineSubtotal);
                $lineTax = ($item['tax_rate'] ?? 0) / 100 * ($lineSubtotal - $lineDiscount);
                $lineTotal = $lineSubtotal - $lineDiscount + $lineTax;
                
                $order->orderItems()->create([
                    'product_id' => $item['product_id'],
                    'product_name' => $item['product_name'],
                    'product_sku' => $item['product_sku'] ?? null,
                    'product_description' => $item['product_description'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit' => $item['unit'],
                    'unit_price' => $item['unit_price'],
                    'discount_rate' => $item['discount_rate'] ?? 0,
                    'discount_amount' => $lineDiscount,
                    'tax_rate' => $item['tax_rate'] ?? 0,
                    'tax_amount' => $lineTax,
                    'line_total' => $lineTotal,
                    'notes' => $item['notes'] ?? null,
                    'sort_order' => $index + 1,
                ]);
            }
            
            DB::commit();
            
            // Load relationships for response
            $order->load(['customer', 'branchShop', 'orderItems.product', 'creator', 'seller']);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Order created successfully',
                'data' => new OrderResource($order)
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order creation failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create order',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Update the specified order
     */
    public function update(Request $request, $id)
    {
        try {
            $order = Order::findOrFail($id);
            
            // Check if order can be updated
            if (in_array($order->status, ['completed', 'cancelled'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cannot update completed or cancelled order'
                ], 422);
            }
            
            $validator = Validator::make($request->all(), [
                'customer_id' => 'nullable|exists:customers,id',
                'customer_name' => 'sometimes|string|max:255',
                'customer_phone' => 'nullable|string|max:20',
                'customer_email' => 'nullable|email|max:255',
                'order_type' => 'sometimes|in:sale,return,exchange,service',
                'order_date' => 'sometimes|date',
                'delivery_date' => 'nullable|date|after_or_equal:order_date',
                'priority' => 'sometimes|in:low,normal,high,urgent',
                'status' => 'sometimes|in:draft,pending,processing,completed,cancelled',
                'payment_method' => 'sometimes|in:cash,card,transfer,cod,other',
                'payment_status' => 'sometimes|in:unpaid,partial,paid,refunded',
                'delivery_status' => 'sometimes|in:pending,processing,shipped,delivered,failed',
                'delivery_address' => 'nullable|string|max:500',
                'delivery_notes' => 'nullable|string|max:500',
                'notes' => 'nullable|string|max:1000',
                'reference_number' => 'nullable|string|max:100',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $updateData = $request->only([
                'customer_id', 'customer_name', 'customer_phone', 'customer_email',
                'order_type', 'order_date', 'delivery_date', 'priority', 'status',
                'payment_method', 'payment_status', 'delivery_status',
                'delivery_address', 'delivery_notes', 'notes', 'reference_number'
            ]);
            
            $updateData['updated_by'] = auth()->id();
            
            $order->update($updateData);
            
            // Load relationships for response
            $order->load(['customer', 'branchShop', 'orderItems.product', 'creator', 'seller']);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Order updated successfully',
                'data' => new OrderResource($order)
            ], 200);
            
        } catch (\Exception $e) {
            Log::error('Order update failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update order',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Remove the specified order
     */
    public function destroy($id)
    {
        try {
            $order = Order::findOrFail($id);
            
            // Check if order can be deleted
            if (in_array($order->status, ['processing', 'completed'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cannot delete processing or completed order'
                ], 422);
            }
            
            $order->delete();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Order deleted successfully'
            ], 200);
            
        } catch (\Exception $e) {
            Log::error('Order deletion failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete order',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Generate unique order number
     */
    private function generateOrderNumber()
    {
        do {
            $number = 'ORD' . date('Ymd') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (Order::where('order_number', $number)->exists());
        
        return $number;
    }

    /**
     * Parse include parameter for dynamic relationship loading
     */
    private function parseOrderIncludes($includeParam)
    {
        if (empty($includeParam)) {
            return [];
        }

        $includes = [];
        $availableIncludes = [
            'customer' => 'customer:id,customer_name,email,phone',
            'branchShop' => 'branchShop:id,name',
            'creator' => 'creator:id,username,full_name',
            'seller' => 'seller:id,username,full_name',
            'orderItems' => [
                'orderItems:id,order_id,product_id,product_name,product_sku,quantity,unit_price,line_total',
                'orderItems.product:id,product_name,product_thumbnail,sku'
            ]
        ];

        $requestedIncludes = explode(',', $includeParam);

        foreach ($requestedIncludes as $include) {
            $include = trim($include);
            if (isset($availableIncludes[$include])) {
                if (is_array($availableIncludes[$include])) {
                    $includes = array_merge($includes, $availableIncludes[$include]);
                } else {
                    $includes[] = $availableIncludes[$include];
                }
            }
        }

        return $includes;
    }

    /**
     * Apply filters to order query with optimization
     */
    private function applyOrderFilters($query, $request)
    {
        // Status filters (support arrays)
        if ($request->filled('status')) {
            if (is_array($request->status)) {
                $query->whereIn('status', $request->status);
            } else {
                $query->where('status', $request->status);
            }
        }

        if ($request->filled('payment_status')) {
            if (is_array($request->payment_status)) {
                $query->whereIn('payment_status', $request->payment_status);
            } else {
                $query->where('payment_status', $request->payment_status);
            }
        }

        if ($request->filled('delivery_status')) {
            if (is_array($request->delivery_status)) {
                $query->whereIn('delivery_status', $request->delivery_status);
            } else {
                $query->where('delivery_status', $request->delivery_status);
            }
        }

        if ($request->filled('order_type')) {
            if (is_array($request->order_type)) {
                $query->whereIn('order_type', $request->order_type);
            } else {
                $query->where('order_type', $request->order_type);
            }
        }

        if ($request->filled('priority')) {
            if (is_array($request->priority)) {
                $query->whereIn('priority', $request->priority);
            } else {
                $query->where('priority', $request->priority);
            }
        }

        // Relationship filters
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->filled('branch_shop_id')) {
            $query->where('branch_shop_id', $request->branch_shop_id);
        }

        if ($request->filled('created_by')) {
            $query->where('created_by', $request->created_by);
        }

        if ($request->filled('sold_by')) {
            $query->where('sold_by', $request->sold_by);
        }

        // Amount filters
        if ($request->filled('min_amount')) {
            $query->where('final_amount', '>=', $request->min_amount);
        }
        if ($request->filled('max_amount')) {
            $query->where('final_amount', '<=', $request->max_amount);
        }

        // Date filters
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('order_date_from')) {
            $query->whereDate('order_date', '>=', $request->order_date_from);
        }
        if ($request->filled('order_date_to')) {
            $query->whereDate('order_date', '<=', $request->order_date_to);
        }

        if ($request->filled('delivery_date_from')) {
            $query->whereDate('delivery_date', '>=', $request->delivery_date_from);
        }
        if ($request->filled('delivery_date_to')) {
            $query->whereDate('delivery_date', '<=', $request->delivery_date_to);
        }

        // Enhanced search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $searchFields = $request->get('search_fields', ['order_number', 'customer_name', 'customer_phone', 'reference_number']);

            $query->where(function($q) use ($search, $searchFields) {
                if (in_array('order_number', $searchFields)) {
                    $q->orWhere('order_number', 'like', "%{$search}%");
                }
                if (in_array('customer_name', $searchFields)) {
                    $q->orWhere('customer_name', 'like', "%{$search}%");
                }
                if (in_array('customer_phone', $searchFields)) {
                    $q->orWhere('customer_phone', 'like', "%{$search}%");
                }
                if (in_array('reference_number', $searchFields)) {
                    $q->orWhere('reference_number', 'like', "%{$search}%");
                }
            });
        }
    }

    /**
     * Apply sorting to order query
     */
    private function applyOrderSorting($query, $request)
    {
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        // Validate sort fields
        $allowedSortFields = [
            'id', 'order_number', 'order_date', 'delivery_date', 'final_amount',
            'status', 'payment_status', 'delivery_status', 'created_at', 'updated_at'
        ];

        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');
        } else {
            $query->orderBy('created_at', 'desc');
        }
    }
}
