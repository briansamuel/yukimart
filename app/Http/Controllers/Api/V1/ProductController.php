<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Http\Resources\V1\ProductResource;
use App\Http\Resources\V1\ProductListResource;
use App\Http\Traits\ApiOptimizationTrait;
use App\Http\Requests\Api\V1\Product\ProductListRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    use ApiOptimizationTrait;
    /**
     * Display a listing of products with pagination and filters
     */
    public function index(ProductListRequest $request)
    {
        $startTime = microtime(true);

        try {
            // Generate cache key for this request
            $cacheKey = $this->generateCacheKey($request, 'products_list');

            // Try to get cached response
            $cachedResponse = $this->cacheResponse($cacheKey, null, 3); // 3 minutes cache

            if ($cachedResponse) {
                $response = response()->json($cachedResponse);
                return $this->addPerformanceHeaders($response, $startTime);
            }

            // Parse include parameter for dynamic relationship loading
            $includes = $this->parseProductIncludes($request->get('include', 'category,inventory'));

            // Base query with optimized eager loading
            $query = Product::query();

            // Apply dynamic includes
            if (!empty($includes)) {
                $query->with($includes);
            }

            // Apply filters with optimization
            $this->applyProductFilters($query, $request);

            // Apply sorting
            $this->applyProductSorting($query, $request);

            // Optimized pagination
            $products = $this->optimizePagination($query, $request);

            // Transform to resource collection
            $resourceCollection = ProductListResource::collection($products);

            // Parse fields for field selection
            $fields = $this->parseFields($request->get('fields'));

            // Prepare response data
            $responseData = [
                'status' => 'success',
                'message' => 'Products retrieved successfully',
                'data' => $resourceCollection,
                'meta' => [
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                    'per_page' => $products->perPage(),
                    'total' => $products->total(),
                    'from' => $products->firstItem(),
                    'to' => $products->lastItem(),
                    'execution_time' => round((microtime(true) - $startTime) * 1000, 2) . 'ms'
                ]
            ];

            // Apply field filtering if requested
            if (!empty($fields)) {
                $responseData = $this->filterFields($responseData, $fields);
            }

            // Cache the response
            $this->cacheResponse($cacheKey, $responseData, 3);

            // Create response with optimization headers
            $response = response()->json($responseData, 200);
            $response = $this->addPerformanceHeaders($response, $startTime);
            $response = $this->addRateLimitHeaders($response);

            return $response;
            
        } catch (\Exception $e) {
            Log::error('Product listing failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve products',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Display the specified product
     */
    public function show($id)
    {
        try {
            $product = Product::with([
                'category',
                'inventory',
                'variants.inventory'
            ])->findOrFail($id);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Product retrieved successfully',
                'data' => new ProductResource($product)
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Product not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }
    
    /**
     * Store a newly created product
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'product_name' => 'required|string|max:255',
                'sku' => 'required|string|max:20|unique:products,sku',
                'barcode' => 'nullable|string|max:50|unique:products,barcode',
                'product_description' => 'nullable|string',
                'product_status' => 'required|in:trash,pending,draft,publish',
                'cost_price' => 'required|numeric|min:0',
                'sale_price' => 'required|numeric|min:0',
                'min_price' => 'nullable|numeric|min:0',
                'max_price' => 'nullable|numeric|min:0',
                'weight' => 'nullable|integer|min:0',
                'length' => 'nullable|numeric|min:0',
                'width' => 'nullable|numeric|min:0',
                'height' => 'nullable|numeric|min:0',
                'category_id' => 'nullable|exists:product_categories,id',
                'brand_id' => 'nullable|exists:brands,id',
                'supplier_id' => 'nullable|exists:suppliers,id',
                'reorder_point' => 'nullable|integer|min:0',
                'product_feature' => 'nullable|boolean',
                'has_variants' => 'nullable|boolean',
                'points' => 'nullable|integer|min:0',
                'product_image' => 'nullable|string',
                'meta_title' => 'nullable|string|max:255',
                'meta_description' => 'nullable|string|max:500',
                'meta_keywords' => 'nullable|string|max:255',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            DB::beginTransaction();
            
            $productData = $request->all();
            $productData['created_by'] = auth()->id();
            
            // Calculate volume if dimensions provided
            if ($request->filled(['length', 'width', 'height'])) {
                $productData['volume'] = $request->length * $request->width * $request->height;
            }
            
            $product = Product::create($productData);
            
            // Create initial inventory record if not variant product
            if (!$product->has_variants) {
                $product->inventory()->create([
                    'quantity' => 0,
                    'reserved_quantity' => 0,
                    'warehouse_id' => 1, // Default warehouse
                ]);
            }
            
            DB::commit();
            
            // Load relationships for response
            $product->load(['category', 'inventory']);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Product created successfully',
                'data' => new ProductResource($product)
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Product creation failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create product',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Update the specified product
     */
    public function update(Request $request, $id)
    {
        try {
            $product = Product::findOrFail($id);
            
            $validator = Validator::make($request->all(), [
                'product_name' => 'sometimes|string|max:255',
                'sku' => 'sometimes|string|max:20|unique:products,sku,' . $id,
                'barcode' => 'nullable|string|max:50|unique:products,barcode,' . $id,
                'product_description' => 'nullable|string',
                'product_status' => 'sometimes|in:trash,pending,draft,publish',
                'cost_price' => 'sometimes|numeric|min:0',
                'sale_price' => 'sometimes|numeric|min:0',
                'min_price' => 'nullable|numeric|min:0',
                'max_price' => 'nullable|numeric|min:0',
                'weight' => 'nullable|integer|min:0',
                'length' => 'nullable|numeric|min:0',
                'width' => 'nullable|numeric|min:0',
                'height' => 'nullable|numeric|min:0',
                'category_id' => 'nullable|exists:product_categories,id',
                'brand_id' => 'nullable|exists:brands,id',
                'supplier_id' => 'nullable|exists:suppliers,id',
                'reorder_point' => 'nullable|integer|min:0',
                'product_feature' => 'nullable|boolean',
                'points' => 'nullable|integer|min:0',
                'product_image' => 'nullable|string',
                'meta_title' => 'nullable|string|max:255',
                'meta_description' => 'nullable|string|max:500',
                'meta_keywords' => 'nullable|string|max:255',
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
            
            // Recalculate volume if dimensions updated
            if ($request->hasAny(['length', 'width', 'height'])) {
                $length = $request->get('length', $product->length);
                $width = $request->get('width', $product->width);
                $height = $request->get('height', $product->height);
                
                if ($length && $width && $height) {
                    $updateData['volume'] = $length * $width * $height;
                }
            }
            
            $product->update($updateData);
            
            // Load relationships for response
            $product->load(['category', 'brand', 'supplier', 'inventory']);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Product updated successfully',
                'data' => new ProductResource($product)
            ], 200);
            
        } catch (\Exception $e) {
            Log::error('Product update failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update product',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Remove the specified product
     */
    public function destroy($id)
    {
        try {
            $product = Product::findOrFail($id);
            
            // Check if product is used in orders or invoices
            $hasOrders = DB::table('order_items')->where('product_id', $id)->exists();
            $hasInvoices = DB::table('invoice_items')->where('product_id', $id)->exists();
            
            if ($hasOrders || $hasInvoices) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cannot delete product that is used in orders or invoices'
                ], 422);
            }
            
            $product->delete();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Product deleted successfully'
            ], 200);
            
        } catch (\Exception $e) {
            Log::error('Product deletion failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete product',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Search products by barcode (optimized with caching)
     */
    public function searchByBarcode(Request $request)
    {
        $startTime = microtime(true);

        try {
            $validator = Validator::make($request->all(), [
                'barcode' => 'required|string|max:50'
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation failed', 422, $validator->errors());
            }

            $barcode = $request->barcode;

            // Generate cache key for barcode search
            $cacheKey = "product_barcode_{$barcode}";

            // Try to get cached product
            $product = $this->cacheResponse($cacheKey, function() use ($barcode) {
                return Product::with(['category:id,name', 'inventory:id,product_id,quantity'])
                    ->where('barcode', $barcode)
                    ->first();
            }, 10); // 10 minutes cache for barcode searches

            if (!$product) {
                return $this->errorResponse('Product not found', 404);
            }

            $responseData = [
                'status' => 'success',
                'message' => 'Product found',
                'data' => new ProductResource($product)
            ];

            $response = response()->json($responseData, 200);
            $response = $this->addPerformanceHeaders($response, $startTime);

            return $response;

        } catch (\Exception $e) {
            Log::error('Product barcode search failed: ' . $e->getMessage());
            return $this->errorResponse('Failed to search product', 500);
        }
    }

    /**
     * Parse include parameter for dynamic relationship loading
     */
    private function parseProductIncludes($includeParam)
    {
        if (empty($includeParam)) {
            return [];
        }

        $includes = [];
        $availableIncludes = [
            'category' => 'category:id,name,slug',
            'brand' => 'brand:id,name',
            'supplier' => 'supplier:id,name',
            'inventory' => 'inventory:id,product_id,quantity,reserved_quantity',
            'variants' => [
                'variants:id,parent_id,sku,sale_price,cost_price',
                'variants.inventory:id,product_id,quantity'
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
     * Apply filters to product query with optimization
     */
    private function applyProductFilters($query, $request)
    {
        // Category filter
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Brand filter
        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }

        // Supplier filter
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        // Status filter
        if ($request->filled('product_status')) {
            if (is_array($request->product_status)) {
                $query->whereIn('product_status', $request->product_status);
            } else {
                $query->where('product_status', $request->product_status);
            }
        }

        // Feature filters
        if ($request->filled('has_variants')) {
            $query->where('has_variants', $request->boolean('has_variants'));
        }

        if ($request->filled('product_feature')) {
            $query->where('product_feature', $request->boolean('product_feature'));
        }

        // Price range filter
        if ($request->filled('min_price')) {
            $query->where('sale_price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('sale_price', '<=', $request->max_price);
        }

        // Stock filters
        if ($request->filled('stock_status')) {
            $stockStatus = $request->stock_status;

            if ($stockStatus === 'in_stock') {
                $query->whereHas('inventory', function ($q) {
                    $q->where('quantity', '>', 0);
                });
            } elseif ($stockStatus === 'out_of_stock') {
                $query->whereDoesntHave('inventory')
                      ->orWhereHas('inventory', function ($q) {
                          $q->where('quantity', '<=', 0);
                      });
            } elseif ($stockStatus === 'low_stock') {
                $query->whereHas('inventory', function ($q) {
                    $q->whereRaw('quantity <= reorder_point AND quantity > 0');
                });
            }
        }

        // Stock quantity range
        if ($request->filled('min_stock')) {
            $query->whereHas('inventory', function ($q) use ($request) {
                $q->where('quantity', '>=', $request->min_stock);
            });
        }
        if ($request->filled('max_stock')) {
            $query->whereHas('inventory', function ($q) use ($request) {
                $q->where('quantity', '<=', $request->max_stock);
            });
        }

        // Date filters
        if ($request->filled('created_from')) {
            $query->whereDate('created_at', '>=', $request->created_from);
        }
        if ($request->filled('created_to')) {
            $query->whereDate('created_at', '<=', $request->created_to);
        }
        if ($request->filled('updated_from')) {
            $query->whereDate('updated_at', '>=', $request->updated_from);
        }
        if ($request->filled('updated_to')) {
            $query->whereDate('updated_at', '<=', $request->updated_to);
        }

        // Enhanced search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $searchFields = $request->get('search_fields', ['name', 'sku', 'barcode', 'description']);

            $query->where(function($q) use ($search, $searchFields) {
                if (in_array('name', $searchFields)) {
                    $q->orWhere('product_name', 'like', "%{$search}%");
                }
                if (in_array('sku', $searchFields)) {
                    $q->orWhere('sku', 'like', "%{$search}%");
                }
                if (in_array('barcode', $searchFields)) {
                    $q->orWhere('barcode', 'like', "%{$search}%");
                }
                if (in_array('description', $searchFields)) {
                    $q->orWhere('product_description', 'like', "%{$search}%");
                }
            });
        }
    }

    /**
     * Apply sorting to product query
     */
    private function applyProductSorting($query, $request)
    {
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        // Validate sort fields
        $allowedSortFields = [
            'id', 'product_name', 'sku', 'sale_price', 'cost_price',
            'created_at', 'updated_at', 'stock_quantity'
        ];

        if ($sortBy === 'stock_quantity') {
            // Special handling for stock quantity sorting
            $query->leftJoin('inventories', 'products.id', '=', 'inventories.product_id')
                  ->orderBy('inventories.quantity', $sortOrder === 'asc' ? 'asc' : 'desc')
                  ->select('products.*');
        } elseif (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');
        } else {
            $query->orderBy('created_at', 'desc');
        }
    }
}
