@extends('admin.main-content')

@section('title', 'Trang chủ')

@section('content')
<div class="d-flex flex-column flex-column-fluid">
    <!--begin::Toolbar-->
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
        <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                    Trang chủ
                </h1>
                <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('admin.dashboard') }}" class="text-muted text-hover-primary">Trang chủ</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <!--end::Toolbar-->

    <!--begin::Content-->
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-xxl">
            
            <!-- Welcome Card -->
            <div class="card mb-5 mb-xl-10">
                <div class="card-body pt-9 pb-0">
                    <div class="d-flex flex-wrap flex-sm-nowrap">
                        <div class="me-7 mb-4">
                            <div class="symbol symbol-100px symbol-lg-160px symbol-fixed position-relative">
                                <img src="{{ asset('admin-assets/assets/media/avatars/300-1.jpg') }}" alt="image" />
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start flex-wrap mb-2">
                                <div class="d-flex flex-column">
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="text-gray-900 text-hover-primary fs-2 fw-bold me-1">
                                            Chào mừng, {{ Auth::user()->full_name ?? Auth::user()->username ?? 'Admin' }}!
                                        </span>
                                    </div>
                                    <div class="d-flex flex-wrap fw-semibold fs-6 mb-4 pe-2">
                                        <span class="d-flex align-items-center text-gray-400 me-5 mb-2">
                                            <i class="fas fa-user-shield me-1 fs-6"></i>
                                            Quản trị viên hệ thống
                                        </span>
                                        <span class="d-flex align-items-center text-gray-400 me-5 mb-2">
                                            <i class="fas fa-calendar me-1 fs-6"></i>
                                            {{ now()->format('d/m/Y H:i') }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex flex-wrap flex-stack">
                                <div class="d-flex flex-column flex-grow-1 pe-8">
                                    <div class="d-flex flex-wrap">
                                        <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-shopping-cart text-primary fs-2 me-2"></i>
                                                <div class="fs-2 fw-bold counted" data-kt-countup="true" data-kt-countup-value="0" id="total-orders">0</div>
                                            </div>
                                            <div class="fw-semibold fs-6 text-gray-400">Đơn hàng</div>
                                        </div>
                                        <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-file-invoice text-success fs-2 me-2"></i>
                                                <div class="fs-2 fw-bold counted" data-kt-countup="true" data-kt-countup-value="0" id="total-invoices">0</div>
                                            </div>
                                            <div class="fw-semibold fs-6 text-gray-400">Hóa đơn</div>
                                        </div>
                                        <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-box text-warning fs-2 me-2"></i>
                                                <div class="fs-2 fw-bold counted" data-kt-countup="true" data-kt-countup-value="0" id="total-products">0</div>
                                            </div>
                                            <div class="fw-semibold fs-6 text-gray-400">Sản phẩm</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Row -->
            <div class="row g-5 g-xl-10 mb-5 mb-xl-10">
                <!-- Revenue Card -->
                <div class="col-md-6 col-lg-6 col-xl-3">
                    <div class="card card-flush bgi-no-repeat bgi-size-contain bgi-position-x-end h-md-50px mb-5 mb-xl-10" style="background-color: #F1416C;background-image:url('{{ asset('admin-assets/assets/media/patterns/vector-1.png') }}')">
                        <div class="card-header pt-5">
                            <div class="card-title d-flex flex-column">
                                <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2" id="total-revenue">0đ</span>
                                <span class="text-white opacity-75 pt-1 fw-semibold fs-6">Doanh thu hôm nay</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Orders Today -->
                <div class="col-md-6 col-lg-6 col-xl-3">
                    <div class="card card-flush bgi-no-repeat bgi-size-contain bgi-position-x-end h-md-50px mb-5 mb-xl-10" style="background-color: #7239EA;background-image:url('{{ asset('admin-assets/assets/media/patterns/vector-1.png') }}')">
                        <div class="card-header pt-5">
                            <div class="card-title d-flex flex-column">
                                <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2" id="orders-today">0</span>
                                <span class="text-white opacity-75 pt-1 fw-semibold fs-6">Đơn hàng hôm nay</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Customers -->
                <div class="col-md-6 col-lg-6 col-xl-3">
                    <div class="card card-flush bgi-no-repeat bgi-size-contain bgi-position-x-end h-md-50px mb-5 mb-xl-10" style="background-color: #17C653;background-image:url('{{ asset('admin-assets/assets/media/patterns/vector-1.png') }}')">
                        <div class="card-header pt-5">
                            <div class="card-title d-flex flex-column">
                                <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2" id="total-customers">0</span>
                                <span class="text-white opacity-75 pt-1 fw-semibold fs-6">Khách hàng</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Low Stock -->
                <div class="col-md-6 col-lg-6 col-xl-3">
                    <div class="card card-flush bgi-no-repeat bgi-size-contain bgi-position-x-end h-md-50px mb-5 mb-xl-10" style="background-color: #FFC700;background-image:url('{{ asset('admin-assets/assets/media/patterns/vector-1.png') }}')">
                        <div class="card-header pt-5">
                            <div class="card-title d-flex flex-column">
                                <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2" id="low-stock">0</span>
                                <span class="text-white opacity-75 pt-1 fw-semibold fs-6">Sản phẩm sắp hết</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="row g-5 g-xl-10 mb-5 mb-xl-10">
                <div class="col-xl-12">
                    <div class="card card-flush h-md-100">
                        <div class="card-header pt-7">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold text-dark">Thao tác nhanh</span>
                                <span class="text-gray-400 mt-1 fw-semibold fs-6">Các tính năng thường dùng</span>
                            </h3>
                        </div>
                        <div class="card-body pt-6">
                            <div class="row g-5">
                                <div class="col-md-6 col-lg-4 col-xl-3">
                                    <a href="{{ route('admin.orders.quick') }}" class="btn btn-flex btn-outline btn-color-gray-700 btn-active-color-primary bg-body h-150px flex-column">
                                        <i class="fas fa-plus-circle fs-2x text-primary mb-3"></i>
                                        <span class="fs-4 fw-semibold text-gray-800 mb-2">Tạo đơn hàng</span>
                                        <span class="fs-7 text-gray-400">Tạo đơn hàng nhanh</span>
                                    </a>
                                </div>
                                <div class="col-md-6 col-lg-4 col-xl-3">
                                    <a href="{{ route('admin.products.index') }}" class="btn btn-flex btn-outline btn-color-gray-700 btn-active-color-primary bg-body h-150px flex-column">
                                        <i class="fas fa-box fs-2x text-success mb-3"></i>
                                        <span class="fs-4 fw-semibold text-gray-800 mb-2">Quản lý sản phẩm</span>
                                        <span class="fs-7 text-gray-400">Thêm, sửa sản phẩm</span>
                                    </a>
                                </div>
                                <div class="col-md-6 col-lg-4 col-xl-3">
                                    <a href="{{ route('admin.customers.index') }}" class="btn btn-flex btn-outline btn-color-gray-700 btn-active-color-primary bg-body h-150px flex-column">
                                        <i class="fas fa-users fs-2x text-warning mb-3"></i>
                                        <span class="fs-4 fw-semibold text-gray-800 mb-2">Khách hàng</span>
                                        <span class="fs-7 text-gray-400">Quản lý khách hàng</span>
                                    </a>
                                </div>
                                <div class="col-md-6 col-lg-4 col-xl-3">
                                    <a href="{{ route('admin.backup.index') }}" class="btn btn-flex btn-outline btn-color-gray-700 btn-active-color-primary bg-body h-150px flex-column">
                                        <i class="fas fa-database fs-2x text-info mb-3"></i>
                                        <span class="fs-4 fw-semibold text-gray-800 mb-2">Sao lưu dữ liệu</span>
                                        <span class="fs-7 text-gray-400">Backup & restore</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activities -->
            <div class="row g-5 g-xl-10">
                <div class="col-xl-6">
                    <div class="card card-flush h-md-100">
                        <div class="card-header pt-7">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold text-dark">Đơn hàng gần đây</span>
                                <span class="text-gray-400 mt-1 fw-semibold fs-6">10 đơn hàng mới nhất</span>
                            </h3>
                            <div class="card-toolbar">
                                <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-light">Xem tất cả</a>
                            </div>
                        </div>
                        <div class="card-body pt-6">
                            <div id="recent-orders">
                                <div class="d-flex align-items-center justify-content-center py-10">
                                    <span class="spinner-border spinner-border-sm text-muted me-2"></span>
                                    <span class="text-muted">Đang tải...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-6">
                    <div class="card card-flush h-md-100">
                        <div class="card-header pt-7">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold text-dark">Sản phẩm bán chạy</span>
                                <span class="text-gray-400 mt-1 fw-semibold fs-6">Top 10 sản phẩm</span>
                            </h3>
                            <div class="card-toolbar">
                                <a href="{{ route('admin.products.index') }}" class="btn btn-sm btn-light">Xem tất cả</a>
                            </div>
                        </div>
                        <div class="card-body pt-6">
                            <div id="top-products">
                                <div class="d-flex align-items-center justify-content-center py-10">
                                    <span class="spinner-border spinner-border-sm text-muted me-2"></span>
                                    <span class="text-muted">Đang tải...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--end::Content-->
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    loadDashboardStats();
    loadRecentOrders();
    loadTopProducts();
});

async function loadDashboardStats() {
    try {
        // Load basic stats
        const response = await fetch('/admin/dashboard/stats');
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('total-orders').textContent = data.data.total_orders || 0;
            document.getElementById('total-invoices').textContent = data.data.total_invoices || 0;
            document.getElementById('total-products').textContent = data.data.total_products || 0;
            document.getElementById('total-revenue').textContent = formatCurrency(data.data.total_revenue || 0);
            document.getElementById('orders-today').textContent = data.data.orders_today || 0;
            document.getElementById('total-customers').textContent = data.data.total_customers || 0;
            document.getElementById('low-stock').textContent = data.data.low_stock || 0;
        }
    } catch (error) {
        console.error('Failed to load dashboard stats:', error);
    }
}

async function loadRecentOrders() {
    try {
        const response = await fetch('/admin/dashboard/recent-orders');
        const data = await response.json();
        
        const container = document.getElementById('recent-orders');
        if (data.success && data.data.length > 0) {
            let html = '';
            data.data.forEach(order => {
                html += `
                    <div class="d-flex align-items-center mb-6">
                        <div class="symbol symbol-45px me-5">
                            <span class="symbol-label bg-light-primary">
                                <i class="fas fa-shopping-cart text-primary fs-2"></i>
                            </span>
                        </div>
                        <div class="d-flex flex-column flex-grow-1">
                            <a href="/admin/orders/${order.id}" class="text-dark fw-bold text-hover-primary fs-6">#${order.order_number}</a>
                            <span class="text-muted fw-semibold">${formatCurrency(order.total_amount)}</span>
                        </div>
                        <span class="badge badge-light-${getStatusColor(order.status)} fs-8">${order.status}</span>
                    </div>
                `;
            });
            container.innerHTML = html;
        } else {
            container.innerHTML = '<div class="text-center py-10"><span class="text-muted">Chưa có đơn hàng nào</span></div>';
        }
    } catch (error) {
        console.error('Failed to load recent orders:', error);
    }
}

async function loadTopProducts() {
    try {
        const response = await fetch('/admin/dashboard/top-products');
        const data = await response.json();
        
        const container = document.getElementById('top-products');
        if (data.success && data.data.length > 0) {
            let html = '';
            data.data.forEach((product, index) => {
                html += `
                    <div class="d-flex align-items-center mb-6">
                        <div class="symbol symbol-45px me-5">
                            <span class="symbol-label bg-light-success">
                                <span class="text-success fw-bold">${index + 1}</span>
                            </span>
                        </div>
                        <div class="d-flex flex-column flex-grow-1">
                            <a href="/admin/products/${product.id}" class="text-dark fw-bold text-hover-primary fs-6">${product.name}</a>
                            <span class="text-muted fw-semibold">Đã bán: ${product.sold_quantity || 0}</span>
                        </div>
                        <span class="text-dark fw-bold">${formatCurrency(product.price)}</span>
                    </div>
                `;
            });
            container.innerHTML = html;
        } else {
            container.innerHTML = '<div class="text-center py-10"><span class="text-muted">Chưa có dữ liệu</span></div>';
        }
    } catch (error) {
        console.error('Failed to load top products:', error);
    }
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(amount);
}

function getStatusColor(status) {
    const colors = {
        'pending': 'warning',
        'processing': 'info',
        'completed': 'success',
        'cancelled': 'danger'
    };
    return colors[status] || 'secondary';
}
</script>
@endpush
