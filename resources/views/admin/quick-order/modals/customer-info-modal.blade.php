<!-- Customer Info Modal -->
<div class="modal fade" id="customerInfoModal" tabindex="-1" aria-labelledby="customerInfoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title" id="customerInfoModalLabel">
                    <h5 class="mb-1">
                        <span id="customerModalName"></span>
                        <span id="customerModalCode" class="text-muted"></span>
                    </h5>
                    <div class="text-muted small" id="customerModalBranch"></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Customer Stats -->
                <div class="row mb-4">
                    <div class="col-md-2 text-center">
                        <div class="bg-light rounded p-3">
                            <div class="fw-bold text-danger fs-4" id="customerDebtAmount">0</div>
                            <div class="text-muted small">Nợ</div>
                        </div>
                    </div>
                    <div class="col-md-2 text-center">
                        <div class="bg-light rounded p-3">
                            <div class="fw-bold text-warning fs-4" id="customerPointCount">0</div>
                            <div class="text-muted small">Điểm</div>
                        </div>
                    </div>
                    <div class="col-md-2 text-center">
                        <div class="bg-light rounded p-3">
                            <div class="fw-bold text-success fs-4" id="customerTotalSpent">0</div>
                            <div class="text-muted small">Tổng điểm</div>
                        </div>
                    </div>
                    <div class="col-md-2 text-center">
                        <div class="bg-light rounded p-3">
                            <div class="fw-bold text-info fs-4" id="customerPurchaseCount">0</div>
                            <div class="text-muted small">Số lần mua</div>
                        </div>
                    </div>
                    <div class="col-md-4 text-center">
                        <div class="bg-light rounded p-3">
                            <div class="fw-bold text-primary fs-4" id="customerNetSales">0</div>
                            <div class="text-muted small">Tổng bán trừ trả hàng</div>
                        </div>
                    </div>
                </div>

                <!-- Tabs Navigation -->
                <ul class="nav nav-tabs" id="customerInfoTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="customer-info-tab" data-bs-toggle="tab" data-bs-target="#customer-info" type="button" role="tab">
                            Thông tin
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="customer-history-tab" data-bs-toggle="tab" data-bs-target="#customer-history" type="button" role="tab">
                            Lịch sử bán/trả hàng
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="customer-debt-tab" data-bs-toggle="tab" data-bs-target="#customer-debt" type="button" role="tab">
                            Dư nợ
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="customer-points-tab" data-bs-toggle="tab" data-bs-target="#customer-points" type="button" role="tab">
                            Lịch sử điểm
                        </button>
                    </li>
                </ul>

                <!-- Tabs Content -->
                <div class="tab-content mt-3" id="customerInfoTabsContent">

                    <!-- Tab 1: Thông tin -->
                    <div class="tab-pane fade show active" id="customer-info" role="tabpanel">
                        <form id="customerEditForm">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Mã khách hàng</label>
                                        <input type="text" class="form-control" id="customerModalCustomerCode" readonly>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Tên khách hàng <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="customerModalFullName" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Điện thoại <span class="text-danger">*</span></label>
                                        <input type="tel" class="form-control" id="customerModalPhone" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Email</label>
                                        <input type="email" class="form-control" id="customerModalEmail">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Địa chỉ</label>
                                        <textarea class="form-control" id="customerModalAddress" rows="2"></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Khu vực</label>
                                        <input type="text" class="form-control" id="customerModalArea">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Loại khách</label>
                                        <select class="form-select" id="customerModalType">
                                            <option value="individual">Cá nhân</option>
                                            <option value="business">Doanh nghiệp</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Nhóm khách hàng</label>
                                        <select class="form-select" id="customerModalGroup">
                                            <option value="">Chọn nhóm khách hàng</option>
                                            <option value="VIP">VIP</option>
                                            <option value="Thường">Thường</option>
                                            <option value="Đại lý">Đại lý</option>
                                            <option value="Sỉ">Sỉ</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Mã số thuế</label>
                                        <input type="text" class="form-control" id="customerModalTaxCode">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Facebook</label>
                                        <input type="text" class="form-control" id="customerModalFacebook">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Ngày sinh</label>
                                        <input type="date" class="form-control" id="customerModalBirthday">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Ghi chú</label>
                                        <textarea class="form-control" id="customerModalNotes" rows="2"></textarea>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Tab 2: Lịch sử bán/trả hàng -->
                    <div class="tab-pane fade" id="customer-history" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead class="table-primary">
                                    <tr>
                                        <th>Mã hóa đơn</th>
                                        <th>Thời gian</th>
                                        <th>Người bán</th>
                                        <th>Tổng cộng</th>
                                        <th>Trạng thái</th>
                                    </tr>
                                </thead>
                                <tbody id="customerOrderHistoryTable">
                                    <!-- Data will be loaded here -->
                                </tbody>
                            </table>
                            <!-- Pagination for order history -->
                            <div id="orderHistoryPagination" class="d-flex justify-content-center mt-3">
                                <!-- Pagination will be loaded here -->
                            </div>
                        </div>
                    </div>

                    <!-- Tab 3: Dư nợ -->
                    <div class="tab-pane fade" id="customer-debt" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead class="table-warning">
                                    <tr>
                                        <th>Mã hóa đơn</th>
                                        <th>Thời gian</th>
                                        <th>Người bán</th>
                                        <th>Tổng cộng</th>
                                        <th>Đã trả</th>
                                        <th>Còn nợ</th>
                                    </tr>
                                </thead>
                                <tbody id="customerDebtTable">
                                    <!-- Data will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tab 4: Lịch sử điểm -->
                    <div class="tab-pane fade" id="customer-points" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead class="table-primary">
                                    <tr>
                                        <th>Mã phiếu</th>
                                        <th>Thời gian</th>
                                        <th>Loại</th>
                                        <th>Giá trị</th>
                                        <th>Điểm GD</th>
                                        <th>Điểm sau GD</th>
                                    </tr>
                                </thead>
                                <tbody id="customerPointsTable">
                                    <!-- Data will be loaded here -->
                                </tbody>
                            </table>
                            <!-- Pagination for point history -->
                            <div id="pointHistoryPagination" class="d-flex justify-content-center mt-3">
                                <!-- Pagination will be loaded here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div class="d-flex justify-content-between w-100">
                    <div>
                        <!-- Left side - empty for now -->
                    </div>
                    <div>
                        <!-- Right side - action buttons -->
                        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>Bỏ qua
                        </button>
                        <button type="button" class="btn btn-primary" id="saveCustomerBtn">
                            <i class="fas fa-save me-1"></i>Lưu
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
