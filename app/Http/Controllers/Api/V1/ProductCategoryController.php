<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductCategoryController extends Controller
{
    /**
     * Display a listing of product categories
     */
    public function index(Request $request)
    {
        try {
            $query = ProductCategory::with(['parent', 'children']);
            
            // Apply filters
            if ($request->filled('parent_id')) {
                if ($request->parent_id === 'null' || $request->parent_id === '0') {
                    $query->whereNull('parent_id'); // Root categories
                } else {
                    $query->where('parent_id', $request->parent_id);
                }
            }
            
            if ($request->filled('is_active')) {
                $query->where('is_active', $request->boolean('is_active'));
            }
            
            if ($request->filled('show_in_menu')) {
                $query->where('show_in_menu', $request->boolean('show_in_menu'));
            }
            
            if ($request->filled('show_on_homepage')) {
                $query->where('show_on_homepage', $request->boolean('show_on_homepage'));
            }
            
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhere('slug', 'like', "%{$search}%");
                });
            }
            
            // Sorting
            $sortBy = $request->get('sort_by', 'sort_order');
            $sortDirection = $request->get('sort_direction', 'asc');
            
            if (in_array($sortBy, ['name', 'sort_order', 'created_at', 'updated_at'])) {
                $query->orderBy($sortBy, $sortDirection);
            } else {
                $query->orderBy('sort_order')->orderBy('name');
            }
            
            // Response format
            $format = $request->get('format', 'paginated'); // paginated, tree, flat
            
            if ($format === 'tree') {
                // Return hierarchical tree structure
                $categories = $query->whereNull('parent_id')->get();
                $tree = $this->buildCategoryTree($categories);
                
                return response()->json([
                    'status' => 'success',
                    'message' => 'Categories tree retrieved successfully',
                    'data' => $tree
                ], 200);
            } elseif ($format === 'flat') {
                // Return flat list without pagination
                $categories = $query->get()->map(function($category) {
                    return $this->formatCategoryData($category);
                });
                
                return response()->json([
                    'status' => 'success',
                    'message' => 'Categories retrieved successfully',
                    'data' => $categories,
                    'total' => $categories->count()
                ], 200);
            } else {
                // Default paginated response
                $perPage = $request->get('per_page', 15);
                $categories = $query->paginate($perPage);

                $formattedCategories = collect($categories->items())->map(function($category) {
                    return $this->formatCategoryData($category);
                });

                return response()->json([
                    'status' => 'success',
                    'message' => 'Categories retrieved successfully',
                    'data' => $formattedCategories,
                    'pagination' => [
                        'current_page' => $categories->currentPage(),
                        'last_page' => $categories->lastPage(),
                        'per_page' => $categories->perPage(),
                        'total' => $categories->total(),
                        'from' => $categories->firstItem(),
                        'to' => $categories->lastItem(),
                    ]
                ], 200);
            }
            
        } catch (\Exception $e) {
            Log::error('Product categories retrieval failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve categories',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created category
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'slug' => 'nullable|string|max:255|unique:product_categories,slug',
                'description' => 'nullable|string',
                'image' => 'nullable|string|max:500',
                'icon' => 'nullable|string|max:100',
                'color' => 'nullable|string|max:7',
                'parent_id' => 'nullable|exists:product_categories,id',
                'sort_order' => 'nullable|integer|min:0',
                'is_active' => 'nullable|boolean',
                'show_in_menu' => 'nullable|boolean',
                'show_on_homepage' => 'nullable|boolean',
                'meta_title' => 'nullable|string|max:255',
                'meta_description' => 'nullable|string|max:500',
                'meta_keywords' => 'nullable|string|max:500',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $validator->validated();
            
            // Generate slug if not provided
            if (empty($data['slug'])) {
                $data['slug'] = Str::slug($data['name']);
            }
            
            // Set defaults
            $data['is_active'] = $data['is_active'] ?? true;
            $data['show_in_menu'] = $data['show_in_menu'] ?? true;
            $data['show_on_homepage'] = $data['show_on_homepage'] ?? false;
            $data['sort_order'] = $data['sort_order'] ?? 0;

            $category = ProductCategory::create($data);
            $category->load(['parent', 'children']);

            return response()->json([
                'status' => 'success',
                'message' => 'Category created successfully',
                'data' => $this->formatCategoryData($category)
            ], 201);

        } catch (\Exception $e) {
            Log::error('Category creation failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create category',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified category
     */
    public function show($id)
    {
        try {
            $category = ProductCategory::with(['parent', 'children', 'products'])
                ->findOrFail($id);

            return response()->json([
                'status' => 'success',
                'message' => 'Category retrieved successfully',
                'data' => $this->formatCategoryData($category, true)
            ], 200);

        } catch (\Exception $e) {
            Log::error('Category retrieval failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Category not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified category
     */
    public function update(Request $request, $id)
    {
        try {
            $category = ProductCategory::findOrFail($id);
            
            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255',
                'slug' => 'nullable|string|max:255|unique:product_categories,slug,' . $id,
                'description' => 'nullable|string',
                'image' => 'nullable|string|max:500',
                'icon' => 'nullable|string|max:100',
                'color' => 'nullable|string|max:7',
                'parent_id' => 'nullable|exists:product_categories,id',
                'sort_order' => 'nullable|integer|min:0',
                'is_active' => 'nullable|boolean',
                'show_in_menu' => 'nullable|boolean',
                'show_on_homepage' => 'nullable|boolean',
                'meta_title' => 'nullable|string|max:255',
                'meta_description' => 'nullable|string|max:500',
                'meta_keywords' => 'nullable|string|max:500',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $validator->validated();
            
            // Prevent setting parent to self or descendant
            if (isset($data['parent_id']) && $data['parent_id']) {
                if ($data['parent_id'] == $category->id) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Category cannot be its own parent'
                    ], 422);
                }
                
                // Check if new parent is a descendant
                $descendants = $category->descendants()->pluck('id')->toArray();
                if (in_array($data['parent_id'], $descendants)) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Category cannot have its descendant as parent'
                    ], 422);
                }
            }

            $category->update($data);
            $category->load(['parent', 'children']);

            return response()->json([
                'status' => 'success',
                'message' => 'Category updated successfully',
                'data' => $this->formatCategoryData($category)
            ], 200);

        } catch (\Exception $e) {
            Log::error('Category update failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update category',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified category
     */
    public function destroy($id)
    {
        try {
            $category = ProductCategory::findOrFail($id);
            
            // Check if category has products
            $productsCount = $category->products()->count();
            if ($productsCount > 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Cannot delete category. It has {$productsCount} products assigned to it."
                ], 422);
            }
            
            // Check if category has children
            $childrenCount = $category->children()->count();
            if ($childrenCount > 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Cannot delete category. It has {$childrenCount} subcategories."
                ], 422);
            }

            $category->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Category deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Category deletion failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete category',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get category tree for select options
     */
    public function getTreeOptions(Request $request)
    {
        try {
            $selectedId = $request->get('selected_id');
            $excludeId = $request->get('exclude_id');

            $options = ProductCategory::getTreeOptions($selectedId, $excludeId);

            return response()->json([
                'status' => 'success',
                'message' => 'Category tree options retrieved successfully',
                'data' => $options
            ], 200);

        } catch (\Exception $e) {
            Log::error('Category tree options retrieval failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve category tree options',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get menu tree structure
     */
    public function getMenuTree()
    {
        try {
            $menuTree = ProductCategory::getMenuTree();
            $formattedTree = $this->buildCategoryTree($menuTree);

            return response()->json([
                'status' => 'success',
                'message' => 'Menu tree retrieved successfully',
                'data' => $formattedTree
            ], 200);

        } catch (\Exception $e) {
            Log::error('Menu tree retrieval failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve menu tree',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get category statistics
     */
    public function getStats()
    {
        try {
            $stats = [
                'total_categories' => ProductCategory::count(),
                'active_categories' => ProductCategory::where('is_active', true)->count(),
                'root_categories' => ProductCategory::whereNull('parent_id')->count(),
                'menu_categories' => ProductCategory::where('show_in_menu', true)->count(),
                'homepage_categories' => ProductCategory::where('show_on_homepage', true)->count(),
                'categories_with_products' => ProductCategory::has('products')->count(),
                'empty_categories' => ProductCategory::doesntHave('products')->count(),
            ];

            return response()->json([
                'status' => 'success',
                'message' => 'Category statistics retrieved successfully',
                'data' => $stats
            ], 200);

        } catch (\Exception $e) {
            Log::error('Category statistics retrieval failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve category statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Format category data for API response
     */
    private function formatCategoryData($category, $includeProducts = false)
    {
        $data = [
            'id' => $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
            'description' => $category->description,
            'image' => $category->image,
            'icon' => $category->icon,
            'color' => $category->color,
            'parent_id' => $category->parent_id,
            'sort_order' => $category->sort_order,
            'is_active' => $category->is_active,
            'show_in_menu' => $category->show_in_menu,
            'show_on_homepage' => $category->show_on_homepage,
            'meta_title' => $category->meta_title,
            'meta_description' => $category->meta_description,
            'meta_keywords' => $category->meta_keywords,
            'created_at' => $category->created_at,
            'updated_at' => $category->updated_at,

            // Relationships
            'parent' => $category->parent ? [
                'id' => $category->parent->id,
                'name' => $category->parent->name,
                'slug' => $category->parent->slug,
            ] : null,

            'children_count' => $category->children->count(),
            'products_count' => $category->products->count(),

            // Computed attributes
            'is_root' => $category->isRoot(),
            'is_leaf' => $category->isLeaf(),
            'has_children' => $category->hasChildren(),
            'level' => $category->level,
            'breadcrumb' => $category->breadcrumb,
        ];

        if ($includeProducts) {
            $data['products'] = $category->products->map(function($product) {
                return [
                    'id' => $product->id,
                    'product_name' => $product->product_name,
                    'sku' => $product->sku,
                    'product_status' => $product->product_status,
                    'sale_price' => $product->sale_price,
                ];
            });
        }

        return $data;
    }

    /**
     * Build hierarchical category tree
     */
    private function buildCategoryTree($categories)
    {
        return $categories->map(function($category) {
            $data = $this->formatCategoryData($category);

            if ($category->children && $category->children->count() > 0) {
                $data['children'] = $this->buildCategoryTree($category->children);
            } else {
                $data['children'] = [];
            }

            return $data;
        });
    }
}
