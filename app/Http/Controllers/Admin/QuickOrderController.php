<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\QuickOrderService;
use App\Services\OrderService;
use App\Services\ReturnOrderService;
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
    protected $returnOrderService;

    public function __construct(QuickOrderService $quickOrderService, OrderService $orderService, ReturnOrderService $returnOrderService)
    {
        $this->quickOrderService = $quickOrderService;
        $this->orderService = $orderService;
        $this->returnOrderService = $returnOrderService;
    }

    /**
     * Display the quick order page
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
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

        // Check if this is a return order request
        $isReturnOrder = $request->get('type') === 'return';

        return view('admin.quick-order.index', compact(
            'customers',
            'branchShops',
            'defaultCustomer',
            'defaultBranchShop',
            'bankAccounts',
            'isReturnOrder'
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
                    'message' => $orderResult['message'] ?? 'Không thể tạo đơn hàng. Vui lòng thử lại sau.'
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
                    'redirect_url' => route('admin.order.list') . '?Code=' . $order->order_code,
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

    /**
     * Get invoices for return selection
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getInvoicesForReturn(Request $request)
    {
        try {
            $search = $request->input('search');
            $timeFilter = $request->input('time_filter', 'this_month');
            $customerFilter = $request->input('customer_filter');

            // Get user's branch shops
            try {
                $userBranchShops = Auth::user()->currentBranchShops()->pluck('id');
            } catch (\Exception $e) {
                Log::error('Error getting user branch shops', ['error' => $e->getMessage()]);
                // Fallback: get all branch shops for now
                $userBranchShops = collect([1]); // Temporary fallback
            }

            // Build query
            $query = \App\Models\Invoice::with(['customer', 'seller', 'creator', 'invoiceItems'])
                ->whereIn('branch_shop_id', $userBranchShops)
                ->where('status', 'paid') // Only paid invoices can be returned
                ->orderBy('created_at', 'desc');

            // Apply time filter
            switch ($timeFilter) {
                case 'this_month':
                    $query->whereMonth('created_at', now()->month)
                          ->whereYear('created_at', now()->year);
                    break;
                case 'last_month':
                    $query->whereMonth('created_at', now()->subMonth()->month)
                          ->whereYear('created_at', now()->subMonth()->year);
                    break;
                case 'this_year':
                    $query->whereYear('created_at', now()->year);
                    break;
            }

            // Apply search filter
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('invoice_number', 'like', "%{$search}%")
                      ->orWhereHas('customer', function($customerQuery) use ($search) {
                          $customerQuery->where('name', 'like', "%{$search}%")
                                       ->orWhere('phone', 'like', "%{$search}%");
                      });
                });
            }

            // Apply customer filter
            if ($customerFilter) {
                $query->whereHas('customer', function($customerQuery) use ($customerFilter) {
                    $customerQuery->where('name', 'like', "%{$customerFilter}%");
                });
            }

            $invoices = $query->limit(50)->get();

            $data = $invoices->map(function($invoice) {
                // Debug seller information
                $sellerName = 'N/A';
                $debugInfo = [
                    'sold_by' => $invoice->sold_by,
                    'created_by' => $invoice->created_by,
                    'seller_loaded' => !!$invoice->seller,
                    'creator_loaded' => !!$invoice->creator,
                    'seller_name' => $invoice->seller?->name ?? 'null',
                    'creator_name' => $invoice->creator?->name ?? 'null'
                ];

                if ($invoice->seller && $invoice->seller->name) {
                    $sellerName = $invoice->seller->name;
                } elseif ($invoice->creator && $invoice->creator->name) {
                    $sellerName = $invoice->creator->name;
                } else {
                    // Try to load manually
                    if ($invoice->sold_by) {
                        $seller = \App\Models\User::find($invoice->sold_by);
                        if ($seller) {
                            $sellerName = $seller->name;
                            $debugInfo['manual_seller'] = $seller->name;
                        } else {
                            $sellerName = 'Nhân viên đã xóa (ID: ' . $invoice->sold_by . ')';
                            $debugInfo['manual_seller'] = 'User ID ' . $invoice->sold_by . ' not found';
                        }
                    } elseif ($invoice->created_by) {
                        $creator = \App\Models\User::find($invoice->created_by);
                        if ($creator) {
                            $sellerName = $creator->name;
                            $debugInfo['manual_creator'] = $creator->name;
                        } else {
                            $sellerName = 'Nhân viên đã xóa';
                            $debugInfo['manual_creator'] = 'User ID ' . $invoice->created_by . ' not found';
                        }
                    }
                }

                return [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'created_at' => $invoice->created_at->format('Y-m-d H:i:s'),
                    'seller_name' => $sellerName,
                    'customer_name' => $invoice->customer->name ?? 'Khách lẻ',
                    'customer_phone' => $invoice->customer->phone ?? '',
                    'total_amount' => $invoice->total_amount,
                    'items_count' => $invoice->invoiceItems->count(),
                    // Debug info
                    'debug' => $debugInfo
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            Log::error('Get invoices for return failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tải danh sách hóa đơn'
            ], 500);
        }
    }

    /**
     * Get invoice items for return
     *
     * @param Request $request
     * @param int $invoiceId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getInvoiceItems(Request $request, $invoiceId)
    {
        try {
            Log::info('getInvoiceItems called', ['invoice_id' => $invoiceId, 'user_id' => Auth::id()]);

            // Get user's branch shops (with fallback)
            try {
                $userBranchShops = Auth::user()->currentBranchShops()->pluck('branch_shops.id');
                Log::info('User branch shops', ['branch_shops' => $userBranchShops->toArray()]);
            } catch (\Exception $e) {
                Log::error('Error getting user branch shops in getInvoiceItems', ['error' => $e->getMessage()]);
                $userBranchShops = collect([1]); // Temporary fallback
                Log::info('Using fallback branch shops', ['branch_shops' => $userBranchShops->toArray()]);
            }

            // First check if invoice exists
            $invoiceExists = \App\Models\Invoice::where('id', $invoiceId)->exists();
            Log::info('Invoice exists check', ['invoice_id' => $invoiceId, 'exists' => $invoiceExists]);

            if (!$invoiceExists) {
                Log::error('Invoice not found', ['invoice_id' => $invoiceId]);
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy hóa đơn'
                ], 404);
            }

            // Check invoice with branch shop filter
            $invoiceInBranch = \App\Models\Invoice::whereIn('branch_shop_id', $userBranchShops)
                ->where('id', $invoiceId)
                ->exists();
            Log::info('Invoice in user branch check', ['invoice_id' => $invoiceId, 'in_branch' => $invoiceInBranch]);

            // Check invoice status
            $invoiceStatus = \App\Models\Invoice::where('id', $invoiceId)->value('status');
            Log::info('Invoice status check', ['invoice_id' => $invoiceId, 'status' => $invoiceStatus]);

            // Try to find invoice with different statuses
            $invoice = \App\Models\Invoice::with(['customer', 'invoiceItems.product', 'creator', 'seller'])
                ->whereIn('branch_shop_id', $userBranchShops)
                ->where('id', $invoiceId)
                ->first();

            if (!$invoice) {
                Log::error('Invoice not found in user branches', ['invoice_id' => $invoiceId, 'user_branches' => $userBranchShops->toArray()]);
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy hóa đơn trong chi nhánh của bạn'
                ], 404);
            }

            // Check if invoice status allows returns
            $allowedStatuses = ['paid', 'completed', 'delivered'];
            if (!in_array($invoice->status, $allowedStatuses)) {
                Log::error('Invoice status not allowed for return', ['invoice_id' => $invoiceId, 'status' => $invoice->status, 'allowed' => $allowedStatuses]);
                return response()->json([
                    'success' => false,
                    'message' => 'Hóa đơn này không thể trả hàng (trạng thái: ' . $invoice->status . ')'
                ], 400);
            }

            Log::info('Invoice loaded successfully', [
                'invoice_id' => $invoiceId,
                'items_count' => $invoice->invoiceItems->count(),
                'customer_id' => $invoice->customer_id,
                'branch_shop_id' => $invoice->branch_shop_id
            ]);

            $data = $invoice->invoiceItems->map(function($item) use ($invoice) {
                // Calculate total returned quantity for this item
                $totalReturned = \App\Models\ReturnOrderItem::whereHas('returnOrder', function($query) use ($invoice) {
                        $query->where('invoice_id', $invoice->id)
                              ->where('status', '!=', 'rejected');
                    })
                    ->where('invoice_item_id', $item->id)
                    ->sum('quantity_returned');

                $returnableQuantity = max(0, $item->quantity - $totalReturned);

                return [
                    'product_id' => $item->product_id,
                    'product_name' => $item->product_name,
                    'product_sku' => $item->product_sku,
                    'price' => $item->unit_price, // Fix: use unit_price instead of price
                    'unit_price' => $item->unit_price, // Also include unit_price for compatibility
                    'quantity' => $item->quantity,
                    'stock_quantity' => $item->product->stock_quantity ?? 0,
                    'product_image' => $item->product->image ?? null,
                    'max_quantity' => $item->quantity, // Original quantity
                    'returned_quantity' => $totalReturned, // Already returned quantity
                    'returnable_quantity' => $returnableQuantity, // Available for return
                    'invoice_item_id' => $item->id // Add invoice_item_id for reference
                ];
            });

            // Determine seller name with priority: seller -> creator -> fallback
            $sellerName = 'N/A';
            if ($invoice->seller && $invoice->seller->full_name) {
                $sellerName = $invoice->seller->full_name;
            } elseif ($invoice->creator && $invoice->creator->full_name) {
                $sellerName = $invoice->creator->full_name;
            }

            // Determine creator name
            $creatorName = 'N/A';
            if ($invoice->creator && $invoice->creator->full_name) {
                $creatorName = $invoice->creator->full_name;
            }

            return response()->json([
                'success' => true,
                'data' => $data,
                'customer_name' => $invoice->customer->name ?? 'Khách lẻ',
                'customer_phone' => $invoice->customer->phone ?? '',
                'seller_name' => $sellerName,
                'creator_name' => $creatorName
            ]);

        } catch (\Exception $e) {
            Log::error('Get invoice items failed', [
                'user_id' => Auth::id(),
                'invoice_id' => $invoiceId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tải sản phẩm từ hóa đơn: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store return order from quick order interface
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeReturnOrder(Request $request)
    {
        try {
            // Validate request
            $validator = Validator::make($request->all(), [
                'invoice_id' => 'required|integer|exists:invoices,id',
                'invoice_number' => 'required|string',
                'customer_name' => 'nullable|string',
                'customer_phone' => 'nullable|string',
                'return_items' => 'required|array|min:1',
                'return_items.*.product_id' => 'required|integer|exists:products,id',
                'return_items.*.product_name' => 'required|string',
                'return_items.*.product_sku' => 'required|string',
                'return_items.*.quantity' => 'required|integer|min:1',
                'return_items.*.price' => 'required|numeric|min:0',
                'exchange_items' => 'nullable|array',
                'exchange_items.*.product_id' => 'required|integer|exists:products,id',
                'exchange_items.*.quantity' => 'required|integer|min:1',
                'exchange_items.*.price' => 'required|numeric|min:0',
                'payment_method' => 'nullable|string|in:cash,transfer,card,ewallet',
                'notes' => 'nullable|string|max:1000',
                'return_subtotal' => 'nullable|numeric|min:0',
                'exchange_subtotal' => 'nullable|numeric|min:0',
                'refund_amount' => 'nullable|numeric',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dữ liệu không hợp lệ. Vui lòng kiểm tra lại.',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Get user's branch shop
            $userBranchShops = Auth::user()->currentBranchShops()->pluck('branch_shops.id')->toArray();
            if (empty($userBranchShops)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn không có quyền truy cập chi nhánh nào.'
                ], 403);
            }

            // Calculate net_payable for exchange orders
            $exchangeSubtotal = $request->exchange_subtotal ?? 0;
            $refundAmount = $request->refund_amount ?? 0;
            $netPayable = max(0, $exchangeSubtotal - $refundAmount);

            // Prepare data for ReturnOrderService
            $returnOrderData = [
                'invoice_code' => $request->invoice_number, // ReturnOrderService expects 'invoice_code'
                'branch_shop_id' => $userBranchShops[0], // Use first available branch shop
                'return_items' => $request->return_items,
                'exchange_items' => $request->exchange_items ?? [],
                'payment_method' => $request->payment_method ?? 'cash',
                'notes' => $request->notes,
                'return_subtotal' => $request->return_subtotal ?? 0,
                'exchange_subtotal' => $exchangeSubtotal,
                'refund_amount' => $refundAmount,
                'net_payable' => $netPayable, // Add net_payable for exchange order processing
            ];

            // Create return order using ReturnOrderService
            $result = $this->returnOrderService->createQuickOrderReturn($returnOrderData);

            if (!$result['success']) {
                Log::error('Return order creation failed in service', [
                    'user_id' => Auth::id(),
                    'error_message' => $result['message'] ?? 'Unknown error',
                    'request_data' => $request->all(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'Không thể tạo đơn trả hàng. Vui lòng thử lại sau.'
                ], 500);
            }

            $returnOrderData = $result['data'];
            $returnOrder = $returnOrderData['return_order'];

            Log::info('Return order created successfully', [
                'return_order_id' => $returnOrder->id,
                'return_number' => $returnOrder->return_number,
                'invoice_id' => $request->invoice_id,
                'user_id' => Auth::id(),
                'items_count' => count($request->input('return_items', [])),
                'refund_amount' => $returnOrder->total_amount,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Đơn trả hàng đã được tạo thành công',
                'data' => [
                    'return_order_id' => $returnOrder->id,
                    'return_number' => $returnOrder->return_number,
                    'total_amount' => $returnOrder->total_amount,
                    'formatted_total' => number_format($returnOrder->total_amount, 0, ',', '.') . ' VND',
                    'redirect_url' => route('admin.returns.index') . '?return_number=' . $returnOrder->return_number,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Return order creation failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
            ]);

            // Return user-friendly message without exposing technical details
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tạo đơn trả hàng. Vui lòng thử lại sau.',
                'data' => null
            ], 500);
        }
    }
}
