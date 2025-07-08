@extends('admin.index')
@section('page-header', __('reports.overview'))
@section('page-sub_header', __('reports.comprehensive_reports'))

@section('style')
    <link href="{{ asset('admin-assets/assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
    <!--begin::Row-->
    <div class="row g-5 g-xl-8">
        <!--begin::Col-->
        <div class="col-xl-3">
            <!--begin::Statistics Widget 5-->
            <div class="card bg-body hoverable card-xl-stretch mb-xl-8">
                <!--begin::Body-->
                <div class="card-body">
                    <i class="ki-duotone ki-chart-simple text-primary fs-2x ms-n1">
                        <span class="path1"></span>
                        <span class="path2"></span>
                        <span class="path3"></span>
                        <span class="path4"></span>
                    </i>
                    <div class="text-gray-900 fw-bold fs-2 mb-2 mt-5" id="total_revenue">0₫</div>
                    <div class="fw-semibold text-gray-400">{{ __('reports.total_revenue') }}</div>
                </div>
                <!--end::Body-->
            </div>
            <!--end::Statistics Widget 5-->
        </div>
        <!--end::Col-->
        <!--begin::Col-->
        <div class="col-xl-3">
            <!--begin::Statistics Widget 5-->
            <div class="card bg-body hoverable card-xl-stretch mb-xl-8">
                <!--begin::Body-->
                <div class="card-body">
                    <i class="ki-duotone ki-basket text-success fs-2x ms-n1">
                        <span class="path1"></span>
                        <span class="path2"></span>
                        <span class="path3"></span>
                        <span class="path4"></span>
                    </i>
                    <div class="text-gray-900 fw-bold fs-2 mb-2 mt-5" id="total_orders">0</div>
                    <div class="fw-semibold text-gray-400">{{ __('reports.total_orders') }}</div>
                </div>
                <!--end::Body-->
            </div>
            <!--end::Statistics Widget 5-->
        </div>
        <!--end::Col-->
        <!--begin::Col-->
        <div class="col-xl-3">
            <!--begin::Statistics Widget 5-->
            <div class="card bg-body hoverable card-xl-stretch mb-xl-8">
                <!--begin::Body-->
                <div class="card-body">
                    <i class="ki-duotone ki-profile-user text-warning fs-2x ms-n1">
                        <span class="path1"></span>
                        <span class="path2"></span>
                        <span class="path3"></span>
                        <span class="path4"></span>
                    </i>
                    <div class="text-gray-900 fw-bold fs-2 mb-2 mt-5" id="total_customers">0</div>
                    <div class="fw-semibold text-gray-400">{{ __('reports.total_customers') }}</div>
                </div>
                <!--end::Body-->
            </div>
            <!--end::Statistics Widget 5-->
        </div>
        <!--end::Col-->
        <!--begin::Col-->
        <div class="col-xl-3">
            <!--begin::Statistics Widget 5-->
            <div class="card bg-body hoverable card-xl-stretch mb-xl-8">
                <!--begin::Body-->
                <div class="card-body">
                    <i class="ki-duotone ki-element-11 text-danger fs-2x ms-n1">
                        <span class="path1"></span>
                        <span class="path2"></span>
                        <span class="path3"></span>
                        <span class="path4"></span>
                    </i>
                    <div class="text-gray-900 fw-bold fs-2 mb-2 mt-5" id="total_products">0</div>
                    <div class="fw-semibold text-gray-400">{{ __('reports.total_products') }}</div>
                </div>
                <!--end::Body-->
            </div>
            <!--end::Statistics Widget 5-->
        </div>
        <!--end::Col-->
    </div>
    <!--end::Row-->

    <!--begin::Row-->
    <div class="row g-5 g-xl-8">
        <!--begin::Col-->
        <div class="col-xl-6">
            <!--begin::Charts Widget 1-->
            <div class="card card-xl-stretch mb-xl-8">
                <!--begin::Header-->
                <div class="card-header border-0 pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold fs-3 mb-1">{{ __('reports.revenue_chart') }}</span>
                        <span class="text-muted fw-semibold fs-7">{{ __('reports.monthly_revenue_comparison') }}</span>
                    </h3>
                    <div class="card-toolbar">
                        <select class="form-select form-select-sm" id="revenue_chart_period" data-control="select2" data-hide-search="true">
                            <option value="7">{{ __('reports.last_7_days') }}</option>
                            <option value="30" selected>{{ __('reports.last_30_days') }}</option>
                            <option value="90">{{ __('reports.last_3_months') }}</option>
                            <option value="365">{{ __('reports.last_year') }}</option>
                        </select>
                    </div>
                </div>
                <!--end::Header-->
                <!--begin::Body-->
                <div class="card-body">
                    <!--begin::Chart-->
                    <div id="kt_charts_widget_1_chart" style="height: 350px"></div>
                    <!--end::Chart-->
                </div>
                <!--end::Body-->
            </div>
            <!--end::Charts Widget 1-->
        </div>
        <!--end::Col-->
        <!--begin::Col-->
        <div class="col-xl-6">
            <!--begin::Charts Widget 2-->
            <div class="card card-xl-stretch mb-xl-8">
                <!--begin::Header-->
                <div class="card-header border-0 pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold fs-3 mb-1">{{ __('reports.orders_chart') }}</span>
                        <span class="text-muted fw-semibold fs-7">{{ __('reports.orders_by_status') }}</span>
                    </h3>
                </div>
                <!--end::Header-->
                <!--begin::Body-->
                <div class="card-body">
                    <!--begin::Chart-->
                    <div id="kt_charts_widget_2_chart" style="height: 350px"></div>
                    <!--end::Chart-->
                </div>
                <!--end::Body-->
            </div>
            <!--end::Charts Widget 2-->
        </div>
        <!--end::Col-->
    </div>
    <!--end::Row-->

    <!--begin::Row-->
    <div class="row g-5 g-xl-8">
        <!--begin::Col-->
        <div class="col-xl-8">
            <!--begin::Tables Widget 3-->
            <div class="card card-xl-stretch mb-5 mb-xl-8">
                <!--begin::Header-->
                <div class="card-header border-0 pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold fs-3 mb-1">{{ __('reports.top_selling_products') }}</span>
                        <span class="text-muted mt-1 fw-semibold fs-7">{{ __('reports.best_performing_products') }}</span>
                    </h3>
                    <div class="card-toolbar">
                        <select class="form-select form-select-sm" id="top_products_period" data-control="select2" data-hide-search="true">
                            <option value="today">{{ __('reports.today') }}</option>
                            <option value="week">{{ __('reports.this_week') }}</option>
                            <option value="month" selected>{{ __('reports.this_month') }}</option>
                            <option value="year">{{ __('reports.this_year') }}</option>
                        </select>
                    </div>
                </div>
                <!--end::Header-->
                <!--begin::Body-->
                <div class="card-body py-3">
                    <!--begin::Table container-->
                    <div class="table-responsive">
                        <!--begin::Table-->
                        <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4" id="top_products_table">
                            <!--begin::Table head-->
                            <thead>
                                <tr class="fw-bold text-muted">
                                    <th class="min-w-150px">{{ __('reports.product') }}</th>
                                    <th class="min-w-140px">{{ __('reports.category') }}</th>
                                    <th class="min-w-120px">{{ __('reports.sold_quantity') }}</th>
                                    <th class="min-w-120px">{{ __('reports.revenue') }}</th>
                                    <th class="min-w-100px text-end">{{ __('reports.growth') }}</th>
                                </tr>
                            </thead>
                            <!--end::Table head-->
                            <!--begin::Table body-->
                            <tbody id="top_products_tbody">
                                <!-- Data will be loaded via AJAX -->
                            </tbody>
                            <!--end::Table body-->
                        </table>
                        <!--end::Table-->
                    </div>
                    <!--end::Table container-->
                </div>
                <!--end::Body-->
            </div>
            <!--end::Tables Widget 3-->
        </div>
        <!--end::Col-->
        <!--begin::Col-->
        <div class="col-xl-4">
            <!--begin::List Widget 4-->
            <div class="card card-xl-stretch mb-5 mb-xl-8">
                <!--begin::Header-->
                <div class="card-header align-items-center border-0 mt-4">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="fw-bold mb-2 text-dark">{{ __('reports.recent_activities') }}</span>
                        <span class="text-muted fw-semibold fs-7">{{ __('reports.latest_system_activities') }}</span>
                    </h3>
                </div>
                <!--end::Header-->
                <!--begin::Body-->
                <div class="card-body pt-5">
                    <!--begin::Timeline-->
                    <div class="timeline-label" id="recent_activities">
                        <!-- Activities will be loaded via AJAX -->
                    </div>
                    <!--end::Timeline-->
                </div>
                <!--end::Body-->
            </div>
            <!--end::List Widget 4-->
        </div>
        <!--end::Col-->
    </div>
    <!--end::Row-->

    <!--begin::Row-->
    <div class="row g-5 g-xl-8">
        <!--begin::Col-->
        <div class="col-xl-12">
            <!--begin::Tables Widget 5-->
            <div class="card card-xl-stretch mb-5 mb-xl-8">
                <!--begin::Header-->
                <div class="card-header align-items-center border-0 mt-4">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="fw-bold mb-2 text-dark">{{ __('reports.revenue_comparison') }}</span>
                        <span class="text-muted fw-semibold fs-7">{{ __('reports.compare_revenue_periods') }}</span>
                    </h3>
                    <div class="card-toolbar">
                        <div class="d-flex align-items-center">
                            <select class="form-select form-select-sm me-3" id="comparison_period" data-control="select2" data-hide-search="true">
                                <option value="daily">{{ __('reports.daily') }}</option>
                                <option value="weekly">{{ __('reports.weekly') }}</option>
                                <option value="monthly" selected>{{ __('reports.monthly') }}</option>
                                <option value="yearly">{{ __('reports.yearly') }}</option>
                            </select>
                            <button type="button" class="btn btn-sm btn-primary" id="export_comparison">
                                <i class="ki-duotone ki-exit-down fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                                {{ __('reports.export') }}
                            </button>
                        </div>
                    </div>
                </div>
                <!--end::Header-->
                <!--begin::Body-->
                <div class="card-body py-3">
                    <!--begin::Chart-->
                    <div id="kt_charts_widget_3_chart" style="height: 400px"></div>
                    <!--end::Chart-->
                </div>
                <!--end::Body-->
            </div>
            <!--end::Tables Widget 5-->
        </div>
        <!--end::Col-->
    </div>
    <!--end::Row-->
@endsection

@section('vendor-script')
    <script src="{{ asset('admin-assets/assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
@endsection

@section('scripts')
    <script src="{{ asset('admin-assets/assets/js/custom/apps/reports/overview.js') }}"></script>
@endsection
