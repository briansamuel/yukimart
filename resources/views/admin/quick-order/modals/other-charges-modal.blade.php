<!-- Other Charges Modal -->
<div class="modal fade" id="otherChargesModal" tabindex="-1" aria-labelledby="otherChargesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="otherChargesModalLabel">Các khoản thu khác</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Add new charge form -->
                <div class="row mb-4 p-3 bg-light rounded">
                    <div class="col-3">
                        <input type="text" id="newChargeCode" class="form-control" placeholder="Mã thu khác">
                    </div>
                    <div class="col-4">
                        <input type="text" id="newChargeDescription" class="form-control" placeholder="Loại thu">
                    </div>
                    <div class="col-3">
                        <input type="text" id="newChargeAmount" class="form-control" placeholder="Số tiền" oninput="formatCurrencyInput(this)">
                    </div>
                    <div class="col-2">
                        <button type="button" class="btn btn-primary w-100" onclick="addOtherCharge()">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>

                <!-- Charges table -->
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead class="table-primary">
                            <tr>
                                <th width="5%">
                                    <input type="checkbox" id="selectAllCharges" onchange="toggleAllCharges(this)">
                                </th>
                                <th width="20%">Mã thu khác</th>
                                <th width="35%">Loại thu</th>
                                <th width="25%">Mức thu</th>
                                <th width="15%">Thu trên hóa đơn</th>
                            </tr>
                        </thead>
                        <tbody id="otherChargesTableBody">
                            <!-- Sample data -->
                            <tr>
                                <td><input type="checkbox" class="charge-checkbox" data-amount="4085"></td>
                                <td>TLTS_846031</td>
                                <td>Thu lệch vận chuyển</td>
                                <td class="text-end">4,085</td>
                                <td class="text-end">0</td>
                            </tr>
                            <tr>
                                <td><input type="checkbox" class="charge-checkbox" data-amount="20000"></td>
                                <td>THSP_846031</td>
                                <td>Trợ giá Shopee</td>
                                <td class="text-end">20,000</td>
                                <td class="text-end">0</td>
                            </tr>
                            <tr>
                                <td><input type="checkbox" class="charge-checkbox" data-amount="32400"></td>
                                <td>TLSP_846031</td>
                                <td>Thu lệch vận chuyển</td>
                                <td class="text-end">32,400</td>
                                <td class="text-end">0</td>
                            </tr>
                            <tr>
                                <td><input type="checkbox" class="charge-checkbox" data-amount="10000"></td>
                                <td>THK000001</td>
                                <td>Phí ship</td>
                                <td class="text-end">10,000</td>
                                <td class="text-end">0</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Total -->
                <div class="row mt-3">
                    <div class="col-12 text-end">
                        <h5>Tổng thu khác: <span class="text-primary" id="totalOtherCharges">0</span></h5>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary" onclick="applyOtherCharges()">Áp dụng</button>
            </div>
        </div>
    </div>
</div>
