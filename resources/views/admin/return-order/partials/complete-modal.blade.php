<!--begin::Modal - Complete Return Order-->
<div class="modal fade" id="kt_modal_complete_return_order" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold">Hoàn thành đơn trả hàng</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-kt-return-order-modal-action="close">
                    <i class="ki-duotone ki-cross fs-1">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                </div>
            </div>

            <form id="kt_modal_complete_return_order_form" class="form" action="#">
                <div class="modal-body py-10 px-lg-17">
                    <div class="scroll-y me-n7 pe-7" id="kt_modal_complete_return_order_scroll" data-kt-scroll="true" data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-max-height="auto" data-kt-scroll-dependencies="#kt_modal_complete_return_order_header" data-kt-scroll-wrappers="#kt_modal_complete_return_order_scroll" data-kt-scroll-offset="300px">
                        
                        <div class="notice d-flex bg-light-success rounded border-success border border-dashed mb-9 p-6">
                            <i class="ki-duotone ki-information fs-2tx text-success me-4">
                                <span class="path1"></span>
                                <span class="path2"></span>
                                <span class="path3"></span>
                            </i>
                            <div class="d-flex flex-stack flex-grow-1">
                                <div class="fw-semibold">
                                    <h4 class="text-gray-900 fw-bold">Hoàn thành đơn trả hàng</h4>
                                    <div class="fs-6 text-gray-700">Hệ thống sẽ tạo phiếu chi tự động để hoàn tiền cho khách hàng.</div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-7">
                            <div class="col-md-6">
                                <label class="required fw-semibold fs-6 mb-2">Phương thức hoàn tiền</label>
                                <select class="form-select form-select-solid" name="payment_method" required>
                                    <option value="">Chọn phương thức</option>
                                    <option value="cash">Tiền mặt</option>
                                    <option value="transfer">Chuyển khoản</option>
                                    <option value="card">Thẻ</option>
                                    <option value="store_credit">Tín dụng cửa hàng</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="fw-semibold fs-6 mb-2">Tài khoản ngân hàng</label>
                                <select class="form-select form-select-solid" name="bank_account_id">
                                    <option value="">Chọn tài khoản</option>
                                    @foreach(\App\Models\BankAccount::getActive() as $account)
                                        <option value="{{ $account->id }}">{{ $account->bank_name }} - {{ $account->account_number }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="fv-row mb-7">
                            <label class="fw-semibold fs-6 mb-2">Ghi chú</label>
                            <textarea class="form-control form-control-solid mb-3 mb-lg-0" name="notes" placeholder="Nhập ghi chú (tùy chọn)"></textarea>
                        </div>

                    </div>
                </div>

                <div class="modal-footer flex-center">
                    <button type="reset" class="btn btn-light me-3" data-kt-return-order-modal-action="cancel">Hủy</button>
                    <button type="submit" class="btn btn-success" data-kt-return-order-modal-action="submit">
                        <span class="indicator-label">Hoàn thành & Hoàn tiền</span>
                        <span class="indicator-progress">Đang xử lý...
                        <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<!--end::Modal - Complete Return Order-->
