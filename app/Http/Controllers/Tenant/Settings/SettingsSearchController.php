<?php

namespace App\Http\Controllers\Tenant\Settings;

use App\Http\Controllers\Tenant\BaseTenantController;
use App\Services\SettingsSearchService;
use Illuminate\Http\Request;

class SettingsSearchController extends BaseTenantController
{
    protected $service;

    public function __construct(SettingsSearchService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    /**
     * Search settings
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $query = $request->input('q', '');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }
        
        $results = $this->service->search($query, auth()->user());
        
        return response()->json($results);
    }
}

