<?php

namespace App\Helpers;

class ArrayHelper
{
    /*
     *
     */

    public static function removeArrayNull($params)
    {
        if (!is_array($params)) {
            return $params;
        }

        if (isset($params['_token'])) {
            unset($params['_token']);
        }

        return collect($params)
            ->reject(function ($item) {
                return is_null($item);
            })
            ->flatMap(function ($item, $key) {

                return is_numeric($key)
                    ? [self::removeArrayNull($item)]
                    : [$key => self::removeArrayNull($item)];
            })
            ->toArray();
    }

    public static function removeToken($params)
    {
        if (!is_array($params)) {
            return $params;
        }

        if (isset($params['_token'])) {
            unset($params['_token']);
        }

        return $params;
    }

    public static function arrayLang() {
        return [
            'vi' => 'Tiếng Việt',
            'en' => 'Tiếng Anh',
        ];
    }
}
