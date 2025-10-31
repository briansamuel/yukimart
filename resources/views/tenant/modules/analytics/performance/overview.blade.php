@extends('admin.layouts.tenant-app')

@section('title', 'Phân Tích Hiệu Suất Nhân Viên')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Phân Tích Hiệu Suất Nhân Viên</h1>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.analytics.performance.overview') }}" class="row g-3">
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
                    <h6 class="card-title text-muted">Tổng Doanh Thu</h6>
                    <h3 class="mb-2">{{ number_format($metrics['total_revenue'], 0) }} ₫</h3>
                    <small class="text-muted">TB/NV: {{ number_format($metrics['avg_revenue_per_staff'], 0) }} ₫</small>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title text-muted">Tổng Lợi Nhuận</h6>
                    <h3 class="mb-2">{{ number_format($metrics['total_profit'], 0) }} ₫</h3>
                    <small class="text-muted">TB/NV: {{ number_format($metrics['avg_profit_per_staff'], 0) }} ₫</small>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title text-muted">Tổng Hóa Đơn</h6>
                    <h3 class="mb-2">{{ number_format($metrics['total_orders']) }}</h3>
                    <small class="text-muted">TB/NV: {{ number_format($metrics['avg_orders_per_staff'], 1) }}</small>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title text-muted">Số Nhân Viên</h6>
                    <h3 class="mb-2">{{ number_format($metrics['staff_count']) }}</h3>
                    <small class="text-muted">Nhân viên bán hàng</small>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title text-muted">Khách Hàng Duy Nhất</h6>
                    <h3 class="mb-2">{{ number_format($metrics['unique_customers']) }}</h3>
                    <small class="text-muted">Tổng khách hàng</small>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title text-muted">Khách Hàng Mới</h6>
                    <h3 class="mb-2">{{ number_format($metrics['new_customers']) }}</h3>
                    <small class="text-muted">Khách hàng mới</small>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title text-muted">Tỷ Lệ Lợi Nhuận</h6>
                    <h3 class="mb-2">{{ number_format($metrics['profit_margin'], 2) }}%</h3>
                    <small class="text-muted">Trung bình</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Performers -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Top 10 Nhân Viên Xuất Sắc</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nhân Viên</th>
                        <th class="text-end">Doanh Thu</th>
                        <th class="text-end">Lợi Nhuận</th>
                        <th class="text-end">Số Đơn</th>
                        <th class="text-end">Tỷ Lệ LN</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topPerformers as $index => $performer)
                        <tr>
                            <td>
                                @if($index == 0)
                                    <i class="fas fa-trophy text-warning"></i>
                                @elseif($index == 1)
                                    <i class="fas fa-medal text-secondary"></i>
                                @elseif($index == 2)
                                    <i class="fas fa-medal text-danger"></i>
                                @else
                                    {{ $index + 1 }}
                                @endif
                            </td>
                            <td>{{ $performer['staff_name'] }}</td>
                            <td class="text-end">{{ number_format($performer['revenue'], 0) }} ₫</td>
                            <td class="text-end">{{ number_format($performer['profit'], 0) }} ₫</td>
                            <td class="text-end">{{ number_format($performer['orders']) }}</td>
                            <td class="text-end">
                                <span class="badge @if($performer['profit_margin'] >= 20) bg-success @elseif($performer['profit_margin'] >= 10) bg-warning @else bg-danger @endif">
                                    {{ number_format($performer['profit_margin'], 2) }}%
                                </span>
                            </td>
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

    <!-- Staff Performance Chart -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Doanh Thu Theo Nhân Viên</h5>
        </div>
        <div class="card-body">
            <canvas id="staffChart" height="80"></canvas>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const chartData = @json($chartData);
    
    const ctx = document.getElementById('staffChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: chartData.map(d => d.staff_name),
            datasets: [
                {
                    label: 'Doanh Thu',
                    data: chartData.map(d => d.total_revenue),
                    backgroundColor: 'rgba(13, 110, 253, 0.5)',
                    borderColor: '#0d6efd',
                    borderWidth: 1,
                },
                {
                    label: 'Lợi Nhuận',
                    data: chartData.map(d => d.total_profit),
                    backgroundColor: 'rgba(25, 135, 84, 0.5)',
                    borderColor: '#198754',
                    borderWidth: 1,
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

