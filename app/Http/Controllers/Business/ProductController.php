<?php

namespace App\Http\Controllers\Business;

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Services\TenantContextService;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * Business Product Controller
 * 
 * Handles all tenant-specific product operations including CRUD operations,
 * search, filtering, and product management features.
 */
class ProductController extends BaseBusinessController
{
    /**
     * Product service instance
     */
    protected ProductService $productService;

    /**
     * Create a new controller instance
     */
    public function __construct(TenantContextService $tenantContext, ProductService $productService)
    {
        parent::__construct($tenantContext);
        $this->productService = $productService;
    }

    /**
     * Display a listing of products
     */
    public function index(Request $request)
    {
        $this->authorizeTenantOperation('products.view');

        $searchParams = $this->getSearchParams();
        $paginationParams = $this->getPaginationParams();

        // Get tenant-scoped products
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
            ->orderBy($paginationParams['sort'], $paginationParams['direction']);

        $products = $query->paginate($paginationParams['per_page']);

        // Get filter options
        $categories = Category::select('id', 'category_name')->get();
        $brands = Brand::select('id', 'brand_name')->get();

        if ($request->ajax()) {
            return $this->businessResponse([
                'products' => $products->items(),
                'pagination' => [
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                    'per_page' => $products->perPage(),
                    'total' => $products->total()
                ]
            ]);
        }

        return view('admin.business.products.index', compact('products', 'categories', 'brands', 'searchParams'));
    }

    /**
     * Show the form for creating a new product
     */
    public function create()
    {
        $this->authorizeTenantOperation('products.create');

        $categories = Category::select('id', 'category_name')->get();
        $brands = Brand::select('id', 'brand_name')->get();

        return view('admin.business.products.create', compact('categories', 'brands'));
    }

    /**
     * Store a newly created product
     */
    public function store(Request $request)
    {
        $this->authorizeTenantOperation('products.create');

        $validator = Validator::make($request->all(), [
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
            'product_type' => 'required|in:simple,variable,grouped,external'
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return $this->businessError('Validation failed', 422, $validator->errors());
            }
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $productData = $validator->validated();
            $productData['tenant_id'] = $this->getCurrentTenant()->id;
            $productData['created_by'] = $this->getCurrentUser()->id;

            $product = Product::create($productData);

            DB::commit();

            if ($request->ajax()) {
                return $this->businessResponse($product, 'Product created successfully', 201);
            }

            return redirect()->route('admin.business.products.index')
                           ->with('success', 'Product created successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Product creation failed', [
                'tenant_id' => $this->getCurrentTenant()->id,
                'user_id' => $this->getCurrentUser()->id,
                'error' => $e->getMessage()
            ]);

            if ($request->ajax()) {
                return $this->businessError('Failed to create product', 500);
            }

            return back()->with('error', 'Failed to create product')->withInput();
        }
    }

    /**
     * Display the specified product
     */
    public function show(Product $product)
    {
        $this->authorizeTenantOperation('products.view');

        $product->load(['category', 'brand', 'variants', 'inventories.branchShop']);

        if (request()->ajax()) {
            return $this->businessResponse($product);
        }

        return view('admin.business.products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified product
     */
    public function edit(Product $product)
    {
        $this->authorizeTenantOperation('products.edit');

        $categories = Category::select('id', 'category_name')->get();
        $brands = Brand::select('id', 'brand_name')->get();

        return view('admin.business.products.edit', compact('product', 'categories', 'brands'));
    }

    /**
     * Update the specified product
     */
    public function update(Request $request, Product $product)
    {
        $this->authorizeTenantOperation('products.edit');

        $validator = Validator::make($request->all(), [
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
            'product_type' => 'required|in:simple,variable,grouped,external'
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return $this->businessError('Validation failed', 422, $validator->errors());
            }
            return back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $productData = $validator->validated();
            $productData['updated_by'] = $this->getCurrentUser()->id;

            $product->update($productData);

            DB::commit();

            if ($request->ajax()) {
                return $this->businessResponse($product, 'Product updated successfully');
            }

            return redirect()->route('admin.business.products.index')
                           ->with('success', 'Product updated successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Product update failed', [
                'product_id' => $product->id,
                'tenant_id' => $this->getCurrentTenant()->id,
                'user_id' => $this->getCurrentUser()->id,
                'error' => $e->getMessage()
            ]);

            if ($request->ajax()) {
                return $this->businessError('Failed to update product', 500);
            }

            return back()->with('error', 'Failed to update product')->withInput();
        }
    }

    /**
     * Remove the specified product
     */
    public function destroy(Product $product)
    {
        $this->authorizeTenantOperation('products.delete');

        try {
            DB::beginTransaction();

            // Check if product has related records
            if ($product->orderItems()->count() > 0) {
                if (request()->ajax()) {
                    return $this->businessError('Cannot delete product with existing orders', 400);
                }
                return back()->with('error', 'Cannot delete product with existing orders');
            }

            $product->delete();

            DB::commit();

            if (request()->ajax()) {
                return $this->businessResponse(null, 'Product deleted successfully');
            }

            return redirect()->route('admin.business.products.index')
                           ->with('success', 'Product deleted successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Product deletion failed', [
                'product_id' => $product->id,
                'tenant_id' => $this->getCurrentTenant()->id,
                'user_id' => $this->getCurrentUser()->id,
                'error' => $e->getMessage()
            ]);

            if (request()->ajax()) {
                return $this->businessError('Failed to delete product', 500);
            }

            return back()->with('error', 'Failed to delete product');
        }
    }

    /**
     * Search products for AJAX requests
     */
    public function search(Request $request)
    {
        $this->authorizeTenantOperation('products.view');

        $query = $request->get('q', '');
        $limit = min($request->get('limit', 10), 50);

        $products = Product::where(function ($q) use ($query) {
                $q->where('product_name', 'like', "%{$query}%")
                  ->orWhere('sku', 'like', "%{$query}%")
                  ->orWhere('barcode', 'like', "%{$query}%");
            })
            ->where('product_status', 'publish')
            ->select('id', 'product_name', 'sku', 'barcode', 'sale_price', 'cost_price')
            ->limit($limit)
            ->get();

        return $this->businessResponse($products);
    }

    /**
     * Find product by barcode
     */
    public function findByBarcode(Request $request)
    {
        $this->authorizeTenantOperation('products.view');

        $barcode = $request->get('barcode');
        
        if (!$barcode) {
            return $this->businessError('Barcode is required', 400);
        }

        $product = Product::where('barcode', $barcode)
                         ->where('product_status', 'publish')
                         ->with(['category', 'brand'])
                         ->first();

        if (!$product) {
            return $this->businessError('Product not found', 404);
        }

        return $this->businessResponse($product);
    }

    /**
     * Bulk update products
     */
    public function bulkUpdate(Request $request)
    {
        $this->authorizeTenantOperation('products.edit');

        $validator = Validator::make($request->all(), [
            'product_ids' => 'required|array',
            'product_ids.*' => 'exists:products,id',
            'action' => 'required|in:publish,draft,trash,delete',
            'category_id' => 'nullable|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id'
        ]);

        if ($validator->fails()) {
            return $this->businessError('Validation failed', 422, $validator->errors());
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

            return $this->businessResponse(null, "Bulk {$action} completed successfully");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk product update failed', [
                'tenant_id' => $this->getCurrentTenant()->id,
                'user_id' => $this->getCurrentUser()->id,
                'action' => $action,
                'product_ids' => $productIds,
                'error' => $e->getMessage()
            ]);

            return $this->businessError('Bulk update failed', 500);
        }
    }
}
