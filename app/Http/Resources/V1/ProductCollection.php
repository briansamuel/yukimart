<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ProductCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'data' => $this->collection,
            'summary' => [
                'total_products' => $this->collection->count(),
                'total_stock_value' => $this->collection->sum(function ($product) {
                    return $product->getInventoryValue();
                }),
                'formatted_total_stock_value' => number_format($this->collection->sum(function ($product) {
                    return $product->getInventoryValue();
                }), 0, ',', '.') . ' VND',
                'average_cost_price' => $this->collection->avg('cost_price'),
                'average_sale_price' => $this->collection->avg('sale_price'),
                'formatted_average_cost_price' => number_format($this->collection->avg('cost_price'), 0, ',', '.') . ' VND',
                'formatted_average_sale_price' => number_format($this->collection->avg('sale_price'), 0, ',', '.') . ' VND',
                'status_breakdown' => [
                    'publish' => $this->collection->where('product_status', 'publish')->count(),
                    'draft' => $this->collection->where('product_status', 'draft')->count(),
                    'pending' => $this->collection->where('product_status', 'pending')->count(),
                    'trash' => $this->collection->where('product_status', 'trash')->count(),
                ],
                'type_breakdown' => [
                    'simple' => $this->collection->where('product_type', 'simple')->count(),
                    'variable' => $this->collection->where('product_type', 'variable')->count(),
                    'grouped' => $this->collection->where('product_type', 'grouped')->count(),
                    'external' => $this->collection->where('product_type', 'external')->count(),
                ],
                'stock_breakdown' => [
                    'in_stock' => $this->collection->filter(function ($product) {
                        return $product->stock_quantity > 0;
                    })->count(),
                    'out_of_stock' => $this->collection->filter(function ($product) {
                        return $product->stock_quantity <= 0;
                    })->count(),
                    'low_stock' => $this->collection->filter(function ($product) {
                        return $product->needsReordering();
                    })->count(),
                    'featured' => $this->collection->where('product_feature', true)->count(),
                ],
                'price_ranges' => [
                    'under_100k' => $this->collection->filter(function ($product) {
                        return $product->sale_price < 100000;
                    })->count(),
                    '100k_500k' => $this->collection->filter(function ($product) {
                        return $product->sale_price >= 100000 && $product->sale_price < 500000;
                    })->count(),
                    '500k_1m' => $this->collection->filter(function ($product) {
                        return $product->sale_price >= 500000 && $product->sale_price < 1000000;
                    })->count(),
                    'over_1m' => $this->collection->filter(function ($product) {
                        return $product->sale_price >= 1000000;
                    })->count(),
                ],
                'brands' => $this->collection->pluck('brand')->filter()->unique()->values(),
                'categories' => $this->collection->load('category')->pluck('category.name')->filter()->unique()->values(),
            ],
        ];
    }
}
