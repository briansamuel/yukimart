@extends('admin.layouts.app')

@section('title', 'Chi tiết hóa đơn #' . $invoice->invoice_number)

@section('content')
<!--begin::Content-->
<div id="kt_app_content" class="app-content flex-column-fluid">
    <!--begin::Content container-->
    <div id="kt_app_content_container" class="app-container container-xxl">
        <!--begin::Invoice-->
        <div class="card">
            <!--begin::Body-->
            <div class="card-body py-20">
                <!--begin::Wrapper-->
                <div class="mw-lg-950px mx-auto w-100">
                    <!--begin::Header-->
                    <div class="d-flex justify-content-between flex-column flex-sm-row mb-19">
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
                                        @if($invoice->payment_method === 'cash')
                                            Tiền mặt
                                        @elseif($invoice->payment_method === 'transfer')
                                            Chuyển khoản
                                        @elseif($invoice->payment_method === 'card')
                                            Thẻ
                                        @else
                                            Khác
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
                                            <div class="text-end fw-bold fs-6 text-gray-800">{{ number_format($invoice->amount_paid, 0, ',', '.') }}đ</div>
                                        </div>
                                        <!--end::Item-->
                                        
                                        <!--begin::Item-->
                                        <div class="d-flex flex-stack">
                                            <div class="fw-semibold pe-10 text-gray-600 fs-7">Còn lại:</div>
                                            <div class="text-end fw-bold fs-6 text-gray-800">{{ number_format($invoice->total_amount - $invoice->amount_paid, 0, ',', '.') }}đ</div>
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
                    </div>
                    <!--end::Body-->
                    
                    <!-- begin::Footer-->
                    <div class="d-flex flex-stack flex-wrap mt-lg-20 pt-13">
                        <!-- begin::Actions-->
                        <div class="my-1 me-5">
                            <button type="button" class="btn btn-success my-1 me-12" onclick="window.print();">
                                <i class="fas fa-print"></i>In hóa đơn
                            </button>
                            <a href="{{ route('invoice.list') }}" class="btn btn-light-primary my-1">
                                <i class="fas fa-arrow-left"></i>Quay lại danh sách
                            </a>
                        </div>
                        <!-- end::Actions-->
                        
                        <!-- begin::Action-->
                        <div class="my-1">
                            <span class="fw-semibold text-muted fs-7">Cảm ơn quý khách đã mua hàng!</span>
                        </div>
                        <!-- end::Action-->
                    </div>
                    <!-- end::Footer-->
                </div>
                <!--end::Wrapper-->
            </div>
            <!--end::Body-->
        </div>
        <!--end::Invoice-->
    </div>
    <!--end::Content container-->
</div>
<!--end::Content-->
@endsection

@section('styles')
<style>
@media print {
    .btn, .app-header, .app-sidebar, .app-footer {
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
}
</style>
@endsection
