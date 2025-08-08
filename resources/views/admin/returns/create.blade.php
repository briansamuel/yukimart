@extends('admin.main-content')

@section('title', 'Tạo đơn trả hàng')

@section('style')
    <link rel="stylesheet" href="{{ asset('admin-assets/assets/plugins/custom/datatables/datatables.bundle.css') }}" />
    <link href="{{ asset('admin-assets/css/globals.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('admin-assets/css/return-create.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
    <!--begin::Content-->
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <!--begin::Toolbar-->
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
                <!--begin::Page title-->
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <!--begin::Title-->
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                        Tạo đơn trả hàng
                    </h1>
                    <!--end::Title-->
                    <!--begin::Breadcrumb-->
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ route('admin.dashboard') }}" class="text-muted text-hover-primary">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <span class="bullet bg-gray-400 w-5px h-2px"></span>
                        </li>
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ route('admin.return.list') }}" class="text-muted text-hover-primary">Đơn trả hàng</a>
                        </li>
                        <li class="breadcrumb-item">
                            <span class="bullet bg-gray-400 w-5px h-2px"></span>
                        </li>
                        <li class="breadcrumb-item text-dark">Tạo mới</li>
                    </ul>
                    <!--end::Breadcrumb-->
                </div>
                <!--end::Page title-->
            </div>
        </div>
        <!--end::Toolbar-->

        <div id="kt_app_content_container" class="app-container container-fluid">
            <!--begin::Form-->
            <form id="kt_return_create_form" class="form d-flex flex-column flex-lg-row">
                @csrf
                <!--begin::Aside column-->
                <div class="d-flex flex-column gap-7 gap-lg-10 w-100 w-lg-300px mb-7 me-lg-10">
                    <!--begin::Return details-->
                    <div class="card card-flush py-4">
                        <!--begin::Card header-->
                        <div class="card-header">
                            <div class="card-title">
                                <h2>Thông tin đơn trả hàng</h2>
                            </div>
                        </div>
                        <!--end::Card header-->
                        <!--begin::Card body-->
                        <div class="card-body pt-0">
                            <!--begin::Input group-->
                            <div class="mb-10 fv-row">
                                <!--begin::Label-->
                                <label class="required form-label">Hóa đơn gốc</label>
                                <!--end::Label-->
                                <!--begin::Select-->
                                <select name="invoice_id" class="form-select mb-2" data-control="select2" data-placeholder="Chọn hóa đơn" data-allow-clear="true">
                                    <option></option>
                                </select>
                                <!--end::Select-->
                                <!--begin::Description-->
                                <div class="text-muted fs-7">Chọn hóa đơn gốc để tạo đơn trả hàng.</div>
                                <!--end::Description-->
                            </div>
                            <!--end::Input group-->

                            <!--begin::Input group-->
                            <div class="mb-10 fv-row">
                                <!--begin::Label-->
                                <label class="form-label">Khách hàng</label>
                                <!--end::Label-->
                                <!--begin::Select-->
                                <select name="customer_id" class="form-select mb-2" data-control="select2" data-placeholder="Chọn khách hàng" data-allow-clear="true">
                                    <option value="0">Khách lẻ</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->name }} - {{ $customer->phone }}</option>
                                    @endforeach
                                </select>
                                <!--end::Select-->
                            </div>
                            <!--end::Input group-->

                            <!--begin::Input group-->
                            <div class="mb-10 fv-row">
                                <!--begin::Label-->
                                <label class="form-label">Chi nhánh</label>
                                <!--end::Label-->
                                <!--begin::Select-->
                                <select name="branch_shop_id" class="form-select mb-2" data-control="select2" data-placeholder="Chọn chi nhánh" data-allow-clear="true">
                                    <option></option>
                                    @foreach($branchShops as $branchShop)
                                        <option value="{{ $branchShop->id }}">{{ $branchShop->name }}</option>
                                    @endforeach
                                </select>
                                <!--end::Select-->
                            </div>
                            <!--end::Input group-->

                            <!--begin::Input group-->
                            <div class="mb-10 fv-row">
                                <!--begin::Label-->
                                <label class="required form-label">Ngày trả hàng</label>
                                <!--end::Label-->
                                <!--begin::Input-->
                                <input class="form-control" placeholder="Chọn ngày" name="return_date" id="kt_return_date" value="{{ date('Y-m-d') }}" />
                                <!--end::Input-->
                            </div>
                            <!--end::Input group-->
                        </div>
                        <!--end::Card body-->
                    </div>
                    <!--end::Return details-->

                    <!--begin::Return reason-->
                    <div class="card card-flush py-4">
                        <!--begin::Card header-->
                        <div class="card-header">
                            <div class="card-title">
                                <h2>Lý do trả hàng</h2>
                            </div>
                        </div>
                        <!--end::Card header-->
                        <!--begin::Card body-->
                        <div class="card-body pt-0">
                            <!--begin::Input group-->
                            <div class="mb-10 fv-row">
                                <!--begin::Label-->
                                <label class="required form-label">Lý do</label>
                                <!--end::Label-->
                                <!--begin::Select-->
                                <select name="reason" class="form-select mb-2" data-control="select2" data-placeholder="Chọn lý do">
                                    <option></option>
                                    <option value="defective">Hàng lỗi</option>
                                    <option value="wrong_item">Giao sai hàng</option>
                                    <option value="customer_request">Khách yêu cầu</option>
                                    <option value="damaged">Hàng bị hỏng</option>
                                    <option value="expired">Hàng hết hạn</option>
                                    <option value="other">Khác</option>
                                </select>
                                <!--end::Select-->
                            </div>
                            <!--end::Input group-->

                            <!--begin::Input group-->
                            <div class="mb-10 fv-row">
                                <!--begin::Label-->
                                <label class="form-label">Chi tiết lý do</label>
                                <!--end::Label-->
                                <!--begin::Textarea-->
                                <textarea name="reason_detail" class="form-control" rows="3" placeholder="Mô tả chi tiết lý do trả hàng..."></textarea>
                                <!--end::Textarea-->
                            </div>
                            <!--end::Input group-->

                            <!--begin::Input group-->
                            <div class="mb-10 fv-row">
                                <!--begin::Label-->
                                <label class="required form-label">Phương thức hoàn tiền</label>
                                <!--end::Label-->
                                <!--begin::Select-->
                                <select name="refund_method" class="form-select mb-2" data-control="select2" data-placeholder="Chọn phương thức">
                                    <option></option>
                                    <option value="cash">Tiền mặt</option>
                                    <option value="card">Thẻ</option>
                                    <option value="transfer">Chuyển khoản</option>
                                    <option value="store_credit">Tín dụng cửa hàng</option>
                                    <option value="exchange">Đổi hàng</option>
                                    <option value="points">Điểm thưởng</option>
                                </select>
                                <!--end::Select-->
                            </div>
                            <!--end::Input group-->

                            <!--begin::Input group-->
                            <div class="mb-0 fv-row">
                                <!--begin::Label-->
                                <label class="form-label">Ghi chú</label>
                                <!--end::Label-->
                                <!--begin::Textarea-->
                                <textarea name="notes" class="form-control" rows="3" placeholder="Ghi chú thêm..."></textarea>
                                <!--end::Textarea-->
                            </div>
                            <!--end::Input group-->
                        </div>
                        <!--end::Card body-->
                    </div>
                    <!--end::Return reason-->
                </div>
                <!--end::Aside column-->

                <!--begin::Main column-->
                <div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-10">
                    <!--begin::Return items-->
                    <div class="card card-flush py-4">
                        <!--begin::Card header-->
                        <div class="card-header">
                            <div class="card-title">
                                <h2>Sản phẩm trả hàng</h2>
                            </div>
                        </div>
                        <!--end::Card header-->
                        <!--begin::Card body-->
                        <div class="card-body pt-0">
                            <div class="table-responsive">
                                <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_return_items_table">
                                    <thead>
                                        <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                            <th class="min-w-200px">Sản phẩm</th>
                                            <th class="min-w-100px">Số lượng trả</th>
                                            <th class="min-w-100px">Đơn giá</th>
                                            <th class="min-w-100px">Thành tiền</th>
                                            <th class="min-w-150px">Tình trạng</th>
                                            <th class="min-w-150px">Ghi chú</th>
                                            <th class="min-w-70px">Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody id="return_items_tbody">
                                        <tr id="empty_row">
                                            <td colspan="7" class="text-center text-muted">
                                                Chưa có sản phẩm nào. Vui lòng chọn hóa đơn để tải danh sách sản phẩm.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <!--end::Card body-->
                    </div>
                    <!--end::Return items-->

                    <!--begin::Actions-->
                    <div class="d-flex justify-content-end">
                        <!--begin::Button-->
                        <a href="{{ route('admin.return.list') }}" id="kt_return_cancel" class="btn btn-light me-5">Hủy</a>
                        <!--end::Button-->
                        <!--begin::Button-->
                        <button type="submit" id="kt_return_submit" class="btn btn-primary">
                            <span class="indicator-label">Tạo đơn trả hàng</span>
                            <span class="indicator-progress">Đang xử lý...
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                        </button>
                        <!--end::Button-->
                    </div>
                    <!--end::Actions-->
                </div>
                <!--end::Main column-->
            </form>
            <!--end::Form-->
        </div>
    </div>
    <!--end::Content-->
@endsection

@section('script')
    <script src="{{ asset('admin-assets/assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
    <script src="{{ asset('admin-assets/js/returns/return-create.js') }}"></script>
@endsection
