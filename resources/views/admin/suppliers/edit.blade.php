@extends('admin.index')
@section('page-header', 'Edit Supplier')
@section('page-sub_header', 'Chỉnh sửa nhà cung cấp')
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
                        <span class="card-label fw-bolder fs-3 mb-1">Chỉnh sửa nhà cung cấp</span>
                        <span class="text-muted mt-1 fw-bold fs-7">Cập nhật thông tin nhà cung cấp</span>
                    </h3>
                    <div class="card-toolbar">
                        <a href="{{ route('supplier.list') }}" class="btn btn-sm btn-light">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                    </div>
                </div>
                <!--end::Header-->
                <!--begin::Body-->
                <div class="card-body py-3">
                    <!--begin::Form-->
                    <form id="kt_edit_supplier_form" class="form" action="{{ route('supplier.edit.action', $supplier->id) }}" method="POST">
                        <input type="hidden" name="supplier_id" value="{{ $supplier->id }}" />
                        @csrf
                        <!--begin::Card body-->
                        <div class="card-body">
                            <!--begin::Row-->
                            <div class="row mb-6">
                                <!--begin::Label-->
                                <label class="col-lg-4 col-form-label required fw-bold fs-6">Mã nhà cung cấp</label>
                                <!--end::Label-->
                                <!--begin::Col-->
                                <div class="col-lg-8">
                                    <!--begin::Row-->
                                    <div class="row">
                                        <!--begin::Col-->
                                        <div class="col-lg-6 fv-row">
                                            <input type="text" name="code" class="form-control form-control-lg form-control-solid mb-3 mb-lg-0" placeholder="Mã nhà cung cấp" value="{{ $supplier->code }}" />
                                        </div>
                                        <!--end::Col-->
                                    </div>
                                    <!--end::Row-->
                                </div>
                                <!--end::Col-->
                            </div>
                            <!--end::Row-->
                            <!--begin::Row-->
                            <div class="row mb-6">
                                <!--begin::Label-->
                                <label class="col-lg-4 col-form-label required fw-bold fs-6">Tên nhà cung cấp</label>
                                <!--end::Label-->
                                <!--begin::Col-->
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="name" class="form-control form-control-lg form-control-solid" placeholder="Tên nhà cung cấp" value="{{ $supplier->name }}" />
                                </div>
                                <!--end::Col-->
                            </div>
                            <!--end::Row-->
                            <!--begin::Row-->
                            <div class="row mb-6">
                                <!--begin::Label-->
                                <label class="col-lg-4 col-form-label fw-bold fs-6">Tên công ty</label>
                                <!--end::Label-->
                                <!--begin::Col-->
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="company" class="form-control form-control-lg form-control-solid" placeholder="Tên công ty" value="{{ $supplier->company }}" />
                                </div>
                                <!--end::Col-->
                            </div>
                            <!--end::Row-->
                            <!--begin::Row-->
                            <div class="row mb-6">
                                <!--begin::Label-->
                                <label class="col-lg-4 col-form-label fw-bold fs-6">Thông tin liên hệ</label>
                                <!--end::Label-->
                                <!--begin::Col-->
                                <div class="col-lg-8">
                                    <!--begin::Row-->
                                    <div class="row">
                                        <!--begin::Col-->
                                        <div class="col-lg-6 fv-row">
                                            <input type="text" name="phone" class="form-control form-control-lg form-control-solid mb-3 mb-lg-0" placeholder="Số điện thoại" value="{{ $supplier->phone }}" />
                                        </div>
                                        <!--end::Col-->
                                        <!--begin::Col-->
                                        <div class="col-lg-6 fv-row">
                                            <input type="email" name="email" class="form-control form-control-lg form-control-solid" placeholder="Email" value="{{ $supplier->email }}" />
                                        </div>
                                        <!--end::Col-->
                                    </div>
                                    <!--end::Row-->
                                </div>
                                <!--end::Col-->
                            </div>
                            <!--end::Row-->
                            <!--begin::Row-->
                            <div class="row mb-6">
                                <!--begin::Label-->
                                <label class="col-lg-4 col-form-label fw-bold fs-6">Mã số thuế</label>
                                <!--end::Label-->
                                <!--begin::Col-->
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="tax_code" class="form-control form-control-lg form-control-solid" placeholder="Mã số thuế" value="{{ $supplier->tax_code }}" />
                                </div>
                                <!--end::Col-->
                            </div>
                            <!--end::Row-->
                            <!--begin::Row-->
                            <div class="row mb-6">
                                <!--begin::Label-->
                                <label class="col-lg-4 col-form-label fw-bold fs-6">Địa chỉ</label>
                                <!--end::Label-->
                                <!--begin::Col-->
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="address" class="form-control form-control-lg form-control-solid mb-3 mb-lg-0" placeholder="Địa chỉ cụ thể" value="{{ $supplier->address }}" />
                                </div>
                                <!--end::Col-->
                            </div>
                            <!--end::Row-->
                            <!--begin::Row-->
                            <div class="row mb-6">
                                <!--begin::Label-->
                                <label class="col-lg-4 col-form-label fw-bold fs-6">Khu vực</label>
                                <!--end::Label-->
                                <!--begin::Col-->
                                <div class="col-lg-8">
                                    <!--begin::Row-->
                                    <div class="row">
                                        <!--begin::Col-->
                                        <div class="col-lg-4 fv-row">
                                            <input type="text" name="province" class="form-control form-control-lg form-control-solid mb-3 mb-lg-0" placeholder="Tỉnh/TP" value="{{ $supplier->province }}" />
                                        </div>
                                        <!--end::Col-->
                                        <!--begin::Col-->
                                        <div class="col-lg-4 fv-row">
                                            <input type="text" name="district" class="form-control form-control-lg form-control-solid mb-3 mb-lg-0" placeholder="Quận/Huyện" value="{{ $supplier->district }}" />
                                        </div>
                                        <!--end::Col-->
                                        <!--begin::Col-->
                                        <div class="col-lg-4 fv-row">
                                            <input type="text" name="ward" class="form-control form-control-lg form-control-solid" placeholder="Phường/Xã" value="{{ $supplier->ward }}" />
                                        </div>
                                        <!--end::Col-->
                                    </div>
                                    <!--end::Row-->
                                </div>
                                <!--end::Col-->
                            </div>
                            <!--end::Row-->
                            <!--begin::Row-->
                            <div class="row mb-6">
                                <!--begin::Label-->
                                <label class="col-lg-4 col-form-label fw-bold fs-6">Chi nhánh</label>
                                <!--end::Label-->
                                <!--begin::Col-->
                                <div class="col-lg-8 fv-row">
                                    <select name="branch_id" class="form-select form-select-solid form-select-lg fw-bold">
                                        <option value="">Chọn chi nhánh</option>
                                        @foreach($branches as $branch)
                                            <option value="{{ $branch->id }}" {{ $supplier->branch_id == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <!--end::Col-->
                            </div>
                            <!--end::Row-->
                            <!--begin::Row-->
                            <div class="row mb-6">
                                <!--begin::Label-->
                                <label class="col-lg-4 col-form-label fw-bold fs-6">Nhóm nhà cung cấp</label>
                                <!--end::Label-->
                                <!--begin::Col-->
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="group" class="form-control form-control-lg form-control-solid" placeholder="Nhóm nhà cung cấp" value="{{ $supplier->group }}" list="supplier-groups" />
                                    <datalist id="supplier-groups">
                                        @foreach($groups as $group)
                                            <option value="{{ $group }}">
                                        @endforeach
                                    </datalist>
                                </div>
                                <!--end::Col-->
                            </div>
                            <!--end::Row-->
                            <!--begin::Row-->
                            <div class="row mb-6">
                                <!--begin::Label-->
                                <label class="col-lg-4 col-form-label required fw-bold fs-6">Trạng thái</label>
                                <!--end::Label-->
                                <!--begin::Col-->
                                <div class="col-lg-8 fv-row">
                                    <select name="status" class="form-select form-select-solid form-select-lg fw-bold">
                                        <option value="">Chọn trạng thái</option>
                                        <option value="active" {{ $supplier->status == 'active' ? 'selected' : '' }}>Hoạt động</option>
                                        <option value="inactive" {{ $supplier->status == 'inactive' ? 'selected' : '' }}>Không hoạt động</option>
                                    </select>
                                </div>
                                <!--end::Col-->
                            </div>
                            <!--end::Row-->
                            <!--begin::Row-->
                            <div class="row mb-6">
                                <!--begin::Label-->
                                <label class="col-lg-4 col-form-label fw-bold fs-6">Ghi chú</label>
                                <!--end::Label-->
                                <!--begin::Col-->
                                <div class="col-lg-8 fv-row">
                                    <textarea name="note" class="form-control form-control-lg form-control-solid" rows="3" placeholder="Ghi chú về nhà cung cấp">{{ $supplier->note }}</textarea>
                                </div>
                                <!--end::Col-->
                            </div>
                            <!--end::Row-->
                        </div>
                        <!--end::Card body-->
                        <!--begin::Actions-->
                        <div class="card-footer d-flex justify-content-end py-6 px-9">
                            <button type="button" class="btn btn-light btn-active-light-primary me-2" data-kt-supplier-action="cancel">Hủy</button>
                            <button type="submit" class="btn btn-primary" data-kt-supplier-action="submit">
                                <span class="indicator-label">Cập nhật</span>
                                <span class="indicator-progress">Đang xử lý...
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                            </button>
                        </div>
                        <!--end::Actions-->
                    </form>
                    <!--end::Form-->
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
    <script src="{{ asset('admin-assets/assets/js/custom/apps/suppliers/list/edit.js') }}"></script>
@endsection
