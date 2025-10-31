@extends('admin.layouts.tenant-app')

@section('page-header', $title ?? 'Trang đang phát triển')
@section('page-sub_header', $description ?? 'Tính năng này đang được phát triển')

@section('style')
<style>
    .placeholder-container {
        min-height: 500px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .placeholder-content {
        text-align: center;
        max-width: 600px;
    }
    
    .placeholder-icon {
        font-size: 120px;
        color: #009ef7;
        margin-bottom: 30px;
    }
    
    .placeholder-title {
        font-size: 32px;
        font-weight: 600;
        color: #181c32;
        margin-bottom: 15px;
    }
    
    .placeholder-description {
        font-size: 16px;
        color: #7e8299;
        margin-bottom: 30px;
        line-height: 1.6;
    }
    
    .placeholder-features {
        background: #f5f8fa;
        border-radius: 8px;
        padding: 20px;
        margin-top: 30px;
    }
    
    .placeholder-features h5 {
        font-size: 18px;
        font-weight: 600;
        color: #181c32;
        margin-bottom: 15px;
    }
    
    .placeholder-features ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .placeholder-features li {
        padding: 8px 0;
        color: #7e8299;
        font-size: 14px;
    }
    
    .placeholder-features li:before {
        content: "✓";
        color: #50cd89;
        font-weight: bold;
        margin-right: 10px;
    }
</style>
@endsection

@section('content')
<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <!--begin::Content-->
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <!--begin::Content container-->
            <div id="kt_app_content_container" class="app-container container-xxl">
                <!--begin::Card-->
                <div class="card">
                    <div class="card-body">
                        <div class="placeholder-container">
                            <div class="placeholder-content">
                                <!--begin::Icon-->
                                <div class="placeholder-icon">
                                    <i class="ki-duotone ki-rocket">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                </div>
                                <!--end::Icon-->
                                
                                <!--begin::Title-->
                                <h1 class="placeholder-title">{{ $title ?? 'Trang đang phát triển' }}</h1>
                                <!--end::Title-->
                                
                                <!--begin::Description-->
                                <p class="placeholder-description">
                                    {{ $description ?? 'Tính năng này đang được phát triển và sẽ sớm ra mắt.' }}
                                    <br>
                                    Vui lòng quay lại sau hoặc liên hệ với quản trị viên để biết thêm thông tin.
                                </p>
                                <!--end::Description-->
                                
                                <!--begin::Actions-->
                                <div class="d-flex justify-content-center gap-3">
                                    <a href="{{ route('admin.dashboard') }}" class="btn btn-primary">
                                        <i class="ki-duotone ki-home fs-2">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                        Về trang chủ
                                    </a>
                                    <a href="javascript:history.back()" class="btn btn-light">
                                        <i class="ki-duotone ki-arrow-left fs-2">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                        Quay lại
                                    </a>
                                </div>
                                <!--end::Actions-->
                                
                                <!--begin::Features (Optional)-->
                                <div class="placeholder-features">
                                    <h5>Tính năng sắp ra mắt:</h5>
                                    <ul>
                                        <li>Giao diện thân thiện, dễ sử dụng</li>
                                        <li>Báo cáo chi tiết và trực quan</li>
                                        <li>Tích hợp đầy đủ với hệ thống</li>
                                        <li>Hỗ trợ xuất dữ liệu Excel/PDF</li>
                                    </ul>
                                </div>
                                <!--end::Features-->
                            </div>
                        </div>
                    </div>
                </div>
                <!--end::Card-->
            </div>
            <!--end::Content container-->
        </div>
        <!--end::Content-->
    </div>
</div>
@endsection

@section('script')
<script>
    // Optional: Add any JavaScript for the placeholder page
    console.log('Placeholder page loaded: {{ $title ?? "Unknown" }}');
</script>
@endsection

