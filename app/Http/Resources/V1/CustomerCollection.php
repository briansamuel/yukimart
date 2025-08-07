<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\ResourceCollection;

class CustomerCollection extends ResourceCollection
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
                'total_customers' => $this->collection->count(),
                'total_points' => $this->collection->sum('points'),
                'average_points' => $this->collection->avg('points'),
                'status_breakdown' => [
                    'active' => $this->collection->where('status', 'active')->count(),
                    'inactive' => $this->collection->where('status', 'inactive')->count(),
                    'blocked' => $this->collection->where('status', 'blocked')->count(),
                ],
                'contact_breakdown' => [
                    'has_phone' => $this->collection->filter(function ($customer) {
                        return !empty($customer->phone);
                    })->count(),
                    'has_email' => $this->collection->filter(function ($customer) {
                        return !empty($customer->email);
                    })->count(),
                    'has_both' => $this->collection->filter(function ($customer) {
                        return !empty($customer->phone) && !empty($customer->email);
                    })->count(),
                    'has_address' => $this->collection->filter(function ($customer) {
                        return !empty($customer->address);
                    })->count(),
                ],
                'type_breakdown' => $this->collection->groupBy('customer_type')
                    ->map(function ($group) {
                        return $group->count();
                    })->toArray(),
                'group_breakdown' => $this->collection->groupBy('customer_group')
                    ->map(function ($group) {
                        return $group->count();
                    })->toArray(),
                'branch_breakdown' => $this->collection->load('branchShop')
                    ->groupBy('branchShop.name')
                    ->map(function ($group) {
                        return $group->count();
                    })->toArray(),
                'age_breakdown' => [
                    'under_18' => $this->collection->filter(function ($customer) {
                        return $customer->birthday && now()->diffInYears($customer->birthday) < 18;
                    })->count(),
                    '18_30' => $this->collection->filter(function ($customer) {
                        if (!$customer->birthday) return false;
                        $age = now()->diffInYears($customer->birthday);
                        return $age >= 18 && $age <= 30;
                    })->count(),
                    '31_50' => $this->collection->filter(function ($customer) {
                        if (!$customer->birthday) return false;
                        $age = now()->diffInYears($customer->birthday);
                        return $age >= 31 && $age <= 50;
                    })->count(),
                    'over_50' => $this->collection->filter(function ($customer) {
                        return $customer->birthday && now()->diffInYears($customer->birthday) > 50;
                    })->count(),
                    'unknown' => $this->collection->filter(function ($customer) {
                        return !$customer->birthday;
                    })->count(),
                ],
                'points_breakdown' => [
                    'no_points' => $this->collection->where('points', 0)->count(),
                    'low_points' => $this->collection->filter(function ($customer) {
                        return $customer->points > 0 && $customer->points <= 100;
                    })->count(),
                    'medium_points' => $this->collection->filter(function ($customer) {
                        return $customer->points > 100 && $customer->points <= 500;
                    })->count(),
                    'high_points' => $this->collection->filter(function ($customer) {
                        return $customer->points > 500;
                    })->count(),
                ],
                'recent_registrations' => [
                    'today' => $this->collection->filter(function ($customer) {
                        return $customer->created_at && $customer->created_at->isToday();
                    })->count(),
                    'this_week' => $this->collection->filter(function ($customer) {
                        return $customer->created_at && $customer->created_at->isCurrentWeek();
                    })->count(),
                    'this_month' => $this->collection->filter(function ($customer) {
                        return $customer->created_at && $customer->created_at->isCurrentMonth();
                    })->count(),
                ],
                'top_areas' => $this->collection->filter(function ($customer) {
                    return !empty($customer->area);
                })->groupBy('area')
                ->map(function ($group) {
                    return $group->count();
                })->sortDesc()
                ->take(5)
                ->toArray(),
            ],
        ];
    }
}
