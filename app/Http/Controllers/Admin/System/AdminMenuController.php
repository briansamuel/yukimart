<?php

namespace App\Http\Controllers\Admin\System;


use Harimayco\Menu\Controllers\MenuController;
use Harimayco\Menu\Models\Menus;


class AdminMenuController extends MenuController
{
    // Override createnewmenu function from package
    public function createnewmenu()
    {

        $menu = new Menus();
        $menu->name = request()->input("menuname");
        $menu->language = request()->input("language");
        $menu->save();
        return json_encode(array("resp" => $menu->id));
    }
}
