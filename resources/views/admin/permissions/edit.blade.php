@extends('admin.index')
@section('page-header', __('permissions.edit_permission'))
@section('page-sub_header', $permission->display_name)

@section('content')
<!--begin::Form-->
<form id="kt_permission_form" class="form d-flex flex-column flex-lg-row fv-plugins-bootstrap5 fv-plugins-framework" action="{{ route('admin.permissions.update', $permission->id) }}" method="POST">
    @csrf
    @method('PUT')
    <!--begin::Aside column-->
    <div class="d-flex flex-column gap-7 gap-lg-10 w-100 w-lg-300px mb-7 me-lg-10">
        <!--begin::Status-->
        <div class="card card-flush py-4">
            <!--begin::Card header-->
            <div class="card-header">
                <!--begin::Card title-->
                <div class="card-title">
                    <h2>{{ __('permissions.status') }}</h2>
                </div>
                <!--end::Card title-->
                <!--begin::Card toolbar-->
                <div class="card-toolbar">
                    <div class="rounded-circle {{ $permission->is_active ? 'bg-success' : 'bg-danger' }} w-15px h-15px" id="kt_permission_status"></div>
                </div>
                <!--end::Card toolbar-->
            </div>
            <!--end::Card header-->
            <!--begin::Card body-->
            <div class="card-body pt-0">
                <!--begin::Select2-->
                <select class="form-select mb-2" data-control="select2" data-hide-search="true" data-placeholder="{{ __('permissions.select_permission') }}" id="kt_permission_status_select" name="is_active">
                    <option></option>
                    <option value="1" {{ $permission->is_active ? 'selected' : '' }}>{{ __('permissions.active') }}</option>
                    <option value="0" {{ !$permission->is_active ? 'selected' : '' }}>{{ __('permissions.inactive') }}</option>
                </select>
                <!--end::Select2-->
                <!--begin::Description-->
                <div class="text-muted fs-7">{{ __('permissions.help.status') }}</div>
                <!--end::Description-->
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Status-->
        <!--begin::Module Info-->
        <div class="card card-flush py-4">
            <!--begin::Card header-->
            <div class="card-header">
                <!--begin::Card title-->
                <div class="card-title">
                    <h2>{{ __('permissions.module') }}</h2>
                </div>
                <!--end::Card title-->
            </div>
            <!--end::Card header-->
            <!--begin::Card body-->
            <div class="card-body pt-0">
                <!--begin::Select2-->
                <select class="form-select mb-2" data-control="select2" data-placeholder="{{ __('permissions.select_module') }}" name="module" id="kt_permission_module_select">
                    <option></option>
                    @if(isset($modules))
                        @foreach($modules as $key => $module)
                            <option value="{{ $key }}" {{ $permission->module == $key ? 'selected' : '' }}>{{ $module }}</option>
                        @endforeach
                    @endif
                </select>
                <!--end::Select2-->
                <!--begin::Description-->
                <div class="text-muted fs-7">{{ __('permissions.help.module') }}</div>
                <!--end::Description-->
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Module Info-->
        <!--begin::Action Info-->
        <div class="card card-flush py-4">
            <!--begin::Card header-->
            <div class="card-header">
                <!--begin::Card title-->
                <div class="card-title">
                    <h2>{{ __('permissions.action') }}</h2>
                </div>
                <!--end::Card title-->
            </div>
            <!--end::Card header-->
            <!--begin::Card body-->
            <div class="card-body pt-0">
                <!--begin::Select2-->
                <select class="form-select mb-2" data-control="select2" data-placeholder="{{ __('permissions.select_action') }}" name="action" id="kt_permission_action_select">
                    <option></option>
                    @if(isset($actions))
                        @foreach($actions as $key => $action)
                            <option value="{{ $key }}" {{ $permission->action == $key ? 'selected' : '' }}>{{ $action }}</option>
                        @endforeach
                    @endif
                </select>
                <!--end::Select2-->
                <!--begin::Description-->
                <div class="text-muted fs-7">{{ __('permissions.help.action') }}</div>
                <!--end::Description-->
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Action Info-->
        <!--begin::Statistics-->
        <div class="card card-flush py-4">
            <!--begin::Card header-->
            <div class="card-header">
                <!--begin::Card title-->
                <div class="card-title">
                    <h2>{{ __('permissions.statistics.title') }}</h2>
                </div>
                <!--end::Card title-->
            </div>
            <!--end::Card header-->
            <!--begin::Card body-->
            <div class="card-body pt-0">
                <!--begin::Stats-->
                <div class="d-flex flex-wrap">
                    <!--begin::Stat-->
                    <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                        <!--begin::Number-->
                        <div class="d-flex align-items-center">
                            <div class="fs-2 fw-bold" data-kt-countup="true" data-kt-countup-value="{{ $permission->roles_count }}">{{ $permission->roles_count }}</div>
                        </div>
                        <!--end::Number-->
                        <!--begin::Label-->
                        <div class="fw-semibold fs-6 text-gray-400">{{ __('permissions.roles_count') }}</div>
                        <!--end::Label-->
                    </div>
                    <!--end::Stat-->
                    <!--begin::Stat-->
                    <div class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-6 mb-3">
                        <!--begin::Number-->
                        <div class="d-flex align-items-center">
                            <div class="fs-2 fw-bold" data-kt-countup="true" data-kt-countup-value="{{ $permission->users_count }}">{{ $permission->users_count }}</div>
                        </div>
                        <!--end::Number-->
                        <!--begin::Label-->
                        <div class="fw-semibold fs-6 text-gray-400">{{ __('permissions.users_count') }}</div>
                        <!--end::Label-->
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
                    <h2>{{ __('permissions.tabs.general') }}</h2>
                </div>
            </div>
            <!--end::Card header-->
            <!--begin::Card body-->
            <div class="card-body pt-0">
                <!--begin::Input group-->
                <div class="mb-10 fv-row fv-plugins-icon-container">
                    <!--begin::Label-->
                    <label class="form-label">{{ __('permissions.name') }}</label>
                    <!--end::Label-->
                    <!--begin::Input-->
                    <input type="text" name="name" class="form-control mb-2" placeholder="{{ __('permissions.enter_permission_name') }}" value="{{ old('name', $permission->name) }}" />
                    <!--end::Input-->
                    <!--begin::Description-->
                    <div class="text-muted fs-7">{{ __('permissions.help.permission_name') }}</div>
                    <!--end::Description-->
                    <div class="fv-plugins-message-container invalid-feedback"></div>
                </div>
                <!--end::Input group-->
                <!--begin::Input group-->
                <div class="mb-10 fv-row fv-plugins-icon-container">
                    <!--begin::Label-->
                    <label class="required form-label">{{ __('permissions.display_name') }}</label>
                    <!--end::Label-->
                    <!--begin::Input-->
                    <input type="text" name="display_name" class="form-control mb-2" placeholder="{{ __('permissions.enter_display_name') }}" value="{{ old('display_name', $permission->display_name) }}" />
                    <!--end::Input-->
                    <!--begin::Description-->
                    <div class="text-muted fs-7">{{ __('permissions.help.display_name') }}</div>
                    <!--end::Description-->
                    <div class="fv-plugins-message-container invalid-feedback"></div>
                </div>
                <!--end::Input group-->
                <!--begin::Input group-->
                <div class="mb-10 fv-row">
                    <!--begin::Label-->
                    <label class="form-label">{{ __('permissions.description') }}</label>
                    <!--end::Label-->
                    <!--begin::Editor-->
                    <textarea name="description" class="form-control mb-2" rows="4" placeholder="{{ __('permissions.enter_description') }}">{{ old('description', $permission->description) }}</textarea>
                    <!--end::Editor-->
                    <!--begin::Description-->
                    <div class="text-muted fs-7">{{ __('permissions.help.description') }}</div>
                    <!--end::Description-->
                </div>
                <!--end::Input group-->
                <!--begin::Input group-->
                <div class="mb-0 fv-row">
                    <!--begin::Label-->
                    <label class="form-label">{{ __('permissions.sort_order') }}</label>
                    <!--end::Label-->
                    <!--begin::Input-->
                    <input type="number" name="sort_order" class="form-control mb-2" placeholder="0" value="{{ old('sort_order', $permission->sort_order) }}" />
                    <!--end::Input-->
                    <!--begin::Description-->
                    <div class="text-muted fs-7">{{ __('permissions.help.sort_order') }}</div>
                    <!--end::Description-->
                </div>
                <!--end::Input group-->
            </div>
            <!--end::Card body-->
        </div>
        <!--end::General options-->
        <!--begin::Auto Generate-->
        <div class="card card-flush py-4">
            <!--begin::Card header-->
            <div class="card-header">
                <div class="card-title">
                    <h2>{{ __('permissions.generator.title') }}</h2>
                </div>
            </div>
            <!--end::Card header-->
            <!--begin::Card body-->
            <div class="card-body pt-0">
                <!--begin::Description-->
                <div class="text-muted fs-7 mb-7">{{ __('permissions.help.auto_generate') }}</div>
                <!--end::Description-->
                <!--begin::Auto generate button-->
                <button type="button" class="btn btn-light-primary" id="kt_permission_auto_generate">
                    <i class="ki-duotone ki-gear fs-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    {{ __('permissions.generator.generate_button') }}
                </button>
                <!--end::Auto generate button-->
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Auto Generate-->
        <div class="d-flex justify-content-end">
            <!--begin::Button-->
            <a href="{{ route('admin.permissions.index') }}" id="kt_permission_cancel" class="btn btn-light me-5">{{ __('common.cancel') }}</a>
            <!--end::Button-->
            <!--begin::Button-->
            <button type="submit" id="kt_permission_submit" class="btn btn-primary">
                <span class="indicator-label">{{ __('permissions.update_permission') }}</span>
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
<script src="{{ asset('admin-assets/assets/js/custom/apps/permissions/edit-permission.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto generate permission name
    const moduleSelect = document.getElementById('kt_permission_module_select');
    const actionSelect = document.getElementById('kt_permission_action_select');
    const nameInput = document.querySelector('input[name="name"]');
    const displayNameInput = document.querySelector('input[name="display_name"]');
    const autoGenerateBtn = document.getElementById('kt_permission_auto_generate');
    
    function generatePermissionName() {
        const module = moduleSelect.value;
        const action = actionSelect.value;
        
        if (module && action) {
            const permissionName = module + '.' + action;
            nameInput.value = permissionName;
            
            // Generate display name
            const moduleText = moduleSelect.options[moduleSelect.selectedIndex].text;
            const actionText = actionSelect.options[actionSelect.selectedIndex].text;
            displayNameInput.value = actionText + ' ' + moduleText;
        }
    }
    
    // Auto generate on change
    moduleSelect.addEventListener('change', generatePermissionName);
    actionSelect.addEventListener('change', generatePermissionName);
    
    // Manual generate button
    autoGenerateBtn.addEventListener('click', generatePermissionName);
});
</script>
@endpush
