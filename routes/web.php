<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Non-tenant routes for the main application.
| Tenant-specific routes are defined in routes/tenant.php
|
*/

// ============================================================================
// WELCOME PAGE
// ============================================================================
Route::get('/', function () {
    return view('welcome');
});

// ============================================================================
// DEBUG & TEST ROUTES
// ============================================================================

// Debug route to check orders count and schema
Route::get('/debug-orders-count', function () {
        try {
            $count = \App\Models\Order::count();
            $orders = \App\Models\Order::with(['customer', 'branchShop'])->take(5)->get();

            // Get table columns
            $columns = \Illuminate\Support\Facades\Schema::getColumnListing('orders');

            return response()->json([
                'orders_count' => $count,
                'table_columns' => $columns,
                'sample_orders' => $orders->map(function($order) {
                    return [
                        'id' => $order->id,
                        'order_code' => $order->order_code,
                        'customer_name' => $order->customer_id == 0 ? 'Khách lẻ' : ($order->customer->name ?? 'N/A'),
                        'total_amount' => $order->total_amount,
                        'status' => $order->status,
                        'created_at' => $order->created_at
                    ];
                })
            ]);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    });

    // Debug route to check tenant info
    Route::get('/debug-tenant-info', function () {
        try {
            // Get current tenant info
            $tenantContextService = app(\App\Services\TenantContextService::class);
            $currentTenant = $tenantContextService->getCurrentTenant();
            $currentTenantId = $tenantContextService->getCurrentTenantId();

            // Get all tenants
            $allTenants = \App\Models\Tenant::all(['id', 'name', 'subdomain', 'slug', 'status']);

            // Get tenant1 specifically
            $tenant1 = \App\Models\Tenant::where('subdomain', 'tenant1')->first();

            // Get orders for tenant1 if it exists
            $tenant1Orders = null;
            if ($tenant1) {
                $tenant1Orders = \App\Models\Order::withoutGlobalScope(\App\Scopes\TenantScope::class)
                                                 ->where('tenant_id', $tenant1->id)
                                                 ->take(5)
                                                 ->get(['id', 'order_code', 'tenant_id', 'status']);
            }

            // Get order 200 info
            $order200 = \App\Models\Order::withoutGlobalScope(\App\Scopes\TenantScope::class)
                                         ->where('id', 200)
                                         ->first(['id', 'order_code', 'tenant_id', 'status']);

            return response()->json([
                'current_tenant' => $currentTenant ? [
                    'id' => $currentTenant->id,
                    'name' => $currentTenant->name,
                    'subdomain' => $currentTenant->subdomain,
                    'slug' => $currentTenant->slug
                ] : null,
                'current_tenant_id' => $currentTenantId,
                'all_tenants' => $allTenants,
                'tenant1_info' => $tenant1 ? [
                    'id' => $tenant1->id,
                    'name' => $tenant1->name,
                    'subdomain' => $tenant1->subdomain,
                    'slug' => $tenant1->slug
                ] : null,
                'tenant1_orders' => $tenant1Orders,
                'order_200_info' => $order200,
                'session_tenant_id' => session('current_tenant_id'),
                'bound_tenant' => app()->bound('current_tenant') ? app('current_tenant') : null
            ]);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    });

    // Debug route to test TenantScope
    Route::get('/debug-tenant-scope', function () {
        try {
            // Test TenantScope getCurrentTenantId
            $tenantScope = new \App\Scopes\TenantScope();
            $reflection = new ReflectionClass($tenantScope);
            $getCurrentTenantIdMethod = $reflection->getMethod('getCurrentTenantId');
            $getCurrentTenantIdMethod->setAccessible(true);
            $tenantIdFromScope = $getCurrentTenantIdMethod->invoke($tenantScope);

            // Test TenantContextService
            $tenantContextService = app(\App\Services\TenantContextService::class);
            $tenantIdFromService = $tenantContextService->getCurrentTenantId();

            // Test Order query with and without scope
            $orderWithScope = null;
            $orderWithoutScope = null;
            $scopeError = null;

            try {
                $orderWithScope = \App\Models\Order::where('id', 200)->first();
            } catch (Exception $e) {
                $scopeError = $e->getMessage();
            }

            $orderWithoutScope = \App\Models\Order::withoutGlobalScope(\App\Scopes\TenantScope::class)
                                                  ->where('id', 200)
                                                  ->first();

            // Test query builder to see what SQL is generated
            $queryBuilder = \App\Models\Order::where('id', 200);
            $sql = $queryBuilder->toSql();
            $bindings = $queryBuilder->getBindings();

            return response()->json([
                'tenant_id_from_scope' => $tenantIdFromScope,
                'tenant_id_from_service' => $tenantIdFromService,
                'order_with_scope' => $orderWithScope,
                'order_without_scope' => $orderWithoutScope ? [
                    'id' => $orderWithoutScope->id,
                    'order_code' => $orderWithoutScope->order_code,
                    'tenant_id' => $orderWithoutScope->tenant_id
                ] : null,
                'scope_error' => $scopeError,
                'generated_sql' => $sql,
                'sql_bindings' => $bindings,
                'session_tenant_id' => session('current_tenant_id'),
                'bound_tenant' => app()->bound('current_tenant') ? app('current_tenant')->id : null
            ]);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    });

    // Debug route to test OrderController get method
    Route::get('/debug-order-get/{order_id}', function () {
        try {
            // Get order_id from route parameters explicitly
            $order_id = request()->route('order_id');

            // Debug parameter binding
            $debugInfo = [
                'received_id' => $order_id,
                'id_type' => gettype($order_id),
                'id_value' => var_export($order_id, true),
                'route_params' => request()->route()->parameters(),
                'request_attributes' => request()->attributes->all()
            ];

            // Test query step by step
            $queryBuilder = \App\Models\Order::where('id', $order_id);
            $sql = $queryBuilder->toSql();
            $bindings = $queryBuilder->getBindings();

            // Try to find order
            $order = $queryBuilder->first();

            return response()->json([
                'debug_info' => $debugInfo,
                'query_sql' => $sql,
                'query_bindings' => $bindings,
                'order_found' => $order ? true : false,
                'order_data' => $order ? [
                    'id' => $order->id,
                    'order_code' => $order->order_code,
                    'tenant_id' => $order->tenant_id
                ] : null
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    });

    // Route to create sample orders for testing
    Route::get('/create-sample-orders', function () {
        try {
            // Get current user and branch shop
            $user = auth('admin')->user();
            $branchShop = \App\Models\BranchShop::first();

            if (!$user || !$branchShop) {
                return response()->json([
                    'error' => 'User or branch shop not found. Please login first.',
                    'user' => $user ? 'Found' : 'Not found',
                    'branch_shop' => $branchShop ? 'Found' : 'Not found'
                ]);
            }

            // Get current tenant ID from session or config
            $tenantId = session('tenant_id', 3); // Default to tenant 3 (TechMart)

            // Create sample customers first
            $customers = [];
            for ($i = 1; $i <= 3; $i++) {
                $customer = \App\Models\Customer::firstOrCreate([
                    'phone' => '090123456' . $i,
                    'tenant_id' => $tenantId
                ], [
                    'name' => 'Khách hàng ' . $i,
                    'email' => 'customer' . $i . '@example.com',
                    'address' => 'Địa chỉ khách hàng ' . $i,
                    'tenant_id' => $tenantId,
                    'created_by' => $user->id
                ]);
                $customers[] = $customer;
            }

            // Sample order data - using correct database schema
            $sampleOrders = [
                [
                    'order_code' => 'DH' . date('Ymd') . '001',
                    'customer_id' => $customers[0]->id,
                    'total_quantity' => 2,
                    'total_amount' => 500000,
                    'final_amount' => 500000,
                    'amount_paid' => 500000,
                    'status' => 'completed',
                    'payment_status' => 'paid',
                    'delivery_status' => 'delivered',
                    'notes' => 'Đơn hàng mẫu 1',
                    'tenant_id' => $tenantId
                ],
                [
                    'order_code' => 'DH' . date('Ymd') . '002',
                    'customer_id' => $customers[1]->id,
                    'total_quantity' => 1,
                    'total_amount' => 300000,
                    'final_amount' => 300000,
                    'amount_paid' => 150000,
                    'status' => 'processing',
                    'payment_status' => 'partial',
                    'delivery_status' => 'shipping',
                    'notes' => 'Đơn hàng mẫu 2',
                    'tenant_id' => $tenantId
                ],
                [
                    'order_code' => 'DH' . date('Ymd') . '003',
                    'customer_id' => 0, // Walk-in customer
                    'total_quantity' => 3,
                    'total_amount' => 750000,
                    'final_amount' => 750000,
                    'amount_paid' => 0,
                    'status' => 'draft',
                    'payment_status' => 'pending',
                    'delivery_status' => 'pending',
                    'notes' => 'Đơn hàng khách lẻ',
                    'tenant_id' => $tenantId
                ],
                [
                    'order_code' => 'DH' . date('Ymd') . '004',
                    'customer_id' => $customers[2]->id,
                    'total_quantity' => 1,
                    'total_amount' => 200000,
                    'final_amount' => 200000,
                    'amount_paid' => 200000,
                    'status' => 'cancelled',
                    'payment_status' => 'refunded',
                    'delivery_status' => 'cancelled',
                    'notes' => 'Đơn hàng bị hủy',
                    'tenant_id' => $tenantId
                ]
            ];

            $createdOrders = [];
            foreach ($sampleOrders as $orderData) {
                // Check if order already exists
                $existingOrder = \App\Models\Order::where('order_code', $orderData['order_code'])->first();
                if ($existingOrder) {
                    continue; // Skip if already exists
                }

                $order = \App\Models\Order::create(array_merge($orderData, [
                    'branch_shop_id' => $branchShop->id,
                    'created_by' => $user->id,
                    'sold_by' => $user->id,
                    'created_at' => now()->subDays(rand(0, 7)), // Random dates within last week
                    'updated_at' => now()
                ]));

                $createdOrders[] = $order;
            }

            return response()->json([
                'success' => true,
                'message' => 'Sample orders created successfully!',
                'created_orders_count' => count($createdOrders),
                'total_orders_count' => \App\Models\Order::count(),
                'created_orders' => $createdOrders->map(function($order) {
                    return [
                        'id' => $order->id,
                        'order_code' => $order->order_code,
                        'customer_id' => $order->customer_id,
                        'total_amount' => $order->total_amount,
                        'status' => $order->status
                    ];
                })
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    });





// Debug route for return order creation
Route::get('/debug-return', function () {
    try {
        \Log::info('=== DEBUG RETURN ORDER CREATION ===');

        // Test data similar to what frontend sends
        $testData = [
            'invoice_id' => '1851',
            'invoice_code' => 'INV-20250701-3184',
            'branch_shop_id' => 1,
            'return_items' => [
                [
                    'product_id' => '81',
                    'product_name' => 'Test Product 1',
                    'product_sku' => '4901234299313',
                    'price' => '205000',
                    'quantity' => '1',
                    'original_quantity' => '1'
                ]
            ],
            'exchange_items' => [],
            'payment_method' => 'cash',
            'notes' => null,
            'return_subtotal' => '205000',
            'exchange_subtotal' => '0',
            'refund_amount' => '205000'
        ];

        \Log::info('Test data prepared', $testData);

        // Test service call with dependencies
        $paymentService = app(\App\Services\PaymentService::class);
        $inventoryService = app(\App\Services\InventoryService::class);
        $service = new \App\Services\ReturnOrderService($paymentService, $inventoryService);
        \Log::info('Service instantiated');

        $result = $service->createQuickOrderReturn($testData, 1);
        \Log::info('Service call completed', ['result' => $result]);

        return response()->json([
            'success' => true,
            'message' => 'Debug completed successfully',
            'result' => $result
        ]);

    } catch (Exception $e) {
        \Log::error('Debug failed', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'line' => $e->getLine(),
            'file' => $e->getFile()
        ], 500);
    }
});

// Test route for debugging return order creation
Route::get('/test-return-order', function () {
    try {
        \Illuminate\Support\Facades\Log::info('=== TESTING RETURN ORDER CREATION ===');

        // Test 1: Check if ReturnOrder model exists
        \Illuminate\Support\Facades\Log::info('Testing ReturnOrder model...');
        $returnOrderClass = new \App\Models\ReturnOrder();
        \Illuminate\Support\Facades\Log::info('✓ ReturnOrder model loaded successfully');

        // Test 2: Check database connection
        \Illuminate\Support\Facades\Log::info('Testing database connection...');
        $count = \Illuminate\Support\Facades\DB::table('return_orders')->count();
        \Illuminate\Support\Facades\Log::info('✓ Database connection OK', ['existing_returns' => $count]);

        // Test 3: Test return number generation
        \Illuminate\Support\Facades\Log::info('Testing return number generation...');
        $returnNumber = \App\Models\ReturnOrder::generateReturnNumber();
        \Illuminate\Support\Facades\Log::info('✓ Return number generated', ['return_number' => $returnNumber]);

        // Test 4: Test simple creation
        \Illuminate\Support\Facades\Log::info('Testing simple return order creation...');
        $returnOrder = new \App\Models\ReturnOrder();
        $returnOrder->invoice_id = 1851;
        $returnOrder->customer_id = 1;
        $returnOrder->branch_shop_id = 1;
        $returnOrder->return_date = now();
        $returnOrder->reason = 'customer_request';
        $returnOrder->refund_method = 'cash';
        $returnOrder->subtotal = 0;
        $returnOrder->total_amount = 0;
        $returnOrder->status = 'pending';
        $returnOrder->created_by = 1;

        Log::info('Saving return order...');
        $returnOrder->save();
        Log::info('✓ Return order created successfully', [
            'id' => $returnOrder->id,
            'return_number' => $returnOrder->return_number
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Test completed successfully',
            'return_order' => $returnOrder
        ]);

    } catch (Exception $e) {
        Log::error('Test failed', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
});



Route::get('/test-payment-summary', function (\Illuminate\Http\Request $request) {
    try {
        // Test with this_month filter
        $request->merge(['time_filter' => 'this_month']);

        $controller = new \App\Http\Controllers\Admin\CMS\PaymentController(new \App\Services\PaymentService());
        $response = $controller->getSummary($request);
        $data = json_decode($response->getContent(), true);

        return response()->json([
            'success' => true,
            'message' => 'Testing with this_month filter',
            'controller_response' => $data,
            'raw_data' => [
                'total_payments' => \App\Models\Payment::count(),
                'total_income_all' => \App\Models\Payment::where('payment_type', 'receipt')->sum('amount'),
                'total_expense_all' => \App\Models\Payment::where('payment_type', 'payment')->sum('amount'),
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});

// Test admin auth status
Route::get('/test-admin-auth', function () {
    return response()->json([
        'authenticated' => auth()->check(),
        'user' => auth()->user() ? [
            'id' => auth()->user()->id,
            'name' => auth()->user()->full_name,
            'email' => auth()->user()->email
        ] : null,
        'session_id' => session()->getId(),
        'csrf_token' => csrf_token()
    ]);
});

// Test get first user
Route::get('/test-get-user', function () {
    try {
        $user = \App\Models\User::first();
        return response()->json([
            'success' => true,
            'user' => $user ? [
                'id' => $user->id,
                'email' => $user->email,
                'name' => $user->full_name
            ] : null,
            'total_users' => \App\Models\User::count()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
});

// Test payment summary without auth
Route::get('/test-payment-summary-direct', function (\Illuminate\Http\Request $request) {
    try {
        // Test with this_month filter
        $request->merge(['time_filter' => 'this_month']);

        $controller = new \App\Http\Controllers\Admin\CMS\PaymentController(new \App\Services\PaymentService());
        $response = $controller->getSummary($request);
        $data = json_decode($response->getContent(), true);

        return response()->json([
            'success' => true,
            'message' => 'Direct controller test - no auth required',
            'route_fixed' => true,
            'controller_response' => $data
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});

// Test payment pagination without auth
Route::get('/test-payment-pagination', function (\Illuminate\Http\Request $request) {
    try {
        $controller = new \App\Http\Controllers\Admin\CMS\PaymentController(new \App\Services\PaymentService());
        $response = $controller->getPaymentsAjax($request);
        $data = json_decode($response->getContent(), true);

        return response()->json([
            'success' => true,
            'message' => 'Pagination test - no auth required',
            'request_params' => $request->all(),
            'controller_response' => $data
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});

// Language change route
Route::get('/change-language/{language}', function ($language) {
    if (in_array($language, ['en', 'vi'])) {
        session(['locale' => $language]);
        app()->setLocale($language);
    }
    return redirect()->back();
})->name('change-language');

// Main domain routes (yukimart.local) - Platform detection
Route::domain(config('tenancy.app_domain', 'yukimart.local'))->group(function () {

    // Check if this is a platform request
    Route::middleware(['platform.detect'])->group(function () {

        // Platform routes (when accessing yukimart.local directly)
        Route::prefix('admin')->group(function () {
            // Platform login routes
            Route::get('/login', [\App\Http\Controllers\Platform\AuthController::class, 'showLoginForm'])->name('admin.login');
            Route::post('/login', [\App\Http\Controllers\Platform\AuthController::class, 'login'])->name('admin.login.post');
            Route::post('/logout', [\App\Http\Controllers\Platform\AuthController::class, 'logout'])->name('admin.logout');

            // Protected platform routes
            Route::middleware(['auth:platform'])->group(function () {
                Route::get('/', [\App\Http\Controllers\Platform\AuthController::class, 'dashboard'])->name('admin.dashboard');
                Route::get('/dashboard', [\App\Http\Controllers\Platform\AuthController::class, 'dashboard'])->name('admin.platform.dashboard');
            });
        });

        // Redirect root to platform login
        Route::get('/', function () {
            return redirect()->route('admin.login');
        });
    });
});


