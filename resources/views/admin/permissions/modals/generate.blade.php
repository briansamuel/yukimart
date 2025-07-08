<!--begin::Modal - Generate permissions-->
<div class="modal fade" id="kt_modal_generate_permissions" tabindex="-1" aria-hidden="true">
    <!--begin::Modal dialog-->
    <div class="modal-dialog modal-dialog-centered mw-750px">
        <!--begin::Modal content-->
        <div class="modal-content">
            <!--begin::Modal header-->
            <div class="modal-header">
                <!--begin::Modal title-->
                <h2 class="fw-bolder">{{ __('permissions.generator.title') }}</h2>
                <!--end::Modal title-->
                <!--begin::Close-->
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-kt-permissions-modal-action="close">
                    <i class="ki-duotone ki-cross fs-1">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                </div>
                <!--end::Close-->
            </div>
            <!--end::Modal header-->
            <!--begin::Modal body-->
            <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                <!--begin::Description-->
                <div class="mb-13 text-center">
                    <h1 class="mb-3">{{ __('permissions.generator.title') }}</h1>
                    <div class="text-muted fw-semibold fs-5">{{ __('permissions.generator.description') }}</div>
                </div>
                <!--end::Description-->
                <!--begin::Form-->
                <form id="kt_modal_generate_permissions_form" class="form" action="#">
                    <!--begin::Input group-->
                    <div class="fv-row mb-8">
                        <!--begin::Label-->
                        <label class="fs-6 fw-semibold form-label mb-2">
                            <span class="required">{{ __('permissions.generator.select_module') }}</span>
                        </label>
                        <!--end::Label-->
                        <!--begin::Select-->
                        <select name="module" data-control="select2" data-placeholder="{{ __('permissions.select_module') }}" class="form-select form-select-solid fw-bold">
                            <option></option>
                            @if(isset($modules))
                                @foreach($modules as $key => $module)
                                    <option value="{{ $key }}">{{ $module }}</option>
                                @endforeach
                            @endif
                        </select>
                        <!--end::Select-->
                    </div>
                    <!--end::Input group-->
                    <!--begin::Input group-->
                    <div class="fv-row mb-8">
                        <!--begin::Label-->
                        <label class="fs-6 fw-semibold form-label mb-2">
                            <span class="required">{{ __('permissions.generator.select_actions') }}</span>
                        </label>
                        <!--end::Label-->
                        <!--begin::Checkboxes-->
                        <div class="d-flex flex-wrap">
                            @if(isset($actions))
                                @foreach($actions as $key => $action)
                                    <!--begin::Checkbox-->
                                    <label class="form-check form-check-custom form-check-solid me-9 mb-3">
                                        <input class="form-check-input" type="checkbox" value="{{ $key }}" name="actions[]" />
                                        <span class="form-check-label fw-semibold text-gray-700">{{ $action }}</span>
                                    </label>
                                    <!--end::Checkbox-->
                                @endforeach
                            @endif
                        </div>
                        <!--end::Checkboxes-->
                        <!--begin::Select All-->
                        <div class="mt-3">
                            <label class="form-check form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" id="select_all_actions" />
                                <span class="form-check-label fw-bold text-primary">{{ __('permissions.permission_actions.select_all') }}</span>
                            </label>
                        </div>
                        <!--end::Select All-->
                    </div>
                    <!--end::Input group-->
                    <!--begin::Preview-->
                    <div class="fv-row mb-8" id="permissions_preview" style="display: none;">
                        <!--begin::Label-->
                        <label class="fs-6 fw-semibold form-label mb-2">{{ __('permissions.generator.preview') }}</label>
                        <!--end::Label-->
                        <!--begin::Preview content-->
                        <div class="bg-light-info p-5 rounded">
                            <div class="fw-bold text-info mb-3">{{ __('permissions.generator.will_create') }}</div>
                            <div id="preview_list"></div>
                            <div id="existing_list" style="display: none;">
                                <div class="fw-bold text-warning mt-3 mb-2">{{ __('permissions.generator.already_exists') }}</div>
                                <div id="existing_permissions"></div>
                            </div>
                        </div>
                        <!--end::Preview content-->
                    </div>
                    <!--end::Preview-->
                    <!--begin::Actions-->
                    <div class="text-center">
                        <button type="reset" class="btn btn-light me-3" data-kt-permissions-modal-action="cancel">
                            {{ __('common.cancel') }}
                        </button>
                        <button type="submit" class="btn btn-primary" data-kt-permissions-modal-action="submit">
                            <span class="indicator-label">{{ __('permissions.generator.generate_button') }}</span>
                            <span class="indicator-progress">{{ __('common.please_wait') }}...
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                            </span>
                        </button>
                    </div>
                    <!--end::Actions-->
                </form>
                <!--end::Form-->
            </div>
            <!--end::Modal body-->
        </div>
        <!--end::Modal content-->
    </div>
    <!--end::Modal dialog-->
</div>
<!--end::Modal - Generate permissions-->

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle select all actions
    const selectAllActions = document.getElementById('select_all_actions');
    const actionCheckboxes = document.querySelectorAll('input[name="actions[]"]');
    
    if (selectAllActions) {
        selectAllActions.addEventListener('change', function() {
            actionCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updatePreview();
        });
    }
    
    // Handle individual action checkboxes
    actionCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updatePreview();
            
            // Update select all checkbox
            const checkedCount = document.querySelectorAll('input[name="actions[]"]:checked').length;
            selectAllActions.checked = checkedCount === actionCheckboxes.length;
            selectAllActions.indeterminate = checkedCount > 0 && checkedCount < actionCheckboxes.length;
        });
    });
    
    // Handle module change
    const moduleSelect = document.querySelector('select[name="module"]');
    if (moduleSelect) {
        moduleSelect.addEventListener('change', function() {
            updatePreview();
        });
    }
    
    function updatePreview() {
        const module = moduleSelect.value;
        const selectedActions = Array.from(document.querySelectorAll('input[name="actions[]"]:checked')).map(cb => cb.value);
        const previewContainer = document.getElementById('permissions_preview');
        const previewList = document.getElementById('preview_list');
        
        if (module && selectedActions.length > 0) {
            previewContainer.style.display = 'block';
            
            let previewHtml = '';
            selectedActions.forEach(action => {
                const permissionName = module + '.' + action;
                previewHtml += `<div class="badge badge-light-primary me-2 mb-2">${permissionName}</div>`;
            });
            
            previewList.innerHTML = previewHtml;
        } else {
            previewContainer.style.display = 'none';
        }
    }
});
</script>
