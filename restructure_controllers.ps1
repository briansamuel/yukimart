# Script to restructure controllers according to new mega menu structure

Write-Host "=== RESTRUCTURING CONTROLLERS ===" -ForegroundColor Cyan
Write-Host ""

# Step 1: Copy existing controllers from Admin/CMS to Tenant modules
Write-Host "Step 1: Copying existing controllers..." -ForegroundColor Yellow

$copyMappings = @(
    # Catalog
    @{
        Source = "app/Http/Controllers/Admin/CMS/ProductController.php"
        Dest = "app/Http/Controllers/Tenant/Catalog/ProductController.php"
        OldNS = "App\Http\Controllers\Admin\CMS"
        NewNS = "App\Http\Controllers\Tenant\Catalog"
    },
    @{
        Source = "app/Http/Controllers/Admin/CMS/ProductCategoryController.php"
        Dest = "app/Http/Controllers/Tenant/Catalog/CategoryController.php"
        OldNS = "App\Http\Controllers\Admin\CMS"
        NewNS = "App\Http\Controllers\Tenant\Catalog"
    },
    
    # Inventory
    @{
        Source = "app/Http/Controllers/Admin/CMS/InventoryController.php"
        Dest = "app/Http/Controllers/Tenant/Inventory/InventoryController.php"
        OldNS = "App\Http\Controllers\Admin\CMS"
        NewNS = "App\Http\Controllers\Tenant\Inventory"
    },
    
    # Purchasing
    @{
        Source = "app/Http/Controllers/Admin/CMS/SupplierController.php"
        Dest = "app/Http/Controllers/Tenant/Purchasing/SupplierController.php"
        OldNS = "App\Http\Controllers\Admin\CMS"
        NewNS = "App\Http\Controllers\Tenant\Purchasing"
    },
    
    # Sales
    @{
        Source = "app/Http/Controllers/Admin/CMS/OrderController.php"
        Dest = "app/Http/Controllers/Tenant/Sales/OrderController.php"
        OldNS = "App\Http\Controllers\Admin\CMS"
        NewNS = "App\Http\Controllers\Tenant\Sales"
    },
    @{
        Source = "app/Http/Controllers/Admin/CMS/InvoiceController.php"
        Dest = "app/Http/Controllers/Tenant/Sales/InvoiceController.php"
        OldNS = "App\Http\Controllers\Admin\CMS"
        NewNS = "App\Http\Controllers\Tenant\Sales"
    },
    @{
        Source = "app/Http/Controllers/Admin/CMS/ReturnController.php"
        Dest = "app/Http/Controllers/Tenant/Sales/ReturnController.php"
        OldNS = "App\Http\Controllers\Admin\CMS"
        NewNS = "App\Http\Controllers\Tenant\Sales"
    },
    
    # CRM
    @{
        Source = "app/Http/Controllers/Admin/CMS/CustomerController.php"
        Dest = "app/Http/Controllers/Tenant/CRM/CustomerController.php"
        OldNS = "App\Http\Controllers\Admin\CMS"
        NewNS = "App\Http\Controllers\Tenant\CRM"
    },
    
    # Staff
    @{
        Source = "app/Http/Controllers/Tenant/Settings/Shop/UserManagerController.php"
        Dest = "app/Http/Controllers/Tenant/Staff/UserController.php"
        OldNS = "App\Http\Controllers\Tenant\Settings\Shop"
        NewNS = "App\Http\Controllers\Tenant\Staff"
    },
    
    # Settings
    @{
        Source = "app/Http/Controllers/Admin/CMS/BranchShopController.php"
        Dest = "app/Http/Controllers/Tenant/Settings/WarehouseController.php"
        OldNS = "App\Http\Controllers\Admin\CMS"
        NewNS = "App\Http\Controllers\Tenant\Settings"
    },
    @{
        Source = "app/Http/Controllers/Tenant/Settings/Shop/BranchManagerController.php"
        Dest = "app/Http/Controllers/Tenant/Settings/BranchController.php"
        OldNS = "App\Http\Controllers\Tenant\Settings\Shop"
        NewNS = "App\Http\Controllers\Tenant\Settings"
    }
)

foreach ($mapping in $copyMappings) {
    if (Test-Path $mapping.Source) {
        Copy-Item $mapping.Source $mapping.Dest -Force
        
        # Update namespace
        $content = Get-Content $mapping.Dest -Raw
        $content = $content -replace [regex]::Escape("namespace $($mapping.OldNS);"), "namespace $($mapping.NewNS);"
        $content = $content -replace "use App\\Http\\Controllers\\Admin\\BaseAdminController;", "use App\Http\Controllers\Tenant\BaseTenantController;"
        $content = $content -replace "extends BaseAdminController", "extends BaseTenantController"
        Set-Content $mapping.Dest $content -NoNewline
        
        Write-Host "  ✓ Copied: $(Split-Path $mapping.Dest -Leaf)" -ForegroundColor Green
    } else {
        Write-Host "  ✗ Not found: $($mapping.Source)" -ForegroundColor Red
    }
}

Write-Host ""
Write-Host "Step 2: Creating new controller stubs..." -ForegroundColor Yellow

# Step 2: Create new controller stubs
$newControllers = @(
    # Catalog
    @{ Path = "app/Http/Controllers/Tenant/Catalog/PriceListController.php"; NS = "App\Http\Controllers\Tenant\Catalog"; Class = "PriceListController" },
    
    # Inventory
    @{ Path = "app/Http/Controllers/Tenant/Inventory/TransferController.php"; NS = "App\Http\Controllers\Tenant\Inventory"; Class = "TransferController" },
    @{ Path = "app/Http/Controllers/Tenant/Inventory/StocktakeController.php"; NS = "App\Http\Controllers\Tenant\Inventory"; Class = "StocktakeController" },
    @{ Path = "app/Http/Controllers/Tenant/Inventory/DisposalController.php"; NS = "App\Http\Controllers\Tenant\Inventory"; Class = "DisposalController" },
    
    # Purchasing
    @{ Path = "app/Http/Controllers/Tenant/Purchasing/PurchaseOrderController.php"; NS = "App\Http\Controllers\Tenant\Purchasing"; Class = "PurchaseOrderController" },
    @{ Path = "app/Http/Controllers/Tenant/Purchasing/PurchaseReturnController.php"; NS = "App\Http\Controllers\Tenant\Purchasing"; Class = "PurchaseReturnController" },
    
    # Sales
    @{ Path = "app/Http/Controllers/Tenant/Sales/ShippingPartnerController.php"; NS = "App\Http\Controllers\Tenant\Sales"; Class = "ShippingPartnerController" },
    @{ Path = "app/Http/Controllers/Tenant/Sales/ShippingController.php"; NS = "App\Http\Controllers\Tenant\Sales"; Class = "ShippingController" },
    
    # CRM
    @{ Path = "app/Http/Controllers/Tenant/CRM/PromotionController.php"; NS = "App\Http\Controllers\Tenant\CRM"; Class = "PromotionController" },
    
    # Staff
    @{ Path = "app/Http/Controllers/Tenant/Staff/WorkScheduleController.php"; NS = "App\Http\Controllers\Tenant\Staff"; Class = "WorkScheduleController" },
    @{ Path = "app/Http/Controllers/Tenant/Staff/TimesheetController.php"; NS = "App\Http\Controllers\Tenant\Staff"; Class = "TimesheetController" },
    @{ Path = "app/Http/Controllers/Tenant/Staff/SalaryController.php"; NS = "App\Http\Controllers\Tenant\Staff"; Class = "SalaryController" },
    @{ Path = "app/Http/Controllers/Tenant/Staff/CommissionController.php"; NS = "App\Http\Controllers\Tenant\Staff"; Class = "CommissionController" },
    @{ Path = "app/Http/Controllers/Tenant/Staff/StaffSettingController.php"; NS = "App\Http\Controllers\Tenant\Staff"; Class = "StaffSettingController" },
    
    # Cashbook
    @{ Path = "app/Http/Controllers/Tenant/Cashbook/AccountController.php"; NS = "App\Http\Controllers\Tenant\Cashbook"; Class = "AccountController" },
    @{ Path = "app/Http/Controllers/Tenant/Cashbook/TransactionController.php"; NS = "App\Http\Controllers\Tenant\Cashbook"; Class = "TransactionController" },
    
    # Reports
    @{ Path = "app/Http/Controllers/Tenant/Reports/BusinessReportController.php"; NS = "App\Http\Controllers\Tenant\Reports"; Class = "BusinessReportController" },
    @{ Path = "app/Http/Controllers/Tenant/Reports/ProductReportController.php"; NS = "App\Http\Controllers\Tenant\Reports"; Class = "ProductReportController" },
    @{ Path = "app/Http/Controllers/Tenant/Reports/CustomerReportController.php"; NS = "App\Http\Controllers\Tenant\Reports"; Class = "CustomerReportController" },
    @{ Path = "app/Http/Controllers/Tenant/Reports/StaffReportController.php"; NS = "App\Http\Controllers\Tenant\Reports"; Class = "StaffReportController" },
    @{ Path = "app/Http/Controllers/Tenant/Reports/ChannelReportController.php"; NS = "App\Http\Controllers\Tenant\Reports"; Class = "ChannelReportController" },
    @{ Path = "app/Http/Controllers/Tenant/Reports/FinanceReportController.php"; NS = "App\Http\Controllers\Tenant\Reports"; Class = "FinanceReportController" }
)

$stubTemplate = @"
<?php

namespace {NAMESPACE};

use App\Http\Controllers\Tenant\BaseTenantController;
use Illuminate\Http\Request;

class {CLASSNAME} extends BaseTenantController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // TODO: Implement index method
        return view('tenant.{MODULE}.index');
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
    public function store(Request `$request)
    {
        // TODO: Implement store method
    }

    /**
     * Display the specified resource.
     */
    public function show(`$id)
    {
        // TODO: Implement show method
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(`$id)
    {
        // TODO: Implement edit method
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request `$request, `$id)
    {
        // TODO: Implement update method
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(`$id)
    {
        // TODO: Implement destroy method
    }
}
"@

foreach ($controller in $newControllers) {
    $module = ($controller.NS -split '\\')[-1].ToLower()
    $stub = $stubTemplate -replace '{NAMESPACE}', $controller.NS
    $stub = $stub -replace '{CLASSNAME}', $controller.Class
    $stub = $stub -replace '{MODULE}', $module
    
    Set-Content $controller.Path $stub -NoNewline
    Write-Host "  ✓ Created: $(Split-Path $controller.Path -Leaf)" -ForegroundColor Green
}

Write-Host ""
Write-Host "=== RESTRUCTURE COMPLETED ===" -ForegroundColor Green
Write-Host ""
Write-Host "Summary:" -ForegroundColor Cyan
Write-Host "  - Copied: $($copyMappings.Count) existing controllers" -ForegroundColor White
Write-Host "  - Created: $($newControllers.Count) new controller stubs" -ForegroundColor White
Write-Host ""
Write-Host "Next steps:" -ForegroundColor Yellow
Write-Host "  1. Update routes in routes/tenant.php" -ForegroundColor White
Write-Host "  2. Implement TODO methods in new controllers" -ForegroundColor White
Write-Host "  3. Test all routes" -ForegroundColor White

