<!--begin::Modal - Record Payment-->
<div class="modal fade" id="kt_modal_record_payment" tabindex="-1" aria-hidden="true">
    <!--begin::Modal dialog-->
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <!--begin::Modal content-->
        <div class="modal-content">
            <!--begin::Modal header-->
            <div class="modal-header" id="kt_modal_record_payment_header">
                <!--begin::Modal title-->
                <h2 class="fw-bold">{{ __('order.record_payment') }}</h2>
                <!--end::Modal title-->
                <!--begin::Close-->
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
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
                <!--begin::Form-->
                <form id="kt_modal_record_payment_form" class="form" action="#">
                    <!--begin::Input group-->
                    <div class="fv-row mb-7">
                        <!--begin::Label-->
                        <label class="required fw-semibold fs-6 mb-2">{{ __('order.payment_amount') }}</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <input type="number" name="payment_amount" class="form-control form-control-solid mb-3 mb-lg-0" placeholder="{{ __('order.enter_payment_amount') }}" max="{{ $order->remaining_amount }}" />
                        <!--end::Input-->
                        <div class="text-muted fs-7">{{ __('order.remaining_balance') }}: {{ number_format($order->remaining_amount, 0, ',', '.') }}₫</div>
                    </div>
                    <!--end::Input group-->
                    <!--begin::Input group-->
                    <div class="fv-row mb-7">
                        <!--begin::Label-->
                        <label class="required fw-semibold fs-6 mb-2">{{ __('order.payment_method') }}</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <select name="payment_method" class="form-select form-select-solid" data-control="select2" data-placeholder="{{ __('order.select_payment_method') }}">
                            <option></option>
                            <option value="cash">{{ __('order.cash') }}</option>
                            <option value="bank_transfer">{{ __('order.bank_transfer') }}</option>
                            <option value="credit_card">{{ __('order.credit_card') }}</option>
                            <option value="e_wallet">{{ __('order.e_wallet') }}</option>
                            <option value="other">{{ __('order.other') }}</option>
                        </select>
                        <!--end::Input-->
                    </div>
                    <!--end::Input group-->
                    <!--begin::Input group-->
                    <div class="fv-row mb-7">
                        <!--begin::Label-->
                        <label class="fw-semibold fs-6 mb-2">{{ __('order.payment_reference') }}</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <input type="text" name="payment_reference" class="form-control form-control-solid mb-3 mb-lg-0" placeholder="{{ __('order.enter_payment_reference') }}" />
                        <!--end::Input-->
                    </div>
                    <!--end::Input group-->
                    <!--begin::Input group-->
                    <div class="fv-row mb-7">
                        <!--begin::Label-->
                        <label class="fw-semibold fs-6 mb-2">{{ __('order.payment_notes') }}</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <textarea name="payment_notes" class="form-control form-control-solid mb-3 mb-lg-0" rows="3" placeholder="{{ __('order.enter_payment_notes') }}"></textarea>
                        <!--end::Input-->
                    </div>
                    <!--end::Input group-->
                    <!--begin::Actions-->
                    <div class="text-center pt-15">
                        <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
                        <button type="submit" class="btn btn-primary" data-kt-payment-modal-action="submit">
                            <span class="indicator-label">{{ __('order.record_payment') }}</span>
                            <span class="indicator-progress">{{ __('common.please_wait') }}...
                            <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
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
<!--end::Modal-->

<!--begin::Modal - Update Status-->
<div class="modal fade" id="kt_modal_update_status" tabindex="-1" aria-hidden="true">
    <!--begin::Modal dialog-->
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <!--begin::Modal content-->
        <div class="modal-content">
            <!--begin::Modal header-->
            <div class="modal-header">
                <!--begin::Modal title-->
                <h2 class="fw-bold">{{ __('order.update_status') }}</h2>
                <!--end::Modal title-->
                <!--begin::Close-->
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
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
                <!--begin::Form-->
                <form id="kt_modal_update_status_form" class="form" action="#">
                    <!--begin::Input group-->
                    <div class="fv-row mb-7">
                        <!--begin::Label-->
                        <label class="required fw-semibold fs-6 mb-2">{{ __('order.order_status') }}</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <select name="order_status" class="form-select form-select-solid" data-control="select2" data-placeholder="{{ __('order.select_order_status') }}">
                            <option></option>
                            <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>{{ __('order.pending') }}</option>
                            <option value="processing" {{ $order->status === 'processing' ? 'selected' : '' }}>{{ __('order.processing') }}</option>
                            <option value="shipped" {{ $order->status === 'shipped' ? 'selected' : '' }}>{{ __('order.shipped') }}</option>
                            <option value="delivered" {{ $order->status === 'delivered' ? 'selected' : '' }}>{{ __('order.delivered') }}</option>
                            <option value="completed" {{ $order->status === 'completed' ? 'selected' : '' }}>{{ __('order.completed') }}</option>
                            <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>{{ __('order.cancelled') }}</option>
                        </select>
                        <!--end::Input-->
                    </div>
                    <!--end::Input group-->
                    <!--begin::Input group-->
                    <div class="fv-row mb-7">
                        <!--begin::Label-->
                        <label class="required fw-semibold fs-6 mb-2">{{ __('order.delivery_status') }}</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <select name="delivery_status" class="form-select form-select-solid" data-control="select2" data-placeholder="{{ __('order.select_delivery_status') }}">
                            <option></option>
                            <option value="pending" {{ $order->delivery_status === 'pending' ? 'selected' : '' }}>{{ __('order.pending') }}</option>
                            <option value="preparing" {{ $order->delivery_status === 'preparing' ? 'selected' : '' }}>{{ __('order.preparing') }}</option>
                            <option value="shipped" {{ $order->delivery_status === 'shipped' ? 'selected' : '' }}>{{ __('order.shipped') }}</option>
                            <option value="delivered" {{ $order->delivery_status === 'delivered' ? 'selected' : '' }}>{{ __('order.delivered') }}</option>
                            <option value="failed" {{ $order->delivery_status === 'failed' ? 'selected' : '' }}>{{ __('order.failed') }}</option>
                        </select>
                        <!--end::Input-->
                    </div>
                    <!--end::Input group-->
                    <!--begin::Input group-->
                    <div class="fv-row mb-7">
                        <!--begin::Label-->
                        <label class="fw-semibold fs-6 mb-2">{{ __('order.tracking_number') }}</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <input type="text" name="tracking_number" class="form-control form-control-solid mb-3 mb-lg-0" placeholder="{{ __('order.enter_tracking_number') }}" value="{{ $order->tracking_number }}" />
                        <!--end::Input-->
                    </div>
                    <!--end::Input group-->
                    <!--begin::Input group-->
                    <div class="fv-row mb-7">
                        <!--begin::Label-->
                        <label class="fw-semibold fs-6 mb-2">{{ __('order.status_notes') }}</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <textarea name="status_notes" class="form-control form-control-solid mb-3 mb-lg-0" rows="3" placeholder="{{ __('order.enter_status_notes') }}"></textarea>
                        <!--end::Input-->
                    </div>
                    <!--end::Input group-->
                    <!--begin::Actions-->
                    <div class="text-center pt-15">
                        <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
                        <button type="submit" class="btn btn-primary" data-kt-status-modal-action="submit">
                            <span class="indicator-label">{{ __('order.update_status') }}</span>
                            <span class="indicator-progress">{{ __('common.please_wait') }}...
                            <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
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
<!--end::Modal-->

<!--begin::Modal - Print Order-->
<div class="modal fade" id="kt_modal_print_order" tabindex="-1" aria-hidden="true">
    <!--begin::Modal dialog-->
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <!--begin::Modal content-->
        <div class="modal-content">
            <!--begin::Modal header-->
            <div class="modal-header">
                <!--begin::Modal title-->
                <h2 class="fw-bold">{{ __('order.print_order') }}</h2>
                <!--end::Modal title-->
                <!--begin::Close-->
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
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
                <!--begin::Notice-->
                <div class="notice d-flex bg-light-primary rounded border-primary border border-dashed mb-9 p-6">
                    <!--begin::Icon-->
                    <i class="ki-duotone ki-information fs-2tx text-primary me-4"></i>
                    <!--end::Icon-->
                    <!--begin::Wrapper-->
                    <div class="d-flex flex-stack flex-grow-1">
                        <!--begin::Content-->
                        <div class="fw-semibold">
                            <h4 class="text-gray-900 fw-bold">{{ __('order.print_options') }}</h4>
                            <div class="fs-6 text-gray-700">{{ __('order.select_print_format') }}</div>
                        </div>
                        <!--end::Content-->
                    </div>
                    <!--end::Wrapper-->
                </div>
                <!--end::Notice-->
                
                <!--begin::Print options-->
                <div class="d-flex flex-column gap-3">
                    <button type="button" class="btn btn-light-primary" id="btn_print_invoice">
                        <i class="ki-duotone ki-printer fs-3 me-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        {{ __('order.print_invoice') }}
                    </button>
                    
                    <button type="button" class="btn btn-light-info" id="btn_print_receipt">
                        <i class="ki-duotone ki-receipt fs-3 me-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        {{ __('order.print_receipt') }}
                    </button>
                    
                    <button type="button" class="btn btn-light-success" id="btn_print_shipping_label">
                        <i class="ki-duotone ki-package fs-3 me-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                        </i>
                        {{ __('order.print_shipping_label') }}
                    </button>
                    
                    <button type="button" class="btn btn-light-warning" id="btn_export_pdf">
                        <i class="ki-duotone ki-file-down fs-3 me-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        {{ __('order.export_pdf') }}
                    </button>
                </div>
                <!--end::Print options-->
            </div>
            <!--end::Modal body-->
        </div>
        <!--end::Modal content-->
    </div>
    <!--end::Modal dialog-->
</div>
<!--end::Modal-->

<!--begin::Modal - Cancel Order-->
<div class="modal fade" id="kt_modal_cancel_order" tabindex="-1" aria-hidden="true">
    <!--begin::Modal dialog-->
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <!--begin::Modal content-->
        <div class="modal-content">
            <!--begin::Modal header-->
            <div class="modal-header">
                <!--begin::Modal title-->
                <h2 class="fw-bold">{{ __('order.cancel_order') }}</h2>
                <!--end::Modal title-->
                <!--begin::Close-->
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
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
                <!--begin::Notice-->
                <div class="notice d-flex bg-light-danger rounded border-danger border border-dashed mb-9 p-6">
                    <!--begin::Icon-->
                    <i class="ki-duotone ki-warning-2 fs-2tx text-danger me-4"></i>
                    <!--end::Icon-->
                    <!--begin::Wrapper-->
                    <div class="d-flex flex-stack flex-grow-1">
                        <!--begin::Content-->
                        <div class="fw-semibold">
                            <h4 class="text-gray-900 fw-bold">{{ __('order.cancel_confirmation') }}</h4>
                            <div class="fs-6 text-gray-700">{{ __('order.cancel_warning') }}</div>
                        </div>
                        <!--end::Content-->
                    </div>
                    <!--end::Wrapper-->
                </div>
                <!--end::Notice-->
                <!--begin::Form-->
                <form id="kt_modal_cancel_order_form">
                    <!--begin::Input group-->
                    <div class="fv-row mb-7">
                        <!--begin::Label-->
                        <label class="required fw-semibold fs-6 mb-2">{{ __('order.cancellation_reason') }}</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <select name="cancellation_reason" class="form-select form-select-solid" data-control="select2" data-placeholder="{{ __('order.select_cancellation_reason') }}">
                            <option></option>
                            <option value="customer_request">{{ __('order.customer_request') }}</option>
                            <option value="out_of_stock">{{ __('order.out_of_stock') }}</option>
                            <option value="payment_failed">{{ __('order.payment_failed') }}</option>
                            <option value="duplicate_order">{{ __('order.duplicate_order') }}</option>
                            <option value="other">{{ __('order.other') }}</option>
                        </select>
                        <!--end::Input-->
                    </div>
                    <!--end::Input group-->
                    <!--begin::Input group-->
                    <div class="fv-row mb-7">
                        <!--begin::Label-->
                        <label class="fw-semibold fs-6 mb-2">{{ __('order.cancellation_notes') }}</label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <textarea name="cancellation_notes" class="form-control form-control-solid mb-3 mb-lg-0" rows="3" placeholder="{{ __('order.enter_cancellation_notes') }}"></textarea>
                        <!--end::Input-->
                    </div>
                    <!--end::Input group-->
                    <!--begin::Actions-->
                    <div class="text-center pt-15">
                        <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
                        <button type="submit" class="btn btn-danger" id="btn_cancel_order">
                            <span class="indicator-label">{{ __('order.cancel_order') }}</span>
                            <span class="indicator-progress">{{ __('common.please_wait') }}...
                            <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
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
<!--end::Modal-->
