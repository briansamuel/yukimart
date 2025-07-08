<?php

namespace App\Helpers;
use File;
use Illuminate\Http\Request;
class Common
{
    protected static $rootAdmin = '/admin';
    public static function saveFileData($path, $data, $json = true)
    {
        // if ($json) {
        //     $data = json_encode_prettify($data);
        // }
        // if (!File::isDirectory(dirname($path))) {
        //     File::makeDirectory(dirname($path), 493, true);
        // }
        // File::put($path, $data);

        // return true;
    }

    public static function getFileData($file, $convert_to_array = true)
    {
        // $file = File::get($file);
        // if (!empty($file)) {
        //     if ($convert_to_array) {
        //         return json_decode($file, true);
        //     } else {
        //         return $file;
        //     }
        // }
        // if (!$convert_to_array) {
        //     return null;
        // }
        // return [];
    }

    public static function isRoute($routes = []) {
        $prefixed_array = preg_filter('/^/', '/admin/', $routes);

        return Request::is($prefixed_array);
    }

    public static function statusBadge($status) {
        $badges = [
            'pending' => 'badge-light-primary',
            'in_progress' => 'badge-light-primary',
            'completed' => 'badge-light-success',
            'over_due' => 'badge-light-danger',
        ];
        return $badges[$status];
    }

    public static function randomBackground() {
        $colors = [
            'bg-primary',
            // 'bg-white',
            'bg-light',
            'bg-secondary',
            'bg-success',
            'bg-info',
            'bg-warning',
            'bg-dark',
            'bg-danger',
        ];
        $k = array_rand($colors);
        return $colors[$k];
    }
}
