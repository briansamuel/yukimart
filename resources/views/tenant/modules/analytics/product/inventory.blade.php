@extends('admin.layouts.tenant-app')

@section('title', 'Phân Tích Tồn Kho')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Phân Tích Tồn Kho</h1>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.analytics.product.inventory') }}" class="row g-3">
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
                    <h6 class="card-title text-muted">Giá Trị Tồn Kho</h6>
                    <h3 class="mb-2">{{ number_format($metrics['total_inventory_value'], 0) }} ₫</h3>
                    <small class="text-muted">Tổng giá trị</small>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title text-muted">Tổng SKU</h6>
                    <h3 class="mb-2">{{ number_format($metrics['total_skus']) }}</h3>
                    <small class="text-muted">Số lượng SKU</small>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title text-muted">Hàng Sắp Hết</h6>
                    <h3 class="mb-2">{{ number_format($metrics['low_stock_items']) }}</h3>
                    <small class="text-muted">{{ number_format($metrics['low_stock_percentage'], 1) }}% tổng SKU</small>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title text-muted">Hết Hàng</h6>
                    <h3 class="mb-2">{{ number_format($metrics['out_of_stock_items']) }}</h3>
                    <small class="text-muted">Cần nhập hàng</small>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title text-muted">Tốc Độ Quay Vòng</h6>
                    <h3 class="mb-2">{{ number_format($metrics['avg_turnover_rate'], 2) }}</h3>
                    <small class="text-muted">Trung bình</small>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title text-muted">Giá Trị Hàng Sắp Hết</h6>
                    <h3 class="mb-2">{{ number_format($metrics['low_stock_value'], 0) }} ₫</h3>
                    <small class="text-muted">Cần theo dõi</small>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title text-muted">Giá Trị Tồn Dư</h6>
                    <h3 class="mb-2">{{ number_format($metrics['overstock_value'], 0) }} ₫</h3>
                    <small class="text-muted">Cần xử lý</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Xu Hướng Tồn Kho</h5>
        </div>
        <div class="card-body">
            <canvas id="inventoryChart" height="80"></canvas>
        </div>
    </div>

    <!-- Low Stock Items -->
    <div class="row mb-4">
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Hàng Sắp Hết (Top 20)</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Hàng Hóa</th>
                                <th class="text-end">Tồn Kho</th>
                                <th class="text-end">Giá Trị</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($lowStockItems as $item)
                                <tr>
                                    <td>{{ $item->product_name }}</td>
                                    <td class="text-end">
                                        <span class="badge bg-warning">{{ $item->current_stock }}</span>
                                    </td>
                                    <td class="text-end">{{ number_format($item->stock_value, 0) }} ₫</td>
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

        <!-- Overstock Items -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Hàng Tồn Dư (Top 20)</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Hàng Hóa</th>
                                <th class="text-end">Tồn Kho</th>
                                <th class="text-end">Ngày Không Bán</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($overstockItems as $item)
                                <tr>
                                    <td>{{ $item->product_name }}</td>
                                    <td class="text-end">
                                        <span class="badge bg-danger">{{ $item->current_stock }}</span>
                                    </td>
                                    <td class="text-end">{{ $item->days_without_sale }} ngày</td>
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
    
    const ctx = document.getElementById('inventoryChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.map(d => d.date),
            datasets: [
                {
                    label: 'Giá Trị Tồn Kho',
                    data: chartData.map(d => d.total_value),
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13, 110, 253, 0.1)',
                    tension: 0.4,
                },
                {
                    label: 'Giá Trị Hàng Sắp Hết',
                    data: chartData.map(d => d.low_stock_value),
                    borderColor: '#ffc107',
                    backgroundColor: 'rgba(255, 193, 7, 0.1)',
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

