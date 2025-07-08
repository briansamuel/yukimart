<?php

// Thêm vào routes/web.php hoặc routes/admin.php

use App\Http\Controllers\Admin\InventoryController;

// Inventory Management Routes
Route::prefix('admin/inventory')->name('admin.inventory.')->group(function () {
    
    // Danh sách tồn kho
    Route::get('/', [InventoryController::class, 'index'])->name('index');
    
    // Nhập kho
    Route::get('/import', [InventoryController::class, 'importForm'])->name('import.form');
    Route::post('/import', [InventoryController::class, 'import'])->name('import');
    
    // Xuất kho
    Route::get('/export', [InventoryController::class, 'exportForm'])->name('export.form');
    Route::post('/export', [InventoryController::class, 'export'])->name('export');
    
    // Chuyển kho
    Route::get('/transfer', [InventoryController::class, 'transferForm'])->name('transfer.form');
    Route::post('/transfer', [InventoryController::class, 'transfer'])->name('transfer');
    
    // Điều chỉnh tồn kho
    Route::get('/adjust', [InventoryController::class, 'adjustForm'])->name('adjust.form');
    Route::post('/adjust', [InventoryController::class, 'adjust'])->name('adjust');
    
    // API endpoints
    Route::get('/stock', [InventoryController::class, 'getProductStock'])->name('stock');
    
    // Lịch sử giao dịch
    Route::get('/transactions', [InventoryController::class, 'transactions'])->name('transactions');
    
    // Báo cáo
    Route::get('/report', [InventoryController::class, 'report'])->name('report');
});

// Warehouse Management Routes (nếu cần)
Route::prefix('admin/warehouses')->name('admin.warehouses.')->group(function () {
    Route::get('/', [WarehouseController::class, 'index'])->name('index');
    Route::get('/create', [WarehouseController::class, 'create'])->name('create');
    Route::post('/', [WarehouseController::class, 'store'])->name('store');
    Route::get('/{warehouse}/edit', [WarehouseController::class, 'edit'])->name('edit');
    Route::put('/{warehouse}', [WarehouseController::class, 'update'])->name('update');
    Route::delete('/{warehouse}', [WarehouseController::class, 'destroy'])->name('destroy');
});

/* 
Các URL sẽ có dạng:
- /admin/inventory - Danh sách tồn kho
- /admin/inventory/import - Form nhập kho
- /admin/inventory/export - Form xuất kho
- /admin/inventory/transfer - Form chuyển kho
- /admin/inventory/adjust - Form điều chỉnh
- /admin/inventory/transactions - Lịch sử giao dịch
- /admin/inventory/report - Báo cáo tồn kho
- /admin/inventory/stock?product_id=1&warehouse_id=1 - API lấy số lượng tồn
*/
