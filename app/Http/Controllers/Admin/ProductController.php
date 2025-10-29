<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Tenant\BaseTenantController;
use App\Models\Product;
use App\Models\ProductCategory as Category;
use App\Models\Inventory;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ProductController extends BaseTenantController
{
    protected $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    /**
     * Display a listing of products
     */
    public function index(Request $request)
    {
        $tenant = $request->attributes->get('tenant');

        // Get filter options
        $categories = Category::all(); // TODO: Add tenant_id to product_categories table
        $statuses = [
            'active' => 'Hoạt động',
            'inactive' => 'Không hoạt động',
            'out_of_stock' => 'Hết hàng'
        ];

        return view('admin.products.index', compact('categories', 'statuses', 'tenant'));
    }

    /**
     * AJAX endpoint for loading products data
     */
    public function ajax(Request $request)
    {
        try {
            // Get pagination parameters
            $perPage = $request->get('per_page', 25);

            // Use ProductService to get products with filters
            // TenantScope will automatically filter by tenant_id
            $paginatedProducts = $this->productService->getProductsWithFilters($request, $perPage);

            // Get items and add total_stock to each product
            $products = collect($paginatedProducts->items())->map(function($product) {
                $product->total_stock = $product->inventory ? $product->inventory->quantity : 0;
                return $product;
            });

            return response()->json([
                'success' => true,
                'data' => $products,
                'recordsTotal' => $paginatedProducts->total(),
                'recordsFiltered' => $paginatedProducts->total(),
                'pagination' => [
                    'total' => $paginatedProducts->total(),
                    'per_page' => $paginatedProducts->perPage(),
                    'current_page' => $paginatedProducts->currentPage(),
                    'last_page' => $paginatedProducts->lastPage()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Products AJAX Error', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Lỗi tải dữ liệu sản phẩm: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show product detail for AJAX (detail panel)
     */
    public function detail(Request $request)
    {
        try {
            // Get productId from route parameters explicitly to avoid conflict with subdomain parameter
            $productId = request()->route('productId');

            $product = Product::with([
                'category',
                'inventory',
                'variants',
                'creator',
                'updater'
            ])
            ->where('id', $productId)
            ->firstOrFail();

            // If it's an AJAX request, return partial view for row expansion
            if (request()->ajax()) {
                return view('admin.products.partials.detail_panel', compact('product'));
            }

            // Otherwise return full detail page
            return view('admin.products.show', compact('product'));

        } catch (\Exception $e) {
            Log::error('Product Detail Error', ['error' => $e->getMessage(), 'product_id' => $productId ?? 'unknown']);

            if (request()->ajax()) {
                return response()->json(['error' => 'Sản phẩm không tồn tại'], 404);
            }

            return response()->json(['error' => 'Sản phẩm không tồn tại'], 404);
        }
    }
    
    /**
     * Show the form for creating a new product
     */
    public function create(Request $request)
    {
        $tenant = $request->attributes->get('tenant');
        $categories = Category::all(); // TODO: Add tenant_id to product_categories table

        return view('admin.products.create', compact('categories', 'tenant'));
    }
    
    /**
     * Store a newly created product
     */
    public function store(Request $request)
    {
        $tenant = $request->attributes->get('tenant');
        
        $request->validate([
            'product_name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:100|unique:products,sku',
            'barcode' => 'nullable|string|max:100|unique:products,barcode',
            'category_id' => 'nullable|exists:product_categories,id',

            'cost_price' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'product_description' => 'nullable|string',
            'product_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'weight' => 'nullable|numeric|min:0',
            'reorder_point' => 'nullable|integer|min:0',
            'product_status' => 'required|in:active,inactive,out_of_stock'
        ]);
        
        try {
            DB::beginTransaction();
            
            $productData = $request->all();
            $productData['tenant_id'] = $tenant->id;
            $productData['created_by'] = auth()->id();
            
            // Generate SKU if not provided
            if (empty($productData['sku'])) {
                $productData['sku'] = $this->generateSKU($tenant->id);
            }
            
            // Handle image upload
            if ($request->hasFile('product_image')) {
                $imagePath = $request->file('product_image')->store(
                    "tenants/{$tenant->id}/products",
                    'public'
                );
                $productData['product_image'] = $imagePath;
            }
            
            $product = Product::create($productData);
            
            // Create initial inventory record if stock quantity provided
            if ($request->filled('initial_stock')) {
                Inventory::create([
                    'product_id' => $product->id,
                    'branch_shop_id' => null, // Default inventory
                    'quantity' => $request->initial_stock,
                    'reserved_quantity' => 0,
                    'tenant_id' => $tenant->id
                ]);
            }
            
            DB::commit();
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Sản phẩm đã được tạo thành công!',
                    'redirect' => route('admin.products.index')
                ]);
            }
            
            return redirect()->route('admin.products.index')
                           ->with('success', 'Sản phẩm đã được tạo thành công!');
                           
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Product creation failed', [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage()
            ]);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi tạo sản phẩm!'
                ], 500);
            }
            
            return back()->with('error', 'Có lỗi xảy ra khi tạo sản phẩm!')
                         ->withInput();
        }
    }
    
    /**
     * Display the specified product
     */
    public function show(Request $request, Product $product)
    {
        $tenant = $request->attributes->get('tenant');
        
        // Ensure product belongs to current tenant
        if ($product->tenant_id !== $tenant->id) {
            abort(404);
        }
        
        $product->load(['category', 'brand', 'inventories.branchShop']);
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'product' => $product
            ]);
        }
        
        return view('admin.products.show', compact('product', 'tenant'));
    }
    
    /**
     * Show the form for editing the specified product
     */
    public function edit(Request $request, Product $product)
    {
        $tenant = $request->attributes->get('tenant');
        
        // Ensure product belongs to current tenant
        if ($product->tenant_id !== $tenant->id) {
            abort(404);
        }
        
        $categories = Category::all(); // TODO: Add tenant_id to product_categories table

        return view('admin.products.edit', compact('product', 'categories', 'tenant'));
    }
    
    /**
     * Update the specified product
     */
    public function update(Request $request, Product $product)
    {
        $tenant = $request->attributes->get('tenant');
        
        // Ensure product belongs to current tenant
        if ($product->tenant_id !== $tenant->id) {
            abort(404);
        }
        
        $request->validate([
            'product_name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:100|unique:products,sku,' . $product->id,
            'barcode' => 'nullable|string|max:100|unique:products,barcode,' . $product->id,
            'category_id' => 'nullable|exists:product_categories,id',

            'cost_price' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'product_description' => 'nullable|string',
            'product_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'weight' => 'nullable|numeric|min:0',
            'reorder_point' => 'nullable|integer|min:0',
            'product_status' => 'required|in:active,inactive,out_of_stock'
        ]);
        
        try {
            DB::beginTransaction();
            
            $productData = $request->all();
            $productData['updated_by'] = auth()->id();
            
            // Handle image upload
            if ($request->hasFile('product_image')) {
                // Delete old image
                if ($product->product_image) {
                    Storage::disk('public')->delete($product->product_image);
                }
                
                $imagePath = $request->file('product_image')->store(
                    "tenants/{$tenant->id}/products",
                    'public'
                );
                $productData['product_image'] = $imagePath;
            }
            
            $product->update($productData);
            
            DB::commit();
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Sản phẩm đã được cập nhật thành công!'
                ]);
            }
            
            return redirect()->route('admin.products.index')
                           ->with('success', 'Sản phẩm đã được cập nhật thành công!');
                           
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Product update failed', [
                'product_id' => $product->id,
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage()
            ]);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi cập nhật sản phẩm!'
                ], 500);
            }
            
            return back()->with('error', 'Có lỗi xảy ra khi cập nhật sản phẩm!')
                         ->withInput();
        }
    }
    
    /**
     * Remove the specified product
     */
    public function destroy(Request $request, Product $product)
    {
        $tenant = $request->attributes->get('tenant');
        
        // Ensure product belongs to current tenant
        if ($product->tenant_id !== $tenant->id) {
            abort(404);
        }
        
        try {
            DB::beginTransaction();
            
            // Check if product has related records
            if ($product->orderItems()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể xóa sản phẩm đã có đơn hàng!'
                ], 400);
            }
            
            // Delete product image
            if ($product->product_image) {
                Storage::disk('public')->delete($product->product_image);
            }
            
            $product->update(['deleted_by' => auth()->id()]);
            $product->delete();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Sản phẩm đã được xóa thành công!'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Product deletion failed', [
                'product_id' => $product->id,
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xóa sản phẩm!'
            ], 500);
        }
    }
    
    /**
     * Generate unique SKU for product
     */
    private function generateSKU($tenantId)
    {
        $prefix = 'PRD';
        $timestamp = now()->format('ymd');
        $random = str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        do {
            $sku = "{$prefix}{$timestamp}{$random}";
            $exists = Product::where('tenant_id', $tenantId)
                           ->where('sku', $sku)
                           ->exists();
            if ($exists) {
                $random = str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
            }
        } while ($exists);
        
        return $sku;
    }
}
