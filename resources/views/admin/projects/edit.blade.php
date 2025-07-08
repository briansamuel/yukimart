@extends('admin.index')
@section('page-header', __('admin.projects.header'))
@section('page-sub_header', __('admin.projects.edit_project'))
@section('style')
<link rel="stylesheet" href="{{ asset('admin-assets/assets/plugins/custom/datatables/datatables.bundle.css') }}" />
@endsection
@section('action_toolbar')
@include('admin.projects.elements.action-toolbar')
@endsection
@section('content')
    @include('admin.projects.elements.details.navbar')
    @include('admin.projects.elements.details.tasks')
@endsection

@section('modal')
@include('admin.projects.elements.create-app')
@endsection
@section('vendor-script')
    <script src="{{ asset('admin-assets/assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
@endsection
@section('script')


    <!--begin::Page Custom Javascript(used by this page)-->
    {{-- <script src="{{ asset('admin-assets/assets/js/custom/apps/projects/list/list.js') }}"></script> --}}
    <script src="{{ asset('admin-assets/assets/js/custom/apps/projects/project.js') }}"></script>
    {{-- <script src="{{ asset('admin-assets/assets/js/custom/apps/pages/list/export-pages.js') }}"></script> --}}
    {{-- <script src="{{ asset('admin-assets/assets/js/custom/apps/pages/list/add.js') }}"></script> --}}

    <script src="{{ asset('admin-assets/assets/js/custom/widgets.js') }}"></script>
    <script src="{{ asset('admin-assets/assets/js/custom/apps/chat/chat.js') }}"></script>
    {{-- <script src="{{ asset('admin-assets/assets/js/custom/modals/create-app.js') }}"></script> --}}
    <script src="{{ asset('admin-assets/assets/js/custom/modals/upgrade-plan.js') }}"></script>
    <script src="{{ asset('admin-assets/assets/js/custom/documentation/search.js') }}"></script>

    <!--end::Page Custom Javascript-->

@endsection
