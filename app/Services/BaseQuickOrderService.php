<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Customer;
use App\Models\BranchShop;
use App\Models\OrderItem;
use App\Models\InvoiceItem;
use App\Models\ReturnOrderItem;
use Illuminate\Support\Facades\Auth;
use Exception;

/**
 * Base service class for Quick Order operations
 * Provides common functionality for Order, Invoice, and Return services
 */
abstract class BaseQuickOrderService
{
    /**
     * Validate items array
     */
    protected function validateItems(array $items): bool
    {
        if (empty($items)) {
            throw new Exception('Danh sách sản phẩm không được để trống');
        }

        foreach ($items as $item) {
            if (!isset($item['product_id']) || !isset($item['quantity']) || !isset($item['price'])) {
                throw new Exception('Thông tin sản phẩm không đầy đủ');
            }

            if ($item['quantity'] <= 0) {
                throw new Exception('Số lượng sản phẩm phải lớn hơn 0');
            }

            if ($item['price'] < 0) {
                throw new Exception('Giá sản phẩm không được âm');
            }

            // Check if product exists
            $product = Product::find($item['product_id']);
            if (!$product) {
                throw new Exception("Không tìm thấy sản phẩm với ID: {$item['product_id']}");
            }
        }

        return true;
    }

    /**
     * Validate customer
     */
    protected function validateCustomer($customerId): bool
    {
        if ($customerId && $customerId > 0) {
            $customer = Customer::find($customerId);
            if (!$customer) {
                throw new Exception("Không tìm thấy khách hàng với ID: {$customerId}");
            }
        }
        return true;
    }

    /**
     * Validate branch shop
     */
    protected function validateBranchShop($branchShopId): bool
    {
        if (!$branchShopId) {
            throw new Exception('Chi nhánh không được để trống');
        }

        $branchShop = BranchShop::find($branchShopId);
        if (!$branchShop) {
            throw new Exception("Không tìm thấy chi nhánh với ID: {$branchShopId}");
        }

        return true;
    }

    /**
     * Calculate subtotal from items
     */
    protected function calculateSubtotal(array $items): float
    {
        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += ($item['quantity'] * $item['price']);
        }
        return $subtotal;
    }

    /**
     * Calculate final amount
     */
    protected function calculateFinalAmount(float $subtotal, float $discount = 0, float $other = 0): float
    {
        return $subtotal - $discount + $other;
    }

    /**
     * Create items for order/invoice/return
     */
    protected function createItems($parentId, array $items, string $type): void
    {
        $sortOrder = 0;
        foreach ($items as $itemData) {
            $product = Product::find($itemData['product_id']);
            if (!$product) {
                throw new Exception("Không tìm thấy sản phẩm với ID: {$itemData['product_id']}");
            }

            $itemAttributes = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'product_sku' => $product->sku,
                'quantity' => $itemData['quantity'],
                'unit_price' => $itemData['price'],
                'line_total' => $itemData['quantity'] * $itemData['price'],
                'sort_order' => $sortOrder++,
            ];

            switch ($type) {
                case 'order':
                    $itemAttributes['order_id'] = $parentId;
                    OrderItem::create($itemAttributes);
                    break;
                case 'invoice':
                    $itemAttributes['invoice_id'] = $parentId;
                    InvoiceItem::create($itemAttributes);
                    break;
                case 'return':
                    $itemAttributes['return_order_id'] = $parentId;
                    ReturnOrderItem::create($itemAttributes);
                    break;
                default:
                    throw new Exception("Invalid item type: {$type}");
            }
        }
    }

    /**
     * Format success response
     */
    protected function successResponse(string $message, array $data = []): array
    {
        return [
            'success' => true,
            'message' => $message,
            'data' => $data
        ];
    }

    /**
     * Format error response
     */
    protected function errorResponse(string $message, array $errors = []): array
    {
        return [
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ];
    }

    /**
     * Get current user ID
     */
    protected function getCurrentUserId(): int
    {
        return Auth::id();
    }

    /**
     * Prepare common data for order/invoice creation
     */
    protected function prepareCommonData(array $data): array
    {
        return [
            'customer_id' => $data['customer_id'] ?? 0,
            'branch_shop_id' => $data['branch_shop_id'],
            'sold_by' => $data['sold_by'] ?? $this->getCurrentUserId(),
            'created_by' => $this->getCurrentUserId(),
            'notes' => $data['notes'] ?? '',
            'subtotal' => $this->calculateSubtotal($data['items']),
            'discount_amount' => $data['discount_amount'] ?? 0,
            'other_amount' => $data['other_amount'] ?? 0,
        ];
    }

    /**
     * Validate common required fields
     */
    protected function validateCommonFields(array $data): void
    {
        $this->validateItems($data['items']);
        $this->validateCustomer($data['customer_id'] ?? null);
        $this->validateBranchShop($data['branch_shop_id']);
    }
}
