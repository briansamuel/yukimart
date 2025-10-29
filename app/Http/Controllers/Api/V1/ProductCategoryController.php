<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use App\Http\Traits\ApiOptimizationTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductCategoryController extends Controller
{
    use ApiOptimizationTrait;
    /**
     * Display a listing of product categories with aggressive caching
     */
    public function index(Request $request)
    {
        $startTime = microtime(true);

        try {
            // Generate cache key for this request
            $cacheKey = $this->generateCacheKey($request, 'product_categories');

            // Response format
            $format = $request->get('format', 'paginated');

            // For tree format, use aggressive caching (30 minutes)
            if ($format === 'tree') {
                $cachedResponse = Cache::remember($cacheKey . '_tree', 1800, function() use ($request) {
                    return $this->generateTreeResponse($request);
                });

                $response = response()->json($cachedResponse);
                return $this->addPerformanceHeaders($response, $startTime);
            }

            // For other formats, use shorter caching (10 minutes)
            $cachedResponse = $this->cacheResponse($cacheKey, null, 10);

            if ($cachedResponse) {
                $response = response()->json($cachedResponse);
                return $this->addPerformanceHeaders($response, $startTime);
            }

            // Parse include parameter for dynamic relationship loading
            $includes = $this->parseCategoryIncludes($request->get('include', 'parent,children'));

            // Base query with optimized eager loading
            $query = ProductCategory::query();

            // Apply dynamic includes
            if (!empty($includes)) {
                $query->with($includes);
            }

            // Apply filters with optimization
            $this->applyCategoryFilters($query, $request);

            // Apply sorting
            $this->applyCategorySorting($query, $request);

            // Handle different response formats
            if ($format === 'flat') {
                // Return flat list without pagination
                $categories = $query->get();

                // Parse fields for field selection
                $fields = $this->parseFields($request->get('fields'));

                // Apply field selection if specified
                $categoryData = $categories->map(function($category) use ($fields) {
                    $data = $this->formatCategoryData($category);
                    return !empty($fields) ? $this->applyFieldSelection($data, $fields) : $data;
                });

                // Prepare response data
                $responseData = [
                    'status' => 'success',
                    'message' => 'Categories retrieved successfully',
                    'data' => $categoryData,
                    'meta' => [
                        'total' => $categoryData->count(),
                        'execution_time' => round((microtime(true) - $startTime) * 1000, 2) . 'ms'
                    ]
                ];
            } else {
                // Paginated response
                $categories = $this->optimizePagination($query, $request);

                // Parse fields for field selection
                $fields = $this->parseFields($request->get('fields'));

                // Transform to formatted data
                $categoryData = $categories->getCollection()->map(function($category) use ($fields) {
                    $data = $this->formatCategoryData($category);
                    return !empty($fields) ? $this->applyFieldSelection($data, $fields) : $data;
                });

                // Update collection with formatted data
                $categories->setCollection($categoryData);

                // Prepare response data
                $responseData = [
                    'status' => 'success',
                    'message' => 'Categories retrieved successfully',
                    'data' => $categories->items(),
                    'meta' => [
                        'current_page' => $categories->currentPage(),
                        'last_page' => $categories->lastPage(),
                        'per_page' => $categories->perPage(),
                        'total' => $categories->total(),
                        'from' => $categories->firstItem(),
                        'to' => $categories->lastItem(),
                        'execution_time' => round((microtime(true) - $startTime) * 1000, 2) . 'ms'
                    ]
                ];
            }

            // Cache the response
            $this->cacheResponse($cacheKey, $responseData, 10); // 10 minutes cache

            // Create response with optimization headers
            $response = response()->json($responseData, 200);
            $response = $this->addPerformanceHeaders($response, $startTime);
            $response = $this->addRateLimitHeaders($response, 120, 1); // 120 requests per minute

            return $response;
            
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
     * Get menu tree structure with aggressive caching
     */
    public function getMenuTree()
    {
        $startTime = microtime(true);

        try {
            // Use aggressive caching for menu tree (60 minutes)
            $cacheKey = 'product_categories_menu_tree';

            $menuData = Cache::remember($cacheKey, 3600, function() {
                $menuTree = ProductCategory::getMenuTree();
                return $this->buildCategoryTreeOptimized($menuTree);
            });

            // Prepare response data
            $responseData = [
                'status' => 'success',
                'message' => 'Menu tree retrieved successfully',
                'data' => $menuData,
                'meta' => [
                    'cached' => Cache::has($cacheKey),
                    'execution_time' => round((microtime(true) - $startTime) * 1000, 2) . 'ms'
                ]
            ];

            // Create response with optimization headers
            $response = response()->json($responseData, 200);
            $response = $this->addPerformanceHeaders($response, $startTime);

            return $response;

        } catch (\Exception $e) {
            Log::error('Menu tree retrieval failed: ' . $e->getMessage());
            return $this->errorResponse('Failed to retrieve menu tree', 500);
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

    /**
     * Generate tree response with caching
     */
    private function generateTreeResponse($request)
    {
        $startTime = microtime(true);

        // Parse include parameter for dynamic relationship loading
        $includes = $this->parseCategoryIncludes($request->get('include', 'parent,children'));

        // Base query with optimized eager loading
        $query = ProductCategory::query();

        // Apply dynamic includes
        if (!empty($includes)) {
            $query->with($includes);
        }

        // Apply filters
        $this->applyCategoryFilters($query, $request);

        // Apply sorting
        $this->applyCategorySorting($query, $request);

        // Get root categories and build tree
        $categories = $query->whereNull('parent_id')->get();
        $tree = $this->buildCategoryTreeOptimized($categories);

        return [
            'status' => 'success',
            'message' => 'Categories tree retrieved successfully',
            'data' => $tree,
            'meta' => [
                'execution_time' => round((microtime(true) - $startTime) * 1000, 2) . 'ms'
            ]
        ];
    }

    /**
     * Parse category includes for dynamic relationship loading
     */
    private function parseCategoryIncludes($includeString)
    {
        if (empty($includeString)) {
            return [];
        }

        $availableIncludes = [
            'parent' => 'parent:id,name,slug,parent_id',
            'children' => 'children:id,name,slug,parent_id,sort_order',
            'products' => 'products:id,product_name,sku,category_id',
            'allChildren' => 'allChildren:id,name,slug,parent_id,sort_order'
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
     * Apply filters to category query with optimization
     */
    private function applyCategoryFilters($query, $request)
    {
        // Parent ID filter
        if ($request->filled('parent_id')) {
            if ($request->parent_id === 'null' || $request->parent_id === '0') {
                $query->whereNull('parent_id'); // Root categories
            } else {
                $query->where('parent_id', $request->parent_id);
            }
        }

        // Active status filter
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Menu visibility filter
        if ($request->filled('show_in_menu')) {
            $query->where('show_in_menu', $request->boolean('show_in_menu'));
        }

        // Homepage visibility filter
        if ($request->filled('show_on_homepage')) {
            $query->where('show_on_homepage', $request->boolean('show_on_homepage'));
        }

        // Level filter
        if ($request->filled('level')) {
            $query->where('level', $request->level);
        }

        // Enhanced search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $searchFields = $request->get('search_fields', ['name', 'description', 'slug']);

            $query->where(function($q) use ($search, $searchFields) {
                foreach ($searchFields as $field) {
                    if (in_array($field, ['name', 'description', 'slug'])) {
                        $q->orWhere($field, 'like', "%{$search}%");
                    }
                }
            });
        }
    }

    /**
     * Apply sorting to category query
     */
    private function applyCategorySorting($query, $request)
    {
        $sortBy = $request->get('sort_by', 'sort_order');
        $sortDirection = $request->get('sort_direction', 'asc');

        // Validate sort field
        $validSortFields = ['name', 'sort_order', 'level', 'created_at', 'updated_at'];

        if (!in_array($sortBy, $validSortFields)) {
            $sortBy = 'sort_order';
        }

        // Validate sort direction
        if (!in_array(strtolower($sortDirection), ['asc', 'desc'])) {
            $sortDirection = 'asc';
        }

        $query->orderBy($sortBy, $sortDirection);

        // Secondary sort by name for consistency
        if ($sortBy !== 'name') {
            $query->orderBy('name', 'asc');
        }
    }

    /**
     * Build optimized category tree with better performance
     */
    private function buildCategoryTreeOptimized($categories)
    {
        return $categories->map(function($category) {
            $data = $this->formatCategoryData($category);

            // Recursively build children if they exist
            if ($category->children && $category->children->count() > 0) {
                $data['children'] = $this->buildCategoryTreeOptimized($category->children);
            } else {
                $data['children'] = [];
            }

            return $data;
        });
    }

    /**
     * Get category statistics with caching
     */
    public function statistics()
    {
        $startTime = microtime(true);

        try {
            // Use aggressive caching for statistics (30 minutes)
            $cacheKey = 'product_categories_statistics';

            $stats = Cache::remember($cacheKey, 1800, function() {
                return $this->calculateCategoryStatistics();
            });

            // Prepare response data
            $responseData = [
                'status' => 'success',
                'message' => 'Category statistics retrieved successfully',
                'data' => $stats,
                'meta' => [
                    'cached' => Cache::has($cacheKey),
                    'execution_time' => round((microtime(true) - $startTime) * 1000, 2) . 'ms'
                ]
            ];

            // Create response with optimization headers
            $response = response()->json($responseData, 200);
            $response = $this->addPerformanceHeaders($response, $startTime);

            return $response;

        } catch (\Exception $e) {
            Log::error('Category statistics failed: ' . $e->getMessage());
            return $this->errorResponse('Failed to retrieve category statistics', 500);
        }
    }

    /**
     * Calculate category statistics
     */
    private function calculateCategoryStatistics()
    {
        $totalCategories = ProductCategory::count();
        $activeCategories = ProductCategory::where('is_active', true)->count();
        $inactiveCategories = ProductCategory::where('is_active', false)->count();
        $rootCategories = ProductCategory::whereNull('parent_id')->count();
        $menuCategories = ProductCategory::where('show_in_menu', true)->count();
        $homepageCategories = ProductCategory::where('show_on_homepage', true)->count();

        // Get statistics by level
        $byLevel = ProductCategory::groupBy('level')
            ->selectRaw('level, count(*) as count')
            ->get()
            ->keyBy('level')
            ->map(function($item) {
                return [
                    'level' => $item->level,
                    'count' => (int) $item->count
                ];
            });

        // Get categories with products count
        $categoriesWithProducts = ProductCategory::has('products')->count();
        $categoriesWithoutProducts = $totalCategories - $categoriesWithProducts;

        return [
            'total_categories' => $totalCategories,
            'active_categories' => $activeCategories,
            'inactive_categories' => $inactiveCategories,
            'root_categories' => $rootCategories,
            'menu_categories' => $menuCategories,
            'homepage_categories' => $homepageCategories,
            'categories_with_products' => $categoriesWithProducts,
            'categories_without_products' => $categoriesWithoutProducts,
            'by_level' => $byLevel,
            'activity_percentage' => $totalCategories > 0 ? round(($activeCategories / $totalCategories) * 100, 2) : 0
        ];
    }
}
