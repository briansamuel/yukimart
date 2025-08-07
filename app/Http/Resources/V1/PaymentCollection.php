<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\ResourceCollection;

class PaymentCollection extends ResourceCollection
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
                'total_payments' => $this->collection->count(),
                'total_receipts' => $this->collection->where('payment_type', 'receipt')->count(),
                'total_disbursements' => $this->collection->where('payment_type', 'payment')->count(),
                
                // Amount summaries
                'total_receipt_amount' => $this->collection->where('payment_type', 'receipt')->sum('amount'),
                'total_payment_amount' => $this->collection->where('payment_type', 'payment')->sum('amount'),
                'net_amount' => $this->collection->where('payment_type', 'receipt')->sum('amount') - 
                               $this->collection->where('payment_type', 'payment')->sum('amount'),
                
                // Actual amount summaries
                'total_actual_receipt_amount' => $this->collection->where('payment_type', 'receipt')->sum('actual_amount'),
                'total_actual_payment_amount' => $this->collection->where('payment_type', 'payment')->sum('actual_amount'),
                'net_actual_amount' => $this->collection->where('payment_type', 'receipt')->sum('actual_amount') - 
                                      $this->collection->where('payment_type', 'payment')->sum('actual_amount'),
                
                // Formatted amounts
                'formatted_total_receipt_amount' => number_format($this->collection->where('payment_type', 'receipt')->sum('amount'), 0, ',', '.') . ' VND',
                'formatted_total_payment_amount' => number_format($this->collection->where('payment_type', 'payment')->sum('amount'), 0, ',', '.') . ' VND',
                'formatted_net_amount' => number_format(
                    $this->collection->where('payment_type', 'receipt')->sum('amount') - 
                    $this->collection->where('payment_type', 'payment')->sum('amount'), 
                    0, ',', '.'
                ) . ' VND',
                'formatted_total_actual_receipt_amount' => number_format($this->collection->where('payment_type', 'receipt')->sum('actual_amount'), 0, ',', '.') . ' VND',
                'formatted_total_actual_payment_amount' => number_format($this->collection->where('payment_type', 'payment')->sum('actual_amount'), 0, ',', '.') . ' VND',
                'formatted_net_actual_amount' => number_format(
                    $this->collection->where('payment_type', 'receipt')->sum('actual_amount') - 
                    $this->collection->where('payment_type', 'payment')->sum('actual_amount'), 
                    0, ',', '.'
                ) . ' VND',
                
                // Status breakdown
                'status_breakdown' => [
                    'pending' => $this->collection->where('status', 'pending')->count(),
                    'completed' => $this->collection->where('status', 'completed')->count(),
                    'cancelled' => $this->collection->where('status', 'cancelled')->count(),
                ],
                
                // Method breakdown
                'method_breakdown' => [
                    'cash' => [
                        'count' => $this->collection->where('payment_method', 'cash')->count(),
                        'amount' => $this->collection->where('payment_method', 'cash')->sum('amount'),
                        'formatted_amount' => number_format($this->collection->where('payment_method', 'cash')->sum('amount'), 0, ',', '.') . ' VND',
                    ],
                    'card' => [
                        'count' => $this->collection->where('payment_method', 'card')->count(),
                        'amount' => $this->collection->where('payment_method', 'card')->sum('amount'),
                        'formatted_amount' => number_format($this->collection->where('payment_method', 'card')->sum('amount'), 0, ',', '.') . ' VND',
                    ],
                    'transfer' => [
                        'count' => $this->collection->where('payment_method', 'transfer')->count(),
                        'amount' => $this->collection->where('payment_method', 'transfer')->sum('amount'),
                        'formatted_amount' => number_format($this->collection->where('payment_method', 'transfer')->sum('amount'), 0, ',', '.') . ' VND',
                    ],
                    'check' => [
                        'count' => $this->collection->where('payment_method', 'check')->count(),
                        'amount' => $this->collection->where('payment_method', 'check')->sum('amount'),
                        'formatted_amount' => number_format($this->collection->where('payment_method', 'check')->sum('amount'), 0, ',', '.') . ' VND',
                    ],
                    'points' => [
                        'count' => $this->collection->where('payment_method', 'points')->count(),
                        'amount' => $this->collection->where('payment_method', 'points')->sum('amount'),
                        'formatted_amount' => number_format($this->collection->where('payment_method', 'points')->sum('amount'), 0, ',', '.') . ' VND',
                    ],
                    'other' => [
                        'count' => $this->collection->where('payment_method', 'other')->count(),
                        'amount' => $this->collection->where('payment_method', 'other')->sum('amount'),
                        'formatted_amount' => number_format($this->collection->where('payment_method', 'other')->sum('amount'), 0, ',', '.') . ' VND',
                    ],
                ],
                
                // Reference type breakdown
                'reference_breakdown' => [
                    'invoice' => [
                        'count' => $this->collection->where('reference_type', 'invoice')->count(),
                        'amount' => $this->collection->where('reference_type', 'invoice')->sum('amount'),
                        'formatted_amount' => number_format($this->collection->where('reference_type', 'invoice')->sum('amount'), 0, ',', '.') . ' VND',
                    ],
                    'order' => [
                        'count' => $this->collection->where('reference_type', 'order')->count(),
                        'amount' => $this->collection->where('reference_type', 'order')->sum('amount'),
                        'formatted_amount' => number_format($this->collection->where('reference_type', 'order')->sum('amount'), 0, ',', '.') . ' VND',
                    ],
                    'return_order' => [
                        'count' => $this->collection->where('reference_type', 'return_order')->count(),
                        'amount' => $this->collection->where('reference_type', 'return_order')->sum('amount'),
                        'formatted_amount' => number_format($this->collection->where('reference_type', 'return_order')->sum('amount'), 0, ',', '.') . ' VND',
                    ],
                    'manual' => [
                        'count' => $this->collection->where('reference_type', 'manual')->count(),
                        'amount' => $this->collection->where('reference_type', 'manual')->sum('amount'),
                        'formatted_amount' => number_format($this->collection->where('reference_type', 'manual')->sum('amount'), 0, ',', '.') . ' VND',
                    ],
                ],
                
                // Bank account breakdown
                'bank_breakdown' => $this->collection->load('bankAccount')
                    ->groupBy('bankAccount.bank_name')
                    ->map(function ($group) {
                        return [
                            'count' => $group->count(),
                            'amount' => $group->sum('amount'),
                            'formatted_amount' => number_format($group->sum('amount'), 0, ',', '.') . ' VND',
                        ];
                    })->toArray(),
                
                // Branch breakdown
                'branch_breakdown' => $this->collection->load('branchShop')
                    ->groupBy('branchShop.name')
                    ->map(function ($group) {
                        return [
                            'count' => $group->count(),
                            'amount' => $group->sum('amount'),
                            'formatted_amount' => number_format($group->sum('amount'), 0, ',', '.') . ' VND',
                        ];
                    })->toArray(),
                
                // Variance analysis
                'variance_analysis' => [
                    'total_variance' => $this->collection->sum('actual_amount') - $this->collection->sum('amount'),
                    'formatted_total_variance' => number_format(
                        $this->collection->sum('actual_amount') - $this->collection->sum('amount'), 
                        0, ',', '.'
                    ) . ' VND',
                    'positive_variances' => $this->collection->filter(function ($payment) {
                        return $payment->actual_amount > $payment->amount;
                    })->count(),
                    'negative_variances' => $this->collection->filter(function ($payment) {
                        return $payment->actual_amount < $payment->amount;
                    })->count(),
                    'exact_matches' => $this->collection->filter(function ($payment) {
                        return abs($payment->actual_amount - $payment->amount) < 0.01;
                    })->count(),
                    'average_variance' => $this->collection->count() > 0 ? 
                        ($this->collection->sum('actual_amount') - $this->collection->sum('amount')) / $this->collection->count() : 0,
                    'formatted_average_variance' => $this->collection->count() > 0 ? 
                        number_format(
                            ($this->collection->sum('actual_amount') - $this->collection->sum('amount')) / $this->collection->count(), 
                            0, ',', '.'
                        ) . ' VND' : '0 VND',
                ],
                
                // Performance metrics
                'performance_metrics' => [
                    'completion_rate' => $this->collection->count() > 0 ? 
                        round(($this->collection->where('status', 'completed')->count() / $this->collection->count()) * 100, 2) : 0,
                    'cancellation_rate' => $this->collection->count() > 0 ? 
                        round(($this->collection->where('status', 'cancelled')->count() / $this->collection->count()) * 100, 2) : 0,
                    'cash_percentage' => $this->collection->count() > 0 ? 
                        round(($this->collection->where('payment_method', 'cash')->count() / $this->collection->count()) * 100, 2) : 0,
                    'digital_percentage' => $this->collection->count() > 0 ? 
                        round(($this->collection->whereIn('payment_method', ['card', 'transfer'])->count() / $this->collection->count()) * 100, 2) : 0,
                    'average_payment_amount' => $this->collection->count() > 0 ? 
                        round($this->collection->sum('amount') / $this->collection->count(), 2) : 0,
                    'formatted_average_payment_amount' => $this->collection->count() > 0 ? 
                        number_format($this->collection->sum('amount') / $this->collection->count(), 0, ',', '.') . ' VND' : '0 VND',
                ],
            ],
        ];
    }
}
