@extends('admin.layouts.tenant-app')

@section('title', 'Phân Tích Kinh Doanh - Tổng Quan')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Phân Tích Kinh Doanh - Tổng Quan</h1>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.analytics.business.overview') }}" class="row g-3">
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
                    <h6 class="card-title text-muted">Doanh Thu</h6>
                    <h3 class="mb-2">{{ number_format($metrics['total_revenue'], 0) }} ₫</h3>
                    <small class="text-muted">TB/ngày: {{ number_format($metrics['avg_revenue_per_day'], 0) }} ₫</small>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title text-muted">Lợi Nhuận Gộp</h6>
                    <h3 class="mb-2">{{ number_format($metrics['total_profit'], 0) }} ₫</h3>
                    <small class="text-muted">TB/ngày: {{ number_format($metrics['avg_profit_per_day'], 0) }} ₫</small>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title text-muted">Tỷ Lệ Lợi Nhuận</h6>
                    <h3 class="mb-2">{{ number_format($metrics['profit_margin'], 2) }}%</h3>
                    <small class="text-muted">Lợi nhuận / Doanh thu</small>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title text-muted">Số Hóa Đơn</h6>
                    <h3 class="mb-2">{{ number_format($metrics['total_orders']) }}</h3>
                    <small class="text-muted">Giá trị TB/đơn: {{ number_format($metrics['avg_order_value'], 0) }} ₫</small>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title text-muted">Khách Hàng Duy Nhất</h6>
                    <h3 class="mb-2">{{ number_format($metrics['unique_customers']) }}</h3>
                    <small class="text-muted">Doanh thu/KH: {{ number_format($metrics['revenue_per_customer'], 0) }} ₫</small>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title text-muted">Tổng Giá Vốn</h6>
                    <h3 class="mb-2">{{ number_format($metrics['total_cogs'], 0) }} ₫</h3>
                    <small class="text-muted">% Doanh thu: {{ number_format(($metrics['total_cogs'] / $metrics['total_revenue']) * 100, 2) }}%</small>
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
                    label: 'Lợi Nhuận',
                    data: chartData.map(d => d.profit),
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

