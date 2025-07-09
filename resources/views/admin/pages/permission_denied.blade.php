@extends('admin.layouts.app')

@section('title', 'Không có quyền truy cập')

@section('content')
<div class="d-flex flex-column flex-center flex-column-fluid">
    <!--begin::Content-->
    <div class="d-flex flex-column flex-center text-center p-10">
        <!--begin::Wrapper-->
        <div class="card card-flush w-lg-650px py-5">
            <div class="card-body py-15 py-lg-20">
                <!--begin::Logo-->
                <div class="mb-13">
                    <a href="{{ route('admin.dashboard') }}" class="mb-3">
                        <img alt="Logo" src="{{ asset('admin-assets/media/logos/logo-1.svg') }}" class="h-40px" />
                    </a>
                </div>
                <!--end::Logo-->
                <!--begin::Title-->
                <h1 class="fw-bolder fs-2hx text-gray-900 mb-4">Không có quyền truy cập</h1>
                <!--end::Title-->
                <!--begin::Text-->
                <div class="fw-semibold fs-6 text-gray-500 mb-7">
                    Bạn không có quyền truy cập vào trang này.<br />
                    Vui lòng liên hệ quản trị viên để được cấp quyền.
                </div>
                <!--end::Text-->
                <!--begin::Illustration-->
                <div class="mb-11">
                    <img src="{{ asset('admin-assets/media/auth/agency.png') }}" class="mw-100 mh-300px theme-light-show" alt="" />
                    <img src="{{ asset('admin-assets/media/auth/agency-dark.png') }}" class="mw-100 mh-300px theme-dark-show" alt="" />
                </div>
                <!--end::Illustration-->
                <!--begin::Link-->
                <div class="mb-0">
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-primary">Quay lại Dashboard</a>
                </div>
                <!--end::Link-->
            </div>
        </div>
        <!--end::Wrapper-->
    </div>
    <!--end::Content-->
</div>
@endsection
