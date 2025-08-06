<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8" />
    <title>{{ __('order.quick_order') }} - {{ config('app.name') }}</title>
    <meta name="description" content="{{ __('order.quick_order') }}" />
    <meta name="keywords" content="{{ __('order.quick_order') }}" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />

    <!-- Global Stylesheets Bundle -->
    <link href="{{ asset('admin-assets/assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('admin-assets/assets/css/style.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('admin-assets/css/quick-order-tabs.css') }}" rel="stylesheet" type="text/css" />

    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />
    
    <!-- Quick Orders CSS -->
    <link href="{{ asset('admin-assets/css/quick-orders.css') }}" rel="stylesheet" type="text/css" />
</head>

<body>
    <div class="quick-order-container">
        <!-- Header -->
        <div class="quick-order-header">
            <div class="header-left">
                <!-- Barcode Input -->
                <div class="barcode-input-container">
                    <i class="fas fa-barcode" style="color: #7e8299; font-size: 18px;"></i>
                    <input type="text"
                           id="barcodeInput"
                           class="barcode-input"
                           placeholder="{{ __('order.search_product_name_sku_barcode') }}"
                           autocomplete="off">

                    <!-- Product Suggestions Dropdown -->
                    <div class="product-suggestions" id="productSuggestions"></div>
                </div>
            </div>

            <div class="header-center">
                <!-- Order Tabs -->
                <div class="order-tabs" id="orderTabsContainer">
                    <!-- Tabs will be added here dynamically -->
                </div>

                <!-- Add Tab Buttons -->
                <div class="tab-controls-group">
                    <button type="button" id="addNewInvoiceBtn" class="add-tab-btn" onclick="addNewTab('invoice')" title="Tạo hóa đơn mới">
                        <i class="fas fa-plus"></i>
                    </button>
                    <div class="dropdown" id="addTabDropdown">
                        <button type="button" id="addTabDropdownBtn" class="add-tab-btn dropdown-toggle" data-bs-toggle="dropdown" title="Thêm tab">
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="#" onclick="addNewTab('order')">
                                    <i class="fas fa-shopping-cart me-2"></i>
                                    Tạo đơn hàng mới
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="#" onclick="addNewTab('invoice')">
                                    <i class="fas fa-file-invoice me-2"></i>
                                    Tạo hóa đơn mới
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="#" onclick="addNewTab('return')">
                                    <i class="fas fa-undo me-2"></i>
                                    Trả hàng
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="quick-order-content">
            <!-- Tab Content Container -->
            <div id="orderTabsContent" class="h-100">
                <!-- Tab content will be added here dynamically -->
            </div>
        </div>
    </div>

    <!-- Include Tab Template -->
    @include('admin.quick-order.elements.tab-template')

    <!-- Include Modals -->
    @include('admin.quick-order.modals.discount-modal')
    @include('admin.quick-order.modals.other-charges-modal')
    @include('admin.quick-order.modals.customer-info-modal')
    @include('admin.quick-order.modals.confirm-close-tab-modal')
    @include('admin.quick-order.modals.invoice-selection-modal')

    <!-- Global Stylesheets Bundle -->
    <script src="{{ asset('admin-assets/assets/plugins/global/plugins.bundle.js') }}"></script>
    <script src="{{ asset('admin-assets/assets/js/scripts.bundle.js') }}"></script>

    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <!-- Quick Order JS -->
    <script src="{{ asset('admin-assets/js/quick-order-main.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('admin-assets/js/quick-order-modals.js') }}?v={{ time() }}"></script>

    <script>
        // Pass server data to JavaScript
        window.defaultBranchShop = @json($defaultBranchShop ?? null);
        window.bankAccounts = @json($bankAccounts ?? []);
        window.customers = @json($customers ?? []);
        window.sellers = @json($sellers ?? []);
        window.currentUserId = {{ Auth::id() }};

        // Initialize Quick Order when document is ready
        $(document).ready(function() {
            initializeQuickOrder();
        });
    </script>
</body>
</html>
