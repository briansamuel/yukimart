<?php

namespace App\Http\Controllers\Business;

use App\Models\ProductCategory;
use App\Services\TenantContextService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Business Product Category Controller
 * 
 * Handles tenant-specific product category operations
 */
class ProductCategoryController extends BaseBusinessController
{
    /**
     * Create a new controller instance
     */
    public function __construct(TenantContextService $tenantContext)
    {
        parent::__construct($tenantContext);
    }

    /**
     * Get categories for AJAX requests (for filters, dropdowns, etc.)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function ajax(Request $request)
    {
        try {
            // ProductCategory doesn't have tenant_id, it's shared across all tenants
            // Build query
            $query = ProductCategory::query();

            // Filter by active status
            if ($request->filled('is_active')) {
                $query->where('is_active', $request->is_active);
            }

            // Filter by parent
            if ($request->filled('parent_id')) {
                $query->where('parent_id', $request->parent_id);
            }

            // Search by name
            if ($request->filled('search')) {
                $query->where('name', 'like', '%' . $request->search . '%');
            }

            // Order by position and name
            $query->orderBy('position', 'asc')
                  ->orderBy('name', 'asc');

            // Get format type (flat, tree, paginated)
            $format = $request->get('format', 'flat');

            if ($format === 'paginated') {
                // Paginated response
                $perPage = $request->get('per_page', 50);
                $categories = $query->paginate($perPage);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Categories retrieved successfully',
                    'data' => $categories->items(),
                    'meta' => [
                        'current_page' => $categories->currentPage(),
                        'last_page' => $categories->lastPage(),
                        'per_page' => $categories->perPage(),
                        'total' => $categories->total(),
                    ]
                ]);
            } elseif ($format === 'tree') {
                // Tree structure response
                $categories = $query->get();
                $tree = $this->buildCategoryTree($categories);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Category tree retrieved successfully',
                    'data' => $tree
                ]);
            } else {
                // Flat list response (default)
                $categories = $query->get();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Categories retrieved successfully',
                    'data' => $categories
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Product Category AJAX Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi tải danh mục sản phẩm'
            ], 500);
        }
    }

    /**
     * Build category tree structure
     * 
     * @param \Illuminate\Support\Collection $categories
     * @param int|null $parentId
     * @param int $level
     * @return array
     */
    protected function buildCategoryTree($categories, $parentId = null, $level = 0)
    {
        $tree = [];

        foreach ($categories as $category) {
            if ($category->parent_id == $parentId) {
                $categoryData = $category->toArray();
                $categoryData['level'] = $level;
                $categoryData['children'] = $this->buildCategoryTree($categories, $category->id, $level + 1);
                $tree[] = $categoryData;
            }
        }

        return $tree;
    }

    /**
     * Get flat list of categories with level indication
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFlat(Request $request)
    {
        try {
            // ProductCategory doesn't have tenant_id, it's shared across all tenants
            $categories = ProductCategory::where('is_active', 1)
                ->orderBy('sort_order', 'asc')
                ->orderBy('name', 'asc')
                ->get();

            // Add level information for each category
            $flatCategories = [];
            foreach ($categories as $category) {
                $level = $this->getCategoryLevel($category);
                $flatCategories[] = [
                    'id' => $category->id,
                    'name' => $category->name,
                    'parent_id' => $category->parent_id,
                    'level' => $level,
                    'is_active' => $category->is_active,
                ];
            }

            return response()->json([
                'status' => 'success',
                'data' => $flatCategories
            ]);

        } catch (\Exception $e) {
            Log::error('Get Flat Categories Error', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi tải danh mục'
            ], 500);
        }
    }

    /**
     * Get category level (depth in tree)
     * 
     * @param ProductCategory $category
     * @param int $level
     * @return int
     */
    protected function getCategoryLevel($category, $level = 0)
    {
        if (!$category->parent_id) {
            return $level;
        }

        $parent = ProductCategory::find($category->parent_id);
        if (!$parent) {
            return $level;
        }

        return $this->getCategoryLevel($parent, $level + 1);
    }
}

