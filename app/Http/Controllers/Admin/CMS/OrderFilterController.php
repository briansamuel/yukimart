<?php

namespace App\Http\Controllers\Admin\CMS;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Models\BranchShop;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderFilterController extends Controller
{
    /**
     * Get order status options for filter
     */
    public function getStatusOptions()
    {
        $statuses = [
            ['value' => 'draft', 'label' => 'Nháp', 'count' => Order::where('status', 'draft')->count()],
            ['value' => 'pending', 'label' => 'Chờ xử lý', 'count' => Order::where('status', 'pending')->count()],
            ['value' => 'processing', 'label' => 'Đang xử lý', 'count' => Order::where('status', 'processing')->count()],
            ['value' => 'shipped', 'label' => 'Đã gửi hàng', 'count' => Order::where('status', 'shipped')->count()],
            ['value' => 'delivered', 'label' => 'Đã giao hàng', 'count' => Order::where('status', 'delivered')->count()],
            ['value' => 'completed', 'label' => 'Hoàn thành', 'count' => Order::where('status', 'completed')->count()],
            ['value' => 'cancelled', 'label' => 'Đã hủy', 'count' => Order::where('status', 'cancelled')->count()],
            ['value' => 'returned', 'label' => 'Đã trả hàng', 'count' => Order::where('status', 'returned')->count()],
        ];

        return response()->json([
            'success' => true,
            'data' => $statuses
        ]);
    }

    /**
     * Get payment status options for filter
     */
    public function getPaymentStatusOptions()
    {
        $paymentStatuses = [
            ['value' => 'unpaid', 'label' => 'Chưa thanh toán', 'count' => Order::where('payment_status', 'unpaid')->count()],
            ['value' => 'partial', 'label' => 'Thanh toán một phần', 'count' => Order::where('payment_status', 'partial')->count()],
            ['value' => 'paid', 'label' => 'Đã thanh toán', 'count' => Order::where('payment_status', 'paid')->count()],
            ['value' => 'refunded', 'label' => 'Đã hoàn tiền', 'count' => Order::where('payment_status', 'refunded')->count()],
        ];

        return response()->json([
            'success' => true,
            'data' => $paymentStatuses
        ]);
    }

    /**
     * Get payment method options for filter
     */
    public function getPaymentMethodOptions()
    {
        $paymentMethods = Order::select('payment_method')
            ->whereNotNull('payment_method')
            ->groupBy('payment_method')
            ->get()
            ->map(function ($item) {
                $label = match($item->payment_method) {
                    'cash' => 'Tiền mặt',
                    'bank_transfer' => 'Chuyển khoản',
                    'credit_card' => 'Thẻ tín dụng',
                    'e_wallet' => 'Ví điện tử',
                    'cod' => 'Thu hộ (COD)',
                    default => ucfirst(str_replace('_', ' ', $item->payment_method))
                };

                return [
                    'value' => $item->payment_method,
                    'label' => $label,
                    'count' => Order::where('payment_method', $item->payment_method)->count()
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $paymentMethods
        ]);
    }

    /**
     * Get creator options for filter
     */
    public function getCreatorOptions()
    {
        $creators = User::select('id', 'name', 'email')
            ->whereHas('createdOrders')
            ->withCount('createdOrders')
            ->orderBy('created_orders_count', 'desc')
            ->limit(50)
            ->get()
            ->map(function ($user) {
                return [
                    'value' => $user->id,
                    'label' => $user->name . ' (' . $user->email . ')',
                    'count' => $user->created_orders_count
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $creators
        ]);
    }

    /**
     * Get seller options for filter
     */
    public function getSellerOptions()
    {
        $sellers = User::select('id', 'name', 'email')
            ->whereHas('soldOrders')
            ->withCount('soldOrders')
            ->orderBy('sold_orders_count', 'desc')
            ->limit(50)
            ->get()
            ->map(function ($user) {
                return [
                    'value' => $user->id,
                    'label' => $user->name . ' (' . $user->email . ')',
                    'count' => $user->sold_orders_count
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $sellers
        ]);
    }

    /**
     * Get branch shop options for filter
     */
    public function getBranchOptions()
    {
        $branches = BranchShop::select('id', 'name', 'address')
            ->whereHas('orders')
            ->withCount('orders')
            ->orderBy('orders_count', 'desc')
            ->get()
            ->map(function ($branch) {
                return [
                    'value' => $branch->id,
                    'label' => $branch->name . ' (' . $branch->address . ')',
                    'count' => $branch->orders_count
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $branches
        ]);
    }

    /**
     * Get delivery status options for filter
     */
    public function getDeliveryStatusOptions()
    {
        $deliveryStatuses = [
            ['value' => 'pending', 'label' => 'Chờ giao hàng', 'count' => Order::where('delivery_status', 'pending')->count()],
            ['value' => 'preparing', 'label' => 'Đang chuẩn bị', 'count' => Order::where('delivery_status', 'preparing')->count()],
            ['value' => 'shipped', 'label' => 'Đã gửi hàng', 'count' => Order::where('delivery_status', 'shipped')->count()],
            ['value' => 'out_for_delivery', 'label' => 'Đang giao hàng', 'count' => Order::where('delivery_status', 'out_for_delivery')->count()],
            ['value' => 'delivered', 'label' => 'Đã giao hàng', 'count' => Order::where('delivery_status', 'delivered')->count()],
            ['value' => 'failed', 'label' => 'Giao hàng thất bại', 'count' => Order::where('delivery_status', 'failed')->count()],
            ['value' => 'returned', 'label' => 'Đã trả hàng', 'count' => Order::where('delivery_status', 'returned')->count()],
        ];

        return response()->json([
            'success' => true,
            'data' => $deliveryStatuses
        ]);
    }

    /**
     * Get channel options for filter
     */
    public function getChannelOptions()
    {
        $channels = Order::select('channel')
            ->whereNotNull('channel')
            ->groupBy('channel')
            ->get()
            ->map(function ($item) {
                $label = match($item->channel) {
                    'website' => 'Website',
                    'mobile_app' => 'Mobile App',
                    'facebook' => 'Facebook',
                    'shopee' => 'Shopee',
                    'lazada' => 'Lazada',
                    'tiki' => 'Tiki',
                    'phone' => 'Điện thoại',
                    'store' => 'Cửa hàng',
                    default => ucfirst(str_replace('_', ' ', $item->channel))
                };

                return [
                    'value' => $item->channel,
                    'label' => $label,
                    'count' => Order::where('channel', $item->channel)->count()
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $channels
        ]);
    }

    /**
     * Get customer type options for filter
     */
    public function getCustomerTypeOptions()
    {
        $customerTypes = Customer::select('customer_type')
            ->whereNotNull('customer_type')
            ->whereHas('orders')
            ->groupBy('customer_type')
            ->get()
            ->map(function ($item) {
                $label = match($item->customer_type) {
                    'individual' => 'Cá nhân',
                    'business' => 'Doanh nghiệp',
                    'vip' => 'VIP',
                    'wholesale' => 'Bán sỉ',
                    default => ucfirst(str_replace('_', ' ', $item->customer_type))
                };

                return [
                    'value' => $item->customer_type,
                    'label' => $label,
                    'count' => Order::whereHas('customer', function($q) use ($item) {
                        $q->where('customer_type', $item->customer_type);
                    })->count()
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $customerTypes
        ]);
    }

    /**
     * Get all filter options at once
     */
    public function getAllFilterOptions()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'statuses' => $this->getStatusOptions()->getData()->data,
                'payment_statuses' => $this->getPaymentStatusOptions()->getData()->data,
                'payment_methods' => $this->getPaymentMethodOptions()->getData()->data,
                'creators' => $this->getCreatorOptions()->getData()->data,
                'sellers' => $this->getSellerOptions()->getData()->data,
                'branches' => $this->getBranchOptions()->getData()->data,
                'delivery_statuses' => $this->getDeliveryStatusOptions()->getData()->data,
                'channels' => $this->getChannelOptions()->getData()->data,
                'customer_types' => $this->getCustomerTypeOptions()->getData()->data,
            ]
        ]);
    }
}
