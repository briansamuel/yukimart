<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\QuickOrderService;
use App\Services\OrderService;
use App\Models\Customer;
use App\Models\BranchShop;
use App\Models\BankAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class QuickOrderController extends Controller
{
    protected $quickOrderService;
    protected $orderService;

    public function __construct(QuickOrderService $quickOrderService, OrderService $orderService)
    {
        $this->quickOrderService = $quickOrderService;
        $this->orderService = $orderService;
    }

    /**
     * Display the quick order page
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Get default customer and branch shop from user settings
        $userSettings = Auth::user()->settings ?? collect();
        $defaultCustomerId = $userSettings->get('default_customer_id');
        $defaultBranchShopId = $userSettings->get('branch_shop_id');

        // Get customers and branch shops for dropdowns
        $customers = Customer::active()->orderBy('name')->get();

        // Get branch shops based on user role
        if (Auth::user()->is_root == 1) {
            // Super Admin: Show all branch shops
            $branchShops = BranchShop::active()->orderBy('name')->get();
        } else {
            // Regular User: Show only branch shops they belong to
            $branchShops = Auth::user()->currentBranchShops()->orderBy('name')->get();
        }

        $bankAccounts = BankAccount::getActive();

        // Get default customer
        $defaultCustomer = $defaultCustomerId ? Customer::find($defaultCustomerId) : $customers->first();

        // Get default branch shop
        $defaultBranchShop = $defaultBranchShopId ? BranchShop::find($defaultBranchShopId) : $branchShops->first();

        return view('admin.quick-order.index', compact(
            'customers',
            'branchShops',
            'defaultCustomer',
            'defaultBranchShop',
            'bankAccounts'
        ));
    }

    /**
     * Process quick order submission
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            // Validate request
            $validator = Validator::make($request->all(), [
                'customer_id' => 'nullable|integer',
                'branch_shop_id' => 'required|exists:branch_shops,id',
                'sold_by' => 'required|exists:users,id',
                'channel' => 'required|string',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.price' => 'required|numeric|min:0',
                'payment_method' => 'nullable|string',
                'discount_amount' => 'nullable|numeric|min:0',
                'other_amount' => 'nullable|numeric|min:0',
                'amount_paid' => 'nullable|numeric|min:0',
                'notes' => 'nullable|string|max:1000',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dữ liệu không hợp lệ. Vui lòng kiểm tra lại.',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Additional validation for customer_id
            if (!empty($request->customer_id) && $request->customer_id != 0) {
                $customer = \App\Models\Customer::find($request->customer_id);
                if (!$customer) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Khách hàng không tồn tại.'
                    ], 422);
                }
            }

            // Process the quick order
            $orderData = $this->quickOrderService->processQuickOrder($request->all());

            // Create the order using OrderService
            $orderResult = $this->orderService->createOrder($orderData);

            if (!$orderResult['success']) {
                // Log detailed error for debugging
                Log::error('Order creation failed in service', [
                    'user_id' => Auth::id(),
                    'error_message' => $orderResult['message'] ?? 'Unknown error',
                    'request_data' => $request->all(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Không thể tạo đơn hàng. Vui lòng thử lại sau.'
                ], 500);
            }

            $order = $orderResult['data'];

            Log::info('Quick order created successfully', [
                'order_id' => $order->id,
                'order_code' => $order->order_code,
                'user_id' => Auth::id(),
                'items_count' => count($request->input('items', [])),
                'total_amount' => $order->total_amount,
            ]);

            return response()->json([
                'success' => true,
                'message' => __('Order created successfully'),
                'data' => [
                    'order_id' => $order->id,
                    'order_code' => $order->order_code,
                    'total_amount' => $order->total_amount,
                    'formatted_total' => number_format($order->total_amount, 0, ',', '.') . ' VND',
                    'redirect_url' => route('admin.order.show', $order->id),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Quick order creation failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
            ]);

            // Return user-friendly message without exposing technical details
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tạo đơn hàng. Vui lòng thử lại sau.',
                'data' => null
            ], 500);
        }
    }

    /**
     * Get quick order session data
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSession(Request $request)
    {
        try {
            $sessionData = $this->quickOrderService->getSessionData();

            return response()->json([
                'success' => true,
                'data' => $sessionData
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get quick order session', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => __('Failed to get session data'),
                'data' => null
            ], 500);
        }
    }

    /**
     * Save quick order session data
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveSession(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'items' => 'required|array',
                'customer_id' => 'nullable|exists:customers,id',
                'branch_shop_id' => 'nullable|exists:branch_shops,id',
                'notes' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => __('Validation failed'),
                    'errors' => $validator->errors()
                ], 422);
            }

            $this->quickOrderService->saveSessionData($request->all());

            return response()->json([
                'success' => true,
                'message' => __('Session saved successfully')
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to save quick order session', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => __('Failed to save session data'),
            ], 500);
        }
    }

    /**
     * Clear quick order session
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function clearSession(Request $request)
    {
        try {
            $this->quickOrderService->clearSessionData();

            return response()->json([
                'success' => true,
                'message' => __('Session cleared successfully')
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to clear quick order session', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => __('Failed to clear session'),
            ], 500);
        }
    }

    /**
     * Get order statistics for quick order dashboard
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStatistics(Request $request)
    {
        try {
            $stats = $this->quickOrderService->getOrderStatistics();

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get quick order statistics', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => __('Failed to get statistics'),
                'data' => null
            ], 500);
        }
    }

    /**
     * Validate order before submission
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function validateOrder(Request $request)
    {
        try {
            $validation = $this->quickOrderService->validateOrderData($request->all());

            return response()->json([
                'success' => $validation['valid'],
                'message' => $validation['message'],
                'errors' => $validation['errors'] ?? [],
                'warnings' => $validation['warnings'] ?? [],
            ]);

        } catch (\Exception $e) {
            Log::error('Order validation failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => __('Validation error occurred'),
                'errors' => [$e->getMessage()]
            ], 500);
        }
    }

    /**
     * Search product by barcode or query
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchProduct(Request $request)
    {
        try {
            $barcode = $request->input('barcode');
            $query = $request->input('query');
            $limit = $request->input('limit', 10);

            // If barcode is provided, search by exact barcode
            if (!empty($barcode)) {
                $product = $this->quickOrderService->findProductByBarcode($barcode);

                if (!$product) {
                    return response()->json([
                        'success' => false,
                        'message' => __('Product not found')
                    ], 404);
                }

                return response()->json([
                    'success' => true,
                    'data' => $product
                ]);
            }

            // If query is provided, search by name, SKU, or barcode
            if (!empty($query)) {
                $products = $this->quickOrderService->searchProducts($query, $limit);

                return response()->json([
                    'success' => true,
                    'data' => $products
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => __('Barcode or query is required')
            ], 400);

        } catch (\Exception $e) {
            Log::error('Product search failed', [
                'user_id' => Auth::id(),
                'barcode' => $request->input('barcode'),
                'query' => $request->input('query'),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => __('Search failed')
            ], 500);
        }
    }
}
