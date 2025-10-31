@extends('admin.layouts.tenant-app')

@section('title', 'Phân Tích Công Nợ')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Phân Tích Công Nợ</h1>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.analytics.accounts-receivable.overview') }}" class="row g-3">
                <div class="col-md-4">
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
                <div class="col-md-4">
                    <label class="form-label">Trạng thái</label>
                    <select name="status" class="form-select">
                        <option value="">Tất cả trạng thái</option>
                        <option value="pending" @selected($filters['status'] == 'pending')>Chờ thanh toán</option>
                        <option value="partial" @selected($filters['status'] == 'partial')>Thanh toán một phần</option>
                        <option value="paid" @selected($filters['status'] == 'paid')>Đã thanh toán</option>
                        <option value="overdue" @selected($filters['status'] == 'overdue')>Quá hạn</option>
                        <option value="cancelled" @selected($filters['status'] == 'cancelled')>Đã hủy</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
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
                    <h6 class="card-title text-muted">Tổng Hóa Đơn</h6>
                    <h3 class="mb-2">{{ number_format($metrics['total_invoice_amount'], 0) }} ₫</h3>
                    <small class="text-muted">Tổng giá trị</small>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title text-muted">Đã Thu</h6>
                    <h3 class="mb-2">{{ number_format($metrics['total_paid_amount'], 0) }} ₫</h3>
                    <small class="text-muted">Tỷ lệ: {{ number_format($metrics['collection_rate'], 1) }}%</small>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title text-muted">Còn Nợ</h6>
                    <h3 class="mb-2">{{ number_format($metrics['total_outstanding'], 0) }} ₫</h3>
                    <small class="text-muted">{{ number_format($metrics['outstanding_percentage'], 1) }}% tổng</small>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3 mb-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title text-muted">Quá Hạn</h6>
                    <h3 class="mb-2 text-danger">{{ number_format($metrics['overdue_amount'], 0) }} ₫</h3>
                    <small class="text-muted">{{ number_format($metrics['overdue_count']) }} hóa đơn</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Breakdown -->
    <div class="row mb-4">
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Phân Loại Theo Trạng Thái</h5>
                </div>
                <div class="card-body">
                    @foreach($receivablesByStatus as $status => $data)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span>
                                    @if($status == 'pending') Chờ thanh toán
                                    @elseif($status == 'partial') Thanh toán một phần
                                    @elseif($status == 'paid') Đã thanh toán
                                    @elseif($status == 'overdue') Quá hạn
                                    @else Đã hủy
                                    @endif
                                    ({{ $data['count'] }})
                                </span>
                                <strong>{{ number_format($data['amount'], 0) }} ₫</strong>
                            </div>
                            <div class="progress">
                                <div class="progress-bar 
                                    @if($status == 'paid') bg-success
                                    @elseif($status == 'overdue') bg-danger
                                    @elseif($status == 'partial') bg-warning
                                    @else bg-info
                                    @endif" 
                                    style="width: {{ ($data['amount'] / $metrics['total_invoice_amount']) * 100 }}%">
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Aging Analysis -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Phân Tích Độ Tuổi Công Nợ</h5>
                </div>
                <div class="card-body">
                    @foreach($agingAnalysis as $range => $data)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span>{{ $range }} ngày ({{ $data['count'] }})</span>
                                <strong>{{ number_format($data['amount'], 0) }} ₫</strong>
                            </div>
                            <div class="progress">
                                <div class="progress-bar 
                                    @if($range == '0-30') bg-success
                                    @elseif($range == '31-60') bg-info
                                    @elseif($range == '61-90') bg-warning
                                    @else bg-danger
                                    @endif" 
                                    style="width: {{ $metrics['total_outstanding'] > 0 ? ($data['amount'] / $metrics['total_outstanding']) * 100 : 0 }}%">
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Overdue Receivables -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Công Nợ Quá Hạn (Top 20)</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Khách Hàng</th>
                        <th class="text-end">Số Tiền</th>
                        <th class="text-end">Ngày Đến Hạn</th>
                        <th class="text-end">Số Ngày Quá Hạn</th>
                        <th class="text-end">Trạng Thái</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($overdueReceivables as $receivable)
                        <tr>
                            <td>{{ $receivable->customer ? $receivable->customer->name : 'N/A' }}</td>
                            <td class="text-end">{{ number_format($receivable->outstanding_amount, 0) }} ₫</td>
                            <td class="text-end">{{ $receivable->due_date->format('d/m/Y') }}</td>
                            <td class="text-end">
                                <span class="badge 
                                    @if($receivable->days_overdue > 90) bg-danger
                                    @elseif($receivable->days_overdue > 60) bg-warning
                                    @else bg-info
                                    @endif">
                                    {{ $receivable->days_overdue }} ngày
                                </span>
                            </td>
                            <td class="text-end">
                                <span class="badge bg-danger">Quá hạn</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">Không có công nợ quá hạn</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

