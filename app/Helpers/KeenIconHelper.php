<?php

namespace App\Helpers;

class FontAwesomeHelper
{
    /**
     * Font Awesome icon mappings
     */
    private static $iconMappings = [
        'home' => 'fas fa-home',
        'user' => 'fas fa-user',
        'setting-2' => 'fas fa-cog',
        'settings' => 'fas fa-cog',
        'pencil' => 'fas fa-pencil-alt',
        'notepad-edit' => 'fas fa-edit',
        'edit' => 'fas fa-edit',
        'copy' => 'fas fa-copy',
        'printer' => 'fas fa-print',
        'print' => 'fas fa-print',
        'file-down' => 'fas fa-download',
        'export' => 'fas fa-download',
        'undo' => 'fas fa-undo',
        'question' => 'fas fa-question-circle',
        'time' => 'fas fa-clock',
        'check-circle' => 'fas fa-check-circle',
        'cross-circle' => 'fas fa-times-circle',
        'cross' => 'fas fa-times',
        'arrow-up-circle' => 'fas fa-arrow-circle-up',
        'arrows-circle' => 'fas fa-sync-alt',
        'filter' => 'fas fa-filter',
        'calendar' => 'fas fa-calendar',
        'calendar-2' => 'fas fa-calendar-alt',
        'eye' => 'fas fa-eye',
        'view' => 'fas fa-eye',
        'rocket' => 'fas fa-rocket',
        'flash' => 'fas fa-bolt',
        'package' => 'fas fa-box',
        'delivery' => 'fas fa-truck',
        'trash' => 'fas fa-trash',
        'delete' => 'fas fa-trash',
        'wallet' => 'fas fa-wallet',
        'medal-star' => 'fas fa-medal',
        'dots-vertical' => 'fas fa-ellipsis-v',
        'plus' => 'fas fa-plus',
        'add' => 'fas fa-plus',
        'check' => 'fas fa-check',
        'save' => 'fas fa-save',
        'cancel' => 'fas fa-times',
        'magnifier' => 'fas fa-search',
        'search' => 'fas fa-search',
        'information' => 'fas fa-info-circle',
        'star' => 'fas fa-star',
    ];

    /**
     * Generate Font Awesome icon HTML
     *
     * @param string $name Icon name
     * @param string $size Icon size class
     * @param string $class Additional CSS classes
     * @param string $color Text color class
     * @return string
     */
    public static function render($name, $size = '', $class = '', $color = '')
    {
        $iconClass = self::$iconMappings[$name] ?? 'fas fa-question-circle';

        if ($size) {
            $iconClass .= " {$size}";
        }

        if ($class) {
            $iconClass .= " {$class}";
        }

        if ($color) {
            $iconClass .= " text-{$color}";
        }

        return "<i class=\"{$iconClass}\"></i>";
    }

    /**
     * Common icon shortcuts
     */
    public static function home($size = '', $class = '')
    {
        return self::render('home', $size, $class);
    }

    public static function user($size = '', $class = '')
    {
        return self::render('user', $size, $class);
    }

    public static function edit($size = '', $class = '')
    {
        return self::render('edit', $size, $class);
    }

    public static function delete($size = '', $class = '')
    {
        return self::render('delete', $size, $class);
    }

    public static function view($size = '', $class = '')
    {
        return self::render('view', $size, $class);
    }

    public static function settings($size = '', $class = '')
    {
        return self::render('settings', $size, $class);
    }

    public static function filter($size = '', $class = '')
    {
        return self::render('filter', $size, $class);
    }

    public static function search($size = '', $class = '')
    {
        return self::render('search', $size, $class);
    }

    public static function add($size = '', $class = '')
    {
        return self::render('add', $size, $class);
    }

    public static function save($size = '', $class = '')
    {
        return self::render('save', $size, $class);
    }

    public static function cancel($size = '', $class = '')
    {
        return self::render('cancel', $size, $class);
    }

    public static function print($size = '', $class = '')
    {
        return self::render('print', $size, $class);
    }

    public static function export($size = '', $class = '')
    {
        return self::render('export', $size, $class);
    }

    public static function copy($size = '', $class = '')
    {
        return self::render('copy', $size, $class);
    }

    public static function wallet($size = '', $class = '')
    {
        return self::render('wallet', $size, $class);
    }

    public static function package($size = '', $class = '')
    {
        return self::render('package', $size, $class);
    }

    public static function delivery($size = '', $class = '')
    {
        return self::render('delivery', $size, $class);
    }

    public static function time($size = '', $class = '')
    {
        return self::render('time', $size, $class);
    }

    public static function checkCircle($size = '', $class = '')
    {
        return self::render('check-circle', $size, $class);
    }

    public static function crossCircle($size = '', $class = '')
    {
        return self::render('cross-circle', $size, $class);
    }

    public static function calendar($size = '', $class = '')
    {
        return self::render('calendar', $size, $class);
    }

    public static function rocket($size = '', $class = '')
    {
        return self::render('rocket', $size, $class);
    }

    /**
     * Status icons with predefined colors
     */
    public static function statusSuccess($size = '', $class = '')
    {
        return self::render('check-circle', $size, $class . ' text-success');
    }

    public static function statusWarning($size = '', $class = '')
    {
        return self::render('time', $size, $class . ' text-warning');
    }

    public static function statusDanger($size = '', $class = '')
    {
        return self::render('cross-circle', $size, $class . ' text-danger');
    }

    public static function statusInfo($size = '', $class = '')
    {
        return self::render('information', $size, $class . ' text-info');
    }

    public static function statusPrimary($size = '', $class = '')
    {
        return self::render('star', $size, $class . ' text-primary');
    }
}