<!-- Invoice Selection Modal for Return Orders -->
<div class="modal fade" id="invoiceSelectionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-file-invoice me-2"></i>
                    Chọn hóa đơn để trả hàng
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Search and filters -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <input type="text" class="form-control" id="invoiceSearchInput" placeholder="Tìm kiếm mã hóa đơn, khách hàng...">
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="invoiceTimeFilter">
                            <option value="this_month">Tháng này</option>
                            <option value="last_month">Tháng trước</option>
                            <option value="this_year">Năm này</option>
                            <option value="custom">Tùy chỉnh</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control" id="invoiceCustomerFilter" placeholder="Lọc theo khách hàng">
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-primary w-100" onclick="searchInvoices()">
                            <i class="fas fa-search"></i> Tìm kiếm
                        </button>
                    </div>
                </div>

                <!-- Invoice list table -->
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Mã hóa đơn</th>
                                <th>Thời gian</th>
                                <th>Nhân viên</th>
                                <th>Khách hàng</th>
                                <th>Tổng cộng</th>
                                <th>Chọn</th>
                            </tr>
                        </thead>
                        <tbody id="invoiceListTableBody">
                            <!-- Invoice rows will be loaded here -->
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div id="invoicePagination" class="mt-3" style="display: none;">
                    <!-- Pagination content will be loaded here by JavaScript -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>
