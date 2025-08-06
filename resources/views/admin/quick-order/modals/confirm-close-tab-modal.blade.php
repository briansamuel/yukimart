<!-- Confirm Close Tab Modal -->
<div class="modal fade" id="confirmCloseTabModal" tabindex="-1" aria-labelledby="confirmCloseTabModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title text-danger fw-bold" id="confirmCloseTabModalLabel">Đóng <span id="closeTabName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-2">
                <p class="mb-0">Thông tin của <strong id="closeTabNameInBody"></strong> sẽ không được lưu lại. Bạn có chắc chắn muốn đóng không?</p>
            </div>
            <div class="modal-footer border-0 pt-2">
                <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">Bỏ qua</button>
                <button type="button" class="btn btn-danger" id="confirmCloseTabBtn">Đồng ý</button>
            </div>
        </div>
    </div>
</div>
