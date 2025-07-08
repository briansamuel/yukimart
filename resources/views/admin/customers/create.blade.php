@extends('admin.index')
@section('page-header', __('customer.add_customer'))
@section('page-sub_header', __('customer.create_new_customer'))

@section('content')
<!--begin::Form-->
<form id="kt_customer_form" class="form d-flex flex-column flex-lg-row" action="{{ route('admin.customers.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <!--begin::Aside column-->
    <div class="d-flex flex-column gap-7 gap-lg-10 w-100 w-lg-300px mb-7 me-lg-10">
        <!--begin::Thumbnail settings-->
        <div class="card card-flush py-4">
            <!--begin::Card header-->
            <div class="card-header">
                <!--begin::Card title-->
                <div class="card-title">
                    <h2>{{ __('customer.avatar') }}</h2>
                </div>
                <!--end::Card title-->
            </div>
            <!--end::Card header-->
            <!--begin::Card body-->
            <div class="card-body text-center pt-0">
                <!--begin::Image input-->
                <div class="image-input image-input-empty image-input-outline image-input-placeholder mb-3" data-kt-image-input="true">
                    <!--begin::Preview existing avatar-->
                    <div class="image-input-wrapper w-150px h-150px"></div>
                    <!--end::Preview existing avatar-->
                    <!--begin::Label-->
                    <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="change" data-bs-toggle="tooltip" title="{{ __('customer.change_avatar') }}">
                        <i class="ki-duotone ki-pencil fs-7">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        <!--begin::Inputs-->
                        <input type="file" name="avatar" accept=".png, .jpg, .jpeg" />
                        <input type="hidden" name="avatar_remove" />
                        <!--end::Inputs-->
                    </label>
                    <!--end::Label-->
                    <!--begin::Cancel-->
                    <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="cancel" data-bs-toggle="tooltip" title="{{ __('customer.cancel_avatar') }}">
                        <i class="ki-duotone ki-cross fs-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                    </span>
                    <!--end::Cancel-->
                    <!--begin::Remove-->
                    <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="remove" data-bs-toggle="tooltip" title="{{ __('customer.remove_avatar') }}">
                        <i class="ki-duotone ki-cross fs-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                    </span>
                    <!--end::Remove-->
                </div>
                <!--end::Image input-->
                <!--begin::Description-->
                <div class="text-muted fs-7">{{ __('customer.avatar_description') }}</div>
                <!--end::Description-->
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Thumbnail settings-->
        <!--begin::Status-->
        <div class="card card-flush py-4">
            <!--begin::Card header-->
            <div class="card-header">
                <!--begin::Card title-->
                <div class="card-title">
                    <h2>{{ __('customer.status') }}</h2>
                </div>
                <!--end::Card title-->
                <!--begin::Card toolbar-->
                <div class="card-toolbar">
                    <div class="rounded-circle bg-success w-15px h-15px" id="kt_customer_status"></div>
                </div>
                <!--end::Card toolbar-->
            </div>
            <!--end::Card header-->
            <!--begin::Card body-->
            <div class="card-body pt-0">
                <!--begin::Select2-->
                <select class="form-select mb-2" name="status" data-control="select2" data-hide-search="true" data-placeholder="{{ __('customer.select_status') }}" id="kt_customer_status_select">
                    <option></option>
                    <option value="active" selected="selected">{{ __('customer.active') }}</option>
                    <option value="inactive">{{ __('customer.inactive') }}</option>
                </select>
                <!--end::Select2-->
                <!--begin::Description-->
                <div class="text-muted fs-7">{{ __('customer.status_description') }}</div>
                <!--end::Description-->
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Status-->
        <!--begin::Customer type-->
        <div class="card card-flush py-4">
            <!--begin::Card header-->
            <div class="card-header">
                <!--begin::Card title-->
                <div class="card-title">
                    <h2>{{ __('customer.customer_type') }}</h2>
                </div>
                <!--end::Card title-->
            </div>
            <!--end::Card header-->
            <!--begin::Card body-->
            <div class="card-body pt-0">
                <!--begin::Select2-->
                <select class="form-select mb-2" name="customer_type" data-control="select2" data-hide-search="true" data-placeholder="{{ __('customer.select_type') }}">
                    <option></option>
                    <option value="individual" selected="selected">{{ __('customer.individual') }}</option>
                    <option value="business">{{ __('customer.business') }}</option>
                </select>
                <!--end::Select2-->
                <!--begin::Description-->
                <div class="text-muted fs-7">{{ __('customer.type_description') }}</div>
                <!--end::Description-->
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Customer type-->
    </div>
    <!--end::Aside column-->
    <!--begin::Main column-->
    <div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-10">
        <!--begin::General options-->
        <div class="card card-flush py-4">
            <!--begin::Card header-->
            <div class="card-header">
                <div class="card-title">
                    <h2>{{ __('customer.general_information') }}</h2>
                </div>
            </div>
            <!--end::Card header-->
            <!--begin::Card body-->
            <div class="card-body pt-0">
                <!--begin::Input group-->
                <div class="mb-10 fv-row">
                    <!--begin::Label-->
                    <label class="required form-label">{{ __('customer.name') }}</label>
                    <!--end::Label-->
                    <!--begin::Input-->
                    <input type="text" name="name" class="form-control mb-2" placeholder="{{ __('customer.enter_name') }}" value="{{ old('name') }}" />
                    <!--end::Input-->
                    <!--begin::Description-->
                    <div class="text-muted fs-7">{{ __('customer.name_description') }}</div>
                    <!--end::Description-->
                </div>
                <!--end::Input group-->
                <!--begin::Input group-->
                <div class="row">
                    <!--begin::Input group-->
                    <div class="col-md-6 mb-10 fv-row">
                        <!--begin::Label-->
                        <label class="required form-label">{{ __('customer.email') }}</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <input type="email" name="email" class="form-control mb-2" placeholder="{{ __('customer.enter_email') }}" value="{{ old('email') }}" />
                        <!--end::Input-->
                    </div>
                    <!--end::Input group-->
                    <!--begin::Input group-->
                    <div class="col-md-6 mb-10 fv-row">
                        <!--begin::Label-->
                        <label class="form-label">{{ __('customer.phone') }}</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <input type="text" name="phone" class="form-control mb-2" placeholder="{{ __('customer.enter_phone') }}" value="{{ old('phone') }}" />
                        <!--end::Input-->
                    </div>
                    <!--end::Input group-->
                </div>
                <!--begin::Input group-->
                <div class="mb-10 fv-row">
                    <!--begin::Label-->
                    <label class="form-label">{{ __('customer.address') }}</label>
                    <!--end::Label-->
                    <!--begin::Input-->
                    <textarea name="address" class="form-control mb-2" rows="3" placeholder="{{ __('customer.enter_address') }}">{{ old('address') }}</textarea>
                    <!--end::Input-->
                </div>
                <!--end::Input group-->
                <!--begin::Input group-->
                <div class="row">
                    <!--begin::Input group-->
                    <div class="col-md-4 mb-10 fv-row">
                        <!--begin::Label-->
                        <label class="form-label">{{ __('customer.city') }}</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <input type="text" name="city" class="form-control mb-2" placeholder="{{ __('customer.enter_city') }}" value="{{ old('city') }}" />
                        <!--end::Input-->
                    </div>
                    <!--end::Input group-->
                    <!--begin::Input group-->
                    <div class="col-md-4 mb-10 fv-row">
                        <!--begin::Label-->
                        <label class="form-label">{{ __('customer.district') }}</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <input type="text" name="district" class="form-control mb-2" placeholder="{{ __('customer.enter_district') }}" value="{{ old('district') }}" />
                        <!--end::Input-->
                    </div>
                    <!--end::Input group-->
                    <!--begin::Input group-->
                    <div class="col-md-4 mb-10 fv-row">
                        <!--begin::Label-->
                        <label class="form-label">{{ __('customer.ward') }}</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <input type="text" name="ward" class="form-control mb-2" placeholder="{{ __('customer.enter_ward') }}" value="{{ old('ward') }}" />
                        <!--end::Input-->
                    </div>
                    <!--end::Input group-->
                </div>
                <!--begin::Input group-->
                <div class="row">
                    <!--begin::Input group-->
                    <div class="col-md-6 mb-10 fv-row">
                        <!--begin::Label-->
                        <label class="form-label">{{ __('customer.date_of_birth') }}</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <input type="date" name="date_of_birth" class="form-control mb-2" value="{{ old('date_of_birth') }}" />
                        <!--end::Input-->
                    </div>
                    <!--end::Input group-->
                    <!--begin::Input group-->
                    <div class="col-md-6 mb-10 fv-row">
                        <!--begin::Label-->
                        <label class="form-label">{{ __('customer.gender') }}</label>
                        <!--end::Label-->
                        <!--begin::Select2-->
                        <select class="form-select mb-2" name="gender" data-control="select2" data-hide-search="true" data-placeholder="{{ __('customer.select_gender') }}">
                            <option></option>
                            <option value="male">{{ __('customer.male') }}</option>
                            <option value="female">{{ __('customer.female') }}</option>
                            <option value="other">{{ __('customer.other') }}</option>
                        </select>
                        <!--end::Select2-->
                    </div>
                    <!--end::Input group-->
                </div>
                <!--begin::Input group-->
                <div class="mb-0 fv-row">
                    <!--begin::Label-->
                    <label class="form-label">{{ __('customer.notes') }}</label>
                    <!--end::Label-->
                    <!--begin::Editor-->
                    <textarea name="notes" class="form-control mb-2" rows="4" placeholder="{{ __('customer.enter_notes') }}">{{ old('notes') }}</textarea>
                    <!--end::Editor-->
                </div>
                <!--end::Input group-->
            </div>
            <!--end::Card body-->
        </div>
        <!--end::General options-->

        <div class="d-flex justify-content-end">
            <!--begin::Button-->
            <a href="{{ route('admin.customers.index') }}" id="kt_customer_cancel" class="btn btn-light me-5">{{ __('customer.cancel') }}</a>
            <!--end::Button-->
            <!--begin::Button-->
            <button type="submit" id="kt_customer_submit" class="btn btn-primary">
                <span class="indicator-label">{{ __('customer.save_customer') }}</span>
                <span class="indicator-progress">{{ __('customer.please_wait') }}...
                    <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                </span>
            </button>
            <!--end::Button-->
        </div>
    </div>
    <!--end::Main column-->
</form>
<!--end::Form-->
@endsection

@push('scripts')
<script src="{{ asset('admin-assets/assets/js/custom/apps/customers/add.js') }}"></script>
@endpush
