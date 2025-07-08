@extends('admin.index')
@section('page-header', __('product.product_detail'))
@section('page-sub_header', $product->product_name)

@section('content')
<div class="d-flex flex-column flex-lg-row">
    <!--begin::Sidebar-->
    <div class="flex-column flex-lg-row-auto w-lg-250px w-xl-350px mb-10">
        <!--begin::Card-->
        <div class="card mb-5 mb-xl-8">
            <!--begin::Card body-->
            <div class="card-body">
                <!--begin::Summary-->
                <!--begin::User Info-->
                <div class="d-flex flex-center flex-column py-5">
                    <!--begin::Avatar-->
                    <div class="symbol symbol-100px symbol-circle mb-7">
                        @if($product->product_thumbnail)
                            <img src="{{ asset($product->product_thumbnail) }}" alt="{{ $product->product_name }}" />
                        @else
                            <div class="symbol-label fs-3 bg-light-primary text-primary">
                                {{ strtoupper(substr($product->product_name, 0, 1)) }}
                            </div>
                        @endif
                    </div>
                    <!--end::Avatar-->
                    <!--begin::Name-->
                    <a href="#" class="fs-3 text-gray-800 text-hover-primary fw-bold mb-3">{{ $product->product_name }}</a>
                    <!--end::Name-->
                    <!--begin::Position-->
                    <div class="mb-9">
                        <!--begin::Badge-->
                        @if($product->product_status === 'publish')
                            <div class="badge badge-lg badge-light-success d-inline">{{ __('product.published') }}</div>
                        @elseif($product->product_status === 'draft')
                            <div class="badge badge-lg badge-light-warning d-inline">{{ __('product.draft') }}</div>
                        @else
                            <div class="badge badge-lg badge-light-danger d-inline">{{ __('product.inactive') }}</div>
                        @endif
                        <!--end::Badge-->
                        @if($product->product_feature)
                            <div class="badge badge-lg badge-light-primary d-inline ms-2">{{ __('product.featured') }}</div>
                        @endif
                    </div>
                    <!--end::Position-->
                </div>
                <!--end::User Info-->
                <!--end::Summary-->

                <!--begin::Details toggle-->
                <div class="d-flex flex-stack fs-4 py-3">
                    <div class="fw-bold rotate collapsible" data-bs-toggle="collapse" href="#kt_user_view_details" role="button" aria-expanded="false" aria-controls="kt_user_view_details">{{ __('product.details') }}
                    <span class="ms-2 rotate-180">
                        <i class="ki-duotone ki-down fs-3"></i>
                    </span></div>
                </div>
                <!--end::Details toggle-->
                <div class="separator"></div>
                <!--begin::Details content-->
                <div id="kt_user_view_details" class="collapse show">
                    <div class="pb-5 fs-6">
                        <!--begin::Details item-->
                        <div class="fw-bold mt-5">{{ __('product.sku') }}</div>
                        <div class="text-gray-600">{{ $product->sku }}</div>
                        <!--begin::Details item-->
                        <!--begin::Details item-->
                        <div class="fw-bold mt-5">{{ __('product.barcode') }}</div>
                        <div class="text-gray-600">{{ $product->barcode ?: __('common.not_set') }}</div>
                        <!--begin::Details item-->
                        <!--begin::Details item-->
                        <div class="fw-bold mt-5">{{ __('product.brand') }}</div>
                        <div class="text-gray-600">{{ $product->brand ?: __('common.not_set') }}</div>
                        <!--begin::Details item-->
                        <!--begin::Details item-->
                        <div class="fw-bold mt-5">{{ __('product.product_type') }}</div>
                        <div class="text-gray-600">{{ __('product.' . $product->product_type) }}</div>
                        <!--begin::Details item-->
                        <!--begin::Details item-->
                        <div class="fw-bold mt-5">{{ __('product.weight') }}</div>
                        <div class="text-gray-600">{{ $product->weight ? $product->weight . ' kg' : __('common.not_set') }}</div>
                        <!--begin::Details item-->
                        <!--begin::Details item-->
                        <div class="fw-bold mt-5">{{ __('product.location') }}</div>
                        <div class="text-gray-600">{{ $product->location ?: __('common.not_set') }}</div>
                        <!--begin::Details item-->
                        <!--begin::Details item-->
                        <div class="fw-bold mt-5">{{ __('product.created_at') }}</div>
                        <div class="text-gray-600">{{ $product->created_at->format('d/m/Y H:i') }}</div>
                        <!--begin::Details item-->
                        <!--begin::Details item-->
                        <div class="fw-bold mt-5">{{ __('product.updated_at') }}</div>
                        <div class="text-gray-600">{{ $product->updated_at->format('d/m/Y H:i') }}</div>
                        <!--begin::Details item-->
                    </div>
                </div>
                <!--end::Details content-->
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Card-->

        <!--begin::Connected Accounts-->
        <div class="card mb-5 mb-xl-8">
            <!--begin::Card header-->
            <div class="card-header border-0">
                <div class="card-title">
                    <h3 class="fw-bold m-0">{{ __('product.quick_actions') }}</h3>
                </div>
            </div>
            <!--end::Card header-->
            <!--begin::Card body-->
            <div class="card-body pt-2">
                <!--begin::Notice-->
                <div class="notice d-flex bg-light-primary rounded border-primary border border-dashed mb-9 p-6">
                    <!--begin::Icon-->
                    <i class="ki-duotone ki-design-1 fs-2tx text-primary me-4"></i>
                    <!--end::Icon-->
                    <!--begin::Wrapper-->
                    <div class="d-flex flex-stack flex-grow-1">
                        <!--begin::Content-->
                        <div class="fw-semibold">
                            <div class="fs-6 text-gray-700">{{ __('product.quick_actions_description') }}</div>
                        </div>
                        <!--end::Content-->
                    </div>
                    <!--end::Wrapper-->
                </div>
                <!--end::Notice-->

                <!--begin::Action buttons-->
                <div class="d-flex flex-column gap-3">
                    <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-light-primary btn-sm">
                        <i class="ki-duotone ki-pencil fs-3">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        {{ __('product.edit_product') }}
                    </a>
                    
                    <button type="button" class="btn btn-light-info btn-sm" data-bs-toggle="modal" data-bs-target="#kt_modal_duplicate_product">
                        <i class="ki-duotone ki-copy fs-3">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        {{ __('product.duplicate_product') }}
                    </button>
                    
                    <button type="button" class="btn btn-light-warning btn-sm" data-bs-toggle="modal" data-bs-target="#kt_modal_adjust_stock">
                        <i class="ki-duotone ki-package fs-3">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                        </i>
                        {{ __('product.adjust_stock') }}
                    </button>
                    
                    <button type="button" class="btn btn-light-danger btn-sm" data-bs-toggle="modal" data-bs-target="#kt_modal_delete_product">
                        <i class="ki-duotone ki-trash fs-3">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                            <span class="path4"></span>
                            <span class="path5"></span>
                        </i>
                        {{ __('product.delete_product') }}
                    </button>
                </div>
                <!--end::Action buttons-->
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Connected Accounts-->
    </div>
    <!--end::Sidebar-->

    <!--begin::Content-->
    <div class="flex-lg-row-fluid ms-lg-15">
        <!--begin:::Tabs-->
        <ul class="nav nav-custom nav-tabs nav-line-tabs nav-line-tabs-2x border-0 fs-4 fw-semibold mb-8">
            <!--begin:::Tab item-->
            <li class="nav-item">
                <a class="nav-link text-active-primary pb-4 active" data-bs-toggle="tab" href="#kt_user_view_overview_tab">{{ __('product.overview') }}</a>
            </li>
            <!--end:::Tab item-->
            <!--begin:::Tab item-->
            <li class="nav-item">
                <a class="nav-link text-active-primary pb-4" data-bs-toggle="tab" href="#kt_user_view_inventory_tab">{{ __('product.inventory') }}</a>
            </li>
            <!--end:::Tab item-->
            <!--begin:::Tab item-->
            <li class="nav-item">
                <a class="nav-link text-active-primary pb-4" data-bs-toggle="tab" href="#kt_user_view_pricing_tab">{{ __('product.pricing') }}</a>
            </li>
            <!--end:::Tab item-->
            <!--begin:::Tab item-->
            <li class="nav-item">
                <a class="nav-link text-active-primary pb-4" data-bs-toggle="tab" href="#kt_user_view_history_tab">{{ __('product.history') }}</a>
            </li>
            <!--end:::Tab item-->
            <!--begin:::Tab item-->
            <li class="nav-item">
                <a class="nav-link text-active-primary pb-4" data-bs-toggle="tab" href="#kt_user_view_shopee_tab">
                    <i class="ki-duotone ki-shop fs-2 me-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    {{ __('Shopee') }}
                </a>
            </li>
            <!--end:::Tab item-->
        </ul>
        <!--end:::Tabs-->

        <!--begin:::Tab content-->
        <div class="tab-content" id="myTabContent">
            <!--begin:::Tab pane-->
            <div class="tab-pane fade show active" id="kt_user_view_overview_tab" role="tabpanel">
                @include('admin.products.partials.overview', ['product' => $product])
            </div>
            <!--end:::Tab pane-->
            <!--begin:::Tab pane-->
            <div class="tab-pane fade" id="kt_user_view_inventory_tab" role="tabpanel">
                @include('admin.products.partials.inventory', ['product' => $product])
            </div>
            <!--end:::Tab pane-->
            <!--begin:::Tab pane-->
            <div class="tab-pane fade" id="kt_user_view_pricing_tab" role="tabpanel">
                @include('admin.products.partials.pricing', ['product' => $product])
            </div>
            <!--end:::Tab pane-->
            <!--begin:::Tab pane-->
            <div class="tab-pane fade" id="kt_user_view_history_tab" role="tabpanel">
                @include('admin.products.partials.history', ['product' => $product])
            </div>
            <!--end:::Tab pane-->
            <!--begin:::Tab pane-->
            <div class="tab-pane fade" id="kt_user_view_shopee_tab" role="tabpanel">
                @include('admin.products.partials.shopee', ['product' => $product])
            </div>
            <!--end:::Tab pane-->
        </div>
        <!--end:::Tab content-->
    </div>
    <!--end::Content-->
</div>

@include('admin.products.partials.modals', ['product' => $product])
@endsection

@section('scripts')
    <script src="{{ asset('admin-assets/assets/js/custom/apps/products/detail.js') }}"></script>
@endsection
