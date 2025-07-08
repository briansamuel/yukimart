<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductBarcodeController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Product Barcode API Routes (for internal use - no auth required)
Route::prefix('products')->group(function () {
    Route::get('/barcode/{barcode}', [ProductBarcodeController::class, 'findByBarcode'])->name('api.products.barcode');
    Route::get('/search', [ProductBarcodeController::class, 'search'])->name('api.products.search');
    Route::post('/barcode/validate', [ProductBarcodeController::class, 'validateBarcode'])->name('api.products.barcode.validate');
});

// Public API routes (if needed for external integrations)
Route::prefix('public')->group(function () {
    // Add public API routes here if needed
});
