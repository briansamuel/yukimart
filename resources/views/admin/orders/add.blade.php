@extends('admin.index')
@section('page-header', __('menu.add_order'))
@section('page-sub_header', __('menu.add_order'))

@section('style')
    <link href="{{ asset('admin-assets/assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
    <!--begin::Row-->
    <div class="row g-5 g-xl-8">
        <!--begin::Col-->
        <div class="col-xl-12">
            <!--begin::Card-->
            <div class="card card-xl-stretch mb-5 mb-xl-8">
                <!--begin::Header-->
                <div class="card-header border-0 pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bolder fs-3 mb-1">{{ __('menu.add_order') }}</span>
                        <span class="text-muted mt-1 fw-bold fs-7">{{ __('order.create_order_description') }}</span>
                    </h3>
                    <div class="card-toolbar">
                        <a href="{{ route('admin.order.list') }}" class="btn btn-sm btn-light">
                            <span class="svg-icon svg-icon-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                    <path d="M11.2657 11.4343L15.45 7.25C15.8642 6.83579 15.8642 6.16421 15.45 5.75C15.0358 5.33579 14.3642 5.33579 13.95 5.75L8.40712 11.2929C8.01659 11.6834 8.01659 12.3166 8.40712 12.7071L13.95 18.25C14.3642 18.6642 15.0358 18.6642 15.45 18.25C15.8642 17.8358 15.8642 17.1642 15.45 16.75L11.2657 12.5657C10.9533 12.2533 10.9533 11.7467 11.2657 11.4343Z" fill="black"/>
                                </svg>
                            </span>
                            {{ __('common.back') }}
                        </a>
                    </div>
                </div>
                <!--end::Header-->
                <!--begin::Body-->
                <div class="card-body py-3">
                    <form id="kt_add_order_form" class="form" action="{{ route('admin.order.add.action') }}" method="POST">
                        @csrf
                        
                        <!--begin::Order Information-->
                        <div class="row mb-8">
                            <div class="col-xl-3">
                                <div class="fs-6 fw-bold mt-2 mb-3">Thông tin đơn hàng</div>
                            </div>
                            <div class="col-xl-9">
                                <div class="row g-9">
                                    <div class="col-md-6 fv-row">
                                        <label class="required fs-6 fw-bold mb-2">Mã đơn hàng</label>
                                        <input type="text" class="form-control form-control-solid" name="order_code" placeholder="Mã đơn hàng tự động" readonly />
                                    </div>
                                    <div class="col-md-6 fv-row">
                                        <label class="required fs-6 fw-bold mb-2">{{ __('orders.branch_shop') }}</label>
                                        @if($userBranchShops->isNotEmpty())
                                            <select name="branch_shop_id" id="branch_shop_id" class="form-select form-select-solid" data-control="select2" data-placeholder="{{ __('orders.select_branch_shop') }}">
                                                <option value="">{{ __('orders.select_branch_shop') }}</option>
                                                @foreach($userBranchShops as $branchShop)
                                                    <option value="{{ $branchShop->id }}"
                                                        {{ ($defaultBranchShop && $defaultBranchShop->id == $branchShop->id) ? 'selected' : '' }}
                                                        data-shop-type="{{ $branchShop->shop_type_label }}"
                                                        data-address="{{ $branchShop->address }}"
                                                        data-role="{{ $branchShop->pivot->role_label }}">
                                                        {{ $branchShop->name }}
                                                        @if($branchShop->pivot->is_primary)
                                                            <span class="badge badge-light-primary ms-2">{{ __('users.primary') }}</span>
                                                        @endif
                                                        ({{ $branchShop->pivot->role_label }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            <div class="form-text">{{ __('orders.help.branch_shop_selection') }}</div>
                                        @else
                                            <div class="alert alert-warning d-flex align-items-center">
                                                <i class="ki-duotone ki-warning fs-2 text-warning me-3">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                                <div class="flex-grow-1">
                                                    <div class="fw-bold">{{ __('orders.no_branch_shops') }}</div>
                                                    <div class="text-muted fs-7">{{ __('orders.contact_admin_for_branch_assignment') }}</div>
                                                </div>
                                            </div>
                                            <input type="hidden" name="branch_shop_id" value="">
                                        @endif
                                    </div>
                                    <div class="col-md-6 fv-row">
                                        <label class="required fs-6 fw-bold mb-2">Kênh bán hàng</label>
                                        <select name="channel" class="form-select form-select-solid" data-control="select2" data-placeholder="Chọn kênh bán hàng">
                                            <option value="">Chọn kênh bán hàng</option>
                                            <option value="online">Online</option>
                                            <option value="offline">Offline</option>
                                            <option value="phone">Điện thoại</option>
                                            <option value="direct">Trực tiếp</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 fv-row">
                                        <label class="required fs-6 fw-bold mb-2">Trạng thái</label>
                                        <select name="status" class="form-select form-select-solid" data-control="select2" data-placeholder="Chọn trạng thái">
                                            <option value="">Chọn trạng thái</option>
                                            <option value="processing" selected>Đang xử lý</option>
                                            <option value="confirmed">Đã xác nhận</option>
                                            <option value="completed">Đã hoàn thành</option>
                                            <option value="returned">Đã hoàn hàng</option>
                                            <option value="cancelled">Đã hủy</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 fv-row">
                                        <label class="fs-6 fw-bold mb-2">Trạng thái giao hàng</label>
                                        <select name="delivery_status" class="form-select form-select-solid" data-control="select2" data-placeholder="Chọn trạng thái giao hàng">
                                            <option value="">Chọn trạng thái giao hàng</option>
                                            <option value="pending" selected>Chờ giao hàng</option>
                                            <option value="in_transit">Đang giao hàng</option>
                                            <option value="delivered">Đã giao hàng</option>
                                            <option value="failed">Giao hàng thất bại</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--end::Order Information-->

                        <!--begin::Customer Information-->
                        <div class="row mb-8">
                            <div class="col-xl-3">
                                <div class="fs-6 fw-bold mt-2 mb-3">{{ __('order.customer_information') }}</div>
                            </div>
                            <div class="col-xl-9">
                                <div class="row g-9">
                                    <div class="col-md-12 fv-row">
                                        <label class="required fs-6 fw-bold mb-2">{{ __('order.customer') }}</label>
                                        <div class="d-flex align-items-center">
                                            <select name="customer_id" id="customer_id" class="form-select form-select-solid me-3" data-control="select2" data-placeholder="{{ __('order.search_customer') }}">
                                                <option value="">{{ __('order.select_customer') }}</option>
                                                <option value="new_customer">{{ __('order.new_customer') }}</option>
                                            </select>
                                            <button type="button" class="btn btn-light-primary" id="btn_add_new_customer" title="{{ __('order.add_new_customer') }}">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!--begin::New Customer Form-->
                                <div id="new_customer_form" class="row g-9 mt-5" style="display: none;">
                                    <div class="col-12">
                                        <div class="card card-bordered">
                                            <div class="card-header">
                                                <h3 class="card-title">{{ __('order.new_customer_info') }}</h3>
                                                <div class="card-toolbar">
                                                    <button type="button" class="btn btn-sm btn-light" id="btn_cancel_new_customer">
                                                        <i class="fas fa-times"></i> {{ __('common.cancel') }}
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <div class="row g-6">
                                                    <div class="col-md-6 fv-row">
                                                        <label class="required fs-6 fw-bold mb-2">{{ __('order.customer_name') }}</label>
                                                        <input type="text" class="form-control form-control-solid" id="new_customer_name" placeholder="{{ __('order.enter_customer_name') }}" />
                                                        <div class="invalid-feedback" id="new_customer_name_error"></div>
                                                    </div>
                                                    <div class="col-md-6 fv-row">
                                                        <label class="required fs-6 fw-bold mb-2">{{ __('order.customer_phone') }}</label>
                                                        <input type="text" class="form-control form-control-solid" id="new_customer_phone" placeholder="{{ __('order.enter_customer_phone') }}" />
                                                        <div class="invalid-feedback" id="new_customer_phone_error"></div>
                                                    </div>
                                                    <div class="col-md-6 fv-row">
                                                        <label class="fs-6 fw-bold mb-2">{{ __('order.customer_email') }}</label>
                                                        <input type="email" class="form-control form-control-solid" id="new_customer_email" placeholder="{{ __('order.enter_customer_email') }}" />
                                                        <div class="invalid-feedback" id="new_customer_email_error"></div>
                                                    </div>
                                                    <div class="col-md-6 fv-row">
                                                        <label class="fs-6 fw-bold mb-2">{{ __('order.customer_type') }}</label>
                                                        <select class="form-select form-select-solid" id="new_customer_type">
                                                            <option value="individual">{{ __('order.individual') }}</option>
                                                            <option value="business">{{ __('order.business') }}</option>
                                                            <option value="vip">{{ __('order.vip') }}</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-12 fv-row">
                                                        <label class="fs-6 fw-bold mb-2">{{ __('order.customer_address') }}</label>
                                                        <textarea class="form-control form-control-solid" id="new_customer_address" rows="2" placeholder="{{ __('order.enter_customer_address') }}"></textarea>
                                                    </div>
                                                </div>
                                                <div class="d-flex justify-content-end mt-6">
                                                    <button type="button" class="btn btn-light me-3" id="btn_cancel_new_customer_form">{{ __('common.cancel') }}</button>
                                                    <button type="button" class="btn btn-primary" id="btn_save_new_customer">
                                                        <span class="indicator-label">{{ __('order.create_customer') }}</span>
                                                        <span class="indicator-progress">{{ __('common.processing') }}...
                                                        <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!--end::New Customer Form-->
                            </div>
                        </div>
                        <!--end::Customer Information-->

                        <!--begin::Products-->
                        <div class="row mb-8">
                            <div class="col-xl-3">
                                <div class="fs-6 fw-bold mt-2 mb-3">Sản phẩm</div>
                            </div>
                            <div class="col-xl-9">
                                <div class="row g-9 mb-5">
                                    <div class="col-md-12 fv-row">
                                        <label class="fs-6 fw-bold mb-2">Tìm kiếm sản phẩm</label>
                                        <select id="product_search" class="form-select form-select-solid" data-control="select2" data-placeholder="Tìm kiếm sản phẩm để thêm vào đơn hàng...">
                                            <option value="">Tìm kiếm sản phẩm...</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <!--begin::Order Items Table-->
                                <div class="table-responsive">
                                    <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4" id="order_items_table">
                                        <thead>
                                            <tr class="fw-bolder text-muted">
                                                <th class="min-w-200px">Sản phẩm</th>
                                                <th class="min-w-100px text-center">Số lượng</th>
                                                <th class="min-w-100px text-end">Đơn giá</th>
                                                <th class="min-w-100px text-end">Thành tiền</th>
                                                <th class="min-w-80px text-center">Tồn kho</th>
                                                <th class="min-w-80px text-center">Thao tác</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td colspan="6" class="text-center">Chưa có sản phẩm nào</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <!--end::Order Items Table-->
                            </div>
                        </div>
                        <!--end::Products-->

                        <!--begin::Order Summary-->
                        <div class="row mb-8">
                            <div class="col-xl-3">
                                <div class="fs-6 fw-bold mt-2 mb-3">Tổng kết đơn hàng</div>
                            </div>
                            <div class="col-xl-9">
                                <div class="row g-9">
                                    <div class="col-md-6 fv-row">
                                        <label class="fs-6 fw-bold mb-2">Giảm giá</label>
                                        <input type="number" class="form-control form-control-solid" name="discount_amount" placeholder="0" min="0" step="1000" value="0" />
                                    </div>
                                    <div class="col-md-6 fv-row">
                                        <label class="fs-6 fw-bold mb-2">Phí vận chuyển</label>
                                        <input type="number" class="form-control form-control-solid" name="shipping_fee" placeholder="0" min="0" step="1000" value="0" />
                                    </div>
                                    <div class="col-md-6 fv-row">
                                        <label class="fs-6 fw-bold mb-2">Thuế</label>
                                        <input type="number" class="form-control form-control-solid" name="tax_amount" placeholder="0" min="0" step="1000" value="0" />
                                    </div>
                                    <div class="col-md-6 fv-row">
                                        <label class="fs-6 fw-bold mb-2">Ghi chú</label>
                                        <textarea class="form-control form-control-solid" name="notes" rows="3" placeholder="Ghi chú đơn hàng..."></textarea>
                                    </div>
                                </div>
                                
                                <!--begin::Order Total-->
                                <div class="separator separator-dashed my-6"></div>
                                <div class="d-flex flex-stack">
                                    <div class="me-5">
                                        <label class="fs-6 fw-bold">Tạm tính:</label>
                                        <div class="fs-7 fw-bold text-gray-400">Tổng tiền sản phẩm</div>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <span class="fs-6 fw-bolder" id="subtotal_display">0 ₫</span>
                                    </div>
                                </div>
                                <div class="separator separator-dashed my-3"></div>
                                <div class="d-flex flex-stack">
                                    <div class="me-5">
                                        <label class="fs-6 fw-bold">Tổng cộng:</label>
                                        <div class="fs-7 fw-bold text-gray-400">Tổng tiền phải thanh toán</div>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <span class="fs-2 fw-bolder text-primary" id="total_display">0 ₫</span>
                                    </div>
                                </div>
                                <!--end::Order Total-->
                            </div>
                        </div>
                        <!--end::Order Summary-->

                        <!--begin::Hidden Fields-->
                        <input type="hidden" name="subtotal_amount" value="0" />
                        <input type="hidden" name="final_amount" value="0" />
                        <input type="hidden" name="total_quantity" value="0" />
                        <!--end::Hidden Fields-->

                        <!--begin::Actions-->
                        <div class="card-footer d-flex justify-content-end py-6 px-9">
                            <button type="button" class="btn btn-light btn-active-light-primary me-2" data-kt-order-action="cancel">Hủy</button>
                            <button type="submit" class="btn btn-primary" data-kt-order-action="submit">
                                <span class="indicator-label">Tạo đơn hàng</span>
                                <span class="indicator-progress">Đang xử lý...
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                            </button>
                        </div>
                        <!--end::Actions-->
                    </form>
                </div>
                <!--end::Body-->
            </div>
            <!--end::Card-->
        </div>
        <!--end::Col-->
    </div>
    <!--end::Row-->
@endsection

@section('vendor-script')
    <script src="{{ asset('admin-assets/assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
@endsection

@section('scripts')
    <script src="{{ asset('admin-assets/assets/js/custom/apps/orders/list/add.js') }}"></script>
    <script src="{{ asset('admin-assets/assets/js/debug-new-customer.js') }}"></script>
@endsection
