<!-- Discount Modal -->
<div class="modal fade" id="discountModal" tabindex="-1" aria-labelledby="discountModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title" id="discountModalLabel">Giảm giá</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-2">
                <div class="row mb-3">
                    <div class="col-8">
                        <input type="text" id="discountInput" class="form-control form-control-lg border-0 border-bottom border-2 border-primary"
                               placeholder="0" style="background: transparent; outline: none; text-align: right; font-size: 1.2rem;"
                               oninput="formatDiscountInput(this)">
                    </div>
                    <div class="col-4 ps-3">
                        <div class="d-flex gap-2">
                            <input type="radio" class="btn-check" name="discountType" id="discountVND" value="VND" checked>
                            <label class="btn btn-primary flex-fill" for="discountVND">VND</label>

                            <input type="radio" class="btn-check" name="discountType" id="discountPercent" value="PERCENT">
                            <label class="btn btn-outline-primary flex-fill" for="discountPercent">%</label>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-6">
                        <label class="form-label text-muted">Khuyến mại:</label>
                        <div id="promotionAmount" class="fw-bold fs-5">0</div>
                    </div>
                    <div class="col-6">
                        <div class="text-end">
                            <div class="text-muted small">Tổng giảm giá:</div>
                            <div id="totalDiscountAmount" class="fw-bold fs-4 text-primary">0</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary" onclick="applyDiscount()">Áp dụng</button>
            </div>
        </div>
    </div>
</div>
