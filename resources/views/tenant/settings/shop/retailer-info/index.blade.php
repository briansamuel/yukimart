@extends('admin.layouts.tenant-app')

@section('title', 'Thông tin cửa hàng')

@section('content')
<!--begin::Content wrapper-->
<div class="d-flex flex-column flex-column-fluid">

    <!--begin::Toolbar-->
    @include('tenant.settings.elements.toolbar')
    <!--end::Toolbar-->

    <!--begin::Content-->
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-fluid">
            <!--begin::Layout-->
            <div class="d-flex flex-column flex-lg-row">
                <!--begin::Sidebar-->
                @include('tenant.settings.elements.sidebar')
                <!--end::Sidebar-->

                <!--begin::Content-->
                <div class="flex-lg-row-fluid ms-lg-10 order-2 order-lg-2">
                    <!--begin::Card-->
                    <div class="card">
                        <!--begin::Card header-->
                        <div class="card-header">
                            <h2 class="card-title">Thông tin cửa hàng</h2>
                        </div>
                        <!--end::Card header-->

                        <!--begin::Card body-->
                        <div class="card-body">
                    <!--begin::Form-->
                    <form id="retailer_info_form" class="form">
                        @csrf
                        
                        <!--begin::Row-->
                        <div class="row mb-6">
                            <label class="col-lg-3 col-form-label required fw-semibold fs-6">Tên cửa hàng</label>
                            <div class="col-lg-9">
                                <input type="text" name="store_name" class="form-control form-control-solid" 
                                       placeholder="Nhập tên cửa hàng" value="{{ $settings['store_name'] }}" required />
                            </div>
                        </div>
                        <!--end::Row-->

                        <!--begin::Row-->
                        <div class="row mb-6">
                            <label class="col-lg-3 col-form-label fw-semibold fs-6">Subdomain</label>
                            <div class="col-lg-9">
                                <div class="input-group input-group-solid">
                                    <input type="text" class="form-control" value="{{ $settings['store_subdomain'] }}" readonly />
                                    <span class="input-group-text">.yukimart.local</span>
                                </div>
                                <div class="form-text">Subdomain không thể thay đổi</div>
                            </div>
                        </div>
                        <!--end::Row-->

                        <!--begin::Row-->
                        <div class="row mb-6">
                            <label class="col-lg-3 col-form-label fw-semibold fs-6">Số điện thoại</label>
                            <div class="col-lg-9">
                                <input type="text" name="store_phone" class="form-control form-control-solid" 
                                       placeholder="Nhập số điện thoại" value="{{ $settings['store_phone'] }}" />
                            </div>
                        </div>
                        <!--end::Row-->

                        <!--begin::Row-->
                        <div class="row mb-6">
                            <label class="col-lg-3 col-form-label fw-semibold fs-6">Email</label>
                            <div class="col-lg-9">
                                <input type="email" name="store_email" class="form-control form-control-solid" 
                                       placeholder="Nhập email" value="{{ $settings['store_email'] }}" />
                            </div>
                        </div>
                        <!--end::Row-->

                        <!--begin::Row-->
                        <div class="row mb-6">
                            <label class="col-lg-3 col-form-label fw-semibold fs-6">Địa chỉ</label>
                            <div class="col-lg-9">
                                <input type="text" name="store_address" class="form-control form-control-solid" 
                                       placeholder="Nhập địa chỉ" value="{{ $settings['store_address'] }}" />
                            </div>
                        </div>
                        <!--end::Row-->

                        <!--begin::Row-->
                        <div class="row mb-6">
                            <label class="col-lg-3 col-form-label fw-semibold fs-6">Logo</label>
                            <div class="col-lg-9">
                                <div class="image-input image-input-outline" data-kt-image-input="true">
                                    <div class="image-input-wrapper w-125px h-125px" 
                                         style="background-image: url('{{ $settings['store_logo'] ? asset('storage/' . $settings['store_logo']) : asset('admin-assets/assets/media/svg/files/blank-image.svg') }}')">
                                    </div>
                                    <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" 
                                           data-kt-image-input-action="change" data-bs-toggle="tooltip" title="Thay đổi logo">
                                        <i class="fas fa-pencil-alt fs-7"></i>
                                        <input type="file" name="store_logo" accept=".png, .jpg, .jpeg" />
                                    </label>
                                    <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" 
                                          data-kt-image-input-action="remove" data-bs-toggle="tooltip" title="Xóa logo">
                                        <i class="fas fa-times fs-7"></i>
                                    </span>
                                </div>
                                <div class="form-text">Cho phép: png, jpg, jpeg. Kích thước tối đa: 2MB</div>
                            </div>
                        </div>
                        <!--end::Row-->

                        <!--begin::Row-->
                        <div class="row mb-6">
                            <label class="col-lg-3 col-form-label fw-semibold fs-6">Mô tả</label>
                            <div class="col-lg-9">
                                <textarea name="store_description" class="form-control form-control-solid" rows="4" 
                                          placeholder="Nhập mô tả về cửa hàng">{{ $settings['store_description'] }}</textarea>
                            </div>
                        </div>
                        <!--end::Row-->

                        <!--begin::Actions-->
                        <div class="row">
                            <div class="col-lg-9 offset-lg-3">
                                <button type="submit" class="btn btn-primary" id="save_btn">
                                    <i class="fas fa-save"></i>
                                    Lưu thay đổi
                                </button>
                                <button type="reset" class="btn btn-secondary">
                                    <i class="fas fa-undo"></i>
                                    Đặt lại
                                </button>
                            </div>
                        </div>
                        <!--end::Actions-->
                    </form>
                    <!--end::Form-->
                        </div>
                        <!--end::Card body-->
                    </div>
                    <!--end::Card-->
                </div>
                <!--end::Content-->
            </div>
            <!--end::Layout-->
        </div>
    </div>
    <!--end::Content-->
</div>
<!--end::Content wrapper-->
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Initialize image input
    KTImageInput.createInstances();

    // Handle form submission
    $('#retailer_info_form').on('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const saveBtn = $('#save_btn');
        const card = saveBtn.closest('.card');

        // Show loading state
        saveBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Đang lưu...');

        // Add loading overlay to card
        card.addClass('overlay overlay-block');
        card.append('<div class="overlay-layer bg-dark bg-opacity-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Đang tải...</span></div></div>');

        $.ajax({
            url: '{{ route("admin.settings.retailer-info.update") }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    // Show success notification
                    Swal.fire({
                        icon: 'success',
                        title: 'Thành công!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false,
                        toast: true,
                        position: 'top-end',
                        timerProgressBar: true
                    });

                    // Optional: Reload page after 2 seconds to reflect changes
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                }
            },
            error: function(xhr) {
                let message = 'Có lỗi xảy ra!';
                let errors = [];

                if (xhr.responseJSON) {
                    if (xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    if (xhr.responseJSON.errors) {
                        errors = Object.values(xhr.responseJSON.errors).flat();
                    }
                }

                // Show error notification
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi!',
                    html: message + (errors.length > 0 ? '<br><ul class="text-start mt-2"><li>' + errors.join('</li><li>') + '</li></ul>' : ''),
                    confirmButtonText: 'Đóng'
                });
            },
            complete: function() {
                // Remove loading state
                saveBtn.prop('disabled', false).html('<i class="fas fa-save"></i> Lưu thay đổi');
                card.removeClass('overlay overlay-block');
                card.find('.overlay-layer').remove();
            }
        });
    });
});
</script>
@endsection

