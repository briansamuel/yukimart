@extends('admin.main-content')

@section('title', __('branch_shops.edit_branch_shop'))

@section('content')
    <!--begin::Content wrapper-->
    <div class="d-flex flex-column flex-column-fluid">
        <!--begin::Toolbar-->
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <!--begin::Toolbar container-->
            <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                <!--begin::Page title-->
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <!--begin::Title-->
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                        {{ __('branch_shops.edit_branch_shop') }}
                    </h1>
                    <!--end::Title-->
                    <!--begin::Breadcrumb-->
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ route('admin.dashboard') }}" class="text-muted text-hover-primary">{{ __('common.dashboard') }}</a>
                        </li>
                        <li class="breadcrumb-item">
                            <span class="bullet bg-gray-400 w-5px h-2px"></span>
                        </li>
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ route('admin.branch-shops.index') }}" class="text-muted text-hover-primary">{{ __('branch_shops.branch_shops') }}</a>
                        </li>
                        <li class="breadcrumb-item">
                            <span class="bullet bg-gray-400 w-5px h-2px"></span>
                        </li>
                        <li class="breadcrumb-item text-muted">{{ __('branch_shops.edit') }}</li>
                    </ul>
                    <!--end::Breadcrumb-->
                </div>
                <!--end::Page title-->
                <!--begin::Actions-->
                <div class="d-flex align-items-center gap-2 gap-lg-3">
                    <a href="{{ route('admin.branch-shops.index') }}" class="btn btn-sm fw-bold btn-secondary">
                        <i class="ki-duotone ki-arrow-left fs-3">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        {{ __('common.back') }}
                    </a>
                </div>
                <!--end::Actions-->
            </div>
            <!--end::Toolbar container-->
        </div>
        <!--end::Toolbar-->

        <!--begin::Content-->
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <!--begin::Content container-->
            <div id="kt_app_content_container" class="app-container container-xxl">
                <!--begin::Form-->
                <form id="kt_branch_shop_edit_form" class="form d-flex flex-column flex-lg-row" method="POST" action="{{ route('admin.branch-shops.update', $branchShop->id) }}">
                    @csrf
                    @method('PUT')
                    
                    <!--begin::Aside column-->
                    <div class="d-flex flex-column gap-7 gap-lg-10 w-100 w-lg-300px mb-7 me-lg-10">
                        <!--begin::Thumbnail settings-->
                        <div class="card card-flush py-4">
                            <!--begin::Card header-->
                            <div class="card-header">
                                <!--begin::Card title-->
                                <div class="card-title">
                                    <h2>{{ __('branch_shops.status') }}</h2>
                                </div>
                                <!--end::Card title-->
                            </div>
                            <!--end::Card header-->
                            <!--begin::Card body-->
                            <div class="card-body text-center pt-0">
                                <!--begin::Select2-->
                                <select class="form-select mb-2" name="status" data-control="select2" data-hide-search="true" data-placeholder="{{ __('branch_shops.select_status') }}">
                                    <option value="active" {{ $branchShop->status == 'active' ? 'selected' : '' }}>{{ __('branch_shops.active') }}</option>
                                    <option value="inactive" {{ $branchShop->status == 'inactive' ? 'selected' : '' }}>{{ __('branch_shops.inactive') }}</option>
                                    <option value="maintenance" {{ $branchShop->status == 'maintenance' ? 'selected' : '' }}>{{ __('branch_shops.maintenance') }}</option>
                                </select>
                                <!--end::Select2-->
                                <!--begin::Description-->
                                <div class="text-muted fs-7">{{ __('branch_shops.status_description') }}</div>
                                <!--end::Description-->
                            </div>
                            <!--end::Card body-->
                        </div>
                        <!--end::Thumbnail settings-->

                        <!--begin::Shop type-->
                        <div class="card card-flush py-4">
                            <!--begin::Card header-->
                            <div class="card-header">
                                <!--begin::Card title-->
                                <div class="card-title">
                                    <h2>{{ __('branch_shops.shop_type') }}</h2>
                                </div>
                                <!--end::Card title-->
                            </div>
                            <!--end::Card header-->
                            <!--begin::Card body-->
                            <div class="card-body text-center pt-0">
                                <!--begin::Select2-->
                                <select class="form-select mb-2" name="shop_type" data-control="select2" data-hide-search="true" data-placeholder="{{ __('branch_shops.select_shop_type') }}">
                                    <option value="flagship" {{ $branchShop->shop_type == 'flagship' ? 'selected' : '' }}>{{ __('branch_shops.flagship') }}</option>
                                    <option value="standard" {{ $branchShop->shop_type == 'standard' ? 'selected' : '' }}>{{ __('branch_shops.standard') }}</option>
                                    <option value="mini" {{ $branchShop->shop_type == 'mini' ? 'selected' : '' }}>{{ __('branch_shops.mini') }}</option>
                                    <option value="kiosk" {{ $branchShop->shop_type == 'kiosk' ? 'selected' : '' }}>{{ __('branch_shops.kiosk') }}</option>
                                </select>
                                <!--end::Select2-->
                                <!--begin::Description-->
                                <div class="text-muted fs-7">{{ __('branch_shops.shop_type_description') }}</div>
                                <!--end::Description-->
                            </div>
                            <!--end::Card body-->
                        </div>
                        <!--end::Shop type-->
                    </div>
                    <!--end::Aside column-->

                    <!--begin::Main column-->
                    <div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-10">
                        <!--begin::General options-->
                        <div class="card card-flush py-4">
                            <!--begin::Card header-->
                            <div class="card-header">
                                <div class="card-title">
                                    <h2>{{ __('branch_shops.general_information') }}</h2>
                                </div>
                            </div>
                            <!--end::Card header-->
                            <!--begin::Card body-->
                            <div class="card-body pt-0">
                                <!--begin::Input group-->
                                <div class="mb-10 fv-row">
                                    <!--begin::Label-->
                                    <label class="required form-label">{{ __('branch_shops.name') }}</label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <input type="text" name="name" class="form-control mb-2" placeholder="{{ __('branch_shops.name_placeholder') }}" value="{{ old('name', $branchShop->name) }}" />
                                    <!--end::Input-->
                                    <!--begin::Description-->
                                    <div class="text-muted fs-7">{{ __('branch_shops.name_description') }}</div>
                                    <!--end::Description-->
                                </div>
                                <!--end::Input group-->

                                <!--begin::Input group-->
                                <div class="mb-10 fv-row">
                                    <!--begin::Label-->
                                    <label class="form-label">{{ __('branch_shops.code') }}</label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <input type="text" name="code" class="form-control mb-2" placeholder="{{ __('branch_shops.code_placeholder') }}" value="{{ old('code', $branchShop->code) }}" />
                                    <!--end::Input-->
                                    <!--begin::Description-->
                                    <div class="text-muted fs-7">{{ __('branch_shops.code_description') }}</div>
                                    <!--end::Description-->
                                </div>
                                <!--end::Input group-->

                                <!--begin::Input group-->
                                <div class="row mb-10">
                                    <div class="col-md-6 fv-row">
                                        <!--begin::Label-->
                                        <label class="form-label">{{ __('branch_shops.phone') }}</label>
                                        <!--end::Label-->
                                        <!--begin::Input-->
                                        <input type="text" name="phone" class="form-control mb-2" placeholder="{{ __('branch_shops.phone_placeholder') }}" value="{{ old('phone', $branchShop->phone) }}" />
                                        <!--end::Input-->
                                    </div>
                                    <div class="col-md-6 fv-row">
                                        <!--begin::Label-->
                                        <label class="form-label">{{ __('branch_shops.email') }}</label>
                                        <!--end::Label-->
                                        <!--begin::Input-->
                                        <input type="email" name="email" class="form-control mb-2" placeholder="{{ __('branch_shops.email_placeholder') }}" value="{{ old('email', $branchShop->email) }}" />
                                        <!--end::Input-->
                                    </div>
                                </div>
                                <!--end::Input group-->

                                <!--begin::Input group-->
                                <div class="mb-10 fv-row">
                                    <!--begin::Label-->
                                    <label class="form-label">{{ __('branch_shops.manager') }}</label>
                                    <!--end::Label-->
                                    <!--begin::Select2-->
                                    <select class="form-select mb-2" name="manager_id" data-control="select2" data-placeholder="{{ __('branch_shops.select_manager') }}">
                                        <option value="">{{ __('branch_shops.no_manager') }}</option>
                                        @foreach($managers as $manager)
                                            <option value="{{ $manager['id'] }}" {{ $branchShop->manager_id == $manager['id'] ? 'selected' : '' }}>
                                                {{ $manager['text'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <!--end::Select2-->
                                </div>
                                <!--end::Input group-->

                                <!--begin::Input group-->
                                <div class="mb-10 fv-row">
                                    <!--begin::Label-->
                                    <label class="required form-label">{{ __('branch_shops.warehouse') }}</label>
                                    <!--end::Label-->
                                    <!--begin::Select2-->
                                    <select class="form-select mb-2" name="warehouse_id" data-control="select2" data-placeholder="{{ __('branch_shops.select_warehouse') }}">
                                        <option value="">{{ __('branch_shops.select_warehouse') }}</option>
                                        @foreach($warehouses as $warehouse)
                                            <option value="{{ $warehouse->id }}" {{ $branchShop->warehouse_id == $warehouse->id ? 'selected' : '' }}>
                                                {{ $warehouse->name }} ({{ $warehouse->code }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <!--end::Select2-->
                                    <!--begin::Description-->
                                    <div class="text-muted fs-7">{{ __('branch_shops.warehouse_description') }}</div>
                                    <!--end::Description-->
                                </div>
                                <!--end::Input group-->
                            </div>
                            <!--end::Card body-->
                        </div>
                        <!--end::General options-->

                        <!--begin::Address information-->
                        <div class="card card-flush py-4">
                            <!--begin::Card header-->
                            <div class="card-header">
                                <div class="card-title">
                                    <h2>{{ __('branch_shops.address_information') }}</h2>
                                </div>
                            </div>
                            <!--end::Card header-->
                            <!--begin::Card body-->
                            <div class="card-body pt-0">
                                <!--begin::Input group-->
                                <div class="mb-10 fv-row">
                                    <!--begin::Label-->
                                    <label class="required form-label">{{ __('branch_shops.address') }}</label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <textarea name="address" class="form-control mb-2" rows="3" placeholder="{{ __('branch_shops.address_placeholder') }}">{{ old('address', $branchShop->address) }}</textarea>
                                    <!--end::Input-->
                                </div>
                                <!--end::Input group-->

                                <!--begin::Input group-->
                                <div class="row mb-10">
                                    <div class="col-md-4 fv-row">
                                        <!--begin::Label-->
                                        <label class="required form-label">{{ __('branch_shops.province') }}</label>
                                        <!--end::Label-->
                                        <!--begin::Input-->
                                        <input type="text" name="province" class="form-control mb-2" placeholder="{{ __('branch_shops.province_placeholder') }}" value="{{ old('province', $branchShop->province) }}" />
                                        <!--end::Input-->
                                    </div>
                                    <div class="col-md-4 fv-row">
                                        <!--begin::Label-->
                                        <label class="required form-label">{{ __('branch_shops.district') }}</label>
                                        <!--end::Label-->
                                        <!--begin::Input-->
                                        <input type="text" name="district" class="form-control mb-2" placeholder="{{ __('branch_shops.district_placeholder') }}" value="{{ old('district', $branchShop->district) }}" />
                                        <!--end::Input-->
                                    </div>
                                    <div class="col-md-4 fv-row">
                                        <!--begin::Label-->
                                        <label class="required form-label">{{ __('branch_shops.ward') }}</label>
                                        <!--end::Label-->
                                        <!--begin::Input-->
                                        <input type="text" name="ward" class="form-control mb-2" placeholder="{{ __('branch_shops.ward_placeholder') }}" value="{{ old('ward', $branchShop->ward) }}" />
                                        <!--end::Input-->
                                    </div>
                                </div>
                                <!--end::Input group-->
                            </div>
                            <!--end::Card body-->
                        </div>
                        <!--end::Address information-->

                        <div class="d-flex justify-content-end">
                            <!--begin::Button-->
                            <a href="{{ route('admin.branch-shops.index') }}" id="kt_branch_shop_edit_cancel" class="btn btn-light me-5">{{ __('common.cancel') }}</a>
                            <!--end::Button-->
                            <!--begin::Button-->
                            <button type="submit" id="kt_branch_shop_edit_submit" class="btn btn-primary">
                                <span class="indicator-label">{{ __('common.save_changes') }}</span>
                                <span class="indicator-progress">{{ __('common.please_wait') }}...
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                            </button>
                            <!--end::Button-->
                        </div>
                    </div>
                    <!--end::Main column-->
                </form>
                <!--end::Form-->
            </div>
            <!--end::Content container-->
        </div>
        <!--end::Content-->
    </div>
    <!--end::Content wrapper-->
@endsection

@section('scripts')
    <script src="{{ asset('admin-assets/assets/js/custom/apps/branch-shops/edit.js') }}"></script>
@endsection
