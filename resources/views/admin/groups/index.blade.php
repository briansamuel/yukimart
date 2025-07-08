@extends('admin.index')
@section('page-header', 'Groups')
@section('page-sub_header', 'Danh sách groups')
@section('style')

@endsection
@section('content')
    <!--begin::Row-->
    @include('admin.groups.elements.roles')
    <!--end::Row-->
    <!--begin::Modals-->
    @include('admin.groups.elements.modal-add-role')
    @include('admin.groups.elements.modal-update-role')
    <!--end::Modals-->
@endsection
@section('vendor-script')
    <script src="{{ asset('admin-assets/assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
@endsection
@section('scripts')


    <!--begin::Page Custom Javascript(used by this page)-->
    <script src="{{ asset('admin-assets/assets/js/custom/apps/user-management/roles/list/add.js') }}"></script>
    <script src="{{ asset('admin-assets/assets/js/custom/apps/user-management/roles/list/update-role.js') }}"></script>
    <script src="{{ asset('admin-assets/assets/js/custom/widgets.js') }}"></script>
    <script src="{{ asset('admin-assets/assets/js/custom/apps/chat/chat.js') }}"></script>
    <script src="{{ asset('admin-assets/assets/js/custom/modals/create-app.js') }}"></script>
    <script src="{{ asset('admin-assets/assets/js/custom/modals/upgrade-plan.js') }}"></script>
    <script src="{{ asset('admin-assets/assets/js/custom/documentation/search.js') }}"></script>

    <!--end::Page Custom Javascript-->

@endsection
