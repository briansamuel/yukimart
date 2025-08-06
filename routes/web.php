<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\LoginController;

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

// Laravel File Manager Routes


Route::get('/', function () {
    return view('welcome');
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

// Admin routes
Route::prefix('admin')->group(function () {
    // Login routes
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/login', [LoginController::class, 'login'])->name('admin.login.post');
    Route::post('/logout', [LoginController::class, 'logout'])->name('admin.logout');

    // Protected admin routes
    Route::middleware(['auth'])->group(function () {
        Route::get('/', function () {
            return view('admin.dashboard');
        })->name('admin.dashboard');

        // Dashboard API routes
        Route::get('/dashboard/stats', [\App\Http\Controllers\Admin\DashboardController::class, 'getStats'])->name('admin.dashboard.stats');
        Route::get('/dashboard/recent-orders', [\App\Http\Controllers\Admin\DashboardController::class, 'getRecentOrders'])->name('admin.dashboard.recent-orders');
        Route::get('/dashboard/top-products', [\App\Http\Controllers\Admin\DashboardController::class, 'getTopProducts'])->name('admin.dashboard.top-products');

    });
});


