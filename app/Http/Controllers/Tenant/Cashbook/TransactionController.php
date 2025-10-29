<?php

namespace App\Http\Controllers\Tenant\Cashbook;

use App\Http\Controllers\Tenant\BaseTenantController;
use Illuminate\Http\Request;

class TransactionController extends BaseTenantController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // TODO: Implement index method
        return view('tenant.cashbook.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // TODO: Implement create method
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // TODO: Implement store method
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        // TODO: Implement show method
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        // TODO: Implement edit method
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // TODO: Implement update method
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // TODO: Implement destroy method
    }
}