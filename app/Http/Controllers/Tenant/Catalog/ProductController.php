<?php

namespace App\Http\Controllers\Tenant\Catalog;

use App\Http\Controllers\Tenant\BaseTenantController;
use App\Services\ValidationService;
use App\Services\ProductService;
use App\Services\ProductVariantService;
use App\Services\ProductUnitService;
use App\Services\LogsUserService;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use App\Models\ProductVariant;
use App\Models\ProductVariantAttribute;
use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use App\Helpers\Message;
use App\Helpers\ArrayHelper;
use App\Models\Product;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProductController extends BaseTenantController
{
    protected $request;
    protected $validator;
    protected $productService;
    protected $variantService;
    protected $unitService;
    protected $language;

    function __construct(
        Request $request,
        ValidationService $validator,
        ProductService $productService,
        ProductVariantService $variantService,
        ProductUnitService $unitService
    )
    {
        parent::__construct();
        $this->request = $request;
        $this->validator = $validator;
        $this->productService = $productService;
        $this->variantService = $variantService;
        $this->unitService = $unitService;
        $this->language = App::currentLocale();
        session()->start();
        session()->put('RF.subfolder', "products");
        session()->put('RF.thumbnailWidth', 355);
        session()->put('RF.thumbnailHeight', 345);
    }

    /**
     * METHOD index - View List Products
     *
     * @return void
     */
    public function index()
    {
        // Check permission
        if (!$this->userCan('products.view')) {
            abort(403, 'Không có quyền truy cập trang n?y.');
        }

        return view('admin.products.index');
    }

    /**
     * METHOD ajaxGetList - Ajax Get List Products
     *
     * @return void
     */
    public function ajaxGetList()
    {
        $params = $this->request->all();
        $result = $this->productService->getList($params);

        return response()->json($result);
    }

    /**
     * METHOD add - VIEW ADD PRODUCT
     *
     * @return void
     */
    public function add()
    {
        $categories = \App\Models\ProductCategory::getTreeOptions();
        $attributes = ProductAttribute::getVariationOptions();
        return view('admin.products.create', compact('categories', 'attributes'));
    }

    /**
     * METHOD addAction - Add Product Action
     *
     * @return json
     */
    public function addAction()
    {
        DB::beginTransaction();
        try {
            $params = $this->request->all();
            $user = auth()->user();
            $params['created_by_user'] = $user->id;
            $params['updated_by_user'] = $user->id;

            $validator = $this->validator->make($params, 'add_product_fields');

            if ($validator->fails()) {
                $errors = $validator->errors();
                $errorMessages = [];

                // Create detailed error messages
                foreach ($errors->all() as $error) {
                    $errorMessages[] = $error;
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed. Please check the following errors:',
                    'errors' => $errorMessages,
                    'detailed_errors' => $errors->toArray()
                ], 422);
            }

            // Check if SKU already exists
            if ($this->productService->checkSkuExists($params['sku'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'SKU already exists in the system. Please use a different SKU.',
                    'errors' => ['The SKU "' . $params['sku'] . '" is already taken by another product.']
                ], 422);
            }

            $add = $this->productService->insert($params);

            if ($add) {
                // Handle variant creation if product type is variable
                if (isset($params['product_type']) && $params['product_type'] === 'variable' && isset($params['variant_attributes'])) {
                    try {
                        $product = $this->productService->findByKey('id', $add);
                        $this->variantService->createVariants($product, $params['variant_attributes']);
                    } catch (\Exception $e) {
                        Log::error('Variant creation failed: ' . $e->getMessage());
                        // Continue with success response as product was created
                    }
                }

                $log['action'] = __('admin.logs.add_type_with_id', ['type' => 'Product', 'id' => $add]);
                $log['content'] = json_encode($params);
                $log['ip'] = $this->request->ip();
                LogsUserService::add($log);

                $data['success'] = true;
                $data['message'] = __('admin.products.create_product_success');
                $data['product_id'] = $add;
                $data['redirect_url'] = route('products.list');
            } else {
                $data['success'] = false;
                $data['message'] = 'Failed to create product. Please check your input data and try again.';
                $data['errors'] = ['Product creation failed due to database error.'];
            }

            DB::commit();
            return response()->json($data);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Product creation failed: ' . $e->getMessage(), [
                'params' => $params ?? [],
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while creating the product.',
                'errors' => [
                    'System error: ' . $e->getMessage(),
                    'Please contact administrator if this problem persists.'
                ]
            ], 500);
        }
    }

    /**
     * METHOD deleteMany - Delete Array Products with IDs
     *
     * @return json
     */
    public function deleteMany()
    {
        try {
            $params = $this->request->only('ids', 'total');
            if (!isset($params['ids'])) {
                return response()->json(Message::get(26, $lang = '', []), 400);
            }
            $delete = $this->productService->deleteMany($params['ids']);
            if (!$delete) {
                return response()->json(Message::get(12, $lang = '', []), 400);
            }

            $log['action'] = trans('admin.logs.delete_type_success_with_ids', ['type' => 'products', 'ids' => implode(", ", $params['ids'])]);
            $log['content'] = json_encode($params);
            $log['ip'] = $this->request->ip();
            LogsUserService::add($log);

            $data['success'] = true;
            $data['message'] = trans('admin.products.delete_many_products_success', ['total' => $params['total']]);

        } catch (\Exception $e) {
            $data['message'] = trans('admin.products.error_exception');
        }
        return response()->json($data);
    }

    /**
     * METHOD delete - Delete single product
     *
     * @return json
     */
    public function delete($id)
    {
        try {
            $delete = $this->productService->delete($id);
            if ($delete) {
                $log['action'] = trans('admin.logs.delete_type_success_with_ids', ['type' => 'product', 'ids' => $id]);
                $log['content'] = '';
                $log['ip'] = $this->request->ip();
                LogsUserService::add($log);

                $data['success'] = true;
                $data['message'] = trans('admin.products.delete_product_success');
            } else {
                $data['message'] = trans('admin.products.error_exception');
            }

        } catch (Exception $e) {
            $data['message'] = trans('admin.products.error_exception');
        }

        return response()->json($data);
    }

    /**
     * METHOD show - product detail view or JSON
     *
     * NOTE: Due to subdomain routing with {tenant} parameter, we cannot use route model binding.
     * The route has 2 parameters: {tenant} (subdomain) and {product} (product ID).
     * We must explicitly get product ID from request()->route('product').
     *
     * Laravel resource route: GET /admin/products/{product}
     * Full route: {tenant}.yukimart.local/admin/products/{product}
     *
     * @return view|json
     */
    public function show(Request $request)
    {
        // Get product ID from route parameter (NOT from method parameter injection)
        // because {tenant} subdomain parameter conflicts with route model binding
        $productId = $request->route('product');

        if (!$productId) {
            abort(404);
        }

        $tenant = $request->attributes->get('tenant');

        // Manually query product with tenant scope
        $productModel = \App\Models\Product::where('id', $productId)
                       ->where('tenant_id', $tenant->id)
                       ->with(['images' => function($query) {
                           $query->orderBy('sort_order');
                       }])
                       ->first();

        if (!$productModel) {
            abort(404);
        }

        // If AJAX request, return JSON
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'product' => [
                    'id' => $productModel->id,
                    'product_name' => $productModel->product_name,
                    'sku' => $productModel->sku,
                    'barcode' => $productModel->barcode,
                    'category_id' => $productModel->category_id,
                    'brand' => $productModel->brand,
                    'cost_price' => $productModel->cost_price,
                    'sale_price' => $productModel->sale_price,
                    'points' => $productModel->points,
                    'weight' => $productModel->weight,
                    'location' => $productModel->location,
                    'product_thumbnail' => $productModel->product_thumbnail ? asset('storage/' . $productModel->product_thumbnail) : null,
                    'product_description' => $productModel->product_description,
                    'product_content' => $productModel->product_content,
                    'images' => $productModel->images->map(function($image) {
                        return [
                            'id' => $image->id,
                            'image_path' => asset('storage/' . $image->image_path),
                            'sort_order' => $image->sort_order
                        ];
                    })
                ]
            ]);
        }

        // Otherwise return view
        return view('admin.products.show', ['product' => $productModel]);
    }




    /**
     * METHOD edit - edit view
     *
     * @return view
     */
    public function edit($id = 0)
    {
        // Check permission
        if (!$this->userCan('products.edit')) {
            abort(403, 'Không có quyền chỉnh sửa sản phẩm.');
        }

        if (!$id) {
            abort(404);
        }

        $product = $this->productService->findByKey('id', $id);

        if (!$product) {
            abort(404);
        }

        $categories = \App\Models\ProductCategory::getTreeOptions();
        $attributes = ProductAttribute::getVariationOptions();
        $variants = $product->isVariable() ? $this->variantService->getVariantCombinations($product) : [];

        return view('admin.products.edit', compact('product', 'categories', 'attributes', 'variants'));
    }

    /**
     * METHOD editAction - Edit Product Action
     *
     * @return json
     */
    public function editAction($id = 0)
    {
        // Check permission
        if (!$this->userCan('products.edit')) {
            return response()->json([
                'success' => false,
                'message' => 'Không có quyền chỉnh sửa sản phẩm.'
            ], 403);
        }

        DB::beginTransaction();
        if (!$id) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid product ID provided.',
                'errors' => ['Product ID is required and must be a valid number.']
            ], 400);
        }

        try {
            // Check if product exists
            $existingProduct = $this->productService->findByKey('id', $id);
            if (!$existingProduct) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found.',
                    'errors' => ['The product with ID ' . $id . ' does not exist in the system.']
                ], 404);
            }

            $params = $this->request->all();
            $params = ArrayHelper::removeArrayNull($params);

            $validator = $this->validator->make($params, 'edit_product_fields');

            if ($validator->fails()) {
                $errors = $validator->errors();
                $errorMessages = [];

                // Create detailed error messages
                foreach ($errors->all() as $error) {
                    $errorMessages[] = $error;
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed. Please check the following errors:',
                    'errors' => $errorMessages,
                    'detailed_errors' => $errors->toArray()
                ], 422);
            }

            // Check if SKU already exists (excluding current product)
            if (isset($params['sku']) && $this->productService->checkSkuExists($params['sku'], $id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'SKU already exists in the system. Please use a different SKU.',
                    'errors' => ['The SKU "' . $params['sku'] . '" is already taken by another product.']
                ], 422);
            }

            $params['updated_by_user'] = auth()->user()->id;
            $update = $this->productService->update($id, $params);

            if (!$update) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update product. Please check your input data and try again.',
                    'errors' => ['Product update failed due to database error.']
                ], 500);
            }

            $log['action'] = __('admin.logs.update_type_with_id', ['type' => 'Product', 'id' => $id]);
            $log['content'] = json_encode($params);
            $log['ip'] = $this->request->ip();
            LogsUserService::add($log);

            $data['success'] = true;
            $data['message'] = __('admin.products.update_product_success');

            DB::commit();
            return response()->json($data);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Product update failed: ' . $e->getMessage(), [
                'product_id' => $id,
                'params' => $params ?? [],
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while updating the product.',
                'errors' => [
                    'System error: ' . $e->getMessage(),
                    'Please contact administrator if this problem persists.'
                ]
            ], 500);
        }
    }

    /**
     * METHOD editManyAction - Edit Array Products with IDs
     *
     * @return json
     */
    public function editManyAction()
    {
        try {
            $params = $this->request->only(['status', 'ids', 'total']);
            $params = ArrayHelper::removeArrayNull($params);
            if (!isset($params['ids'])) {
                return response()->json(Message::get(26, $lang = '', []), 400);
            }
            $update = $this->productService->updateMany($params['ids'], ['product_status' => $params['status']]);
            if (!$update) {
                return response()->json(Message::get(12, $lang = '', []), 400);
            }

            $log['action'] = "Updated products with IDs = " . implode(", ", $params['ids']) . " successfully";
            $log['content'] = json_encode($params);
            $log['ip'] = $this->request->ip();
            LogsUserService::add($log);

            $data['success'] = true;
            $data['message'] = "Updated " . $params['total'] . " products successfully";
            return response()->json($data);
        } catch (\Exception $e) {
            $data['message'] = __('admin.products.update_product_error');
            return response()->json($data);
        }
    }

    /**
     * METHOD uploadImage - Upload image for products
     *
     * @return json
     */
    public function uploadImage()
    {
        try {
            if (!$this->request->hasFile('upload')) {
                return response()->json(['success' => false, 'message' => 'No file uploaded'], 400);
            }

            $file = $this->request->file('upload');

            // Validate file
            if (!$file->isValid()) {
                return response()->json(['success' => false, 'message' => 'Invalid file'], 400);
            }

            // Check file type
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/jpg'];
            $mimeType = $file->getMimeType();
            if (!in_array($mimeType, $allowedTypes)) {
                return response()->json(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, GIF, and WebP are allowed. Received: ' . $mimeType], 400);
            }

            // Check file size (max 5MB)
            if ($file->getSize() > 5 * 1024 * 1024) {
                return response()->json(['success' => false, 'message' => 'File too large. Maximum size is 5MB.'], 400);
            }

            // Ensure storage directory exists
            $storageDir = storage_path('app/public/products');
            if (!file_exists($storageDir)) {
                mkdir($storageDir, 0755, true);
            }

            // Generate unique filename
            $extension = $file->getClientOriginalExtension();
            if (empty($extension)) {
                $extension = 'jpg'; // Default extension
            }
            $filename = time() . '_' . uniqid() . '.' . $extension;

            // Store file in public/storage/products directory
            $path = $file->storeAs('products', $filename, 'public');

            // Return success response
            return response()->json([
                'success' => true,
                'path' => '/storage/' . $path,
                'url' => asset('storage/' . $path),
                'filename' => $filename
            ]);

        } catch (Exception $e) {
            Log::error('Upload failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * METHOD duplicate - Duplicate a product
     *
     * @param int $id
     * @return json
     */
    public function duplicate($id)
    {
        DB::beginTransaction();
        try {
            // Check if product exists
            $originalProduct = $this->productService->findByKey('id', $id);
            if (!$originalProduct) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found.',
                    'errors' => ['The product with ID ' . $id . ' does not exist.']
                ], 404);
            }

            // Duplicate the product
            $duplicatedProduct = $this->productService->duplicate($id);

            if (!$duplicatedProduct) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to duplicate product.',
                    'errors' => ['Product duplication failed due to database error.']
                ], 500);
            }

            // Log the action
            $log['action'] = "Duplicated product ID {$id} to new product ID {$duplicatedProduct->id}";
            $log['content'] = json_encode(['original_id' => $id, 'new_id' => $duplicatedProduct->id]);
            $log['ip'] = $this->request->ip();
            LogsUserService::add($log);

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Product duplicated successfully.',
                'data' => [
                    'original_id' => $id,
                    'new_id' => $duplicatedProduct->id,
                    'new_product_name' => $duplicatedProduct->product_name,
                    'new_sku' => $duplicatedProduct->sku
                ]
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Product duplication failed: ' . $e->getMessage(), [
                'product_id' => $id,
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while duplicating the product.',
                'errors' => [
                    'System error: ' . $e->getMessage(),
                    'Please contact administrator if this problem persists.'
                ]
            ], 500);
        }
    }

    /**
     * METHOD changeStatus - Change product status
     *
     * @param int $id
     * @return json
     */
    public function changeStatus($id)
    {
        DB::beginTransaction();
        try {
            // Check if product exists
            $product = $this->productService->findByKey('id', $id);
            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found.',
                    'errors' => ['The product with ID ' . $id . ' does not exist.']
                ], 404);
            }

            // Validate status
            $params = $this->request->only(['status']);
            $allowedStatuses = ['publish', 'draft', 'pending', 'trash'];

            if (!isset($params['status']) || !in_array($params['status'], $allowedStatuses)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid status provided.',
                    'errors' => ['Status must be one of: ' . implode(', ', $allowedStatuses)]
                ], 422);
            }

            // Update product status
            $updateData = [
                'product_status' => $params['status'],
                'updated_by_user' => auth()->user()->id
            ];

            $update = $this->productService->updatePartial($id, $updateData);

            if (!$update) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update product status.',
                    'errors' => ['Status update failed due to database error.']
                ], 500);
            }

            // Log the action
            $log['action'] = "Changed product ID {$id} status from '{$product->product_status}' to '{$params['status']}'";
            $log['content'] = json_encode(['product_id' => $id, 'old_status' => $product->product_status, 'new_status' => $params['status']]);
            $log['ip'] = $this->request->ip();
            LogsUserService::add($log);

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Product status updated successfully.',
                'data' => [
                    'product_id' => $id,
                    'old_status' => $product->product_status,
                    'new_status' => $params['status']
                ]
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Product status change failed: ' . $e->getMessage(), [
                'product_id' => $id,
                'params' => $params ?? [],
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while updating product status.',
                'errors' => [
                    'System error: ' . $e->getMessage(),
                    'Please contact administrator if this problem persists.'
                ]
            ], 500);
        }
    }

    /**
     * METHOD getHistory - Get product history
     *
     * @param int $id
     * @return json
     */
    public function getHistory($id)
    {
        try {
            // Check if product exists
            $product = $this->productService->findByKey('id', $id);
            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found.',
                    'errors' => ['The product with ID ' . $id . ' does not exist.']
                ], 404);
            }

            // Get product history
            $history = $this->productService->getHistory($id);

            return response()->json([
                'success' => true,
                'message' => 'Product history retrieved successfully.',
                'data' => [
                    'product_id' => $id,
                    'product_name' => $product->product_name,
                    'history' => $history
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Product history retrieval failed: ' . $e->getMessage(), [
                'product_id' => $id,
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while retrieving product history.',
                'errors' => [
                    'System error: ' . $e->getMessage(),
                    'Please contact administrator if this problem persists.'
                ]
            ], 500);
        }
    }

    /**
     * METHOD quickEdit - Quick edit product fields
     *
     * @param int $id
     * @return json
     */
    public function quickEdit($id)
    {
        DB::beginTransaction();
        try {
            // Check if product exists
            $product = $this->productService->findByKey('id', $id);
            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found.',
                    'errors' => ['The product with ID ' . $id . ' does not exist.']
                ], 404);
            }

            // Get allowed quick edit fields
            $allowedFields = ['product_name', 'sku', 'sale_price', 'regular_price', 'cost_price', 'product_status', 'reorder_point'];
            $params = $this->request->only($allowedFields);

            if (empty($params)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No valid fields provided for quick edit.',
                    'errors' => ['Please provide at least one field to update: ' . implode(', ', $allowedFields)]
                ], 422);
            }

            // Basic validation
            if (isset($params['sku']) && $this->productService->checkSkuExists($params['sku'], $id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'SKU already exists.',
                    'errors' => ['The SKU "' . $params['sku'] . '" is already taken by another product.']
                ], 422);
            }

            // Add update metadata
            $params['updated_by_user'] = auth()->user()->id;

            // Update product
            $update = $this->productService->updatePartial($id, $params);

            if (!$update) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update product.',
                    'errors' => ['Quick edit failed due to database error.']
                ], 500);
            }

            // Log the action
            $log['action'] = "Quick edited product ID {$id}";
            $log['content'] = json_encode(['product_id' => $id, 'updated_fields' => array_keys($params)]);
            $log['ip'] = $this->request->ip();
            LogsUserService::add($log);

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully.',
                'data' => [
                    'product_id' => $id,
                    'updated_fields' => array_keys($params)
                ]
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Product quick edit failed: ' . $e->getMessage(), [
                'product_id' => $id,
                'params' => $params ?? [],
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while updating the product.',
                'errors' => [
                    'System error: ' . $e->getMessage(),
                    'Please contact administrator if this problem persists.'
                ]
            ], 500);
        }
    }

    /**
     * METHOD adjustStock - Adjust product stock
     *
     * @param int $id
     * @return json
     */
    public function adjustStock($id)
    {
        try {
            $params = $this->request->all();

            // Validate required fields
            if (!isset($params['adjustment_type']) || !isset($params['quantity'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Adjustment type and quantity are required.',
                    'errors' => ['Missing required fields: adjustment_type, quantity']
                ], 422);
            }

            // Check if product exists
            $product = $this->productService->findByKey('id', $id);
            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found.',
                    'errors' => ['The product with ID ' . $id . ' does not exist.']
                ], 404);
            }

            // Adjust stock using ProductService
            $result = $this->productService->adjustStock($id, $params);

            if ($result['success']) {
                // Log the action
                $log['action'] = "Adjusted stock for product ID {$id}";
                $log['content'] = json_encode($params);
                $log['ip'] = $this->request->ip();
                LogsUserService::add($log);

                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                    'data' => $result['data']
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                    'errors' => $result['errors'] ?? []
                ], 422);
            }

        } catch (\Exception $e) {
            Log::error('Stock adjustment failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to adjust stock.',
                'errors' => ['Stock adjustment failed due to system error: ' . $e->getMessage()]
            ], 500);
        }
    }

    /**
     * Get available attributes for variant creation
     */
    public function getAttributes()
    {
        try {
            $attributes = $this->variantService->getAvailableAttributes();

            return response()->json([
                'success' => true,
                'data' => $attributes
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load attributes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a new attribute
     */
    public function storeAttribute()
    {
        try {
            $this->request->validate([
                'name' => 'required|string|max:255|unique:product_attributes,name',
                'type' => 'required|in:select,color,text,number',
                'description' => 'nullable|string',
                'is_variation' => 'boolean',
                'is_visible' => 'boolean',
                'default_values' => 'nullable|array',
                'default_values.*' => 'string|max:255'
            ]);

            $attribute = ProductAttribute::create([
                'name' => $this->request->name,
                'slug' => \Str::slug($this->request->name),
                'type' => $this->request->type,
                'description' => $this->request->description,
                'is_required' => false,
                'is_variation' => $this->request->boolean('is_variation', true),
                'is_visible' => $this->request->boolean('is_visible', true),
                'sort_order' => ProductAttribute::max('sort_order') + 1,
                'status' => 'active'
            ]);

            // Create default values if provided
            if ($this->request->has('default_values') && is_array($this->request->default_values)) {
                foreach ($this->request->default_values as $index => $value) {
                    if (!empty(trim($value))) {
                        ProductAttributeValue::create([
                            'attribute_id' => $attribute->id,
                            'value' => trim($value),
                            'slug' => \Str::slug(trim($value)),
                            'price_adjustment' => 0,
                            'sort_order' => $index + 1,
                            'status' => 'active'
                        ]);
                    }
                }
            }

            // Load the attribute with its values
            $attribute->load('values');

            return response()->json([
                'success' => true,
                'message' => 'Tạo thuộc tính th?nh công',
                'data' => $attribute
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể tạo thuộc tính: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store attribute value for an existing attribute
     */
    public function storeAttributeValue($attributeId)
    {
        try {
            $attribute = ProductAttribute::find($attributeId);
            if (!$attribute) {
                return response()->json([
                    'success' => false,
                    'message' => 'Thuộc tính không tồn tại'
                ], 404);
            }

            $this->request->validate([
                'value' => 'required|string|max:255',
                'price_adjustment' => 'nullable|numeric',
                'color_code' => 'nullable|string|max:7'
            ]);

            // Check if value already exists for this attribute
            $existingValue = ProductAttributeValue::where('attribute_id', $attributeId)
                ->where('value', $this->request->value)
                ->first();

            if ($existingValue) {
                return response()->json([
                    'success' => false,
                    'message' => 'Giá trị này đã tồn tại cho thuộc tính'
                ], 422);
            }

            $attributeValue = ProductAttributeValue::create([
                'attribute_id' => $attributeId,
                'value' => $this->request->value,
                'slug' => \Str::slug($this->request->value),
                'price_adjustment' => $this->request->price_adjustment ?? 0,
                'color_code' => $this->request->color_code,
                'sort_order' => ProductAttributeValue::where('attribute_id', $attributeId)->max('sort_order') + 1,
                'status' => 'active'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Tạo giá trị thuộc tính thành công',
                'data' => $attributeValue
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể tạo giá trị thuộc tính: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get attribute values for an attribute
     */
    public function getAttributeValues($attributeId)
    {
        try {
            $attribute = ProductAttribute::find($attributeId);
            if (!$attribute) {
                return response()->json([
                    'success' => false,
                    'message' => 'Thuộc tính không tồn tại'
                ], 404);
            }

            $values = ProductAttributeValue::where('attribute_id', $attributeId)
                ->where('status', 'active')
                ->orderBy('sort_order')
                ->get(['id', 'value', 'color_code', 'price_adjustment']);

            return response()->json([
                'success' => true,
                'data' => $values
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể lấy danh sách giá trị ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create variants from form data (new UI)
     */
    public function createVariantsFromForm($id)
    {
        try {
            $product = $this->productService->findByKey('id', $id);
            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found'
                ], 404);
            }

            $params = $this->request->all();

            if (!isset($params['variants']) || empty($params['variants'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'No variant data provided'
                ], 422);
            }

            // Create new attribute values first if any
            $newValuesCreated = 0;
            if (isset($params['new_attribute_values']) && !empty($params['new_attribute_values'])) {
                foreach ($params['new_attribute_values'] as $newValue) {
                    $this->createAttributeValueIfNotExists($newValue['attribute_id'], $newValue['value']);
                    $newValuesCreated++;
                }
            }

            $variants = $this->variantService->createVariantsFromFormData($product, $params['variants']);

            return response()->json([
                'success' => true,
                'message' => 'Variants created successfully',
                'data' => [
                    'variants_count' => count($variants),
                    'new_values_created' => $newValuesCreated,
                    'variants' => $this->variantService->getVariantCombinations($product)
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Variant creation from form failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create variants: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create attribute value if it doesn't exist
     */
    private function createAttributeValueIfNotExists($attributeId, $value)
    {
        $existingValue = ProductAttributeValue::where('attribute_id', $attributeId)
            ->where('value', $value)
            ->first();

        if (!$existingValue) {
            ProductAttributeValue::create([
                'attribute_id' => $attributeId,
                'value' => $value,
                'slug' => \Str::slug($value),
                'price_adjustment' => 0,
                'sort_order' => ProductAttributeValue::where('attribute_id', $attributeId)->max('sort_order') + 1,
                'status' => 'active'
            ]);
        }

        return $existingValue;
    }

    /**
     * Create variants for a product
     */
    public function createVariants($id)
    {
        try {
            $product = $this->productService->findByKey('id', $id);
            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found'
                ], 404);
            }

            $params = $this->request->all();

            if (!isset($params['attributes']) || empty($params['attributes'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'No attributes selected for variant creation'
                ], 422);
            }

            $variants = $this->variantService->createVariants($product, $params['attributes']);

            return response()->json([
                'success' => true,
                'message' => 'Variants created successfully',
                'data' => [
                    'variants_count' => count($variants),
                    'variants' => $this->variantService->getVariantCombinations($product)
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Variant creation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create variants: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get variants for a product
     */
    public function getVariants($id)
    {
        try {
            $product = $this->productService->findByKey('id', $id);
            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found'
                ], 404);
            }

            $variants = $this->variantService->getVariantCombinations($product);

            return response()->json([
                'success' => true,
                'data' => $variants
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load variants: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a specific variant
     */
    public function updateVariant($productId, $variantId)
    {
        try {
            $variant = ProductVariant::find($variantId);
            if (!$variant || $variant->parent_product_id != $productId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Variant not found'
                ], 404);
            }

            $params = $this->request->all();
            $updatedVariant = $this->variantService->updateVariant($variant, $params);

            return response()->json([
                'success' => true,
                'message' => 'Variant updated successfully',
                'data' => $updatedVariant
            ]);

        } catch (\Exception $e) {
            Log::error('Variant update failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update variant: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a specific variant
     */
    public function deleteVariant($productId, $variantId)
    {
        try {
            $variant = ProductVariant::find($variantId);
            if (!$variant || $variant->parent_product_id != $productId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Variant not found'
                ], 404);
            }

            $this->variantService->deleteVariant($variant);

            return response()->json([
                'success' => true,
                'message' => 'Variant deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Variant deletion failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete variant: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk update variant prices
     */
    public function bulkUpdateVariantPrices($id)
    {
        try {
            $product = $this->productService->findByKey('id', $id);
            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found'
                ], 404);
            }

            $params = $this->request->all();

            if (!isset($params['variants']) || empty($params['variants'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'No variant price data provided'
                ], 422);
            }

            $this->variantService->bulkUpdatePrices($product, $params['variants']);

            return response()->json([
                'success' => true,
                'message' => 'Variant prices updated successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Bulk variant price update failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update variant prices: ' . $e->getMessage()
            ], 500);
        }
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

            $product = \App\Models\Product::with([
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
        $categories = \App\Models\ProductCategory::all();

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
            'brand' => 'nullable|string|max:255',
            'cost_price' => 'nullable|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'product_description' => 'nullable|string',
            'product_content' => 'nullable|string',
            'product_thumbnail' => 'nullable|string',
            'weight' => 'nullable|integer|min:0',
            'reorder_point' => 'nullable|integer|min:0',
            'max_stock' => 'nullable|integer|min:0',
            'points' => 'nullable|integer|min:0',
            'location' => 'nullable|string|max:255',
            'initial_stock' => 'nullable|integer|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Handle main thumbnail upload (base64)
            $thumbnailPath = null;
            if ($request->filled('product_thumbnail')) {
                $thumbnailPath = $this->saveBase64Image($request->product_thumbnail, 'products/thumbnails');
            }

            // Prepare product data
            $productData = [
                'tenant_id' => $tenant->id,
                'product_name' => $request->product_name,
                'product_slug' => \Illuminate\Support\Str::slug($request->product_name),
                'sku' => $request->sku ?: $this->generateSKU($tenant->id),
                'barcode' => $request->barcode,
                'category_id' => $request->category_id,
                'brand' => $request->input('brand_select') ?: $request->brand,
                'cost_price' => $request->cost_price ?? 0,
                'sale_price' => $request->sale_price ?? 0,
                'product_description' => $request->product_description,
                'product_content' => $request->product_content,
                'product_thumbnail' => $thumbnailPath,
                'weight' => $request->weight ?? 0,
                'reorder_point' => $request->reorder_point ?? 0,
                'points' => $request->enable_points ? ($request->points ?? 0) : 0,
                'location' => $request->location,
                'product_status' => 'publish',
                'product_type' => 'simple',
                'product_feature' => 0,
                'language' => 'vi',
                'created_by_user' => auth()->id(),
            ];

            $product = \App\Models\Product::create($productData);

            // Handle additional images (base64)
            if ($request->filled('product_images')) {
                $images = $request->product_images;
                foreach ($images as $index => $imageData) {
                    if (!empty($imageData)) {
                        $imagePath = $this->saveBase64Image($imageData, 'products/images');
                        if ($imagePath) {
                            \App\Models\ProductImage::create([
                                'product_id' => $product->id,
                                'tenant_id' => $tenant->id,
                                'image_path' => $imagePath,
                                'sort_order' => $index,
                            ]);
                        }
                    }
                }
            }

            // Create initial inventory record if stock quantity provided
            if ($request->filled('initial_stock')) {
                \App\Models\Inventory::create([
                    'product_id' => $product->id,
                    'branch_shop_id' => null,
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
                    'product' => $product
                ]);
            }

            return redirect()->route('admin.products.index')
                           ->with('success', 'Sản phẩm đã được tạo thành công!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Product creation failed', [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi tạo sản phẩm: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Có lỗi xảy ra khi tạo sản phẩm!')
                         ->withInput();
        }
    }

    /**
     * Update the specified product
     */
    public function update(Request $request, $id)
    {
        $tenant = $request->attributes->get('tenant');

        // Manually query product with tenant scope
        $product = \App\Models\Product::where('id', $id)
                         ->where('tenant_id', $tenant->id)
                         ->firstOrFail();

        $request->validate([
            'product_name' => 'required|string|max:255',
            'sku' => 'nullable|string|max:100|unique:products,sku,' . $product->id,
            'barcode' => 'nullable|string|max:100|unique:products,barcode,' . $product->id,
            'category_id' => 'nullable|exists:product_categories,id',
            'brand' => 'nullable|string|max:255',
            'cost_price' => 'nullable|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'product_description' => 'nullable|string',
            'product_content' => 'nullable|string',
            'product_thumbnail' => 'nullable|string',
            'weight' => 'nullable|integer|min:0',
            'reorder_point' => 'nullable|integer|min:0',
            'max_stock' => 'nullable|integer|min:0',
            'points' => 'nullable|integer|min:0',
            'location' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            // Handle main thumbnail upload (base64)
            if ($request->filled('product_thumbnail')) {
                // Check if it's a base64 image (starts with data:image)
                if (str_starts_with($request->product_thumbnail, 'data:image')) {
                    // Delete old thumbnail if exists
                    if ($product->product_thumbnail) {
                        \Storage::disk('public')->delete($product->product_thumbnail);
                    }
                    $thumbnailPath = $this->saveBase64Image($request->product_thumbnail, 'products/thumbnails');
                } else {
                    // It's already a path, keep it
                    $thumbnailPath = $request->product_thumbnail;
                }
            } else {
                $thumbnailPath = $product->product_thumbnail;
            }

            // Prepare update data
            $productData = [
                'product_name' => $request->product_name,
                'product_slug' => \Illuminate\Support\Str::slug($request->product_name),
                'sku' => $request->sku ?: $product->sku,
                'barcode' => $request->barcode,
                'category_id' => $request->category_id,
                'brand' => $request->input('brand_select') ?: $request->brand,
                'cost_price' => $request->cost_price ?? 0,
                'sale_price' => $request->sale_price ?? 0,
                'product_description' => $request->product_description,
                'product_content' => $request->product_content,
                'product_thumbnail' => $thumbnailPath,
                'weight' => $request->weight ?? 0,
                'reorder_point' => $request->reorder_point ?? 0,
                'points' => $request->enable_points ? ($request->points ?? 0) : 0,
                'location' => $request->location,
            ];

            $product->update($productData);

            // Handle additional images - delete old ones first
            $product->images()->each(function($image) {
                \Storage::disk('public')->delete($image->image_path);
                $image->delete();
            });

            // Save new additional images (base64)
            if ($request->filled('product_images')) {
                $images = $request->product_images;
                foreach ($images as $index => $imageData) {
                    if (!empty($imageData)) {
                        // Check if it's base64
                        if (str_starts_with($imageData, 'data:image')) {
                            $imagePath = $this->saveBase64Image($imageData, 'products/images');
                        } else {
                            // It's already a path
                            $imagePath = $imageData;
                        }

                        if ($imagePath) {
                            \App\Models\ProductImage::create([
                                'product_id' => $product->id,
                                'tenant_id' => $product->tenant_id,
                                'image_path' => $imagePath,
                                'sort_order' => $index,
                            ]);
                        }
                    }
                }
            }

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Sản phẩm đã được cập nhật thành công!',
                    'product' => $product
                ]);
            }

            return redirect()->route('admin.products.index')
                           ->with('success', 'Sản phẩm đã được cập nhật thành công!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Product update failed', [
                'product_id' => $product->id,
                'tenant_id' => $product->tenant_id,
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
    public function destroy(Request $request, $id)
    {
        $tenant = $request->attributes->get('tenant');

        // Manually query product with tenant scope
        $product = \App\Models\Product::where('id', $id)
                         ->where('tenant_id', $tenant->id)
                         ->firstOrFail();

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
                \Storage::disk('public')->delete($product->product_image);
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
            $exists = \App\Models\Product::where('tenant_id', $tenantId)
                           ->where('sku', $sku)
                           ->exists();
            if ($exists) {
                $random = str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
            }
        } while ($exists);

        return $sku;
    }

    /**
     * Save base64 image to storage
     *
     * @param string $base64Image Base64 encoded image data
     * @param string $directory Directory to save image in
     * @return string|null Path to saved image or null on failure
     */
    private function saveBase64Image($base64Image, $directory = 'products')
    {
        try {
            // Check if it's a valid base64 image
            if (!str_starts_with($base64Image, 'data:image')) {
                return null;
            }

            // Extract image data
            preg_match('/^data:image\/(\w+);base64,/', $base64Image, $matches);
            if (!$matches) {
                return null;
            }

            $extension = $matches[1];
            $imageData = substr($base64Image, strpos($base64Image, ',') + 1);
            $imageData = base64_decode($imageData);

            if ($imageData === false) {
                return null;
            }

            // Generate unique filename
            $filename = uniqid() . '_' . time() . '.' . $extension;
            $path = $directory . '/' . $filename;

            // Save to storage
            \Storage::disk('public')->put($path, $imageData);

            return $path;
        } catch (\Exception $e) {
            Log::error('Failed to save base64 image', [
                'error' => $e->getMessage(),
                'directory' => $directory
            ]);
            return null;
        }
    }

    /**
     * Save product units
     * API endpoint for JavaScript
     */
    public function saveProductUnits(Request $request, $productId)
    {
        try {
            $tenantId = $this->getCurrentTenantId();

            // Verify product belongs to tenant
            $product = Product::where('id', $productId)
                ->where('tenant_id', $tenantId)
                ->first();

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sản phẩm không tồn tại'
                ], 404);
            }

            $unitsData = $request->input('units', []);

            // Use ProductUnitService to save units
            $result = $this->unitService->saveProductUnits($product, $unitsData);

            return response()->json($result, $result['success'] ? 200 : 500);

        } catch (Exception $e) {
            Log::error('Error in saveProductUnits controller: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi lưu đơn vị tính'
            ], 500);
        }
    }

    /**
     * Save product variants
     * API endpoint for JavaScript
     */
    public function saveProductVariants(Request $request, $productId)
    {
        DB::beginTransaction();
        try {
            $tenantId = $this->getCurrentTenantId();

            // Verify product belongs to tenant
            $product = Product::where('id', $productId)
                ->where('tenant_id', $tenantId)
                ->first();

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sản phẩm không tồn tại'
                ], 404);
            }

            $variantsData = $request->input('variants', []);

            // Delete existing variants
            $product->variants()->delete();

            $createdVariants = [];

            // Create new variants
            foreach ($variantsData as $variantData) {
                // Create variant
                $variant = $product->variants()->create([
                    'tenant_id' => $tenantId,
                    'variant_name' => $variantData['attributeValuesText'] ?? '',
                    'sku' => $variantData['sku'] ?? '',
                    'barcode' => $variantData['barcode'] ?? '',
                    'cost_price' => $variantData['cost_price'] ?? 0,
                    'sale_price' => $variantData['sale_price'] ?? 0,
                    'regular_price' => $variantData['sale_price'] ?? 0,
                    'points' => $variantData['points'] ?? 0,
                    'is_active' => true,
                ]);

                // Create variant attributes (junction table)
                if (isset($variantData['attributeValues']) && is_array($variantData['attributeValues'])) {
                    foreach ($variantData['attributeValues'] as $attrValue) {
                        ProductVariantAttribute::create([
                            'variant_id' => $variant->id,
                            'attribute_id' => $attrValue['attributeId'],
                            'attribute_value_id' => $attrValue['valueId'],
                        ]);
                    }
                }

                // Create inventory for variant
                Inventory::create([
                    'tenant_id' => $tenantId,
                    'product_id' => $product->id,
                    'variant_id' => $variant->id,
                    'quantity' => $variantData['stock'] ?? 0,
                    'reserved_quantity' => 0,
                ]);

                $createdVariants[] = $variant;
            }

            // Update product to variable type
            $product->update([
                'product_type' => 'variable',
                'has_variants' => true,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Đã lưu biến thể sản phẩm thành công',
                'data' => $createdVariants
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error in saveProductVariants: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi lưu biến thể sản phẩm: ' . $e->getMessage()
            ], 500);
        }
    }
}
