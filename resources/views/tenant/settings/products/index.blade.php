@extends('admin.layouts.tenant-app')

@section('title', 'Thông tin hàng hóa')

@section('style')
<style>
    .setting-item {
        padding: 20px;
        border-bottom: 1px solid #e4e6ef;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }
    .setting-item:hover {
        background-color: #f9f9f9;
    }
    .setting-item:last-child {
        border-bottom: none;
    }
    .setting-toggle .form-check-input {
        width: 45px;
        height: 24px;
    }
    .bg-light-primary {
        background-color: #f1faff !important;
        transition: background-color 0.3s ease;
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
                        <div class="card-header">
                            <h2 class="card-title">Thông tin hàng hóa</h2>
                        </div>
                        <!--end::Card header-->

                        <!--begin::Card body-->
                        <div class="card-body p-0">
                            <!--begin::Settings list-->
                            <div id="settings_list">
                                <!--begin::Setting item - Mã vạch hàng hóa-->
                                <div class="setting-item" data-section-id="product_barcode">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="flex-grow-1">
                                            <div class="fw-bold text-gray-800 fs-6 mb-1">Mã vạch hàng hóa</div>
                                            <div class="text-muted fs-7">Quản lý hàng hóa bằng mã vạch chuẩn hoặc mã vạch do cửa hàng tạo ra.</div>
                                        </div>
                                        <div class="setting-toggle ms-5">
                                            <div class="form-check form-switch form-check-custom form-check-solid">
                                                <input class="form-check-input" type="checkbox" id="toggle_barcode" checked />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!--end::Setting item-->

                                <!--begin::Setting item - Tự động gợi ý thông tin hàng hóa-->
                                <div class="setting-item" data-section-id="product_auto_suggest">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="flex-grow-1">
                                            <div class="fw-bold text-gray-800 fs-6 mb-1">Tự động gợi ý thông tin hàng hóa</div>
                                            <div class="text-muted fs-7">KiotViet sẽ tự động gợi ý tên, mã, mô tả, hình ảnh hàng hóa khi tạo hàng hóa.</div>
                                        </div>
                                        <div class="setting-toggle ms-5">
                                            <div class="form-check form-switch form-check-custom form-check-solid">
                                                <input class="form-check-input" type="checkbox" id="toggle_auto_suggest" checked />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!--end::Setting item-->

                                <!--begin::Setting item - Đơn vị tính-->
                                <div class="setting-item" data-section-id="product_units" onclick="window.location.href='{{ route('admin.settings.products.units.index') }}'">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="flex-grow-1">
                                            <div class="fw-bold text-gray-800 fs-6 mb-1">Đơn vị tính</div>
                                            <div class="text-muted fs-7">Quản lý hàng hóa theo đơn vị tính khác nhau như chiếc, lốc, thùng.</div>
                                        </div>
                                        <div class="d-flex align-items-center gap-3">
                                            <span class="text-gray-600 fs-6" id="units_count">
                                                <span class="spinner-border spinner-border-sm" role="status"></span>
                                            </span>
                                            <i class="fas fa-chevron-right text-gray-400"></i>
                                        </div>
                                    </div>
                                </div>
                                <!--end::Setting item-->

                                <!--begin::Setting item - Thuộc tính-->
                                <div class="setting-item" data-section-id="product_attributes" onclick="window.location.href='{{ route('admin.settings.products.attributes.index') }}'">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="flex-grow-1">
                                            <div class="fw-bold text-gray-800 fs-6 mb-1">Thuộc tính</div>
                                            <div class="text-muted fs-7">Quản lý hàng hóa theo đặc điểm riêng như màu sắc, kích cỡ, chất liệu.</div>
                                        </div>
                                        <div class="d-flex align-items-center gap-3">
                                            <span class="text-gray-600 fs-6" id="attributes_count">
                                                <span class="spinner-border spinner-border-sm" role="status"></span>
                                            </span>
                                            <i class="fas fa-chevron-right text-gray-400"></i>
                                        </div>
                                    </div>
                                </div>
                                <!--end::Setting item-->

                                <!--begin::Setting item - Nhóm hàng (Placeholder)-->
                                <div class="setting-item" data-section-id="product_groups" style="opacity: 0.6; cursor: not-allowed;" onclick="event.stopPropagation();">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="flex-grow-1">
                                            <div class="fw-bold text-gray-800 fs-6 mb-1">Nhóm hàng</div>
                                            <div class="text-muted fs-7">Quản lý hàng hóa theo chủng loại, đặc tính, công năng.</div>
                                        </div>
                                        <div class="d-flex align-items-center gap-3">
                                            <span class="text-gray-600 fs-6">113 nhóm hàng</span>
                                            <i class="fas fa-chevron-right text-gray-400"></i>
                                        </div>
                                    </div>
                                </div>
                                <!--end::Setting item-->

                                <!--begin::Setting item - Thương hiệu (Placeholder)-->
                                <div class="setting-item" data-section-id="product_brands" style="opacity: 0.6; cursor: not-allowed;" onclick="event.stopPropagation();">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="flex-grow-1">
                                            <div class="fw-bold text-gray-800 fs-6 mb-1">Thương hiệu</div>
                                            <div class="text-muted fs-7">Quản lý hàng hóa theo thương hiệu nhà sản xuất hoặc đơn sản phẩm.</div>
                                        </div>
                                        <div class="d-flex align-items-center gap-3">
                                            <span class="text-gray-600 fs-6">440 thương hiệu</span>
                                            <i class="fas fa-chevron-right text-gray-400"></i>
                                        </div>
                                    </div>
                                </div>
                                <!--end::Setting item-->

                                <!--begin::Setting item - Vị trí (Placeholder)-->
                                <div class="setting-item" data-section-id="product_locations" style="opacity: 0.6; cursor: not-allowed;" onclick="event.stopPropagation();">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="flex-grow-1">
                                            <div class="fw-bold text-gray-800 fs-6 mb-1">Vị trí</div>
                                            <div class="text-muted fs-7">Quản lý hàng hóa theo vị trí bán hàng hoặc lưu trữ như giá, kệ, tủ.</div>
                                        </div>
                                        <div class="d-flex align-items-center gap-3">
                                            <span class="text-gray-600 fs-6">11 vị trí</span>
                                            <i class="fas fa-chevron-right text-gray-400"></i>
                                        </div>
                                    </div>
                                </div>
                                <!--end::Setting item-->
                            </div>
                            <!--end::Settings list-->
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
// Load counts
$(document).ready(function() {
    // Load units count
    $.ajax({
        url: '{{ route('admin.settings.products.units.data') }}',
        method: 'GET',
        success: function(response) {
            if (response.success) {
                $('#units_count').text(response.total + ' đơn vị tính');
            }
        },
        error: function() {
            $('#units_count').text('-- đơn vị tính');
        }
    });

    // Load attributes count
    $.ajax({
        url: '{{ route('admin.settings.products.attributes.data') }}',
        method: 'GET',
        success: function(response) {
            if (response.success) {
                $('#attributes_count').text(response.total + ' thuộc tính');
            }
        },
        error: function() {
            $('#attributes_count').text('-- thuộc tính');
        }
    });

    // Search functionality
    $('#search_settings').on('input', function() {
        const searchTerm = $(this).val().toLowerCase();
        $('.setting-item').each(function() {
            const text = $(this).text().toLowerCase();
            $(this).toggle(text.includes(searchTerm));
        });
    });

    // Toggle switches
    $('#toggle_barcode, #toggle_auto_suggest').on('change', function() {
        const settingName = $(this).attr('id').replace('toggle_', '');
        const isEnabled = $(this).is(':checked');
        console.log(settingName + ':', isEnabled ? 'Enabled' : 'Disabled');
        // TODO: Save setting to backend
    });
});
</script>
@endsection

