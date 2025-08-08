<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Http\Resources\V1\ProductResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    /**
     * Display a listing of products with pagination and filters
     */
    public function index(Request $request)
    {
        try {
            $query = Product::with(['category', 'inventory']);
            
            // Apply filters
            if ($request->filled('category_id')) {
                $query->where('category_id', $request->category_id);
            }
            
            // Note: brand_id and supplier_id filters removed as these relationships don't exist in current model
            
            if ($request->filled('product_status')) {
                $query->where('product_status', $request->product_status);
            }
            
            if ($request->filled('has_variants')) {
                $query->where('has_variants', $request->boolean('has_variants'));
            }
            
            if ($request->filled('product_feature')) {
                $query->where('product_feature', $request->boolean('product_feature'));
            }
            
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('product_name', 'like', "%{$search}%")
                      ->orWhere('sku', 'like', "%{$search}%")
                      ->orWhere('barcode', 'like', "%{$search}%")
                      ->orWhere('product_description', 'like', "%{$search}%");
                });
            }
            
            // Price range filter
            if ($request->filled('min_price')) {
                $query->where('sale_price', '>=', $request->min_price);
            }
            
            if ($request->filled('max_price')) {
                $query->where('sale_price', '<=', $request->max_price);
            }
            
            // Stock filter
            if ($request->filled('in_stock')) {
                if ($request->boolean('in_stock')) {
                    $query->whereHas('inventory', function ($q) {
                        $q->where('quantity', '>', 0);
                    });
                } else {
                    $query->whereDoesntHave('inventory')
                          ->orWhereHas('inventory', function ($q) {
                              $q->where('quantity', '<=', 0);
                          });
                }
            }
            
            // Sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);
            
            // Pagination
            $perPage = $request->get('per_page', 15);
            $products = $query->paginate($perPage);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Products retrieved successfully',
                'data' => ProductResource::collection($products),
                'meta' => [
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                    'per_page' => $products->perPage(),
                    'total' => $products->total(),
                    'from' => $products->firstItem(),
                    'to' => $products->lastItem(),
                ]
            ], 200);
            
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
     * Search products by barcode
     */
    public function searchByBarcode(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'barcode' => 'required|string'
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $product = Product::with(['category', 'inventory'])
                ->where('barcode', $request->barcode)
                ->first();
            
            if (!$product) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Product not found'
                ], 404);
            }
            
            return response()->json([
                'status' => 'success',
                'message' => 'Product found',
                'data' => new ProductResource($product)
            ], 200);
            
        } catch (\Exception $e) {
            Log::error('Product barcode search failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to search product',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
