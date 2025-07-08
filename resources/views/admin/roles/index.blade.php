@extends('admin.index')
@section('page-header', __('roles.title'))
@section('page-sub_header', __('roles.subtitle'))

@section('style')
    <link href="{{ asset('admin-assets/assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
    <!--begin::Roles Grid-->
    <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-5 g-xl-9">
        @foreach ($roles as $role)
            <!--begin::Col-->
            <div class="col-md-4">
                <!--begin::Card-->
                <div class="card card-flush h-md-100">
                    <!--begin::Card header-->
                    <div class="card-header">
                        <!--begin::Card title-->
                        <div class="card-title">
                            <h2>{{ $role->display_name }}</h2>
                        </div>
                        <!--end::Card title-->
                        <!--begin::Card toolbar-->
                        <div class="card-toolbar">
                            @if ($role->is_active)
                                <span class="badge badge-light-success">{{ __('roles.active') }}</span>
                            @else
                                <span class="badge badge-light-danger">{{ __('roles.inactive') }}</span>
                            @endif
                        </div>
                        <!--end::Card toolbar-->
                    </div>
                    <!--end::Card header-->
                    <!--begin::Card body-->
                    <div class="card-body pt-1">
                        <!--begin::Description-->
                        @if ($role->description)
                            <div class="text-muted mb-5">{{ Str::limit($role->description, 100) }}</div>
                        @endif
                        <!--end::Description-->
                        <!--begin::Users-->
                        <div class="fw-bolder text-gray-600 mb-5">
                            {{ __('roles.total_users_with_role', ['count' => $role->users_count]) }}
                        </div>
                        <!--end::Users-->
                        <!--begin::Permissions-->
                        <div class="d-flex flex-column text-gray-600">
                            @php
                                $permissions_of_roles = $role->permissions()->limit(5)->get();
                                $totalPermissions = $role->permissions_count;
                            @endphp
                            @foreach ($permissions_of_roles as $permission)
                                <div class="d-flex align-items-center py-2">
                                    <span class="bullet bg-primary me-3"></span>
                                    {{ $permission->display_name }}
                                </div>
                            @endforeach

                            @if ($totalPermissions > 5)
                                <div class="d-flex align-items-center py-2">
                                    <span class="bullet bg-primary me-3"></span>
                                    <em>{{ __('roles.and_more_permissions', ['count' => $totalPermissions - 5]) }}</em>
                                </div>
                            @endif

                            @if ($totalPermissions == 0)
                                <div class="text-muted fst-italic">{{ __('roles.no_permissions_assigned') }}</div>
                            @endif
                        </div>
                        <!--end::Permissions-->
                    </div>
                    <!--end::Card body-->
                    <!--begin::Card footer-->
                    <div class="card-footer flex-wrap pt-0">
                        <a href="{{ route('admin.roles.show', $role->id) }}"
                            class="btn btn-light btn-active-primary my-1 me-2">
                            {{ __('roles.view_role') }}
                        </a>
                        <a href="{{ route('admin.roles.edit', $role->id) }}"
                            class="btn btn-light btn-active-primary my-1 me-2">
                            {{ __('roles.edit_role') }}
                        </a>
                        <a href="#" class="btn btn-light btn-active-danger my-1"
                            data-kt-roles-grid-filter="delete_row" data-role-id="{{ $role->id }}"
                            data-role-name="{{ $role->display_name }}">
                            {{ __('roles.delete_role') }}
                        </a>
                    </div>
                    <!--end::Card footer-->
                </div>
                <!--end::Card-->
            </div>
            <!--end::Col-->
        @endforeach

        <!--begin::Add new card-->
        <div class="col-md-4">
            <!--begin::Card-->
            <div class="card h-md-100">
                <!--begin::Card body-->
                <div class="card-body d-flex flex-center">
                    <!--begin::Button-->
                    <a href="{{ route('admin.roles.create') }}" class="btn btn-clear d-flex flex-column flex-center">
                        <!--begin::Illustration-->
                        <img src="{{ asset('admin-assets/assets/media/illustrations/sketchy-1/4.png') }}" alt=""
                            class="mw-100 mh-150px mb-7">
                        <!--end::Illustration-->
                        <!--begin::Label-->
                        <div class="fw-bolder fs-3 text-gray-600 text-hover-primary">{{ __('roles.add_new_role') }}</div>
                        <!--end::Label-->
                    </a>
                    <!--end::Button-->
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Card-->
        </div>
        <!--end::Add new card-->
    </div>
    <!--end::Roles Grid-->

    <!--begin::Pagination-->
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <div class="d-flex align-items-center py-3">
            {{ __('common.showing') }} {{ $roles->firstItem() ?? 0 }} {{ __('common.to') }}
            {{ $roles->lastItem() ?? 0 }} {{ __('common.of') }} {{ $roles->total() }} {{ __('common.results') }}
        </div>
        <div class="d-flex align-items-center py-3">
            {{ $roles->links() }}
        </div>
    </div>
    <!--end::Pagination-->

    <!--begin::Modals-->
    @include('admin.roles.modals.add')
    @include('admin.roles.modals.export')
    <!--end::Modals-->
@endsection

@section('vendor-script')
    <script src="{{ asset('admin-assets/assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
@endsection

@push('scripts')
    <script src="{{ asset('admin-assets/assets/js/custom/apps/roles/list/grid.js') }}"></script>
    <script src="{{ asset('admin-assets/assets/js/custom/apps/roles/list/export.js') }}"></script>
    <script src="{{ asset('admin-assets/assets/js/custom/apps/roles/list/add.js') }}"></script>
@endpush
