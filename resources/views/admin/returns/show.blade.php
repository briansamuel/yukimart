@extends('admin.layouts.app')

@section('title', 'Chi tiết hóa đơn #' . $invoice->invoice_number)

@section('content')
<!--begin::Content-->
<div id="kt_app_content" class="app-content flex-column-fluid">
    <!--begin::Content container-->
    <div id="kt_app_content_container" class="app-container container-xxl">
        <!--begin::Invoice Detail Card-->
        <div class="card">
            <!--begin::Header-->
            <div class="card-header border-0 pt-6">
                <div class="card-title">
                    <div class="d-flex align-items-center position-relative my-1">
                        <h3 class="fw-bold me-5">Chi tiết hóa đơn #{{ $invoice->invoice_number }}</h3>
                        <span class="badge badge-light-primary fs-7 fw-bold">{{ $invoice->status_badge }}</span>
                    </div>
                </div>
                <div class="card-toolbar">
                    <div class="d-flex justify-content-end">
                        <button type="button" class="btn btn-success me-3" onclick="window.print();">
                            <i class="fas fa-print"></i>In hóa đơn
                        </button>
                        <a href="{{ route('invoice.list') }}" class="btn btn-light-primary">
                            <i class="fas fa-arrow-left"></i>Quay lại
                        </a>
                    </div>
                </div>
            </div>
            <!--end::Header-->

            <!--begin::Body-->
            <div class="card-body py-4">
                <!--begin::Tab nav-->
                <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x mb-5 fs-6">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#kt_tab_pane_info">
                            <i class="fas fa-info-circle me-2"></i>Thông tin
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#kt_tab_pane_payment_history">
                            <i class="fas fa-history me-2"></i>Lịch sử thanh toán
                        </a>
                    </li>
                </ul>
                <!--end::Tab nav-->

                <!--begin::Tab content-->
                <div class="tab-content" id="myTabContent">
                    <!--begin::Tab pane Info-->
                    <div class="tab-pane fade show active" id="kt_tab_pane_info" role="tabpanel">
                        <!--begin::Invoice Info-->
                        <!--begin::Wrapper-->
                        <div class="mw-lg-950px mx-auto w-100">
                            <!--begin::Header-->
                            <div class="d-flex justify-content-between flex-column flex-sm-row mb-10">
                                <h4 class="fw-bolder text-gray-800 fs-2qx pe-5 pb-7">HÓA ĐƠN</h4>
                                <!--begin::Logo-->
                                <div class="text-sm-end">
                                    <a href="#" class="d-block mw-150px ms-sm-auto">
                                        <img alt="Logo" src="{{ asset('admin/media/logos/logo-1.svg') }}" class="w-100" />
                                    </a>
                                    <div class="text-sm-end fw-semibold fs-4 text-muted mt-7">
                                        <div>{{ config('app.name', 'YukiMart') }}</div>
                                        <div>Địa chỉ cửa hàng</div>
                                    </div>
                                </div>
                                <!--end::Logo-->
                            </div>
                            <!--end::Header-->
                    
                    <!--begin::Body-->
                    <div class="pb-12">
                        <!--begin::Wrapper-->
                        <div class="d-flex flex-column gap-7 gap-md-10">
                            <!--begin::Message-->
                            <div class="fw-bold fs-2">
                                Kính gửi {{ $invoice->customer ? $invoice->customer->name : 'Khách lẻ' }},
                                <span class="text-muted fs-5">Cảm ơn quý khách đã mua hàng tại cửa hàng chúng tôi.</span>
                            </div>
                            <!--end::Message-->
                            
                            <!--begin::Separator-->
                            <div class="separator"></div>
                            <!--end::Separator-->
                            
                            <!--begin::Order details-->
                            <div class="d-flex flex-column flex-sm-row gap-7 gap-md-10 fw-bold">
                                <div class="flex-root d-flex flex-column">
                                    <span class="text-muted">Mã hóa đơn</span>
                                    <span class="fs-5">{{ $invoice->invoice_number }}</span>
                                </div>
                                <div class="flex-root d-flex flex-column">
                                    <span class="text-muted">Ngày tạo</span>
                                    <span class="fs-5">{{ $invoice->created_at->format('d/m/Y') }}</span>
                                </div>
                                <div class="flex-root d-flex flex-column">
                                    <span class="text-muted">Trạng thái thanh toán</span>
                                    <span class="fs-5">
                                        @if($invoice->payment_status === 'paid')
                                            <span class="badge badge-light-success">Đã thanh toán</span>
                                        @elseif($invoice->payment_status === 'partial')
                                            <span class="badge badge-light-warning">Thanh toán một phần</span>
                                        @else
                                            <span class="badge badge-light-danger">Chưa thanh toán</span>
                                        @endif
                                    </span>
                                </div>
                                <div class="flex-root d-flex flex-column">
                                    <span class="text-muted">Phương thức thanh toán</span>
                                    <span class="fs-5">
                                        @if($invoice->payments->count() > 0)
                                            {{ $invoice->payments->first()->payment_method_display }}
                                        @else
                                            Chưa có thanh toán
                                        @endif
                                    </span>
                                </div>
                            </div>
                            <!--end::Order details-->
                            
                            <!--begin::Billing & shipping-->
                            <div class="d-flex flex-column flex-sm-row gap-7 gap-md-10 fw-bold">
                                <div class="flex-root d-flex flex-column">
                                    <span class="text-muted">Thông tin khách hàng</span>
                                    @if($invoice->customer)
                                        <span class="fs-6">{{ $invoice->customer->name }}</span>
                                        <span class="fs-7 text-muted">{{ $invoice->customer->phone }}</span>
                                        <span class="fs-7 text-muted">{{ $invoice->customer->email }}</span>
                                        <span class="fs-7 text-muted">{{ $invoice->customer->address }}</span>
                                    @else
                                        <span class="fs-6">Khách lẻ</span>
                                    @endif
                                </div>
                                <div class="flex-root d-flex flex-column">
                                    <span class="text-muted">Chi nhánh</span>
                                    <span class="fs-6">{{ $invoice->branchShop->name ?? 'N/A' }}</span>
                                    <span class="fs-7 text-muted">{{ $invoice->branchShop->address ?? '' }}</span>
                                </div>
                            </div>
                            <!--end::Billing & shipping-->
                            
                            <!--begin::Product table-->
                            <div class="d-flex justify-content-between flex-column">
                                <!--begin::Table-->
                                <div class="table-responsive border-bottom mb-9">
                                    <table class="table align-middle table-row-dashed fs-6 gy-5 mb-0">
                                        <thead>
                                            <tr class="border-bottom fs-6 fw-bold text-muted">
                                                <th class="min-w-175px pb-2">Sản phẩm</th>
                                                <th class="min-w-70px text-end pb-2">Số lượng</th>
                                                <th class="min-w-80px text-end pb-2">Đơn giá</th>
                                                <th class="min-w-100px text-end pb-2">Thành tiền</th>
                                            </tr>
                                        </thead>
                                        <tbody class="fw-semibold text-gray-600">
                                            @if($invoice->invoiceItems && $invoice->invoiceItems->count() > 0)
                                                @foreach($invoice->invoiceItems as $item)
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="ms-5">
                                                                <div class="fw-bold">{{ $item->product->product_name ?? 'N/A' }}</div>
                                                                <div class="fs-7 text-muted">SKU: {{ $item->product->sku ?? 'N/A' }}</div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="text-end">{{ number_format($item->quantity, 0, ',', '.') }}</td>
                                                    <td class="text-end">{{ number_format($item->unit_price, 0, ',', '.') }}đ</td>
                                                    <td class="text-end">{{ number_format($item->total_price, 0, ',', '.') }}đ</td>
                                                </tr>
                                                @endforeach
                                            @else
                                                <tr>
                                                    <td colspan="4" class="text-center text-muted">Không có sản phẩm nào</td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                                <!--end::Table-->
                                
                                <!--begin::Container-->
                                <div class="d-flex justify-content-end">
                                    <!--begin::Section-->
                                    <div class="mw-300px">
                                        <!--begin::Item-->
                                        <div class="d-flex flex-stack mb-3">
                                            <div class="fw-semibold pe-10 text-gray-600 fs-7">Tổng tiền hàng:</div>
                                            <div class="text-end fw-bold fs-6 text-gray-800">{{ number_format($invoice->total_amount, 0, ',', '.') }}đ</div>
                                        </div>
                                        <!--end::Item-->
                                        
                                        <!--begin::Item-->
                                        <div class="d-flex flex-stack mb-3">
                                            <div class="fw-semibold pe-10 text-gray-600 fs-7">Đã thanh toán:</div>
                                            <div class="text-end fw-bold fs-6 text-gray-800">{{ number_format($invoice->paid_amount, 0, ',', '.') }}đ</div>
                                        </div>
                                        <!--end::Item-->

                                        <!--begin::Item-->
                                        <div class="d-flex flex-stack">
                                            <div class="fw-semibold pe-10 text-gray-600 fs-7">Còn lại:</div>
                                            <div class="text-end fw-bold fs-6 text-gray-800">{{ number_format($invoice->remaining_amount, 0, ',', '.') }}đ</div>
                                        </div>
                                        <!--end::Item-->
                                    </div>
                                    <!--end::Section-->
                                </div>
                                <!--end::Container-->
                            </div>
                            <!--end::Product table-->
                        </div>
                        <!--end::Wrapper-->
                        <!--end::Invoice Info-->
                    </div>
                    <!--end::Tab pane Info-->

                    <!--begin::Tab pane Payment History-->
                    <div class="tab-pane fade" id="kt_tab_pane_payment_history" role="tabpanel">
                        <!--begin::Payment History-->
                        <div class="card">
                            <div class="card-header border-0 pt-6">
                                <div class="card-title">
                                    <h3 class="fw-bold">Lịch sử thanh toán</h3>
                                </div>
                            </div>
                            <div class="card-body py-4">
                                <!--begin::Table-->
                                <div class="table-responsive">
                                    <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_payment_history_table">
                                        <thead>
                                            <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                                <th class="min-w-125px">Mã phiếu</th>
                                                <th class="min-w-125px">Thời gian</th>
                                                <th class="min-w-125px">Người tạo</th>
                                                <th class="min-w-100px text-end">Giá trị phiếu</th>
                                                <th class="min-w-100px">Phương thức</th>
                                                <th class="min-w-100px">Trạng thái</th>
                                                <th class="min-w-100px text-end">Tiền thu/chi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="text-gray-600 fw-semibold" id="payment_history_tbody">
                                            <tr>
                                                <td colspan="7" class="text-center">
                                                    <div class="spinner-border spinner-border-sm" role="status">
                                                        <span class="visually-hidden">Loading...</span>
                                                    </div>
                                                    <span class="ms-2">Đang tải dữ liệu...</span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <!--end::Table-->
                            </div>
                        </div>
                        <!--end::Payment History-->
                    </div>
                    <!--end::Tab pane Payment History-->
                </div>
                <!--end::Tab content-->
            </div>
            <!--end::Body-->
        </div>
        <!--end::Invoice Detail Card-->
    </div>
    <!--end::Content container-->
</div>
<!--end::Content-->
@endsection

@section('styles')
<style>
@media print {
    .btn, .app-header, .app-sidebar, .app-footer, .nav-tabs {
        display: none !important;
    }

    .app-main {
        margin: 0 !important;
        padding: 0 !important;
    }

    .card {
        border: none !important;
        box-shadow: none !important;
    }

    .tab-pane {
        display: block !important;
    }

    #kt_tab_pane_payment_history {
        display: none !important;
    }
}
</style>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let paymentHistoryLoaded = false;
    const invoiceId = {{ $invoice->id }};

    // Load payment history when tab is clicked
    document.querySelector('a[href="#kt_tab_pane_payment_history"]').addEventListener('click', function() {
        if (!paymentHistoryLoaded) {
            loadPaymentHistory();
            paymentHistoryLoaded = true;
        }
    });

    function loadPaymentHistory() {
        const tbody = document.getElementById('payment_history_tbody');

        fetch(`/admin/invoices/${invoiceId}/payment-history`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.data.length === 0) {
                        tbody.innerHTML = `
                            <tr>
                                <td colspan="7" class="text-center text-muted">
                                    <i class="fas fa-info-circle me-2"></i>Chưa có lịch sử thanh toán
                                </td>
                            </tr>
                        `;
                    } else {
                        let html = '';
                        data.data.forEach(payment => {
                            html += `
                                <tr>
                                    <td>
                                        <a href="#" class="text-gray-800 text-hover-primary fw-bold">${payment.payment_number}</a>
                                    </td>
                                    <td>${payment.payment_date}</td>
                                    <td>${payment.creator_name}</td>
                                    <td class="text-end">${payment.formatted_amount} VND</td>
                                    <td>${payment.payment_method}</td>
                                    <td>${payment.status_badge}</td>
                                    <td class="text-end text-success fw-bold">${payment.formatted_amount}</td>
                                </tr>
                            `;
                        });
                        tbody.innerHTML = html;
                    }
                } else {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="7" class="text-center text-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i>Có lỗi xảy ra khi tải dữ liệu
                            </td>
                        </tr>
                    `;
                }
            })
            .catch(error => {
                console.error('Error loading payment history:', error);
                tbody.innerHTML = `
                    <tr>
                        <td colspan="7" class="text-center text-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>Có lỗi xảy ra khi tải dữ liệu
                        </td>
                    </tr>
                `;
            });
    }
});
</script>
@endsection
