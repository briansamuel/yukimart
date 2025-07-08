@extends('admin.index')
@section('page-header', 'Supplier Detail')
@section('page-sub_header', 'Chi tiết nhà cung cấp')
@section('style')
@endsection

@section('content')
    <!--begin::Row-->
    <div class="row g-5 g-xl-8">
        <!--begin::Col-->
        <div class="col-xl-12">
            <!--begin::Card-->
            <div class="card card-xl-stretch mb-5 mb-xl-8">
                <!--begin::Header-->
                <div class="card-header border-0 pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bolder fs-3 mb-1">{{ $supplier->name }}</span>
                        <span class="text-muted mt-1 fw-bold fs-7">{{ $supplier->code ?? 'N/A' }}</span>
                    </h3>
                    <div class="card-toolbar">
                        <a href="{{ route('admin.supplier.list') }}" class="btn btn-sm btn-light me-2">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                        <a href="{{ route('admin.supplier.edit', $supplier->id) }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-edit"></i> Chỉnh sửa
                        </a>
                    </div>
                </div>
                <!--end::Header-->
                <!--begin::Body-->
                <div class="card-body py-3">
                    <!--begin::Row-->
                    <div class="row mb-8">
                        <!--begin::Col-->
                        <div class="col-lg-6">
                            <!--begin::Details-->
                            <div class="card card-flush h-lg-100">
                                <!--begin::Card header-->
                                <div class="card-header pt-7">
                                    <!--begin::Title-->
                                    <h3 class="card-title align-items-start flex-column">
                                        <span class="card-label fw-bolder text-gray-800">Thông tin cơ bản</span>
                                    </h3>
                                    <!--end::Title-->
                                </div>
                                <!--end::Card header-->
                                <!--begin::Card body-->
                                <div class="card-body pt-0">
                                    <!--begin::Table-->
                                    <table class="table align-middle table-row-dashed fs-6 gy-4 mb-0">
                                        <tbody>
                                            <tr>
                                                <td class="min-w-175px text-muted">Mã nhà cung cấp:</td>
                                                <td class="fw-bolder text-end">{{ $supplier->code ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted">Tên nhà cung cấp:</td>
                                                <td class="fw-bolder text-end">{{ $supplier->name }}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted">Công ty:</td>
                                                <td class="fw-bolder text-end">{{ $supplier->company ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted">Mã số thuế:</td>
                                                <td class="fw-bolder text-end">{{ $supplier->tax_code ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted">Điện thoại:</td>
                                                <td class="fw-bolder text-end">{{ $supplier->phone ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted">Email:</td>
                                                <td class="fw-bolder text-end">{{ $supplier->email ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted">Chi nhánh:</td>
                                                <td class="fw-bolder text-end">{{ $supplier->branch->name ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted">Nhóm:</td>
                                                <td class="fw-bolder text-end">{{ $supplier->group ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted">Trạng thái:</td>
                                                <td class="text-end">{!! $supplier->status_badge !!}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted">Ngày tạo:</td>
                                                <td class="fw-bolder text-end">{{ $supplier->created_at->format('d/m/Y H:i') }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <!--end::Table-->
                                </div>
                                <!--end::Card body-->
                            </div>
                            <!--end::Details-->
                        </div>
                        <!--end::Col-->
                        <!--begin::Col-->
                        <div class="col-lg-6">
                            <!--begin::Statistics-->
                            <div class="card card-flush h-lg-100">
                                <!--begin::Card header-->
                                <div class="card-header pt-7">
                                    <!--begin::Title-->
                                    <h3 class="card-title align-items-start flex-column">
                                        <span class="card-label fw-bolder text-gray-800">Thống kê</span>
                                    </h3>
                                    <!--end::Title-->
                                </div>
                                <!--end::Card header-->
                                <!--begin::Card body-->
                                <div class="card-body pt-0">
                                    <!--begin::Stats-->
                                    <div class="row g-6 g-xl-9 mb-6 mb-xl-9">
                                        <!--begin::Col-->
                                        <div class="col-sm-6 col-xl-6">
                                            <!--begin::Card widget 2-->
                                            <div class="card h-lg-100">
                                                <!--begin::Body-->
                                                <div class="card-body d-flex justify-content-between align-items-start flex-column">
                                                    <!--begin::Icon-->
                                                    <div class="m-0">
                                                        <i class="fas fa-boxes fs-2hx text-gray-600"></i>
                                                    </div>
                                                    <!--end::Icon-->
                                                    <!--begin::Section-->
                                                    <div class="d-flex flex-column my-7">
                                                        <!--begin::Number-->
                                                        <span class="fw-semibold fs-3x text-gray-800 lh-1 ls-n2">{{ number_format($stats['total_imports']) }}</span>
                                                        <!--end::Number-->
                                                        <!--begin::Follower-->
                                                        <div class="m-0">
                                                            <span class="fw-semibold fs-6 text-gray-400">Lần nhập hàng</span>
                                                        </div>
                                                        <!--end::Follower-->
                                                    </div>
                                                    <!--end::Section-->
                                                </div>
                                                <!--end::Body-->
                                            </div>
                                            <!--end::Card widget 2-->
                                        </div>
                                        <!--end::Col-->
                                        <!--begin::Col-->
                                        <div class="col-sm-6 col-xl-6">
                                            <!--begin::Card widget 2-->
                                            <div class="card h-lg-100">
                                                <!--begin::Body-->
                                                <div class="card-body d-flex justify-content-between align-items-start flex-column">
                                                    <!--begin::Icon-->
                                                    <div class="m-0">
                                                        <i class="fas fa-dollar-sign fs-2hx text-gray-600"></i>
                                                    </div>
                                                    <!--end::Icon-->
                                                    <!--begin::Section-->
                                                    <div class="d-flex flex-column my-7">
                                                        <!--begin::Number-->
                                                        <span class="fw-semibold fs-3x text-gray-800 lh-1 ls-n2">{{ number_format($stats['total_import_value']) }}</span>
                                                        <!--end::Number-->
                                                        <!--begin::Follower-->
                                                        <div class="m-0">
                                                            <span class="fw-semibold fs-6 text-gray-400">Tổng giá trị nhập</span>
                                                        </div>
                                                        <!--end::Follower-->
                                                    </div>
                                                    <!--end::Section-->
                                                </div>
                                                <!--end::Body-->
                                            </div>
                                            <!--end::Card widget 2-->
                                        </div>
                                        <!--end::Col-->
                                    </div>
                                    <!--end::Stats-->
                                    <!--begin::Table-->
                                    <table class="table align-middle table-row-dashed fs-6 gy-4 mb-0">
                                        <tbody>
                                            <tr>
                                                <td class="min-w-175px text-muted">Tổng sản phẩm:</td>
                                                <td class="fw-bolder text-end">{{ number_format($stats['total_products']) }}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted">Sản phẩm đang bán:</td>
                                                <td class="fw-bolder text-end">{{ number_format($stats['active_products']) }}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted">Lần nhập gần nhất:</td>
                                                <td class="fw-bolder text-end">
                                                    @if($stats['last_import_date'])
                                                        {{ $stats['last_import_date']->format('d/m/Y H:i') }}
                                                    @else
                                                        Chưa có
                                                    @endif
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <!--end::Table-->
                                </div>
                                <!--end::Card body-->
                            </div>
                            <!--end::Statistics-->
                        </div>
                        <!--end::Col-->
                    </div>
                    <!--end::Row-->

                    @if($supplier->full_address)
                    <!--begin::Address-->
                    <div class="row mb-8">
                        <div class="col-lg-12">
                            <div class="card card-flush">
                                <div class="card-header pt-7">
                                    <h3 class="card-title align-items-start flex-column">
                                        <span class="card-label fw-bolder text-gray-800">Địa chỉ</span>
                                    </h3>
                                </div>
                                <div class="card-body pt-0">
                                    <p class="fs-6 fw-semibold text-gray-600 mb-7">{{ $supplier->full_address }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end::Address-->
                    @endif

                    @if($supplier->note)
                    <!--begin::Notes-->
                    <div class="row mb-8">
                        <div class="col-lg-12">
                            <div class="card card-flush">
                                <div class="card-header pt-7">
                                    <h3 class="card-title align-items-start flex-column">
                                        <span class="card-label fw-bolder text-gray-800">Ghi chú</span>
                                    </h3>
                                </div>
                                <div class="card-body pt-0">
                                    <p class="fs-6 fw-semibold text-gray-600 mb-7">{{ $supplier->note }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end::Notes-->
                    @endif

                    <!--begin::Recent Transactions-->
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card card-flush">
                                <div class="card-header pt-7">
                                    <h3 class="card-title align-items-start flex-column">
                                        <span class="card-label fw-bolder text-gray-800">Giao dịch gần đây</span>
                                        <span class="text-muted mt-1 fw-bold fs-7">10 giao dịch nhập hàng gần nhất</span>
                                    </h3>
                                    <div class="card-toolbar">
                                        <a href="{{ route('admin.inventory.transactions') }}?supplier_id={{ $supplier->id }}" class="btn btn-sm btn-light">
                                            Xem tất cả
                                        </a>
                                    </div>
                                </div>
                                <div class="card-body pt-0">
                                    <div class="table-responsive">
                                        <table class="table align-middle table-row-dashed fs-6 gy-4 mb-0">
                                            <thead>
                                                <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                                    <th>Ngày</th>
                                                    <th>Sản phẩm</th>
                                                    <th>Số lượng</th>
                                                    <th>Giá trị</th>
                                                    <th>Ghi chú</th>
                                                </tr>
                                            </thead>
                                            <tbody id="recent-transactions">
                                                <!-- Will be loaded via AJAX -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end::Recent Transactions-->
                </div>
                <!--end::Body-->
            </div>
            <!--end::Card-->
        </div>
        <!--end::Col-->
    </div>
    <!--end::Row-->
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            loadRecentTransactions();
        });

        function loadRecentTransactions() {
            $.ajax({
                url: '{{ route("admin.inventory.transactions.ajax") }}',
                method: 'GET',
                data: {
                    supplier_id: {{ $supplier->id }},
                    length: 10,
                    start: 0
                },
                success: function(response) {
                    let html = '';
                    if (response.data && response.data.length > 0) {
                        response.data.forEach(function(transaction) {
                            html += `
                                <tr>
                                    <td>${transaction.created_at}</td>
                                    <td>${transaction.product_info}</td>
                                    <td>${transaction.quantity}</td>
                                    <td>${transaction.total_value} VND</td>
                                    <td>${transaction.notes || '-'}</td>
                                </tr>
                            `;
                        });
                    } else {
                        html = '<tr><td colspan="5" class="text-center text-muted">Chưa có giao dịch nào</td></tr>';
                    }
                    $('#recent-transactions').html(html);
                },
                error: function() {
                    $('#recent-transactions').html('<tr><td colspan="5" class="text-center text-muted">Không thể tải dữ liệu</td></tr>');
                }
            });
        }
    </script>
@endsection
