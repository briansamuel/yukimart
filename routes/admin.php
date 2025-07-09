<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\WelcomeController;
use App\Http\Controllers\Admin\CMS\MyProfileController;
use App\Http\Controllers\Admin\CMS\PageController;
use App\Http\Controllers\Admin\CMS\NewsController;
use App\Http\Controllers\Admin\CMS\SubcribeEmailsController;
use App\Http\Controllers\Admin\CMS\ProjectController;
use App\Http\Controllers\Admin\CMS\PartnerController;
use App\Http\Controllers\Admin\CMS\RecruitmentController;
use App\Http\Controllers\Admin\CMS\VideoController;
use App\Http\Controllers\Admin\CMS\ImageController;
use App\Http\Controllers\Admin\CMS\BrandsController;
use App\Http\Controllers\Admin\CMS\CommentController;
use App\Http\Controllers\Admin\CMS\CategoryController;
use App\Http\Controllers\Admin\CMS\ProductController;
use App\Http\Controllers\Admin\CMS\InventoryController;
use App\Http\Controllers\Admin\CMS\SupplierController;
use App\Http\Controllers\Admin\CMS\OrderController;
use App\Http\Controllers\Admin\CMS\UsersController;
use App\Http\Controllers\Admin\CMS\RoleController;
use App\Http\Controllers\Admin\CMS\PermissionController;

use App\Http\Controllers\Admin\CMS\LogsUserController;

use App\Http\Controllers\Admin\CMS\ThemeOptionsController;
use App\Http\Controllers\Admin\CMS\CustomCssController;
use App\Http\Controllers\Admin\CMS\TemplateController;
use App\Http\Controllers\Admin\CMS\ContactController;
use App\Http\Controllers\Admin\CMS\SettingController;
use App\Http\Controllers\Admin\CMS\GalleryController;
use App\Http\Controllers\Admin\CMS\CustomerController;
use App\Http\Controllers\Admin\CMS\BannersController;
use App\Http\Controllers\Admin\CMS\ReviewController;
use App\Http\Controllers\Admin\CMS\MultiLanguageController;
use App\Http\Controllers\Admin\CMS\MenusController;
use App\Http\Controllers\Admin\CMS\ServiceController;
use App\Http\Controllers\Admin\CMS\InvoiceController;
use App\Http\Controllers\Admin\CMS\AuditLogController;
use App\Http\Controllers\Admin\CMS\InventoryImportExportController;
use App\Http\Controllers\Admin\CMS\ReportsController;
use App\Http\Controllers\Admin\CMS\NotificationController;
use App\Http\Controllers\Admin\CMS\BranchShopController;
use App\Http\Controllers\Admin\CMS\WarehouseController;

use App\Http\Controllers\Admin\CMS\ProductCategoryController;
use App\Http\Controllers\Admin\BackupController;

// Shopee Controllers
use App\Http\Controllers\Admin\Shopee\ShopeeOAuthController;
use App\Http\Controllers\Admin\Shopee\ShopeeProductController;
use App\Http\Controllers\Admin\Shopee\ShopeeSyncController;
use App\Http\Controllers\Admin\QuickOrderController;
use App\Http\Controllers\Admin\QuickInvoiceController;
use App\Http\Controllers\Admin\ProductImportController;


if (!defined('FM_USE_ACCESS_KEYS')) {
    define('FM_USE_ACCESS_KEYS', true); // TRUE or FALSE
}


if (!defined('FM_DEBUG_ERROR_MESSAGE')) {
    define('FM_DEBUG_ERROR_MESSAGE', false); // TRUE or FALSE
}


// Denied Permission Page
Route::get('denied-permission', function () {
    return view('admin.pages.permission_denied');
});
// login
Route::get('/login', [LoginController::class, 'login'])->name('login');
Route::post('/login', [LoginController::class, 'loginAction'])->name('login.action');
Route::get('/logout', [LoginController::class, 'logout'])->name('logout');
Route::get('/reset_password', [LoginController::class, 'resetPassword'])->name('resetPassword');
Route::get('/active-user', [LoginController::class, 'activeUser'])->name('activeUser');
Route::get('/active-agent', [LoginController::class, 'activeAgent'])->name('activeAgent');
Route::get('/kich-hoat-tai-khoan', [LoginController::class, 'activeGuest'])->name('activeGuest');
Route::get('/admin', [DashboardController::class, 'index'])->middleware(['auth'])->name('admin.home');
Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {

    
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/revenue-data', [DashboardController::class, 'getRevenueData'])->name('dashboard.revenue-data');
    Route::get('/dashboard/top-products-data', [DashboardController::class, 'getTopProductsData'])->name('dashboard.top-products-data');
    
   

    // my profile
    Route::get('/my-profile', [MyProfileController::class, 'profile'])->name('profile');
    Route::post('/my-profile', [MyProfileController::class, 'updateProfile'])->name('profile.update');

    // change password
    Route::get('/change-password', [MyProfileController::class, 'changePassword'])->name('profile.changePassword');
    Route::put('/change-password', [MyProfileController::class, 'changePasswordAction'])->name('profile.changePasswordAction');

    Route::namespace('CMS')->group(function () {
        // Page
        Route::get('/page', [PageController::class, 'index'])->name('page.list');
        Route::get('/page/add', [PageController::class, 'add'])->name('page.add');
        Route::post('/page/add', [PageController::class, 'addAction'])->name('page.add.action');
        Route::get('/page/edit/{page_id}', [PageController::class, 'edit'])->name('page.edit');
        Route::post('/page/edit/{page_id}', [PageController::class, 'editAction'])->name('page.edit.action');
        Route::post('/page/edit', [PageController::class, 'editManyAction'])->name('page.edit.many.action');
        Route::get('/page/delete/{page_id}', [PageController::class, 'delete'])->name('page.delete');
        Route::post('/page/delete', [PageController::class, 'deletemany'])->name('page.delete.many');
        Route::post('/page', [PageController::class, 'ajaxGetList'])->name('page.ajax.getList');

        // News
        Route::get('/news', [NewsController::class, 'index'])->name('news.list');
        Route::get('/news/add', [NewsController::class, 'add'])->name('news.add');
        Route::post('/news/add', [NewsController::class, 'addAction'])->name('news.add.action');
        Route::get('/news/edit/{news_id}', [NewsController::class, 'edit'])->name('news.edit');
        Route::post('/news/edit/{news_id}', [NewsController::class, 'editAction'])->name('news.edit.action');
        Route::post('/news/edit', [NewsController::class, 'editManyAction'])->name('news.edit.many.action');
        Route::get('/news/delete/{news_id}', [NewsController::class, 'delete'])->name('news.delete');
        Route::post('/news/delete', [NewsController::class, 'deletemany'])->name('news.delete.many');
        Route::post('/news', [NewsController::class, 'ajaxGetList'])->name('news.ajax.getList');

        // subcribe Emails
        Route::get('/subcribe-emails', [SubcribeEmailsController::class, 'index'])->name('subcribe_email.list');
        Route::get('/subcribe-emails/edit/{id}', [SubcribeEmailsController::class, 'edit'])->name('subcribe_email.edit');
        Route::post('/subcribe-emails/edit/{id}', [SubcribeEmailsController::class, 'editAction'])->name('subcribe_email.edit.action');
        Route::get('/subcribe-emails/delete/{id}', [SubcribeEmailsController::class, 'delete'])->name('subcribe_email.delete');
        Route::get('/subcribe-emails/ajax/get-list', [SubcribeEmailsController::class, 'ajaxGetList'])->name('subcribe_email.ajax.getList');

        // Project
        Route::get('/project', [ProjectController::class, 'index'])->name('project.list');
        Route::get('/project/add', [ProjectController::class, 'add'])->name('project.add');
        Route::post('/project/add', [ProjectController::class, 'addAction'])->name('project.add.action');
        Route::get('/project/edit/{project_id}', [ProjectController::class, 'edit'])->name('project.edit');
        Route::post('/project/edit/{edit_id}', [ProjectController::class, 'editAction'])->name('project.edit.action');
        Route::post('/project/edit', [ProjectController::class, 'editManyAction'])->name('project.edit.many.action');
        Route::get('/project/delete/{comment_id}', [ProjectController::class, 'delete'])->name('project.delete');
        Route::post('/project/delete', [ProjectController::class, 'deletemany'])->name('project.delete.many');
        Route::post('/project', [ProjectController::class, 'ajaxGetList'])->name('project.ajax.getList');

        // Partner
        Route::get('/partner', [PartnerController::class, 'index'])->name('partner.list');
        Route::get('/partner/add', [PartnerController::class, 'add'])->name('partner.add');
        Route::get('/partner/edit/{news_id}', [PartnerController::class, 'add'])->name('partner.edit');
        Route::post('/partner/edit', [PartnerController::class, 'editManyAction'])->name('partner.edit.many.action');
        Route::get('/partner/delete/{news_id}', [PartnerController::class, 'delete'])->name('partner.delete');
        Route::post('/partner/delete', [PartnerController::class, 'deletemany'])->name('partner.delete.many');
        Route::post('/partner/save', [PartnerController::class, 'save'])->name('partner.save');
        Route::get('/partner/ajax/get-list', [PartnerController::class, 'ajaxGetList'])->name('partner.ajax.getList');

        // Recruitment
        Route::get('/recruitment', [RecruitmentController::class, 'index'])->name('recruitment.list');
        Route::get('/recruitment/add', [RecruitmentController::class, 'add'])->name('recruitment.add');
        Route::get('/recruitment/edit/{news_id}', [RecruitmentController::class, 'add'])->name('recruitment.edit');
        Route::post('/recruitment/edit', [RecruitmentController::class, 'editManyAction'])->name('recruitment.edit.many.action');
        Route::get('/recruitment/delete/{news_id}', [RecruitmentController::class, 'delete'])->name('recruitment.delete');
        Route::post('/recruitment/delete', [RecruitmentController::class, 'deletemany'])->name('recruitment.delete.many');
        Route::post('/recruitment/save', [RecruitmentController::class, 'save'])->name('recruitment.save');
        Route::get('/recruitment/ajax/get-list', [RecruitmentController::class, 'ajaxGetList'])->name('recruitment.ajax.getList');

        // Video Gallery
        Route::get('/video', [VideoController::class, 'index'])->name('video.list');
        Route::get('/video/add', [VideoController::class, 'add'])->name('video.add');
        Route::get('/video/edit/{news_id}', [VideoController::class, 'add'])->name('video.edit');
        Route::post('/video/edit', [VideoController::class, 'editManyAction'])->name('video.edit.many.action');
        Route::get('/video/delete/{news_id}', [VideoController::class, 'delete'])->name('video.delete');
        Route::post('/video/delete', [VideoController::class, 'deletemany'])->name('video.delete.many');
        Route::post('/video/save', [VideoController::class, 'save'])->name('video.save');
        Route::get('/video/ajax/get-list', [VideoController::class, 'ajaxGetList'])->name('video.ajax.getList');

        // Album Image
        Route::get('/album', [ImageController::class, 'index'])->name('album.list');
        Route::get('/album/add', [ImageController::class, 'add'])->name('album.add');
        Route::get('/album/edit/{id}', [ImageController::class, 'add'])->name('album.edit');
        Route::post('/album/edit', [ImageController::class, 'editManyAction'])->name('album.edit.many.action');
        Route::get('/album/delete/{id}', [ImageController::class, 'delete'])->name('album.delete');
        Route::post('/album/delete', [ImageController::class, 'deletemany'])->name('album.delete.many');
        Route::post('/album/save', [ImageController::class, 'save'])->name('album.save');
        Route::get('/album/ajax/get-list', [ImageController::class, 'ajaxGetList'])->name('album.ajax.getList');

        // Brand
        Route::get('/brand', [BrandsController::class, 'index'])->name('brand.list');
        Route::get('/brand/add', [BrandsController::class, 'add'])->name('brand.add');
        Route::post('/brand/add', [BrandsController::class, 'addAction'])->name('brand.add.action');
        Route::get('/brand/edit/{edit_id}', [BrandsController::class, 'edit'])->name('brand.edit');
        Route::post('/brand/edit/{edit_id}', [BrandsController::class, 'editAction'])->name('brand.edit.action');
        Route::post('/brand/edit', [BrandsController::class, 'editManyAction'])->name('brand.edit.many.action');
        Route::get('/brand/delete/{comment_id}', [BrandsController::class, 'delete'])->name('brand.delete');
        Route::post('/brand/delete', [BrandsController::class, 'deletemany'])->name('brand.delete.many');
        Route::get('/brand/ajax/get-list', [BrandsController::class, 'ajaxGetList'])->name('brand.ajax.getList');

        // Products
        Route::get('/products', [ProductController::class, 'index'])->name('products.list');
        Route::get('/products/add', [ProductController::class, 'add'])->name('products.add');
        Route::post('/products/add', [ProductController::class, 'addAction'])->name('products.add.action');

        // Product Import Routes (must be before {id} routes)
        Route::prefix('products/import')->name('products.import.')->group(function () {
            Route::get('/', [ProductImportController::class, 'index'])->name('index');
            Route::post('/upload', [ProductImportController::class, 'upload'])->name('upload');
            Route::post('/test-upload', [ProductImportController::class, 'testUpload'])->name('test-upload');
            Route::get('/fields', [ProductImportController::class, 'getFields'])->name('fields');
            Route::get('/preview', [ProductImportController::class, 'preview'])->name('preview');
            Route::get('/stats', [ProductImportController::class, 'getFileStats'])->name('stats');
            Route::post('/validate', [ProductImportController::class, 'validateImport'])->name('validate');
            Route::post('/process', [ProductImportController::class, 'process'])->name('process');
            Route::get('/template', [ProductImportController::class, 'downloadTemplate'])->name('template');
            Route::get('/history', [ProductImportController::class, 'history'])->name('history');
            Route::delete('/clear-session', [ProductImportController::class, 'clearSession'])->name('clear-session');
        });

        // Product Attribute Routes (MUST be before routes with {id} parameter)
        Route::get('/products/attributes', [ProductController::class, 'getAttributes'])->name('products.attributes');
        Route::post('/products/attributes', [ProductController::class, 'storeAttribute'])->name('products.attributes.store');
        Route::get('/products/attributes/{attributeId}/values', [ProductController::class, 'getAttributeValues'])->name('products.attributes.values.get');
        Route::post('/products/attributes/{attributeId}/values', [ProductController::class, 'storeAttributeValue'])->name('products.attributes.values.store');

        // Product AJAX Routes (specific paths before {id})
        Route::get('/products/ajax/get-list', [ProductController::class, 'ajaxGetList'])->name('products.ajax.getList');

        // Product Routes with {id} parameter (MUST be after specific paths)
        Route::get('/products/{id}', [ProductController::class, 'show'])->name('products.show');
        Route::get('/products/edit/{id}', [ProductController::class, 'edit'])->name('products.edit');
        Route::post('/products/edit/{id}', [ProductController::class, 'editAction'])->name('products.edit.action');
        Route::post('/products/edit', [ProductController::class, 'editManyAction'])->name('products.edit.many.action');
        Route::get('/products/delete/{id}', [ProductController::class, 'delete'])->name('products.delete');
        Route::post('/products/delete', [ProductController::class, 'deleteMany'])->name('products.delete.many');

        // Product Action Menu Routes
        Route::post('/products/{id}/duplicate', [ProductController::class, 'duplicate'])->name('products.duplicate');
        Route::patch('/products/{id}/status', [ProductController::class, 'changeStatus'])->name('products.change.status');
        Route::get('/products/{id}/history', [ProductController::class, 'getHistory'])->name('products.history');
        Route::post('/products/{id}/quick-edit', [ProductController::class, 'quickEdit'])->name('products.quick.edit');
        Route::post('/products/{id}/adjust-stock', [ProductController::class, 'adjustStock'])->name('products.adjust.stock');

        // Product Variant Routes
        Route::post('/products/{id}/variants', [ProductController::class, 'createVariants'])->name('products.variants.create');
        Route::post('/products/{id}/variants/from-form', [ProductController::class, 'createVariantsFromForm'])->name('products.variants.create.form');
        Route::get('/products/{id}/variants', [ProductController::class, 'getVariants'])->name('products.variants.get');
        Route::put('/products/{productId}/variants/{variantId}', [ProductController::class, 'updateVariant'])->name('products.variants.update');
        Route::delete('/products/{productId}/variants/{variantId}', [ProductController::class, 'deleteVariant'])->name('products.variants.delete');
        Route::post('/products/{id}/variants/bulk-update-prices', [ProductController::class, 'bulkUpdateVariantPrices'])->name('products.variants.bulk.update.prices');



        // Image upload route (legacy)
        Route::post('/upload-image', [ProductController::class, 'uploadImage'])->name('upload.image');

        // Inventory Management
        Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.dashboard');

        // Import/Export Routes
        Route::get('/inventory/import', [InventoryController::class, 'import'])->name('inventory.import');
        Route::post('/inventory/import', [InventoryController::class, 'processImport'])->name('inventory.process-import');
        Route::get('/inventory/export', [InventoryController::class, 'export'])->name('inventory.export');
        Route::post('/inventory/export', [InventoryController::class, 'processExport'])->name('inventory.process-export');

        // Adjustment Routes
        Route::get('/inventory/adjustment', [InventoryController::class, 'adjustment'])->name('inventory.adjustment');
        Route::post('/inventory/adjustment', [InventoryController::class, 'processAdjustment'])->name('inventory.process-adjustment');

        // Transaction Routes
        Route::get('/inventory/transactions', [InventoryController::class, 'transactions'])->name('inventory.transactions');
        Route::get('/inventory/transactions/ajax', [InventoryController::class, 'getTransactionsAjax'])->name('inventory.transactions.ajax');
        Route::get('/inventory/transactions/statistics', [InventoryController::class, 'getTransactionStatistics'])->name('inventory.transactions.statistics');
        Route::get('/inventory/transactions/{id}', [InventoryController::class, 'getTransactionDetail'])->name('inventory.transaction.detail');

        // Report Routes
        Route::get('/inventory/report', [InventoryController::class, 'report'])->name('inventory.report');
        Route::get('/inventory/export-transactions', [InventoryController::class, 'exportTransactions'])->name('inventory.export-transactions');

        // Stock Check Routes
        Route::get('/inventory/stock-check', [InventoryController::class, 'stockCheck'])->name('inventory.stock-check');

        // Suppliers
        Route::get('/supplier', [SupplierController::class, 'index'])->name('supplier.list');
        Route::get('/supplier/ajax', [SupplierController::class, 'ajaxGetList'])->name('supplier.ajax');
        Route::get('/supplier/add', [SupplierController::class, 'add'])->name('supplier.add');
        Route::post('/supplier/add', [SupplierController::class, 'addAction'])->name('supplier.add.action');
        Route::get('/supplier/edit/{supplier_id}', [SupplierController::class, 'edit'])->name('supplier.edit');
        Route::post('/supplier/edit/{supplier_id}', [SupplierController::class, 'editAction'])->name('supplier.edit.action');
        Route::get('/supplier/detail/{supplier_id}', [SupplierController::class, 'detail'])->name('supplier.detail');
        Route::delete('/supplier/delete/{supplier_id}', [SupplierController::class, 'delete'])->name('supplier.delete');
        Route::post('/supplier/delete', [SupplierController::class, 'deleteMany'])->name('supplier.delete.many');
        Route::get('/supplier/active', [SupplierController::class, 'getActiveSuppliers'])->name('supplier.active');
        Route::post('/supplier/check-code', [SupplierController::class, 'checkCodeUnique'])->name('supplier.check.code');
        Route::get('/supplier/statistics', [SupplierController::class, 'getStatistics'])->name('supplier.statistics');


        // Order routes - API routes first to avoid conflicts
        Route::get('/order', [OrderController::class, 'index'])->name('order.list');
        Route::get('/order/add', [OrderController::class, 'add'])->name('order.add');
        Route::post('/order/add', [OrderController::class, 'addAction'])->name('order.add.action');
        Route::get('/order/ajax', [OrderController::class, 'ajaxGetOrders'])->name('order.ajax');
        Route::get('/order/customers', [OrderController::class, 'getCustomers'])->name('order.customers');
        Route::get('/order/products', [OrderController::class, 'getProducts'])->name('order.products');
        Route::get('/order/initial-data', [OrderController::class, 'getInitialData'])->name('order.initial.data');
        Route::get('/order/check-phone', [OrderController::class, 'checkPhoneExists'])->name('order.check.phone');
        Route::get('/order/statistics', [OrderController::class, 'getStatistics'])->name('order.statistics');
        Route::get('/order/product-details/{product_id}', [OrderController::class, 'getProductDetails'])->name('order.product.details');
        Route::post('/order/create-customer', [OrderController::class, 'createNewCustomer'])->name('order.create.customer');
        Route::post('/order/update-status/{order_id}', [OrderController::class, 'updateStatus'])->name('order.update.status');
        Route::post('/order/delete-many', [OrderController::class, 'deleteMany'])->name('order.delete.many');

        // Order CRUD routes with parameters - these should come after API routes
        Route::get('/order/{id}', [OrderController::class, 'show'])->name('order.show');
        Route::get('/order/edit/{order_id}', [OrderController::class, 'edit'])->name('order.edit');
        Route::post('/order/edit/{order_id}', [OrderController::class, 'editAction'])->name('order.edit.action');
        Route::get('/order/detail/{order_id}', [OrderController::class, 'detail'])->name('order.detail');
        Route::delete('/order/delete/{order_id}', [OrderController::class, 'delete'])->name('order.delete');

        // Order detail actions
        Route::post('/order/{id}/record-payment', [OrderController::class, 'recordPayment'])->name('order.record.payment');
        Route::post('/order/{id}/cancel', [OrderController::class, 'cancelOrder'])->name('order.cancel');
        Route::get('/order/{id}/print/{type}', [OrderController::class, 'printOrder'])->name('order.print');
        Route::get('/order/{id}/export/{type}', [OrderController::class, 'exportOrder'])->name('order.export');

        // Test route for new customer feature
        Route::get('/order/test-new-customer', function() {
            return view('admin.orders.test-new-customer');
        })->name('order.test.new.customer');
        Route::post('/order/quick-update/{order_id}', [OrderController::class, 'quickUpdate'])->name('order.quick.update');
        Route::get('/order/{order_id}/get', [OrderController::class, 'getOrder'])->name('order.get');
        Route::get('/order/{order_id}/detail-modal', [OrderController::class, 'getOrderDetail'])->name('order.detail.modal');
        Route::post('/order/bulk-delete', [OrderController::class, 'bulkDelete'])->name('order.bulk.delete');
        Route::get('/order/print/{order_id}', [OrderController::class, 'print'])->name('order.print.simple');
        Route::get('/order/export/{order_id}', [OrderController::class, 'exportOrder'])->name('order.export.single');

        // Icon showcase route
        Route::get('/icons-showcase', function () {
            return view('admin.icons-showcase');
        })->name('icons.showcase');

        // Demo order detail route
        Route::get('/demo-order-detail', function () {
            return view('admin.orders.demo-detail');
        })->name('demo.order.detail');

        // Test order detail route
        Route::get('/test-order-detail', function () {
            return view('admin.orders.test-detail');
        })->name('test.order.detail');

        // Debug route for testing order API endpoints
        Route::get('/debug-order-routes', function () {
            $routes = [];
            $allRoutes = \Illuminate\Support\Facades\Route::getRoutes();

            foreach ($allRoutes as $route) {
                $uri = $route->uri();
                if (strpos($uri, 'admin/order') !== false) {
                    $routes[] = [
                        'uri' => $uri,
                        'name' => $route->getName(),
                        'methods' => implode('|', $route->methods()),
                        'action' => $route->getActionName()
                    ];
                }
            }

            // Sort by URI
            usort($routes, function($a, $b) {
                return strcmp($a['uri'], $b['uri']);
            });

            return response()->json([
                'total_routes' => count($routes),
                'routes' => $routes,
                'test_urls' => [
                    'customers' => url('/admin/order/customers'),
                    'products' => url('/admin/order/products'),
                    'initial_data' => url('/admin/order/initial-data'),
                    'check_phone' => url('/admin/order/check-phone'),
                    'statistics' => url('/admin/order/statistics')
                ]
            ]);
        })->name('debug.order.routes');

        // Test direct API calls
        Route::get('/test-order-api', function () {
            try {
                $orderService = app(\App\Services\OrderService::class);

                $results = [
                    'customers' => [],
                    'products' => [],
                    'errors' => []
                ];

                // Test customers
                try {
                    $customers = $orderService->getCustomersForDropdown();
                    $results['customers'] = [
                        'count' => count($customers),
                        'sample' => array_slice($customers, 0, 3)
                    ];
                } catch (\Exception $e) {
                    $results['errors']['customers'] = $e->getMessage();
                }

                // Test products
                try {
                    $products = $orderService->getProductsForOrder();
                    $results['products'] = [
                        'count' => count($products),
                        'sample' => array_slice($products, 0, 3)
                    ];
                } catch (\Exception $e) {
                    $results['errors']['products'] = $e->getMessage();
                }

                return response()->json($results);

            } catch (\Exception $e) {
                return response()->json([
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ], 500);
            }
        })->name('test.order.api');

        // Direct test without middleware for debugging
        Route::get('/direct-test-customers', function () {
            try {
                $orderController = app(\App\Http\Controllers\Admin\CMS\OrderController::class);
                $response = $orderController->getCustomers();
                return $response;
            } catch (\Exception $e) {
                return response()->json([
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ], 500);
            }
        })->name('direct.test.customers');

        Route::get('/direct-test-products', function () {
            try {
                $orderController = app(\App\Http\Controllers\Admin\CMS\OrderController::class);
                $response = $orderController->getProducts();
                return $response;
            } catch (\Exception $e) {
                return response()->json([
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ], 500);
            }
        })->name('direct.test.products');

        // Debug page
        Route::get('/debug-order-page', function () {
            return view('admin.debug.order-routes');
        })->name('debug.order.page');

        // Stock status demo page
        Route::get('/stock-status-demo', function () {
            return view('admin.examples.stock-status-demo');
        })->name('stock.status.demo');

        // Order inventory transaction demo page
        Route::get('/order-inventory-demo', function () {
            return view('admin.examples.order-inventory-demo');
        })->name('order.inventory.demo');

        // Order notifications demo page
        Route::get('/order-notifications-demo', function () {
            return view('admin.examples.order-notifications-demo');
        })->name('order.notifications.demo');

        // Branch Shops Management
        Route::prefix('branch-shops')->name('branch-shops.')->group(function () {
            Route::get('/', [BranchShopController::class, 'index'])->name('index');
            Route::get('/data', [BranchShopController::class, 'getData'])->name('data');
            Route::get('/create', [BranchShopController::class, 'create'])->name('create');
            Route::post('/', [BranchShopController::class, 'store'])->name('store');
            Route::get('/active', [BranchShopController::class, 'getActiveBranchShops'])->name('active');
            Route::get('/dropdown/active', [BranchShopController::class, 'getActiveForDropdown'])->name('dropdown.active');
            Route::get('/dropdown/managers', [BranchShopController::class, 'getManagersForDropdown'])->name('dropdown.managers');
            Route::get('/{id}', [BranchShopController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [BranchShopController::class, 'edit'])->name('edit');
            Route::put('/{id}', [BranchShopController::class, 'update'])->name('update');
            Route::delete('/{id}', [BranchShopController::class, 'destroy'])->name('destroy');
            Route::post('/bulk-action', [BranchShopController::class, 'bulkAction'])->name('bulk-action');
            Route::get('/statistics/summary', [BranchShopController::class, 'getStatistics'])->name('statistics');

            // User management for branch shops
            Route::get('/{branchShop}/users/data', [BranchShopController::class, 'getUsersData'])->name('users.data');
            Route::post('/{branchShop}/users', [BranchShopController::class, 'addUser'])->name('users.add');
            Route::delete('/{branchShop}/users/{user}', [BranchShopController::class, 'removeUser'])->name('users.remove');
            Route::put('/{branchShop}/users/{user}', [BranchShopController::class, 'updateUser'])->name('users.update');
        });

        // Warehouses Management
        Route::prefix('warehouses')->name('warehouses.')->group(function () {
            Route::get('/', [WarehouseController::class, 'index'])->name('index');
            Route::get('/create', [WarehouseController::class, 'create'])->name('create');
            Route::post('/', [WarehouseController::class, 'store'])->name('store');
            Route::get('/dropdown', [WarehouseController::class, 'getForDropdown'])->name('dropdown');
            Route::get('/{id}', [WarehouseController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [WarehouseController::class, 'edit'])->name('edit');
            Route::put('/{id}', [WarehouseController::class, 'update'])->name('update');
            Route::delete('/{id}', [WarehouseController::class, 'destroy'])->name('destroy');
        });

        // Notifications Management
        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::get('/', [NotificationController::class, 'index'])->name('index');
            Route::get('/data', [NotificationController::class, 'getData'])->name('data');
            Route::get('/recent', [NotificationController::class, 'getRecent'])->name('recent');
            Route::get('/count', [NotificationController::class, 'getCount'])->name('count');
            Route::get('/unread-count', [NotificationController::class, 'getUnreadCount'])->name('unread-count');
            Route::get('/types', [NotificationController::class, 'getTypes'])->name('types');
            Route::get('/statistics', [NotificationController::class, 'getStatistics'])->name('statistics');
            Route::post('/', [NotificationController::class, 'store'])->name('store');
            Route::put('/{id}/read', [NotificationController::class, 'markAsRead'])->name('mark-read');
            Route::put('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
            Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('destroy');
            Route::delete('/cleanup/expired', [NotificationController::class, 'cleanupExpired'])->name('cleanup-expired');
            Route::delete('/cleanup/old', [NotificationController::class, 'cleanupOld'])->name('cleanup-old');
        });

        // Inventory Import/Export
        Route::prefix('inventory/import-export')->name('inventory.import-export.')->group(function () {
            Route::get('/', [InventoryImportExportController::class, 'index'])->name('index');
            Route::post('/export', [InventoryImportExportController::class, 'export'])->name('export');
            Route::post('/import', [InventoryImportExportController::class, 'import'])->name('import');
            Route::get('/template', [InventoryImportExportController::class, 'downloadTemplate'])->name('template');
            Route::get('/history', [InventoryImportExportController::class, 'getHistory'])->name('history');
            Route::get('/summary', [InventoryImportExportController::class, 'getSummary'])->name('summary');
            Route::post('/validate', [InventoryImportExportController::class, 'validateImportFile'])->name('validate');
            Route::get('/warehouses', [InventoryImportExportController::class, 'getWarehouses'])->name('warehouses');
            Route::get('/categories', [InventoryImportExportController::class, 'getProductCategories'])->name('categories');
        });

        // Reports and Analytics
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [ReportsController::class, 'index'])->name('index');
            Route::get('/sales', [ReportsController::class, 'getSalesReport'])->name('sales');
            Route::get('/inventory', [ReportsController::class, 'getInventoryReport'])->name('inventory');
            Route::get('/product-performance', [ReportsController::class, 'getProductPerformanceReport'])->name('product-performance');
            Route::get('/customer-analytics', [ReportsController::class, 'getCustomerAnalytics'])->name('customer-analytics');
            Route::get('/financial-summary', [ReportsController::class, 'getFinancialSummary'])->name('financial-summary');
            Route::get('/dashboard-analytics', [ReportsController::class, 'getDashboardAnalytics'])->name('dashboard-analytics');
            Route::post('/export', [ReportsController::class, 'exportReport'])->name('export');
            Route::get('/filters', [ReportsController::class, 'getFilters'])->name('filters');
        });

        // Product Categories
        Route::prefix('product-categories')->name('product-categories.')->group(function () {
            Route::get('/', [ProductCategoryController::class, 'index'])->name('index');
            Route::get('/data', [ProductCategoryController::class, 'getData'])->name('data');
            Route::get('/create', [ProductCategoryController::class, 'create'])->name('create');
            Route::post('/', [ProductCategoryController::class, 'store'])->name('store');
            Route::get('/{productCategory}', [ProductCategoryController::class, 'show'])->name('show');
            Route::get('/{productCategory}/edit', [ProductCategoryController::class, 'edit'])->name('edit');
            Route::put('/{productCategory}', [ProductCategoryController::class, 'update'])->name('update');
            Route::delete('/{productCategory}', [ProductCategoryController::class, 'destroy'])->name('destroy');
            Route::get('/parent/list', [ProductCategoryController::class, 'getParentCategories'])->name('parent-list');
            Route::post('/sort-order', [ProductCategoryController::class, 'updateSortOrder'])->name('sort-order');
        });

        // Backup and Restore
        Route::prefix('backup')->name('backup.')->group(function () {
            Route::get('/', [BackupController::class, 'index'])->name('index');
            Route::post('/manual', [BackupController::class, 'createManual'])->name('manual');
            Route::get('/progress/{id}', [BackupController::class, 'getProgress'])->name('progress');
            Route::get('/download/{id}', [BackupController::class, 'download'])->name('download');
            Route::delete('/{id}', [BackupController::class, 'delete'])->name('delete');
            Route::post('/restore/{id}', [BackupController::class, 'restore'])->name('restore');
            Route::post('/schedule', [BackupController::class, 'createSchedule'])->name('schedule.create');
            Route::patch('/schedule/{id}/toggle', [BackupController::class, 'toggleSchedule'])->name('schedule.toggle');
            Route::delete('/schedule/{id}', [BackupController::class, 'deleteSchedule'])->name('schedule.delete');
            Route::get('/stats', [BackupController::class, 'getStats'])->name('stats');
        });

        // User Settings (Legacy - removed to avoid conflicts)

        // Language switching routes
        Route::get('/change-language/{locale}', function ($locale) {
            if (in_array($locale, config('app.supported_locales', ['vi', 'en']))) {
                // Set session locale
                session(['locale' => $locale]);

                // Save to user settings if authenticated
                if (Auth::check()) {
                    \App\Models\UserSetting::updateOrCreate(
                        ['user_id' => Auth::id(), 'key' => 'language'],
                        ['value' => $locale, 'type' => 'string']
                    );
                }

                // Set application locale
                App::setLocale($locale);
            }

            return redirect()->back();
        })->name('change-language');

        // Audit Logs
        Route::prefix('audit-logs')->name('audit-logs.')->group(function () {
            Route::get('/', [AuditLogController::class, 'index'])->name('index');
            Route::get('/data', [AuditLogController::class, 'getData'])->name('data');
            Route::get('/{auditLog}', [AuditLogController::class, 'show'])->name('show');
            Route::get('/statistics/summary', [AuditLogController::class, 'getStatistics'])->name('statistics');
            Route::post('/export', [AuditLogController::class, 'export'])->name('export');
            Route::post('/cleanup', [AuditLogController::class, 'cleanup'])->name('cleanup');
            Route::get('/filters/list', [AuditLogController::class, 'getFilters'])->name('filters');
        });

        // Customers
        Route::prefix('customers')->name('customers.')->group(function () {
            Route::get('/', [CustomerController::class, 'index'])->name('index');
            Route::get('/data', [CustomerController::class, 'getData'])->name('data');
            Route::get('/statistics', [CustomerController::class, 'getStatistics'])->name('statistics');
            Route::get('/active/list', [CustomerController::class, 'getActiveCustomers'])->name('active-list');
            Route::get('/create', [CustomerController::class, 'create'])->name('create');
            Route::post('/', [CustomerController::class, 'store'])->name('store');
            Route::get('/{customer}/statistics', [CustomerController::class, 'statistics'])->name('statistics.detail');
            Route::get('/{customer}', [CustomerController::class, 'show'])->name('show');
            Route::get('/{customer}/edit', [CustomerController::class, 'edit'])->name('edit');
            Route::put('/{customer}', [CustomerController::class, 'update'])->name('update');
            Route::delete('/{customer}', [CustomerController::class, 'destroy'])->name('destroy');
        });

        // Invoice routes
        Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoice.list');
        //Route::get('/invoices', [InvoiceController::class, 'index'])->name('admin.invoice.list'); // Alias for header menu

        Route::get('/invoices/ajax', [InvoiceController::class, 'getInvoicesAjax'])->name('invoice.ajax');
        Route::get('/invoices/filter-users', [InvoiceController::class, 'getFilterUsers'])->name('invoice.filter-users');
        Route::get('/invoices/{id}/detail-panel', [InvoiceController::class, 'getDetailPanel'])->name('invoice.detail-panel');
        Route::get('/invoices/test-detail/{id}', function($id) {
            return response()->json([
                'success' => true,
                'message' => 'Test endpoint working',
                'invoice_id' => $id,
                'html' => '<div class="alert alert-success">Test detail panel for invoice ' . $id . '</div>'
            ]);
        })->name('invoice.test-detail');
        Route::get('/invoices/create', [InvoiceController::class, 'create'])->name('invoice.create');
        Route::post('/invoices/create', [InvoiceController::class, 'store'])->name('invoice.store');
        Route::get('/invoices/{id}', [InvoiceController::class, 'show'])->name('invoice.show');
        Route::get('/invoices/{id}/edit', [InvoiceController::class, 'edit'])->name('invoice.edit');
        Route::put('/invoices/{id}', [InvoiceController::class, 'update'])->name('invoice.update');
        Route::delete('/invoices/{id}', [InvoiceController::class, 'destroy'])->name('invoice.delete');
        Route::post('/invoices/{id}/payment', [InvoiceController::class, 'recordPayment'])->name('invoice.payment');
        Route::post('/invoices/{id}/send', [InvoiceController::class, 'sendInvoice'])->name('invoice.send');
        Route::post('/invoices/{id}/cancel', [InvoiceController::class, 'cancelInvoice'])->name('invoice.cancel');
        Route::get('/invoices/{id}/print', [InvoiceController::class, 'print'])->name('invoice.print');
        Route::get('/invoices/statistics', [InvoiceController::class, 'getStatistics'])->name('invoice.statistics');
        Route::post('/invoices/from-order/{order_id}', [InvoiceController::class, 'createFromOrder'])->name('invoice.from-order');

        // Custom File Manager Routes
        Route::prefix('filemanager')->name('filemanager.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\FileManagerController::class, 'index'])->name('index');
            Route::get('/contents', [\App\Http\Controllers\Admin\FileManagerController::class, 'getContents'])->name('contents');
            Route::post('/upload', [\App\Http\Controllers\Admin\FileManagerController::class, 'upload'])->name('upload');
            Route::post('/upload-single', [\App\Http\Controllers\Admin\FileManagerController::class, 'uploadSingle'])->name('upload.single');
            Route::delete('/delete', [\App\Http\Controllers\Admin\FileManagerController::class, 'delete'])->name('delete');
            Route::delete('/delete-multiple', [\App\Http\Controllers\Admin\FileManagerController::class, 'deleteMultiple'])->name('delete.multiple');
            Route::put('/rename', [\App\Http\Controllers\Admin\FileManagerController::class, 'rename'])->name('rename');
            Route::post('/create-folder', [\App\Http\Controllers\Admin\FileManagerController::class, 'createFolder'])->name('create.folder');
            Route::put('/move', [\App\Http\Controllers\Admin\FileManagerController::class, 'move'])->name('move');
            Route::put('/copy', [\App\Http\Controllers\Admin\FileManagerController::class, 'copy'])->name('copy');
            Route::get('/file-info', [\App\Http\Controllers\Admin\FileManagerController::class, 'getFileInfo'])->name('file.info');
            Route::get('/search', [\App\Http\Controllers\Admin\FileManagerController::class, 'search'])->name('search');
        });

        // Legacy file manager route (for backward compatibility)
        Route::get('/file-manager', [ProductController::class, 'fileManager'])->name('file.manager');

        // Comment
        Route::get('/comment', [CommentController::class, 'index'])->name('comment.list');
        Route::get('/comment/add', [CommentController::class, 'add'])->name('comment.add');
        Route::post('/comment/add', [CommentController::class, 'addAction'])->name('comment.add.action');
        Route::get('/comment/edit/{comment_id}', [CommentController::class, 'edit'])->name('comment.edit');
        Route::post('/comment/edit/{comment_id}', [CommentController::class, 'editAction'])->name('comment.edit.action');
        Route::post('/comment/edit', [CommentController::class, 'editManyAction'])->name('comment.edit.many.action');
        Route::get('/comment/delete/{comment_id}', [CommentController::class, 'delete'])->name('comment.delete');
        Route::post('/comment/delete', [CommentController::class, 'deletemany'])->name('comment.delete.many');
        Route::get('/comment/ajax/get-list', [CommentController::class, 'ajaxGetList'])->name('comment.ajax.getList');

        // Category
        Route::get('/category', [CategoryController::class, 'index'])->name('category.list');
        // Route::post('/category/save', [CategoryController::class, 'save'])->name('category.save');
        Route::post('/category/add', [CategoryController::class, 'addAction'])->name('category.add');
        Route::get('/category/edit/{category_id}', [CategoryController::class, 'edit'])->name('category.edit');
        Route::post('/category/edit', [CategoryController::class, 'editManyAction'])->name('category.edit.many.action');
        Route::post('/category/edit/{category_id}', [CategoryController::class, 'editAction'])->name('category.edit.action');
        Route::get('/category/delete/{category_id}', [CategoryController::class, 'delete'])->name('category.delete');
        Route::post('/category/delete', [CategoryController::class, 'deleteMany'])->name('category.deleteMany');
        Route::post('/category', [CategoryController::class, 'ajaxGetList'])->name('category.ajax.getList');

        // Users Management
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [UsersController::class, 'index'])->name('index');
            Route::get('/create', [UsersController::class, 'add'])->name('create');
            Route::post('/', [UsersController::class, 'addAction'])->name('store');
            Route::get('/{id}', [UsersController::class, 'detail'])->name('show');
            Route::get('/{id}/edit', [UsersController::class, 'edit'])->name('edit');
            Route::put('/{id}', [UsersController::class, 'update'])->name('update');
            Route::delete('/{id}', [UsersController::class, 'delete'])->name('destroy');
            Route::post('/bulk-delete', [UsersController::class, 'deleteMany'])->name('bulk-delete');
            Route::get('/ajax/get-list', [UsersController::class, 'ajaxGetList'])->name('ajax.getList');
            Route::get('/dropdown/available', [UsersController::class, 'getAvailableForDropdown'])->name('dropdown.available');
            Route::get('/dropdown/list', [UsersController::class, 'listForDropdown'])->name('dropdown.list');

            // Branch Shop Management for Users
            Route::post('/{userId}/assign-branch-shop', [UsersController::class, 'assignBranchShop'])->name('assign-branch-shop');
            Route::put('/{userId}/branch-shops/{branchShopId}', [UsersController::class, 'updateBranchShop'])->name('update-branch-shop');
            Route::delete('/{userId}/branch-shops/{branchShopId}', [UsersController::class, 'removeBranchShop'])->name('remove-branch-shop');
        });

        // Legacy routes for backward compatibility
        Route::get('/user', [UsersController::class, 'index'])->name('user.list');
        Route::get('/user/add', [UsersController::class, 'add'])->name('user.add');
        Route::post('/user/add', [UsersController::class, 'addAction'])->name('user.add.action');
        Route::get('/user/detail/{user_id}', [UsersController::class, 'detail'])->name('user.detail');
        Route::get('/user/edit/{user_id}', [UsersController::class, 'edit'])->name('user.edit');
        Route::post('/user/edit', [UsersController::class, 'editManyAction'])->name('user.edit.many.action');
        Route::post('/user/edit/{user_id}', [UsersController::class, 'editAction'])->name('user.edit.action');
        Route::get('/user/delete', [UsersController::class, 'deleteMany'])->name('user.delete.many');
        Route::get('/user/delete/{user_id}', [UsersController::class, 'delete'])->name('user.delete');
        Route::get('/user/ajax/get-list', [UsersController::class, 'ajaxGetList'])->name('user.ajax.getList');

        // // Agent
        // Route::get('/agent', [AgentsController::class, 'index'])->name('agent.list');
        // Route::get('/agent/add', [AgentsController::class, 'add'])->name('agent.add');
        // Route::post('/agent/add', [AgentsController::class, 'addAction'])->name('agent.add.action');
        // Route::get('/agent/detail/{agent_id}', [AgentsController::class, 'detail'])->name('agent.detail');
        // Route::get('/agent/edit/{agent_id}', [AgentsController::class, 'edit'])->name('agent.edit');
        // Route::post('/agent/edit', [AgentsController::class, 'editManyAction'])->name('agent.edit.many.action');
        // Route::post('/agent/edit/{agent_id}', [AgentsController::class, 'editAction'])->name('agent.edit.action');
        // Route::get('/agent/delete', [AgentsController::class, 'deleteMany'])->name('agent.delete.many');
        // Route::get('/agent/delete/{agent_id}', [AgentsController::class, 'delete'])->name('agent.delete');
        // // Route::post('/agent/delete', [AgentsController::class, 'delete'])->name('agent.delete');
        // Route::get('/agent/ajax/get-list', [AgentsController::class, 'ajaxGetList'])->name('agent.ajax.getList');

        //User
        // Route::get('/guest', [GuestsController::class, 'index'])->name('guest.list');
        // Route::get('/guest/add', [GuestsController::class, 'add'])->name('guest.add');
        // Route::post('/guest/add', [GuestsController::class, 'addAction'])->name('guest.add.action');
        // Route::get('/guest/detail/{guest_id}', [GuestsController::class, 'detail'])->name('guest.detail');
        // Route::get('/guest/edit/{guest_id}', [GuestsController::class, 'edit'])->name('guest.edit');
        // Route::post('/guest/edit', [GuestsController::class, 'editManyAction'])->name('guest.edit.many.action');
        // Route::post('/guest/edit/{guest_id}', [GuestsController::class, 'editAction'])->name('guest.edit.action');
        // Route::get('/guest/delete', [GuestsController::class, 'deleteMany'])->name('guest.delete.many');
        // Route::get('/guest/delete/{guest_id}', [GuestsController::class, 'delete'])->name('guest.delete');
        // Route::post('/guest/delete', [GuestsController::class, 'delete'])->name('guest.delete');
        // Route::get('/guest/ajax/get-list', [GuestsController::class, 'ajaxGetList'])->name('guest.ajax.getList');

        // Roles Management
        Route::prefix('roles')->name('roles.')->group(function () {
            Route::get('/', [RoleController::class, 'index'])->name('index');
            Route::get('/create', [RoleController::class, 'create'])->name('create');
            Route::post('/', [RoleController::class, 'store'])->name('store');
            Route::get('/{id}', [RoleController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [RoleController::class, 'edit'])->name('edit');
            Route::put('/{id}', [RoleController::class, 'update'])->name('update');
            Route::delete('/{id}', [RoleController::class, 'destroy'])->name('destroy');
            Route::post('/{id}/toggle-status', [RoleController::class, 'toggleStatus'])->name('toggle-status');
            Route::get('/{id}/permissions', [RoleController::class, 'getPermissions'])->name('permissions');
            Route::post('/bulk-delete', [RoleController::class, 'bulkDelete'])->name('bulk-delete');
        });

        // Permissions Management
        Route::prefix('permissions')->name('permissions.')->group(function () {
            Route::get('/', [PermissionController::class, 'index'])->name('index');
            Route::get('/data', [PermissionController::class, 'getData'])->name('data');
            Route::get('/create', [PermissionController::class, 'create'])->name('create');
            Route::post('/', [PermissionController::class, 'store'])->name('store');
            Route::get('/{id}', [PermissionController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [PermissionController::class, 'edit'])->name('edit');
            Route::put('/{id}', [PermissionController::class, 'update'])->name('update');
            Route::delete('/{id}', [PermissionController::class, 'destroy'])->name('destroy');
            Route::post('/{id}/toggle-status', [PermissionController::class, 'toggleStatus'])->name('toggle-status');
            Route::get('/by-module', [PermissionController::class, 'getByModule'])->name('by-module');
            Route::post('/bulk-delete', [PermissionController::class, 'bulkDelete'])->name('bulk-delete');
            Route::post('/generate-for-module', [PermissionController::class, 'generateForModule'])->name('generate-for-module');
        });

        // Logs User
        Route::get('/logs-user', [LogsUserController::class, 'index'])->name('logs_user.list');
        Route::get('/logs-user/detail/{id}', [LogsUserController::class, 'detail'])->name('logs_user.detail');
        Route::get('/logs-user/ajax/get-list', [LogsUserController::class, 'ajaxGetList'])->name('logs_user.ajax.getList');

        // Menu
        Route::get('/menus', [LogsUserController::class, 'index'])->name('menu.index');

        // Custom Css
        Route::get('/theme-option', [ThemeOptionsController::class, 'option'])->name('theme_option.index');
        Route::post('/theme-option', [ThemeOptionsController::class, 'optionAction'])->name('theme_option.action');

        // Custom Css
        Route::get('/custom-css', [CustomCssController::class, 'index'])->name('custom_css.index');
        Route::post('/custom-css', [CustomCssController::class, 'editAction'])->name('custom_css.editAction');

        // Custom Template
        Route::get('/template', [TemplateController::class, 'index'])->name('template.index');
        Route::post('/template', [TemplateController::class, 'editAction'])->name('template.editAction');
        // Contacts
        Route::get('/contact', [ContactController::class, 'index'])->name('contact.index');
        Route::get('/contact/ajax/get-list', [ContactController::class, 'ajaxGetList'])->name('contact.ajax.getList');
        Route::get('/contact/edit/{id}', [ContactController::class, 'edit'])->name('contact.edit');
        Route::post('/contact/edit', [ContactController::class, 'editManyAction'])->name('contact.edit.many.action');
        Route::post('/contact/edit/{id}', [ContactController::class, 'editAction'])->name('contact.edit.action');
        Route::get('/contact/delete', [ContactController::class, 'deleteMany'])->name('contact.delete.many');
        Route::get('/contact/delete/{id}', [ContactController::class, 'delete'])->name('contact.delete');
        // Route::post('/contact/delete', [ContactController::class, 'delete'])->name('contact.delete');
        Route::get('/contact/ajax/get-list', [ContactController::class, 'ajaxGetList'])->name('contact.ajax.getList');
        Route::post('/contact/{id}/reply', [ContactController::class, 'replyAction'])->name('contact.reply.action');

        // Setting
        Route::get('/settings-general', [SettingController::class, 'general'])->name('setting.general');
        Route::post('/settings-general', [SettingController::class, 'generalAction'])->name('setting.general.action');
        Route::post('/settings-login-social', [SettingController::class, 'loginSocialAction'])->name('setting.login_social.action');
        Route::get('/settings-email', [SettingController::class, 'email'])->name('setting.email');
        Route::get('/settings-social-login', [SettingController::class, 'loginSocial'])->name('setting.login_social');
        Route::get('/settings-notification', [SettingController::class, 'notification'])->name('setting.notification');
        Route::post('/settings-notification', [SettingController::class, 'notificationAction'])->name('setting.notification.action');

        // Gallery
        Route::get('/gallery', [GalleryController::class, 'index'])->name('gallery.index');

        // User Settings routes
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\UserSettingController::class, 'index'])->name('index');
            Route::post('/update', [\App\Http\Controllers\Admin\UserSettingController::class, 'update'])->name('update');
            Route::get('/export', [\App\Http\Controllers\Admin\UserSettingController::class, 'export'])->name('export');
            Route::post('/import', [\App\Http\Controllers\Admin\UserSettingController::class, 'import'])->name('import');
            Route::post('/reset', [\App\Http\Controllers\Admin\UserSettingController::class, 'resetToDefault'])->name('reset');
            Route::post('/clear-cache', [\App\Http\Controllers\Admin\UserSettingController::class, 'clearCache'])->name('clear-cache');
            Route::get('/{key}', [\App\Http\Controllers\Admin\UserSettingController::class, 'getSetting'])->name('get');
            Route::post('/{key}', [\App\Http\Controllers\Admin\UserSettingController::class, 'setSetting'])->name('set');
            Route::post('/theme/update', [\App\Http\Controllers\Admin\UserSettingController::class, 'updateTheme'])->name('theme.update');
            Route::post('/language/update', [\App\Http\Controllers\Admin\UserSettingController::class, 'updateLanguage'])->name('language.update');
        });

        // Quick Order Routes
        Route::prefix('quick-order')->name('quick-order.')->group(function () {
            Route::get('/', [QuickOrderController::class, 'index'])->name('index');
            Route::post('/', [QuickOrderController::class, 'store'])->name('store');
            Route::get('/session', [QuickOrderController::class, 'getSession'])->name('session.get');
            Route::post('/session', [QuickOrderController::class, 'saveSession'])->name('session.save');
            Route::delete('/session', [QuickOrderController::class, 'clearSession'])->name('session.clear');
            Route::get('/statistics', [QuickOrderController::class, 'getStatistics'])->name('statistics');
            Route::post('/validate', [QuickOrderController::class, 'validateOrder'])->name('validate');
            Route::post('/search-product', [QuickOrderController::class, 'searchProduct'])->name('search-product');
        });

        // Quick Invoice Routes (same interface, different backend)
        Route::prefix('quick-invoice')->name('quick-invoice.')->group(function () {
            Route::post('/', [QuickInvoiceController::class, 'store'])->name('store');
        });



        // Shopee Integration Routes
        Route::prefix('shopee')->name('shopee.')->group(function () {
            // OAuth Routes
            Route::get('/connect', [ShopeeOAuthController::class, 'connect'])->name('connect');
            Route::get('/callback', [ShopeeOAuthController::class, 'callback'])->name('callback');
            Route::post('/refresh', [ShopeeOAuthController::class, 'refresh'])->name('refresh');
            Route::post('/revoke', [ShopeeOAuthController::class, 'revoke'])->name('revoke');
            Route::get('/status', [ShopeeOAuthController::class, 'status'])->name('status');
            Route::get('/dashboard', [ShopeeOAuthController::class, 'dashboard'])->name('dashboard');
            Route::post('/check-expiring-tokens', [ShopeeOAuthController::class, 'checkExpiringTokens'])->name('check-expiring-tokens');

            // Product Management Routes
            Route::prefix('products')->name('products.')->group(function () {
                Route::post('/search-by-sku', [ShopeeProductController::class, 'searchBySku'])->name('search-by-sku');
                Route::post('/link', [ShopeeProductController::class, 'linkProduct'])->name('link');
                Route::post('/create', [ShopeeProductController::class, 'createProduct'])->name('create');
                Route::post('/sync-inventory', [ShopeeProductController::class, 'syncInventory'])->name('sync-inventory');
                Route::post('/bulk-sync-inventory', [ShopeeProductController::class, 'bulkSyncInventory'])->name('bulk-sync-inventory');
                Route::get('/links', [ShopeeProductController::class, 'getLinks'])->name('links');
                Route::post('/unlink', [ShopeeProductController::class, 'unlinkProduct'])->name('unlink');
            });

            // Order Sync Routes
            Route::prefix('orders')->name('orders.')->group(function () {
                Route::post('/sync', [ShopeeSyncController::class, 'syncOrders'])->name('sync');
                Route::get('/sync-status', [ShopeeSyncController::class, 'getSyncStatus'])->name('sync-status');
                Route::post('/sync-single', [ShopeeSyncController::class, 'syncSingleOrder'])->name('sync-single');
                Route::get('/detail', [ShopeeSyncController::class, 'getOrderDetail'])->name('detail');
                Route::get('/marketplace-orders', [ShopeeSyncController::class, 'getMarketplaceOrders'])->name('marketplace-orders');
                Route::get('/sync-logs', [ShopeeSyncController::class, 'getSyncLogs'])->name('sync-logs');
            });

            // Test and Utility Routes
            Route::post('/test-connection', [ShopeeSyncController::class, 'testConnection'])->name('test-connection');
        });
    });


    // Route::namespace('General')->group(function () {
    //     Route::post('/upload-image', [UpLoadImageController::class, 'uploadImage'])->name('uploadImage');
    //     Route::post('/destroy-image', [UpLoadImageController::class, 'imageDestroy'])->name('imageDestroy');
    //     Route::get('/language', [MultiLanguageController::class, 'index'])->name('language.index');
    // });
});

// Public Shopee OAuth Routes (outside auth middleware)
Route::prefix('shopee')->name('shopee.')->group(function () {
    Route::get('/connect', [ShopeeOAuthController::class, 'connect'])->name('public.connect');
    Route::get('/callback', [ShopeeOAuthController::class, 'callback'])->name('public.callback');
});
