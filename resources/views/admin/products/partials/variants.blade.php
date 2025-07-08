{{-- Product Variants Management Section --}}
<!-- Attribute Selection -->
<div id="attribute_selection_container" style="display: none;">
    <div class="card shadow-sm mb-5">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fa fa-tags"></i> {{ __('admin.products.variants.title') }}
            </h3>
            <div class="card-toolbar">
                <button type="button" id="add_new_attribute_row_btn" class="btn btn-sm btn-light-primary">
                    <i class="fa fa-plus"></i> {{ __('admin.products.variants.add_attribute') }}
                </button>
            </div>
        </div>
        <div class="card-body">
            <div id="attribute_rows_container">
                <!-- Attribute rows will be added here -->
            </div>
        </div>
    </div>
</div>

<!-- Variant Details Table -->
<div id="variant_details_container" class="card shadow-sm mb-5" style="display: none;">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fa fa-list"></i> {{ __('admin.products.variants.variant_list') }}
        </h3>
    </div>
    <div class="card-body">
        <div id="variant_details_table">
            <!-- Variant details table will be displayed here -->
        </div>
    </div>
</div>

{{-- Add Attribute Modal --}}
<div class="modal fade" id="kt_modal_add_attribute" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <div class="modal-header" id="kt_modal_add_attribute_header">
                <h2 class="fw-bolder">{{ __('admin.products.variants.add_new_attribute') }}</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-kt-attribute-modal-action="close">
                    <span class="svg-icon svg-icon-1">
                        <i class="fa fa-times"></i>
                    </span>
                </div>
            </div>
            <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                <form id="kt_modal_add_attribute_form" class="form" action="#">
                    <div class="d-flex flex-column scroll-y me-n7 pe-7" id="kt_modal_add_attribute_scroll" data-kt-scroll="true" data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-max-height="auto" data-kt-scroll-dependencies="#kt_modal_add_attribute_header" data-kt-scroll-wrappers="#kt_modal_add_attribute_scroll" data-kt-scroll-offset="300px">
                        
                        <div class="fv-row mb-7">
                            <label class="required fw-bold fs-6 mb-2">{{ __('admin.products.variants.attribute_name') }}</label>
                            <input type="text" name="name" class="form-control form-control-solid mb-3 mb-lg-0" placeholder="{{ __('admin.products.variants.attribute_name_placeholder') }}" />
                        </div>

                        <div class="fv-row mb-7">
                            <label class="required fw-bold fs-6 mb-2">{{ __('admin.products.variants.attribute_type') }}</label>
                            <select name="type" class="form-select form-select-solid" data-control="select2" data-placeholder="{{ __('admin.products.variants.select_type') }}">
                                <option></option>
                                <option value="select">{{ __('admin.products.variants.type_select') }}</option>
                                <option value="color">{{ __('admin.products.variants.type_color') }}</option>
                                <option value="text">{{ __('admin.products.variants.type_text') }}</option>
                                <option value="number">{{ __('admin.products.variants.type_number') }}</option>
                            </select>
                        </div>

                        <div class="fv-row mb-7">
                            <label class="fw-bold fs-6 mb-2">{{ __('admin.products.variants.attribute_description') }}</label>
                            <textarea name="description" class="form-control form-control-solid" rows="3" placeholder="{{ __('admin.products.variants.attribute_description_placeholder') }}"></textarea>
                        </div>

                        <div class="fv-row mb-7">
                            <label class="fw-bold fs-6 mb-2">{{ __('admin.products.variants.default_values') }}</label>
                            <input type="text" name="default_values" class="form-control form-control-solid" placeholder="{{ __('admin.products.variants.default_values_placeholder') }}" />
                            <div class="form-text">{{ __('admin.products.variants.default_values_help') }}</div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="fv-row mb-7">
                                    <div class="form-check form-check-custom form-check-solid">
                                        <input class="form-check-input" type="checkbox" value="1" id="is_variation" name="is_variation" checked />
                                        <label class="form-check-label" for="is_variation">
                                            {{ __('admin.products.variants.is_variation') }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="fv-row mb-7">
                                    <div class="form-check form-check-custom form-check-solid">
                                        <input class="form-check-input" type="checkbox" value="1" id="is_visible" name="is_visible" checked />
                                        <label class="form-check-label" for="is_visible">
                                            {{ __('admin.products.variants.is_visible') }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer flex-center">
                <button type="reset" id="kt_modal_add_attribute_cancel" class="btn btn-light me-3">{{ __('common.cancel') }}</button>
                <button type="submit" id="kt_modal_add_attribute_submit" class="btn btn-primary">
                    {{ __('common.save') }}
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Show/hide variants container based on product type
    var productTypeSelect = document.querySelector('#product_type');
    var attributeContainer = document.querySelector('#attribute_selection_container');
    var variantContainer = document.querySelector('#variant_details_container');

    if (productTypeSelect && attributeContainer) {
        function toggleVariantsContainer() {
            if (productTypeSelect.value === 'variable') {
                attributeContainer.style.display = 'block';
                // Initialize variant manager if not already done
                if (typeof window.KTProductVariantManager !== 'undefined') {
                    window.KTProductVariantManager.loadVariants();
                }
            } else {
                attributeContainer.style.display = 'none';
                if (variantContainer) {
                    variantContainer.style.display = 'none';
                }
            }
        }

        // Initial check
        toggleVariantsContainer();

        // Listen for changes
        productTypeSelect.addEventListener('change', toggleVariantsContainer);
    }
});
</script>
