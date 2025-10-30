@extends('admin.layouts.tenant-app')

@section('title', 'Phân Tích Kinh Doanh')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Phân Tích Kinh Doanh</h1>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.report.sales') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Từ ngày</label>
                    <input type="date" name="from_date" class="form-control" value="{{ $filters['from_date'] }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Đến ngày</label>
                    <input type="date" name="to_date" class="form-control" value="{{ $filters['to_date'] }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Chi nhánh</label>
                    <select name="branch_shop_id" class="form-select">
                        <option value="">Tất cả chi nhánh</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" @selected($filters['branch_shop_id'] == $branch->id)>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Lọc</button>
                </div>
            </form>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="row mb-4">
        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title text-muted">Số Hóa Đơn</h6>
                    <h3 class="mb-2">{{ number_format($kpi['invoices']['total']) }}</h3>
                    <small class="text-muted">TB/ngày: {{ number_format($kpi['invoices']['avg_per_day'], 2) }}</small>
                    <div class="mt-2">
                        <span class="badge @if($kpi['invoices']['change_vs_prev'] >= 0) bg-success @else bg-danger @endif">
                            {{ $kpi['invoices']['change_vs_prev'] >= 0 ? '+' : '' }}{{ number_format($kpi['invoices']['change_vs_prev'], 2) }}%
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title text-muted">Doanh Thu</h6>
                    <h3 class="mb-2">{{ number_format($kpi['revenue']['total'], 0) }} ₫</h3>
                    <small class="text-muted">TB/ngày: {{ number_format($kpi['revenue']['avg_per_day'], 0) }} ₫</small>
                    <div class="mt-2">
                        <span class="badge @if($kpi['revenue']['change_vs_prev'] >= 0) bg-success @else bg-danger @endif">
                            {{ $kpi['revenue']['change_vs_prev'] >= 0 ? '+' : '' }}{{ number_format($kpi['revenue']['change_vs_prev'], 2) }}%
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title text-muted">Giá Trị Trả</h6>
                    <h3 class="mb-2">{{ number_format($kpi['returns']['total'], 0) }} ₫</h3>
                    <small class="text-muted">TB/ngày: {{ number_format($kpi['returns']['avg_per_day'], 0) }} ₫</small>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title text-muted">Doanh Thu Thuần</h6>
                    <h3 class="mb-2">{{ number_format($kpi['net_revenue']['total'], 0) }} ₫</h3>
                    <small class="text-muted">TB/ngày: {{ number_format($kpi['net_revenue']['avg_per_day'], 0) }} ₫</small>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title text-muted">Tổng Giá Vốn</h6>
                    <h3 class="mb-2">{{ number_format($kpi['total_cost']['total'], 0) }} ₫</h3>
                    <small class="text-muted">TB/ngày: {{ number_format($kpi['total_cost']['avg_per_day'], 0) }} ₫</small>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title text-muted">Lợi Nhuận Gộp</h6>
                    <h3 class="mb-2">{{ number_format($kpi['gross_profit']['total'], 0) }} ₫</h3>
                    <small class="text-muted">TB/ngày: {{ number_format($kpi['gross_profit']['avg_per_day'], 0) }} ₫</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Xu Hướng Doanh Thu</h5>
        </div>
        <div class="card-body">
            <canvas id="revenueChart" height="80"></canvas>
        </div>
    </div>

    <!-- Branch Table -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Phân Tích Theo Chi Nhánh</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Chi Nhánh</th>
                        <th class="text-end">Doanh Thu</th>
                        <th class="text-end">Trả Hàng</th>
                        <th class="text-end">Doanh Thu Thuần</th>
                        <th class="text-end">Giá Vốn</th>
                        <th class="text-end">Lợi Nhuận</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($branchTable as $branch)
                        <tr>
                            <td>{{ $branch['branch_name'] }}</td>
                            <td class="text-end">{{ number_format($branch['revenue'], 0) }} ₫</td>
                            <td class="text-end">{{ number_format($branch['return_amount'], 0) }} ₫</td>
                            <td class="text-end">{{ number_format($branch['net_revenue'], 0) }} ₫</td>
                            <td class="text-end">{{ number_format($branch['total_cost'], 0) }} ₫</td>
                            <td class="text-end">{{ number_format($branch['gross_profit'], 0) }} ₫</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">Không có dữ liệu</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Top Rankings -->
    <div class="row">
        <!-- Top Categories -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Top 10 Nhóm Hàng</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Nhóm Hàng</th>
                                <th class="text-end">Doanh Thu</th>
                                <th class="text-end">TB/Đơn</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topCategories as $category)
                                <tr>
                                    <td>{{ $category['name'] }}</td>
                                    <td class="text-end">{{ number_format($category['revenue'], 0) }} ₫</td>
                                    <td class="text-end">{{ number_format($category['avg_per_invoice'], 0) }} ₫</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">Không có dữ liệu</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Top Products -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Top 10 Hàng Hóa</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Hàng Hóa</th>
                                <th class="text-end">Doanh Thu</th>
                                <th class="text-end">TB/Đơn</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topProducts as $product)
                                <tr>
                                    <td>{{ $product['name'] }}</td>
                                    <td class="text-end">{{ number_format($product['revenue'], 0) }} ₫</td>
                                    <td class="text-end">{{ number_format($product['avg_per_invoice'], 0) }} ₫</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">Không có dữ liệu</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Top Customers -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Top 10 Khách Hàng</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Khách Hàng</th>
                                <th class="text-end">Doanh Thu</th>
                                <th class="text-end">TB/Đơn</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topCustomers as $customer)
                                <tr>
                                    <td>{{ $customer['name'] }}</td>
                                    <td class="text-end">{{ number_format($customer['revenue'], 0) }} ₫</td>
                                    <td class="text-end">{{ number_format($customer['avg_per_invoice'], 0) }} ₫</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">Không có dữ liệu</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Top Channels -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Top 10 Kênh Bán</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Kênh Bán</th>
                                <th class="text-end">Doanh Thu</th>
                                <th class="text-end">TB/Đơn</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topChannels as $channel)
                                <tr>
                                    <td>{{ $channel['channel'] }}</td>
                                    <td class="text-end">{{ number_format($channel['revenue'], 0) }} ₫</td>
                                    <td class="text-end">{{ number_format($channel['avg_per_invoice'], 0) }} ₫</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">Không có dữ liệu</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const chartData = @json($chartData);
    
    const ctx = document.getElementById('revenueChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.map(d => d.date),
            datasets: [
                {
                    label: 'Doanh Thu',
                    data: chartData.map(d => d.revenue),
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13, 110, 253, 0.1)',
                    tension: 0.4,
                },
                {
                    label: 'Trả Hàng',
                    data: chartData.map(d => d.return_amount),
                    borderColor: '#dc3545',
                    backgroundColor: 'rgba(220, 53, 69, 0.1)',
                    tension: 0.4,
                },
                {
                    label: 'Doanh Thu Thuần',
                    data: chartData.map(d => d.net_revenue),
                    borderColor: '#198754',
                    backgroundColor: 'rgba(25, 135, 84, 0.1)',
                    tension: 0.4,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'top',
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return new Intl.NumberFormat('vi-VN', {
                                style: 'currency',
                                currency: 'VND',
                                minimumFractionDigits: 0
                            }).format(value);
                        }
                    }
                }
            }
        }
    });
</script>
@endpush
@endsection

