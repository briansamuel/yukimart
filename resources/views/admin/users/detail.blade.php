@extends('admin.index')
@section('page-header', 'User')
@section('page-sub_header', 'Danh sách user')
@section('style')

@endsection
@section('content')
    <!--begin::Content container-->
    <div class="d-flex flex-column flex-lg-row">
        @include('admin.users.elements.sidebar')
        @include('admin.users.elements.content')
    </div>
    <!--end::Content container-->
@endsection
@section('vendor-script')

@endsection
@section('scripts')


    <!--begin::Page Custom Javascript(used by this page)-->

    <script src="{{ asset('admin-assets/assets/js/custom/widgets.js') }}"></script>
    <script src="{{ asset('admin-assets/assets/js/custom/widgets.js') }}"></script>
    <script src="{{ asset('admin-assets/assets/js/custom/apps/chat/chat.js') }}"></script>
    <script src="{{ asset('admin-assets/assets/js/custom/modals/create-app.js') }}"></script>
    <script src="{{ asset('admin-assets/assets/js/custom/modals/upgrade-plan.js') }}"></script>
    <script src="{{ asset('admin-assets/assets/js/custom/documentation/search.js') }}"></script>

    <!--end::Page Custom Javascript-->

@endsection
