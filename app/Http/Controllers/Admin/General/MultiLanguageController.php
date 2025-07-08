<?php

namespace App\Http\Controllers\General;


use App\Http\Controllers\Controller;

use Illuminate\Http\Request;


class MultiLanguageController extends Controller
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

        return view('admin.languages.index');
    }

    
}
