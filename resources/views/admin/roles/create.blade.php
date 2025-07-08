@extends('admin.index')
@section('page-header', __('roles.create_role'))
@section('page-sub_header', __('roles.subtitle'))

@section('content')
<!--begin::Form-->
<form id="kt_role_form" class="form d-flex flex-column flex-lg-row fv-plugins-bootstrap5 fv-plugins-framework" action="{{ route('admin.roles.store') }}" method="POST">
    @csrf
    <!--begin::Aside column-->
    <div class="d-flex flex-column gap-7 gap-lg-10 w-100 w-lg-300px mb-7 me-lg-10">
        <!--begin::Thumbnail settings-->
        <div class="card card-flush py-4">
            <!--begin::Card header-->
            <div class="card-header">
                <!--begin::Card title-->
                <div class="card-title">
                    <h2>{{ __('roles.role_details') }}</h2>
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
                    <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="change" data-bs-toggle="tooltip" title="{{ __('roles.tooltips.change_avatar') }}">
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
                    <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="cancel" data-bs-toggle="tooltip" title="{{ __('common.cancel_avatar') }}">
                        <i class="ki-duotone ki-cross fs-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                    </span>
                    <!--end::Cancel-->
                    <!--begin::Remove-->
                    <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="remove" data-bs-toggle="tooltip" title="{{ __('common.remove_avatar') }}">
                        <i class="ki-duotone ki-cross fs-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                    </span>
                    <!--end::Remove-->
                </div>
                <!--end::Image input-->
                <!--begin::Description-->
                <div class="text-muted fs-7">{{ __('roles.help.role_avatar') }}</div>
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
                    <h2>{{ __('roles.status') }}</h2>
                </div>
                <!--end::Card title-->
                <!--begin::Card toolbar-->
                <div class="card-toolbar">
                    <div class="rounded-circle bg-success w-15px h-15px" id="kt_role_status"></div>
                </div>
                <!--end::Card toolbar-->
            </div>
            <!--end::Card header-->
            <!--begin::Card body-->
            <div class="card-body pt-0">
                <!--begin::Select2-->
                <select class="form-select mb-2" data-control="select2" data-hide-search="true" data-placeholder="{{ __('roles.select_role') }}" id="kt_role_status_select" name="is_active">
                    <option></option>
                    <option value="1" selected="selected">{{ __('roles.active') }}</option>
                    <option value="0">{{ __('roles.inactive') }}</option>
                </select>
                <!--end::Select2-->
                <!--begin::Description-->
                <div class="text-muted fs-7">{{ __('roles.help.status') }}</div>
                <!--end::Description-->
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Status-->
    </div>
    <!--end::Aside column-->
    <!--begin::Main column-->
    <div class="d-flex flex-column flex-row-fluid gap-7 gap-lg-10">
        <!--begin::General options-->
        <div class="card card-flush py-4">
            <!--begin::Card header-->
            <div class="card-header">
                <div class="card-title">
                    <h2>{{ __('roles.tabs.general') }}</h2>
                </div>
            </div>
            <!--end::Card header-->
            <!--begin::Card body-->
            <div class="card-body pt-0">
                <!--begin::Input group-->
                <div class="mb-10 fv-row fv-plugins-icon-container">
                    <!--begin::Label-->
                    <label class="required form-label">{{ __('roles.name') }}</label>
                    <!--end::Label-->
                    <!--begin::Input-->
                    <input type="text" name="name" class="form-control mb-2" placeholder="{{ __('roles.enter_role_name') }}" value="{{ old('name') }}" />
                    <!--end::Input-->
                    <!--begin::Description-->
                    <div class="text-muted fs-7">{{ __('roles.help.role_name') }}</div>
                    <!--end::Description-->
                    <div class="fv-plugins-message-container invalid-feedback"></div>
                </div>
                <!--end::Input group-->
                <!--begin::Input group-->
                <div class="mb-10 fv-row fv-plugins-icon-container">
                    <!--begin::Label-->
                    <label class="required form-label">{{ __('roles.display_name') }}</label>
                    <!--end::Label-->
                    <!--begin::Input-->
                    <input type="text" name="display_name" class="form-control mb-2" placeholder="{{ __('roles.enter_display_name') }}" value="{{ old('display_name') }}" />
                    <!--end::Input-->
                    <!--begin::Description-->
                    <div class="text-muted fs-7">{{ __('roles.help.display_name') }}</div>
                    <!--end::Description-->
                    <div class="fv-plugins-message-container invalid-feedback"></div>
                </div>
                <!--end::Input group-->
                <!--begin::Input group-->
                <div class="mb-10 fv-row">
                    <!--begin::Label-->
                    <label class="form-label">{{ __('roles.description') }}</label>
                    <!--end::Label-->
                    <!--begin::Editor-->
                    <textarea name="description" class="form-control mb-2" rows="4" placeholder="{{ __('roles.enter_description') }}">{{ old('description') }}</textarea>
                    <!--end::Editor-->
                    <!--begin::Description-->
                    <div class="text-muted fs-7">{{ __('roles.help.description') }}</div>
                    <!--end::Description-->
                </div>
                <!--end::Input group-->
                <!--begin::Input group-->
                <div class="mb-0 fv-row">
                    <!--begin::Label-->
                    <label class="form-label">{{ __('roles.sort_order') }}</label>
                    <!--end::Label-->
                    <!--begin::Input-->
                    <input type="number" name="sort_order" class="form-control mb-2" placeholder="0" value="{{ old('sort_order', 0) }}" />
                    <!--end::Input-->
                    <!--begin::Description-->
                    <div class="text-muted fs-7">{{ __('roles.help.sort_order') }}</div>
                    <!--end::Description-->
                </div>
                <!--end::Input group-->
            </div>
            <!--end::Card body-->
        </div>
        <!--end::General options-->
        <!--begin::Permissions-->
        <div class="card card-flush py-4">
            <!--begin::Card header-->
            <div class="card-header">
                <div class="card-title">
                    <h2>{{ __('roles.role_permissions') }}</h2>
                </div>
            </div>
            <!--end::Card header-->
            <!--begin::Card body-->
            <div class="card-body pt-0">
                <!--begin::Table wrapper-->
                <div class="table-responsive">
                    <!--begin::Table-->
                    <table class="table align-middle table-row-dashed fs-6 gy-5">
                        <!--begin::Table body-->
                        <tbody class="text-gray-600 fw-bold">
                            <!--begin::Table row-->
                            <tr>
                                <td class="text-gray-800">{{ __('roles.permission_modules.administrator_access') }}
                                    <i class="ki-duotone ki-information-5 ms-1 fs-7" data-bs-toggle="tooltip" title="{{ __('roles.help.administrator_access') }}">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                        <span class="path3"></span>
                                    </i>
                                </td>
                                <td>
                                    <!--begin::Checkbox-->
                                    <label class="form-check form-check-custom form-check-solid me-9">
                                        <input class="form-check-input" type="checkbox" value="" id="kt_roles_select_all" />
                                        <span class="form-check-label" for="kt_roles_select_all">{{ __('roles.permission_actions.select_all') }}</span>
                                    </label>
                                    <!--end::Checkbox-->
                                </td>
                            </tr>
                            <!--end::Table row-->
                            
                            @if(isset($permissions))
                                @foreach ($permissions as $module => $modulePermissions)
                                    <!--begin::Table row-->
                                    <tr>
                                        <!--begin::Label-->
                                        <td class="text-gray-800">{{ __('roles.permission_modules.' . $module) }}</td>
                                        <!--end::Label-->
                                        <!--begin::Options-->
                                        <td>
                                            <!--begin::Wrapper-->
                                            <div class="d-flex flex-wrap">
                                                @foreach ($modulePermissions as $permission)
                                                    <!--begin::Checkbox-->
                                                    <label class="form-check form-check-sm form-check-custom form-check-solid me-5 me-lg-20 mb-3">
                                                        <input class="form-check-input" type="checkbox" value="{{ $permission->id }}" name="permissions[]" />
                                                        <span class="form-check-label">{{ $permission->display_name }}</span>
                                                    </label>
                                                    <!--end::Checkbox-->
                                                @endforeach
                                            </div>
                                            <!--end::Wrapper-->
                                        </td>
                                        <!--end::Options-->
                                    </tr>
                                    <!--end::Table row-->
                                @endforeach
                            @endif
                        </tbody>
                        <!--end::Table body-->
                    </table>
                    <!--end::Table-->
                </div>
                <!--end::Table wrapper-->
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Permissions-->
        <div class="d-flex justify-content-end">
            <!--begin::Button-->
            <a href="{{ route('admin.roles.index') }}" id="kt_role_cancel" class="btn btn-light me-5">{{ __('common.cancel') }}</a>
            <!--end::Button-->
            <!--begin::Button-->
            <button type="submit" id="kt_role_submit" class="btn btn-primary">
                <span class="indicator-label">{{ __('roles.create_role') }}</span>
                <span class="indicator-progress">{{ __('common.please_wait') }}...
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
<script src="{{ asset('admin-assets/assets/js/custom/apps/roles/add-role.js') }}"></script>
@endpush
