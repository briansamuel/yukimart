<?php

namespace App\Http\Controllers\Tenant\Settings\Shop;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class RetailerInfoController extends Controller
{
    /**
     * Display retailer information page
     */
    public function index()
    {
        $tenant = Auth::user()->tenant;

        // Get description from settings JSON
        $tenantSettings = $tenant->settings ?? [];

        $settings = [
            'store_name' => $tenant->name ?? '',
            'store_subdomain' => $tenant->subdomain ?? '',
            'store_phone' => $tenant->phone ?? '',
            'store_email' => $tenant->email ?? '',
            'store_address' => $tenant->address ?? '',
            'store_logo' => $tenant->logo_url ?? '',
            'store_description' => $tenantSettings['description'] ?? '',
        ];

        return view('tenant.settings.shop.retailer-info.index', compact('settings'));
    }

    /**
     * Update retailer information
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'store_name' => 'required|string|max:255',
            'store_phone' => 'nullable|string|max:20',
            'store_email' => 'nullable|email|max:255',
            'store_address' => 'nullable|string|max:500',
            'store_description' => 'nullable|string|max:1000',
            'store_logo' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $tenant = Auth::user()->tenant;

            // Update tenant basic information
            $tenant->name = $request->store_name;
            $tenant->phone = $request->store_phone;
            $tenant->email = $request->store_email;
            $tenant->address = $request->store_address;

            // Update description in settings JSON
            $settings = $tenant->settings ?? [];
            $settings['description'] = $request->store_description;
            $tenant->settings = $settings;

            // Handle logo upload
            if ($request->hasFile('store_logo')) {
                // Delete old logo if exists
                if ($tenant->logo_url && Storage::disk('public')->exists($tenant->logo_url)) {
                    Storage::disk('public')->delete($tenant->logo_url);
                }

                $logo = $request->file('store_logo');
                $logoPath = $logo->store('tenant-logos', 'public');
                $tenant->logo_url = $logoPath;
            }

            $tenant->save();

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật thông tin cửa hàng thành công!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }
}

