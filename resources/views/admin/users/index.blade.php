@extends('admin.index')
@section('page-header', 'User')
@section('page-sub_header', 'Danh sách user')
@section('style')
    <link rel="stylesheet" href="{{ asset('admin-assets/assets/plugins/custom/datatables/datatables.bundle.css') }}" />
@endsection
@section('content')
    <div class="card">
        @include('admin.users.elements.toolbar')
        <div class="card-body pt-0">
            <!--begin::Table-->
            <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_table_users">
                <!--begin::Table head-->
                <thead>
                    <!--begin::Table row-->
                    <tr class="text-start text-muted fw-bolder fs-7 text-uppercase gs-0">
                        <th class="w-10px pe-2">
                            <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                <input class="form-check-input" type="checkbox" data-kt-check="true"
                                    data-kt-check-target="#kt_table_users .form-check-input" value="1">
                            </div>
                        </th>
                        <th>User</th>
                        <th>Role</th>
                        <th>Last login</th>
                        <th>Status</th>
                        <th>Joined Date</th>
                        <th class="text-end min-w-100px">Actions</th>
                    </tr>
                    <!--end::Table row-->
                </thead>
                <!--end::Table head-->
                <!--begin::Table body-->
                <tbody class="text-gray-600 fw-bold">

                </tbody>
                <!--end::Table body-->
            </table>
            <!--end::Table-->
        </div>
    </div>
@endsection
@section('vendor-script')
    <script src="{{ asset('admin-assets/assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
@endsection
@section('scripts')


    <!--begin::Page Custom Javascript(used by this page)-->
    <script src="{{ asset('admin-assets/assets/js/custom/apps/user-management/users/list/table.js') }}"></script>
    <script src="{{ asset('admin-assets/assets/js/custom/apps/user-management/users/list/export-users.js') }}"></script>
    <script src="{{ asset('admin-assets/assets/js/custom/apps/user-management/users/list/add.js') }}"></script>
    <script src="{{ asset('admin-assets/assets/js/custom/widgets.js') }}"></script>
    <script src="{{ asset('admin-assets/assets/js/custom/widgets.js') }}"></script>
    <script src="{{ asset('admin-assets/assets/js/custom/apps/chat/chat.js') }}"></script>
    <script src="{{ asset('admin-assets/assets/js/custom/modals/create-app.js') }}"></script>
    <script src="{{ asset('admin-assets/assets/js/custom/modals/upgrade-plan.js') }}"></script>
    <script src="{{ asset('admin-assets/assets/js/custom/documentation/search.js') }}"></script>

    <!--end::Page Custom Javascript-->

@endsection
