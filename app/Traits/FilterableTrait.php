<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

trait FilterableTrait
{
    /**
     * Apply common filters to a query builder
     *
     * @param Builder $query
     * @param Request $request
     * @param array $config Additional configuration for filters
     * @return Builder
     */
    protected function applyCommonFilters(Builder $query, Request $request, array $config = [])
    {
        // Default configuration
        $defaultConfig = [
            'searchColumns' => [],
            'exactMatchColumns' => [],
            'dateRangeColumns' => ['created_at'],
            'userColumns' => ['created_by' => 'creator_id', 'updated_by' => 'updater_id'],
            'statusColumn' => 'status',
            'relationFilters' => []
        ];

        $config = array_merge($defaultConfig, $config);

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->input('search');
            if (is_array($search) && isset($search['value'])) {
                $search = $search['value'];
            }
            
            if (!empty($search) && !empty($config['searchColumns'])) {
                $query->where(function ($q) use ($search, $config) {
                    foreach ($config['searchColumns'] as $column) {
                        if (strpos($column, '.') !== false) {
                            // Handle relation.column format
                            list($relation, $relationColumn) = explode('.', $column);
                            $q->orWhereHas($relation, function ($subQuery) use ($relationColumn, $search) {
                                $subQuery->where($relationColumn, 'like', "%{$search}%");
                            });
                        } else {
                            $q->orWhere($column, 'like', "%{$search}%");
                        }
                    }
                });
            }
        }

        // Apply exact match filters (like Code, ID, etc.)
        foreach ($config['exactMatchColumns'] as $column) {
            if ($request->filled($column)) {
                $query->where($column, $request->input($column));
            }
        }

        // Apply time/date range filters
        if ($request->filled('time_filter')) {
            $timeFilter = $request->input('time_filter');
            $dateColumn = $config['dateRangeColumns'][0] ?? 'created_at';
            
            $this->applyTimeFilter($query, $timeFilter, $dateColumn, $request);
        } else {
            // Apply explicit date range if provided
            if ($request->filled('date_from')) {
                $dateColumn = $config['dateRangeColumns'][0] ?? 'created_at';
                $query->whereDate($dateColumn, '>=', $request->input('date_from'));
            }
            
            if ($request->filled('date_to')) {
                $dateColumn = $config['dateRangeColumns'][0] ?? 'created_at';
                $query->whereDate($dateColumn, '<=', $request->input('date_to'));
            }
        }

        // Apply status filters
        if ($request->filled('status')) {
            $statuses = $request->input('status');
            if (!is_array($statuses)) {
                $statuses = [$statuses];
            }
            $query->whereIn($config['statusColumn'], $statuses);
        } elseif ($request->filled('status_filters') && is_array($request->input('status_filters'))) {
            $query->whereIn($config['statusColumn'], $request->input('status_filters'));
        }

        // Apply user filters (creator, updater, etc.)
        foreach ($config['userColumns'] as $dbColumn => $requestParam) {
            if ($request->filled($requestParam)) {
                $query->where($dbColumn, $request->input($requestParam));
            }
        }

        // Apply relation filters
        foreach ($config['relationFilters'] as $relation => $config) {
            if ($request->filled($config['param'])) {
                $value = $request->input($config['param']);
                $column = $config['column'] ?? 'id';
                
                $query->whereHas($relation, function ($q) use ($column, $value) {
                    $q->where($column, $value);
                });
            }
        }

        return $query;
    }

    /**
     * Apply time filter to query
     *
     * @param Builder $query
     * @param string $timeFilter
     * @param string $dateColumn
     * @param Request $request
     * @return void
     */
    protected function applyTimeFilter(Builder $query, $timeFilter, $dateColumn = 'created_at', Request $request = null)
    {
        $now = Carbon::now();

        switch ($timeFilter) {
            case 'all_time':
            case 'all':
                // Don't apply any time filter - show all records
                Log::info('FilterableTrait: All time filter applied - no date restrictions');
                break;

            case 'today':
                $query->whereDate($dateColumn, $now->toDateString());
                break;

            case 'yesterday':
                $query->whereDate($dateColumn, $now->copy()->subDay()->toDateString());
                break;

            case 'this_week':
                $query->whereBetween($dateColumn, [
                    $now->copy()->startOfWeek()->toDateString(),
                    $now->copy()->endOfWeek()->toDateString()
                ]);
                break;

            case 'last_week':
                $query->whereBetween($dateColumn, [
                    $now->copy()->subWeek()->startOfWeek()->toDateString(),
                    $now->copy()->subWeek()->endOfWeek()->toDateString()
                ]);
                break;

            case 'this_month':
                $query->whereMonth($dateColumn, $now->month)
                      ->whereYear($dateColumn, $now->year);
                break;

            case 'last_month':
                $lastMonth = $now->copy()->subMonth();
                $query->whereMonth($dateColumn, $lastMonth->month)
                      ->whereYear($dateColumn, $lastMonth->year);
                break;

            case 'this_quarter':
                $query->whereBetween($dateColumn, [
                    $now->copy()->startOfQuarter()->toDateString(),
                    $now->copy()->endOfQuarter()->toDateString()
                ]);
                break;

            case 'last_quarter':
                $query->whereBetween($dateColumn, [
                    $now->copy()->subQuarter()->startOfQuarter()->toDateString(),
                    $now->copy()->subQuarter()->endOfQuarter()->toDateString()
                ]);
                break;

            case 'this_year':
                $query->whereYear($dateColumn, $now->year);
                break;

            case 'last_year':
                $query->whereYear($dateColumn, $now->year - 1);
                break;

            case 'custom':
                if ($request && $request->filled('date_from')) {
                    $query->whereDate($dateColumn, '>=', $request->input('date_from'));
                }

                if ($request && $request->filled('date_to')) {
                    $query->whereDate($dateColumn, '<=', $request->input('date_to'));
                }
                break;

            default:
                // Default to this month if no valid filter provided
                $query->whereMonth($dateColumn, $now->month)
                      ->whereYear($dateColumn, $now->year);
                break;
        }
    }

    /**
     * Get filter options for a specific model
     *
     * @param string $modelClass
     * @param array $config
     * @return array
     */
    protected function getFilterOptions($modelClass, array $config = [])
    {
        $defaultConfig = [
            'statusColumn' => 'status',
            'includeUsers' => true,
            'includeStatuses' => true,
            'additionalOptions' => []
        ];

        $config = array_merge($defaultConfig, $config);
        $options = [];

        // Get status options if needed
        if ($config['includeStatuses']) {
            $statusColumn = $config['statusColumn'];
            $options['statuses'] = $modelClass::distinct()
                ->pluck($statusColumn)
                ->filter()
                ->sort()
                ->values()
                ->map(function($status) {
                    return [
                        'id' => $status,
                        'text' => ucfirst($status)
                    ];
                });
        }

        // Add additional options
        foreach ($config['additionalOptions'] as $key => $callback) {
            if (is_callable($callback)) {
                $options[$key] = $callback();
            }
        }

        return $options;
    }
}