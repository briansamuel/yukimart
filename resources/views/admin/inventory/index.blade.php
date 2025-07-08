@extends('admin.index')

@section('title', 'Quản Lý Tồn Kho')
@section('page-header', 'Quản Lý Tồn Kho')
@section('page-sub_header', 'Tồn kho')
@section('content')
    <!--begin::Statistics Cards-->
    <div class="row g-5 g-xl-10 mb-5 mb-xl-10">
        <!--begin::Total Products-->
        <div class="col-md-6 col-lg-6 col-xl-3 col-xxl-3 mb-md-5 mb-xl-10">
            <div class="card card-flush bgi-no-repeat bgi-size-contain bgi-position-x-end h-md-75 mb-5 mb-xl-10"
                style="background-color: #F1416C;background-image:url('{{ asset('admin-assets/assets/media/patterns/vector-1.png') }}')">
                <div class="card-header pt-5">
                    <div class="card-title d-flex flex-column">
                        <span
                            class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2">{{ $statistics['total_products'] ?? 0 }}</span>
                        <span class="text-white opacity-75 pt-1 fw-semibold fs-6">Tổng Sản Phẩm</span>
                    </div>
                </div>
                <div class="card-body d-flex align-items-end pt-0">
                    <div class="d-flex align-items-center flex-column mt-3 w-100">
                        <div class="d-flex justify-content-between w-100 mt-auto mb-2">
                            <span class="fw-bolder fs-6 text-white opacity-75">Tổng Giá Trị</span>
                            <span
                                class="fw-bold fs-6 text-white">{{ number_format($statistics['total_value'] ?? 0, 0, ',', '.') }}
                                VND</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--end::Total Products-->

        <!--begin::Low Stock-->
        <div class="col-md-6 col-lg-6 col-xl-3 col-xxl-3 mb-md-5 mb-xl-10">
            <div class="card card-flush bgi-no-repeat bgi-size-contain bgi-position-x-end h-md-75 mb-5 mb-xl-10"
                style="background-color: #FFC700;background-image:url('{{ asset('admin-assets/assets/media/patterns/vector-1.png') }}')">
                <div class="card-header pt-5">
                    <div class="card-title d-flex flex-column">
                        <span
                            class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2">{{ $statistics['low_stock_count'] ?? 0 }}</span>
                        <span class="text-white opacity-75 pt-1 fw-semibold fs-6">Sắp Hết Hàng</span>
                    </div>
                </div>
                <div class="card-body d-flex align-items-end pt-0">
                    <div class="d-flex align-items-center flex-column mt-3 w-100">
                        <div class="d-flex justify-content-between w-100 mt-auto mb-2">
                            <span class="fw-bolder fs-6 text-white opacity-75">Cần Nhập Thêm</span>
                            <span class="fw-bold fs-6 text-white">{{ $statistics['low_stock_count'] ?? 0 }} SP</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--end::Low Stock-->

        <!--begin::Out of Stock-->
        <div class="col-md-6 col-lg-6 col-xl-3 col-xxl-3 mb-md-5 mb-xl-10">
            <div class="card card-flush bgi-no-repeat bgi-size-contain bgi-position-x-end h-md-75 mb-5 mb-xl-10"
                style="background-color: #7239EA;background-image:url('{{ asset('admin-assets/assets/media/patterns/vector-1.png') }}')">
                <div class="card-header pt-5">
                    <div class="card-title d-flex flex-column">
                        <span
                            class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2">{{ $statistics['out_of_stock_count'] ?? 0 }}</span>
                        <span class="text-white opacity-75 pt-1 fw-semibold fs-6">Hết Hàng</span>
                    </div>
                </div>
                <div class="card-body d-flex align-items-end pt-0">
                    <div class="d-flex align-items-center flex-column mt-3 w-100">
                        <div class="d-flex justify-content-between w-100 mt-auto mb-2">
                            <span class="fw-bolder fs-6 text-white opacity-75">Cần Nhập Ngay</span>
                            <span class="fw-bold fs-6 text-white">{{ $statistics['out_of_stock_count'] ?? 0 }} SP</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--end::Out of Stock-->

        <!--begin::Stock Health-->
        <div class="col-md-6 col-lg-6 col-xl-3 col-xxl-3 mb-md-5 mb-xl-10">
            <div class="card card-flush bgi-no-repeat bgi-size-contain bgi-position-x-end h-md-75 mb-5 mb-xl-10"
                style="background-color: #50CD89;background-image:url('{{ asset('admin-assets/assets/media/patterns/vector-1.png') }}')">
                <div class="card-header pt-5">
                    <div class="card-title d-flex flex-column">
                        <span
                            class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2">{{ $statistics['stock_health'] ?? 100 }}%</span>
                        <span class="text-white opacity-75 pt-1 fw-semibold fs-6">Tình Trạng Kho</span>
                    </div>
                </div>
                <div class="card-body d-flex align-items-end pt-0">
                    <div class="d-flex align-items-center flex-column mt-3 w-100">
                        <div class="d-flex justify-content-between w-100 mt-auto mb-2">
                            <span class="fw-bolder fs-6 text-white opacity-75">Tình Trạng</span>
                            <span class="fw-bold fs-6 text-white">
                                @if (($statistics['stock_health'] ?? 100) >= 80)
                                    Tốt
                                @elseif(($statistics['stock_health'] ?? 100) >= 60)
                                    Khá
                                @else
                                    Cần Cải Thiện
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--end::Stock Health-->
    </div>
    <!--end::Statistics Cards-->

    <!--begin::Quick Actions-->
    <div class="row g-5 g-xl-10 mb-5 mb-xl-10">
        <div class="col-xl-12">
            <div class="card card-flush h-xl-100">
                <div class="card-header pt-7">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-dark">Thao Tác Nhanh</span>
                        <span class="text-gray-400 mt-1 fw-semibold fs-6">Các chức năng thường dùng</span>
                    </h3>
                </div>
                <div class="card-body pt-5">
                    <div class="row g-5">
                        <!--begin::Import Stock-->
                        <div class="col-md-6 col-lg-4">
                            <a href="{{ route('admin.inventory.import') }}"
                                class="btn btn-flex btn-outline btn-color-gray-700 btn-active-color-primary bg-body h-150px w-100 flex-column">
                                <i class="fas fa-arrow-down fs-2x text-primary mb-3"></i>
                                <div class="fs-4 fw-bold text-gray-900 mb-2">Nhập Hàng</div>
                                <div class="fs-7 fw-semibold text-gray-400 text-center">Nhập hàng mới vào kho</div>
                            </a>
                        </div>
                        <!--end::Import Stock-->

                        <!--begin::Export Stock-->
                        <div class="col-md-6 col-lg-4">
                            <a href="{{ route('admin.inventory.export') }}"
                                class="btn btn-flex btn-outline btn-color-gray-700 btn-active-color-primary bg-body h-150px w-100 flex-column">
                                <i class="fas fa-arrow-up fs-2x text-danger mb-3"></i>
                                <div class="fs-4 fw-bold text-gray-900 mb-2">Xuất Hàng</div>
                                <div class="fs-7 fw-semibold text-gray-400 text-center">Xuất hàng ra khỏi kho</div>
                            </a>
                        </div>
                        <!--end::Export Stock-->

                        <!--begin::Stock Adjustment-->
                        <div class="col-md-6 col-lg-4">
                            <a href="{{ route('admin.inventory.adjustment') }}"
                                class="btn btn-flex btn-outline btn-color-gray-700 btn-active-color-primary bg-body h-150px w-100 flex-column">
                                <i class="fas fa-balance-scale fs-2x text-warning mb-3"></i>
                                <div class="fs-4 fw-bold text-gray-900 mb-2">Điều Chỉnh</div>
                                <div class="fs-7 fw-semibold text-gray-400 text-center">Điều chỉnh số lượng tồn kho</div>
                            </a>
                        </div>
                        <!--end::Stock Adjustment-->

                        <!--begin::Transactions-->
                        <div class="col-md-6 col-lg-4">
                            <a href="{{ route('admin.inventory.transactions') }}"
                                class="btn btn-flex btn-outline btn-color-gray-700 btn-active-color-primary bg-body h-150px w-100 flex-column">
                                <i class="fas fa-history fs-2x text-info mb-3"></i>
                                <div class="fs-4 fw-bold text-gray-900 mb-2">Lịch Sử</div>
                                <div class="fs-7 fw-semibold text-gray-400 text-center">Xem lịch sử xuất nhập</div>
                            </a>
                        </div>
                        <!--end::Transactions-->

                        <!--begin::Reports-->
                        <div class="col-md-6 col-lg-4">
                            <a href="{{ route('admin.inventory.report') }}"
                                class="btn btn-flex btn-outline btn-color-gray-700 btn-active-color-primary bg-body h-150px w-100 flex-column">
                                <i class="fas fa-chart-bar fs-2x text-success mb-3"></i>
                                <div class="fs-4 fw-bold text-gray-900 mb-2">Báo Cáo</div>
                                <div class="fs-7 fw-semibold text-gray-400 text-center">Báo cáo tồn kho chi tiết</div>
                            </a>
                        </div>
                        <!--end::Reports-->

                        <!--begin::Stock Check-->
                        <div class="col-md-6 col-lg-4">
                            <a href="{{ route('admin.inventory.stock-check') }}"
                                class="btn btn-flex btn-outline btn-color-gray-700 btn-active-color-primary bg-body h-150px w-100 flex-column">
                                <i class="fas fa-search fs-2x text-dark mb-3"></i>
                                <div class="fs-4 fw-bold text-gray-900 mb-2">Kiểm Kho</div>
                                <div class="fs-7 fw-semibold text-gray-400 text-center">Kiểm tra và đối soát kho</div>
                            </a>
                        </div>
                        <!--end::Stock Check-->
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--end::Quick Actions-->

    <!--begin::Recent Transactions-->
    <div class="row g-5 g-xl-10 mb-5 mb-xl-10">
        <div class="col-xl-8">
            <div class="card card-flush h-xl-100">
                <div class="card-header pt-7">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-dark">Giao Dịch Gần Đây</span>
                        <span class="text-gray-400 mt-1 fw-semibold fs-6">20 giao dịch mới nhất</span>
                    </h3>
                    <div class="card-toolbar">
                        <a href="{{ route('admin.inventory.transactions') }}" class="btn btn-sm btn-light">Xem Tất Cả</a>
                    </div>
                </div>
                <div class="card-body pt-5">
                    <div class="table-responsive">
                        <table class="table table-row-dashed align-middle gs-0 gy-3 my-0">
                            <thead>
                                <tr class="fs-7 fw-bold text-gray-400 border-bottom-0">
                                    <th class="p-0 pb-3 min-w-150px text-start">SẢN PHẨM</th>
                                    <th class="p-0 pb-3 min-w-100px text-end">LOẠI</th>
                                    <th class="p-0 pb-3 min-w-100px text-end">SỐ LƯỢNG</th>
                                    <th class="p-0 pb-3 min-w-150px text-end">THỜI GIAN</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentTransactions ?? [] as $transaction)
                                    <tr>
                                        <td class="text-start">
                                            <div class="d-flex align-items-center">
                                                <div class="d-flex flex-column">
                                                    <span
                                                        class="text-dark fw-bold text-hover-primary fs-6">{{ $transaction->product->product_name ?? 'N/A' }}</span>
                                                    <span
                                                        class="text-muted fw-semibold text-muted d-block fs-7">{{ $transaction->product->sku ?? 'N/A' }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            @if ($transaction->transaction_type == 'import')
                                                <span class="badge badge-light-success">Nhập</span>
                                            @elseif($transaction->transaction_type == 'export')
                                                <span class="badge badge-light-danger">Xuất</span>
                                            @elseif($transaction->transaction_type == 'adjustment')
                                                <span class="badge badge-light-warning">Điều chỉnh</span>
                                            @else
                                                <span
                                                    class="badge badge-light-info">{{ ucfirst($transaction->transaction_type) }}</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <span class="text-dark fw-bold d-block fs-6">
                                                @if ($transaction->quantity > 0)
                                                    <span
                                                        class="text-success">+{{ number_format($transaction->quantity) }}</span>
                                                @else
                                                    <span
                                                        class="text-danger">{{ number_format($transaction->quantity) }}</span>
                                                @endif
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <span
                                                class="text-muted fw-semibold d-block fs-7">{{ $transaction->created_at->format('d/m/Y H:i') }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-5">
                                            <div class="text-gray-400">Chưa có giao dịch nào</div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!--begin::Low Stock Products-->
        <div class="col-xl-4">
            <div class="card card-flush h-xl-100">
                <div class="card-header pt-7">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-dark">Sản Phẩm Sắp Hết</span>
                        <span class="text-gray-400 mt-1 fw-semibold fs-6">Cần nhập thêm hàng</span>
                    </h3>
                </div>
                <div class="card-body pt-5">
                    @forelse($lowStockProducts ?? [] as $product)
                        <div class="d-flex align-items-center mb-6">
                            <div class="symbol symbol-45px me-5">
                                <span
                                    class="symbol-label bg-light-warning text-warning fs-6 fw-bolder">{{ substr($product->product_name, 0, 2) }}</span>
                            </div>
                            <div class="d-flex align-items-center flex-wrap w-100">
                                <div class="mb-1 pe-3 flex-grow-1">
                                    <span
                                        class="fs-5 text-gray-800 text-hover-primary fw-bold">{{ $product->product_name }}</span>
                                    <div class="text-gray-400 fw-semibold fs-7">{{ $product->sku }}</div>
                                </div>
                                <div class="d-flex align-items-center">
                                    <div class="fw-bold fs-5 text-gray-800 pe-1">{{ $product->stock_quantity ?? 0 }}</div>
                                    <span class="text-gray-400 fw-semibold fs-7">/
                                        {{ $product->reorder_point ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5">
                            <div class="text-gray-400">Tất cả sản phẩm đều đủ hàng</div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
        <!--end::Low Stock Products-->
    </div>
    <!--end::Recent Transactions-->
@endsection

@section('scripts')
    <script>
        // Auto refresh dashboard every 5 minutes
        setInterval(function() {
            location.reload();
        }, 300000);
    </script>
@endsection
