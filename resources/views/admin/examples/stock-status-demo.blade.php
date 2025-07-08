@extends('admin.index')
@section('page-header', 'Stock Status Info Demo')
@section('page-sub_header', 'Examples of getStockStatusInfo() function usage')

@section('content')
<div class="row g-6 g-xl-9">
    <!--begin::Col-->
    <div class="col-md-6 col-xl-4">
        <!--begin::Card-->
        <div class="card">
            <!--begin::Card header-->
            <div class="card-header border-0 pt-6">
                <!--begin::Card title-->
                <div class="card-title">
                    <h3 class="fw-bold m-0">Stock Status Examples</h3>
                </div>
                <!--end::Card title-->
            </div>
            <!--end::Card header-->
            <!--begin::Card body-->
            <div class="card-body pt-0">
                @php
                    // Create demo product for testing
                    $demoProduct = new \App\Models\Product();
                    $demoProduct->product_name = 'Demo Product';
                    $demoProduct->sku = 'DEMO-001';
                    
                    $scenarios = [
                        ['stock' => 0, 'reorder' => 10, 'name' => 'Out of Stock'],
                        ['stock' => 5, 'reorder' => 10, 'name' => 'Low Stock'],
                        ['stock' => 15, 'reorder' => 10, 'name' => 'Medium Stock'],
                        ['stock' => 50, 'reorder' => 10, 'name' => 'Good Stock'],
                    ];
                @endphp
                
                @foreach($scenarios as $scenario)
                    @php
                        $stockInfo = $demoProduct->getStockStatusInfo($scenario['stock'], $scenario['reorder']);
                    @endphp
                    
                    <!--begin::Item-->
                    <div class="d-flex align-items-center mb-6">
                        <!--begin::Symbol-->
                        <div class="symbol symbol-45px me-5">
                            <div class="symbol-label bg-light-{{ $stockInfo['class'] }} text-{{ $stockInfo['class'] }}">
                                <i class="ki-duotone ki-{{ $stockInfo['icon'] }} fs-1">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </div>
                        </div>
                        <!--end::Symbol-->
                        <!--begin::Description-->
                        <div class="d-flex align-items-center flex-wrap w-100">
                            <!--begin::Title-->
                            <div class="mb-1 pe-3 flex-grow-1">
                                <span class="fs-5 text-gray-800 text-hover-primary fw-bold">{{ $scenario['name'] }}</span>
                                <div class="text-gray-400 fw-semibold fs-7">
                                    Stock: {{ $scenario['stock'] }}, Reorder: {{ $scenario['reorder'] }}
                                </div>
                            </div>
                            <!--end::Title-->
                            <!--begin::Label-->
                            <div class="d-flex flex-column align-items-end">
                                {!! $stockInfo['badge_html'] !!}
                                <div class="text-gray-400 fw-semibold fs-7 mt-1">
                                    {{ $stockInfo['urgency'] }} urgency
                                </div>
                            </div>
                            <!--end::Label-->
                        </div>
                        <!--end::Description-->
                    </div>
                    <!--end::Item-->
                    
                    <!--begin::Progress-->
                    <div class="mb-6">
                        {!! $stockInfo['progress_html'] !!}
                        <div class="d-flex justify-content-between mt-2">
                            <span class="text-muted fs-7">{{ $stockInfo['percentage'] }}% of max stock</span>
                            <span class="text-muted fs-7">{{ $stockInfo['days_until_out_of_stock'] }} days left</span>
                        </div>
                    </div>
                    <!--end::Progress-->
                    
                    @if($stockInfo['reorder_suggestion']['should_reorder'])
                        <!--begin::Alert-->
                        <div class="alert alert-{{ $stockInfo['class'] }} d-flex align-items-center p-5 mb-6">
                            <i class="ki-duotone ki-shield-tick fs-2hx text-{{ $stockInfo['class'] }} me-4">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            <div class="d-flex flex-column">
                                <h4 class="mb-1 text-{{ $stockInfo['class'] }}">Reorder Needed</h4>
                                <span>{{ $stockInfo['reorder_suggestion']['message'] }}</span>
                            </div>
                        </div>
                        <!--end::Alert-->
                    @endif
                    
                    <div class="separator my-6"></div>
                @endforeach
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Card-->
    </div>
    <!--end::Col-->
    
    <!--begin::Col-->
    <div class="col-md-6 col-xl-4">
        <!--begin::Card-->
        <div class="card">
            <!--begin::Card header-->
            <div class="card-header border-0 pt-6">
                <!--begin::Card title-->
                <div class="card-title">
                    <h3 class="fw-bold m-0">Real Products</h3>
                </div>
                <!--end::Card title-->
            </div>
            <!--end::Card header-->
            <!--begin::Card body-->
            <div class="card-body pt-0">
                @php
                    $realProducts = \App\Models\Product::with('inventory')->limit(5)->get();
                @endphp
                
                @forelse($realProducts as $product)
                    @php
                        $stockInfo = $product->getStockStatusInfo();
                    @endphp
                    
                    <!--begin::Item-->
                    <div class="d-flex align-items-center mb-6">
                        <!--begin::Symbol-->
                        <div class="symbol symbol-45px me-5">
                            @if($product->product_thumbnail)
                                <img src="{{ asset($product->product_thumbnail) }}" alt="{{ $product->product_name }}" />
                            @else
                                <div class="symbol-label bg-light-primary text-primary fw-bold">
                                    {{ strtoupper(substr($product->product_name, 0, 1)) }}
                                </div>
                            @endif
                        </div>
                        <!--end::Symbol-->
                        <!--begin::Description-->
                        <div class="d-flex align-items-center flex-wrap w-100">
                            <!--begin::Title-->
                            <div class="mb-1 pe-3 flex-grow-1">
                                <a href="{{ route('admin.products.edit', $product->id) }}" class="fs-6 text-gray-800 text-hover-primary fw-bold">
                                    {{ Str::limit($product->product_name, 25) }}
                                </a>
                                <div class="text-gray-400 fw-semibold fs-7">
                                    SKU: {{ $product->sku }}
                                </div>
                            </div>
                            <!--end::Title-->
                            <!--begin::Label-->
                            <div class="d-flex flex-column align-items-end">
                                <span class="badge badge-light-{{ $stockInfo['class'] }}">
                                    {{ $stockInfo['label'] }}
                                </span>
                                <div class="text-gray-400 fw-semibold fs-7 mt-1">
                                    {{ $stockInfo['quantity'] }} units
                                </div>
                            </div>
                            <!--end::Label-->
                        </div>
                        <!--end::Description-->
                    </div>
                    <!--end::Item-->
                    
                    <!--begin::Progress-->
                    <div class="mb-6">
                        <div class="progress h-6px w-100">
                            <div class="progress-bar bg-{{ $stockInfo['class'] }}" role="progressbar" 
                                 style="width: {{ $stockInfo['percentage'] }}%; background-color: {{ $stockInfo['color'] }} !important;" 
                                 aria-valuenow="{{ $stockInfo['percentage'] }}" aria-valuemin="0" aria-valuemax="100">
                            </div>
                        </div>
                        <div class="d-flex justify-content-between mt-2">
                            <span class="text-muted fs-7">{{ $stockInfo['urgency'] }} priority</span>
                            <span class="text-muted fs-7">{{ $stockInfo['percentage'] }}%</span>
                        </div>
                    </div>
                    <!--end::Progress-->
                    
                    @if($stockInfo['urgency'] === 'critical' || $stockInfo['urgency'] === 'high')
                        <!--begin::Alert-->
                        <div class="alert alert-{{ $stockInfo['class'] }} p-3 mb-6">
                            <div class="d-flex align-items-center">
                                <i class="ki-duotone ki-{{ $stockInfo['icon'] }} fs-2x text-{{ $stockInfo['class'] }} me-3">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                                <span class="fs-7">{{ $stockInfo['alert_message'] }}</span>
                            </div>
                        </div>
                        <!--end::Alert-->
                    @endif
                    
                    <div class="separator my-6"></div>
                @empty
                    <div class="text-center py-10">
                        <div class="text-muted">No products found</div>
                    </div>
                @endforelse
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Card-->
    </div>
    <!--end::Col-->
    
    <!--begin::Col-->
    <div class="col-md-6 col-xl-4">
        <!--begin::Card-->
        <div class="card">
            <!--begin::Card header-->
            <div class="card-header border-0 pt-6">
                <!--begin::Card title-->
                <div class="card-title">
                    <h3 class="fw-bold m-0">Code Examples</h3>
                </div>
                <!--end::Card title-->
            </div>
            <!--end::Card header-->
            <!--begin::Card body-->
            <div class="card-body pt-0">
                <!--begin::Code example 1-->
                <div class="mb-8">
                    <h5 class="text-gray-800 fw-bold mb-3">Basic Usage</h5>
                    <div class="bg-light-primary rounded p-4">
                        <code class="text-gray-700">
                            $product = Product::find(1);<br>
                            $stockInfo = $product->getStockStatusInfo();<br><br>
                            echo $stockInfo['label']; // "Good Stock"<br>
                            echo $stockInfo['class']; // "success"<br>
                            echo $stockInfo['urgency']; // "normal"
                        </code>
                    </div>
                </div>
                <!--end::Code example 1-->
                
                <!--begin::Code example 2-->
                <div class="mb-8">
                    <h5 class="text-gray-800 fw-bold mb-3">Using Accessor</h5>
                    <div class="bg-light-info rounded p-4">
                        <code class="text-gray-700">
                            $product = Product::find(1);<br>
                            $stockStatus = $product->stock_status;<br><br>
                            echo $stockStatus['status']; // "in_stock"<br>
                            echo $stockStatus['percentage']; // 85.5
                        </code>
                    </div>
                </div>
                <!--end::Code example 2-->
                
                <!--begin::Code example 3-->
                <div class="mb-8">
                    <h5 class="text-gray-800 fw-bold mb-3">In Blade Templates</h5>
                    <div class="bg-light-success rounded p-4">
                        <code class="text-gray-700">
                            @php<br>
                            &nbsp;&nbsp;&nbsp;&nbsp;$stockInfo = $product->getStockStatusInfo();<br>
                            @endphp<br><br>
                            {!! $stockInfo['badge_html'] !!}<br>
                            {!! $stockInfo['progress_html'] !!}
                        </code>
                    </div>
                </div>
                <!--end::Code example 3-->
                
                <!--begin::Code example 4-->
                <div class="mb-8">
                    <h5 class="text-gray-800 fw-bold mb-3">Custom Parameters</h5>
                    <div class="bg-light-warning rounded p-4">
                        <code class="text-gray-700">
                            $product = Product::find(1);<br>
                            $stockInfo = $product->getStockStatusInfo(25, 10);<br><br>
                            // Test with custom stock (25) and reorder point (10)<br>
                            echo $stockInfo['status']; // "medium_stock"
                        </code>
                    </div>
                </div>
                <!--end::Code example 4-->
                
                <!--begin::Test command-->
                <div class="alert alert-primary d-flex align-items-center p-5">
                    <i class="ki-duotone ki-shield-tick fs-2hx text-primary me-4">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    <div class="d-flex flex-column">
                        <h4 class="mb-1 text-primary">Test Command</h4>
                        <span>Run: <code>php artisan test:stock-status-info</code></span>
                    </div>
                </div>
                <!--end::Test command-->
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Card-->
    </div>
    <!--end::Col-->
</div>

<!--begin::Row-->
<div class="row g-6 g-xl-9 mt-6">
    <!--begin::Col-->
    <div class="col-12">
        <!--begin::Card-->
        <div class="card">
            <!--begin::Card header-->
            <div class="card-header border-0 pt-6">
                <!--begin::Card title-->
                <div class="card-title">
                    <h3 class="fw-bold m-0">Stock Status Summary</h3>
                </div>
                <!--end::Card title-->
            </div>
            <!--end::Card header-->
            <!--begin::Card body-->
            <div class="card-body pt-0">
                @php
                    $allProducts = \App\Models\Product::with('inventory')->get();
                    $statusSummary = $allProducts->groupBy(function($product) {
                        return $product->getStockStatusInfo()['status'];
                    });
                @endphp
                
                <div class="row g-6 g-xl-9">
                    @foreach(['out_of_stock', 'low_stock', 'medium_stock', 'in_stock'] as $status)
                        @php
                            $products = $statusSummary[$status] ?? collect();
                            $count = $products->count();
                            $percentage = $allProducts->count() > 0 ? round(($count / $allProducts->count()) * 100, 1) : 0;
                            
                            $statusConfig = [
                                'out_of_stock' => ['label' => 'Out of Stock', 'class' => 'danger', 'icon' => 'cross-circle'],
                                'low_stock' => ['label' => 'Low Stock', 'class' => 'warning', 'icon' => 'warning-2'],
                                'medium_stock' => ['label' => 'Medium Stock', 'class' => 'info', 'icon' => 'information-5'],
                                'in_stock' => ['label' => 'Good Stock', 'class' => 'success', 'icon' => 'check-circle']
                            ];
                            
                            $config = $statusConfig[$status];
                        @endphp
                        
                        <!--begin::Col-->
                        <div class="col-md-6 col-xl-3">
                            <!--begin::Card widget-->
                            <div class="card card-flush h-md-50 mb-5 mb-xl-10">
                                <!--begin::Header-->
                                <div class="card-header pt-5">
                                    <!--begin::Title-->
                                    <div class="card-title d-flex flex-column">
                                        <!--begin::Info-->
                                        <div class="d-flex align-items-center">
                                            <!--begin::Amount-->
                                            <span class="fs-2hx fw-bold text-dark me-2 lh-1 ls-n2">{{ $count }}</span>
                                            <!--end::Amount-->
                                        </div>
                                        <!--end::Info-->
                                        <!--begin::Subtitle-->
                                        <span class="text-gray-400 pt-1 fw-semibold fs-6">{{ $config['label'] }}</span>
                                        <!--end::Subtitle-->
                                    </div>
                                    <!--end::Title-->
                                </div>
                                <!--end::Header-->
                                <!--begin::Card body-->
                                <div class="card-body pt-2 pb-4 d-flex align-items-center">
                                    <div class="d-flex flex-center me-5 pt-2">
                                        <i class="ki-duotone ki-{{ $config['icon'] }} fs-2tx text-{{ $config['class'] }}">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                    </div>
                                    <div class="d-flex flex-column content-justify-center w-100">
                                        <div class="d-flex fs-6 fw-semibold align-items-center">
                                            <div class="text-gray-500 flex-grow-1 me-4">{{ __('product.products') }}</div>
                                            <div class="fw-bolder text-gray-700 text-xxl-end">{{ $percentage }}%</div>
                                        </div>
                                    </div>
                                </div>
                                <!--end::Card body-->
                            </div>
                            <!--end::Card widget-->
                        </div>
                        <!--end::Col-->
                    @endforeach
                </div>
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Card-->
    </div>
    <!--end::Col-->
</div>
<!--end::Row-->
@endsection
