@extends('admin.index')
@section('page-header', 'Dashboard')
@section('page-sub_header', 'Thống kê Dashboard')
@section('style')
<style>
    .stats-card {
        transition: transform 0.2s ease-in-out;
    }
    .stats-card:hover {
        transform: translateY(-2px);
    }
    .activity-item {
        border-left: 3px solid #e4e6ef;
        padding-left: 15px;
        margin-bottom: 15px;
    }
    .activity-item.recent {
        border-left-color: #50cd89;
    }

    /* Loading States */
    .chart-loading-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.9);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10;
        border-radius: 0.475rem;
    }

    .chart-loading-spinner {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 15px;
    }

    .spinner-border-custom {
        width: 3rem;
        height: 3rem;
        border: 0.25em solid #e4e6ef;
        border-top: 0.25em solid #009ef7;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .loading-text {
        color: #7e8299;
        font-size: 0.875rem;
        font-weight: 500;
    }

    /* Disabled Dropdown States */
    .dropdown-disabled {
        opacity: 0.6;
        pointer-events: none;
        cursor: not-allowed;
        transition: opacity 0.3s ease;
    }

    .dropdown-disabled select {
        background-color: #f5f8fa;
        cursor: not-allowed;
    }

    /* Smooth Dropdown Animations */
    .form-select {
        transition: all 0.3s ease;
        border: 1px solid #e4e6ef;
    }

    .form-select:hover:not(:disabled) {
        border-color: #009ef7;
        box-shadow: 0 0 0 0.1rem rgba(0, 158, 247, 0.1);
        transform: translateY(-1px);
    }

    .form-select:focus {
        border-color: #009ef7;
        box-shadow: 0 0 0 0.2rem rgba(0, 158, 247, 0.25);
        transform: translateY(-1px);
    }

    .form-select:disabled {
        background-color: #f5f8fa;
        border-color: #e4e6ef;
        transform: none;
        box-shadow: none;
    }

    /* Card Toolbar Animations */
    .card-toolbar {
        transition: all 0.3s ease;
    }

    .card-toolbar .form-select {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .card-toolbar .form-select:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    /* Chart Container */
    .chart-container {
        position: relative;
        min-height: 350px;
    }

    .chart-container.loading {
        overflow: hidden;
    }
</style>
@endsection
@section('content')
    <!-- Today's Sales Statistics -->
    <div class="row g-5 g-xl-8 mb-8">
        <div class="col-xl-12">
            <div class="card card-flush">
                <div class="card-header pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-gray-900">Hôm nay - Kết quả bán hàng</span>
                        <span class="text-gray-500 mt-1 fw-semibold fs-6">{{ now()->format('d/m/Y') }}</span>
                    </h3>
                </div>
                <div class="card-body pt-2">
                    <div class="row g-5">
                        <div class="col-md-3">
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-40px me-3">
                                    <span class="symbol-label bg-light-primary">
                                        <i class="fas fa-shopping-cart text-primary fs-4"></i>
                                    </span>
                                </div>
                                <div class="d-flex flex-column">
                                    <span class="fw-bold fs-2 text-gray-900">{{ $todaySales['orders_count'] ?? 0 }}</span>
                                    <span class="text-gray-500 fs-7">Đơn hàng</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-40px me-3">
                                    <span class="symbol-label bg-light-success">
                                        <i class="fas fa-dollar-sign text-success fs-4"></i>
                                    </span>
                                </div>
                                <div class="d-flex flex-column">
                                    <span class="fw-bold fs-2 text-gray-900">{{ number_format($todaySales['revenue'] ?? 0, 0, ',', '.') }}</span>
                                    <span class="text-gray-500 fs-7">Doanh thu (VNĐ)</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-40px me-3">
                                    <span class="symbol-label bg-light-warning">
                                        <i class="fas fa-users text-warning fs-4"></i>
                                    </span>
                                </div>
                                <div class="d-flex flex-column">
                                    <span class="fw-bold fs-2 text-gray-900">{{ $todaySales['customers_count'] ?? 0 }}</span>
                                    <span class="text-gray-500 fs-7">Khách hàng</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-40px me-3">
                                    <span class="symbol-label bg-light-info">
                                        <i class="fas fa-chart-line text-info fs-4"></i>
                                    </span>
                                </div>
                                <div class="d-flex flex-column">
                                    <span class="fw-bold fs-2 text-gray-900">{{ number_format($todaySales['avg_order_value'] ?? 0, 0, ',', '.') }}</span>
                                    <span class="text-gray-500 fs-7">Giá trị TB/đơn</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Statistics Cards -->
    <div class="row g-5 g-xl-8 mb-8">
        <div class="col-xl-3">
            <div class="card card-flush stats-card  mb-5 mb-xl-10">
                <div class="card-header pt-5">
                    <div class="card-title d-flex flex-column">
                        <div class="d-flex align-items-center">
                            <span class="fs-2hx fw-bold text-gray-900 me-2 lh-1 ls-n2">{{ $totalProducts ?? 0 }}</span>
                            <span class="badge badge-light-success fs-base">
                                <i class="fas fa-arrow-up fs-6 text-success ms-n1"></i>
                                {{ $activeProducts ?? 0 }}
                            </span>
                        </div>
                        <span class="text-gray-500 pt-1 fw-semibold fs-6">Tổng sản phẩm</span>
                    </div>
                </div>
                <div class="card-body pt-2 pb-4 d-flex align-items-center">
                    <div class="d-flex flex-center me-5 pt-2">
                        <i class="fas fa-box text-primary fs-2x"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3">
            <div class="card card-flush stats-card  mb-5 mb-xl-10">
                <div class="card-header pt-5">
                    <div class="card-title d-flex flex-column">
                        <div class="d-flex align-items-center">
                            <span class="fs-2hx fw-bold text-gray-900 me-2 lh-1 ls-n2">{{ $totalOrders ?? 0 }}</span>
                        </div>
                        <span class="text-gray-500 pt-1 fw-semibold fs-6">Tổng đơn hàng</span>
                    </div>
                </div>
                <div class="card-body pt-2 pb-4 d-flex align-items-center">
                    <div class="d-flex flex-center me-5 pt-2">
                        <i class="fas fa-shopping-cart text-success fs-2x"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3">
            <div class="card card-flush stats-card  mb-5 mb-xl-10">
                <div class="card-header pt-5">
                    <div class="card-title d-flex flex-column">
                        <div class="d-flex align-items-center">
                            <span class="fs-2hx fw-bold text-gray-900 me-2 lh-1 ls-n2">{{ $totalCustomers ?? 0 }}</span>
                        </div>
                        <span class="text-gray-500 pt-1 fw-semibold fs-6">Tổng khách hàng</span>
                    </div>
                </div>
                <div class="card-body pt-2 pb-4 d-flex align-items-center">
                    <div class="d-flex flex-center me-5 pt-2">
                        <i class="fas fa-users text-warning fs-2x"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3">
            <div class="card card-flush stats-card  mb-5 mb-xl-10">
                <div class="card-header pt-5">
                    <div class="card-title d-flex flex-column">
                        <div class="d-flex align-items-center">
                            <span class="fs-2hx fw-bold text-gray-900 me-2 lh-1 ls-n2">{{ $totalUsers ?? 0 }}</span>
                            <span class="badge badge-light-info fs-base">
                                {{ $activeUsers ?? 0 }} hoạt động
                            </span>
                        </div>
                        <span class="text-gray-500 pt-1 fw-semibold fs-6">Tổng người dùng</span>
                    </div>
                </div>
                <div class="card-body pt-2 pb-4 d-flex align-items-center">
                    <div class="d-flex flex-center me-5 pt-2">
                        <i class="fas fa-user-tie text-info fs-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-5 g-xl-8 mb-8">
        <!-- Revenue Chart -->
        <div class="col-xl-8">
            <div class="card card-flush overflow-hidden h-md-100">
                <div class="card-header py-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-gray-900">Biểu đồ doanh thu</span>
                        <span class="text-gray-500 mt-1 fw-semibold fs-6">Thống kê doanh thu theo thời gian</span>
                    </h3>
                    <div class="card-toolbar">
                        <select class="form-select form-select-sm" id="revenue-period-select" style="width: 150px;">
                            <option value="today">Hôm nay</option>
                            <option value="yesterday">Hôm qua</option>
                            <option value="month" selected>Tháng này</option>
                            <option value="last_month">Tháng trước</option>
                            <option value="year">Theo năm</option>
                        </select>
                    </div>
                </div>
                <div class="card-body d-flex justify-content-between flex-column pb-1 px-0">
                    <div class="chart-container">
                        <div id="revenue_chart" style="height: 350px;"></div>
                        <div id="revenue-chart-loading" class="chart-loading-overlay" style="display: none;">
                            <div class="chart-loading-spinner">
                                <div class="spinner-border-custom"></div>
                                <div class="loading-text">Đang tải biểu đồ doanh thu...</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Order Activities Widget -->
        <div class="col-xl-4">
            <div class="card card-flush h-md-100">
                <div class="card-header pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-gray-900">Đơn hàng gần đây</span>
                        <span class="text-gray-500 mt-1 fw-semibold fs-6">Thông báo tạo đơn hàng mới</span>
                    </h3>
                    <div class="card-toolbar">
                        <a href="{{ route('admin.order.list') }}" class="btn btn-sm btn-light-primary">
                            <i class="ki-duotone ki-eye fs-3">
                                <span class="path1"></span>
                                <span class="path2"></span>
                                <span class="path3"></span>
                            </i>
                            Xem tất cả
                        </a>
                    </div>
                </div>
                <div class="card-body pt-5">
                    @if(isset($recentActivities) && $recentActivities->count() > 0)
                        <div class="scroll-y me-n5 pe-5" data-kt-scroll="true" data-kt-scroll-height="350px">
                            @foreach($recentActivities->take(10) as $activity)
                                <!--begin::Item-->
                                <div class="d-flex align-items-center border-bottom border-gray-300 pb-6 mb-6">
                                    <!--begin::Symbol-->
                                    <div class="symbol symbol-45px me-5">
                                        <div class="symbol-label bg-light-primary text-primary">
                                            <i class="fas fa-{{ $activity['icon'] ?? 'basket' }} fs-1">
                                               
                                            </i>
                                        </div>
                                    </div>
                                    <!--end::Symbol-->
                                    <!--begin::Content-->
                                    <div class="d-flex flex-column flex-grow-1">
                                        <!--begin::Title-->
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <span class="fw-bold text-gray-800 fs-6">
                                                @if(isset($activity['order_code']))
                                                    {{ $activity['order_code'] }}
                                                @else
                                                    {{ $activity['action'] }}
                                                @endif
                                            </span>
                                            @if(isset($activity['priority_badge']))
                                                {!! $activity['priority_badge'] !!}
                                            @endif
                                        </div>
                                        <!--end::Title-->
                                        <!--begin::Description-->
                                        <div class="text-gray-600 fs-7 mb-2">
                                            @if(isset($activity['seller_name']))
                                                <i class="fas fa-user fs-7 me-1">
                                                    
                                                </i>
                                                {{ $activity['seller_name'] }}
                                            @else
                                                {{ $activity['description'] }}
                                            @endif
                                        </div>
                                        <!--end::Description-->
                                        <!--begin::Info-->
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-profile-user fs-7 text-gray-400 me-1">
                                                    
                                                </i>
                                                <span class="text-gray-400 fs-7">{{ $activity['user_name'] }}</span>
                                                @if(isset($activity['seller_name']) && $activity['seller_name'] !== $activity['user_name'])
                                                    <span class="text-gray-300 fs-8 mx-1">•</span>
                                                    <i class="ki-duotone ki-handcart fs-7 text-gray-500 me-1">
                                                        <span class="path1"></span>
                                                        <span class="path2"></span>
                                                    </i>
                                                    <span class="text-gray-500 fs-7">{{ $activity['seller_name'] }}</span>
                                                @endif
                                            </div>
                                            @if(isset($activity['formatted_amount']))
                                                <span class="fw-bold text-primary fs-7">{{ $activity['formatted_amount'] }}</span>
                                            @endif
                                        </div>
                                        <!--end::Info-->
                                        <!--begin::Time-->
                                        <div class="d-flex align-items-center justify-content-between mt-2">
                                            <span class="text-gray-400 fs-8">{{ $activity['time_ago'] }}</span>
                                            @if(isset($activity['action_url']) && $activity['action_url'])
                                                <a href="{{ $activity['action_url'] }}" class="btn btn-sm btn-light-primary">
                                                    <i class="fa fa-eye fs-7">
                                                       
                                                    </i>
                                                    Xem
                                                </a>
                                            @endif
                                        </div>
                                        <!--end::Time-->
                                    </div>
                                    <!--end::Content-->
                                    <!--begin::Status-->
                                    @if(!$activity['is_read'])
                                        <div class="w-8px h-8px rounded-circle bg-primary ms-3"></div>
                                    @endif
                                    <!--end::Status-->
                                </div>
                                <!--end::Item-->
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-10">
                            <div class="symbol symbol-100px symbol-circle mb-7">
                                <div class="symbol-label bg-light-primary text-primary">
                                    <i class="ki-duotone ki-basket fs-3x">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                        <span class="path3"></span>
                                        <span class="path4"></span>
                                    </i>
                                </div>
                            </div>
                            <h3 class="text-gray-800 fw-bold mb-3">Chưa có đơn hàng nào</h3>
                            <p class="text-gray-500 mb-6">Các thông báo tạo đơn hàng mới sẽ hiển thị ở đây</p>
                            <a href="{{ route('admin.order.add') }}" class="btn btn-primary">
                                <i class="ki-duotone ki-plus fs-3">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                                Tạo đơn hàng đầu tiên
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Top Products Chart -->
    <div class="row g-5 g-xl-8 mb-8">
        <div class="col-xl-12">
            <div class="card card-flush">
                <div class="card-header py-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-gray-900">Top 10 sản phẩm bán chạy</span>
                        <span class="text-gray-500 mt-1 fw-semibold fs-6">Thống kê sản phẩm theo doanh thu hoặc số lượng</span>
                    </h3>
                    <div class="card-toolbar d-flex gap-3">
                        <select class="form-select form-select-sm" id="top-products-period-select" style="width: 140px;">
                            <option value="today">Hôm nay</option>
                            <option value="yesterday">Hôm qua</option>
                            <option value="month" selected>Tháng này</option>
                            <option value="last_month">Tháng trước</option>
                            <option value="year">Năm nay</option>
                        </select>
                        <select class="form-select form-select-sm" id="top-products-type-select" style="width: 140px;">
                            <option value="revenue" selected>Theo doanh thu</option>
                            <option value="quantity">Theo số lượng</option>
                        </select>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <div id="top_products_chart" style="height: 400px;"></div>
                        <div id="top-products-chart-loading" class="chart-loading-overlay" style="display: none;">
                            <div class="chart-loading-spinner">
                                <div class="spinner-border-custom"></div>
                                <div class="loading-text">Đang tải biểu đồ sản phẩm...</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
  

    <script>
        var labelColor = KTUtil.getCssVariableValue('--kt-gray-500');
        var borderColor = KTUtil.getCssVariableValue('--kt-gray-200');
        var baseColor = KTUtil.getCssVariableValue('--kt-primary');
        var secondaryColor = KTUtil.getCssVariableValue('--kt-gray-300');

        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Revenue Chart
            initRevenueChart();

            // Initialize Top Products Chart
            initTopProductsChart();

            // Event listeners for dropdowns
            document.getElementById('revenue-period-select').addEventListener('change', function() {
                updateRevenueChart(this.value);
            });

            document.getElementById('top-products-period-select').addEventListener('change', function() {
                const type = document.getElementById('top-products-type-select').value;
                updateTopProductsChart(type, this.value);
            });

            document.getElementById('top-products-type-select').addEventListener('change', function() {
                const period = document.getElementById('top-products-period-select').value;
                updateTopProductsChart(this.value, period);
            });
        });

        let revenueChart;
        let topProductsChart;

        // Loading state management functions
        function showChartLoading(chartType) {
            const loadingElement = document.getElementById(`${chartType}-chart-loading`);
            const dropdownElement = document.getElementById(`${chartType}-period-select`) ||
                                  document.getElementById(`${chartType}-type-select`);

            if (loadingElement) {
                loadingElement.style.display = 'flex';
            }

            if (dropdownElement) {
                dropdownElement.disabled = true;
                dropdownElement.parentElement.classList.add('dropdown-disabled');
            }
        }

        function hideChartLoading(chartType) {
            const loadingElement = document.getElementById(`${chartType}-chart-loading`);
            const dropdownElement = document.getElementById(`${chartType}-period-select`) ||
                                  document.getElementById(`${chartType}-type-select`);

            if (loadingElement) {
                loadingElement.style.display = 'none';
            }

            if (dropdownElement) {
                dropdownElement.disabled = false;
                dropdownElement.parentElement.classList.remove('dropdown-disabled');
            }
        }

        function setDropdownLoading(dropdownId, isLoading) {
            const dropdown = document.getElementById(dropdownId);
            if (dropdown) {
                dropdown.disabled = isLoading;
                if (isLoading) {
                    dropdown.parentElement.classList.add('dropdown-disabled');
                } else {
                    dropdown.parentElement.classList.remove('dropdown-disabled');
                }
            }
        }

        function initRevenueChart() {
            const chartData = @json($chartData ?? []);

            // Debug: Log chart data to console
            console.log('Chart Data:', chartData);

            // Check if chart data exists and has required properties
            if (!chartData || !chartData.data || !chartData.categories) {
                console.error('Chart data is missing or invalid:', chartData);

                // Show error message in chart container
                document.querySelector("#revenue_chart").innerHTML = `
                    <div class="d-flex align-items-center justify-content-center h-100">
                        <div class="text-center">
                            <i class="fas fa-chart-line fs-3x text-gray-300 mb-3"></i>
                            <p class="text-gray-500">Không có dữ liệu biểu đồ</p>
                            <small class="text-gray-400">Vui lòng kiểm tra dữ liệu đơn hàng</small>
                        </div>
                    </div>
                `;
                return;
            }

            const options = {
                series: [{
                    name: chartData.series_name || 'Doanh thu',
                    data: chartData.data || []
                }],
                chart: {
                    type: 'bar',
                    height: 350,
                    toolbar: {
                        show: false
                    },
                    animations: {
                        enabled: true,
                        easing: 'easeinout',
                        speed: 800
                    },
                    columnWidth: ['80%'],
                    endingShape: 'rounded'
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    show: true,
                    width: 2,
                    colors: ['transparent']
                },
                xaxis: {
                    categories: chartData.categories || [],
                    labels: {
                        style: {
                            colors: '#a1a5b7',
                            fontSize: '12px'
                        }
                    }
                },
                yaxis: {
                    labels: {
                        style: {
                            colors: '#a1a5b7',
                            fontSize: '12px'
                        },
                        formatter: function (val) {
                            return val.toFixed(1) + 'M';
                        }
                    }
                },
                fill: {
                     opacity: 1
                },
                colors: ['#009ef7'],
                grid: {
                    borderColor: '#e7e7e7',
                    strokeDashArray: 4,
                    yaxis: {
                        lines: {
                            show: true
                        }
                    }
                },
                tooltip: {
                    style: {
                        fontSize: '12px'
                    },
                    y: {
                        formatter: function(val) {
                            return val.toFixed(2) + ' triệu VNĐ';
                        }
                    }
                }
            };

            try {
                revenueChart = new ApexCharts(document.querySelector("#revenue_chart"), options);
                revenueChart.render().then(() => {
                    console.log('Revenue chart rendered successfully');
                }).catch((error) => {
                    console.error('Error rendering revenue chart:', error);
                });
            } catch (error) {
                console.error('Error creating revenue chart:', error);
                document.querySelector("#revenue_chart").innerHTML = `
                    <div class="d-flex align-items-center justify-content-center h-100">
                        <div class="text-center">
                            <i class="fas fa-exclamation-triangle fs-3x text-warning mb-3"></i>
                            <p class="text-gray-500">Lỗi tạo biểu đồ</p>
                            <small class="text-gray-400">${error.message}</small>
                        </div>
                    </div>
                `;
            }
        }

        function initTopProductsChart() {
            const chartData = @json($topProductsChart ?? []);

            console.log('Top Products Chart Data:', chartData);

            // Check if chart data exists and has required properties
            if (!chartData || !chartData.data || !chartData.categories) {
                console.error('Top products chart data is missing or invalid:', chartData);

                document.querySelector("#top_products_chart").innerHTML = `
                    <div class="d-flex align-items-center justify-content-center h-100">
                        <div class="text-center">
                            <i class="fas fa-chart-bar fs-3x text-gray-300 mb-3"></i>
                            <p class="text-gray-500">Không có dữ liệu sản phẩm</p>
                            <small class="text-gray-400">Vui lòng kiểm tra dữ liệu bán hàng</small>
                        </div>
                    </div>
                `;
                return;
            }

            const options = {
                series: [{
                    name: chartData.series_name || 'Doanh thu',
                    data: chartData.data || []
                }],
                chart: {
                    type: 'bar',
                    height: 400,
                    toolbar: {
                        show: false
                    },
                    animations: {
                        enabled: true,
                        easing: 'easeinout',
                        speed: 800
                    }
                },
                plotOptions: {
                    bar: {
                        horizontal: true,
                        borderRadius: 4,
                        dataLabels: {
                            position: 'top'
                        }
                    }
                },
                dataLabels: {
                    enabled: true,
                    formatter: function (val) {
                        return chartData.type === 'quantity' ? val : val.toFixed(1) + 'M';
                    },
                    style: {
                        fontSize: '12px',
                        colors: ['#304758']
                    }
                },
                xaxis: {
                    categories: chartData.categories || [],
                    labels: {
                        style: {
                            colors: '#a1a5b7',
                            fontSize: '12px'
                        }
                    }
                },
                yaxis: {
                    labels: {
                        style: {
                            colors: '#a1a5b7',
                            fontSize: '12px'
                        }
                    }
                },
                colors: ['#50cd89'],
                grid: {
                    borderColor: '#e7e7e7',
                    strokeDashArray: 4
                },
                tooltip: {
                    y: {
                        formatter: function(val) {
                            return chartData.type === 'quantity' ?
                                val + ' sản phẩm' :
                                val.toFixed(2) + ' triệu VNĐ';
                        }
                    }
                }
            };

            try {
                topProductsChart = new ApexCharts(document.querySelector("#top_products_chart"), options);
                topProductsChart.render().then(() => {
                    console.log('Top products chart rendered successfully');
                }).catch((error) => {
                    console.error('Error rendering top products chart:', error);
                });
            } catch (error) {
                console.error('Error creating top products chart:', error);
                document.querySelector("#top_products_chart").innerHTML = `
                    <div class="d-flex align-items-center justify-content-center h-100">
                        <div class="text-center">
                            <i class="fas fa-exclamation-triangle fs-3x text-warning mb-3"></i>
                            <p class="text-gray-500">Lỗi tạo biểu đồ</p>
                            <small class="text-gray-400">${error.message}</small>
                        </div>
                    </div>
                `;
            }
        }

        function updateRevenueChart(period) {
            console.log('Updating revenue chart for period:', period);

            // Show loading state
            showChartLoading('revenue');
            setDropdownLoading('revenue-period-select', true);

            fetch(`{{ route('admin.dashboard.revenue-data') }}?period=${period}`)
                .then(response => {
                    console.log('Response status:', response.status);
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Revenue chart data received:', data);

                    if (data.success && data.data) {
                        if (revenueChart) {
                            revenueChart.updateOptions({
                                series: [{
                                    name: data.data.series_name || 'Doanh thu',
                                    data: data.data.data || []
                                }],
                                xaxis: {
                                    categories: data.data.categories || []
                                }
                            });
                            console.log('Revenue chart updated successfully');
                        } else {
                            console.error('Revenue chart instance not found');
                        }
                    } else {
                        console.error('Invalid data received:', data);
                        if (revenueChart) {
                            revenueChart.updateOptions({
                                noData: {
                                    text: 'Không có dữ liệu'
                                }
                            });
                        }
                    }
                })
                .catch(error => {
                    console.error('Error updating revenue chart:', error);
                    if (revenueChart) {
                        revenueChart.updateOptions({
                            noData: {
                                text: 'Lỗi tải dữ liệu: ' + error.message
                            }
                        });
                    }
                })
                .finally(() => {
                    // Hide loading state
                    hideChartLoading('revenue');
                    setDropdownLoading('revenue-period-select', false);
                });
        }

        function updateTopProductsChart(type, period = 'month') {
            console.log('Updating top products chart for type:', type, 'period:', period);

            // Show loading state
            showChartLoading('top-products');
            setDropdownLoading('top-products-type-select', true);
            setDropdownLoading('top-products-period-select', true);

            fetch(`{{ route('admin.dashboard.top-products-data') }}?type=${type}&period=${period}`)
                .then(response => {
                    console.log('Top products response status:', response.status);
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Top products chart data received:', data);

                    if (data.success && data.data) {
                        if (topProductsChart) {
                            topProductsChart.updateOptions({
                                series: [{
                                    name: data.data.series_name || 'Doanh thu',
                                    data: data.data.data || []
                                }],
                                xaxis: {
                                    categories: data.data.categories || []
                                },
                                dataLabels: {
                                    enabled: true,
                                    formatter: function (val) {
                                        return data.data.type === 'quantity' ? val : val.toFixed(1) + 'M';
                                    }
                                },
                                tooltip: {
                                    y: {
                                        formatter: function(val) {
                                            return data.data.type === 'quantity' ?
                                                val + ' sản phẩm' :
                                                val.toFixed(2) + ' triệu VNĐ';
                                        }
                                    }
                                }
                            });
                            console.log('Top products chart updated successfully');
                        } else {
                            console.error('Top products chart instance not found');
                        }
                    } else {
                        console.error('Invalid top products data received:', data);
                        if (topProductsChart) {
                            topProductsChart.updateOptions({
                                noData: {
                                    text: 'Không có dữ liệu'
                                }
                            });
                        }
                    }
                })
                .catch(error => {
                    console.error('Error updating top products chart:', error);
                    if (topProductsChart) {
                        topProductsChart.updateOptions({
                            noData: {
                                text: 'Lỗi tải dữ liệu: ' + error.message
                            }
                        });
                    }
                })
                .finally(() => {
                    // Hide loading state
                    hideChartLoading('top-products');
                    setDropdownLoading('top-products-type-select', false);
                    setDropdownLoading('top-products-period-select', false);
                });
        }
    </script>
@endsection
