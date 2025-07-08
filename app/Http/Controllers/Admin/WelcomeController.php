<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;

class WelcomeController extends Controller
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
        
        return view('admin.welcome');
    }
}
