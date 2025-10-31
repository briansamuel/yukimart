<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Route;

class BreadcrumbHelper
{
    /**
     * Get breadcrumbs for current route
     *
     * @return array
     */
    public static function getBreadcrumbs()
    {
        $currentRoute = Route::currentRouteName();
        
        // Get breadcrumbs from analytics config
        $analyticsBreadcrumbs = config('analytics_menu.breadcrumbs', []);
        
        if (isset($analyticsBreadcrumbs[$currentRoute])) {
            return $analyticsBreadcrumbs[$currentRoute];
        }
        
        // Default breadcrumbs
        return [
            ['title' => 'Dashboard', 'route' => 'admin.dashboard'],
        ];
    }

    /**
     * Render breadcrumbs HTML
     *
     * @return string
     */
    public static function render()
    {
        $breadcrumbs = self::getBreadcrumbs();
        
        $html = '<nav aria-label="breadcrumb">';
        $html .= '<ol class="breadcrumb">';
        
        foreach ($breadcrumbs as $index => $breadcrumb) {
            $isLast = ($index === count($breadcrumbs) - 1);
            
            if ($isLast) {
                $html .= '<li class="breadcrumb-item active" aria-current="page">' . $breadcrumb['title'] . '</li>';
            } else {
                if ($breadcrumb['route']) {
                    $html .= '<li class="breadcrumb-item"><a href="' . route($breadcrumb['route']) . '">' . $breadcrumb['title'] . '</a></li>';
                } else {
                    $html .= '<li class="breadcrumb-item">' . $breadcrumb['title'] . '</li>';
                }
            }
        }
        
        $html .= '</ol>';
        $html .= '</nav>';
        
        return $html;
    }
}

