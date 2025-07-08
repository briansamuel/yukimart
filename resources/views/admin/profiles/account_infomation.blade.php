@extends('admin.index')
@section('page-header', 'Thông tin tài khoản')
@section('page-sub_header', 'Tài khoản')
@section('style')

@endsection
@section('content')
    @include('admin.profiles.elements.header-detail')
    @include('admin.profiles.elements.basic-info')
@endsection
@section('vendor-script')
    <script src="{{ asset('admin-assets/assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
@endsection
@section('script')


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
