@extends('admin.layouts.tenant-app')
@section('title', __('product.products'))
@section('style')
  
    <link rel="stylesheet" href="{{ asset('admin-assets/assets/plugins/custom/datatables/datatables.bundle.css') }}" />
    <link href="{{ asset('admin-assets/css/globals.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('admin-assets/css/table-loading.css') }}" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="{{ asset('admin-assets/assets/css/custom/product-tabs.css') }}" />
    @include('admin.products.elements.stock-status-styles')
    @include('admin.products.elements.row-expansion-styles')
@endsection
@section('content')
    <!--begin::Content-->
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <!--begin::Toolbar-->
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">

        </div>
        <!--end::Toolbar-->
        <div id="kt_app_content_container" class="app-container container-fluid">
            <div class="d-flex flex-column flex-lg-row">
                @include('admin.products.elements.filter')

                <!--begin::Content-->
                <div class="flex-lg-row-fluid ms-lg-10 order-2 order-lg-2">
                    <div class="d-flex flex-column gap-7 gap-lg-10">
                        <!--begin::Card-->
                        <div class="card card-flush">
                            <!--begin::Card header-->
                            <div id="kt_products_table_toolbar" class="card-header align-items-center py-5 gap-2 gap-md-5">
                                <!--begin::Card title-->
                                <div class="card-title">
                                    <!--begin::Search-->
                                    <div class="d-flex align-items-center position-relative my-1">
                                        <i class="fas fa-search fs-3 position-absolute ms-4"></i>
                                        <input type="text" id="kt_products_search"
                                            class="form-control form-control-solid w-250px ps-12"
                                            placeholder="Tìm kiếm sản phẩm..." />
                                    </div>
                                    <!--end::Search-->
                                </div>
                                <!--end::Card title-->

                                <!--begin::Card toolbar-->
                                <div class="card-toolbar flex-row-fluid justify-content-end gap-5">
                                   <!--begin::Bulk Actions Dropdown-->
                                    @can('catalog.products.update')
                                    <div class="btn-group" id="bulk-actions-dropdown" style="display: none;">
                                        <button type="button" class="btn btn-light-warning dropdown-toggle"
                                            data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fas fa-tasks fs-2 me-2"></i>
                                            <span id="bulk-actions-text">Thao tác hàng loạt</span>
                                            <span class="badge badge-circle badge-warning ms-2" id="bulk-count">0</span>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a class="dropdown-item" href="#" id="bulk-update-status">
                                                    <i class="fas fa-toggle-on text-success me-2"></i>Cập nhật trạng thái
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="#" id="bulk-update-category">
                                                    <i class="fas fa-folder text-primary me-2"></i>Cập nhật danh mục
                                                </a>
                                            </li>
                                            <li>
                                                <hr class="dropdown-divider">
                                            </li>
                                            <li>
                                                <a class="dropdown-item text-danger" href="#" id="bulk-delete">
                                                    <i class="fas fa-trash text-danger me-2"></i>Xóa
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                    @endcan
                                    <!--end::Bulk Actions Dropdown-->

                                    <!--begin::Reset Filters-->
                                    <button type="button" class="btn btn-light-warning" id="reset_filters_btn">
                                        <i class="fas fa-redo"></i>
                                        Reset Filters
                                    </button>
                                    <!--end::Reset Filters-->

                                    <!--begin::Export dropdown-->
                                    @can('catalog.products.export')
                                    <button type="button" class="btn btn-light-primary" data-kt-menu-trigger="click"
                                        data-kt-menu-placement="bottom-end">
                                        <i class="fas fa-download"></i>
                                        Xuất Excel
                                    </button>
                                    @endcan
                                    <!--end::Export dropdown-->

                                    <!--begin::Column visibility-->
                                    <div class="position-relative">
                                        <button type="button" class="btn btn-success column-visibility-trigger"
                                            id="column_visibility_trigger">
                                            <i class="fas fa-list fs-2"></i>
                                        </button>
                                        <!-- Column visibility panel -->
                                        <div id="column_visibility_panel" class="column-visibility-panel position-absolute"
                                            style="display: none;">
                                            <div class="panel-content">
                                                <div class="panel-header">
                                                    <h6 class="fw-bold text-dark mb-0">Chọn cột hiển thị</h6>
                                                </div>
                                                <div class="panel-body">
                                                    <div class="row">
                                                        <div class="col-6">
                                                          
                                                            <div class="form-check form-check-custom form-check-solid mb-3">
                                                                <input class="form-check-input column-toggle"
                                                                    type="checkbox" value="0" id="col_checkbox"
                                                                    checked />
                                                                <label class="form-check-label fw-semibold"
                                                                    for="col_checkbox">Checkbox</label>
                                                            </div>
                                                            <div class="form-check form-check-custom form-check-solid mb-3">
                                                                <input class="form-check-input column-toggle"
                                                                    type="checkbox" value="1" id="col_product_name"
                                                                    checked />
                                                                <label class="form-check-label fw-semibold"
                                                                    for="col_product_name">Tên sản phẩm</label>
                                                            </div>
                                                            <div class="form-check form-check-custom form-check-solid mb-3">
                                                                <input class="form-check-input column-toggle"
                                                                    type="checkbox" value="2" id="col_sku"
                                                                    checked />
                                                                <label class="form-check-label fw-semibold"
                                                                    for="col_sku">SKU</label>
                                                            </div>
                                                            <div class="form-check form-check-custom form-check-solid mb-3">
                                                                <input class="form-check-input column-toggle"
                                                                    type="checkbox" value="3" id="col_price"
                                                                    checked />
                                                                <label class="form-check-label fw-semibold"
                                                                    for="col_price">Giá</label>
                                                            </div>
                                                        </div>
                                                        <div class="col-6">
                                                            <div class="form-check form-check-custom form-check-solid mb-3">
                                                                <input class="form-check-input column-toggle"
                                                                    type="checkbox" value="4" id="col_stock"
                                                                    checked />
                                                                <label class="form-check-label fw-semibold"
                                                                    for="col_stock">Tồn kho</label>
                                                            </div>
                                                            <div class="form-check form-check-custom form-check-solid mb-3">
                                                                <input class="form-check-input column-toggle"
                                                                    type="checkbox" value="5" id="col_status"
                                                                    checked />
                                                                <label class="form-check-label fw-semibold"
                                                                    for="col_status">Trạng thái</label>
                                                            </div>
                                                            <div class="form-check form-check-custom form-check-solid mb-3">
                                                                <input class="form-check-input column-toggle"
                                                                    type="checkbox" value="6" id="col_created_at"
                                                                    checked />
                                                                <label class="form-check-label fw-semibold"
                                                                    for="col_created_at">Ngày tạo</label>
                                                            </div>
                                                            <div class="form-check form-check-custom form-check-solid mb-3">
                                                                <input class="form-check-input column-toggle"
                                                                    type="checkbox" value="7" id="col_actions"
                                                                    checked />
                                                                <label class="form-check-label fw-semibold"
                                                                    for="col_actions">Actions</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!--end::Column visibility-->

                                    <!--begin::Add product-->
                                    @can('catalog.products.create')
                                    <a href="#" class="btn btn-primary" id="add_product_btn">
                                        <i class="fas fa-plus"></i>
                                        Thêm mới
                                    </a>
                                    @endcan
                                    <!--end::Add product-->
                                </div>
                                <!--end::Card toolbar-->
                            </div>
                            <!--end::Card header-->

                            <div class="card-body pt-0">
                                <!--begin::Table container-->
                                <div id="kt_products_table_container" class="kt_table_responsive_container table-responsive" style="position: relative; overflow-x: auto;">
                                    <!--begin::Table-->
                                    <table class="table kt_table_responsive align-middle table-row-dashed fs-6 gy-5" id="kt_products_table">
                                        <!--begin::Table head-->
                                        <thead>
                                            <!--begin::Table row-->
                                            <tr class="text-start text-muted fw-bolder fs-7 text-uppercase gs-0">
                                                <th class="w-10px pe-2">
                                                    <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                                        <input class="form-check-input" type="checkbox" id="kt_products_select_all" value="1">
                                                    </div>
                                                </th>
                                                <th>Tên sản phẩm</th>
                                                <th>SKU</th>
                                                <th>Giá</th>
                                                <th>Tồn kho</th>
                                                <th>Trạng thái</th>
                                                <th>Ngày tạo</th>
                                                <th class="text-end min-w-100px">Actions</th>
                                            </tr>
                                            <!--end::Table row-->
                                        </thead>
                                        <!--end::Table head-->
                                        <!--begin::Table body-->
                                        <tbody class="text-gray-600 fw-bold">

                                        </tbody>
                                        <!--end::Table body-->
                                    </table>
                                    <!--end::Table-->
                                </div>
                                <!--end::Table container-->

                                <!--begin::Pagination-->
                                <div id="kt_products_pagination" class="d-flex justify-content-between align-items-center flex-wrap pt-5">
                                    <div class="d-flex align-items-center">
                                        <span class="text-muted me-2" id="pagination-info">Hiển thị 1 đến 25 của 0 kết quả</span>
                                        <select class="form-select form-select-sm w-auto" id="per_page_select">
                                            <option value="10">10 / trang</option>
                                            <option value="25" selected>25 / trang</option>
                                            <option value="50">50 / trang</option>
                                            <option value="100">100 / trang</option>
                                        </select>
                                    </div>
                                    <ul class="pagination" id="pagination-links">
                                        <!-- Pagination links will be inserted here by JavaScript -->
                                    </ul>
                                </div>
                                <!--end::Pagination-->
                            </div>
                        </div>
                        <!--end::Card-->
                    </div>
                </div>
                <!--end::Content-->
            </div>
        </div>
    </div>
    <!--end::Content-->
    @include('admin.elements.time_options_panel')

@endsection

@section('vendor-script')
    <script src="{{ asset('admin-assets/assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
@endsection

@section('scripts')
    <!--begin::Global Scripts-->
    <script>
        // Define product routes for JavaScript
        window.productRoutes = {
            data: '{{ url('/admin/products/ajax') }}',
            detail: '{{ url('/admin/products/:id/detail') }}'
        };
    </script>

    <!--begin::Base Table Manager-->
    <script src="{{ asset('admin-assets/js/base/table-manager.js') }}"></script>
    <!--end::Base Table Manager-->

    <!--begin::Global Filter-->
    <script src="{{ asset('admin-assets/globals/filter.js') }}"></script>
    <!--end::Global Filter-->

    <!--begin::Column Visibility-->
    <script src="{{ asset('admin-assets/globals/column-visibility.js') }}"></script>
    <!--end::Column Visibility-->

    <!--begin::Product Table Manager-->
    <script src="{{ asset('admin-assets/js/products/product-manager.js') }}?v={{ time() }}"></script>
    <!--end::Product Table Manager-->

    <!--begin::Page Initialization-->
    <script>
        console.log('Initializing Products Page...');

        // Pass permissions to JavaScript
        window.productPermissions = {
            canCreate: {{ auth()->user()->can('catalog.products.create') ? 'true' : 'false' }},
            canUpdate: {{ auth()->user()->can('catalog.products.update') ? 'true' : 'false' }},
            canDelete: {{ auth()->user()->can('catalog.products.delete') ? 'true' : 'false' }},
            canExport: {{ auth()->user()->can('catalog.products.export') ? 'true' : 'false' }},
        };

        // Load branch shops for filter
        function loadBranchShops() {
            $.ajax({
                url: '{{ url('/admin/filters/branch-shops') }}',
                method: 'GET',
                data: {
                    type: 'active'
                },
                success: function(response) {
                    if (response.success && response.data) {
                        const $branchShopSelect = $('#branch_shop_filter');
                        $branchShopSelect.empty();
                        $branchShopSelect.append('<option></option>');

                        response.data.forEach(function(branchShop) {
                            $branchShopSelect.append(
                                `<option value="${branchShop.id}">${branchShop.text}</option>`
                            );
                        });

                        // Reinitialize Select2
                        $branchShopSelect.select2({
                            placeholder: 'Chọn chi nhánh',
                            allowClear: true,
                            multiple: true
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Failed to load branch shops:', error);
                }
            });
        }

        // Load categories for filter
        function loadCategories() {
            $.ajax({
                url: '{{ url('/admin/filters/product-categories') }}',
                method: 'GET',
                data: {
                    format: 'tree'
                },
                success: function(response) {
                    if (response.success && response.data) {
                        const $categorySelect = $('#category_filter');
                        $categorySelect.empty();
                        $categorySelect.append('<option></option>');

                        response.data.forEach(function(category) {
                            $categorySelect.append(
                                `<option value="${category.id}">${category.text}</option>`
                            );
                        });

                        // Reinitialize Select2
                        $categorySelect.select2({
                            placeholder: 'Chọn danh mục',
                            allowClear: true,
                            multiple: true
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Failed to load categories:', error);
                }
            });
        }

        // Load creators for filter
        function loadCreators() {
            $.ajax({
                url: '{{ url('/admin/filters/creators') }}',
                method: 'GET',
                success: function(response) {
                    if (response.success && response.data) {
                        const $creatorSelect = $('#creator_filter');
                        $creatorSelect.empty();
                        $creatorSelect.append('<option></option>');

                        response.data.forEach(function(user) {
                            $creatorSelect.append(
                                `<option value="${user.id}">${user.name || user.username}</option>`
                            );
                        });

                        // Reinitialize Select2
                        $creatorSelect.select2({
                            placeholder: 'Chọn người tạo',
                            allowClear: true,
                            multiple: true
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Failed to load creators:', error);
                }
            });
        }

        // Initialize ProductTableManager
        const productManager = new ProductTableManager();
        productManager.init();

        // Initialize global filters
        if (typeof window.KTGlobalFilter !== 'undefined') {
            // Set load data callback
            window.KTGlobalFilter.setLoadDataCallback(function() {
                console.log('Filter changed, reloading products...');

                // Update time filter values from form
                const timeFilterDisplay = $('input[name="time_filter_display"]:checked').val() || 'this_month';
                const dateFrom = $('#date_from').val() || '';
                const dateTo = $('#date_to').val() || '';

                productManager.currentFilters.time_filter_display = timeFilterDisplay;
                productManager.currentFilters.date_from = dateFrom;
                productManager.currentFilters.date_to = dateTo;
                productManager.currentFilters.page = 1; // Reset to first page

                console.log('Time filter values:', {
                    time_filter_display: timeFilterDisplay,
                    date_from: dateFrom,
                    date_to: dateTo
                });

                productManager.loadData();
            });

            // Initialize time filter
            window.KTGlobalFilter.initTimeFilter('#kt_products_filter_form');
        }

        // Load branch shops, categories, and creators
        loadBranchShops();
        loadCategories();
        loadCreators();

        // Apply saved filter state to UI after dropdowns are loaded
        setTimeout(() => {
            applySavedFilterState();
        }, 500);

        // Setup filter change listeners
        $('#branch_shop_filter').on('change', function() {
            let selectedValues = $(this).val() || [];
            // Filter out empty values
            selectedValues = selectedValues.filter(val => val !== '' && val !== null && val !== undefined);
            console.log('Branch shop filter changed:', selectedValues);
            productManager.currentFilters.branch_shop_ids = selectedValues;
            productManager.currentFilters.page = 1;
            productManager.loadData();
        });

        $('#category_filter').on('change', function() {
            let selectedValues = $(this).val() || [];
            // Filter out empty values
            selectedValues = selectedValues.filter(val => val !== '' && val !== null && val !== undefined);
            console.log('Category filter changed:', selectedValues);
            productManager.currentFilters.category_ids = selectedValues;
            productManager.currentFilters.page = 1;
            productManager.loadData();
        });

        $('#creator_filter').on('change', function() {
            let selectedValues = $(this).val() || [];
            // Filter out empty values
            selectedValues = selectedValues.filter(val => val !== '' && val !== null && val !== undefined);
            console.log('Creator filter changed:', selectedValues);
            productManager.currentFilters.created_by = selectedValues;
            productManager.currentFilters.page = 1;
            productManager.loadData();
        });

        // Setup status filter checkboxes
        $('input[name="status[]"]').on('change', function() {
            const selectedStatuses = [];
            $('input[name="status[]"]:checked').each(function() {
                selectedStatuses.push($(this).val());
            });
            console.log('Status filter changed:', selectedStatuses);
            productManager.currentFilters.status = selectedStatuses.join(',');
            productManager.currentFilters.page = 1;
            productManager.loadData();
        });

        // Setup stock status filter checkboxes
        $('input[name="stock_status[]"]').on('change', function() {
            const selectedStockStatuses = [];
            $('input[name="stock_status[]"]:checked').each(function() {
                selectedStockStatuses.push($(this).val());
            });
            console.log('Stock status filter changed:', selectedStockStatuses);
            productManager.currentFilters.stock_status = selectedStockStatuses.join(',');
            productManager.currentFilters.page = 1;
            productManager.loadData();
        });

        // Setup price range filters
        $('#price_from, #price_to').on('change', function() {
            const priceFrom = $('#price_from').val() || '';
            const priceTo = $('#price_to').val() || '';
            console.log('Price range changed:', priceFrom, priceTo);
            productManager.currentFilters.price_from = priceFrom;
            productManager.currentFilters.price_to = priceTo;
            productManager.currentFilters.page = 1;
            productManager.loadData();
        });

        /**
         * Apply saved filter state to UI elements
         */
        function applySavedFilterState() {
            const savedState = window.KTGlobalFilter.loadFilterState('products');
            if (!savedState) return;

            console.log('Applying saved filter state to UI:', savedState);

            // Apply branch shop filter
            if (savedState.branch_shop_ids && savedState.branch_shop_ids.length > 0) {
                $('#branch_shop_filter').val(savedState.branch_shop_ids).trigger('change.select2');
            }

            // Apply category filter
            if (savedState.category_ids && savedState.category_ids.length > 0) {
                $('#category_filter').val(savedState.category_ids).trigger('change.select2');
            }

            // Apply creator filter
            if (savedState.created_by && savedState.created_by.length > 0) {
                $('#creator_filter').val(savedState.created_by).trigger('change.select2');
            }

            // Apply status checkboxes
            if (savedState.status) {
                const statuses = Array.isArray(savedState.status)
                    ? savedState.status
                    : savedState.status.split(',');
                $('input[name="status[]"]').prop('checked', false);
                statuses.forEach(status => {
                    if (status) {
                        $(`input[name="status[]"][value="${status}"]`).prop('checked', true);
                    }
                });
            }

            // Apply stock status checkboxes
            if (savedState.stock_status) {
                const stockStatuses = Array.isArray(savedState.stock_status)
                    ? savedState.stock_status
                    : savedState.stock_status.split(',');
                $('input[name="stock_status[]"]').prop('checked', false);
                stockStatuses.forEach(status => {
                    if (status) {
                        $(`input[name="stock_status[]"][value="${status}"]`).prop('checked', true);
                    }
                });
            }

            // Apply price range
            if (savedState.price_from) {
                $('#price_from').val(savedState.price_from);
            }
            if (savedState.price_to) {
                $('#price_to').val(savedState.price_to);
            }

            // Apply time filter
            if (savedState.time_filter_display) {
                $(`input[name="time_filter_display"][value="${savedState.time_filter_display}"]`).prop('checked', true);
            }
            if (savedState.date_from) {
                $('#date_from').val(savedState.date_from);
            }
            if (savedState.date_to) {
                $('#date_to').val(savedState.date_to);
            }

            console.log('Saved filter state applied to UI');
        }

        console.log('Products page initialized successfully');
    </script>
    <!--end::Page Initialization-->
@endsection
