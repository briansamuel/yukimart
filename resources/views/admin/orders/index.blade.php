@extends('admin.index')
@section('page-header', 'Order Management')
@section('page-sub_header', 'Quản lý đơn hàng')
@section('style')
@endsection

@section('content')
    <!--begin::Row-->
    <div class="row g-5 g-xl-8">
        <!--begin::Col-->
        <div class="col-xl-12">
            <!--begin::Tables Widget 9-->
            <div class="card card-xl-stretch mb-5 mb-xl-8">
                <!--begin::Header-->
                <div class="card-header border-0 pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bolder fs-3 mb-1">Danh sách đơn hàng</span>
                        <span class="text-muted mt-1 fw-bold fs-7">Quản lý tất cả đơn hàng trong hệ thống</span>
                    </h3>
                    <div class="card-toolbar" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-trigger="hover"
                        title="Thêm đơn hàng mới">
                        <a href="{{ route('admin.order.add') }}" class="btn btn-sm btn-light btn-active-primary">
                            <!--begin::Svg Icon | path: icons/duotune/arrows/arr075.svg-->
                            <span class="svg-icon svg-icon-3">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none">
                                    <rect opacity="0.5" x="11.364" y="20.364" width="16" height="2" rx="1"
                                        transform="rotate(-90 11.364 20.364)" fill="black" />
                                    <rect x="4.364" y="11.364" width="16" height="2" rx="1" fill="black" />
                                </svg>
                            </span>
                            <!--end::Svg Icon-->Thêm đơn hàng
                        </a>
                    </div>
                </div>
                <!--end::Header-->
                <!--begin::Body-->
                <div class="card-body py-3">
                    <!--begin::Table Toolbar-->
                    <div class="d-flex justify-content-between" data-kt-orders-table-toolbar="base">
                        <!--begin::Search-->
                        <div class="d-flex align-items-center position-relative my-1">
                            <span class="svg-icon svg-icon-1 position-absolute ms-6">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                    <rect opacity="0.5" x="17.0365" y="15.1223" width="8.15546" height="2" rx="1" transform="rotate(45 17.0365 15.1223)" fill="black" />
                                    <path d="m20.9 10.8c0-2.6-2.1-4.6-4.6-4.6s-4.6 2.1-4.6 4.6c0 1.3 0.5 2.4 1.4 3.2l-5.2 5.2c-0.3 0.3-0.3 0.7 0 1s0.7 0.3 1 0l5.2-5.2c0.8 0.9 1.9 1.4 3.2 1.4 2.6 0 4.6-2.1 4.6-4.6zm-1.5 0c0 1.7-1.4 3.1-3.1 3.1s-3.1-1.4-3.1-3.1 1.4-3.1 3.1-3.1 3.1 1.4 3.1 3.1z" fill="black" />
                                </svg>
                            </span>
                            <input type="text" data-kt-orders-table-filter="search" class="form-control form-control-solid w-250px ps-15" placeholder="Tìm kiếm đơn hàng..." />
                        </div>
                        <!--end::Search-->
                        <!--begin::Filters-->
                        <div class="d-flex align-items-center gap-2">
                            <!-- Filter Button -->
                            <button type="button" class="btn btn-light-primary position-relative" data-bs-toggle="modal" data-bs-target="#filterModal">
                                <i class="fas fa-filter me-2"></i>
                                Bộ lọc
                                <span class="position-absolute top-0 start-100 translate-middle  badge badge-circle badge-warning" id="activeFiltersCount" style="display: none;">0</span>
                            </button>

                            <!-- Quick Filters -->
                            <div class="d-flex align-items-center gap-2" id="quickFilters">
                                <button type="button" class="btn btn-sm btn-light-success" onclick="applyQuickFilter('status', 'processing')">
                                    <i class="fas fa-clock me-1"></i>
                                    Đang xử lý
                                </button>
                                <button type="button" class="btn btn-sm btn-light-warning" onclick="applyQuickFilter('payment_status', 'unpaid')">
                                    <i class="fas fa-times-circle me-1"></i>
                                    Chưa thanh toán
                                </button>
                                <button type="button" class="btn btn-sm btn-light-info" onclick="applyQuickFilter('delivery_status', 'pending')">
                                    <i class="fas fa-box me-1"></i>
                                    Chờ giao hàng
                                </button>
                            </div>

                            <!-- Clear Filters -->
                            <button type="button" class="btn btn-light-danger" id="clearAllFilters" onclick="clearAllFilters()" style="display: none;">
                                <i class="fas fa-times me-2"></i>
                                Xóa bộ lọc
                            </button>
                        </div>
                        <!--end::Filters-->
                        
                    </div>
                    <!--end::Table Toolbar-->
                    <!--begin::Group actions-->
                        <div class="d-flex justify-content-end d-none" data-kt-orders-table-toolbar="selected">
                            <div class="fw-bolder me-5">
                                <span class="me-2 p-3" data-kt-orders-table-select="selected_count"></span>Đã chọn
                            </div>
                            <button type="button" class="btn btn-danger" data-kt-orders-table-select="delete_selected">
                                <i class="fas fa-trash me-2 "></i>
                                Xóa đã chọn
                            </button>
                        </div>
                        <!--end::Group actions-->
                    <!--begin::Table container-->
                    <div class="table-responsive">
                        <!--begin::Table-->
                        <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4" id="kt_orders_table">
                            <!--begin::Table head-->
                            <thead>
                                <tr class="fw-bolder text-muted">
                                    <th class="w-10px pe-2">
                                        <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                            <input class="form-check-input" type="checkbox" data-kt-check="true" data-kt-check-target="#kt_orders_table .form-check-input" value="1" />
                                        </div>
                                    </th>
                                    <th class="min-w-150px">Mã đơn hàng</th>
                                    <th class="min-w-140px">Khách hàng</th>
                                    <th class="min-w-120px">Số lượng</th>
                                    <th class="min-w-120px">Tổng tiền</th>
                                    <th class="min-w-120px">Đã thanh toán</th>
                                    <th class="min-w-100px">Trạng thái</th>
                                    <th class="min-w-100px">Thanh toán</th>
                                    <th class="min-w-100px">Giao hàng</th>
                                    <th class="min-w-100px">Kênh</th>
                                    <th class="min-w-100px">Ngày tạo</th>
                                    <th class="min-w-100px text-end">Thao tác</th>
                                </tr>
                            </thead>
                            <!--end::Table head-->
                            <!--begin::Table body-->
                            <tbody>
                                <!-- Data will be loaded via AJAX -->
                            </tbody>
                            <!--end::Table body-->
                        </table>
                        <!--end::Table-->
                    </div>
                    <!--end::Table container-->
                </div>
                <!--begin::Body-->
            </div>
            <!--end::Tables Widget 9-->
        </div>
        <!--end::Col-->
    </div>
    <!--end::Row-->

    <!-- Filter Modal -->
    <div class="modal fade" id="filterModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mw-700px">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="fw-bolder">
                        <i class="fas fa-filter fs-2 me-2"></i>
                        Bộ lọc đơn hàng
                    </h2>
                    <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                        <i class="fas fa-times fs-1"></i>
                    </div>
                </div>
                <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                    <form id="filterForm">
                        <div class="row">
                            <!-- Order Status Filter -->
                            <div class="col-md-6 mb-7">
                                <label class="fw-bold fs-6 mb-2">Trạng thái đơn hàng</label>
                                <select class="form-select form-select-solid" data-kt-orders-table-filter="status" id="filterStatus">
                                    <option value="">Tất cả trạng thái</option>
                                    <option value="processing">Đang xử lý</option>
                                    <option value="completed">Hoàn thành</option>
                                    <option value="cancelled">Đã hủy</option>
                                    <option value="failed">Thất bại</option>
                                </select>
                            </div>

                            <!-- Payment Status Filter -->
                            <div class="col-md-6 mb-7">
                                <label class="fw-bold fs-6 mb-2">Trạng thái thanh toán</label>
                                <select class="form-select form-select-solid" data-kt-orders-table-filter="payment_status" id="filterPaymentStatus">
                                    <option value="">Tất cả thanh toán</option>
                                    <option value="unpaid">Chưa thanh toán</option>
                                    <option value="partial">Thanh toán một phần</option>
                                    <option value="paid">Đã thanh toán</option>
                                    <option value="overpaid">Thanh toán thừa</option>
                                    <option value="refunded">Đã hoàn tiền</option>
                                </select>
                            </div>

                            <!-- Delivery Status Filter -->
                            <div class="col-md-6 mb-7">
                                <label class="fw-bold fs-6 mb-2">Trạng thái giao hàng</label>
                                <select class="form-select form-select-solid" data-kt-orders-table-filter="delivery_status" id="filterDeliveryStatus">
                                    <option value="">Tất cả giao hàng</option>
                                    <option value="pending">Chờ xử lý</option>
                                    <option value="picking">Đang chuẩn bị</option>
                                    <option value="delivering">Đang giao</option>
                                    <option value="delivered">Đã giao</option>
                                    <option value="returning">Đang trả</option>
                                    <option value="returned">Đã trả</option>
                                </select>
                            </div>

                            <!-- Channel Filter -->
                            <div class="col-md-6 mb-7">
                                <label class="fw-bold fs-6 mb-2">Kênh bán hàng</label>
                                <select class="form-select form-select-solid" data-kt-orders-table-filter="channel" id="filterChannel">
                                    <option value="">Tất cả kênh</option>
                                    <option value="direct">Trực tiếp</option>
                                    <option value="online">Online</option>
                                    <option value="pos">POS</option>
                                    <option value="other">Khác</option>
                                </select>
                            </div>

                            <!-- Date Range Filter -->
                            <div class="col-md-6 mb-7">
                                <label class="fw-bold fs-6 mb-2">Từ ngày</label>
                                <input type="date" class="form-control form-control-solid" data-kt-orders-table-filter="date_from" id="filterDateFrom">
                            </div>

                            <div class="col-md-6 mb-7">
                                <label class="fw-bold fs-6 mb-2">Đến ngày</label>
                                <input type="date" class="form-control form-control-solid" data-kt-orders-table-filter="date_to" id="filterDateTo">
                            </div>

                            <!-- Amount Range Filter -->
                            <div class="col-md-6 mb-7">
                                <label class="fw-bold fs-6 mb-2">Tổng tiền từ (₫)</label>
                                <input type="number" class="form-control form-control-solid" data-kt-orders-table-filter="amount_from" id="filterAmountFrom" placeholder="0" min="0" step="1000">
                            </div>

                            <div class="col-md-6 mb-7">
                                <label class="fw-bold fs-6 mb-2">Tổng tiền đến (₫)</label>
                                <input type="number" class="form-control form-control-solid" data-kt-orders-table-filter="amount_to" id="filterAmountTo" placeholder="10,000,000" min="0" step="1000">
                            </div>

                            <!-- Customer Filter -->
                            <div class="col-md-6 mb-7">
                                <label class="fw-bold fs-6 mb-2">Khách hàng</label>
                                <input type="text" class="form-control form-control-solid" data-kt-orders-table-filter="customer" id="filterCustomer" placeholder="Tên hoặc SĐT khách hàng">
                            </div>

                            <!-- Branch Shop Filter -->
                            <div class="col-md-6 mb-7">
                                <label class="fw-bold fs-6 mb-2">Chi nhánh cửa hàng</label>
                                <select class="form-select form-select-solid" data-kt-orders-table-filter="branch_shop_id" id="filterBranchShop">
                                    <option value="">Tất cả chi nhánh</option>
                                    <!-- Options will be loaded via AJAX -->
                                </select>
                            </div>
                        </div>

                        <!-- Saved Filters -->
                        <div class="separator separator-dashed my-7"></div>
                        <div class="mb-7">
                            <label class="fw-bold fs-6 mb-2">Bộ lọc đã lưu</label>
                            <div class="d-flex flex-wrap gap-2" id="savedFilters">
                                <button type="button" class="btn btn-sm btn-light-primary" onclick="applySavedFilter('today')">
                                    <i class="fas fa-calendar-day fs-6 me-1"></i>
                                    Hôm nay
                                </button>
                                <button type="button" class="btn btn-sm btn-light-primary" onclick="applySavedFilter('this_week')">
                                    <i class="fas fa-calendar-week fs-6 me-1"></i>
                                    Tuần này
                                </button>
                                <button type="button" class="btn btn-sm btn-light-primary" onclick="applySavedFilter('this_month')">
                                    <i class="fas fa-calendar-alt fs-6 me-1"></i>
                                    Tháng này
                                </button>
                                <button type="button" class="btn btn-sm btn-light-success" onclick="applySavedFilter('completed_orders')">
                                    <i class="fas fa-check-circle fs-6 me-1"></i>
                                    Đơn hoàn thành
                                </button>
                                <button type="button" class="btn btn-sm btn-light-warning" onclick="applySavedFilter('pending_payment')">
                                    <i class="fas fa-clock fs-6 me-1"></i>
                                    Chờ thanh toán
                                </button>
                            </div>
                        </div>

                        <div class="text-center pt-15">
                            <button type="button" class="btn btn-light me-3" onclick="resetFilters()">
                                <i class="fas fa-sync-alt fs-2"></i>
                                Reset
                            </button>
                            <button type="button" class="btn btn-primary" onclick="applyFilters()">
                                <i class="fas fa-filter fs-2"></i>
                                Áp dụng bộ lọc
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Update Modal -->
    <div class="modal fade" id="statusUpdateModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mw-650px">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="fw-bolder">Cập nhật trạng thái đơn hàng</h2>
                    <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                        <span class="svg-icon svg-icon-1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                <rect opacity="0.5" x="6" y="17.3137" width="16" height="2" rx="1" transform="rotate(-45 6 17.3137)" fill="black" />
                                <rect x="7.41422" y="6" width="16" height="2" rx="1" transform="rotate(45 7.41422 6)" fill="black" />
                            </svg>
                        </span>
                    </div>
                </div>
                <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                    <form id="statusUpdateForm">
                        <input type="hidden" id="updateOrderId" name="order_id">
                        
                        <div class="fv-row mb-7">
                            <label class="required fw-bold fs-6 mb-2">Trạng thái đơn hàng</label>
                            <select class="form-select form-select-solid" name="status" id="orderStatus">
                                <option value="processing">Đang xử lý</option>
                                <option value="completed">Hoàn thành</option>
                                <option value="cancelled">Đã hủy</option>
                                <option value="failed">Thất bại</option>
                            </select>
                        </div>

                        <div class="fv-row mb-7">
                            <label class="required fw-bold fs-6 mb-2">Trạng thái giao hàng</label>
                            <select class="form-select form-select-solid" name="delivery_status" id="deliveryStatus">
                                <option value="pending">Chờ xử lý</option>
                                <option value="picking">Đang chuẩn bị</option>
                                <option value="delivering">Đang giao</option>
                                <option value="delivered">Đã giao</option>
                                <option value="returning">Đang trả</option>
                                <option value="returned">Đã trả</option>
                            </select>
                        </div>

                        <div class="fv-row mb-7">
                            <label class="required fw-bold fs-6 mb-2">Trạng thái thanh toán</label>
                            <select class="form-select form-select-solid" name="payment_status" id="paymentStatus">
                                <option value="unpaid">Chưa thanh toán</option>
                                <option value="partial">Thanh toán một phần</option>
                                <option value="paid">Đã thanh toán</option>
                                <option value="overpaid">Thanh toán thừa</option>
                                <option value="refunded">Đã hoàn tiền</option>
                            </select>
                        </div>

                        <div class="fv-row mb-7">
                            <label class="fw-bold fs-6 mb-2">Ghi chú nội bộ</label>
                            <textarea class="form-control form-control-solid" name="internal_notes" id="internalNotes" rows="3" placeholder="Ghi chú nội bộ cho đơn hàng..."></textarea>
                        </div>

                        <div class="text-center pt-15">
                            <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">Hủy</button>
                            <button type="submit" class="btn btn-primary" id="statusUpdateSubmit">
                                <span class="indicator-label">Cập nhật</span>
                                <span class="indicator-progress">Đang xử lý...
                                    <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Detail Modal -->
    <div class="modal fade" id="orderDetailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mw-900px">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="fw-bolder">Chi tiết đơn hàng</h2>
                    <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                        <i class="fas fa-times fs-1"></i>
                    </div>
                </div>
                <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                    <div id="orderDetailContent">
                        <!-- Content will be loaded via AJAX -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Record Modal -->
    <div class="modal fade" id="paymentRecordModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mw-650px">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="fw-bolder">Ghi nhận thanh toán</h2>
                    <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                        <i class="fas fa-times fs-1"></i>
                    </div>
                </div>
                <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                    <form id="paymentRecordForm">
                        <input type="hidden" id="paymentOrderId" name="order_id">

                        <div class="row mb-7">
                            <div class="col-md-6">
                                <label class="fw-bold fs-6 mb-2">Tổng tiền đơn hàng</label>
                                <div class="form-control form-control-solid bg-light" id="orderTotalAmount">0 ₫</div>
                            </div>
                            <div class="col-md-6">
                                <label class="fw-bold fs-6 mb-2">Đã thanh toán</label>
                                <div class="form-control form-control-solid bg-light" id="orderPaidAmount">0 ₫</div>
                            </div>
                        </div>

                        <div class="fv-row mb-7">
                            <label class="required fw-bold fs-6 mb-2">Số tiền thanh toán</label>
                            <input type="number" class="form-control form-control-solid" name="amount" id="paymentAmount" placeholder="Nhập số tiền thanh toán" min="0" step="1000" />
                        </div>

                        <div class="fv-row mb-7">
                            <label class="required fw-bold fs-6 mb-2">Phương thức thanh toán</label>
                            <select class="form-select form-select-solid" name="payment_method" id="paymentMethod">
                                <option value="cash">Tiền mặt</option>
                                <option value="card">Thẻ tín dụng/ghi nợ</option>
                                <option value="transfer">Chuyển khoản ngân hàng</option>
                                <option value="cod">Thanh toán khi nhận hàng</option>
                                <option value="e_wallet">Ví điện tử</option>
                                <option value="installment">Trả góp</option>
                                <option value="credit">Công nợ</option>
                                <option value="voucher">Phiếu quà tặng</option>
                                <option value="points">Điểm tích lũy</option>
                                <option value="mixed">Thanh toán hỗn hợp</option>
                            </select>
                        </div>

                        <div class="fv-row mb-7">
                            <label class="fw-bold fs-6 mb-2">Mã tham chiếu</label>
                            <input type="text" class="form-control form-control-solid" name="payment_reference" id="paymentReference" placeholder="Mã giao dịch, số hóa đơn..." />
                        </div>

                        <div class="fv-row mb-7">
                            <label class="fw-bold fs-6 mb-2">Ghi chú thanh toán</label>
                            <textarea class="form-control form-control-solid" name="payment_notes" id="paymentNotes" rows="3" placeholder="Ghi chú về thanh toán..."></textarea>
                        </div>

                        <div class="text-center pt-15">
                            <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">Hủy</button>
                            <button type="submit" class="btn btn-primary" id="paymentRecordSubmit">
                                <span class="indicator-label">Ghi nhận thanh toán</span>
                                <span class="indicator-progress">Đang xử lý...
                                    <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions Modal -->
    <div class="modal fade" id="quickActionsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mw-500px">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="fw-bolder">Thao tác nhanh</h2>
                    <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                        <i class="fas fa-times fs-1"></i>
                    </div>
                </div>
                <div class="modal-body text-center py-10">
                    <input type="hidden" id="quickActionOrderId">

                    <div class="d-flex flex-column gap-5">
                        <button type="button" class="btn btn-light-success" onclick="markOrderAsPaid()">
                            <i class="fas fa-check-circle fs-2"></i>
                            Đánh dấu đã thanh toán
                        </button>

                        <button type="button" class="btn btn-light-primary" onclick="markOrderAsDelivered()">
                            <i class="fas fa-truck fs-2"></i>
                            Đánh dấu đã giao hàng
                        </button>

                        <button type="button" class="btn btn-light-warning" onclick="markOrderAsCompleted()">
                            <i class="fas fa-medal fs-2"></i>
                            Hoàn thành đơn hàng
                        </button>

                        <button type="button" class="btn btn-light-danger" onclick="cancelOrder()">
                            <i class="fas fa-times-circle fs-2"></i>
                            Hủy đơn hàng
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('vendor-script')
    <script src="{{ asset('admin-assets/assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
@endsection

@section('scripts')
    <script src="{{ asset('admin-assets/assets/js/custom/apps/orders/list/table.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
    <script>
    // Update JavaScript URLs to match our route structure
    const orderRoutes = {
        data: '{{ route("admin.order.ajax") }}',
        get: '{{ route("admin.order.get", ":id") }}',
        detail: '{{ route("admin.order.detail.modal", ":id") }}',
        updateStatus: '/admin/order/update-status/:id',
        recordPayment: '/admin/order/:id/record-payment',
        quickUpdate: '{{ route("admin.order.quick.update", ":id") }}',
        bulkDelete: '{{ route("admin.order.bulk.delete") }}'
    };

    // Replace :id placeholder with actual ID
    function getOrderRoute(routeName, orderId) {
        return orderRoutes[routeName].replace(':id', orderId);
    }

    function formatCurrency(amount) {
        return new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND'
        }).format(amount);
    }

    // Filter Management Functions
    let currentFilters = {};

    function applyQuickFilter(filterType, value) {
        // Set the filter value
        const filterElement = document.querySelector(`[data-kt-orders-table-filter="${filterType}"]`);
        if (filterElement) {
            filterElement.value = value;
            currentFilters[filterType] = value;
        }

        // Update active filters count and reload table
        updateActiveFiltersCount();
        $('#kt_orders_table').DataTable().ajax.reload();
    }

    function clearAllFilters() {
        // Clear all filter elements
        document.querySelectorAll('[data-kt-orders-table-filter]').forEach(element => {
            if (element.type === 'select-one') {
                element.value = '';
            } else if (element.type === 'date' || element.type === 'number' || element.type === 'text') {
                element.value = '';
            }
        });

        // Clear current filters object
        currentFilters = {};

        // Update UI and reload table
        updateActiveFiltersCount();
        $('#kt_orders_table').DataTable().ajax.reload();
    }

    function resetFilters() {
        clearAllFilters();
        $('#filterModal').modal('hide');
    }

    function applyFilters() {
        // Collect all filter values
        currentFilters = {};
        document.querySelectorAll('[data-kt-orders-table-filter]').forEach(element => {
            if (element.value && element.value.trim() !== '') {
                const filterName = element.getAttribute('data-kt-orders-table-filter');
                currentFilters[filterName] = element.value;
            }
        });

        // Update UI and reload table
        updateActiveFiltersCount();
        $('#kt_orders_table').DataTable().ajax.reload();
        $('#filterModal').modal('hide');
    }

    function updateActiveFiltersCount() {
        const count = Object.keys(currentFilters).length;
        const badge = document.getElementById('activeFiltersCount');
        const clearButton = document.getElementById('clearAllFilters');

        if (count > 0) {
            badge.textContent = count;
            badge.style.display = 'inline-block';
            clearButton.style.display = 'inline-block';
        } else {
            badge.style.display = 'none';
            clearButton.style.display = 'none';
        }
    }

    function applySavedFilter(filterType) {
        const today = new Date();
        const formatDate = (date) => date.toISOString().split('T')[0];

        // Clear existing filters first
        clearAllFilters();

        switch(filterType) {
            case 'today':
                document.getElementById('filterDateFrom').value = formatDate(today);
                document.getElementById('filterDateTo').value = formatDate(today);
                break;

            case 'this_week':
                const startOfWeek = new Date(today);
                startOfWeek.setDate(today.getDate() - today.getDay());
                const endOfWeek = new Date(startOfWeek);
                endOfWeek.setDate(startOfWeek.getDate() + 6);

                document.getElementById('filterDateFrom').value = formatDate(startOfWeek);
                document.getElementById('filterDateTo').value = formatDate(endOfWeek);
                break;

            case 'this_month':
                const startOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
                const endOfMonth = new Date(today.getFullYear(), today.getMonth() + 1, 0);

                document.getElementById('filterDateFrom').value = formatDate(startOfMonth);
                document.getElementById('filterDateTo').value = formatDate(endOfMonth);
                break;

            case 'completed_orders':
                document.getElementById('filterStatus').value = 'completed';
                break;

            case 'pending_payment':
                document.getElementById('filterPaymentStatus').value = 'unpaid';
                break;
        }

        // Apply the filters
        applyFilters();
    }

    // Load branch shops for filter dropdown
    function loadBranchShops() {
        fetch('/admin/branch-shops/active')
            .then(response => response.json())
            .then(data => {
                const branchShopSelect = document.getElementById('filterBranchShop');
                if (data.success && data.branch_shops) {
                    data.branch_shops.forEach(branchShop => {
                        const option = document.createElement('option');
                        option.value = branchShop.id;
                        option.textContent = branchShop.name;
                        branchShopSelect.appendChild(option);
                    });
                }
            })
            .catch(error => {
                console.error('Error loading branch shops:', error);
            });
    }

    // Form submissions
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize filter system
        loadBranchShops();
        updateActiveFiltersCount();

        // Initialize filter modal event listeners
        const filterModal = document.getElementById('filterModal');
        if (filterModal) {
            filterModal.addEventListener('shown.bs.modal', function() {
                // Focus on first filter when modal opens
                document.getElementById('filterStatus').focus();
            });
        }

        // Initialize status update modal event listeners
        const statusUpdateModal = document.getElementById('statusUpdateModal');
        if (statusUpdateModal) {
            statusUpdateModal.addEventListener('shown.bs.modal', function() {
                // Re-attach event listeners when modal is shown
                const form = document.getElementById('statusUpdateForm');
                const submitBtn = document.getElementById('statusUpdateSubmit');
            });
        }

        // Initialize payment record modal event listeners
        const paymentRecordModal = document.getElementById('paymentRecordModal');
        if (paymentRecordModal) {
            paymentRecordModal.addEventListener('shown.bs.modal', function() {
                // Re-attach event listeners when modal is shown
                const form = document.getElementById('paymentRecordForm');
                const submitBtn = document.getElementById('paymentRecordSubmit');
            });
        }

        // Add change event listeners to filter elements
        document.querySelectorAll('[data-kt-orders-table-filter]').forEach(element => {
            element.addEventListener('change', function() {
                // Auto-apply filters when changed (optional)
                // You can comment this out if you want manual apply only
                // setTimeout(() => applyFilters(), 300);
            });
        });
        // Status update form
        const statusForm = document.getElementById('statusUpdateForm');
        if (statusForm) {
            statusForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(statusForm);
                const orderId = formData.get('order_id');
                const submitButton = document.getElementById('statusUpdateSubmit');

                // Show loading indication
                if (submitButton) {
                    submitButton.setAttribute('data-kt-indicator', 'on');
                    submitButton.disabled = true;
                }

                fetch(getOrderRoute('updateStatus', orderId), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        $('#statusUpdateModal').modal('hide');
                        Swal.fire({
                            text: "Đã cập nhật trạng thái đơn hàng thành công.",
                            icon: "success",
                            buttonsStyling: false,
                            confirmButtonText: "Đã hiểu!",
                            customClass: {
                                confirmButton: "btn btn-primary"
                            }
                        });
                        $('#kt_orders_table').DataTable().ajax.reload();
                    } else {
                        Swal.fire({
                            text: data.message || "Có lỗi xảy ra khi cập nhật trạng thái.",
                            icon: "error",
                            buttonsStyling: false,
                            confirmButtonText: "Đã hiểu!",
                            customClass: {
                                confirmButton: "btn btn-primary"
                            }
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        text: "Có lỗi xảy ra khi cập nhật trạng thái.",
                        icon: "error",
                        buttonsStyling: false,
                        confirmButtonText: "Đã hiểu!",
                        customClass: {
                            confirmButton: "btn btn-primary"
                        }
                    });
                })
                .finally(() => {
                    // Remove loading indication
                    if (submitButton) {
                        submitButton.removeAttribute('data-kt-indicator');
                        submitButton.disabled = false;
                    }
                });
            });
        }

        // Alternative: Listen for submit button clicks directly
        const statusSubmitButton = document.getElementById('statusUpdateSubmit');
        if (statusSubmitButton) {
            statusSubmitButton.addEventListener('click', function(e) {
                e.preventDefault();

                const form = document.getElementById('statusUpdateForm');
                if (form) {
                    const formData = new FormData(form);
                    const orderId = formData.get('order_id');

                    // Show loading indication
                    this.setAttribute('data-kt-indicator', 'on');
                    this.disabled = true;

                    const url = getOrderRoute('updateStatus', orderId);

                    fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            $('#statusUpdateModal').modal('hide');
                            Swal.fire({
                                text: "Đã cập nhật trạng thái đơn hàng thành công.",
                                icon: "success",
                                buttonsStyling: false,
                                confirmButtonText: "Đã hiểu!",
                                customClass: {
                                    confirmButton: "btn btn-primary"
                                }
                            });
                            $('#kt_orders_table').DataTable().ajax.reload();
                        } else {
                            Swal.fire({
                                text: data.message || "Có lỗi xảy ra khi cập nhật trạng thái.",
                                icon: "error",
                                buttonsStyling: false,
                                confirmButtonText: "Đã hiểu!",
                                customClass: {
                                    confirmButton: "btn btn-primary"
                                }
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            text: "Có lỗi xảy ra khi cập nhật trạng thái.",
                            icon: "error",
                            buttonsStyling: false,
                            confirmButtonText: "Đã hiểu!",
                            customClass: {
                                confirmButton: "btn btn-primary"
                            }
                        });
                    })
                    .finally(() => {
                        // Remove loading indication
                        this.removeAttribute('data-kt-indicator');
                        this.disabled = false;
                    });
                }
            });
        }

        // Payment record form
        const paymentForm = document.getElementById('paymentRecordForm');
        if (paymentForm) {
            paymentForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(paymentForm);
                const orderId = formData.get('order_id');
                const submitButton = document.getElementById('paymentRecordSubmit');

                // Show loading indication
                if (submitButton) {
                    submitButton.setAttribute('data-kt-indicator', 'on');
                    submitButton.disabled = true;
                }

                fetch(getOrderRoute('recordPayment', orderId), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        $('#paymentRecordModal').modal('hide');
                        Swal.fire({
                            text: "Đã ghi nhận thanh toán thành công.",
                            icon: "success",
                            buttonsStyling: false,
                            confirmButtonText: "Đã hiểu!",
                            customClass: {
                                confirmButton: "btn btn-primary"
                            }
                        });
                        $('#kt_orders_table').DataTable().ajax.reload();
                    } else {
                        Swal.fire({
                            text: data.message || "Có lỗi xảy ra khi ghi nhận thanh toán.",
                            icon: "error",
                            buttonsStyling: false,
                            confirmButtonText: "Đã hiểu!",
                            customClass: {
                                confirmButton: "btn btn-primary"
                            }
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        text: "Có lỗi xảy ra khi ghi nhận thanh toán.",
                        icon: "error",
                        buttonsStyling: false,
                        confirmButtonText: "Đã hiểu!",
                        customClass: {
                            confirmButton: "btn btn-primary"
                        }
                    });
                })
                .finally(() => {
                    // Remove loading indication
                    if (submitButton) {
                        submitButton.removeAttribute('data-kt-indicator');
                        submitButton.disabled = false;
                    }
                });
            });
        }

        // Alternative: Listen for payment submit button clicks directly
        const paymentSubmitButton = document.getElementById('paymentRecordSubmit');
        if (paymentSubmitButton) {
            paymentSubmitButton.addEventListener('click', function(e) {
                e.preventDefault();

                const form = document.getElementById('paymentRecordForm');
                if (form) {
                    const formData = new FormData(form);
                    const orderId = formData.get('order_id');

                    // Show loading indication
                    this.setAttribute('data-kt-indicator', 'on');
                    this.disabled = true;

                    const url = getOrderRoute('recordPayment', orderId);

                    fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            $('#paymentRecordModal').modal('hide');
                            Swal.fire({
                                text: "Đã ghi nhận thanh toán thành công.",
                                icon: "success",
                                buttonsStyling: false,
                                confirmButtonText: "Đã hiểu!",
                                customClass: {
                                    confirmButton: "btn btn-primary"
                                }
                            });
                            $('#kt_orders_table').DataTable().ajax.reload();
                        } else {
                            Swal.fire({
                                text: data.message || "Có lỗi xảy ra khi ghi nhận thanh toán.",
                                icon: "error",
                                buttonsStyling: false,
                                confirmButtonText: "Đã hiểu!",
                                customClass: {
                                    confirmButton: "btn btn-primary"
                                }
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            text: "Có lỗi xảy ra khi ghi nhận thanh toán.",
                            icon: "error",
                            buttonsStyling: false,
                            confirmButtonText: "Đã hiểu!",
                            customClass: {
                                confirmButton: "btn btn-primary"
                            }
                        });
                    })
                    .finally(() => {
                        // Remove loading indication
                        this.removeAttribute('data-kt-indicator');
                        this.disabled = false;
                    });
                }
            });
        }
    });
    </script>
@endsection
