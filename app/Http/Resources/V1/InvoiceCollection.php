<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\ResourceCollection;

class InvoiceCollection extends ResourceCollection
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
                'total_invoices' => $this->collection->count(),
                'total_amount' => $this->collection->sum('total_amount'),
                'total_paid' => $this->collection->sum('paid_amount'),
                'total_remaining' => $this->collection->sum('remaining_amount'),
                'formatted_total_amount' => number_format($this->collection->sum('total_amount'), 0, ',', '.') . ' VND',
                'formatted_total_paid' => number_format($this->collection->sum('paid_amount'), 0, ',', '.') . ' VND',
                'formatted_total_remaining' => number_format($this->collection->sum('remaining_amount'), 0, ',', '.') . ' VND',
                'status_breakdown' => [
                    'draft' => $this->collection->where('status', 'draft')->count(),
                    'processing' => $this->collection->where('status', 'processing')->count(),
                    'complete' => $this->collection->where('status', 'complete')->count(),
                    'cancelled' => $this->collection->where('status', 'cancelled')->count(),
                    'undeliverable' => $this->collection->where('status', 'undeliverable')->count(),
                ],
                'payment_status_breakdown' => [
                    'paid' => $this->collection->where('is_paid', true)->count(),
                    'partial' => $this->collection->where('payment_status', 'partial')->count(),
                    'unpaid' => $this->collection->where('payment_status', 'unpaid')->count(),
                    'overdue' => $this->collection->where('is_overdue', true)->count(),
                ],
            ],
        ];
    }
}
