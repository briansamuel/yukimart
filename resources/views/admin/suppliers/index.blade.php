@extends('admin.index')
@section('page-header', 'Suppliers')
@section('page-sub_header', 'Quản lý nhà cung cấp')
@section('style')
    <link href="{{ asset('admin-assets/assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
    <!--begin::Row-->
    <div class="row g-5 g-xl-8">
        <!--begin::Col-->
        <div class="col-xl-12">
            <!--begin::Tables Widget 9-->
            <div class="card card-xl-stretch mb-5 mb-xl-8">
                <!--begin::Header-->
                <div class="card-header border-0 pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bolder fs-3 mb-1">Danh sách nhà cung cấp</span>
                        <span class="text-muted mt-1 fw-bold fs-7">Quản lý thông tin nhà cung cấp</span>
                    </h3>
                    <div class="card-toolbar" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-trigger="hover" title="Click to add a supplier">
                        <a href="{{ route('supplier.add') }}" class="btn btn-sm btn-light btn-active-primary">
                            <i class="fas fa-plus me-2"></i>Thêm nhà cung cấp
                        </a>
                    </div>
                </div>
                <!--end::Header-->
                <!--begin::Body-->
                <div class="card-body py-3">
                    <!--begin::Table Toolbar-->
                    <div class="d-flex justify-content-between" data-kt-suppliers-table-toolbar="base">
                        <!--begin::Search-->
                        <div class="d-flex align-items-center position-relative my-1">
                            <i class="fas fa-search position-absolute ms-3 text-muted"></i>
                            <input type="text" data-kt-suppliers-table-filter="search" class="form-control form-control-solid w-250px ps-10" placeholder="Tìm kiếm nhà cung cấp..." />
                        </div>
                        <!--end::Search-->
                        <!--begin::Group actions-->
                        <div class="d-flex justify-content-end" data-kt-suppliers-table-toolbar="selected" style="display: none;">
                            <div class="fw-bolder me-5">
                                <span class="me-2" data-kt-suppliers-table-select="selected_count"></span>Đã chọn
                            </div>
                            <button type="button" class="btn btn-danger" data-kt-suppliers-table-select="delete_selected">Xóa đã chọn</button>
                        </div>
                        <!--end::Group actions-->
                    </div>
                    <!--end::Table Toolbar-->
                    <!--begin::Table container-->
                    <div class="table-responsive">
                        <!--begin::Table-->
                        <table id="kt_datatable_suppliers" class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                            <!--begin::Table head-->
                            <thead>
                                <tr class="fw-bolder text-muted">
                                    <th class="w-25px">
                                        <div class="form-check form-check-sm form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" value="1" data-kt-check="true" data-kt-check-target="#kt_datatable_suppliers .form-check-input" />
                                        </div>
                                    </th>
                                    <th class="min-w-150px">Mã NCC</th>
                                    <th class="min-w-200px">Tên nhà cung cấp</th>
                                    <th class="min-w-150px">Công ty</th>
                                    <th class="min-w-120px">Điện thoại</th>
                                    <th class="min-w-120px">Email</th>
                                    <th class="min-w-100px">Chi nhánh</th>
                                    <th class="min-w-100px">Trạng thái</th>
                                    <th class="min-w-100px text-end">Thao tác</th>
                                </tr>
                            </thead>
                            <!--end::Table head-->
                            <!--begin::Table body-->
                            <tbody class="text-gray-600 fw-bold">
                                <!-- Data will be loaded via AJAX -->
                            </tbody>
                            <!--end::Table body-->
                        </table>
                        <!--end::Table-->
                    </div>
                    <!--end::Table container-->
                </div>
                <!--begin::Body-->
            </div>
            <!--end::Tables Widget 9-->
        </div>
        <!--end::Col-->
    </div>
    <!--end::Row-->

    <!--begin::Modals-->
    <!--begin::Modal - Delete Confirmation-->
    <div class="modal fade" id="kt_modal_delete_supplier" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mw-650px">
            <div class="modal-content">
                <div class="modal-header" id="kt_modal_delete_supplier_header">
                    <h2 class="fw-bolder">Xác nhận xóa</h2>
                    <div class="btn btn-icon btn-sm btn-active-icon-primary" data-kt-suppliers-modal-action="close">
                        <i class="fas fa-times"></i>
                    </div>
                </div>
                <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                    <div class="fw-bolder fs-3 text-gray-600 mb-5">Bạn có chắc chắn muốn xóa nhà cung cấp này?</div>
                    <div class="text-muted fw-bold fs-5">Hành động này không thể hoàn tác.</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-kt-suppliers-modal-action="cancel">Hủy</button>
                    <button type="button" class="btn btn-danger" data-kt-suppliers-modal-action="submit">
                        <span class="indicator-label">Xóa</span>
                        <span class="indicator-progress">Đang xử lý...
                        <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!--end::Modal - Delete Confirmation-->
    <!--end::Modals-->
@endsection

@section('vendor-script')
    <script src="{{ asset('admin-assets/assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
@endsection

@section('scripts')
    <script src="{{ asset('admin-assets/assets/js/custom/apps/suppliers/list/table.js') }}"></script>
@endsection
