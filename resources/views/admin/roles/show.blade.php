@extends('admin.index')
@section('page-header', __('roles.role_details'))
@section('page-sub_header', $role->display_name)

@section('content')
<!--begin::Layout-->
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
                        <div class="symbol-label fs-2 fw-semibold text-{{ str_replace('badge-light-', '', $role->badge_class) }} bg-light-{{ str_replace('badge-light-', '', $role->badge_class) }}">
                            {{ strtoupper(substr($role->name, 0, 2)) }}
                        </div>
                    </div>
                    <!--end::Avatar-->
                    <!--begin::Name-->
                    <a href="#" class="fs-3 text-gray-800 text-hover-primary fw-bold mb-3">{{ $role->display_name }}</a>
                    <!--end::Name-->
                    <!--begin::Position-->
                    <div class="mb-9">
                        <!--begin::Badge-->
                        <div class="badge {{ $role->badge_class }} fw-bold">{{ $role->name }}</div>
                        <!--end::Badge-->
                    </div>
                    <!--end::Position-->
                </div>
                <!--end::User Info-->
                <!--end::Summary-->
                <!--begin::Details toggle-->
                <div class="d-flex flex-stack fs-4 py-3">
                    <div class="fw-bold rotate collapsible" data-bs-toggle="collapse" href="#kt_role_view_details" role="button" aria-expanded="false" aria-controls="kt_role_view_details">{{ __('roles.role_details') }}
                        <span class="ms-2 rotate-180">
                            <i class="ki-duotone ki-down fs-3"></i>
                        </span>
                    </div>
                    <span data-bs-toggle="tooltip" data-bs-trigger="hover" title="{{ __('roles.tooltips.edit_role') }}">
                        <a href="{{ route('admin.roles.edit', $role->id) }}" class="btn btn-sm btn-light-primary">{{ __('roles.edit_role') }}</a>
                    </span>
                </div>
                <!--end::Details toggle-->
                <div class="separator"></div>
                <!--begin::Details content-->
                <div id="kt_role_view_details" class="collapse show">
                    <div class="pb-5 fs-6">
                        <!--begin::Details item-->
                        <div class="fw-bold mt-5">{{ __('roles.name') }}</div>
                        <div class="text-gray-600">{{ $role->name }}</div>
                        <!--begin::Details item-->
                        <!--begin::Details item-->
                        <div class="fw-bold mt-5">{{ __('roles.display_name') }}</div>
                        <div class="text-gray-600">{{ $role->display_name }}</div>
                        <!--begin::Details item-->
                        <!--begin::Details item-->
                        <div class="fw-bold mt-5">{{ __('roles.description') }}</div>
                        <div class="text-gray-600">{{ $role->description ?: __('common.no_description') }}</div>
                        <!--begin::Details item-->
                        <!--begin::Details item-->
                        <div class="fw-bold mt-5">{{ __('roles.status') }}</div>
                        <div class="text-gray-600">
                            @if($role->is_active)
                                <span class="badge badge-light-success">{{ __('roles.active') }}</span>
                            @else
                                <span class="badge badge-light-danger">{{ __('roles.inactive') }}</span>
                            @endif
                        </div>
                        <!--begin::Details item-->
                        <!--begin::Details item-->
                        <div class="fw-bold mt-5">{{ __('roles.users_count') }}</div>
                        <div class="text-gray-600">{{ $role->users_count }} {{ __('common.users') }}</div>
                        <!--begin::Details item-->
                        <!--begin::Details item-->
                        <div class="fw-bold mt-5">{{ __('roles.permissions_count') }}</div>
                        <div class="text-gray-600">{{ $role->permissions_count }} {{ __('common.permissions') }}</div>
                        <!--begin::Details item-->
                        <!--begin::Details item-->
                        <div class="fw-bold mt-5">{{ __('roles.created_at') }}</div>
                        <div class="text-gray-600">{{ $role->created_at->format('d/m/Y H:i') }}</div>
                        <!--begin::Details item-->
                    </div>
                </div>
                <!--end::Details content-->
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Card-->
    </div>
    <!--end::Sidebar-->
    <!--begin::Content-->
    <div class="flex-lg-row-fluid ms-lg-15">
        <!--begin:::Tabs-->
        <ul class="nav nav-custom nav-tabs nav-line-tabs nav-line-tabs-2x border-0 fs-4 fw-semibold mb-8">
            <!--begin:::Tab item-->
            <li class="nav-item">
                <a class="nav-link text-active-primary pb-4 active" data-bs-toggle="tab" href="#kt_role_view_permissions">{{ __('roles.permissions') }}</a>
            </li>
            <!--end:::Tab item-->
            <!--begin:::Tab item-->
            <li class="nav-item">
                <a class="nav-link text-active-primary pb-4" data-bs-toggle="tab" href="#kt_role_view_users">{{ __('roles.tabs.users') }}</a>
            </li>
            <!--end:::Tab item-->
        </ul>
        <!--end:::Tabs-->
        <!--begin:::Tab content-->
        <div class="tab-content" id="myTabContent">
            <!--begin:::Tab pane-->
            <div class="tab-pane fade show active" id="kt_role_view_permissions" role="tabpanel">
                <!--begin::Card-->
                <div class="card card-flush">
                    <!--begin::Card header-->
                    <div class="card-header align-items-center py-5 gap-2 gap-md-5">
                        <!--begin::Card title-->
                        <div class="card-title">
                            <h3 class="fw-bold">{{ __('roles.role_permissions') }}</h3>
                        </div>
                        <!--end::Card title-->
                    </div>
                    <!--end::Card header-->
                    <!--begin::Card body-->
                    <div class="card-body pt-0">
                        <!--begin::Table wrapper-->
                        <div class="table-responsive">
                            <!--begin::Table-->
                            <table class="table align-middle table-row-dashed fs-6 gy-5 mb-0">
                                <!--begin::Table head-->
                                <thead>
                                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                        <th class="min-w-125px">{{ __('permissions.module') }}</th>
                                        <th class="min-w-250px">{{ __('permissions.permissions') }}</th>
                                    </tr>
                                </thead>
                                <!--end::Table head-->
                                <!--begin::Table body-->
                                <tbody class="fw-semibold text-gray-600">
                                    @php
                                        $permissionsByModule = $role->getPermissionsByModule();
                                    @endphp
                                    @forelse($permissionsByModule as $module => $permissions)
                                        <tr>
                                            <td>{{ __('roles.permission_modules.' . $module) }}</td>
                                            <td>
                                                @foreach($permissions as $permission)
                                                    <span class="badge {{ $permission->badge_class }} me-2 mb-2">{{ $permission->display_name }}</span>
                                                @endforeach
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="text-center py-10">
                                                <div class="text-muted">{{ __('roles.empty_states.no_permissions') }}</div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <!--end::Table body-->
                            </table>
                            <!--end::Table-->
                        </div>
                        <!--end::Table wrapper-->
                    </div>
                    <!--end::Card body-->
                </div>
                <!--end::Card-->
            </div>
            <!--end:::Tab pane-->
            <!--begin:::Tab pane-->
            <div class="tab-pane fade" id="kt_role_view_users" role="tabpanel">
                <!--begin::Card-->
                <div class="card card-flush">
                    <!--begin::Card header-->
                    <div class="card-header align-items-center py-5 gap-2 gap-md-5">
                        <!--begin::Card title-->
                        <div class="card-title">
                            <h3 class="fw-bold">{{ __('roles.tabs.users') }} ({{ $role->users_count }})</h3>
                        </div>
                        <!--end::Card title-->
                    </div>
                    <!--end::Card header-->
                    <!--begin::Card body-->
                    <div class="card-body pt-0">
                        <!--begin::Table wrapper-->
                        <div class="table-responsive">
                            <!--begin::Table-->
                            <table class="table align-middle table-row-dashed fs-6 gy-5 mb-0">
                                <!--begin::Table head-->
                                <thead>
                                    <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                        <th class="min-w-250px">{{ __('common.user') }}</th>
                                        <th class="min-w-125px">{{ __('common.email') }}</th>
                                        <th class="min-w-125px">{{ __('common.assigned_at') }}</th>
                                        <th class="text-end min-w-100px">{{ __('common.actions') }}</th>
                                    </tr>
                                </thead>
                                <!--end::Table head-->
                                <!--begin::Table body-->
                                <tbody class="fw-semibold text-gray-600">
                                    @forelse($role->users as $user)
                                        <tr>
                                            <td class="d-flex align-items-center">
                                                <!--begin::Avatar-->
                                                <div class="symbol symbol-circle symbol-50px overflow-hidden me-3">
                                                    <a href="#">
                                                        <div class="symbol-label">
                                                            <img src="{{ $user->avatar ?? asset('admin-assets/assets/media/avatars/300-6.jpg') }}" alt="{{ $user->name }}" class="w-100" />
                                                        </div>
                                                    </a>
                                                </div>
                                                <!--end::Avatar-->
                                                <!--begin::User details-->
                                                <div class="d-flex flex-column">
                                                    <a href="#" class="text-gray-800 text-hover-primary mb-1">{{ $user->name }}</a>
                                                    <span>{{ $user->email }}</span>
                                                </div>
                                                <!--begin::User details-->
                                            </td>
                                            <td>{{ $user->email }}</td>
                                            <td>{{ $user->pivot->assigned_at ? \Carbon\Carbon::parse($user->pivot->assigned_at)->format('d/m/Y H:i') : '-' }}</td>
                                            <td class="text-end">
                                                <a href="#" class="btn btn-light btn-active-light-primary btn-flex btn-center btn-sm" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">{{ __('common.actions') }}
                                                    <i class="ki-duotone ki-down fs-5 ms-1"></i>
                                                </a>
                                                <!--begin::Menu-->
                                                <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4" data-kt-menu="true">
                                                    <!--begin::Menu item-->
                                                    <div class="menu-item px-3">
                                                        <a href="#" class="menu-link px-3">{{ __('common.view') }}</a>
                                                    </div>
                                                    <!--end::Menu item-->
                                                    <!--begin::Menu item-->
                                                    <div class="menu-item px-3">
                                                        <a href="#" class="menu-link px-3" data-kt-users-table-filter="delete_row">{{ __('roles.remove_role') }}</a>
                                                    </div>
                                                    <!--end::Menu item-->
                                                </div>
                                                <!--end::Menu-->
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-10">
                                                <div class="text-muted">{{ __('roles.empty_states.no_users') }}</div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <!--end::Table body-->
                            </table>
                            <!--end::Table-->
                        </div>
                        <!--end::Table wrapper-->
                    </div>
                    <!--end::Card body-->
                </div>
                <!--end::Card-->
            </div>
            <!--end:::Tab pane-->
        </div>
        <!--end:::Tab content-->
    </div>
    <!--end::Content-->
</div>
<!--end::Layout-->
@endsection

@push('scripts')
<script src="{{ asset('admin-assets/assets/js/custom/apps/roles/view-role.js') }}"></script>
@endpush
