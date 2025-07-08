<?php

namespace App\Http\Controllers\Admin\CMS;


use App\Http\Controllers\Controller;

use Illuminate\Http\Request;


class GalleryController extends Controller
{
    private $setting;
    private $request;


    public function __construct(Request $request)
    {
        $this->request = $request;

    }

    /**
     * ======================
     * Method:: INDEX
     * ======================
     */

    public function index()
    {
        session()->start();
        session()->put('RF.subfolder', "thu-vien");
       
        return view('admin.galleries.index');
    }

    
}
