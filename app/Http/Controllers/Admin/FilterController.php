<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Order;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\BranchShop;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class FilterController extends Controller
{
    /**
     * Get list of users who have created orders/invoices/payments
     */
    public function getCreators(Request $request): JsonResponse
    {
        try {
            $type = $request->get('type', 'all'); // all, orders, invoices, payments

            // Get current user's branch shops
            $currentUser = auth()->user();
            $userBranchShopIds = $currentUser->currentBranchShops()->pluck('branch_shops.id');

            // If user has no branch shops, return empty result
            if ($userBranchShopIds->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'data' => []
                ]);
            }

            // Get users from the same branch shops
            $query = User::select('users.id', 'users.full_name', 'users.email')
                ->join('user_branch_shops', 'users.id', '=', 'user_branch_shops.user_id')
                ->whereIn('user_branch_shops.branch_shop_id', $userBranchShopIds)
                ->where('user_branch_shops.is_active', true)
                ->where('users.status', 'active')
                ->where(function($q) {
                    $q->whereNull('user_branch_shops.end_date')
                      ->orWhere('user_branch_shops.end_date', '>=', now()->toDateString());
                });

            // Filter by specific type if requested
            switch ($type) {
                case 'orders':
                    $query->whereHas('createdOrders');
                    break;
                case 'invoices':
                    $query->whereHas('createdInvoices');
                    break;
                case 'payments':
                    $query->whereHas('createdPayments');
                    break;
                default:
                    // Get all users who have created any records
                    $query->where(function($q) {
                        $q->whereHas('createdOrders')
                          ->orWhereHas('createdInvoices')
                          ->orWhereHas('createdPayments');
                    });
                    break;
            }

            $creators = $query->orderBy('users.full_name')
                ->distinct()
                ->limit(50)
                ->get()
                ->map(function($user) {
                    return [
                        'id' => $user->id,
                        'value' => $user->id,
                        'text' => $user->full_name,
                        'label' => $user->full_name,
                        'name' => $user->full_name,
                        'email' => $user->email
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $creators
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tải danh sách người tạo: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get list of users who have sold orders/invoices
     */
    public function getSellers(Request $request): JsonResponse
    {
        try {
            $type = $request->get('type', 'all'); // all, orders, invoices

            // Get current user's branch shops
            $currentUser = auth()->user();
            $userBranchShopIds = $currentUser->currentBranchShops()->pluck('branch_shops.id');

            // If user has no branch shops, return empty result
            if ($userBranchShopIds->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'data' => []
                ]);
            }

            // Get users from the same branch shops
            $query = User::select('users.id', 'users.full_name', 'users.email', 'users.phone')
                ->join('user_branch_shops', 'users.id', '=', 'user_branch_shops.user_id')
                ->whereIn('user_branch_shops.branch_shop_id', $userBranchShopIds)
                ->where('user_branch_shops.is_active', true)
                ->where('users.status', 'active')
                ->where(function($q) {
                    $q->whereNull('user_branch_shops.end_date')
                      ->orWhere('user_branch_shops.end_date', '>=', now()->toDateString());
                });

            // Filter by specific type if requested
            switch ($type) {
                case 'orders':
                    $query->whereHas('soldOrders');
                    break;
                case 'invoices':
                    // For invoices, sellers use sold_by field
                    $query->whereIn('id', function($subQuery) use ($userBranchShopIds) {
                        $subQuery->select('sold_by')
                                 ->from('invoices')
                                 ->whereIn('branch_shop_id', $userBranchShopIds)
                                 ->whereNotNull('sold_by')
                                 ->distinct();
                    });
                    break;
                default:
                    // Get all users who have sold orders or created invoices
                    $query->where(function($q) {
                        $q->whereHas('soldOrders')
                          ->orWhereHas('createdInvoices');
                    });
                    break;
            }

            $sellers = $query->orderBy('users.full_name')
                ->distinct()
                ->limit(50)
                ->get()
                ->map(function($user) {
                    return [
                        'id' => $user->id,
                        'value' => $user->id,
                        'text' => $user->full_name,
                        'label' => $user->full_name,
                        'name' => $user->full_name,
                        'email' => $user->email,
                        'phone' => $user->phone
                    ];
                });
            
            return response()->json([
                'success' => true,
                'data' => $sellers
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tải danh sách người bán: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get list of sales channels
     */
    public function getChannels(Request $request): JsonResponse
    {
        try {
            $type = $request->get('type', 'all'); // all, orders, invoices
            
            $channels = collect();
            
            // Get channels from orders
            if ($type === 'all' || $type === 'orders') {
                $orderChannels = Order::select('channel')
                    ->whereNotNull('channel')
                    ->where('channel', '!=', '')
                    ->distinct()
                    ->pluck('channel');
                $channels = $channels->merge($orderChannels);
            }
            
            // Get channels from invoices
            if ($type === 'all' || $type === 'invoices') {
                $invoiceChannels = Invoice::select('sales_channel')
                    ->whereNotNull('sales_channel')
                    ->where('sales_channel', '!=', '')
                    ->distinct()
                    ->pluck('sales_channel');
                $channels = $channels->merge($invoiceChannels);
            }
            
            // Add default sales channels if no data found
            if ($channels->isEmpty()) {
                $defaultChannels = [
                    'offline' => 'Bán tại cửa hàng',
                    'online' => 'Bán online',
                    'shopee' => 'Shopee',
                    'lazada' => 'Lazada',
                    'tiki' => 'Tiki',
                    'marketplace' => 'Marketplace',
                    'social_media' => 'Mạng xã hội',
                    'phone_order' => 'Điện thoại'
                ];

                $channels = collect($defaultChannels)->map(function($label, $value) {
                    return [
                        'id' => $value,
                        'text' => $label,
                        'value' => $value,
                        'label' => $label
                    ];
                })->values();
            } else {
                // Remove duplicates and format existing data
                $channels = $channels->unique()
                    ->sort()
                    ->values()
                    ->map(function($channel) {
                        // Map channel values to display labels
                        $labels = [
                            'offline' => 'Bán tại cửa hàng',
                            'online' => 'Bán online',
                            'shopee' => 'Shopee',
                            'lazada' => 'Lazada',
                            'tiki' => 'Tiki',
                            'marketplace' => 'Marketplace',
                            'social_media' => 'Mạng xã hội',
                            'phone_order' => 'Điện thoại'
                        ];

                        return [
                            'id' => $channel,
                            'text' => $labels[$channel] ?? ucfirst($channel),
                            'value' => $channel,
                            'label' => $labels[$channel] ?? ucfirst($channel)
                        ];
                    });
            }
            
            return response()->json([
                'success' => true,
                'data' => $channels
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tải danh sách kênh bán: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get list of payment methods
     */
    public function getPaymentMethods(Request $request): JsonResponse
    {
        try {
            $methods = collect([
                ['id' => 'cash', 'text' => 'Tiền mặt', 'value' => 'cash'],
                ['id' => 'bank_transfer', 'text' => 'Chuyển khoản', 'value' => 'bank_transfer'],
                ['id' => 'credit_card', 'text' => 'Thẻ tín dụng', 'value' => 'credit_card'],
                ['id' => 'e_wallet', 'text' => 'Ví điện tử', 'value' => 'e_wallet'],
                ['id' => 'cod', 'text' => 'Thu hộ COD', 'value' => 'cod'],
                ['id' => 'other', 'text' => 'Khác', 'value' => 'other']
            ]);
            
            return response()->json([
                'success' => true,
                'data' => $methods
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tải danh sách phương thức thanh toán: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get all filter options for a specific module
     */
    public function getAllFilters(Request $request): JsonResponse
    {
        try {
            $module = $request->get('module', 'all'); // orders, invoices, payments, all
            
            $data = [
                'creators' => $this->getCreators($request)->getData()->data ?? [],
                'sellers' => $this->getSellers($request)->getData()->data ?? [],
                'channels' => $this->getChannels($request)->getData()->data ?? [],
                'payment_methods' => $this->getPaymentMethods($request)->getData()->data ?? []
            ];
            
            return response()->json([
                'success' => true,
                'data' => $data,
                'module' => $module
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tải dữ liệu filter: ' . $e->getMessage()
            ], 500);
        }
    }
}
