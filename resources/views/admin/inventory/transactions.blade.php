@extends('admin.main-content')

@section('title', 'Lịch Sử Giao Dịch')

@section('content')
<div class="d-flex flex-column flex-column-fluid">
    <!--begin::Toolbar-->
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
        <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                    Lịch Sử Giao Dịch
                </h1>
                <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('admin.dashboard') }}" class="text-muted text-hover-primary">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-400 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('admin.inventory.dashboard') }}" class="text-muted text-hover-primary">Tồn Kho</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-400 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-muted">Lịch Sử Giao Dịch</li>
                </ul>
            </div>
            <div class="d-flex align-items-center gap-2 gap-lg-3">
                <a href="{{ route('admin.inventory.dashboard') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left"></i> Quay Lại
                </a>
                <a href="{{ route('admin.inventory.import') }}" class="btn btn-sm btn-success">
                    <i class="fas fa-plus"></i> Nhập Hàng
                </a>
                <a href="{{ route('admin.inventory.export') }}" class="btn btn-sm btn-danger">
                    <i class="fas fa-minus"></i> Xuất Hàng
                </a>
            </div>
        </div>
    </div>
    <!--end::Toolbar-->

    <!--begin::Content-->
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-xxl">

            <!--begin::Statistics Cards-->
            <div class="row g-5 g-xl-8 mb-5 mb-xl-8">
                <!--begin::Col-->
                <div class="col-xl-3">
                    <div class="card card-xl-stretch mb-xl-8">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-50px me-5">
                                    <span class="symbol-label bg-light-success">
                                        <i class="fas fa-arrow-up fs-2x text-success"></i>
                                    </span>
                                </div>
                                <div class="d-flex flex-column">
                                    <span class="fw-bold fs-6 text-gray-800">Nhập Hàng Hôm Nay</span>
                                    <span class="fw-bold fs-3 text-success" id="today-imports">0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end::Col-->
                <!--begin::Col-->
                <div class="col-xl-3">
                    <div class="card card-xl-stretch mb-xl-8">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-50px me-5">
                                    <span class="symbol-label bg-light-danger">
                                        <i class="fas fa-arrow-down fs-2x text-danger"></i>
                                    </span>
                                </div>
                                <div class="d-flex flex-column">
                                    <span class="fw-bold fs-6 text-gray-800">Xuất Hàng Hôm Nay</span>
                                    <span class="fw-bold fs-3 text-danger" id="today-exports">0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end::Col-->
                <!--begin::Col-->
                <div class="col-xl-3">
                    <div class="card card-xl-stretch mb-xl-8">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-50px me-5">
                                    <span class="symbol-label bg-light-warning">
                                        <i class="fas fa-sync fs-2x text-warning"></i>
                                    </span>
                                </div>
                                <div class="d-flex flex-column">
                                    <span class="fw-bold fs-6 text-gray-800">Điều Chỉnh Hôm Nay</span>
                                    <span class="fw-bold fs-3 text-warning" id="today-adjustments">0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end::Col-->
                <!--begin::Col-->
                <div class="col-xl-3">
                    <div class="card card-xl-stretch mb-xl-8">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-50px me-5">
                                    <span class="symbol-label bg-light-primary">
                                        <i class="fas fa-list fs-2x text-primary"></i>
                                    </span>
                                </div>
                                <div class="d-flex flex-column">
                                    <span class="fw-bold fs-6 text-gray-800">Tổng Giao Dịch</span>
                                    <span class="fw-bold fs-3 text-primary" id="total-transactions">0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end::Col-->
            </div>
            <!--end::Statistics Cards-->

            <!--begin::Card-->
            <div class="card">
                <!--begin::Card header-->
                <div class="card-header border-0 pt-6">
                    <div class="card-title">
                        <div class="d-flex align-items-center position-relative my-1">
                            <span class="svg-icon svg-icon-1 position-absolute ms-6">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                    <rect opacity="0.5" x="17.0365" y="15.1223" width="8.15546" height="2" rx="1" transform="rotate(45 17.0365 15.1223)" fill="black" />
                                    <path d="m20.9 10.8c0-2.6-2.1-4.6-4.6-4.6s-4.6 2.1-4.6 4.6c0 1.3 0.5 2.4 1.4 3.2l-5.2 5.2c-0.3 0.3-0.3 0.7 0 1s0.7 0.3 1 0l5.2-5.2c0.8 0.9 1.9 1.4 3.2 1.4 2.6 0 4.6-2.1 4.6-4.6zm-1.5 0c0 1.7-1.4 3.1-3.1 3.1s-3.1-1.4-3.1-3.1 1.4-3.1 3.1-3.1 3.1 1.4 3.1 3.1z" fill="black" />
                                </svg>
                            </span>
                            <input type="text" data-kt-transaction-table-filter="search"
                                   class="form-control form-control-solid w-250px ps-15"
                                   placeholder="Tìm kiếm giao dịch..." />
                        </div>
                    </div>
                    <div class="card-toolbar">
                        <div class="d-flex justify-content-end" data-kt-transaction-table-toolbar="base">
                            <!--begin::Refresh-->
                            <button type="button" class="btn btn-light-primary me-3" onclick="KTInventoryTransactions.reload()" title="Làm mới dữ liệu">
                                <span class="svg-icon svg-icon-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                        <path d="M14.5 20.7259C14.6 21.2259 14.2 21.7259 13.7 21.7259C13.2 21.7259 12.7 21.2259 12.8 20.7259L13.4 17.7259H8.00001C7.90001 17.7259 7.80001 17.6259 7.80001 17.5259L7.90001 16.7259C7.90001 16.5259 8.00001 16.4259 8.20001 16.4259L14.1 16.5259L14.5 20.7259Z" fill="black"/>
                                        <path opacity="0.3" d="M10.3 15.4238L8.40001 9.32378C8.20001 8.72378 8.60001 8.12378 9.30001 8.12378H11.2C11.6 8.12378 11.9 8.42378 12 8.82378L14.1 16.5238L10.3 15.4238Z" fill="black"/>
                                    </svg>
                                </span>
                                Làm mới
                            </button>
                            <!--end::Refresh-->
                            <!--begin::Filter-->
                            <button type="button" class="btn btn-light-primary me-3" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                <span class="svg-icon svg-icon-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                        <path d="M19.0759 3H4.72777C3.95892 3 3.47768 3.83148 3.86067 4.49814L8.56967 12.6949C9.17923 13.7559 9.5 14.9582 9.5 16.1819V19.5072C9.5 20.2189 10.2223 20.7028 10.8805 20.432L13.8805 19.1977C14.2553 19.0435 14.5 18.6783 14.5 18.273V13.8372C14.5 12.8089 14.8171 11.8056 15.408 10.964L19.8943 4.57465C20.3596 3.912 19.8856 3 19.0759 3Z" fill="black"/>
                                    </svg>
                                </span>
                                Lọc
                            </button>
                            <div class="menu menu-sub menu-sub-dropdown w-300px w-md-325px" data-kt-menu="true">
                                <div class="px-7 py-5">
                                    <div class="fs-5 text-dark fw-bold">Lọc Giao Dịch</div>
                                </div>
                                <div class="separator border-gray-200"></div>
                                <div class="px-7 py-5" data-kt-transaction-table-filter="form">
                                    <!--begin::Input group-->
                                    <div class="mb-10">
                                        <label class="form-label fs-6 fw-semibold">Loại Giao Dịch:</label>
                                        <select class="form-select form-select-solid fw-bold" data-kt-select2="true" 
                                                data-placeholder="Chọn loại" data-allow-clear="true" 
                                                data-kt-transaction-table-filter="transaction_type">
                                            <option></option>
                                            <option value="import">Nhập Hàng</option>
                                            <option value="export">Xuất Hàng</option>
                                            <option value="adjustment">Điều Chỉnh</option>
                                            <option value="transfer">Chuyển Kho</option>
                                            <option value="return">Trả Hàng</option>
                                            <option value="damage">Hàng Hỏng</option>
                                        </select>
                                    </div>
                                    <!--end::Input group-->
                                    <!--begin::Input group-->
                                    <div class="mb-10">
                                        <label class="form-label fs-6 fw-semibold">Kho:</label>
                                        <select class="form-select form-select-solid fw-bold" data-kt-select2="true"
                                                data-placeholder="Chọn kho" data-allow-clear="true"
                                                data-kt-transaction-table-filter="warehouse">
                                            <option></option>
                                            @foreach($warehouses ?? [] as $warehouse)
                                                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <!--end::Input group-->
                                    <!--begin::Input group-->
                                    <div class="mb-10">
                                        <label class="form-label fs-6 fw-semibold">Nhà Cung Cấp:</label>
                                        <select class="form-select form-select-solid fw-bold" data-kt-select2="true"
                                                data-placeholder="Chọn nhà cung cấp" data-allow-clear="true"
                                                data-kt-transaction-table-filter="supplier">
                                            <option></option>
                                            <!-- Suppliers will be loaded via AJAX -->
                                        </select>
                                    </div>
                                    <!--end::Input group-->
                                    <!--begin::Input group-->
                                    <div class="mb-10">
                                        <label class="form-label fs-6 fw-semibold">Từ Ngày:</label>
                                        <input class="form-control form-control-solid" type="date" 
                                               data-kt-transaction-table-filter="date_from" />
                                    </div>
                                    <!--end::Input group-->
                                    <!--begin::Input group-->
                                    <div class="mb-10">
                                        <label class="form-label fs-6 fw-semibold">Đến Ngày:</label>
                                        <input class="form-control form-control-solid" type="date" 
                                               data-kt-transaction-table-filter="date_to" />
                                    </div>
                                    <!--end::Input group-->
                                    <!--begin::Actions-->
                                    <div class="d-flex justify-content-end">
                                        <button type="reset" class="btn btn-light btn-active-light-primary fw-semibold me-2 px-6" 
                                                data-kt-menu-dismiss="true" data-kt-transaction-table-filter="reset">Đặt Lại</button>
                                        <button type="submit" class="btn btn-primary fw-semibold px-6" 
                                                data-kt-menu-dismiss="true" data-kt-transaction-table-filter="filter">Áp Dụng</button>
                                    </div>
                                    <!--end::Actions-->
                                </div>
                            </div>
                            <!--end::Filter-->
                            
                            <!--begin::Export-->
                            <button type="button" class="btn btn-light-success" data-bs-toggle="modal" data-bs-target="#export-modal">
                                <span class="svg-icon svg-icon-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                        <path opacity="0.3" d="M19 22H5C4.4 22 4 21.6 4 21V3C4 2.4 4.4 2 5 2H14L20 8V21C20 21.6 19.6 22 19 22ZM15 17C15 16.4 14.6 16 14 16H8C7.4 16 7 16.4 7 17C7 17.6 7.4 18 8 18H14C14.6 18 15 17.6 15 17ZM17 12C17 11.4 16.6 11 16 11H8C7.4 11 7 11.4 7 12C7 12.6 7.4 13 8 13H16C16.6 13 17 12.6 17 12ZM17 7C17 6.4 16.6 6 16 6H8C7.4 6 7 6.4 7 7C7 7.6 7.4 8 8 8H16C16.6 8 17 7.6 17 7Z" fill="black"/>
                                        <path d="M15 8H20L14 2V7C14 7.6 14.4 8 15 8Z" fill="black"/>
                                    </svg>
                                </span>
                                Xuất Excel
                            </button>
                            <!--end::Export-->
                        </div>
                    </div>
                </div>
                <!--end::Card header-->

                <!--begin::Card body-->
                <div class="card-body py-4">
                    <!--begin::Table-->
                    <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_transactions_table">
                        <thead>
                            <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                <th class="min-w-125px">Thời Gian</th>
                                <th class="min-w-125px">Số Phiếu</th>
                                <th class="min-w-200px">Sản Phẩm</th>
                                <th class="min-w-100px">Loại</th>
                                <th class="min-w-100px">Kho</th>
                                <th class="min-w-150px">Nhà Cung Cấp</th>
                                <th class="min-w-100px text-center">Số Lượng</th>
                                <th class="min-w-100px text-center">Giá Trị</th>
                                <th class="min-w-125px">Người Thực Hiện</th>
                                <th class="text-end min-w-100px">Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 fw-semibold">
                            <!-- Data will be loaded via AJAX -->
                        </tbody>
                    </table>
                    <!--end::Table-->
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Card-->

        </div>
    </div>
    <!--end::Content-->
</div>

<!--begin::Transaction Detail Modal-->
<div class="modal fade" id="transaction-detail-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Chi Tiết Giao Dịch</h3>
                <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal">
                    <i class="fas fa-times fs-2"></i>
                </div>
            </div>
            <div class="modal-body" id="transaction-detail-content">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>
<!--end::Transaction Detail Modal-->

<!--begin::Export Modal-->
<div class="modal fade" id="export-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Xuất Báo Cáo Excel</h3>
                <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal">
                    <i class="fas fa-times fs-2"></i>
                </div>
            </div>
            <div class="modal-body">
                <form id="export-form">
                    <div class="mb-5">
                        <label class="form-label">Từ Ngày:</label>
                        <input type="date" class="form-control" name="export_date_from" value="{{ date('Y-m-01') }}">
                    </div>
                    <div class="mb-5">
                        <label class="form-label">Đến Ngày:</label>
                        <input type="date" class="form-control" name="export_date_to" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="mb-5">
                        <label class="form-label">Loại Giao Dịch:</label>
                        <select class="form-select" name="export_transaction_type">
                            <option value="">Tất cả</option>
                            <option value="import">Nhập Hàng</option>
                            <option value="export">Xuất Hàng</option>
                            <option value="adjustment">Điều Chỉnh</option>
                            <option value="transfer">Chuyển Kho</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary" onclick="exportTransactions()">
                    <i class="fas fa-download"></i> Xuất Excel
                </button>
            </div>
        </div>
    </div>
</div>
<!--end::Export Modal-->

<!--begin::Transaction Detail Modal-->
<div class="modal fade" id="transaction-detail-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bolder">Chi Tiết Giao Dịch</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <span class="svg-icon svg-icon-1">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                            <rect opacity="0.5" x="6" y="17.3137" width="16" height="2" rx="1" transform="rotate(-45 6 17.3137)" fill="black"/>
                            <rect x="7.41422" y="6" width="16" height="2" rx="1" transform="rotate(45 7.41422 6)" fill="black"/>
                        </svg>
                    </span>
                </div>
            </div>
            <div class="modal-body scroll-y mx-5 mx-xl-15 my-7" id="transaction-detail-content">
                <!-- Content will be loaded via AJAX -->
                <div class="d-flex justify-content-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!--end::Transaction Detail Modal-->

@endsection

@section('vendor-script')
    <script src="{{ asset('admin-assets/assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
@endsection

@section('scripts')
    <script src="{{ asset('admin-assets/assets/js/custom/inventory/transactions.js') }}"></script>
@endsection
