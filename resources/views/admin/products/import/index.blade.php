@extends('admin.app')

@section('title', __('product.import_products'))

@section('content')
<div class="d-flex flex-column flex-column-fluid">
    <!--begin::Toolbar-->
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
        <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                    {{ __('product.import_products') }}
                </h1>
                <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('admin.dashboard') }}" class="text-muted text-hover-primary">{{ __('product.dashboard') }}</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-400 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('admin.products.list') }}" class="text-muted text-hover-primary">{{ __('product.products') }}</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-400 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-muted">{{ __('product.import') }}</li>
                </ul>
            </div>
            <div class="d-flex align-items-center gap-2 gap-lg-3">
                <a href="{{ route('admin.products.import.template') }}" class="btn btn-sm btn-light-primary">
                    <i class="ki-duotone ki-file-down fs-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    {{ __('product.download_template') }}
                </a>
                <a href="{{ route('admin.products.list') }}" class="btn btn-sm btn-secondary">
                    <i class="ki-duotone ki-arrow-left fs-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    {{ __('product.back_to_products') }}
                </a>
            </div>
        </div>
    </div>
    <!--end::Toolbar-->

    <!--begin::Content-->
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-xxl">
            
            <!-- Import Steps -->
            <div class="card mb-5">
                <div class="card-body">
                    <div class="stepper stepper-pills stepper-column d-flex flex-column flex-xl-row flex-row-fluid" id="kt_import_stepper">
                        <!--begin::Aside-->
                        <div class="card d-flex justify-content-center justify-content-xl-start flex-row-auto w-100 w-xl-300px w-xxl-400px me-9">
                            <div class="card-body px-6 px-lg-10 px-xxl-15 py-20">
                                <!--begin::Nav-->
                                <div class="stepper-nav">
                                    <!--begin::Step 1-->
                                    <div class="stepper-item current" data-kt-stepper-element="nav">
                                        <div class="stepper-wrapper">
                                            <div class="stepper-icon w-40px h-40px">
                                                <i class="stepper-check fas fa-check"></i>
                                                <span class="stepper-number">1</span>
                                            </div>
                                            <div class="stepper-label">
                                                <h3 class="stepper-title">{{ __('product.upload_file') }}</h3>
                                                <div class="stepper-desc fw-semibold">{{ __('product.upload_excel_csv_file') }}</div>
                                            </div>
                                        </div>
                                        <div class="stepper-line h-40px"></div>
                                    </div>
                                    <!--end::Step 1-->

                                    <!--begin::Step 2-->
                                    <div class="stepper-item" data-kt-stepper-element="nav">
                                        <div class="stepper-wrapper">
                                            <div class="stepper-icon w-40px h-40px">
                                                <i class="stepper-check fas fa-check"></i>
                                                <span class="stepper-number">2</span>
                                            </div>
                                            <div class="stepper-label">
                                                <h3 class="stepper-title">{{ __('product.map_columns') }}</h3>
                                                <div class="stepper-desc fw-semibold">{{ __('product.map_file_columns_to_product_fields') }}</div>
                                            </div>
                                        </div>
                                        <div class="stepper-line h-40px"></div>
                                    </div>
                                    <!--end::Step 2-->

                                    <!--begin::Step 3-->
                                    <div class="stepper-item" data-kt-stepper-element="nav">
                                        <div class="stepper-wrapper">
                                            <div class="stepper-icon w-40px h-40px">
                                                <i class="stepper-check fas fa-check"></i>
                                                <span class="stepper-number">3</span>
                                            </div>
                                            <div class="stepper-label">
                                                <h3 class="stepper-title">{{ __('product.validate_data') }}</h3>
                                                <div class="stepper-desc fw-semibold">{{ __('product.validate_import_data') }}</div>
                                            </div>
                                        </div>
                                        <div class="stepper-line h-40px"></div>
                                    </div>
                                    <!--end::Step 3-->

                                    <!--begin::Step 4-->
                                    <div class="stepper-item" data-kt-stepper-element="nav">
                                        <div class="stepper-wrapper">
                                            <div class="stepper-icon w-40px h-40px">
                                                <i class="stepper-check fas fa-check"></i>
                                                <span class="stepper-number">4</span>
                                            </div>
                                            <div class="stepper-label">
                                                <h3 class="stepper-title">{{ __('product.import_complete') }}</h3>
                                                <div class="stepper-desc fw-semibold">{{ __('product.review_import_results') }}</div>
                                            </div>
                                        </div>
                                    </div>
                                    <!--end::Step 4-->
                                </div>
                                <!--end::Nav-->
                            </div>
                        </div>
                        <!--begin::Aside-->

                        <!--begin::Content-->
                        <div class="card d-flex flex-row-fluid flex-center">
                            <form class="card-body py-20 w-100 mw-xl-700px px-9" novalidate="novalidate" id="kt_import_form">
                                <!--begin::Step 1-->
                                <div class="current" data-kt-stepper-element="content">
                                    <div class="w-100">
                                        <!--begin::Heading-->
                                        <div class="pb-10 pb-lg-15">
                                            <h2 class="fw-bold d-flex align-items-center text-dark">
                                                {{ __('product.upload_import_file') }}
                                                <i class="fas fa-exclamation-circle ms-2 fs-7" data-bs-toggle="tooltip" 
                                                   title="{{ __('product.supported_formats_xlsx_xls_csv') }}"></i>
                                            </h2>
                                            <div class="text-muted fw-semibold fs-6">
                                                {{ __('product.upload_file_description') }}
                                            </div>
                                        </div>
                                        <!--end::Heading-->

                                        <!--begin::Input group-->
                                        <div class="mb-10 fv-row">
                                            <label class="form-label mb-3">{{ __('product.select_file') }}</label>
                                            <input type="file" class="form-control form-control-lg form-control-solid" 
                                                   id="import_file" name="import_file" accept=".xlsx,.xls,.csv" />
                                            <div class="form-text">{{ __('product.max_file_size_100mb') }}</div>
                                        </div>
                                        <!--end::Input group-->

                                        <!--begin::File info-->
                                        <div id="file_info" class="d-none">
                                            <div class="notice d-flex bg-light-primary rounded border-primary border border-dashed p-6">
                                                <i class="ki-duotone ki-information-5 fs-2tx text-primary me-4">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                    <span class="path3"></span>
                                                </i>
                                                <div class="d-flex flex-stack flex-grow-1">
                                                    <div class="fw-semibold">
                                                        <h4 class="text-gray-900 fw-bold">{{ __('product.file_uploaded') }}</h4>
                                                        <div class="fs-6 text-gray-700" id="file_details"></div>
                                                        <div class="mt-3">
                                                            <button type="button" class="btn btn-sm btn-light-primary" id="viewFileStatsBtn">
                                                                <i class="ki-duotone ki-chart-simple fs-2 me-2">
                                                                    <span class="path1"></span>
                                                                    <span class="path2"></span>
                                                                    <span class="path3"></span>
                                                                    <span class="path4"></span>
                                                                </i>
                                                                {{ __('product.view_file_statistics') }}
                                                            </button>
                                                            <button type="button" class="btn btn-sm btn-light-info ms-2" id="viewDetailedPreviewBtn">
                                                                <i class="ki-duotone ki-eye fs-2 me-2">
                                                                    <span class="path1"></span>
                                                                    <span class="path2"></span>
                                                                    <span class="path3"></span>
                                                                </i>
                                                                {{ __('product.view_detailed_preview') }}
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!--end::File info-->
                                    </div>
                                </div>
                                <!--end::Step 1-->

                                <!--begin::Step 2-->
                                <div data-kt-stepper-element="content">
                                    <div class="w-100">
                                        <!--begin::Heading-->
                                        <div class="pb-10 pb-lg-12">
                                            <h2 class="fw-bold text-dark">{{ __('product.map_columns') }}</h2>
                                            <div class="text-muted fw-semibold fs-6">
                                                {{ __('product.map_columns_description') }}
                                            </div>
                                        </div>
                                        <!--end::Heading-->

                                        <!--begin::Column mapping-->
                                        <div id="column_mapping_container">
                                            <!-- Column mapping will be populated by JavaScript -->
                                            <div class="alert alert-warning">
                                                <h6>Debug Info:</h6>
                                                <button type="button" class="btn btn-sm btn-warning" onclick="window.productImport.setupColumnMapping()">
                                                    Test Setup Column Mapping
                                                </button>
                                                <button type="button" class="btn btn-sm btn-info" onclick="console.log('Debug:', {fileData: window.productImport.fileData, availableFields: window.productImport.availableFields})">
                                                    Log Debug Info
                                                </button>
                                            </div>
                                        </div>
                                        <!--end::Column mapping-->

                                        <!--begin::Preview-->
                                        <div class="mb-10">
                                            <h4 class="fw-bold text-dark mb-5">{{ __('product.data_preview') }}</h4>
                                            <div class="table-responsive">
                                                <table class="table table-rounded table-striped border gy-7 gs-7" id="preview_table">
                                                    <thead>
                                                        <tr class="fw-semibold fs-6 text-gray-800 border-bottom-2 border-gray-200">
                                                            <!-- Headers will be populated by JavaScript -->
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <!-- Preview data will be populated by JavaScript -->
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <!--end::Preview-->
                                    </div>
                                </div>
                                <!--end::Step 2-->

                                <!--begin::Step 3-->
                                <div data-kt-stepper-element="content">
                                    <div class="w-100">
                                        <!--begin::Heading-->
                                        <div class="pb-10 pb-lg-12">
                                            <h2 class="fw-bold text-dark">{{ __('product.validate_data') }}</h2>
                                            <div class="text-muted fw-semibold fs-6">
                                                {{ __('product.validation_description') }}
                                            </div>
                                        </div>
                                        <!--end::Heading-->

                                        <!--begin::Import options-->
                                        <div class="mb-10">
                                            <h4 class="fw-bold text-dark mb-5">{{ __('product.import_options') }}</h4>
                                            <div class="form-check form-check-custom form-check-solid mb-5">
                                                <input class="form-check-input" type="checkbox" value="1" id="update_existing" name="update_existing" checked />
                                                <label class="form-check-label fw-semibold text-gray-700 fs-6" for="update_existing">
                                                    {{ __('product.update_existing_products') }}
                                                </label>
                                                <div class="form-text">{{ __('product.update_existing_description') }}</div>
                                            </div>
                                        </div>
                                        <!--end::Import options-->

                                        <!--begin::Debug info-->
                                        <div class="alert alert-info mb-5">
                                            <h6>Debug Info:</h6>
                                            <button type="button" class="btn btn-sm btn-info me-2" onclick="console.log('Column Mapping:', window.productImport.columnMapping)">
                                                Log Column Mapping
                                            </button>
                                            <button type="button" class="btn btn-sm btn-warning me-2" onclick="window.productImport.validateImportData()">
                                                Test Validate
                                            </button>
                                            <button type="button" class="btn btn-sm btn-success me-2" onclick="window.productImport.stepper.goPrevious()">
                                                Test Previous
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger me-2" onclick="console.log('Validation Results:', window.productImport.validationResults)">
                                                Log Validation Results
                                            </button>
                                            <button type="button" class="btn btn-sm btn-primary" onclick="window.productImport.processImport()">
                                                Test Import
                                            </button>
                                        </div>
                                        <!--end::Debug info-->

                                        <!--begin::Validation results-->
                                        <div id="validation_results">
                                            <!-- Validation results will be populated by JavaScript -->
                                        </div>
                                        <!--end::Validation results-->
                                    </div>
                                </div>
                                <!--end::Step 3-->

                                <!--begin::Step 4-->
                                <div data-kt-stepper-element="content">
                                    <div class="w-100">
                                        <!--begin::Heading-->
                                        <div class="pb-10 pb-lg-15">
                                            <h2 class="fw-bold text-dark">{{ __('product.import_complete') }}</h2>
                                            <div class="text-muted fw-semibold fs-6">
                                                {{ __('product.import_results_description') }}
                                            </div>
                                        </div>
                                        <!--end::Heading-->

                                        <!--begin::Import results-->
                                        <div id="import_results">
                                            <!-- Import results will be populated by JavaScript -->
                                        </div>
                                        <!--end::Import results-->
                                    </div>
                                </div>
                                <!--end::Step 4-->

                                <!--begin::Actions-->
                                <div class="d-flex flex-stack pt-15">
                                    <div class="mr-2">
                                        <button type="button" class="btn btn-lg btn-light-primary me-3" data-kt-stepper-action="previous">
                                            <i class="ki-duotone ki-arrow-left fs-4 me-1">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                            {{ __('product.previous') }}
                                        </button>
                                    </div>
                                    <div>
                                        <button type="submit" class="btn btn-lg btn-success me-3" data-kt-stepper-action="submit">
                                            <span class="indicator-label">
                                                {{ __('product.import_products') }}
                                                <i class="ki-duotone ki-arrow-right fs-3 ms-2 me-0">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                            </span>
                                            <span class="indicator-progress">
                                                {{ __('product.please_wait') }}...
                                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                            </span>
                                        </button>
                                        <button type="button" class="btn btn-lg btn-primary" data-kt-stepper-action="next">
                                            {{ __('product.next') }}
                                            <i class="ki-duotone ki-arrow-right fs-4 ms-1 me-0">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                        </button>
                                    </div>
                                </div>
                                <!--end::Actions-->
                            </form>
                        </div>
                        <!--end::Content-->
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--end::Content-->
</div>

<!-- File Statistics Modal -->
<div class="modal fade" id="fileStatsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold">{{ __('product.file_statistics') }}</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                </div>
            </div>
            <div class="modal-body">
                <div id="file_stats_content">
                    <!-- File statistics will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('product.close') }}</button>
            </div>
        </div>
    </div>
</div>

<!-- Detailed Preview Modal -->
<div class="modal fade" id="detailedPreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold">{{ __('product.detailed_file_preview') }}</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                </div>
            </div>
            <div class="modal-body">
                <div class="d-flex justify-content-between align-items-center mb-5">
                    <div>
                        <span class="text-muted">{{ __('product.showing') }}</span>
                        <span id="preview_showing_info" class="fw-bold"></span>
                    </div>
                    <div>
                        <button type="button" class="btn btn-sm btn-light-primary" id="refreshPreviewBtn">
                            <i class="ki-duotone ki-arrows-circle fs-2 me-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            {{ __('product.refresh') }}
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-rounded table-striped border gy-7 gs-7" id="detailed_preview_table">
                        <thead>
                            <tr class="fw-semibold fs-6 text-gray-800 border-bottom-2 border-gray-200">
                                <!-- Headers will be populated by JavaScript -->
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Preview data will be populated by JavaScript -->
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-5">
                    <div class="text-muted">
                        <span id="preview_pagination_info"></span>
                    </div>
                    <div>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm btn-light" id="prevPageBtn" disabled>
                                <i class="ki-duotone ki-arrow-left fs-2"></i>
                                {{ __('product.previous') }}
                            </button>
                            <button type="button" class="btn btn-sm btn-light" id="nextPageBtn">
                                {{ __('product.next') }}
                                <i class="ki-duotone ki-arrow-right fs-2"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('product.close') }}</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="{{ asset('admin/js/product-import.js') }}"></script>
@endsection
