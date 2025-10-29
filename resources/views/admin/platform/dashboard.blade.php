@extends('admin.layouts.platform-app')

@section('title', 'Platform Dashboard')

@section('content')
<div class="d-flex flex-column flex-column-fluid">
    <!--begin::Toolbar-->
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
        <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                    Platform Dashboard
                </h1>
                <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('platform.dashboard') }}" class="text-muted text-hover-primary">Platform</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-400 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-muted">Dashboard</li>
                </ul>
            </div>
        </div>
    </div>
    <!--end::Toolbar-->

    <!--begin::Content-->
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-xxl">
            
            <!--begin::Row-->
            <div class="row g-5 g-xl-10 mb-5 mb-xl-10">
                <!--begin::Col-->
                <div class="col-md-6 col-lg-6 col-xl-6 col-xxl-3 mb-md-5 mb-xl-10">
                    <!--begin::Card widget 20-->
                    <div class="card card-flush bgi-no-repeat bgi-size-contain bgi-position-x-end h-md-50 mb-5 mb-xl-10" style="background-color: #F1416C;background-image:url('{{ asset('admin-assets/media/patterns/vector-1.png') }}')">
                        <div class="card-header pt-5">
                            <div class="card-title d-flex flex-column">
                                <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2">{{ $stats['total_tenants'] ?? 0 }}</span>
                                <span class="text-white opacity-75 pt-1 fw-semibold fs-6">Total Tenants</span>
                            </div>
                        </div>
                        <div class="card-body d-flex align-items-end pt-0">
                            <div class="d-flex align-items-center flex-column mt-3 w-100">
                                <div class="d-flex justify-content-between w-100 mt-auto mb-2">
                                    <span class="fw-bolder fs-6 text-white opacity-75">Active</span>
                                    <span class="fw-bold fs-6 text-white">{{ $stats['active_tenants'] ?? 0 }}</span>
                                </div>
                                <div class="h-8px mx-3 w-100 bg-white bg-opacity-50 rounded">
                                    <div class="bg-white rounded h-8px" role="progressbar" style="width: {{ $stats['total_tenants'] > 0 ? ($stats['active_tenants'] / $stats['total_tenants']) * 100 : 0 }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end::Card widget 20-->
                </div>
                <!--end::Col-->

                <!--begin::Col-->
                <div class="col-md-6 col-lg-6 col-xl-6 col-xxl-3 mb-md-5 mb-xl-10">
                    <!--begin::Card widget 17-->
                    <div class="card card-flush h-md-50 mb-5 mb-xl-10">
                        <div class="card-header pt-5">
                            <div class="card-title d-flex flex-column">
                                <div class="d-flex align-items-center">
                                    <span class="fs-4 fw-semibold text-gray-400 me-1 align-self-start">{{ number_format($stats['total_users'] ?? 0) }}</span>
                                    <span class="fs-2hx fw-bold text-dark me-2 lh-1">Users</span>
                                </div>
                                <span class="text-gray-400 pt-1 fw-semibold fs-6">Platform Users</span>
                            </div>
                        </div>
                        <div class="card-body pt-2 pb-4 d-flex flex-wrap align-items-center">
                            <div class="d-flex flex-center me-5 pt-2">
                                <div id="kt_card_widget_17_chart" style="min-width: 70px; min-height: 70px" data-kt-size="70" data-kt-line="11"></div>
                            </div>
                            <div class="d-flex flex-column content-justify-center flex-row-fluid">
                                <div class="d-flex fw-semibold align-items-center">
                                    <div class="bullet w-8px h-3px rounded-2 bg-success me-3"></div>
                                    <div class="text-gray-500 flex-1 fs-6">Active Users</div>
                                    <div class="fw-bolder text-gray-700 text-xxl-end">{{ $stats['active_users'] ?? 0 }}</div>
                                </div>
                                <div class="d-flex fw-semibold align-items-center my-3">
                                    <div class="bullet w-8px h-3px rounded-2 bg-primary me-3"></div>
                                    <div class="text-gray-500 flex-1 fs-6">Platform Admins</div>
                                    <div class="fw-bolder text-gray-700 text-xxl-end">{{ $stats['platform_admins'] ?? 0 }}</div>
                                </div>
                                <div class="d-flex fw-semibold align-items-center">
                                    <div class="bullet w-8px h-3px rounded-2 bg-info me-3"></div>
                                    <div class="text-gray-500 flex-1 fs-6">Tenant Users</div>
                                    <div class="fw-bolder text-gray-700 text-xxl-end">{{ $stats['tenant_users'] ?? 0 }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end::Card widget 17-->
                </div>
                <!--end::Col-->

                <!--begin::Col-->
                <div class="col-md-6 col-lg-6 col-xl-6 col-xxl-3 mb-md-5 mb-xl-10">
                    <!--begin::Card widget 18-->
                    <div class="card card-flush h-md-50 mb-xl-10">
                        <div class="card-header pt-5">
                            <div class="card-title d-flex flex-column">
                                <span class="fs-2hx fw-bold text-dark me-2 lh-1">{{ number_format($stats['total_products'] ?? 0) }}</span>
                                <span class="text-gray-400 pt-1 fw-semibold fs-6">Total Products</span>
                            </div>
                        </div>
                        <div class="card-body pt-2 pb-4 d-flex align-items-center">
                            <div class="d-flex flex-center me-5 pt-2">
                                <div id="kt_card_widget_18_chart" style="min-width: 70px; min-height: 70px" data-kt-size="70" data-kt-line="11"></div>
                            </div>
                            <div class="d-flex flex-column content-justify-center w-100">
                                <div class="d-flex fw-semibold align-items-center">
                                    <div class="bullet w-8px h-3px rounded-2 bg-warning me-3"></div>
                                    <div class="text-gray-500 flex-1 fs-6">Published</div>
                                    <div class="fw-bolder text-gray-700 text-xxl-end">{{ $stats['published_products'] ?? 0 }}</div>
                                </div>
                                <div class="d-flex fw-semibold align-items-center my-3">
                                    <div class="bullet w-8px h-3px rounded-2 bg-primary me-3"></div>
                                    <div class="text-gray-500 flex-1 fs-6">Draft</div>
                                    <div class="fw-bolder text-gray-700 text-xxl-end">{{ $stats['draft_products'] ?? 0 }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end::Card widget 18-->
                </div>
                <!--end::Col-->

                <!--begin::Col-->
                <div class="col-md-6 col-lg-6 col-xl-6 col-xxl-3 mb-md-5 mb-xl-10">
                    <!--begin::Card widget 19-->
                    <div class="card card-flush h-md-50 mb-xl-10">
                        <div class="card-header pt-5">
                            <div class="card-title d-flex flex-column">
                                <span class="fs-2hx fw-bold text-dark me-2 lh-1">{{ number_format($stats['total_orders'] ?? 0) }}</span>
                                <span class="text-gray-400 pt-1 fw-semibold fs-6">Total Orders</span>
                            </div>
                        </div>
                        <div class="card-body pt-2 pb-4 d-flex align-items-center">
                            <div class="d-flex flex-center me-5 pt-2">
                                <div id="kt_card_widget_19_chart" style="min-width: 70px; min-height: 70px" data-kt-size="70" data-kt-line="11"></div>
                            </div>
                            <div class="d-flex flex-column content-justify-center w-100">
                                <div class="d-flex fw-semibold align-items-center">
                                    <div class="bullet w-8px h-3px rounded-2 bg-success me-3"></div>
                                    <div class="text-gray-500 flex-1 fs-6">Completed</div>
                                    <div class="fw-bolder text-gray-700 text-xxl-end">{{ $stats['completed_orders'] ?? 0 }}</div>
                                </div>
                                <div class="d-flex fw-semibold align-items-center my-3">
                                    <div class="bullet w-8px h-3px rounded-2 bg-warning me-3"></div>
                                    <div class="text-gray-500 flex-1 fs-6">Processing</div>
                                    <div class="fw-bolder text-gray-700 text-xxl-end">{{ $stats['processing_orders'] ?? 0 }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end::Card widget 19-->
                </div>
                <!--end::Col-->
            </div>
            <!--end::Row-->

            <!--begin::Row-->
            <div class="row g-5 g-xl-10 mb-5 mb-xl-10">
                <!--begin::Col-->
                <div class="col-xl-6">
                    <!--begin::Chart widget 36-->
                    <div class="card card-flush overflow-hidden h-md-100">
                        <div class="card-header py-5">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold text-dark">Tenant Growth</span>
                                <span class="text-gray-400 mt-1 fw-semibold fs-6">Monthly tenant registration</span>
                            </h3>
                            <div class="card-toolbar">
                                <button class="btn btn-icon btn-color-gray-400 btn-active-color-primary justify-content-end" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end" data-kt-menu-overflow="true">
                                    <i class="ki-duotone ki-dots-square fs-1 text-gray-300 me-n1">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                        <span class="path3"></span>
                                        <span class="path4"></span>
                                    </i>
                                </button>
                                <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold w-100px py-4" data-kt-menu="true">
                                    <div class="menu-item px-3">
                                        <a href="#" class="menu-link px-3">View Details</a>
                                    </div>
                                    <div class="menu-item px-3">
                                        <a href="#" class="menu-link px-3">Export</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body d-flex justify-content-between flex-column pb-1 px-0">
                            <div class="px-9 mb-5">
                                <div class="d-flex mb-2">
                                    <span class="fs-4 fw-semibold text-gray-400 me-1">$</span>
                                    <span class="fs-2hx fw-bold text-gray-800 me-2 lh-1">{{ number_format($stats['monthly_revenue'] ?? 0) }}</span>
                                    <span class="fs-4 fw-semibold text-gray-400 me-1">VND</span>
                                </div>
                                <span class="fs-6 fw-semibold text-gray-400">Monthly Revenue</span>
                            </div>
                            <div id="kt_charts_widget_36" class="min-h-auto ps-4 pe-6" style="height: 300px"></div>
                        </div>
                    </div>
                    <!--end::Chart widget 36-->
                </div>
                <!--end::Col-->

                <!--begin::Col-->
                <div class="col-xl-6">
                    <!--begin::Table Widget 5-->
                    <div class="card card-flush h-md-100">
                        <div class="card-header pt-7">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold text-gray-800">Recent Tenants</span>
                                <span class="text-gray-400 mt-1 fw-semibold fs-6">Latest registered tenants</span>
                            </h3>
                            <div class="card-toolbar">
                                <a href="{{ route('platform.tenants.index') }}" class="btn btn-sm btn-light">View All</a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-row-dashed align-middle gs-0 gy-3 my-0">
                                    <thead>
                                        <tr class="fs-7 fw-bold text-gray-400 border-bottom-0">
                                            <th class="p-0 pb-3 min-w-175px text-start">TENANT</th>
                                            <th class="p-0 pb-3 min-w-100px text-end">STATUS</th>
                                            <th class="p-0 pb-3 min-w-100px text-end">USERS</th>
                                            <th class="p-0 pb-3 min-w-100px text-end">PRODUCTS</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($stats['recent_tenants'] ?? [] as $tenant)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="symbol symbol-50px me-3">
                                                        <div class="symbol-label bg-light-primary">
                                                            <i class="ki-duotone ki-abstract-26 fs-2x text-primary">
                                                                <span class="path1"></span>
                                                                <span class="path2"></span>
                                                            </i>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex justify-content-start flex-column">
                                                        <a href="{{ route('platform.tenants.show', $tenant->id) }}" class="text-gray-800 fw-bold text-hover-primary mb-1 fs-6">{{ $tenant->name }}</a>
                                                        <span class="text-gray-400 fw-semibold d-block fs-7">{{ $tenant->subdomain }}.yukimart.local</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-end">
                                                @if($tenant->status === 'active')
                                                    <span class="badge badge-light-success fs-7 fw-bold">Active</span>
                                                @elseif($tenant->status === 'inactive')
                                                    <span class="badge badge-light-warning fs-7 fw-bold">Inactive</span>
                                                @else
                                                    <span class="badge badge-light-danger fs-7 fw-bold">Suspended</span>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                <span class="text-gray-800 fw-bold d-block fs-6">{{ $tenant->users_count ?? 0 }}</span>
                                            </td>
                                            <td class="text-end">
                                                <span class="text-gray-800 fw-bold d-block fs-6">{{ $tenant->products_count ?? 0 }}</span>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-5">
                                                <div class="text-gray-400">No tenants found</div>
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!--end::Table Widget 5-->
                </div>
                <!--end::Col-->
            </div>
            <!--end::Row-->

            <!--begin::Row-->
            <div class="row g-5 g-xl-10">
                <!--begin::Col-->
                <div class="col-xl-12">
                    <!--begin::Table widget 14-->
                    <div class="card card-flush h-md-100">
                        <div class="card-header pt-7">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold text-gray-800">Tenant Statistics</span>
                                <span class="text-gray-400 mt-1 fw-semibold fs-6">Performance overview by tenant</span>
                            </h3>
                            <div class="card-toolbar">
                                <div class="d-flex flex-stack flex-wrap gap-4">
                                    <div class="d-flex align-items-center fw-bold">
                                        <div class="text-muted fs-7 me-2">Sort by:</div>
                                        <select class="form-select form-select-transparent text-dark fs-7 lh-1 fw-bold py-0 ps-3 w-auto" data-control="select2" data-hide-search="true" data-dropdown-css-class="w-150px" data-placeholder="Select an option">
                                            <option></option>
                                            <option value="1" selected="selected">Users</option>
                                            <option value="2">Products</option>
                                            <option value="3">Revenue</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body pt-2">
                            <div class="table-responsive">
                                <table class="table table-row-dashed align-middle gs-0 gy-4 my-0">
                                    <thead>
                                        <tr class="fs-7 fw-bold text-gray-500 border-bottom-0">
                                            <th class="ps-0 min-w-200px">TENANT</th>
                                            <th class="min-w-100px">USERS</th>
                                            <th class="min-w-100px">PRODUCTS</th>
                                            <th class="min-w-100px">ORDERS</th>
                                            <th class="min-w-100px">REVENUE</th>
                                            <th class="min-w-100px">STATUS</th>
                                            <th class="min-w-100px text-end">ACTIONS</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($stats['tenant_stats'] ?? [] as $tenant)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="symbol symbol-50px me-3">
                                                        <div class="symbol-label bg-light-{{ $tenant['status'] === 'active' ? 'success' : 'warning' }}">
                                                            <i class="ki-duotone ki-abstract-26 fs-2x text-{{ $tenant['status'] === 'active' ? 'success' : 'warning' }}">
                                                                <span class="path1"></span>
                                                                <span class="path2"></span>
                                                            </i>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex justify-content-start flex-column">
                                                        <a href="{{ route('platform.tenants.show', $tenant['id']) }}" class="text-gray-800 fw-bold text-hover-primary mb-1 fs-6">{{ $tenant['name'] }}</a>
                                                        <span class="text-gray-400 fw-semibold d-block fs-7">{{ $tenant['subdomain'] }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-gray-800 fw-bold d-block fs-6">{{ $tenant['users_count'] ?? 0 }}</span>
                                            </td>
                                            <td>
                                                <span class="text-gray-800 fw-bold d-block fs-6">{{ $tenant['products_count'] ?? 0 }}</span>
                                            </td>
                                            <td>
                                                <span class="text-gray-800 fw-bold d-block fs-6">{{ $tenant['orders_count'] ?? 0 }}</span>
                                            </td>
                                            <td>
                                                <span class="text-gray-800 fw-bold d-block fs-6">{{ number_format($tenant['revenue'] ?? 0) }} VND</span>
                                            </td>
                                            <td>
                                                @if($tenant['status'] === 'active')
                                                    <span class="badge badge-light-success fs-7 fw-bold">Active</span>
                                                @elseif($tenant['status'] === 'inactive')
                                                    <span class="badge badge-light-warning fs-7 fw-bold">Inactive</span>
                                                @else
                                                    <span class="badge badge-light-danger fs-7 fw-bold">Suspended</span>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                <a href="{{ route('platform.tenants.show', $tenant['id']) }}" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1">
                                                    <i class="ki-duotone ki-switch fs-2">
                                                        <span class="path1"></span>
                                                        <span class="path2"></span>
                                                    </i>
                                                </a>
                                                <button class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm" data-bs-toggle="modal" data-bs-target="#kt_modal_switch_tenant" data-tenant-id="{{ $tenant['id'] }}" data-tenant-name="{{ $tenant['name'] }}">
                                                    <i class="ki-duotone ki-entrance-right fs-2">
                                                        <span class="path1"></span>
                                                        <span class="path2"></span>
                                                    </i>
                                                </button>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-5">
                                                <div class="text-gray-400">No tenant statistics available</div>
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!--end::Table widget 14-->
                </div>
                <!--end::Col-->
            </div>
            <!--end::Row-->

        </div>
    </div>
    <!--end::Content-->
</div>

<!--begin::Modal - Switch Tenant-->
<div class="modal fade" id="kt_modal_switch_tenant" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold">Switch to Tenant</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                </div>
            </div>
            <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                <div class="text-center mb-10">
                    <div class="text-gray-400 fw-semibold fs-6">You are about to switch to tenant context.</div>
                    <div class="text-gray-800 fw-bold fs-4 mt-2" id="tenant-name-display"></div>
                </div>
                <div class="d-flex flex-center flex-row-fluid">
                    <button type="button" class="btn btn-light me-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirm-switch-tenant">
                        <span class="indicator-label">Switch to Tenant</span>
                        <span class="indicator-progress">Please wait...
                        <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<!--end::Modal - Switch Tenant-->
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle tenant switching modal
    const switchTenantModal = document.getElementById('kt_modal_switch_tenant');
    const tenantNameDisplay = document.getElementById('tenant-name-display');
    const confirmSwitchBtn = document.getElementById('confirm-switch-tenant');
    let selectedTenantId = null;

    // Modal show event
    switchTenantModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        selectedTenantId = button.getAttribute('data-tenant-id');
        const tenantName = button.getAttribute('data-tenant-name');
        tenantNameDisplay.textContent = tenantName;
    });

    // Confirm switch button
    confirmSwitchBtn.addEventListener('click', function() {
        if (!selectedTenantId) return;

        // Show loading state
        confirmSwitchBtn.setAttribute('data-kt-indicator', 'on');
        confirmSwitchBtn.disabled = true;

        // Make AJAX request to switch tenant
        fetch('{{ route("platform.switch-to-tenant") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                tenant_id: selectedTenantId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Redirect to tenant dashboard
                window.location.href = data.redirect_url || '{{ route("admin.dashboard") }}';
            } else {
                // Show error message
                Swal.fire({
                    text: data.message || 'Failed to switch tenant',
                    icon: 'error',
                    buttonsStyling: false,
                    confirmButtonText: 'Ok, got it!',
                    customClass: {
                        confirmButton: 'btn btn-primary'
                    }
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                text: 'An error occurred while switching tenant',
                icon: 'error',
                buttonsStyling: false,
                confirmButtonText: 'Ok, got it!',
                customClass: {
                    confirmButton: 'btn btn-primary'
                }
            });
        })
        .finally(() => {
            // Hide loading state
            confirmSwitchBtn.removeAttribute('data-kt-indicator');
            confirmSwitchBtn.disabled = false;
            
            // Hide modal
            const modal = bootstrap.Modal.getInstance(switchTenantModal);
            modal.hide();
        });
    });
});
</script>
@endpush
