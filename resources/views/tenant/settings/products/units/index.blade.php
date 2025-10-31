@extends('admin.layouts.tenant-app')

@section('title', 'Đơn vị tính')

@section('style')
<link href="{{ asset('admin-assets/css/globals.css') }}" rel="stylesheet" type="text/css" />
<style>
    .unit-item {
        padding: 16px 20px;
        border-bottom: 1px solid #e4e6ef;
        cursor: pointer;
        transition: background-color 0.2s;
    }
    .unit-item:last-child {
        border-bottom: none;
    }
    .unit-item:hover {
        background-color: #f9f9f9;
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
                                <h3 class="fw-bold mb-1">Đơn vị tính</h3>
                                <div class="text-muted fs-6">Quản lý hàng hóa theo đơn vị tính khác nhau như chiếc, lốc, thùng.</div>
                            </div>
                            <div class="card-toolbar d-flex gap-3">
                                <button type="button" class="btn btn-primary" id="btn_create_unit">
                                    <i class="fas fa-plus fs-4 me-2"></i>
                                    Tạo đơn vị tính
                                </button>
                                <div class="form-check form-switch form-check-custom form-check-solid">
                                    <input class="form-check-input" type="checkbox" id="units_toggle" checked />
                                </div>
                            </div>
                        </div>
                        <!--end::Card header-->

                    <!--begin::Card body-->
                    <div class="card-body pt-0">
                        <!--begin::Search-->
                        <div class="mb-5">
                            <div class="position-relative">
                                <i class="fas fa-search fs-3 position-absolute ms-4 mt-3 text-gray-500"></i>
                                <input type="text" id="search_units" class="form-control form-control-solid ps-12" placeholder="Tìm kiếm thiết lập" />
                            </div>
                        </div>
                        <!--end::Search-->

                        <!--begin::Units list-->
                        <div id="units_list">
                            <!-- Units will be loaded here via AJAX -->
                            <div class="text-center py-10">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        </div>
                        <!--end::Units list-->

                        <!--begin::Pagination-->
                        <div id="units_pagination" class="d-flex justify-content-between align-items-center mt-5">
                            <div class="text-muted fs-7">
                                <span id="pagination_info">1 - 10 trong 32 đơn vị tính</span>
                            </div>
                            <div class="d-flex gap-2">
                                <button class="btn btn-sm btn-icon btn-light" id="prev_page" disabled>
                                    <i class="fas fa-chevron-left"></i>
                                </button>
                                <span class="btn btn-sm btn-light" id="current_page">1</span>
                                <button class="btn btn-sm btn-icon btn-light" id="next_page">
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>
                        </div>
                        <!--end::Pagination-->
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

<!--begin::Modal - Create/Edit Unit-->
<div class="modal fade" id="modal_unit" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold" id="modal_unit_title">Tạo đơn vị tính</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="fas fa-times fs-1"></i>
                </div>
            </div>
            <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                <form id="form_unit">
                    <input type="hidden" id="unit_id" name="id" />

                    <div class="mb-7">
                        <label class="required fw-semibold fs-6 mb-2">Tên đơn vị tính</label>
                        <input type="text" id="unit_name" name="name" class="form-control form-control-solid" placeholder="Nhập tên đơn vị tính" required />
                    </div>

                    <div class="mb-7">
                        <label class="fw-semibold fs-6 mb-2">Trạng thái</label>
                        <div class="form-check form-switch form-check-custom form-check-solid">
                            <input class="form-check-input" type="checkbox" id="unit_is_active" name="is_active" checked />
                            <label class="form-check-label" for="unit_is_active">
                                Đang hoạt động
                            </label>
                        </div>
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
@endsection

@section('scripts')
<script>
// Routes configuration
window.unitsRoutes = {
    data: '{{ route('admin.settings.products.units.data') }}',
    store: '{{ route('admin.settings.products.units.store') }}',
    update: (id) => '{{ route('admin.settings.products.units.update', ':id') }}'.replace(':id', id),
    destroy: (id) => '{{ route('admin.settings.products.units.destroy', ':id') }}'.replace(':id', id),
    toggleStatus: (id) => '{{ route('admin.settings.products.units.toggle-status', ':id') }}'.replace(':id', id),
};

// State
let currentPage = 1;
let perPage = 10;
let totalUnits = 0;
let unitsData = [];

// Load units data
function loadUnits() {
    const searchQuery = $('#search_units').val();
    
    $.ajax({
        url: window.unitsRoutes.data,
        method: 'GET',
        data: {
            search: searchQuery,
            page: currentPage,
            per_page: perPage
        },
        success: function(response) {
            if (response.success) {
                unitsData = response.data;
                totalUnits = response.total;
                renderUnits();
                updatePagination();
            }
        },
        error: function(xhr) {
            console.error('Error loading units:', xhr);
            $('#units_list').html('<div class="alert alert-danger">Có lỗi xảy ra khi tải dữ liệu</div>');
        }
    });
}

// Render units list
function renderUnits() {
    if (unitsData.length === 0) {
        $('#units_list').html('<div class="text-center text-muted py-10">Không có dữ liệu</div>');
        return;
    }

    let html = '';
    unitsData.forEach(unit => {
        html += `
            <div class="unit-item">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="flex-grow-1">
                        <div class="fw-semibold text-gray-800 fs-6">${unit.name}</div>
                    </div>
                    <div class="d-flex gap-2 align-items-center">
                        <span class="badge badge-light-${unit.is_active ? 'success' : 'danger'}">${unit.is_active_label}</span>
                        <button class="btn btn-sm btn-icon btn-light btn-active-light-primary" onclick="editUnit(${unit.id})">
                            <i class="fas fa-pen fs-5"></i>
                        </button>
                        <button class="btn btn-sm btn-icon btn-light btn-active-light-danger" onclick="deleteUnit(${unit.id})">
                            <i class="fas fa-trash fs-5"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
    });

    $('#units_list').html(html);
}

// Update pagination
function updatePagination() {
    const start = (currentPage - 1) * perPage + 1;
    const end = Math.min(currentPage * perPage, totalUnits);
    $('#pagination_info').text(`${start} - ${end} trong ${totalUnits} đơn vị tính`);

    // Update buttons
    $('#prev_page').prop('disabled', currentPage === 1);
    $('#next_page').prop('disabled', end >= totalUnits);
    $('#current_page').text(currentPage);
}

// Initialize
$(document).ready(function() {
    // Load initial data
    loadUnits();

    // Search
    let searchTimeout;
    $('#search_units').on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            currentPage = 1;
            loadUnits();
        }, 500);
    });

    // Pagination
    $('#prev_page').on('click', function() {
        if (currentPage > 1) {
            currentPage--;
            loadUnits();
        }
    });

    $('#next_page').on('click', function() {
        const maxPage = Math.ceil(totalUnits / perPage);
        if (currentPage < maxPage) {
            currentPage++;
            loadUnits();
        }
    });

    // Toggle status
    $('#units_toggle').on('change', function() {
        const isEnabled = $(this).is(':checked');
        console.log('Units feature:', isEnabled ? 'Enabled' : 'Disabled');
        // TODO: Save setting to backend
    });

    // Create unit button
    $('#btn_create_unit').on('click', function() {
        $('#modal_unit_title').text('Tạo đơn vị tính');
        $('#form_unit')[0].reset();
        $('#unit_id').val('');
        $('#unit_is_active').prop('checked', true);
        $('#modal_unit').modal('show');
    });

    // Form submit
    $('#form_unit').on('submit', function(e) {
        e.preventDefault();
        saveUnit();
    });
});

// Create/Update unit
function saveUnit() {
    const unitId = $('#unit_id').val();
    const formData = {
        name: $('#unit_name').val(),
        is_active: $('#unit_is_active').is(':checked') ? 1 : 0
    };

    const url = unitId ? window.unitsRoutes.update(unitId) : window.unitsRoutes.store;
    const method = unitId ? 'PUT' : 'POST';

    const submitBtn = $('#form_unit button[type="submit"]');
    submitBtn.attr('data-kt-indicator', 'on').prop('disabled', true);

    $.ajax({
        url: url,
        method: method,
        data: formData,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                $('#modal_unit').modal('hide');
                loadUnits();

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

// Edit unit
function editUnit(id) {
    const unit = unitsData.find(u => u.id === id);
    if (!unit) return;

    $('#modal_unit_title').text('Sửa đơn vị tính');
    $('#unit_id').val(unit.id);
    $('#unit_name').val(unit.name);
    $('#unit_is_active').prop('checked', unit.is_active);
    $('#modal_unit').modal('show');
}

// Delete unit
function deleteUnit(id) {
    Swal.fire({
        title: 'Xác nhận xóa?',
        text: 'Bạn có chắc chắn muốn xóa đơn vị tính này?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Xóa',
        cancelButtonText: 'Hủy'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: window.unitsRoutes.destroy(id),
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        loadUnits();
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
</script>
@endsection

