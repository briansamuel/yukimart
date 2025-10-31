<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Services\TenantContextService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * API Product Controller
 * 
 * Handles all API operations for products including CRUD operations,
 * search, filtering, and product management features.
 */
class ProductController extends BaseApiController
{
    /**
     * Create a new controller instance
     */
    public function __construct(TenantContextService $tenantContext)
    {
        parent::__construct($tenantContext);
    }

    /**
     * Display a listing of products
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorizeTenantOperation('products.view');
        $this->applyRateLimit('products.index', 120, 1); // 120 requests per minute

        try {
            $searchParams = $this->getSearchParams($request);
            $paginationParams = $this->getPaginationParams($request);

            $query = Product::query()
                ->with(['category', 'brand'])
                ->when($searchParams['search'], function ($q, $search) {
                    $q->where(function ($query) use ($search) {
                        $query->where('product_name', 'like', "%{$search}%")
                              ->orWhere('sku', 'like', "%{$search}%")
                              ->orWhere('barcode', 'like', "%{$search}%");
                    });
                })
                ->when($searchParams['status'], function ($q, $status) {
                    $q->where('product_status', $status);
                })
                ->when($searchParams['category_id'], function ($q, $categoryId) {
                    $q->where('category_id', $categoryId);
                })
                ->when($searchParams['brand_id'], function ($q, $brandId) {
                    $q->where('brand_id', $brandId);
                })
                ->orderBy($paginationParams['sort'], $paginationParams['direction']);

            $products = $query->paginate($paginationParams['per_page']);

            $this->logActivity('products.index', ['search' => $searchParams]);

            return $this->paginatedResponse($products, 'Products retrieved successfully');

        } catch (\Exception $e) {
            return $this->handleException($e, 'retrieve products');
        }
    }

    /**
     * Store a newly created product
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorizeTenantOperation('products.create');
        $this->applyRateLimit('products.store', 30, 1); // 30 requests per minute

        $validator = $this->validateRequest($request, [
            'product_name' => 'required|string|max:255',
            'sku' => 'required|string|max:50|unique:products,sku',
            'barcode' => 'nullable|string|max:50|unique:products,barcode',
            'category_id' => 'nullable|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'product_description' => 'nullable|string',
            'product_content' => 'nullable|string',
            'cost_price' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'weight' => 'nullable|numeric|min:0',
            'product_status' => 'required|in:draft,publish,pending,trash',
            'product_feature' => 'boolean',
            'product_type' => 'required|in:simple,variable,grouped,external',
            'product_image' => 'nullable|image|max:2048|mimes:jpeg,png,jpg,gif'
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        try {
            DB::beginTransaction();

            $productData = $validator->validated();
            $productData['tenant_id'] = $this->getCurrentTenant()->id;
            $productData['created_by'] = $this->getCurrentUser()->id;

            // Handle image upload
            if ($request->hasFile('product_image')) {
                $imagePath = $request->file('product_image')->store(
                    "tenants/{$this->getCurrentTenant()->id}/products",
                    'public'
                );
                $productData['product_image'] = $imagePath;
            }

            $product = Product::create($productData);

            DB::commit();

            $this->logActivity('products.store', ['product_id' => $product->id]);

            return $this->successResponse(
                $product->load(['category', 'brand']),
                'Product created successfully',
                201
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleException($e, 'create product');
        }
    }

    /**
     * Display the specified product
     */
    public function show(Product $product): JsonResponse
    {
        $this->authorizeTenantOperation('products.view');
        $this->applyRateLimit('products.show', 180, 1); // 180 requests per minute

        try {
            $product->load(['category', 'brand', 'variants', 'inventories.branchShop']);

            $this->logActivity('products.show', ['product_id' => $product->id]);

            return $this->successResponse($product, 'Product retrieved successfully');

        } catch (\Exception $e) {
            return $this->handleException($e, 'retrieve product');
        }
    }

    /**
     * Update the specified product
     */
    public function update(Request $request, Product $product): JsonResponse
    {
        $this->authorizeTenantOperation('products.edit');
        $this->applyRateLimit('products.update', 60, 1); // 60 requests per minute

        $validator = $this->validateRequest($request, [
            'product_name' => 'required|string|max:255',
            'sku' => 'required|string|max:50|unique:products,sku,' . $product->id,
            'barcode' => 'nullable|string|max:50|unique:products,barcode,' . $product->id,
            'category_id' => 'nullable|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'product_description' => 'nullable|string',
            'product_content' => 'nullable|string',
            'cost_price' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'weight' => 'nullable|numeric|min:0',
            'product_status' => 'required|in:draft,publish,pending,trash',
            'product_feature' => 'boolean',
            'product_type' => 'required|in:simple,variable,grouped,external',
            'product_image' => 'nullable|image|max:2048|mimes:jpeg,png,jpg,gif'
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        try {
            DB::beginTransaction();

            $productData = $validator->validated();
            $productData['updated_by'] = $this->getCurrentUser()->id;

            // Handle image upload
            if ($request->hasFile('product_image')) {
                // Delete old image
                if ($product->product_image) {
                    Storage::disk('public')->delete($product->product_image);
                }

                $imagePath = $request->file('product_image')->store(
                    "tenants/{$this->getCurrentTenant()->id}/products",
                    'public'
                );
                $productData['product_image'] = $imagePath;
            }

            $product->update($productData);

            DB::commit();

            $this->logActivity('products.update', ['product_id' => $product->id]);

            return $this->successResponse(
                $product->load(['category', 'brand']),
                'Product updated successfully'
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleException($e, 'update product');
        }
    }

    /**
     * Remove the specified product
     */
    public function destroy(Product $product): JsonResponse
    {
        $this->authorizeTenantOperation('products.delete');
        $this->applyRateLimit('products.destroy', 30, 1); // 30 requests per minute

        try {
            DB::beginTransaction();

            // Check if product has related records
            if ($product->orderItems()->count() > 0) {
                return $this->errorResponse(
                    'Cannot delete product with existing orders',
                    400
                );
            }

            // Delete product image
            if ($product->product_image) {
                Storage::disk('public')->delete($product->product_image);
            }

            $productId = $product->id;
            $product->delete();

            DB::commit();

            $this->logActivity('products.destroy', ['product_id' => $productId]);

            return $this->successResponse(null, 'Product deleted successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleException($e, 'delete product');
        }
    }

    /**
     * Search products
     */
    public function search(Request $request, string $query): JsonResponse
    {
        $this->authorizeTenantOperation('products.view');
        $this->applyRateLimit('products.search', 120, 1); // 120 requests per minute

        try {
            $limit = min($request->get('limit', 10), 50);

            $products = Product::where(function ($q) use ($query) {
                    $q->where('product_name', 'like', "%{$query}%")
                      ->orWhere('sku', 'like', "%{$query}%")
                      ->orWhere('barcode', 'like', "%{$query}%");
                })
                ->where('product_status', 'publish')
                ->with(['category', 'brand'])
                ->limit($limit)
                ->get();

            $this->logActivity('products.search', ['query' => $query]);

            return $this->successResponse($products, 'Search results retrieved successfully');

        } catch (\Exception $e) {
            return $this->handleException($e, 'search products');
        }
    }

    /**
     * Find product by barcode
     */
    public function findByBarcode(string $barcode): JsonResponse
    {
        $this->authorizeTenantOperation('products.view');
        $this->applyRateLimit('products.barcode', 120, 1); // 120 requests per minute

        try {
            $product = Product::where('barcode', $barcode)
                             ->where('product_status', 'publish')
                             ->with(['category', 'brand'])
                             ->first();

            if (!$product) {
                return $this->errorResponse('Product not found', 404);
            }

            $this->logActivity('products.findByBarcode', ['barcode' => $barcode]);

            return $this->successResponse($product, 'Product found successfully');

        } catch (\Exception $e) {
            return $this->handleException($e, 'find product by barcode');
        }
    }

    /**
     * Bulk update products
     */
    public function bulkUpdate(Request $request): JsonResponse
    {
        $this->authorizeTenantOperation('products.edit');
        $this->applyRateLimit('products.bulkUpdate', 10, 1); // 10 requests per minute

        $validator = $this->validateRequest($request, [
            'product_ids' => 'required|array',
            'product_ids.*' => 'exists:products,id',
            'action' => 'required|in:publish,draft,trash,delete',
            'category_id' => 'nullable|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id'
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        try {
            DB::beginTransaction();

            $productIds = $request->product_ids;
            $action = $request->action;

            $query = Product::whereIn('id', $productIds);

            switch ($action) {
                case 'publish':
                case 'draft':
                case 'trash':
                    $query->update(['product_status' => $action]);
                    break;
                case 'delete':
                    $query->delete();
                    break;
            }

            // Update category/brand if provided
            if ($request->category_id) {
                Product::whereIn('id', $productIds)->update(['category_id' => $request->category_id]);
            }

            if ($request->brand_id) {
                Product::whereIn('id', $productIds)->update(['brand_id' => $request->brand_id]);
            }

            DB::commit();

            $this->logActivity('products.bulkUpdate', [
                'action' => $action,
                'product_ids' => $productIds,
                'count' => count($productIds)
            ]);

            return $this->successResponse(
                ['updated_count' => count($productIds)],
                "Bulk {$action} completed successfully"
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleException($e, 'bulk update products');
        }
    }

    /**
     * Get product categories
     */
    public function getCategories(): JsonResponse
    {
        $this->authorizeTenantOperation('products.view');

        try {
            $categories = Category::select('id', 'category_name', 'category_slug')
                                ->where('category_status', 'active')
                                ->orderBy('category_name')
                                ->get();

            return $this->successResponse($categories, 'Categories retrieved successfully');

        } catch (\Exception $e) {
            return $this->handleException($e, 'retrieve categories');
        }
    }

    /**
     * Get product brands
     */
    public function getBrands(): JsonResponse
    {
        $this->authorizeTenantOperation('products.view');

        try {
            $brands = Brand::select('id', 'brand_name', 'brand_slug')
                          ->where('brand_status', 'active')
                          ->orderBy('brand_name')
                          ->get();

            return $this->successResponse($brands, 'Brands retrieved successfully');

        } catch (\Exception $e) {
            return $this->handleException($e, 'retrieve brands');
        }
    }
}
