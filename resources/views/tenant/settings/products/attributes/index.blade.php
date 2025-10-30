@extends('admin.layouts.tenant-app')

@section('title', 'Thuộc tính')

@section('style')
<link href="{{ asset('admin-assets/css/globals.css') }}" rel="stylesheet" type="text/css" />
<style>
    .attribute-value-tag {
        display: inline-block;
        padding: 4px 12px;
        margin: 2px;
        background-color: #f1f1f2;
        border-radius: 4px;
        font-size: 13px;
        color: #3f4254;
    }
    .attribute-value-tag.color-tag {
        padding-left: 28px;
        position: relative;
    }
    .attribute-value-tag .color-circle {
        position: absolute;
        left: 8px;
        top: 50%;
        transform: translateY(-50%);
        width: 14px;
        height: 14px;
        border-radius: 50%;
        border: 1px solid #ddd;
    }
</style>
@endsection

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
                    <!--begin::Settings Search-->
                    @include('tenant.settings.elements.search')
                    <!--end::Settings Search-->

                    <!--begin::Card-->
                    <div class="card">
                    <!--begin::Card header-->
                    <div class="card-header align-items-center py-5 gap-2 gap-md-5">
                        <div class="card-title flex-column">
                            <h3 class="fw-bold mb-1">Thuộc tính</h3>
                            <div class="text-muted fs-6">Quản lý hàng hóa theo đặc điểm riêng như màu sắc, kích cỡ, chất liệu.</div>
                        </div>
                        <div class="card-toolbar d-flex gap-3">
                            <button type="button" class="btn btn-primary" id="btn_create_attribute">
                                <i class="fas fa-plus fs-4 me-2"></i>
                                Tạo thuộc tính
                            </button>
                            <div class="form-check form-switch form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" id="attributes_toggle" checked />
                            </div>
                        </div>
                    </div>
                    <!--end::Card header-->

                    <!--begin::Card body-->
                    <div class="card-body pt-0">
                        <!--begin::Table-->
                        <div class="table-responsive">
                            <table class="table table-row-bordered align-middle gy-4" id="attributes_table">
                                <thead>
                                    <tr class="fw-bold text-muted bg-light">
                                        <th class="ps-4">Tên thuộc tính</th>
                                        <th>Giá trị thuộc tính</th>
                                        <th class="text-end pe-4">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody id="attributes_tbody">
                                    <!-- Attributes will be loaded here via AJAX -->
                                    <tr>
                                        <td colspan="3" class="text-center py-10">
                                            <div class="spinner-border text-primary" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <!--end::Table-->
                    </div>
                    <!--end::Card body-->
                </div>
                <!--end::Card-->
            </div>
            <!--end::Content-->
        </div>
        <!--end::Layout-->
    </div>
    <!--end::Content-->
</div>
<!--end::Content wrapper-->

<!--begin::Modal - Create Attribute-->
<div class="modal fade" id="modal_create_attribute" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold">Tạo thuộc tính</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="fas fa-times fs-1"></i>
                </div>
            </div>
            <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                <form id="form_create_attribute">
                    <div class="mb-7">
                        <label class="required fw-semibold fs-6 mb-2">Tên thuộc tính</label>
                        <input type="text" name="name" class="form-control form-control-solid" placeholder="Nhập tên thuộc tính" required />
                    </div>
                    <div class="text-end">
                        <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">Bỏ qua</button>
                        <button type="submit" class="btn btn-primary">
                            <span class="indicator-label">Lưu</span>
                            <span class="indicator-progress">Đang xử lý...
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!--end::Modal-->

<!--begin::Modal - Edit Attribute-->
<div class="modal fade" id="modal_edit_attribute" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold">Sửa thuộc tính</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="fas fa-times fs-1"></i>
                </div>
            </div>
            <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                <form id="form_edit_attribute">
                    <input type="hidden" id="edit_attribute_id" />
                    <div class="mb-7">
                        <label class="required fw-semibold fs-6 mb-2">Tên thuộc tính</label>
                        <input type="text" id="edit_attribute_name" name="name" class="form-control form-control-solid" placeholder="Nhập tên thuộc tính" required />
                    </div>
                    <div class="text-end">
                        <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">Bỏ qua</button>
                        <button type="submit" class="btn btn-primary">
                            <span class="indicator-label">Lưu</span>
                            <span class="indicator-progress">Đang xử lý...
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!--end::Modal-->

<!--begin::Modal - Manage Attribute Values-->
<div class="modal fade" id="modal_manage_values" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-800px">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold">Quản lý giá trị: <span id="manage_values_attribute_name" class="text-primary"></span></h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="fas fa-times fs-1"></i>
                </div>
            </div>
            <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                <input type="hidden" id="manage_values_attribute_id" />

                <!--begin::Add new value form-->
                <div class="card mb-5 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title mb-4">Thêm giá trị mới</h5>
                        <form id="form_add_value">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Tên giá trị</label>
                                    <input type="text" id="new_value_name" name="value" class="form-control form-control-solid" placeholder="Nhập tên giá trị" required />
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Màu sắc (tùy chọn)</label>
                                    <input type="color" id="new_value_color" name="color_code" class="form-control form-control-color form-control-solid" title="Chọn màu" />
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-plus me-2"></i>Thêm
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <!--end::Add new value form-->

                <!--begin::Values list-->
                <div>
                    <h5 class="mb-4">Danh sách giá trị (<span id="values_count">0</span>)</h5>
                    <div id="attribute_values_list">
                        <!-- Values will be loaded here -->
                        <div class="text-center py-10">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end::Values list-->
            </div>
        </div>
    </div>
</div>
<!--end::Modal-->
@endsection

@section('scripts')
<script>
// Routes configuration
window.attributesRoutes = {
    data: '{{ route('admin.settings.products.attributes.data') }}',
    store: '{{ route('admin.settings.products.attributes.store') }}',
    update: (id) => '{{ route('admin.settings.products.attributes.update', ':id') }}'.replace(':id', id),
    destroy: (id) => '{{ route('admin.settings.products.attributes.destroy', ':id') }}'.replace(':id', id),
    storeValue: (attributeId) => '{{ route('admin.settings.products.attributes.values.store', ':attributeId') }}'.replace(':attributeId', attributeId),
    updateValue: (attributeId, valueId) => '{{ route('admin.settings.products.attributes.values.update', [':attributeId', ':valueId']) }}'.replace(':attributeId', attributeId).replace(':valueId', valueId),
    destroyValue: (attributeId, valueId) => '{{ route('admin.settings.products.attributes.values.destroy', [':attributeId', ':valueId']) }}'.replace(':attributeId', attributeId).replace(':valueId', valueId),
};

// State
let attributesData = [];

// Load attributes data
function loadAttributes() {
    $.ajax({
        url: window.attributesRoutes.data,
        method: 'GET',
        success: function(response) {
            if (response.success) {
                attributesData = response.data;
                renderAttributes();
            }
        },
        error: function(xhr) {
            console.error('Error loading attributes:', xhr);
            $('#attributes_tbody').html('<tr><td colspan="3" class="text-center"><div class="alert alert-danger">Có lỗi xảy ra khi tải dữ liệu</div></td></tr>');
        }
    });
}

// Render attributes table
function renderAttributes() {
    if (attributesData.length === 0) {
        $('#attributes_tbody').html('<tr><td colspan="3" class="text-center text-muted py-10">Không có dữ liệu</td></tr>');
        return;
    }

    let html = '';
    attributesData.forEach(attribute => {
        // Render values as tags
        let valuesHtml = '';
        if (attribute.values && attribute.values.length > 0) {
            attribute.values.forEach(value => {
                if (value.color_code) {
                    valuesHtml += `
                        <span class="attribute-value-tag color-tag">
                            <span class="color-circle" style="background-color: ${value.color_code};"></span>
                            ${value.value}
                        </span>
                    `;
                } else {
                    valuesHtml += `<span class="attribute-value-tag">${value.value}</span>`;
                }
            });
        } else {
            valuesHtml = '<span class="text-muted">Chưa có giá trị</span>';
        }

        html += `
            <tr>
                <td class="ps-4">
                    <div class="fw-semibold text-gray-800">${attribute.name}</div>
                    <div class="text-muted fs-7">${attribute.values_count} giá trị</div>
                </td>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <div class="flex-grow-1">${valuesHtml}</div>
                        <button class="btn btn-sm btn-light-primary" onclick="manageValues(${attribute.id}, '${attribute.name}')">
                            <i class="fas fa-cog me-1"></i>Quản lý
                        </button>
                    </div>
                </td>
                <td class="text-end pe-4">
                    <button class="btn btn-sm btn-icon btn-light btn-active-light-primary me-2" onclick="editAttribute(${attribute.id}, '${attribute.name}')">
                        <i class="fas fa-pen fs-5"></i>
                    </button>
                    <button class="btn btn-sm btn-icon btn-light btn-active-light-danger" onclick="deleteAttribute(${attribute.id})">
                        <i class="fas fa-trash fs-5"></i>
                    </button>
                </td>
            </tr>
        `;
    });

    $('#attributes_tbody').html(html);
}

// Create attribute
function createAttribute() {
    const formData = {
        name: $('input[name="name"]').val(),
        type: 'select',
        status: 'active'
    };

    const submitBtn = $('#form_create_attribute button[type="submit"]');
    submitBtn.attr('data-kt-indicator', 'on').prop('disabled', true);

    $.ajax({
        url: window.attributesRoutes.store,
        method: 'POST',
        data: formData,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                $('#modal_create_attribute').modal('hide');
                $('#form_create_attribute')[0].reset();
                loadAttributes();
                
                Swal.fire({
                    icon: 'success',
                    title: 'Thành công',
                    text: response.message,
                    timer: 2000
                });
            }
        },
        error: function(xhr) {
            Swal.fire({
                icon: 'error',
                title: 'Lỗi',
                text: xhr.responseJSON?.message || 'Có lỗi xảy ra'
            });
        },
        complete: function() {
            submitBtn.removeAttr('data-kt-indicator').prop('disabled', false);
        }
    });
}

// Edit attribute
function editAttribute(id, name) {
    $('#edit_attribute_id').val(id);
    $('#edit_attribute_name').val(name);
    $('#modal_edit_attribute').modal('show');
}

// Update attribute
function updateAttribute() {
    const attributeId = $('#edit_attribute_id').val();
    const formData = {
        name: $('#edit_attribute_name').val()
    };

    const submitBtn = $('#form_edit_attribute button[type="submit"]');
    submitBtn.attr('data-kt-indicator', 'on').prop('disabled', true);

    $.ajax({
        url: window.attributesRoutes.update(attributeId),
        method: 'PUT',
        data: formData,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                $('#modal_edit_attribute').modal('hide');
                loadAttributes();

                Swal.fire({
                    icon: 'success',
                    title: 'Thành công',
                    text: response.message,
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        },
        error: function(xhr) {
            Swal.fire({
                icon: 'error',
                title: 'Lỗi',
                text: xhr.responseJSON?.message || 'Có lỗi xảy ra'
            });
        },
        complete: function() {
            submitBtn.removeAttr('data-kt-indicator').prop('disabled', false);
        }
    });
}

// Delete attribute
function deleteAttribute(id) {
    Swal.fire({
        title: 'Xác nhận xóa?',
        text: 'Bạn có chắc chắn muốn xóa thuộc tính này?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Xóa',
        cancelButtonText: 'Hủy'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: window.attributesRoutes.destroy(id),
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        loadAttributes();
                        Swal.fire('Đã xóa!', response.message, 'success');
                    }
                },
                error: function(xhr) {
                    Swal.fire('Lỗi!', xhr.responseJSON?.message || 'Có lỗi xảy ra', 'error');
                }
            });
        }
    });
}

// Manage attribute values
function manageValues(attributeId, attributeName) {
    $('#manage_values_attribute_id').val(attributeId);
    $('#manage_values_attribute_name').text(attributeName);
    $('#modal_manage_values').modal('show');
    loadAttributeValues(attributeId);
}

// Load attribute values
function loadAttributeValues(attributeId) {
    const attribute = attributesData.find(a => a.id === attributeId);
    if (!attribute) return;

    const values = attribute.values || [];
    $('#values_count').text(values.length);

    if (values.length === 0) {
        $('#attribute_values_list').html('<div class="text-center text-muted py-10">Chưa có giá trị nào</div>');
        return;
    }

    let html = '';
    values.forEach(value => {
        const colorHtml = value.color_code
            ? `<span class="value-color me-2" style="background-color: ${value.color_code};"></span>`
            : '';

        html += `
            <div class="value-item" data-value-id="${value.id}">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center flex-grow-1">
                        ${colorHtml}
                        <span class="fw-semibold">${value.value}</span>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-icon btn-light btn-active-light-primary" onclick="editValue(${attributeId}, ${value.id}, '${value.value}', '${value.color_code || ''}')">
                            <i class="fas fa-pen fs-6"></i>
                        </button>
                        <button class="btn btn-sm btn-icon btn-light btn-active-light-danger" onclick="deleteValue(${attributeId}, ${value.id})">
                            <i class="fas fa-trash fs-6"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
    });

    $('#attribute_values_list').html(html);
}

// Add new value
function addValue() {
    const attributeId = $('#manage_values_attribute_id').val();
    const formData = {
        value: $('#new_value_name').val(),
        color_code: $('#new_value_color').val() || null,
        status: 'active'
    };

    const submitBtn = $('#form_add_value button[type="submit"]');
    submitBtn.attr('data-kt-indicator', 'on').prop('disabled', true);

    $.ajax({
        url: window.attributesRoutes.storeValue(attributeId),
        method: 'POST',
        data: formData,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                $('#form_add_value')[0].reset();
                loadAttributes(); // Reload to get updated values

                // Reload values in modal
                setTimeout(() => {
                    loadAttributeValues(attributeId);
                }, 500);

                Swal.fire({
                    icon: 'success',
                    title: 'Thành công',
                    text: response.message,
                    timer: 1500,
                    showConfirmButton: false
                });
            }
        },
        error: function(xhr) {
            Swal.fire({
                icon: 'error',
                title: 'Lỗi',
                text: xhr.responseJSON?.message || 'Có lỗi xảy ra'
            });
        },
        complete: function() {
            submitBtn.removeAttr('data-kt-indicator').prop('disabled', false);
        }
    });
}

// Edit value (inline)
function editValue(attributeId, valueId, currentValue, currentColor) {
    Swal.fire({
        title: 'Sửa giá trị',
        html: `
            <div class="mb-3">
                <label class="form-label">Tên giá trị</label>
                <input type="text" id="swal_value_name" class="form-control" value="${currentValue}" />
            </div>
            <div class="mb-3">
                <label class="form-label">Màu sắc (tùy chọn)</label>
                <input type="color" id="swal_value_color" class="form-control form-control-color" value="${currentColor || '#000000'}" />
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Lưu',
        cancelButtonText: 'Hủy',
        preConfirm: () => {
            return {
                value: document.getElementById('swal_value_name').value,
                color_code: document.getElementById('swal_value_color').value
            };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: window.attributesRoutes.updateValue(attributeId, valueId),
                method: 'PUT',
                data: result.value,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        loadAttributes();
                        setTimeout(() => {
                            loadAttributeValues(attributeId);
                        }, 500);

                        Swal.fire({
                            icon: 'success',
                            title: 'Thành công',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                    }
                },
                error: function(xhr) {
                    Swal.fire('Lỗi!', xhr.responseJSON?.message || 'Có lỗi xảy ra', 'error');
                }
            });
        }
    });
}

// Delete value
function deleteValue(attributeId, valueId) {
    Swal.fire({
        title: 'Xác nhận xóa?',
        text: 'Bạn có chắc chắn muốn xóa giá trị này?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Xóa',
        cancelButtonText: 'Hủy'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: window.attributesRoutes.destroyValue(attributeId, valueId),
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        loadAttributes();
                        setTimeout(() => {
                            loadAttributeValues(attributeId);
                        }, 500);

                        Swal.fire('Đã xóa!', response.message, 'success');
                    }
                },
                error: function(xhr) {
                    Swal.fire('Lỗi!', xhr.responseJSON?.message || 'Có lỗi xảy ra', 'error');
                }
            });
        }
    });
}

// Initialize
$(document).ready(function() {
    // Load initial data
    loadAttributes();

    // Create attribute button
    $('#btn_create_attribute').on('click', function() {
        $('#modal_create_attribute').modal('show');
    });

    // Form submit - Create attribute
    $('#form_create_attribute').on('submit', function(e) {
        e.preventDefault();
        createAttribute();
    });

    // Form submit - Edit attribute
    $('#form_edit_attribute').on('submit', function(e) {
        e.preventDefault();
        updateAttribute();
    });

    // Form submit - Add value
    $('#form_add_value').on('submit', function(e) {
        e.preventDefault();
        addValue();
    });

    // Toggle status
    $('#attributes_toggle').on('change', function() {
        const isEnabled = $(this).is(':checked');
        console.log('Attributes feature:', isEnabled ? 'Enabled' : 'Disabled');
        // TODO: Save setting to backend
    });
});
</script>
@endsection

