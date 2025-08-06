<!--begin::Payment Detail Panel-->
<div id="payment_detail_panel" class="payment-detail-panel">
    <div class="panel-content">
        <!--begin::Panel Header-->
        <div class="panel-header">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <h5 class="fw-bold text-dark mb-0">Thông tin</h5>
                </div>
                <button type="button" class="btn btn-sm btn-icon btn-light" id="close_payment_detail">
                    <i class="fas fa-times fs-2"></i>
                </button>
            </div>
        </div>
        <!--end::Panel Header-->

        <!--begin::Panel Body-->
        <div class="panel-body">
            <!--begin::Payment Info-->
            <div class="payment-info-section mb-6">
                <div class="d-flex align-items-center mb-4">
                    <div class="payment-type-badge me-3" id="payment_type_badge">
                        <span class="badge badge-light-success">Phiếu thu</span>
                    </div>
                    <div class="payment-number">
                        <span class="fw-bold text-dark fs-4" id="payment_number">TTH0040607</span>
                    </div>
                    <div class="ms-auto">
                        <span class="badge badge-light-success" id="payment_status_badge">Đã thanh toán</span>
                        <span class="badge badge-light-warning ms-2" id="accounting_status_badge">Không hạch toán</span>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-6">
                        <div class="info-item">
                            <span class="text-muted fs-7">Người tạo:</span>
                            <span class="text-dark fw-semibold" id="payment_creator">Lục Thị Như Hoa</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="info-item">
                            <span class="text-muted fs-7">Người thu:</span>
                            <span class="text-dark fw-semibold" id="payment_collector">Lục Thị Như Hoa</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="info-item">
                            <span class="text-muted fs-7">Thời gian:</span>
                            <span class="text-dark fw-semibold" id="payment_datetime">10/07/2025 15:07</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="info-item">
                            <span class="text-muted fs-7">Chi nhánh:</span>
                            <span class="text-dark fw-semibold" id="payment_branch">524 Lý Thường Kiệt</span>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-3">
                        <div class="info-item">
                            <span class="text-muted fs-7">Số tiền</span>
                            <div class="text-dark fw-bold fs-5" id="payment_amount">155,000</div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="info-item">
                            <span class="text-muted fs-7">Loại thu</span>
                            <div class="text-dark fw-semibold" id="payment_income_type">Thu Tiền khách trả</div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="info-item">
                            <span class="text-muted fs-7">Đối tượng nộp</span>
                            <div class="text-dark fw-semibold" id="payment_payer">Khách hàng</div>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="info-item">
                            <span class="text-muted fs-7">Phương thức thanh toán</span>
                            <div class="text-dark fw-semibold" id="payment_method">Tiền mặt</div>
                        </div>
                    </div>
                </div>

                <div class="info-item mb-4">
                    <span class="text-muted fs-7">Người nộp</span>
                    <div class="text-dark fw-semibold" id="payment_recipient">Khách lẻ</div>
                </div>
            </div>
            <!--end::Payment Info-->

            <!--begin::Related Invoice Section-->
            <div class="related-invoice-section mb-6" id="related_invoice_section">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h6 class="fw-bold text-dark mb-0">Phiếu thu từ đơn được gắn với hóa đơn <span class="text-primary" id="related_invoice_code">HD040607</span></h6>
                    <button type="button" class="btn btn-sm btn-icon btn-light" id="toggle_invoice_detail">
                        <i class="fas fa-chevron-up fs-3"></i>
                    </button>
                </div>

                <!--begin::Invoice Detail Table-->
                <div class="invoice-detail-table" id="invoice_detail_table">
                    <div class="table-responsive">
                        <table class="table table-row-bordered table-row-gray-100 align-middle gs-0 gy-3">
                            <thead>
                                <tr class="fw-bold text-muted">
                                    <th class="min-w-100px">Mã phiếu</th>
                                    <th class="min-w-100px">Thời gian</th>
                                    <th class="min-w-100px text-end">Giá trị phiếu</th>
                                    <th class="min-w-100px text-end">Đã thu trước</th>
                                    <th class="min-w-100px text-end">Giá trị thu</th>
                                    <th class="min-w-100px">Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody id="invoice_detail_tbody">
                                <tr>
                                    <td>
                                        <a href="#" class="text-primary fw-bold" id="invoice_code_link">HD040607</a>
                                    </td>
                                    <td class="text-dark fw-semibold" id="invoice_datetime">10/07/2025 15:07</td>
                                    <td class="text-end text-dark fw-bold" id="invoice_amount">155,000</td>
                                    <td class="text-end text-muted" id="invoice_paid_before">0</td>
                                    <td class="text-end text-success fw-bold" id="invoice_collected_amount">155,000</td>
                                    <td>
                                        <span class="badge badge-light-success" id="invoice_status">Đã thanh toán</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <!--end::Invoice Detail Table-->
            </div>
            <!--end::Related Invoice Section-->

            <!--begin::Notes Section-->
            <div class="notes-section mb-6" id="notes_section">
                <div class="d-flex align-items-center mb-3">
                    <i class="fas fa-sticky-note text-muted me-2"></i>
                    <span class="text-muted fs-7">Chưa có ghi chú</span>
                </div>
            </div>
            <!--end::Notes Section-->
        </div>
        <!--end::Panel Body-->

        <!--begin::Panel Footer-->
        <div class="panel-footer">
            <div class="d-flex justify-content-end gap-3">
                <button type="button" class="btn btn-light" id="cancel_payment_btn">
                    <i class="fas fa-times me-2"></i>Hủy
                </button>
                <button type="button" class="btn btn-primary" id="edit_payment_btn">
                    <i class="fas fa-edit me-2"></i>Chỉnh sửa
                </button>
                <button type="button" class="btn btn-light-primary" id="print_payment_btn">
                    <i class="fas fa-print me-2"></i>In
                </button>
            </div>
        </div>
        <!--end::Panel Footer-->
    </div>
</div>
<!--end::Payment Detail Panel-->
