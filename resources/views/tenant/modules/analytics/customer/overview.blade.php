@extends('admin.layouts.tenant-app')

@section('title', 'Phân Tích Khách Hàng - Tổng Quan')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Phân Tích Khách Hàng - Tổng Quan</h1>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.analytics.customer.overview') }}" class="row g-3">
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
                    <h6 class="card-title text-muted">Tổng Khách Hàng</h6>
                    <h3 class="mb-2">{{ number_format($metrics['total_customers']) }}</h3>
                    <small class="text-muted">Khách hàng duy nhất</small>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title text-muted">Khách Hàng Mới</h6>
                    <h3 class="mb-2">{{ number_format($metrics['new_customers']) }}</h3>
                    <small class="text-muted">{{ number_format($metrics['new_customer_percentage'], 1) }}% tổng KH</small>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title text-muted">Khách Hàng Quay Lại</h6>
                    <h3 class="mb-2">{{ number_format($metrics['returning_customers']) }}</h3>
                    <small class="text-muted">{{ number_format($metrics['returning_customer_percentage'], 1) }}% tổng KH</small>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title text-muted">Khách Hàng VIP</h6>
                    <h3 class="mb-2">{{ number_format($metrics['vip_customers']) }}</h3>
                    <small class="text-muted">{{ number_format($metrics['vip_customer_percentage'], 1) }}% tổng KH</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue by Customer Type -->
    <div class="row mb-4">
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Doanh Thu Theo Loại Khách Hàng</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Khách Hàng Mới</span>
                            <strong>{{ number_format($metrics['new_customer_revenue'], 0) }} ₫</strong>
                        </div>
                        <div class="progress">
                            <div class="progress-bar" style="width: {{ ($metrics['new_customer_revenue'] / $metrics['total_revenue']) * 100 }}%"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Khách Hàng Quay Lại</span>
                            <strong>{{ number_format($metrics['returning_customer_revenue'], 0) }} ₫</strong>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-success" style="width: {{ ($metrics['returning_customer_revenue'] / $metrics['total_revenue']) * 100 }}%"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Khách Lẻ</span>
                            <strong>{{ number_format($metrics['walkin_revenue'], 0) }} ₫</strong>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-warning" style="width: {{ ($metrics['walkin_revenue'] / $metrics['total_revenue']) * 100 }}%"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Khách Hàng VIP</span>
                            <strong>{{ number_format($metrics['vip_revenue'], 0) }} ₫</strong>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-danger" style="width: {{ ($metrics['vip_revenue'] / $metrics['total_revenue']) * 100 }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Chỉ Số Khách Hàng</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Giá Trị Trung Bình/Khách Hàng</span>
                            <strong>{{ number_format($metrics['avg_customer_lifetime_value'], 0) }} ₫</strong>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Tổng Doanh Thu</span>
                            <strong>{{ number_format($metrics['total_revenue'], 0) }} ₫</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Retention Stats -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Phân Tích Retention Cohort</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead>
                    <tr>
                        <th>Cohort</th>
                        <th class="text-end">Kích Thước</th>
                        <th class="text-end">Month 0</th>
                        <th class="text-end">Month 1</th>
                        <th class="text-end">Month 3</th>
                        <th class="text-end">Month 6</th>
                        <th class="text-end">Month 12</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($retentionStats as $stat)
                        <tr>
                            <td>{{ $stat->cohort_month }}</td>
                            <td class="text-end">{{ $stat->cohort_size }}</td>
                            <td class="text-end">{{ number_format($stat->month_0_retention_rate, 1) }}%</td>
                            <td class="text-end">{{ number_format($stat->month_1_retention_rate, 1) }}%</td>
                            <td class="text-end">{{ number_format($stat->month_3_retention_rate, 1) }}%</td>
                            <td class="text-end">{{ number_format($stat->month_6_retention_rate, 1) }}%</td>
                            <td class="text-end">{{ number_format($stat->month_12_retention_rate, 1) }}%</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">Không có dữ liệu</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

