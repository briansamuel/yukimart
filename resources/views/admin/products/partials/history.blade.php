<!--begin::Card-->
<div class="card mb-5 mb-xl-10">
    <!--begin::Card header-->
    <div class="card-header border-0">
        <!--begin::Card title-->
        <div class="card-title m-0">
            <h3 class="fw-bold m-0">{{ __('product.product_history') }}</h3>
        </div>
        <!--end::Card title-->
        <!--begin::Card toolbar-->
        <div class="card-toolbar">
            <button type="button" class="btn btn-sm btn-light-primary" data-bs-toggle="modal" data-bs-target="#kt_modal_view_full_history">
                <i class="ki-duotone ki-eye fs-3"></i>
                {{ __('product.view_full_history') }}
            </button>
        </div>
        <!--end::Card toolbar-->
    </div>
    <!--begin::Card header-->
    <!--begin::Card body-->
    <div class="card-body border-top p-9">
        <!--begin::Timeline-->
        <div class="timeline-label">
            <!--begin::Item-->
            <div class="timeline-item">
                <!--begin::Label-->
                <div class="timeline-label fw-bold text-gray-800 fs-6">{{ $product->created_at->format('H:i') }}</div>
                <!--end::Label-->
                <!--begin::Badge-->
                <div class="timeline-badge">
                    <i class="fa fa-genderless text-success fs-1"></i>
                </div>
                <!--end::Badge-->
                <!--begin::Text-->
                <div class="fw-muted text-muted ps-3">
                    {{ __('product.product_created') }}
                    @if($product->createdByUser)
                        {{ __('product.by') }} <strong>{{ $product->createdByUser->name }}</strong>
                    @endif
                    <br>
                    <span class="text-muted fs-7">{{ $product->created_at->format('d/m/Y H:i') }}</span>
                </div>
                <!--end::Text-->
            </div>
            <!--end::Item-->

            @if($product->updated_at != $product->created_at)
            <!--begin::Item-->
            <div class="timeline-item">
                <!--begin::Label-->
                <div class="timeline-label fw-bold text-gray-800 fs-6">{{ $product->updated_at->format('H:i') }}</div>
                <!--end::Label-->
                <!--begin::Badge-->
                <div class="timeline-badge">
                    <i class="fa fa-genderless text-warning fs-1"></i>
                </div>
                <!--end::Badge-->
                <!--begin::Text-->
                <div class="fw-muted text-muted ps-3">
                    {{ __('product.product_updated') }}
                    @if($product->updatedByUser)
                        {{ __('product.by') }} <strong>{{ $product->updatedByUser->name }}</strong>
                    @endif
                    <br>
                    <span class="text-muted fs-7">{{ $product->updated_at->format('d/m/Y H:i') }}</span>
                </div>
                <!--end::Text-->
            </div>
            <!--end::Item-->
            @endif

            @if($product->inventoryTransactions && $product->inventoryTransactions->count() > 0)
                @foreach($product->inventoryTransactions->take(5) as $transaction)
                <!--begin::Item-->
                <div class="timeline-item">
                    <!--begin::Label-->
                    <div class="timeline-label fw-bold text-gray-800 fs-6">{{ $transaction->created_at->format('H:i') }}</div>
                    <!--end::Label-->
                    <!--begin::Badge-->
                    <div class="timeline-badge">
                        @if($transaction->type === 'in')
                            <i class="fa fa-genderless text-success fs-1"></i>
                        @elseif($transaction->type === 'out')
                            <i class="fa fa-genderless text-danger fs-1"></i>
                        @else
                            <i class="fa fa-genderless text-info fs-1"></i>
                        @endif
                    </div>
                    <!--end::Badge-->
                    <!--begin::Text-->
                    <div class="fw-muted text-muted ps-3">
                        @if($transaction->type === 'in')
                            {{ __('product.stock_increased') }} {{ __('product.by') }} <strong>{{ number_format($transaction->quantity) }}</strong> {{ __('product.units') }}
                        @elseif($transaction->type === 'out')
                            {{ __('product.stock_decreased') }} {{ __('product.by') }} <strong>{{ number_format($transaction->quantity) }}</strong> {{ __('product.units') }}
                        @else
                            {{ __('product.stock_adjusted') }} {{ __('product.by') }} <strong>{{ number_format($transaction->quantity) }}</strong> {{ __('product.units') }}
                        @endif
                        @if($transaction->reference)
                            <br><span class="text-muted fs-7">{{ __('product.reference') }}: {{ $transaction->reference }}</span>
                        @endif
                        <br>
                        <span class="text-muted fs-7">{{ $transaction->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    <!--end::Text-->
                </div>
                <!--end::Item-->
                @endforeach
            @endif
        </div>
        <!--end::Timeline-->
    </div>
    <!--end::Card body-->
</div>
<!--end::Card-->

<!--begin::Card-->
<div class="card mb-5 mb-xl-10">
    <!--begin::Card header-->
    <div class="card-header border-0">
        <!--begin::Card title-->
        <div class="card-title m-0">
            <h3 class="fw-bold m-0">{{ __('product.system_information') }}</h3>
        </div>
        <!--end::Card title-->
    </div>
    <!--begin::Card header-->
    <!--begin::Card body-->
    <div class="card-body border-top p-9">
        <!--begin::Row-->
        <div class="row mb-6">
            <!--begin::Label-->
            <label class="col-lg-4 col-form-label fw-semibold fs-6">{{ __('product.product_id') }}</label>
            <!--end::Label-->
            <!--begin::Col-->
            <div class="col-lg-8 fv-row">
                <span class="fw-semibold text-gray-800 fs-6">#{{ $product->id }}</span>
            </div>
            <!--end::Col-->
        </div>
        <!--end::Row-->
        <!--begin::Row-->
        <div class="row mb-6">
            <!--begin::Label-->
            <label class="col-lg-4 col-form-label fw-semibold fs-6">{{ __('product.created_by') }}</label>
            <!--end::Label-->
            <!--begin::Col-->
            <div class="col-lg-8 fv-row">
                <span class="fw-semibold text-gray-600 fs-6">
                    @if($product->createdByUser)
                        {{ $product->createdByUser->name }}
                    @else
                        {{ __('common.unknown') }}
                    @endif
                </span>
            </div>
            <!--end::Col-->
        </div>
        <!--end::Row-->
        <!--begin::Row-->
        <div class="row mb-6">
            <!--begin::Label-->
            <label class="col-lg-4 col-form-label fw-semibold fs-6">{{ __('product.updated_by') }}</label>
            <!--end::Label-->
            <!--begin::Col-->
            <div class="col-lg-8 fv-row">
                <span class="fw-semibold text-gray-600 fs-6">
                    @if($product->updatedByUser)
                        {{ $product->updatedByUser->name }}
                    @else
                        {{ __('common.unknown') }}
                    @endif
                </span>
            </div>
            <!--end::Col-->
        </div>
        <!--end::Row-->
        <!--begin::Row-->
        <div class="row mb-6">
            <!--begin::Label-->
            <label class="col-lg-4 col-form-label fw-semibold fs-6">{{ __('product.created_at') }}</label>
            <!--end::Label-->
            <!--begin::Col-->
            <div class="col-lg-8 fv-row">
                <span class="fw-semibold text-gray-600 fs-6">{{ $product->created_at->format('d/m/Y H:i:s') }}</span>
                <div class="text-muted fs-7">{{ $product->created_at->diffForHumans() }}</div>
            </div>
            <!--end::Col-->
        </div>
        <!--end::Row-->
        <!--begin::Row-->
        <div class="row mb-6">
            <!--begin::Label-->
            <label class="col-lg-4 col-form-label fw-semibold fs-6">{{ __('product.updated_at') }}</label>
            <!--end::Label-->
            <!--begin::Col-->
            <div class="col-lg-8 fv-row">
                <span class="fw-semibold text-gray-600 fs-6">{{ $product->updated_at->format('d/m/Y H:i:s') }}</span>
                <div class="text-muted fs-7">{{ $product->updated_at->diffForHumans() }}</div>
            </div>
            <!--end::Col-->
        </div>
        <!--end::Row-->
        <!--begin::Row-->
        <div class="row mb-6">
            <!--begin::Label-->
            <label class="col-lg-4 col-form-label fw-semibold fs-6">{{ __('product.language') }}</label>
            <!--end::Label-->
            <!--begin::Col-->
            <div class="col-lg-8 fv-row">
                <span class="fw-semibold text-gray-600 fs-6">
                    @if($product->language === 'vi')
                        {{ __('common.vietnamese') }}
                    @elseif($product->language === 'en')
                        {{ __('common.english') }}
                    @else
                        {{ $product->language }}
                    @endif
                </span>
            </div>
            <!--end::Col-->
        </div>
        <!--end::Row-->
    </div>
    <!--end::Card body-->
</div>
<!--end::Card-->
