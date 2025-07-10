<!--begin::Modal - Approve Payment-->
<div class="modal fade" id="kt_modal_approve_payment" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold">Duyệt phiếu thu/chi</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-kt-payment-modal-action="close">
                    <i class="ki-duotone ki-cross fs-1">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                </div>
            </div>

            <form id="kt_modal_approve_payment_form" class="form" action="#">
                <div class="modal-body py-10 px-lg-17">
                    <div class="scroll-y me-n7 pe-7" id="kt_modal_approve_payment_scroll" data-kt-scroll="true" data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-max-height="auto" data-kt-scroll-dependencies="#kt_modal_approve_payment_header" data-kt-scroll-wrappers="#kt_modal_approve_payment_scroll" data-kt-scroll-offset="300px">
                        
                        <div class="notice d-flex bg-light-success rounded border-success border border-dashed mb-9 p-6">
                            <i class="ki-duotone ki-information fs-2tx text-success me-4">
                                <span class="path1"></span>
                                <span class="path2"></span>
                                <span class="path3"></span>
                            </i>
                            <div class="d-flex flex-stack flex-grow-1">
                                <div class="fw-semibold">
                                    <h4 class="text-gray-900 fw-bold">Xác nhận duyệt phiếu thu/chi</h4>
                                    <div class="fs-6 text-gray-700">Sau khi duyệt, phiếu sẽ được hoàn thành và không thể chỉnh sửa.</div>
                                </div>
                            </div>
                        </div>

                        <div class="fv-row mb-7">
                            <label class="fw-semibold fs-6 mb-2">Ghi chú duyệt</label>
                            <textarea class="form-control form-control-solid mb-3 mb-lg-0" name="notes" placeholder="Nhập ghi chú (tùy chọn)"></textarea>
                        </div>

                    </div>
                </div>

                <div class="modal-footer flex-center">
                    <button type="reset" class="btn btn-light me-3" data-kt-payment-modal-action="cancel">Hủy</button>
                    <button type="submit" class="btn btn-success" data-kt-payment-modal-action="submit">
                        <span class="indicator-label">Duyệt phiếu</span>
                        <span class="indicator-progress">Đang xử lý...
                        <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<!--end::Modal - Approve Payment-->
