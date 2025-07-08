"use strict";

// Class definition
var KTProductsList = function () {
    // Define shared variables
    var table = document.getElementById('kt_table_products');
    var datatable;
    var toolbarBase;
    var toolbarSelected;
    var selectedCount;

    // Private functions
    var initProductTable = function () {
        // Set date data order
        const tableRows = table.querySelectorAll('tbody tr');

        // Init datatable --- more info on datatables: https://datatables.net/manual/
        datatable = $(table).DataTable({
            "info": false,
            'order': [],
            'pageLength': 10,
            'processing': true,
            'serverSide': true,
            'ajax': {
                'url': '/admin/products/ajax/get-list',
                'type': 'GET',
                'data': function(d) {
                    // Add stock status filter to request
                    d.stock_status = $('#kt_product_stock_status').val();
                    return d;
                }
            },
            'columns': [
                {
                    'className': 'expand-control',
                    'orderable': false,
                    'data': null,
                    'defaultContent': '<i class="fas fa-info-circle text-primary cursor-pointer fs-4" title="Click row to view details"></i>',
                    'width': '20px'
                },
                { 'data': 'id', 'orderable': false, 'searchable': false },
                { 'data': 'product_name' },
                { 'data': 'sku' },
                { 'data': 'sale_price' },
                { 'data': 'stock_quantity' },
                { 'data': 'product_status' },
                { 'data': 'created_at' },
                { 'data': 'actions', 'orderable': false, 'searchable': false }
            ],
            'columnDefs': [
                {
                    'targets': 0,
                    'className': 'expand-control',
                    'orderable': false,
                    'data': null,
                    'defaultContent': '',
                    'width': '30px'
                },
                {
                    'targets': 1,
                    'orderable': false,
                    'render': function (data, type, full, meta) {
                        return `
                            <div class="form-check form-check-sm form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" value="${data}" />
                            </div>`;
                    }
                },
                {
                    'targets': 2,
                    'render': function (data, type, full, meta) {
                        var thumbnail = full.product_thumbnail ? full.product_thumbnail : '/admin-assets/assets/images/upload-thumbnail.png';
                        return `
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-45px me-5">
                                    <img src="${thumbnail}" alt="${data}" />
                                </div>
                                <div class="d-flex justify-content-start flex-column">
                                    <a href="${full.product_edit_url}" class="text-dark fw-bolder text-hover-primary fs-6">${data}</a>
                                    <span class="text-muted fw-bold text-muted d-block fs-7">${full.product_type || 'Simple'}</span>
                                </div>
                            </div>`;
                    }
                },
                {
                    'targets': 4,
                    'render': function (data, type, full, meta) {
                        return `<span class="fw-bolder">${parseFloat(data).toLocaleString()} VND</span>`;
                    }
                },
                {
                    'targets': 5,
                    'className': 'stock-quantity-column',
                    'render': function (data, type, full, meta) {
                        var stockStatus = full.stock_status || { status: 'unknown', label: 'Unknown', class: 'secondary' };
                        var quantity = data || 0;
                        var iconClass = '';
                        var statusClass = '';
                        var tooltip = '';

                        switch(stockStatus.status) {
                            case 'in_stock':
                                iconClass = 'fas fa-check-circle text-success';
                                statusClass = 'stock-status-in-stock';
                                tooltip = `In Stock: ${quantity} units available`;
                                break;
                            case 'low_stock':
                                iconClass = 'fas fa-exclamation-triangle text-warning';
                                statusClass = 'stock-status-low-stock';
                                tooltip = `Low Stock: Only ${quantity} units remaining`;
                                break;
                            case 'out_of_stock':
                                iconClass = 'fas fa-times-circle text-danger';
                                statusClass = 'stock-status-out-of-stock';
                                tooltip = `Out of Stock: ${quantity} units`;
                                break;
                            default:
                                iconClass = 'fas fa-question-circle';
                                statusClass = 'stock-status-loading';
                                tooltip = `Stock status unknown`;
                        }

                        return `
                            <span class="stock-status-badge ${statusClass} stock-status-tooltip" data-tooltip="${tooltip}">
                                <i class="${iconClass}"></i>
                                <span class="stock-quantity-number">${quantity}</span>
                                <span class="ms-1">${stockStatus.label}</span>
                            </span>
                        `;
                    }
                },
                {
                    'targets': 6,
                    'render': function (data, type, full, meta) {
                        return full.badge_status || `<span class="badge badge-light-secondary">${data}</span>`;
                    }
                },
                {
                    'targets': 7,
                    'render': function (data, type, full, meta) {
                        return moment(data).format('DD/MM/YYYY HH:mm');
                    }
                },
                {
                    'targets': -1,
                    'data': null,
                    'orderable': false,
                    'className': 'text-end',
                    'render': function (data, type, full, meta) {
                        return `
                            <div class="dropdown position-relative">
                                <button class="btn btn-light btn-active-light-primary btn-sm action-dropdown-btn"
                                        type="button"
                                        data-product-id="${full.id}"
                                        data-product-status="${full.product_status}">
                                    Actions
                                    <i class="fas fa-chevron-down ms-2 fs-7 dropdown-arrow"></i>
                                </button>
                                <div class="action-dropdown-menu" style="display: none;">
                                    <!-- Product Actions -->
                                    <div class="dropdown-header">
                                        <small class="text-muted text-uppercase fw-bold">Product Actions</small>
                                    </div>

                                    <a class="dropdown-item" href="${full.product_edit_url}">
                                        <i class="fas fa-edit text-primary me-2"></i>
                                        Edit Product
                                    </a>

                                    <a class="dropdown-item" href="#" onclick="duplicateProduct(${full.id}); return false;">
                                        <i class="fas fa-copy text-info me-2"></i>
                                        Duplicate
                                    </a>

                                    <a class="dropdown-item" href="#" onclick="manageStock(${full.id}); return false;">
                                        <i class="fas fa-boxes text-warning me-2"></i>
                                        Manage Stock
                                    </a>

                                    <a class="dropdown-item" href="#" onclick="viewProductHistory(${full.id}); return false;">
                                        <i class="fas fa-history text-secondary me-2"></i>
                                        View History
                                    </a>

                                    <div class="dropdown-divider"></div>

                                    <!-- Status Actions -->
                                    <div class="dropdown-header">
                                        <small class="text-muted text-uppercase fw-bold">Status Actions</small>
                                    </div>

                                    ${full.product_status === 'publish' ? `
                                        <a class="dropdown-item" href="#" onclick="changeProductStatus(${full.id}, 'draft'); return false;">
                                            <i class="fas fa-pause text-warning me-2"></i>
                                            Set to Draft
                                        </a>
                                    ` : `
                                        <a class="dropdown-item" href="#" onclick="changeProductStatus(${full.id}, 'publish'); return false;">
                                            <i class="fas fa-play text-success me-2"></i>
                                            Publish
                                        </a>
                                    `}

                                    <div class="dropdown-divider"></div>

                                    <!-- Danger Zone -->
                                    <div class="dropdown-header">
                                        <small class="text-muted text-uppercase fw-bold">Danger Zone</small>
                                    </div>

                                    <a class="dropdown-item text-danger" href="#" data-kt-products-table-filter="delete_row" data-id="${full.id}">
                                        <i class="fas fa-trash text-danger me-2"></i>
                                        Delete Product
                                    </a>
                                </div>
                            </div>
                        `;
                    },
                },
            ],
        });

        // Re-init functions on every table re-draw -- more info: https://datatables.net/reference/event/draw
        datatable.on('draw', function () {
            initToggleToolbar();
            handleDeleteRows();
            toggleToolbars();
            handleRowExpansion();
            initActionMenus();
        });
    }

    // Format product details for expansion
    function formatProductDetails(data) {
        const stockStatus = data.stock_status || { status: 'unknown', label: 'Unknown', class: 'secondary' };
        const stockQuantity = data.stock_quantity || 0;
        const productId = data.id;

        return `
            <div class="row product-details-expansion">
                <div class="col-md-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-6">
                            <!-- Header -->
                            <div class="d-flex align-items-center mb-6">
                                <div class="symbol symbol-60px me-4">
                                    <img src="${data.product_thumbnail || '/admin-assets/assets/images/upload-thumbnail.png'}"
                                         alt="${data.product_name}" class="symbol-label" />
                                </div>
                                <div class="flex-grow-1">
                                    <h4 class="text-gray-900 fw-bold mb-1">${data.product_name}</h4>
                                    <div class="text-muted fs-6">SKU: ${data.sku || 'N/A'}</div>
                                </div>
                                <div class="text-end">
                                    <span class="badge badge-light-primary fs-7 fw-bold">${data.product_type || 'Simple'}</span>
                                </div>
                            </div>

                            <!-- Tabs Navigation -->
                            <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x mb-5 fs-6" id="product-tabs-${productId}">
                                <li class="nav-item">
                                    <a class="nav-link active" data-bs-toggle="tab" href="#product-info-${productId}">
                                        <i class="fas fa-info-circle me-2"></i>Thông tin sản phẩm
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#product-inventory-${productId}">
                                        <i class="fas fa-boxes me-2"></i>Tồn kho
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#product-sales-${productId}">
                                        <i class="fas fa-shopping-cart me-2"></i>Liên kết bán hàng
                                    </a>
                                </li>
                            </ul>

                            <!-- Tabs Content -->
                            <div class="tab-content" id="product-tabs-content-${productId}">

                                <!-- Tab 1: Product Information -->
                                <div class="tab-pane fade show active" id="product-info-${productId}">
                                    <div class="row g-6">
                                        <!-- Basic Info -->
                                        <div class="col-lg-6">
                                            <div class="card bg-light-primary border-0">
                                                <div class="card-body p-4">
                                                    <h6 class="text-primary fw-bold mb-3">
                                                        <i class="fas fa-info-circle me-2"></i>Thông tin cơ bản
                                                    </h6>
                                                    <div class="row g-3">
                                                        <div class="col-6">
                                                            <div class="text-muted fs-7">Tên sản phẩm</div>
                                                            <div class="fw-bold fs-6">${data.product_name}</div>
                                                        </div>
                                                        <div class="col-6">
                                                            <div class="text-muted fs-7">SKU</div>
                                                            <div class="fw-bold fs-6">${data.sku || 'N/A'}</div>
                                                        </div>
                                                        <div class="col-6">
                                                            <div class="text-muted fs-7">Trạng thái</div>
                                                            <div>${data.badge_status || '<span class="badge badge-light-secondary">' + data.product_status + '</span>'}</div>
                                                        </div>
                                                        <div class="col-6">
                                                            <div class="text-muted fs-7">Ngày tạo</div>
                                                            <div class="fw-bold fs-6">${moment(data.created_at).format('DD/MM/YYYY')}</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Pricing Info -->
                                        <div class="col-lg-6">
                                            <div class="card bg-light-success border-0">
                                                <div class="card-body p-4">
                                                    <h6 class="text-success fw-bold mb-3">
                                                        <i class="fas fa-dollar-sign me-2"></i>Thông tin giá cả
                                                    </h6>
                                                    <div class="row g-3">
                                                        <div class="col-6">
                                                            <div class="text-muted fs-7">Giá bán</div>
                                                            <div class="fw-bold fs-5 text-success">${parseFloat(data.sale_price || 0).toLocaleString()} VND</div>
                                                        </div>
                                                        <div class="col-6">
                                                            <div class="text-muted fs-7">Giá vốn</div>
                                                            <div class="fw-bold fs-6">${parseFloat(data.cost_price || 0).toLocaleString()} VND</div>
                                                        </div>
                                                        <div class="col-6">
                                                            <div class="text-muted fs-7">Giá thường</div>
                                                            <div class="fw-bold fs-6">${parseFloat(data.regular_price || 0).toLocaleString()} VND</div>
                                                        </div>
                                                        <div class="col-6">
                                                            <div class="text-muted fs-7">Tỷ suất lợi nhuận</div>
                                                            <div class="fw-bold fs-6 text-success">
                                                                ${data.sale_price && data.cost_price ?
                                                                    Math.round(((data.sale_price - data.cost_price) / data.sale_price) * 100) + '%' :
                                                                    'N/A'
                                                                }
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Additional Info -->
                                        <div class="col-lg-12">
                                            <div class="card bg-light-info border-0">
                                                <div class="card-body p-4">
                                                    <h6 class="text-info fw-bold mb-3">
                                                        <i class="fas fa-cog me-2"></i>Thông số kỹ thuật
                                                    </h6>
                                                    <div class="row g-3">
                                                        <div class="col-3">
                                                            <div class="text-muted fs-7">Trọng lượng</div>
                                                            <div class="fw-bold fs-6">${data.weight || 'N/A'}</div>
                                                        </div>
                                                        <div class="col-3">
                                                            <div class="text-muted fs-7">Kích thước</div>
                                                            <div class="fw-bold fs-6">${data.dimensions || 'N/A'}</div>
                                                        </div>
                                                        <div class="col-3">
                                                            <div class="text-muted fs-7">Mã vạch</div>
                                                            <div class="fw-bold fs-6">${data.barcode || 'N/A'}</div>
                                                        </div>
                                                        <div class="col-3">
                                                            <div class="text-muted fs-7">Cập nhật lần cuối</div>
                                                            <div class="fw-bold fs-6">${moment(data.updated_at || data.created_at).format('DD/MM/YYYY HH:mm')}</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>


                                <!-- Tab 2: Inventory Information -->
                                <div class="tab-pane fade" id="product-inventory-${productId}">
                                    <div class="row g-6">
                                        <!-- Current Stock -->
                                        <div class="col-lg-6">
                                            <div class="card bg-light-warning border-0">
                                                <div class="card-body p-4">
                                                    <h6 class="text-warning fw-bold mb-3">
                                                        <i class="fas fa-boxes me-2"></i>Tồn kho hiện tại
                                                    </h6>
                                                    <div class="row g-3">
                                                        <div class="col-6">
                                                            <div class="text-muted fs-7">Số lượng</div>
                                                            <div class="fw-bold fs-3 text-warning">${stockQuantity}</div>
                                                            <div class="text-muted fs-8">đơn vị</div>
                                                        </div>
                                                        <div class="col-6">
                                                            <div class="text-muted fs-7">Trạng thái tồn kho</div>
                                                            <div>
                                                                <span class="stock-status-badge stock-status-${stockStatus.status}">
                                                                    <i class="fas fa-${stockStatus.status === 'in_stock' ? 'check-circle' :
                                                                        stockStatus.status === 'low_stock' ? 'exclamation-triangle' : 'times-circle'}"></i>
                                                                    ${stockStatus.label}
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div class="col-6">
                                                            <div class="text-muted fs-7">Điểm đặt hàng lại</div>
                                                            <div class="fw-bold fs-6">${data.reorder_point || 0} đơn vị</div>
                                                        </div>
                                                        <div class="col-6">
                                                            <div class="text-muted fs-7">Giá trị tồn kho</div>
                                                            <div class="fw-bold fs-6 text-warning">
                                                                ${(stockQuantity * (data.cost_price || 0)).toLocaleString()} VND
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Stock Actions -->
                                        <div class="col-lg-6">
                                            <div class="card bg-light-info border-0">
                                                <div class="card-body p-4">
                                                    <h6 class="text-info fw-bold mb-3">
                                                        <i class="fas fa-cogs me-2"></i>Thao tác tồn kho
                                                    </h6>
                                                    <div class="d-grid gap-3">
                                                        <button type="button" class="btn btn-light-primary btn-sm" onclick="adjustStock(${productId}, 'add')">
                                                            <i class="fas fa-plus me-2"></i>Thêm tồn kho
                                                        </button>
                                                        <button type="button" class="btn btn-light-warning btn-sm" onclick="adjustStock(${productId}, 'remove')">
                                                            <i class="fas fa-minus me-2"></i>Giảm tồn kho
                                                        </button>
                                                        <button type="button" class="btn btn-light-info btn-sm" onclick="viewStockHistory(${productId})">
                                                            <i class="fas fa-history me-2"></i>Lịch sử tồn kho
                                                        </button>
                                                        <button type="button" class="btn btn-light-success btn-sm" onclick="stockTake(${productId})">
                                                            <i class="fas fa-clipboard-check me-2"></i>Kiểm kê tồn kho
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>


                                <!-- Tab 3: Sales Links -->
                                <div class="tab-pane fade" id="product-sales-${productId}">
                                    <div class="row g-6">
                                        <!-- E-commerce Platforms -->
                                        <div class="col-lg-8">
                                            <div class="card bg-light-success border-0">
                                                <div class="card-body p-4">
                                                    <h6 class="text-success fw-bold mb-3">
                                                        <i class="fas fa-shopping-cart me-2"></i>Sàn thương mại điện tử
                                                    </h6>
                                                    <div class="row g-3">
                                                        <!-- Shopee -->
                                                        <div class="col-md-6">
                                                            <div class="d-flex align-items-center p-3 bg-white rounded border">
                                                                <div class="symbol symbol-40px me-3">
                                                                    <img src="/admin-assets/assets/images/platforms/shopee.png" alt="Shopee" class="symbol-label"
                                                                         onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA0MCA0MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjQwIiBoZWlnaHQ9IjQwIiByeD0iOCIgZmlsbD0iI0VFNEQyRCIvPgo8cGF0aCBkPSJNMTIgMTZIMjhWMjRIMTJWMTZaIiBmaWxsPSJ3aGl0ZSIvPgo8L3N2Zz4K'" />
                                                                </div>
                                                                <div class="flex-grow-1">
                                                                    <div class="fw-bold fs-6">Shopee</div>
                                                                    <div class="text-muted fs-7">Sàn giao dịch</div>
                                                                </div>
                                                                <div>
                                                                    <button type="button" class="btn btn-sm btn-light-primary" onclick="linkToShopee(${productId})">
                                                                        <i class="fas fa-link me-1"></i>Liên kết
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Lazada -->
                                                        <div class="col-md-6">
                                                            <div class="d-flex align-items-center p-3 bg-white rounded border">
                                                                <div class="symbol symbol-40px me-3">
                                                                    <img src="/admin-assets/assets/images/platforms/lazada.png" alt="Lazada" class="symbol-label"
                                                                         onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA0MCA0MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjQwIiBoZWlnaHQ9IjQwIiByeD0iOCIgZmlsbD0iI0ZGNjYwMCIvPgo8cGF0aCBkPSJNMTIgMTZIMjhWMjRIMTJWMTZaIiBmaWxsPSJ3aGl0ZSIvPgo8L3N2Zz4K'" />
                                                                </div>
                                                                <div class="flex-grow-1">
                                                                    <div class="fw-bold fs-6">Lazada</div>
                                                                    <div class="text-muted fs-7">Sàn giao dịch</div>
                                                                </div>
                                                                <div>
                                                                    <button type="button" class="btn btn-sm btn-light-warning" onclick="linkToLazada(${productId})">
                                                                        <i class="fas fa-link me-1"></i>Liên kết
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Tiki -->
                                                        <div class="col-md-6">
                                                            <div class="d-flex align-items-center p-3 bg-white rounded border">
                                                                <div class="symbol symbol-40px me-3">
                                                                    <img src="/admin-assets/assets/images/platforms/tiki.png" alt="Tiki" class="symbol-label"
                                                                         onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA0MCA0MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjQwIiBoZWlnaHQ9IjQwIiByeD0iOCIgZmlsbD0iIzAwNzNFNiIvPgo8cGF0aCBkPSJNMTIgMTZIMjhWMjRIMTJWMTZaIiBmaWxsPSJ3aGl0ZSIvPgo8L3N2Zz4K'" />
                                                                </div>
                                                                <div class="flex-grow-1">
                                                                    <div class="fw-bold fs-6">Tiki</div>
                                                                    <div class="text-muted fs-7">Sàn giao dịch</div>
                                                                </div>
                                                                <div>
                                                                    <button type="button" class="btn btn-sm btn-light-info" onclick="linkToTiki(${productId})">
                                                                        <i class="fas fa-link me-1"></i>Liên kết
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <!-- Sendo -->
                                                        <div class="col-md-6">
                                                            <div class="d-flex align-items-center p-3 bg-white rounded border">
                                                                <div class="symbol symbol-40px me-3">
                                                                    <img src="/admin-assets/assets/images/platforms/sendo.png" alt="Sendo" class="symbol-label"
                                                                         onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA0MCA0MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjQwIiBoZWlnaHQ9IjQwIiByeD0iOCIgZmlsbD0iI0VEMzc1NyIvPgo8cGF0aCBkPSJNMTIgMTZIMjhWMjRIMTJWMTZaIiBmaWxsPSJ3aGl0ZSIvPgo8L3N2Zz4K'" />
                                                                </div>
                                                                <div class="flex-grow-1">
                                                                    <div class="fw-bold fs-6">Sendo</div>
                                                                    <div class="text-muted fs-7">Sàn giao dịch</div>
                                                                </div>
                                                                <div>
                                                                    <button type="button" class="btn btn-sm btn-light-danger" onclick="linkToSendo(${productId})">
                                                                        <i class="fas fa-link me-1"></i>Liên kết
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Sales Statistics -->
                                        <div class="col-lg-4">
                                            <div class="card bg-light-primary border-0">
                                                <div class="card-body p-4">
                                                    <h6 class="text-primary fw-bold mb-3">
                                                        <i class="fas fa-chart-line me-2"></i>Thống kê bán hàng
                                                    </h6>
                                                    <div class="d-grid gap-3">
                                                        <div class="text-center">
                                                            <div class="text-muted fs-7">Tổng bán hàng</div>
                                                            <div class="fw-bold fs-4 text-primary">${data.total_sales || 0}</div>
                                                            <div class="text-muted fs-8">đơn vị đã bán</div>
                                                        </div>
                                                        <div class="text-center">
                                                            <div class="text-muted fs-7">Doanh thu</div>
                                                            <div class="fw-bold fs-5 text-success">${((data.total_sales || 0) * (data.sale_price || 0)).toLocaleString()} VND</div>
                                                        </div>
                                                        <div class="text-center">
                                                            <div class="text-muted fs-7">Lần bán cuối</div>
                                                            <div class="fw-bold fs-6">${data.last_sale_date ? moment(data.last_sale_date).format('DD/MM/YYYY') : 'N/A'}</div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-flex justify-content-end mt-6">
                                <a href="${data.product_edit_url}" class="btn btn-primary btn-sm me-3">
                                    <i class="fas fa-edit me-2"></i>Chỉnh sửa sản phẩm
                                </a>
                                <button type="button" class="btn btn-light-primary btn-sm me-3" onclick="viewProductHistory(${productId})">
                                    <i class="fas fa-history me-2"></i>Xem lịch sử
                                </button>
                                <button type="button" class="btn btn-light-success btn-sm" onclick="manageStock(${productId})">
                                    <i class="fas fa-boxes me-2"></i>Quản lý tồn kho
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    // Initialize action menus
    var initActionMenus = function() {
        // Handle custom action dropdown buttons
        const actionButtons = document.querySelectorAll('.action-dropdown-btn');

        actionButtons.forEach(function(button) {
            // Remove existing event listeners to prevent duplicates
            button.removeEventListener('click', handleActionDropdownClick);

            // Add click event listener
            button.addEventListener('click', handleActionDropdownClick);
        });

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.dropdown')) {
                closeAllActionDropdowns();
            }
        });
    };

    // Handle action dropdown click
    function handleActionDropdownClick(e) {
        e.preventDefault();
        e.stopPropagation();

        const button = e.currentTarget;
        const dropdown = button.closest('.dropdown');
        const menu = dropdown.querySelector('.action-dropdown-menu');
        const arrow = button.querySelector('.dropdown-arrow');

        // Close all other dropdowns first
        closeAllActionDropdowns();

        // Toggle current dropdown
        const isVisible = menu.style.display === 'block';

        if (!isVisible) {
            // Show dropdown
            menu.style.display = 'block';
            button.classList.add('active');
            arrow.style.transform = 'rotate(180deg)';

            // Position dropdown
            positionDropdown(button, menu);

            // Add animation
            menu.style.opacity = '0';
            menu.style.transform = 'translateY(-10px)';

            setTimeout(() => {
                menu.style.transition = 'all 0.2s ease';
                menu.style.opacity = '1';
                menu.style.transform = 'translateY(0)';
            }, 10);
        } else {
            // Hide dropdown
            hideDropdown(button, menu, arrow);
        }
    }

    // Position dropdown relative to button
    function positionDropdown(button, menu) {
        const buttonRect = button.getBoundingClientRect();
        const menuWidth = 200; // Fixed width for consistency

        // Calculate position
        let left = buttonRect.right - menuWidth;
        let top = buttonRect.bottom + 5;

        // Adjust if dropdown would go off-screen
        if (left < 10) {
            left = 10;
        }

        if (top + menu.offsetHeight > window.innerHeight - 20) {
            top = buttonRect.top - menu.offsetHeight - 5;
        }

        // Apply positioning
        menu.style.position = 'fixed';
        menu.style.left = left + 'px';
        menu.style.top = top + 'px';
        menu.style.zIndex = '1050';
        menu.style.minWidth = menuWidth + 'px';
    }

    // Hide dropdown with animation
    function hideDropdown(button, menu, arrow) {
        menu.style.transition = 'all 0.2s ease';
        menu.style.opacity = '0';
        menu.style.transform = 'translateY(-10px)';

        setTimeout(() => {
            menu.style.display = 'none';
            button.classList.remove('active');
            arrow.style.transform = 'rotate(0deg)';
        }, 200);
    }

    // Close all action dropdowns
    function closeAllActionDropdowns() {
        const openMenus = document.querySelectorAll('.action-dropdown-menu[style*="display: block"]');

        openMenus.forEach(function(menu) {
            const dropdown = menu.closest('.dropdown');
            const button = dropdown.querySelector('.action-dropdown-btn');
            const arrow = button.querySelector('.dropdown-arrow');

            hideDropdown(button, menu, arrow);
        });
    }

    // Handle row expansion
    var handleRowExpansion = function() {
        // Add event listener to entire row (except action column)
        $('#kt_table_products tbody').off('click', 'tr').on('click', 'tr', function (e) {
            // Don't trigger if clicking on action buttons or checkboxes
            if ($(e.target).closest('td:last-child').length > 0 ||
                $(e.target).is('input[type="checkbox"]') ||
                $(e.target).closest('.dropdown').length > 0 ||
                $(e.target).is('button') ||
                $(e.target).is('a')) {
                return;
            }

            var tr = $(this);
            var row = datatable.row(tr);
            var icon = tr.find('td.expand-control i');

            if (row.child.isShown()) {
                // This row is already open - close it
                row.child.hide();
                tr.removeClass('shown');
                icon.removeClass('fa-minus-circle text-danger').addClass('fa-info-circle text-primary');
            } else {
                // Close all other open rows first
                $('#kt_table_products tbody tr.shown').each(function() {
                    var otherRow = datatable.row($(this));
                    var otherIcon = $(this).find('td.expand-control i');
                    otherRow.child.hide();
                    $(this).removeClass('shown');
                    otherIcon.removeClass('fa-minus-circle text-danger').addClass('fa-info-circle text-primary');
                });

                // Open this row
                row.child(formatProductDetails(row.data())).show();
                tr.addClass('shown');
                icon.removeClass('fa-info-circle text-primary').addClass('fa-minus-circle text-danger');

                // Add animation and scroll to view
                row.child().hide().fadeIn(300);

                // Scroll to the expanded row
                setTimeout(function() {
                    $('html, body').animate({
                        scrollTop: tr.offset().top - 100
                    }, 500);
                }, 100);
            }
        });
    };

    // Search Datatable --- official docs reference: https://datatables.net/reference/api/search()
    var handleSearchDatatable = function () {
        const filterSearch = document.querySelector('[data-kt-products-table-filter="search"]');
        filterSearch.addEventListener('keyup', function (e) {
            datatable.search(e.target.value).draw();
        });
    }

    // Reset individual filter helper function
    var resetFilter = function(selector) {
        const filterElement = document.querySelector(selector);
        if (filterElement) {
            filterElement.value = '';
            if ($(filterElement).hasClass('select2-hidden-accessible')) {
                $(filterElement).val('').trigger('change');
            }
        }
    };

    // Reset all filters helper function
    var resetAllFilters = function() {
        // Get all filter elements
        const filterStatus = document.querySelector('[data-kt-products-table-filter="status"]');
        const filterStockStatus = document.querySelector('[data-kt-products-table-filter="stock_status"]');
        const searchInput = document.querySelector('[data-kt-products-table-filter="search"]');

        // Reset product status filter
        if (filterStatus) {
            filterStatus.value = '';
            if ($(filterStatus).hasClass('select2-hidden-accessible')) {
                $(filterStatus).val('').trigger('change');
            }
        }

        // Reset stock status filter
        if (filterStockStatus) {
            filterStockStatus.value = '';
            if ($(filterStockStatus).hasClass('select2-hidden-accessible')) {
                $(filterStockStatus).val('').trigger('change');
            }
        }

        // Reset search input
        if (searchInput) {
            searchInput.value = '';
        }

        // Clear all DataTable filters and redraw
        datatable.search('').columns().search('').draw();

        // Show visual feedback
        if (typeof toastr !== 'undefined') {
            toastr.success('All filters have been reset', 'Filters Reset');
        } else if (typeof Swal !== 'undefined') {
            Swal.fire({
                text: "All filters have been reset successfully!",
                icon: "success",
                buttonsStyling: false,
                confirmButtonText: "Ok, got it!",
                customClass: {
                    confirmButton: "btn fw-bold btn-primary",
                },
                timer: 2000,
                timerProgressBar: true
            });
        } else {
            // Fallback: Simple visual feedback with button animation
            const resetBtn = document.querySelector('[data-kt-products-table-filter="reset"]');
            if (resetBtn) {
                const originalText = resetBtn.innerHTML;
                resetBtn.innerHTML = `
                    <span class="svg-icon svg-icon-2">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M9.89557 13.4982L7.79487 11.2651C7.26967 10.7068 6.38251 10.7068 5.85731 11.2651C5.37559 11.7772 5.37559 12.5757 5.85731 13.0878L9.74989 17.2257C10.1448 17.6455 10.8118 17.6455 11.2066 17.2257L18.1427 9.85252C18.6244 9.34044 18.6244 8.54191 18.1427 8.02984C17.6175 7.47154 16.7303 7.47154 16.2051 8.02984L11.061 13.4982C10.7451 13.8308 10.2115 13.8308 9.89557 13.4982Z" fill="currentColor"/>
                        </svg>
                    </span>Reset Complete
                `;
                resetBtn.classList.add('btn-success');
                resetBtn.classList.remove('btn-light-secondary');

                setTimeout(() => {
                    resetBtn.innerHTML = originalText;
                    resetBtn.classList.remove('btn-success');
                    resetBtn.classList.add('btn-light-secondary');
                }, 1500);
            }
        }
    };

    // Filter Datatable
    var handleFilterDatatable = function () {
        // Select filter options
        const filterStatus = document.querySelector('[data-kt-products-table-filter="status"]');
        const filterStockStatus = document.querySelector('[data-kt-products-table-filter="stock_status"]');
        const filterButton = document.querySelector('[data-kt-products-table-filter="filter"]');
        const resetButton = document.querySelector('[data-kt-products-table-filter="reset"]');

        // Filter datatable on submit
        filterButton&&filterButton.addEventListener('click', function () {
            
             // Apply stock status filter
            var statusStockFilterValue = filterStockStatus.value;
            if (statusStockFilterValue === 'all') {
                statusStockFilterValue = '';
            }
            
            datatable.column(4).search(statusStockFilterValue);

            // Apply product status filter
            var statusFilterValue = filterStatus.value;
            if (statusFilterValue === 'all') {
                statusFilterValue = '';
            }

            datatable.column(5).search(statusFilterValue);

            // Reload datatable with stock status filter (handled in ajax data function)
            datatable.draw();
        });

        // Reset datatable
        resetButton && resetButton.addEventListener('click', function () {
            resetAllFilters();
        });

        // Add keyboard shortcut for reset (Ctrl+R or Cmd+R)
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'r' && e.target.closest('.dataTables_wrapper')) {
                e.preventDefault();
                resetAllFilters();
            }
        });

        // Add double-click reset for filter dropdowns
        filterStatus && filterStatus.addEventListener('dblclick', function() {
            resetFilter('[data-kt-products-table-filter="status"]');
            datatable.column(5).search('').draw();
        });

        filterStockStatus && filterStockStatus.addEventListener('dblclick', function() {
            resetFilter('[data-kt-products-table-filter="stock_status"]');
            datatable.draw();
        });
    }

    // Delete product
    var handleDeleteRows = function () {
        // Select all delete buttons
        const deleteButtons = table.querySelectorAll('[data-kt-products-table-filter="delete_row"]');

        deleteButtons.forEach(d => {
            // Delete button on click
            d.addEventListener('click', function (e) {
                e.preventDefault();

                // Select parent row
                const parent = e.target.closest('tr');

                // Get product name
                const productName = parent.querySelectorAll('td')[1].innerText;
                const productId = this.getAttribute('data-id');

                // SweetAlert2 pop up --- official docs reference: https://sweetalert2.github.io/
                Swal.fire({
                    text: "Are you sure you want to delete " + productName + "?",
                    icon: "warning",
                    showCancelButton: true,
                    buttonsStyling: false,
                    confirmButtonText: "Yes, delete!",
                    cancelButtonText: "No, cancel",
                    customClass: {
                        confirmButton: "btn fw-bold btn-danger",
                        cancelButton: "btn fw-bold btn-active-light-primary"
                    }
                }).then(function (result) {
                    if (result.value) {
                        // Delete request
                        fetch(`/admin/products/delete/${productId}`, {
                            method: 'GET',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    text: "You have deleted " + productName + "!.",
                                    icon: "success",
                                    buttonsStyling: false,
                                    confirmButtonText: "Ok, got it!",
                                    customClass: {
                                        confirmButton: "btn fw-bold btn-primary",
                                    }
                                }).then(function () {
                                    // Remove current row
                                    datatable.row($(parent)).remove().draw();
                                });
                            } else {
                                Swal.fire({
                                    text: data.message || "Sorry, looks like there are some errors detected, please try again.",
                                    icon: "error",
                                    buttonsStyling: false,
                                    confirmButtonText: "Ok, got it!",
                                    customClass: {
                                        confirmButton: "btn fw-bold btn-primary",
                                    }
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire({
                                text: "Sorry, looks like there are some errors detected, please try again.",
                                icon: "error",
                                buttonsStyling: false,
                                confirmButtonText: "Ok, got it!",
                                customClass: {
                                    confirmButton: "btn fw-bold btn-primary",
                                }
                            });
                        });
                    }
                });
            })
        });
    }

    // Init toggle toolbar
    var initToggleToolbar = function () {
        // Toggle selected action toolbar
        // Select all checkboxes
        const checkboxes = table.querySelectorAll('[type="checkbox"]');

        // Select elements
        toolbarBase = document.querySelector('[data-kt-products-table-toolbar="base"]');
        toolbarSelected = document.querySelector('[data-kt-products-table-toolbar="selected"]');
        selectedCount = document.querySelector('[data-kt-products-table-select="selected_count"]');
        const deleteSelected = document.querySelector('[data-kt-products-table-select="delete_selected"]');

        // Toggle delete selected toolbar
        checkboxes.forEach(c => {
            // Checkbox on click event
            c.addEventListener('click', function () {
                setTimeout(function () {
                    toggleToolbars();
                }, 50);
            });
        });

        // Deleted selected rows
        deleteSelected.addEventListener('click', function () {
            // SweetAlert2 pop up --- official docs reference: https://sweetalert2.github.io/
            Swal.fire({
                text: "Are you sure you want to delete selected products?",
                icon: "warning",
                showCancelButton: true,
                buttonsStyling: false,
                confirmButtonText: "Yes, delete!",
                cancelButtonText: "No, cancel",
                customClass: {
                    confirmButton: "btn fw-bold btn-danger",
                    cancelButton: "btn fw-bold btn-active-light-primary"
                }
            }).then(function (result) {
                if (result.value) {
                    // Collect selected IDs
                    const selectedIds = [];
                    const selectedCheckboxes = table.querySelectorAll('tbody [type="checkbox"]:checked');
                    
                    selectedCheckboxes.forEach(checkbox => {
                        selectedIds.push(checkbox.value);
                    });

                    if (selectedIds.length > 0) {
                        // Delete request
                        fetch('/admin/products/delete', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({
                                ids: selectedIds,
                                total: selectedIds.length
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    text: "You have deleted selected products!",
                                    icon: "success",
                                    buttonsStyling: false,
                                    confirmButtonText: "Ok, got it!",
                                    customClass: {
                                        confirmButton: "btn fw-bold btn-primary",
                                    }
                                }).then(function () {
                                    // Reload datatable
                                    datatable.draw();
                                });

                                // Remove header checked box
                                const headerCheckbox = table.querySelectorAll('[type="checkbox"]')[0];
                                headerCheckbox.checked = false;
                            } else {
                                Swal.fire({
                                    text: data.message || "Sorry, looks like there are some errors detected, please try again.",
                                    icon: "error",
                                    buttonsStyling: false,
                                    confirmButtonText: "Ok, got it!",
                                    customClass: {
                                        confirmButton: "btn fw-bold btn-primary",
                                    }
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire({
                                text: "Sorry, looks like there are some errors detected, please try again.",
                                icon: "error",
                                buttonsStyling: false,
                                confirmButtonText: "Ok, got it!",
                                customClass: {
                                    confirmButton: "btn fw-bold btn-primary",
                                }
                            });
                        });
                    }
                }
            });
        });
    }

    // Toggle toolbars
    var toggleToolbars = function () {
        // Select refreshed checkbox DOM elements 
        const allCheckboxes = table.querySelectorAll('tbody [type="checkbox"]');

        // Detect checkboxes state & count
        let checkedState = false;
        let count = 0;

        // Count checked boxes
        allCheckboxes.forEach(c => {
            if (c.checked) {
                checkedState = true;
                count++;
            }
        });

        // Toggle toolbars
        if (checkedState) {
            selectedCount.innerHTML = count;
            toolbarBase.classList.add('d-none');
            toolbarSelected.classList.remove('d-none');
        } else {
            toolbarBase.classList.remove('d-none');
            toolbarSelected.classList.add('d-none');
        }
    }

    // Helper functions for action buttons
    window.viewProductHistory = function(productId) {
        // Show loading
        Swal.fire({
            title: 'Loading...',
            text: 'Fetching product history',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Fetch product history (you can implement this endpoint)
        fetch(`/admin/products/${productId}/history`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show history in modal or new page
                    Swal.fire({
                        title: 'Product History',
                        html: formatProductHistory(data.history),
                        width: '80%',
                        showCloseButton: true,
                        showConfirmButton: false,
                        customClass: {
                            container: 'product-history-modal'
                        }
                    });
                } else {
                    Swal.fire('Error', 'Could not load product history', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error', 'Could not load product history', 'error');
            });
    };

    window.manageStock = function(productId) {
        // Redirect to stock management page or show modal
        window.location.href = `/admin/inventory?product_id=${productId}`;
    };

    // Duplicate product function
    window.duplicateProduct = function(productId) {
        Swal.fire({
            title: 'Duplicate Product',
            text: 'Are you sure you want to duplicate this product?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, duplicate it!',
            cancelButtonText: 'Cancel',
            customClass: {
                confirmButton: 'btn btn-primary',
                cancelButton: 'btn btn-light'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading
                Swal.fire({
                    title: 'Duplicating...',
                    text: 'Please wait while we duplicate the product',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Make duplicate request
                fetch(`/admin/products/${productId}/duplicate`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'Success!',
                            text: 'Product has been duplicated successfully',
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            // Refresh table
                            datatable.draw();
                        });
                    } else {
                        Swal.fire('Error', data.message || 'Failed to duplicate product', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Error', 'An error occurred while duplicating the product', 'error');
                });
            }
        });
    };

    // Change product status function
    window.changeProductStatus = function(productId, newStatus) {
        const statusLabels = {
            'publish': 'Published',
            'draft': 'Draft',
            'pending': 'Pending',
            'trash': 'Trash'
        };

        Swal.fire({
            title: 'Change Status',
            text: `Are you sure you want to change the product status to ${statusLabels[newStatus]}?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, change it!',
            cancelButtonText: 'Cancel',
            customClass: {
                confirmButton: 'btn btn-primary',
                cancelButton: 'btn btn-light'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Make status change request
                fetch(`/admin/products/${productId}/status`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        status: newStatus
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'Success!',
                            text: `Product status changed to ${statusLabels[newStatus]}`,
                            icon: 'success',
                            timer: 2000,
                            timerProgressBar: true
                        });

                        // Refresh table
                        datatable.draw();
                    } else {
                        Swal.fire('Error', data.message || 'Failed to change product status', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire('Error', 'An error occurred while changing the product status', 'error');
                });
            }
        });
    };

    function formatProductHistory(history) {
        if (!history || history.length === 0) {
            return '<p class="text-muted">No history available for this product.</p>';
        }

        let html = '<div class="timeline timeline-border-dashed">';

        history.forEach((item, index) => {
            const iconClass = getHistoryIcon(item.type);
            const colorClass = getHistoryColor(item.type);

            html += `
                <div class="timeline-item">
                    <div class="timeline-line w-40px"></div>
                    <div class="timeline-icon symbol symbol-circle symbol-40px ${colorClass}">
                        <div class="symbol-label">
                            <i class="${iconClass} text-white fs-4"></i>
                        </div>
                    </div>
                    <div class="timeline-content mb-10 mt-n1">
                        <div class="pe-3 mb-5">
                            <div class="fs-5 fw-bold mb-2">${item.title}</div>
                            <div class="d-flex align-items-center mt-1 fs-6">
                                <div class="text-muted me-2 fs-7">${moment(item.created_at).format('DD/MM/YYYY HH:mm')}</div>
                            </div>
                        </div>
                        <div class="overflow-auto pb-5">
                            <div class="d-flex align-items-center border border-dashed border-gray-300 rounded min-w-750px px-7 py-3 mb-5">
                                ${item.description}
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });

        html += '</div>';
        return html;
    }

    function getHistoryIcon(type) {
        const icons = {
            'created': 'fas fa-plus',
            'updated': 'fas fa-edit',
            'stock_change': 'fas fa-boxes',
            'price_change': 'fas fa-dollar-sign',
            'status_change': 'fas fa-toggle-on'
        };
        return icons[type] || 'fas fa-info';
    }

    function getHistoryColor(type) {
        const colors = {
            'created': 'bg-success',
            'updated': 'bg-primary',
            'stock_change': 'bg-warning',
            'price_change': 'bg-info',
            'status_change': 'bg-secondary'
        };
        return colors[type] || 'bg-gray-500';
    }

    // Public methods
    return {
        init: function () {
            if (!table) {
                return;
            }

            initProductTable();
            initToggleToolbar();
            handleSearchDatatable();
            handleFilterDatatable();
            handleDeleteRows();
            handleRowExpansion();
            initActionMenus();
   
        }
    }
}();

// Global functions for product actions
window.adjustStock = function(productId, type) {
    console.log(`Adjust stock for product ${productId}, type: ${type}`);
    // TODO: Implement stock adjustment modal
    Swal.fire({
        title: type === 'add' ? 'Add Stock' : 'Remove Stock',
        text: `This will ${type} stock for product ID: ${productId}`,
        icon: 'info',
        confirmButtonText: 'OK'
    });
};

window.viewStockHistory = function(productId) {
    console.log(`View stock history for product ${productId}`);
    // TODO: Implement stock history modal
    Swal.fire({
        title: 'Stock History',
        text: `Showing stock history for product ID: ${productId}`,
        icon: 'info',
        confirmButtonText: 'OK'
    });
};

window.stockTake = function(productId) {
    console.log(`Stock take for product ${productId}`);
    // TODO: Implement stock take modal
    Swal.fire({
        title: 'Stock Take',
        text: `Performing stock take for product ID: ${productId}`,
        icon: 'info',
        confirmButtonText: 'OK'
    });
};

window.linkToShopee = function(productId) {
    console.log(`Link product ${productId} to Shopee`);
    // TODO: Implement Shopee integration
    Swal.fire({
        title: 'Link to Shopee',
        html: `
            <div class="text-start">
                <p>Connect this product to Shopee marketplace:</p>
                <div class="form-group mb-3">
                    <label class="form-label">Shopee Product URL:</label>
                    <input type="url" class="form-control" placeholder="https://shopee.vn/product/..." />
                </div>
                <div class="form-group mb-3">
                    <label class="form-label">Shopee Shop ID:</label>
                    <input type="text" class="form-control" placeholder="Enter your Shopee shop ID" />
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Link Product',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#ee4d2d'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire('Success!', 'Product linked to Shopee successfully.', 'success');
        }
    });
};

window.linkToLazada = function(productId) {
    console.log(`Link product ${productId} to Lazada`);
    // TODO: Implement Lazada integration
    Swal.fire({
        title: 'Link to Lazada',
        text: `Linking product ID: ${productId} to Lazada marketplace`,
        icon: 'info',
        confirmButtonText: 'OK'
    });
};

window.linkToTiki = function(productId) {
    console.log(`Link product ${productId} to Tiki`);
    // TODO: Implement Tiki integration
    Swal.fire({
        title: 'Link to Tiki',
        text: `Linking product ID: ${productId} to Tiki marketplace`,
        icon: 'info',
        confirmButtonText: 'OK'
    });
};

window.linkToSendo = function(productId) {
    console.log(`Link product ${productId} to Sendo`);
    // TODO: Implement Sendo integration
    Swal.fire({
        title: 'Link to Sendo',
        text: `Linking product ID: ${productId} to Sendo marketplace`,
        icon: 'info',
        confirmButtonText: 'OK'
    });
};

// On document ready
KTUtil.onDOMContentLoaded(function () {
    KTProductsList.init();
});
