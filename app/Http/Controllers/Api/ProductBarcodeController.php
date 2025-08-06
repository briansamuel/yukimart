<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ProductBarcodeController extends Controller
{
    /**
     * Find product by barcode
     *
     * @param string $barcode
     * @return JsonResponse
     */
    public function findByBarcode(string $barcode): JsonResponse
    {
        try {
            // Validate barcode format (basic validation)
            if (empty($barcode) || strlen($barcode) < 3) {
                return response()->json([
                    'success' => false,
                    'message' => __('Invalid barcode format'),
                    'data' => null
                ], 400);
            }

            // Search for product by barcode
            $product = Product::with(['category', 'inventory'])
                ->where('barcode', $barcode)
                ->where('product_status', 'publish')
                ->first();

            if (!$product) {
                // Also try searching by SKU as fallback
                $product = Product::with(['category', 'inventory'])
                    ->where('sku', $barcode)
                    ->where('product_status', 'publish')
                    ->first();
            }

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => __('Product not found with barcode: :barcode', ['barcode' => $barcode]),
                    'data' => null
                ], 404);
            }

            // Check if product is available for sale
            $stockQuantity = $product->stock_quantity ?? 0;
            $isAvailable = $stockQuantity > 0;

            // Format product data for quick order
            $productData = [
                'id' => $product->id,
                'name' => $product->product_name,
                'sku' => $product->sku,
                'barcode' => $product->barcode,
                'price' => $product->sale_price,
                'cost_price' => $product->cost_price,
                'stock_quantity' => $stockQuantity,
                'is_available' => $isAvailable,
                'category' => $product->category ? $product->category->name : null,
                'image' => $product->product_thumbnail ? asset($product->product_thumbnail) : null,
                'formatted_price' => number_format($product->sale_price, 0, ',', '.') . ' VND',
                'stock_status' => $this->getStockStatus($stockQuantity, $product->reorder_point ?? 0),
                'can_order' => $isAvailable && $product->canOrder(1),
                'weight' => $product->weight,
                'dimensions' => [
                    'length' => $product->length,
                    'width' => $product->width,
                    'height' => $product->height,
                ],
            ];

            Log::info('Product found by barcode', [
                'barcode' => $barcode,
                'product_id' => $product->id,
                'product_name' => $product->product_name,
                'stock_quantity' => $stockQuantity,
            ]);

            return response()->json([
                'success' => true,
                'message' => __('Product found successfully'),
                'data' => $productData
            ]);

        } catch (\Exception $e) {
            Log::error('Error finding product by barcode', [
                'barcode' => $barcode,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => __('An error occurred while searching for the product'),
                'data' => null
            ], 500);
        }
    }

    /**
     * Search products by barcode or name (for autocomplete)
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $query = $request->get('q', '');
            $limit = min($request->get('limit', 10), 50); // Max 50 results

            if (strlen($query) < 2) {
                return response()->json([
                    'success' => false,
                    'message' => __('Search query must be at least 2 characters'),
                    'data' => []
                ], 400);
            }

            $products = Product::with(['category', 'inventory'])
                ->where('product_status', 'publish')
                ->where(function ($q) use ($query) {
                    $q->where('barcode', 'LIKE', "%{$query}%")
                      ->orWhere('sku', 'LIKE', "%{$query}%")
                      ->orWhere('product_name', 'LIKE', "%{$query}%");
                })
                ->limit($limit)
                ->get()
                ->map(function ($product) {
                    $stockQuantity = $product->stock_quantity ?? 0;
                    
                    return [
                        'id' => $product->id,
                        'name' => $product->product_name,
                        'sku' => $product->sku,
                        'barcode' => $product->barcode,
                        'price' => $product->sale_price,
                        'stock_quantity' => $stockQuantity,
                        'is_available' => $stockQuantity > 0,
                        'formatted_price' => number_format($product->sale_price, 0, ',', '.') . ' VND',
                        'image' => $product->product_thumbnail ? asset('storage/' . $product->product_thumbnail) : null,
                        'category' => $product->category ? $product->category->name : null,
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => __('Search completed'),
                'data' => $products
            ]);

        } catch (\Exception $e) {
            Log::error('Error searching products', [
                'query' => $request->get('q'),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => __('An error occurred while searching'),
                'data' => []
            ], 500);
        }
    }

    /**
     * Get stock status information
     *
     * @param int $stockQuantity
     * @param int $reorderPoint
     * @return array
     */
    private function getStockStatus(int $stockQuantity, int $reorderPoint): array
    {
        if ($stockQuantity <= 0) {
            return [
                'status' => 'out_of_stock',
                'label' => __('Out of Stock'),
                'class' => 'badge-danger',
                'color' => 'danger'
            ];
        } elseif ($stockQuantity <= $reorderPoint) {
            return [
                'status' => 'low_stock',
                'label' => __('Low Stock'),
                'class' => 'badge-warning',
                'color' => 'warning'
            ];
        } else {
            return [
                'status' => 'in_stock',
                'label' => __('In Stock'),
                'class' => 'badge-success',
                'color' => 'success'
            ];
        }
    }

    /**
     * Validate barcode format
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function validateBarcode(Request $request): JsonResponse
    {
        $barcode = $request->get('barcode');

        if (empty($barcode)) {
            return response()->json([
                'success' => false,
                'message' => __('Barcode is required'),
                'valid' => false
            ], 400);
        }

        // Basic barcode validation
        $isValid = strlen($barcode) >= 3 && strlen($barcode) <= 50;
        
        // Check if barcode already exists
        $exists = Product::where('barcode', $barcode)->exists();

        return response()->json([
            'success' => true,
            'valid' => $isValid,
            'exists' => $exists,
            'message' => $isValid 
                ? ($exists ? __('Barcode already exists') : __('Barcode is valid'))
                : __('Invalid barcode format')
        ]);
    }
}
