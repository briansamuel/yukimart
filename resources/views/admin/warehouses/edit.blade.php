@extends('admin.layouts.app')

@section('title', 'Sửa kho hàng')

@section('content')
<div class="d-flex flex-column flex-column-fluid">
    <!--begin::Toolbar-->
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
        <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">Sửa kho hàng</h1>
                <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('admin.dashboard') }}" class="text-muted text-hover-primary">Trang chủ</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-400 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('admin.warehouses.index') }}" class="text-muted text-hover-primary">Quản lý kho hàng</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-400 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-muted">{{ $warehouse->name }}</li>
                </ul>
            </div>
            <div class="d-flex align-items-center gap-2 gap-lg-3">
                <a href="{{ route('admin.warehouses.index') }}" class="btn btn-sm btn-secondary">
                    <i class="ki-duotone ki-arrow-left fs-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>Quay lại
                </a>
            </div>
        </div>
    </div>
    <!--end::Toolbar-->

    <!--begin::Content-->
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-xxl">
            <!--begin::Form-->
            <form id="kt_warehouse_form" class="form d-flex flex-column flex-lg-row" action="{{ route('admin.warehouses.update', $warehouse->id) }}" method="POST">
                @csrf
                @method('PUT')
                <!--begin::Aside column-->
                <div class="d-flex flex-column gap-7 gap-lg-10 w-100 w-lg-300px mb-7 me-lg-10">
                    <!--begin::Thumbnail settings-->
                    <div class="card card-flush py-4">
                        <!--begin::Card header-->
                        <div class="card-header">
                            <div class="card-title">
                                <h2>Biểu tượng kho hàng</h2>
                            </div>
                        </div>
                        <!--end::Card header-->
                        <!--begin::Card body-->
                        <div class="card-body text-center pt-0">
                            <!--begin::Image placeholder-->
                            <div class="symbol symbol-150px symbol-circle mb-7">
                                <div class="symbol-label bg-light-primary">
                                    <i class="ki-duotone ki-package fs-2x text-primary">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                        <span class="path3"></span>
                                    </i>
                                </div>
                            </div>
                            <!--end::Image placeholder-->
                            <!--begin::Description-->
                            <div class="text-muted fs-7">Biểu tượng mặc định cho kho hàng</div>
                            <!--end::Description-->
                        </div>
                        <!--end::Card body-->
                    </div>
                    <!--end::Thumbnail settings-->

                    <!--begin::Status-->
                    <div class="card card-flush py-4">
                        <!--begin::Card header-->
                        <div class="card-header">
                            <div class="card-title">
                                <h2>Trạng thái</h2>
                            </div>
                        </div>
                        <!--end::Card header-->
                        <!--begin::Card body-->
                        <div class="card-body pt-0">
                            <!--begin::Select2-->
                            <select class="form-select mb-2" name="status" data-control="select2" data-hide-search="true" data-placeholder="Chọn trạng thái">
                                <option value="active" {{ $warehouse->status == 'active' ? 'selected' : '' }}>Hoạt động</option>
                                <option value="inactive" {{ $warehouse->status == 'inactive' ? 'selected' : '' }}>Không hoạt động</option>
                            </select>
                            <!--end::Select2-->
                            <!--begin::Description-->
                            <div class="text-muted fs-7">Trạng thái hoạt động của kho hàng</div>
                            <!--end::Description-->
                        </div>
                        <!--end::Card body-->
                    </div>
                    <!--end::Status-->

                    <!--begin::Default warehouse-->
                    <div class="card card-flush py-4">
                        <!--begin::Card header-->
                        <div class="card-header">
                            <div class="card-title">
                                <h2>Kho mặc định</h2>
                            </div>
                        </div>
                        <!--end::Card header-->
                        <!--begin::Card body-->
                        <div class="card-body pt-0">
                            <!--begin::Switch-->
                            <div class="form-check form-switch form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" name="is_default" value="1" id="is_default" {{ $warehouse->is_default ? 'checked' : '' }} />
                                <label class="form-check-label" for="is_default">
                                    Đặt làm kho mặc định
                                </label>
                            </div>
                            <!--end::Switch-->
                            <!--begin::Description-->
                            <div class="text-muted fs-7 mt-2">Kho mặc định sẽ được sử dụng khi không chỉ định kho cụ thể</div>
                            <!--end::Description-->
                        </div>
                        <!--end::Card body-->
                    </div>
                    <!--end::Default warehouse-->

                    <!--begin::Statistics-->
                    <div class="card card-flush py-4">
                        <!--begin::Card header-->
                        <div class="card-header">
                            <div class="card-title">
                                <h2>Thống kê</h2>
                            </div>
                        </div>
                        <!--end::Card header-->
                        <!--begin::Card body-->
                        <div class="card-body pt-0">
                            <!--begin::Stats-->
                            <div class="d-flex flex-wrap">
                                <!--begin::Stat-->
                                <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                    <div class="d-flex align-items-center">
                                        <div class="fs-2 fw-bold counted text-dark">{{ $warehouse->branchShops()->count() }}</div>
                                    </div>
                                    <div class="fw-semibold fs-6 text-gray-400">Chi nhánh</div>
                                </div>
                                <!--end::Stat-->
                                <!--begin::Stat-->
                                <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                                    <div class="d-flex align-items-center">
                                        <div class="fs-2 fw-bold counted text-dark">{{ $warehouse->total_products }}</div>
                                    </div>
                                    <div class="fw-semibold fs-6 text-gray-400">Sản phẩm</div>
                                </div>
                                <!--end::Stat-->
                            </div>
                            <!--end::Stats-->
                        </div>
                        <!--end::Card body-->
                    </div>
                    <!--end::Statistics-->
                </div>
                <!--end::Aside column-->

                <!--begin::Main column-->
                <div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-10">
                    <!--begin::General options-->
                    <div class="card card-flush py-4">
                        <!--begin::Card header-->
                        <div class="card-header">
                            <div class="card-title">
                                <h2>Thông tin chung</h2>
                            </div>
                        </div>
                        <!--end::Card header-->
                        <!--begin::Card body-->
                        <div class="card-body pt-0">
                            <!--begin::Input group-->
                            <div class="mb-10 fv-row">
                                <!--begin::Label-->
                                <label class="required form-label">Tên kho hàng</label>
                                <!--end::Label-->
                                <!--begin::Input-->
                                <input type="text" name="name" class="form-control mb-2" placeholder="Nhập tên kho hàng" value="{{ old('name', $warehouse->name) }}" />
                                <!--end::Input-->
                                <!--begin::Description-->
                                <div class="text-muted fs-7">Tên kho hàng sẽ được hiển thị trong hệ thống</div>
                                <!--end::Description-->
                            </div>
                            <!--end::Input group-->

                            <!--begin::Input group-->
                            <div class="mb-10 fv-row">
                                <!--begin::Label-->
                                <label class="required form-label">Mã kho hàng</label>
                                <!--end::Label-->
                                <!--begin::Input-->
                                <input type="text" name="code" class="form-control mb-2" placeholder="Nhập mã kho hàng" value="{{ old('code', $warehouse->code) }}" />
                                <!--end::Input-->
                                <!--begin::Description-->
                                <div class="text-muted fs-7">Mã kho hàng duy nhất để phân biệt các kho</div>
                                <!--end::Description-->
                            </div>
                            <!--end::Input group-->

                            <!--begin::Input group-->
                            <div class="mb-10 fv-row">
                                <!--begin::Label-->
                                <label class="form-label">Mô tả</label>
                                <!--end::Label-->
                                <!--begin::Textarea-->
                                <textarea name="description" class="form-control mb-2" rows="4" placeholder="Nhập mô tả kho hàng">{{ old('description', $warehouse->description) }}</textarea>
                                <!--end::Textarea-->
                                <!--begin::Description-->
                                <div class="text-muted fs-7">Mô tả chi tiết về kho hàng</div>
                                <!--end::Description-->
                            </div>
                            <!--end::Input group-->

                            <!--begin::Input group-->
                            <div class="mb-10 fv-row">
                                <!--begin::Label-->
                                <label class="form-label">Địa chỉ</label>
                                <!--end::Label-->
                                <!--begin::Input-->
                                <input type="text" name="address" class="form-control mb-2" placeholder="Nhập địa chỉ kho hàng" value="{{ old('address', $warehouse->address) }}" />
                                <!--end::Input-->
                                <!--begin::Description-->
                                <div class="text-muted fs-7">Địa chỉ vật lý của kho hàng</div>
                                <!--end::Description-->
                            </div>
                            <!--end::Input group-->
                        </div>
                        <!--end::Card body-->
                    </div>
                    <!--end::General options-->

                    <!--begin::Contact information-->
                    <div class="card card-flush py-4">
                        <!--begin::Card header-->
                        <div class="card-header">
                            <div class="card-title">
                                <h2>Thông tin liên hệ</h2>
                            </div>
                        </div>
                        <!--end::Card header-->
                        <!--begin::Card body-->
                        <div class="card-body pt-0">
                            <!--begin::Input group-->
                            <div class="row g-9 mb-7">
                                <!--begin::Col-->
                                <div class="col-md-6 fv-row">
                                    <!--begin::Label-->
                                    <label class="form-label">Số điện thoại</label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <input type="text" name="phone" class="form-control mb-2" placeholder="Nhập số điện thoại" value="{{ old('phone', $warehouse->phone) }}" />
                                    <!--end::Input-->
                                </div>
                                <!--end::Col-->
                                <!--begin::Col-->
                                <div class="col-md-6 fv-row">
                                    <!--begin::Label-->
                                    <label class="form-label">Email</label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <input type="email" name="email" class="form-control mb-2" placeholder="Nhập email" value="{{ old('email', $warehouse->email) }}" />
                                    <!--end::Input-->
                                </div>
                                <!--end::Col-->
                            </div>
                            <!--end::Input group-->

                            <!--begin::Input group-->
                            <div class="mb-10 fv-row">
                                <!--begin::Label-->
                                <label class="form-label">Tên quản lý</label>
                                <!--end::Label-->
                                <!--begin::Input-->
                                <input type="text" name="manager_name" class="form-control mb-2" placeholder="Nhập tên quản lý kho" value="{{ old('manager_name', $warehouse->manager_name) }}" />
                                <!--end::Input-->
                                <!--begin::Description-->
                                <div class="text-muted fs-7">Tên người quản lý kho hàng</div>
                                <!--end::Description-->
                            </div>
                            <!--end::Input group-->
                        </div>
                        <!--end::Card body-->
                    </div>
                    <!--end::Contact information-->

                    <div class="d-flex justify-content-end">
                        <!--begin::Button-->
                        <a href="{{ route('admin.warehouses.index') }}" class="btn btn-light me-5">Hủy</a>
                        <!--end::Button-->
                        <!--begin::Button-->
                        <button type="submit" class="btn btn-primary" data-kt-warehouse-action="save">
                            <span class="indicator-label">Cập nhật</span>
                            <span class="indicator-progress">Đang xử lý...
                            <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                        </button>
                        <!--end::Button-->
                    </div>
                </div>
                <!--end::Main column-->
            </form>
            <!--end::Form-->
        </div>
    </div>
    <!--end::Content-->
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('kt_warehouse_form');
    const submitButton = form.querySelector('[data-kt-warehouse-action="save"]');
    
    if (form && submitButton) {
        submitButton.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Show loading state
            submitButton.setAttribute('data-kt-indicator', 'on');
            submitButton.disabled = true;
            
            // Create FormData
            const formData = new FormData(form);
            
            // Submit form via AJAX
            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        text: data.message || 'Cập nhật kho hàng thành công!',
                        icon: 'success',
                        buttonsStyling: false,
                        confirmButtonText: 'OK',
                        customClass: {
                            confirmButton: 'btn btn-primary'
                        }
                    }).then(() => {
                        window.location.href = '{{ route("admin.warehouses.index") }}';
                    });
                } else {
                    // Show validation errors
                    let errorMessage = data.message || 'Có lỗi xảy ra';
                    if (data.errors) {
                        const errors = Object.values(data.errors).flat();
                        errorMessage = errors.join('\n');
                    }
                    
                    Swal.fire({
                        text: errorMessage,
                        icon: 'error',
                        buttonsStyling: false,
                        confirmButtonText: 'OK',
                        customClass: {
                            confirmButton: 'btn btn-primary'
                        }
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    text: 'Có lỗi xảy ra khi cập nhật kho hàng',
                    icon: 'error',
                    buttonsStyling: false,
                    confirmButtonText: 'OK',
                    customClass: {
                        confirmButton: 'btn btn-primary'
                    }
                });
            })
            .finally(() => {
                // Hide loading state
                submitButton.removeAttribute('data-kt-indicator');
                submitButton.disabled = false;
            });
        });
    }
});
</script>
@endsection
