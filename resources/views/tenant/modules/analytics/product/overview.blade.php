@extends('admin.layouts.tenant-app')

@section('title', 'Phân Tích Hàng Hóa - Tổng Quan')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Phân Tích Hàng Hóa - Tổng Quan</h1>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.analytics.product.overview') }}" class="row g-3">
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
                    <small class="text-muted">Giá trị TB/đơn: {{ number_format($metrics['avg_order_value'], 0) }} ₫</small>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title text-muted">Số Hóa Đơn</h6>
                    <h3 class="mb-2">{{ number_format($metrics['total_orders']) }}</h3>
                    <small class="text-muted">Doanh thu/KH: {{ number_format($metrics['revenue_per_customer'], 0) }} ₫</small>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title text-muted">Khách Hàng Duy Nhất</h6>
                    <h3 class="mb-2">{{ number_format($metrics['unique_customers']) }}</h3>
                    <small class="text-muted">Khách hàng mua hàng</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Slow Moving Items -->
    <div class="row mb-4">
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Hàng Chậm Bán</h5>
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
                            @forelse($slowMovingItems as $item)
                                <tr>
                                    <td>{{ $item->product_name }}</td>
                                    <td class="text-end">{{ $item->current_stock }}</td>
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

        <!-- Dead Stock -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Hàng Tồn Kho Chết</h5>
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
                            @forelse($deadStockItems as $item)
                                <tr>
                                    <td>{{ $item->product_name }}</td>
                                    <td class="text-end">{{ $item->current_stock }}</td>
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
    </div>

    <!-- Bundle Suggestions -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Gợi Ý Combo Sản Phẩm</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead>
                    <tr>
                        <th>Sản Phẩm 1</th>
                        <th>Sản Phẩm 2</th>
                        <th class="text-end">Tần Suất Co-purchase</th>
                        <th class="text-end">Điểm Gợi Ý</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bundleSuggestions as $bundle)
                        <tr>
                            <td>{{ $bundle->product_name_1 }}</td>
                            <td>{{ $bundle->product_name_2 }}</td>
                            <td class="text-end">{{ number_format($bundle->co_purchase_frequency, 2) }}</td>
                            <td class="text-end">
                                <span class="badge bg-success">{{ $bundle->recommendation_score }}/100</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">Không có dữ liệu</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

