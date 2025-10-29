<?php

namespace App\Http\Controllers\Tenant\Catalog;

use App\Http\Controllers\Tenant\BaseTenantController;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Exception;

class CategoryController extends BaseTenantController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('tenant.catalog.categories.index');
    }

    /**
     * Get categories data for DataTables
     */
    public function getData(Request $request)
    {
        try {
            $tenantId = $this->getCurrentTenantId();

            $query = ProductCategory::where('tenant_id', $tenantId)
                ->with('parent', 'children')
                ->withCount('products');

            // Apply search
            if ($request->has('search') && !empty($request->search['value'])) {
                $search = $request->search['value'];
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhere('slug', 'like', "%{$search}%");
                });
            }

            // Apply filters
            if ($request->has('status') && $request->status !== '') {
                $query->where('is_active', 1);
            }

            if ($request->has('parent_id') && $request->parent_id !== '') {
                if ($request->parent_id === '0') {
                    $query->whereNull('parent_id');
                } else {
                    $query->where('parent_id', $request->parent_id);
                }
            }

            // Get total count before pagination
            $totalRecords = $query->count();

            // Apply ordering
            $orderColumn = $request->order[0]['column'] ?? 0;
            $orderDirection = $request->order[0]['dir'] ?? 'asc';
            
            $columns = ['id', 'name', 'parent_id', 'status', 'products_count', 'sort_order', 'created_at'];
            if (isset($columns[$orderColumn])) {
                $query->orderBy($columns[$orderColumn], $orderDirection);
            } else {
                $query->orderBy('sort_order')->orderBy('name');
            }

            // Apply pagination
            $start = $request->start ?? 0;
            $length = $request->length ?? 10;
            $categories = $query->skip($start)->take($length)->get();

            $data = $categories->map(function($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'parent_name' => $category->parent ? $category->parent->name : '',
                    'status' => $category->is_active,
                    'status_badge' => $category->status_badge,
                    'products_count' => $category->products_count,
                    'sort_order' => $category->sort_order,
                    'created_at' => $category->created_at->format('d/m/Y H:i'),
                    'breadcrumb' => $category->breadcrumb,
                    'level' => $category->level,
                ];
            });

            return response()->json([
                'draw' => intval($request->draw),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $totalRecords,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Lỗi khi tải d�?liệu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get single category detail
     */
    public function show()
    {
        try {
            $id = request()->route('id');
            $tenantId = $this->getCurrentTenantId();

            $category = ProductCategory::where('tenant_id', $tenantId)
                ->where('id', $id)
                ->with(['parent', 'children'])
                ->withCount('products')
                ->first();

            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy danh mục'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
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
                    'products_count' => $category->products_count,
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Error in Category show: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $tenantId = $this->getCurrentTenantId();

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'slug' => [
                    'nullable',
                    'string',
                    'max:255',
                    Rule::unique('product_categories')->where(function ($query) use ($tenantId) {
                        return $query->where('tenant_id', $tenantId);
                    })
                ],
                'description' => 'nullable|string',
                'parent_id' => 'nullable|exists:product_categories,id',
                'is_active' => 'nullable|boolean',
                'show_in_menu' => 'nullable|boolean',
                'show_on_homepage' => 'nullable|boolean',
                'sort_order' => 'nullable|integer|min:0',
                'meta_title' => 'nullable|string|max:255',
                'meta_description' => 'nullable|string|max:500',
                'meta_keywords' => 'nullable|string|max:255',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ], [
                'name.required' => 'Vui lòng nhập tên danh mục',
                'slug.unique' => 'Slug đã tồn tại',
                'parent_id.exists' => 'Danh mục cha không tồn tại',
                'image.image' => 'File không hợp lệ',
                'image.max' => 'Kích thước ảnh quá lớn (tối đa 2MB)',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dữ liệu không hợp lệ',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $data = $request->all();
            $data['tenant_id'] = $tenantId;

            // Generate slug if not provided
            if (empty($data['slug'])) {
                $data['slug'] = Str::slug($data['name']);
            }

            // Handle image upload
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '_' . Str::slug($data['name']) . '.' . $image->getClientOriginalExtension();
                $image->storeAs('public/product-categories', $imageName);
                $data['image'] = 'product-categories/' . $imageName;
            }

            // Set default sort order
            if (empty($data['sort_order'])) {
                $maxOrder = ProductCategory::where('tenant_id', $tenantId)
                    ->where('parent_id', $data['parent_id'] ?? null)
                    ->max('sort_order') ?? 0;
                $data['sort_order'] = $maxOrder + 1;
            }

            $category = ProductCategory::create($data);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tạo danh mục thành công',
                'data' => $category
            ]);

        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Error in Category store: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tạo danh mục: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        try {
            $id = request()->route('id');
            $tenantId = $this->getCurrentTenantId();

            $category = ProductCategory::where('tenant_id', $tenantId)
                ->where('id', $id)
                ->first();

            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy danh mục'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'slug' => [
                    'nullable',
                    'string',
                    'max:255',
                    Rule::unique('product_categories')->where(function ($query) use ($tenantId) {
                        return $query->where('tenant_id', $tenantId);
                    })->ignore($category->id)
                ],
                'description' => 'nullable|string',
                'parent_id' => 'nullable|exists:product_categories,id',
                'is_active' => 'nullable|boolean',
                'show_in_menu' => 'nullable|boolean',
                'show_on_homepage' => 'nullable|boolean',
                'sort_order' => 'nullable|integer|min:0',
                'meta_title' => 'nullable|string|max:255',
                'meta_description' => 'nullable|string|max:500',
                'meta_keywords' => 'nullable|string|max:255',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ], [
                'name.required' => 'Vui lòng nhập tên danh mục',
                'slug.unique' => 'Slug đã tồn tại',
                'parent_id.exists' => 'Danh mục cha không tồn tại',
                'image.image' => 'File không hợp lệ',
                'image.max' => 'Kích thước ảnh quá lớn (tối đa 2MB)',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dữ liệu không hợp lệ',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Check if trying to set itself as parent
            if ($request->parent_id == $category->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể chọn chính nó làm danh mục cha'
                ], 422);
            }

            DB::beginTransaction();

            $data = $request->all();

            // Generate slug if not provided
            if (empty($data['slug'])) {
                $data['slug'] = Str::slug($data['name']);
            }

            // Handle image upload
            if ($request->hasFile('image')) {
                // Delete old image
                if ($category->image) {
                    Storage::delete('public/' . $category->image);
                }

                $image = $request->file('image');
                $imageName = time() . '_' . Str::slug($data['name']) . '.' . $image->getClientOriginalExtension();
                $image->storeAs('public/product-categories', $imageName);
                $data['image'] = 'product-categories/' . $imageName;
            }

            $category->update($data);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật danh mục thành công',
                'data' => $category
            ]);

        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Error in Category update: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi cập nhật: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy()
    {
        try {
            $id = request()->route('id');
            $tenantId = $this->getCurrentTenantId();

            $category = ProductCategory::where('tenant_id', $tenantId)
                ->where('id', $id)
                ->first();

            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy danh mục'
                ], 404);
            }

            // Check if category has products
            if ($category->products()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể xóa danh mục đang có sản phẩm'
                ], 422);
            }

            // Check if category has children
            if ($category->children()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể xóa danh mục đang có danh mục con'
                ], 422);
            }

            // Delete image
            if ($category->image) {
                Storage::delete('public/' . $category->image);
            }

            $category->delete();

            return response()->json([
                'success' => true,
                'message' => 'Xóa danh mục thành công'
            ]);

        } catch (Exception $e) {
            Log::error('Error in Category destroy: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xóa: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get parent categories for dropdown
     */
    public function getParentCategories(Request $request)
    {
        try {
            $tenantId = $this->getCurrentTenantId();

            $query = ProductCategory::where('tenant_id', $tenantId)
                ->whereNull('parent_id')
                ->where('is_active', 1);

            // Search
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where('name', 'like', "%{$search}%");
            }

            $categories = $query->orderBy('name')
                ->limit(20)
                ->get(['id', 'name']);

            return response()->json([
                'success' => true,
                'data' => $categories->map(function($cat) {
                    return [
                        'id' => $cat->id,
                        'text' => $cat->name,
                        'name' => $cat->name,
                    ];
                })
            ]);

        } catch (Exception $e) {
            Log::error('Error in Category getParentCategories: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'data' => []
            ]);
        }
    }

    /**
     * Get all categories tree for dropdown
     */
    public function getCategoriesTree(Request $request)
    {
        try {
            $tenantId = $this->getCurrentTenantId();
            $excludeId = $request->input('exclude_id');

            $categories = ProductCategory::where('tenant_id', $tenantId)
                ->where('is_active', 1)
                ->when($excludeId, function($query) use ($excludeId) {
                    return $query->where('id', '!=', $excludeId);
                })
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $this->buildCategoryTree($categories)
            ]);

        } catch (Exception $e) {
            Log::error('Error in Category getCategoriesTree: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'data' => []
            ]);
        }
    }

    /**
     * Build category tree structure
     */
    private function buildCategoryTree($categories, $parentId = null, $level = 0)
    {
        $tree = [];

        foreach ($categories as $category) {
            if ($category->parent_id == $parentId) {
                $tree[] = [
                    'id' => $category->id,
                    'text' => str_repeat('— ', $level) . $category->name,
                    'name' => $category->name,
                    'level' => $level,
                ];

                // Recursively add children
                $children = $this->buildCategoryTree($categories, $category->id, $level + 1);
                $tree = array_merge($tree, $children);
            }
        }

        return $tree;
    }

    /**
     * Update sort order
     */
    public function updateSortOrder(Request $request)
    {
        try {
            $tenantId = $this->getCurrentTenantId();

            $validator = Validator::make($request->all(), [
                'items' => 'required|array',
                'items.*.id' => 'required|exists:product_categories,id',
                'items.*.sort_order' => 'required|integer|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dữ liệu không hợp lệ',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            foreach ($request->items as $item) {
                ProductCategory::where('tenant_id', $tenantId)
                    ->where('id', $item['id'])
                    ->update(['sort_order' => $item['sort_order']]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật thứ tự thành công'
            ]);

        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Error in Category updateSortOrder: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi cập nhật thứ tự: ' . $e->getMessage()
            ], 500);
        }
    }
}
