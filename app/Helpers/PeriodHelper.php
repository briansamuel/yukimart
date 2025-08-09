<?php

namespace App\Helpers;

use Carbon\Carbon;

class PeriodHelper
{
    /**
     * Valid periods
     */
    public static function getValidPeriods()
    {
        return ['today', 'yesterday', 'this_week', 'last_week', 'month', 'last_month', 'year'];
    }

    /**
     * Get date range for period
     */
    public static function getDateRangeForPeriod($period)
    {
        switch ($period) {
            case 'today':
                return [
                    'start' => Carbon::today(),
                    'end' => Carbon::today()->endOfDay()
                ];
            case 'yesterday':
                return [
                    'start' => Carbon::yesterday(),
                    'end' => Carbon::yesterday()->endOfDay()
                ];
            case 'this_week':
                return [
                    'start' => Carbon::now()->startOfWeek(),
                    'end' => Carbon::now()->endOfWeek()
                ];
            case 'last_week':
                return [
                    'start' => Carbon::now()->subWeek()->startOfWeek(),
                    'end' => Carbon::now()->subWeek()->endOfWeek()
                ];
            case 'month':
                return [
                    'start' => Carbon::now()->startOfMonth(),
                    'end' => Carbon::now()->endOfMonth()
                ];
            case 'last_month':
                return [
                    'start' => Carbon::now()->subMonth()->startOfMonth(),
                    'end' => Carbon::now()->subMonth()->endOfMonth()
                ];
            case 'year':
                return [
                    'start' => Carbon::now()->startOfYear(),
                    'end' => Carbon::now()->endOfYear()
                ];
            default:
                return [
                    'start' => Carbon::now()->startOfMonth(),
                    'end' => Carbon::now()->endOfMonth()
                ];
        }
    }

    /**
     * Get period name in Vietnamese
     */
    public static function getPeriodName($period)
    {
        switch ($period) {
            case 'today':
                return 'hôm nay';
            case 'yesterday':
                return 'hôm qua';
            case 'this_week':
                return 'tuần này';
            case 'last_week':
                return 'tuần trước';
            case 'month':
                return 'tháng này';
            case 'last_month':
                return 'tháng trước';
            case 'year':
                return 'năm nay';
            default:
                return 'tháng này';
        }
    }

    /**
     * Validate period
     */
    public static function isValidPeriod($period)
    {
        return in_array($period, self::getValidPeriods());
    }

    /**
     * Get period display info
     */
    public static function getPeriodInfo($period)
    {
        $dateRange = self::getDateRangeForPeriod($period);
        
        return [
            'period' => $period,
            'period_name' => self::getPeriodName($period),
            'date_range' => [
                'start' => $dateRange['start']->toDateString(),
                'end' => $dateRange['end']->toDateString(),
            ],
            'date_range_formatted' => [
                'start' => $dateRange['start']->format('Y-m-d H:i:s'),
                'end' => $dateRange['end']->format('Y-m-d H:i:s'),
            ]
        ];
    }
}
