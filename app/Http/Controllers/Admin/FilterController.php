<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Order;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\BranchShop;
use App\Models\ProductCategory;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\BankAccount;
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

            // Disable branch shop filtering - return all users for admin access
            $filterByBranchShops = false;
            $userBranchShopIds = collect();

            // Get users - filter by branch shops if user has branch shops
            if ($filterByBranchShops) {
                $query = User::select('users.id', 'users.full_name', 'users.email')
                    ->join('user_branch_shops', 'users.id', '=', 'user_branch_shops.user_id')
                    ->whereIn('user_branch_shops.branch_shop_id', $userBranchShopIds)
                    ->where('user_branch_shops.is_active', true)
                    ->where('users.status', 'active')
                    ->where(function($q) {
                        $q->whereNull('user_branch_shops.end_date')
                          ->orWhere('user_branch_shops.end_date', '>=', now()->toDateString());
                    });
            } else {
                // If no branch shops, get all users (for admin or setup scenarios)
                $query = User::select('users.id', 'users.full_name', 'users.email')
                    ->where('users.status', 'active');
            }

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

            // Disable branch shop filtering - return all users for admin access
            $filterByBranchShops = false;
            $userBranchShopIds = collect();

            // Get users - filter by branch shops if user has branch shops
            if ($filterByBranchShops) {
                $query = User::select('users.id', 'users.full_name', 'users.email', 'users.phone')
                    ->join('user_branch_shops', 'users.id', '=', 'user_branch_shops.user_id')
                    ->whereIn('user_branch_shops.branch_shop_id', $userBranchShopIds)
                    ->where('user_branch_shops.is_active', true)
                    ->where('users.status', 'active')
                    ->where(function($q) {
                        $q->whereNull('user_branch_shops.end_date')
                          ->orWhere('user_branch_shops.end_date', '>=', now()->toDateString());
                    });
            } else {
                // If no branch shops, get all users (for admin or setup scenarios)
                $query = User::select('users.id', 'users.full_name', 'users.email', 'users.phone')
                    ->where('users.status', 'active');
            }

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
     * Get list of product categories with hierarchical structure
     */
    public function getProductCategories(Request $request): JsonResponse
    {
        try {
            $format = $request->get('format', 'flat'); // flat, tree

            if ($format === 'tree') {
                // Get tree structure with parent-child relationships
                $categories = ProductCategory::with('children')
                    ->whereNull('parent_id')
                    ->where('is_active', 1)
                    ->orderBy('sort_order', 'asc')
                    ->orderBy('name', 'asc')
                    ->get();

                $tree = $this->buildCategoryTree($categories);

                return response()->json([
                    'success' => true,
                    'data' => $tree
                ]);
            } else {
                // Get flat list with level indication
                $categories = ProductCategory::where('is_active', 1)
                    ->orderBy('sort_order', 'asc')
                    ->orderBy('name', 'asc')
                    ->get();

                $flatCategories = [];
                foreach ($categories as $category) {
                    $level = $this->getCategoryLevel($category);
                    $prefix = str_repeat('— ', $level);

                    $flatCategories[] = [
                        'id' => $category->id,
                        'value' => $category->id,
                        'text' => $prefix . $category->name,
                        'label' => $prefix . $category->name,
                        'name' => $category->name,
                        'parent_id' => $category->parent_id,
                        'level' => $level,
                        'is_active' => $category->is_active
                    ];
                }

                return response()->json([
                    'success' => true,
                    'data' => $flatCategories
                ]);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tải danh mục sản phẩm: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Build category tree recursively
     */
    private function buildCategoryTree($categories)
    {
        $tree = [];

        foreach ($categories as $category) {
            $node = [
                'id' => $category->id,
                'value' => $category->id,
                'text' => $category->name,
                'label' => $category->name,
                'name' => $category->name,
                'parent_id' => $category->parent_id,
                'is_active' => $category->is_active
            ];

            if ($category->children && $category->children->count() > 0) {
                $node['children'] = $this->buildCategoryTree($category->children);
            }

            $tree[] = $node;
        }

        return $tree;
    }

    /**
     * Get category level (depth in hierarchy)
     */
    private function getCategoryLevel($category, $level = 0)
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

    /**
     * Get list of branch shops for filter
     */
    public function getBranchShops(Request $request): JsonResponse
    {
        try {
            $type = $request->get('type', 'all'); // all, active, with_orders, with_invoices, with_payments

            $query = BranchShop::select('id', 'name', 'code', 'address', 'phone', 'status', 'shop_type');

            // Filter by type
            switch ($type) {
                case 'active':
                    $query->where('status', 'active');
                    break;
                case 'with_orders':
                    $query->whereHas('orders');
                    break;
                case 'with_invoices':
                    $query->whereHas('invoices');
                    break;
                case 'with_payments':
                    $query->whereHas('payments');
                    break;
                default:
                    // Get all branch shops
                    break;
            }

            $branchShops = $query->orderBy('sort_order', 'asc')
                ->orderBy('name', 'asc')
                ->get()
                ->map(function($branch) {
                    return [
                        'id' => $branch->id,
                        'value' => $branch->id,
                        'text' => $branch->name,
                        'label' => $branch->name,
                        'name' => $branch->name,
                        'code' => $branch->code,
                        'address' => $branch->address,
                        'phone' => $branch->phone,
                        'status' => $branch->status,
                        'shop_type' => $branch->shop_type
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $branchShops
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tải danh sách chi nhánh: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get list of customers for filter
     */
    public function getCustomers(Request $request): JsonResponse
    {
        try {
            $search = $request->get('search', '');
            $limit = $request->get('limit', 50);

            $query = Customer::select('id', 'customer_code', 'name', 'phone', 'email', 'status')
                ->where('status', 'active');

            // Search by name, phone, email, or customer code
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('customer_code', 'like', "%{$search}%");
                });
            }

            $customers = $query->orderBy('name', 'asc')
                ->limit($limit)
                ->get()
                ->map(function($customer) {
                    $displayText = $customer->name;
                    if ($customer->phone) {
                        $displayText .= ' - ' . $customer->phone;
                    }
                    if ($customer->customer_code) {
                        $displayText .= ' (' . $customer->customer_code . ')';
                    }

                    return [
                        'id' => $customer->id,
                        'value' => $customer->id,
                        'text' => $displayText,
                        'label' => $displayText,
                        'name' => $customer->name,
                        'phone' => $customer->phone,
                        'email' => $customer->email,
                        'customer_code' => $customer->customer_code
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $customers
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tải danh sách khách hàng: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get list of suppliers for filter
     */
    public function getSuppliers(Request $request): JsonResponse
    {
        try {
            $search = $request->get('search', '');
            $limit = $request->get('limit', 50);

            $query = Supplier::select('id', 'code', 'name', 'company', 'phone', 'email', 'status')
                ->where('status', 'active');

            // Search by name, company, phone, email, or code
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('company', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%");
                });
            }

            $suppliers = $query->orderBy('name', 'asc')
                ->limit($limit)
                ->get()
                ->map(function($supplier) {
                    $displayText = $supplier->name;
                    if ($supplier->company && $supplier->company !== $supplier->name) {
                        $displayText = $supplier->company . ' - ' . $supplier->name;
                    }
                    if ($supplier->code) {
                        $displayText .= ' (' . $supplier->code . ')';
                    }

                    return [
                        'id' => $supplier->id,
                        'value' => $supplier->id,
                        'text' => $displayText,
                        'label' => $displayText,
                        'name' => $supplier->name,
                        'company' => $supplier->company,
                        'phone' => $supplier->phone,
                        'email' => $supplier->email,
                        'code' => $supplier->code
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $suppliers
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tải danh sách nhà cung cấp: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get list of bank accounts for filter
     */
    public function getBankAccounts(Request $request): JsonResponse
    {
        try {
            $query = BankAccount::select('id', 'bank_name', 'bank_code', 'account_number', 'account_holder', 'is_active', 'is_default')
                ->where('is_active', true);

            $bankAccounts = $query->orderBy('is_default', 'desc')
                ->orderBy('sort_order', 'asc')
                ->orderBy('bank_name', 'asc')
                ->get()
                ->map(function($account) {
                    $displayText = $account->bank_name . ' - ' . $account->account_number;
                    if ($account->account_holder) {
                        $displayText .= ' - ' . $account->account_holder;
                    }

                    return [
                        'id' => $account->id,
                        'value' => $account->id,
                        'text' => $displayText,
                        'label' => $displayText,
                        'bank_name' => $account->bank_name,
                        'bank_code' => $account->bank_code,
                        'account_number' => $account->account_number,
                        'account_holder' => $account->account_holder,
                        'is_default' => $account->is_default
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $bankAccounts
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi tải danh sách tài khoản ngân hàng: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get categories (alias for getProductCategories for backward compatibility)
     */
    public function getCategories(Request $request): JsonResponse
    {
        return $this->getProductCategories($request);
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
                'payment_methods' => $this->getPaymentMethods($request)->getData()->data ?? [],
                'product_categories' => $this->getProductCategories($request)->getData()->data ?? [],
                'branch_shops' => $this->getBranchShops($request)->getData()->data ?? []
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
