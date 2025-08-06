@extends('admin.layouts.app')

@section('title', 'Quản lý đơn hàng')

@section('content')
<div class="d-flex flex-column flex-column-fluid">
    <!--begin::Toolbar-->
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
        <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">Quản lý đơn hàng</h1>
                <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('admin.dashboard') }}" class="text-muted text-hover-primary">Trang chủ</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-400 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-muted">Quản lý đơn hàng</li>
                </ul>
            </div>
        </div>
    </div>
    <!--end::Toolbar-->

    <!--begin::Content-->
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-xxl">
            <div class="d-flex flex-column flex-lg-row">
                <!--begin::Sidebar-->
                <div class="flex-column flex-lg-row-auto w-100 w-lg-300px w-xl-400px mb-10 mb-lg-0">
                    <!--begin::Card-->
                    <div class="card card-flush">
                        <!--begin::Card header-->
                        <div class="card-header">
                            <div class="card-title">
                                <h2>Bộ lọc</h2>
                            </div>
                        </div>
                        <!--end::Card header-->

                        <!--begin::Card body-->
                        <div class="card-body pt-0">
                            <!--begin::Time Filter-->
                            <div class="mb-10" id="time-filter-container">
                                <h3 class="form-label fw-bold">Thời gian</h3>
                                <div class="separator separator-dashed my-5"></div>
                                <div class="d-flex flex-column">
                                    <label class="form-check form-check-custom form-check-solid mb-5">
                                        <input class="form-check-input" type="radio" name="time_filter" value="this_month" checked />
                                        <span class="form-check-label text-gray-600">
                                            Tháng này
                                            <span class="badge badge-light-primary ms-2" id="this-month-count">0</span>
                                        </span>
                                    </label>
                                    <label class="form-check form-check-custom form-check-solid">
                                        <input class="form-check-input" type="radio" name="time_filter" value="custom" />
                                        <span class="form-check-label text-gray-600">
                                            Tùy chỉnh
                                            <span class="badge badge-light-info ms-2" id="custom-count">0</span>
                                        </span>
                                    </label>
                                </div>
                            </div>
                            <!--end::Time Filter-->

                            <!--begin::Status Filter-->
                            <div class="mb-10" id="status-filter-container">
                                <h3 class="form-label fw-bold">Trạng thái đơn hàng</h3>
                                <div class="separator separator-dashed my-5"></div>
                                <div class="d-flex flex-column">
                                    <label class="form-check form-check-custom form-check-solid mb-3">
                                        <input class="form-check-input" type="checkbox" name="status[]" value="pending" checked />
                                        <span class="form-check-label text-gray-600">Chờ xử lý</span>
                                    </label>
                                    <label class="form-check form-check-custom form-check-solid mb-3">
                                        <input class="form-check-input" type="checkbox" name="status[]" value="processing" checked />
                                        <span class="form-check-label text-gray-600">Đang xử lý</span>
                                    </label>
                                    <label class="form-check form-check-custom form-check-solid mb-3">
                                        <input class="form-check-input" type="checkbox" name="status[]" value="shipped" />
                                        <span class="form-check-label text-gray-600">Đã gửi hàng</span>
                                    </label>
                                    <label class="form-check form-check-custom form-check-solid mb-3">
                                        <input class="form-check-input" type="checkbox" name="status[]" value="delivered" />
                                        <span class="form-check-label text-gray-600">Đã giao hàng</span>
                                    </label>
                                    <label class="form-check form-check-custom form-check-solid mb-3">
                                        <input class="form-check-input" type="checkbox" name="status[]" value="completed" />
                                        <span class="form-check-label text-gray-600">Hoàn thành</span>
                                    </label>
                                    <label class="form-check form-check-custom form-check-solid mb-3">
                                        <input class="form-check-input" type="checkbox" name="status[]" value="cancelled" />
                                        <span class="form-check-label text-gray-600">Đã hủy</span>
                                    </label>
                                </div>
                            </div>
                            <!--end::Status Filter-->

                            <!--begin::Creator Filter-->
                            <div class="mb-10" id="creator-filter-container">
                                <h3 class="form-label fw-bold">Người tạo</h3>
                                <div class="separator separator-dashed my-5"></div>
                                <select class="form-select form-select-solid" data-control="select2" data-placeholder="Chọn người tạo" data-allow-clear="true" name="creator_id" id="creator-filter">
                                    <option></option>
                                </select>
                            </div>
                            <!--end::Creator Filter-->

                            <!--begin::Seller Filter-->
                            <div class="mb-10" id="seller-filter-container">
                                <h3 class="form-label fw-bold">Người bán</h3>
                                <div class="separator separator-dashed my-5"></div>
                                <select class="form-select form-select-solid" data-control="select2" data-placeholder="Chọn người bán" data-allow-clear="true" name="seller_id" id="seller-filter">
                                    <option></option>
                                </select>
                            </div>
                            <!--end::Seller Filter-->

                            <!--begin::Payment Status Filter-->
                            <div class="mb-10" id="payment-status-filter-container">
                                <h3 class="form-label fw-bold">Trạng thái thanh toán</h3>
                                <div class="separator separator-dashed my-5"></div>
                                <div class="d-flex flex-column">
                                    <label class="form-check form-check-custom form-check-solid mb-3">
                                        <input class="form-check-input" type="checkbox" name="payment_status[]" value="unpaid" />
                                        <span class="form-check-label text-gray-600">Chưa thanh toán</span>
                                    </label>
                                    <label class="form-check form-check-custom form-check-solid mb-3">
                                        <input class="form-check-input" type="checkbox" name="payment_status[]" value="partial" />
                                        <span class="form-check-label text-gray-600">Thanh toán một phần</span>
                                    </label>
                                    <label class="form-check form-check-custom form-check-solid mb-3">
                                        <input class="form-check-input" type="checkbox" name="payment_status[]" value="paid" />
                                        <span class="form-check-label text-gray-600">Đã thanh toán</span>
                                    </label>
                                    <label class="form-check form-check-custom form-check-solid mb-3">
                                        <input class="form-check-input" type="checkbox" name="payment_status[]" value="refunded" />
                                        <span class="form-check-label text-gray-600">Đã hoàn tiền</span>
                                    </label>
                                </div>
                            </div>
                            <!--end::Payment Status Filter-->

                            <!--begin::Delivery Status Filter-->
                            <div class="mb-10" id="delivery-status-filter-container">
                                <h3 class="form-label fw-bold">Trạng thái giao hàng</h3>
                                <div class="separator separator-dashed my-5"></div>
                                <div class="d-flex flex-column">
                                    <label class="form-check form-check-custom form-check-solid mb-3">
                                        <input class="form-check-input" type="checkbox" name="delivery_status[]" value="pending" />
                                        <span class="form-check-label text-gray-600">Chờ giao hàng</span>
                                    </label>
                                    <label class="form-check form-check-custom form-check-solid mb-3">
                                        <input class="form-check-input" type="checkbox" name="delivery_status[]" value="preparing" />
                                        <span class="form-check-label text-gray-600">Đang chuẩn bị</span>
                                    </label>
                                    <label class="form-check form-check-custom form-check-solid mb-3">
                                        <input class="form-check-input" type="checkbox" name="delivery_status[]" value="shipped" />
                                        <span class="form-check-label text-gray-600">Đã gửi hàng</span>
                                    </label>
                                    <label class="form-check form-check-custom form-check-solid mb-3">
                                        <input class="form-check-input" type="checkbox" name="delivery_status[]" value="out_for_delivery" />
                                        <span class="form-check-label text-gray-600">Đang giao hàng</span>
                                    </label>
                                    <label class="form-check form-check-custom form-check-solid mb-3">
                                        <input class="form-check-input" type="checkbox" name="delivery_status[]" value="delivered" />
                                        <span class="form-check-label text-gray-600">Đã giao hàng</span>
                                    </label>
                                    <label class="form-check form-check-custom form-check-solid mb-3">
                                        <input class="form-check-input" type="checkbox" name="delivery_status[]" value="failed" />
                                        <span class="form-check-label text-gray-600">Giao hàng thất bại</span>
                                    </label>
                                    <label class="form-check form-check-custom form-check-solid mb-3">
                                        <input class="form-check-input" type="checkbox" name="delivery_status[]" value="returned" />
                                        <span class="form-check-label text-gray-600">Đã trả hàng</span>
                                    </label>
                                </div>
                            </div>
                            <!--end::Delivery Status Filter-->

                            <!--begin::Channel Filter-->
                            <div class="mb-10" id="channel-filter-container">
                                <h3 class="form-label fw-bold">Kênh bán</h3>
                                <div class="separator separator-dashed my-5"></div>
                                <input type="text" class="form-control form-control-solid" placeholder="Chọn kênh bán..." name="channel" id="channel-filter" />
                            </div>
                            <!--end::Channel Filter-->

                            <!--begin::Payment Method Filter-->
                            <div class="mb-10" id="payment-method-filter-container">
                                <h3 class="form-label fw-bold">Phương thức thanh toán</h3>
                                <div class="separator separator-dashed my-5"></div>
                                <select class="form-select form-select-solid" data-control="select2" data-placeholder="Chọn phương thức thanh toán..." data-allow-clear="true" name="payment_method" id="payment-method-filter">
                                    <option></option>
                                </select>
                            </div>
                            <!--end::Payment Method Filter-->

                            <!--begin::Branch Filter-->
                            <div class="mb-10" id="branch-filter-container">
                                <h3 class="form-label fw-bold">Chi nhánh</h3>
                                <div class="separator separator-dashed my-5"></div>
                                <select class="form-select form-select-solid" data-control="select2" data-placeholder="Chọn chi nhánh..." data-allow-clear="true" name="branch_shop_id" id="branch-filter">
                                    <option></option>
                                </select>
                            </div>
                            <!--end::Branch Filter-->
                        </div>
                        <!--end::Card body-->
                    </div>
                    <!--end::Card-->
                </div>
                <!--end::Sidebar-->

                <!--begin::Content-->
                <div class="flex-lg-row-fluid ms-lg-10">
                    <!--begin::Card-->
                    <div class="card card-flush">
                        <!--begin::Card header-->
                        <div class="card-header align-items-center py-5 gap-2 gap-md-5">
                            <!--begin::Card title-->
                            <div class="card-title">
                                <!--begin::Search-->
                                <div class="d-flex align-items-center position-relative my-1">
                                    <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-4">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                    <input type="text" data-kt-orders-table-filter="search" class="form-control form-control-solid w-250px ps-12" placeholder="Tìm kiếm đơn hàng..." />
                                </div>
                                <!--end::Search-->
                            </div>
                            <!--end::Card title-->

                            <!--begin::Card toolbar-->
                            <div class="card-toolbar flex-row-fluid justify-content-end gap-5">
                                <!--begin::Export dropdown-->
                                <button type="button" class="btn btn-light-primary" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                    <i class="ki-duotone ki-exit-down fs-2"><span class="path1"></span><span class="path2"></span></i>
                                    Xuất Excel
                                </button>
                                <!--begin::Menu-->
                                <div id="kt_orders_export_menu" class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-200px py-4" data-kt-menu="true">
                                    <!--begin::Menu item-->
                                    <div class="menu-item px-3">
                                        <a href="#" class="menu-link px-3" data-kt-orders-export="copy">
                                            Copy to clipboard
                                        </a>
                                    </div>
                                    <!--end::Menu item-->
                                    <!--begin::Menu item-->
                                    <div class="menu-item px-3">
                                        <a href="#" class="menu-link px-3" data-kt-orders-export="excel">
                                            Export as Excel
                                        </a>
                                    </div>
                                    <!--end::Menu item-->
                                    <!--begin::Menu item-->
                                    <div class="menu-item px-3">
                                        <a href="#" class="menu-link px-3" data-kt-orders-export="csv">
                                            Export as CSV
                                        </a>
                                    </div>
                                    <!--end::Menu item-->
                                    <!--begin::Menu item-->
                                    <div class="menu-item px-3">
                                        <a href="#" class="menu-link px-3" data-kt-orders-export="pdf">
                                            Export as PDF
                                        </a>
                                    </div>
                                    <!--end::Menu item-->
                                </div>
                                <!--end::Menu-->
                                <!--end::Export dropdown-->

                                <!--begin::Add order-->
                                <a href="{{ route('admin.order.add') }}" class="btn btn-primary">
                                    <i class="ki-duotone ki-plus fs-2"></i>
                                    Thêm mới
                                </a>
                                <!--end::Add order-->

                                <!--begin::Column visibility-->
                                <button type="button" class="btn btn-light-primary" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end" id="column-visibility-btn">
                                    <i class="ki-duotone ki-setting-3 fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                                    Cột hiển thị
                                </button>
                                <!--begin::Menu-->
                                <div id="kt_orders_column_visibility" class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-200px py-4" data-kt-menu="true">
                                    <!--Column visibility options will be populated by JavaScript-->
                                </div>
                                <!--end::Menu-->
                                <!--end::Column visibility-->
                            </div>
                            <!--end::Card toolbar-->
                        </div>
                        <!--end::Card header-->

                        <!--begin::Card body-->
                        <div class="card-body pt-0">
                            <!--begin::Table container-->
                            <div class="table-responsive" id="kt_orders_table_container">
                                <!--begin::Table-->
                                <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_orders_table">
                                    <thead>
                                        <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                            <th class="w-10px pe-2">
                                                <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                                    <input class="form-check-input" type="checkbox" data-kt-check="true" data-kt-check-target="#kt_orders_table .form-check-input" value="1" />
                                                </div>
                                            </th>
                                            <th class="min-w-125px">Mã đơn hàng</th>
                                            <th class="min-w-125px">Khách hàng</th>
                                            <th class="min-w-125px">Tổng tiền</th>
                                            <th class="min-w-125px">Đã thanh toán</th>
                                            <th class="min-w-125px">Trạng thái</th>
                                            <th class="min-w-125px">TT Thanh toán</th>
                                            <th class="min-w-125px">TT Giao hàng</th>
                                            <th class="min-w-125px">Kênh bán</th>
                                            <th class="min-w-125px">Ngày tạo</th>
                                            <th class="min-w-125px">Người bán</th>
                                            <th class="min-w-125px">Người tạo</th>
                                            <th class="min-w-125px">Email</th>
                                            <th class="min-w-125px">Chi nhánh</th>
                                        </tr>
                                    </thead>
                                    <tbody class="fw-semibold text-gray-600">
                                        <!-- Data will be populated by AJAX -->
                                    </tbody>
                                </table>
                                <!--end::Table-->
                            </div>
                            <!--end::Table container-->

                            <!--begin::Pagination-->
                            <div class="d-flex flex-stack flex-wrap pt-10">
                                <div class="fs-6 fw-semibold text-gray-700" id="kt_orders_table_info">
                                    Hiển thị 1 đến 10 của 50 kết quả
                                </div>
                                <ul class="pagination kt_table_pagination" id="kt_orders_table_pagination">
                                    <li class="page-item previous">
                                        <a href="#" class="page-link"><i class="previous"></i></a>
                                    </li>
                                    <li class="page-item active">
                                        <a href="#" class="page-link">1</a>
                                    </li>
                                    <li class="page-item">
                                        <a href="#" class="page-link">2</a>
                                    </li>
                                    <li class="page-item">
                                        <a href="#" class="page-link">3</a>
                                    </li>
                                    <li class="page-item next">
                                        <a href="#" class="page-link"><i class="next"></i></a>
                                    </li>
                                </ul>
                            </div>
                            <!--end::Pagination-->
                        </div>
                        <!--end::Card body-->
                    </div>
                    <!--end::Card-->
                </div>
                <!--end::Content-->
            </div>
        </div>
    </div>
    <!--end::Content-->
</div>
@endsection

@section('scripts')
<!-- Include global filter and column visibility scripts -->
<script src="{{ asset('admin-assets/globals/filter.js') }}"></script>
<script src="{{ asset('admin-assets/globals/column-visibility.js') }}"></script>

<script>
// Order routes configuration
const orderRoutes = {
    data: '{{ route("admin.order.ajax") }}',
    get: '{{ route("admin.order.get", ":id") }}',
    detail: '{{ route("admin.order.detail.modal", ":id") }}',
    updateStatus: '/admin/orders/update-status/:id',
    recordPayment: '/admin/orders/:id/record-payment',
    quickUpdate: '{{ route("admin.order.quick.update", ":id") }}',
    bulkDelete: '{{ route("admin.order.bulk.delete") }}',
    filterOptions: {
        statuses: '{{ route("admin.order.filter.statuses") }}',
        paymentStatuses: '{{ route("admin.order.filter.payment.statuses") }}',
        paymentMethods: '{{ route("admin.order.filter.payment.methods") }}',
        creators: '{{ route("admin.order.filter.creators") }}',
        sellers: '{{ route("admin.order.filter.sellers") }}',
        branches: '{{ route("admin.order.filter.branches") }}',
        deliveryStatuses: '{{ route("admin.order.filter.delivery.statuses") }}',
        channels: '{{ route("admin.order.filter.channels") }}',
        customerTypes: '{{ route("admin.order.filter.customer.types") }}',
        all: '{{ route("admin.order.filter.all") }}'
    }
};

// Initialize page when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    initOrdersPage();
});

function initOrdersPage() {
    console.log('Initializing Orders Page...');
    
    // Initialize filters
    initTimeFilter(loadOrders);
    initFilterStatus(['pending', 'processing', 'shipped', 'delivered', 'completed', 'cancelled'], loadOrders);
    initFilterCreators(orderRoutes.filterOptions.creators, loadOrders);
    initFilterSellers(orderRoutes.filterOptions.sellers, loadOrders);
    
    // Initialize column visibility
    initColumnVisibility('orders_page', [
        { key: 'order_code', label: 'Mã đơn hàng', visible: true },
        { key: 'customer', label: 'Khách hàng', visible: true },
        { key: 'total_amount', label: 'Tổng tiền', visible: true },
        { key: 'amount_paid', label: 'Đã thanh toán', visible: true },
        { key: 'status', label: 'Trạng thái', visible: true },
        { key: 'payment_status', label: 'TT Thanh toán', visible: true },
        { key: 'delivery_status', label: 'TT Giao hàng', visible: true },
        { key: 'channel', label: 'Kênh bán', visible: true },
        { key: 'created_at', label: 'Ngày tạo', visible: true },
        { key: 'seller', label: 'Người bán', visible: true },
        { key: 'creator', label: 'Người tạo', visible: true },
        { key: 'email', label: 'Email', visible: false },
        { key: 'branch_shop', label: 'Chi nhánh', visible: true }
    ]);
    
    // Initialize search
    initOrderSearch();
    
    // Load initial data
    loadOrders();
}

function loadOrders(filters = {}) {
    console.log('Loading orders with filters:', filters);
    
    // Show loading state
    const tbody = document.querySelector('#kt_orders_table tbody');
    tbody.innerHTML = '<tr><td colspan="14" class="text-center">Đang tải...</td></tr>';
    
    // Build query parameters
    const params = new URLSearchParams({
        page: filters.page || 1,
        per_page: filters.per_page || 10,
        search: filters.search || '',
        time_filter_display: filters.time_filter_display || 'this_month',
        date_from: filters.date_from || '',
        date_to: filters.date_to || '',
        ...filters
    });
    
    // Make AJAX request
    fetch(`${orderRoutes.data}?${params}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderOrders(data.data);
                updatePagination(data);
            } else {
                console.error('Failed to load orders:', data.message);
                tbody.innerHTML = '<tr><td colspan="14" class="text-center text-danger">Lỗi tải dữ liệu</td></tr>';
            }
        })
        .catch(error => {
            console.error('Error loading orders:', error);
            tbody.innerHTML = '<tr><td colspan="14" class="text-center text-danger">Lỗi kết nối</td></tr>';
        });
}

function renderOrders(orders) {
    const tbody = document.querySelector('#kt_orders_table tbody');
    
    if (!orders || orders.length === 0) {
        tbody.innerHTML = '<tr><td colspan="14" class="text-center">Không có dữ liệu</td></tr>';
        return;
    }
    
    tbody.innerHTML = orders.map(order => `
        <tr class="order-row" data-order-id="${order.id}">
            <td>
                <div class="form-check form-check-sm form-check-custom form-check-solid">
                    <input class="form-check-input" type="checkbox" value="${order.id}" />
                </div>
            </td>
            <td>
                <div class="text-gray-800 text-hover-primary mb-1">${order.order_code || 'N/A'}</div>
            </td>
            <td>
                <div class="text-gray-800 text-hover-primary mb-1">${order.customer_name || 'Khách lẻ'}</div>
            </td>
            <td>
                <div class="text-gray-800 mb-1">${formatCurrency(order.total_amount)}</div>
            </td>
            <td>
                <div class="text-gray-800 mb-1">${formatCurrency(order.amount_paid)}</div>
            </td>
            <td>
                <div class="badge badge-light-${getStatusColor(order.status)}">${getStatusLabel(order.status)}</div>
            </td>
            <td>
                <div class="badge badge-light-${getPaymentStatusColor(order.payment_status)}">${getPaymentStatusLabel(order.payment_status)}</div>
            </td>
            <td>
                <div class="badge badge-light-${getDeliveryStatusColor(order.delivery_status)}">${getDeliveryStatusLabel(order.delivery_status)}</div>
            </td>
            <td>
                <div class="text-gray-800 mb-1">${getChannelLabel(order.channel)}</div>
            </td>
            <td>
                <div class="text-gray-800 mb-1">
                    <div>${formatDate(order.created_at)}</div>
                    <div class="text-muted fs-7">${formatTime(order.created_at)}</div>
                </div>
            </td>
            <td>
                <div class="text-gray-800 mb-1">${order.seller_name || 'N/A'}</div>
            </td>
            <td>
                <div class="text-gray-800 mb-1">${order.creator_name || 'N/A'}</div>
            </td>
            <td>
                <div class="text-gray-800 mb-1">${order.customer_email || 'N/A'}</div>
            </td>
            <td>
                <div class="text-gray-800 mb-1">${order.branch_shop_name || 'N/A'}</div>
            </td>
        </tr>
    `).join('');
    
    // Apply column visibility
    applyColumnVisibility();
    
    // Bind row click events for expansion
    bindRowClickEvents();
}

// Helper functions
function formatCurrency(amount) {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(amount || 0);
}

function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString('vi-VN');
}

function formatTime(dateString) {
    return new Date(dateString).toLocaleTimeString('vi-VN', {
        hour: '2-digit',
        minute: '2-digit'
    });
}

function getStatusColor(status) {
    const colors = {
        'draft': 'secondary',
        'pending': 'warning',
        'processing': 'info',
        'shipped': 'primary',
        'delivered': 'success',
        'completed': 'success',
        'cancelled': 'danger',
        'returned': 'dark'
    };
    return colors[status] || 'secondary';
}

function getStatusLabel(status) {
    const labels = {
        'draft': 'Nháp',
        'pending': 'Chờ xử lý',
        'processing': 'Đang xử lý',
        'shipped': 'Đã gửi hàng',
        'delivered': 'Đã giao hàng',
        'completed': 'Hoàn thành',
        'cancelled': 'Đã hủy',
        'returned': 'Đã trả hàng'
    };
    return labels[status] || status;
}

function getPaymentStatusColor(status) {
    const colors = {
        'unpaid': 'danger',
        'partial': 'warning',
        'paid': 'success',
        'refunded': 'info'
    };
    return colors[status] || 'secondary';
}

function getPaymentStatusLabel(status) {
    const labels = {
        'unpaid': 'Chưa thanh toán',
        'partial': 'Thanh toán một phần',
        'paid': 'Đã thanh toán',
        'refunded': 'Đã hoàn tiền'
    };
    return labels[status] || status;
}

function getDeliveryStatusColor(status) {
    const colors = {
        'pending': 'secondary',
        'preparing': 'warning',
        'shipped': 'info',
        'out_for_delivery': 'primary',
        'delivered': 'success',
        'failed': 'danger',
        'returned': 'dark'
    };
    return colors[status] || 'secondary';
}

function getDeliveryStatusLabel(status) {
    const labels = {
        'pending': 'Chờ giao hàng',
        'preparing': 'Đang chuẩn bị',
        'shipped': 'Đã gửi hàng',
        'out_for_delivery': 'Đang giao hàng',
        'delivered': 'Đã giao hàng',
        'failed': 'Giao hàng thất bại',
        'returned': 'Đã trả hàng'
    };
    return labels[status] || status;
}

function getChannelLabel(channel) {
    const labels = {
        'website': 'Website',
        'mobile_app': 'Mobile App',
        'facebook': 'Facebook',
        'shopee': 'Shopee',
        'lazada': 'Lazada',
        'tiki': 'Tiki',
        'phone': 'Điện thoại',
        'store': 'Cửa hàng'
    };
    return labels[channel] || channel || 'N/A';
}

function initOrderSearch() {
    const searchInput = document.querySelector('[data-kt-orders-table-filter="search"]');
    let searchTimeout;
    
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            loadOrders({ search: this.value });
        }, 500);
    });
}

function bindRowClickEvents() {
    document.querySelectorAll('.order-row').forEach(row => {
        row.addEventListener('click', function(e) {
            if (e.target.type === 'checkbox') return;
            
            const orderId = this.dataset.orderId;
            toggleOrderExpansion(orderId, this);
        });
    });
}

function toggleOrderExpansion(orderId, row) {
    console.log('Toggling order expansion for:', orderId);
    // Implementation for order detail expansion similar to invoices
    // This will be implemented in the next step
}

function updatePagination(data) {
    // Update pagination info and controls
    const info = document.getElementById('kt_orders_table_info');
    const pagination = document.getElementById('kt_orders_table_pagination');
    
    if (info && data.recordsTotal) {
        const start = ((data.draw - 1) * data.length) + 1;
        const end = Math.min(start + data.length - 1, data.recordsTotal);
        info.textContent = `Hiển thị ${start} đến ${end} của ${data.recordsTotal} kết quả`;
    }
    
    // Update pagination controls (simplified)
    // Full pagination implementation would go here
}
</script>
@endsection
