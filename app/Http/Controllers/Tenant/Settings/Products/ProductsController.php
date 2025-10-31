<?php

namespace App\Http\Controllers\Tenant\Settings\Products;

use App\Http\Controllers\Tenant\BaseTenantController;

class ProductsController extends BaseTenantController
{
    /**
     * Display products settings overview page
     */
    public function index()
    {
        return view('tenant.settings.products.index');
    }
}

