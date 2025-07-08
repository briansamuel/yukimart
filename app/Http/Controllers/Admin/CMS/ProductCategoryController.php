<?php

namespace App\Http\Controllers\Admin\CMS;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ProductCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('admin.product-categories.index');
    }

    /**
     * Get categories data for DataTables
     */
    public function getData(Request $request)
    {
        try {
            $query = ProductCategory::with('parent', 'children')
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
                'error' => 'Lỗi khi tải dữ liệu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $parentCategories = ProductCategory::whereNull('parent_id')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
            
        return view('admin.product-categories.create', compact('parentCategories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:product_categories,slug',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:product_categories,id',
            'status' => 'required|in:active,inactive',
            'sort_order' => 'nullable|integer|min:0',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            'name.required' => __('product_category.name_required'),
            'slug.unique' => __('product_category.slug_unique'),
            'parent_id.exists' => __('product_category.parent_not_found'),
            'status.required' => __('product_category.status_required'),
            'image.image' => __('product_category.image_invalid'),
            'image.max' => __('product_category.image_too_large'),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => __('product_category.validation_failed'),
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = $request->all();
            
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
                $maxOrder = ProductCategory::where('parent_id', $data['parent_id'])->max('sort_order') ?? 0;
                $data['sort_order'] = $maxOrder + 1;
            }

            $category = ProductCategory::create($data);

            return response()->json([
                'success' => true,
                'message' => __('product_category.created_successfully'),
                'data' => $category
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('product_category.create_failed') . ': ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(ProductCategory $productCategory)
    {
        $productCategory->load(['parent', 'children', 'products']);
        return view('admin.product-categories.show', compact('productCategory'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProductCategory $productCategory)
    {
        $parentCategories = ProductCategory::whereNull('parent_id')
            ->where('status', 'active')
            ->where('id', '!=', $productCategory->id)
            ->orderBy('name')
            ->get();
            
        return view('admin.product-categories.edit', compact('productCategory', 'parentCategories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProductCategory $productCategory)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:product_categories,slug,' . $productCategory->id,
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:product_categories,id',
            'status' => 'required|in:active,inactive',
            'sort_order' => 'nullable|integer|min:0',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            'name.required' => __('product_category.name_required'),
            'slug.unique' => __('product_category.slug_unique'),
            'parent_id.exists' => __('product_category.parent_not_found'),
            'status.required' => __('product_category.status_required'),
            'image.image' => __('product_category.image_invalid'),
            'image.max' => __('product_category.image_too_large'),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => __('product_category.validation_failed'),
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = $request->all();
            
            // Generate slug if not provided
            if (empty($data['slug'])) {
                $data['slug'] = Str::slug($data['name']);
            }

            // Handle image upload
            if ($request->hasFile('image')) {
                // Delete old image
                if ($productCategory->image) {
                    \Storage::delete('public/' . $productCategory->image);
                }
                
                $image = $request->file('image');
                $imageName = time() . '_' . Str::slug($data['name']) . '.' . $image->getClientOriginalExtension();
                $image->storeAs('public/product-categories', $imageName);
                $data['image'] = 'product-categories/' . $imageName;
            }

            $productCategory->update($data);

            return response()->json([
                'success' => true,
                'message' => __('product_category.updated_successfully'),
                'data' => $productCategory
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('product_category.update_failed') . ': ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProductCategory $productCategory)
    {
        try {
            // Check if category has products
            if ($productCategory->products()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => __('product_category.has_products')
                ], 422);
            }

            // Check if category has children
            if ($productCategory->children()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => __('product_category.has_children')
                ], 422);
            }

            // Delete image
            if ($productCategory->image) {
                \Storage::delete('public/' . $productCategory->image);
            }

            $productCategory->delete();

            return response()->json([
                'success' => true,
                'message' => __('product_category.deleted_successfully')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('product_category.delete_failed') . ': ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get parent categories for dropdown
     */
    public function getParentCategories()
    {
        try {
            $categories = ProductCategory::whereNull('parent_id')
                ->where('is_active', 1)
                ->orderBy('name')
                ->get(['id', 'name']);

            return response()->json([
                'success' => true,
                'data' => $categories
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tải danh mục cha: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update sort order
     */
    public function updateSortOrder(Request $request)
    {
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

        try {
            foreach ($request->items as $item) {
                ProductCategory::where('id', $item['id'])
                    ->update(['sort_order' => $item['sort_order']]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật thứ tự thành công'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi cập nhật thứ tự: ' . $e->getMessage()
            ], 500);
        }
    }
}
