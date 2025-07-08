<?php
namespace App\Http\Controllers\Admin\CMS;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Harimayco\Menu\Facades\Menu;

class MenuController extends Controller
{


    public function __construct()
    {


    }

    /**
     * ======================
     * Method:: INDEX
     * ======================
     */

    public function index()
    {

        return view('admin.menus.menu');
    }
}
