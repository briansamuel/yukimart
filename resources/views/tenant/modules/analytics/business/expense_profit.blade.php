@extends('admin.layouts.tenant-app')

@section('title', 'Phân Tích Chi Phí & Lợi Nhuận')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Phân Tích Chi Phí & Lợi Nhuận</h1>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.analytics.business.expense-profit') }}" class="row g-3">
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
        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title text-muted">Doanh Thu</h6>
                    <h3 class="mb-2">{{ number_format($metrics['total_revenue'], 0) }} ₫</h3>
                    <small class="text-muted">Tổng doanh thu</small>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title text-muted">Giá Vốn</h6>
                    <h3 class="mb-2">{{ number_format($metrics['total_cogs'], 0) }} ₫</h3>
                    <small class="text-muted">{{ number_format($metrics['cogs_percentage'], 1) }}% doanh thu</small>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title text-muted">Lợi Nhuận</h6>
                    <h3 class="mb-2">{{ number_format($metrics['total_profit'], 0) }} ₫</h3>
                    <small class="text-muted">TB/ngày: {{ number_format($metrics['avg_profit_per_day'], 0) }} ₫</small>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title text-muted">Tỷ Lệ Lợi Nhuận</h6>
                    <h3 class="mb-2">{{ number_format($metrics['profit_margin'], 2) }}%</h3>
                    <small class="text-muted">Lợi nhuận / Doanh thu</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Xu Hướng Giá Vốn & Lợi Nhuận</h5>
        </div>
        <div class="card-body">
            <canvas id="expenseProfitChart" height="80"></canvas>
        </div>
    </div>

    <!-- Branch Breakdown -->
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
                        <th class="text-end">Giá Vốn</th>
                        <th class="text-end">Lợi Nhuận</th>
                        <th class="text-end">Tỷ Lệ LN</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($branchBreakdown as $branch)
                        <tr>
                            <td>{{ $branch['branch_name'] }}</td>
                            <td class="text-end">{{ number_format($branch['revenue'], 0) }} ₫</td>
                            <td class="text-end">{{ number_format($branch['cogs'], 0) }} ₫</td>
                            <td class="text-end">{{ number_format($branch['profit'], 0) }} ₫</td>
                            <td class="text-end">
                                <span class="badge @if($branch['profit_margin'] >= 20) bg-success @elseif($branch['profit_margin'] >= 10) bg-warning @else bg-danger @endif">
                                    {{ number_format($branch['profit_margin'], 2) }}%
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">Không có dữ liệu</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const chartData = @json($chartData);
    
    const ctx = document.getElementById('expenseProfitChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.map(d => d.date),
            datasets: [
                {
                    label: 'Giá Vốn',
                    data: chartData.map(d => d.cogs),
                    borderColor: '#dc3545',
                    backgroundColor: 'rgba(220, 53, 69, 0.1)',
                    tension: 0.4,
                },
                {
                    label: 'Lợi Nhuận',
                    data: chartData.map(d => d.profit),
                    borderColor: '#198754',
                    backgroundColor: 'rgba(25, 135, 84, 0.1)',
                    tension: 0.4,
                },
                {
                    label: 'Tỷ Lệ LN (%)',
                    data: chartData.map(d => d.profit_margin),
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13, 110, 253, 0.1)',
                    tension: 0.4,
                    yAxisID: 'y1',
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
                    type: 'linear',
                    display: true,
                    position: 'left',
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
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    beginAtZero: true,
                    grid: {
                        drawOnChartArea: false,
                    },
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                }
            }
        }
    });
</script>
@endpush
@endsection

