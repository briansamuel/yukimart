@extends('admin.layouts.tenant-app')

@section('title', 'Test Virtual Scrollbar')

@section('style')
    <link href="{{ asset('admin-assets/css/globals.css') }}" rel="stylesheet" type="text/css" />
    <style>
        .demo-container {
            margin: 20px;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .demo-table {
            width: 2000px; /* Force horizontal scroll */
        }
        
        .demo-table th,
        .demo-table td {
            padding: 12px;
            border: 1px solid #e4e6ef;
            white-space: nowrap;
            min-width: 150px;
        }
        
        .demo-table th {
            background: #f8f9fa;
            font-weight: 600;
        }
        
        .demo-table tbody tr:nth-child(even) {
            background: #f9f9f9;
        }
        
        .demo-table tbody tr:hover {
            background: #e3f2fd;
        }
    </style>
@endsection

@section('content')
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                        Test Virtual Scrollbar
                    </h1>
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ route('admin.dashboard') }}" class="text-muted text-hover-primary">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <span class="bullet bg-gray-400 w-5px h-2px"></span>
                        </li>
                        <li class="breadcrumb-item text-muted">Test Virtual Scrollbar</li>
                    </ul>
                </div>
            </div>
        </div>

        <div id="kt_app_content_container" class="app-container container-fluid">
            <div class="demo-container">
                <h3>Virtual Scrollbar Demo</h3>
                <p>Bảng này có width 2000px để test virtual scrollbar. Scrollbar gốc đã bị ẩn và thay thế bằng virtual scrollbar ở phía dưới.</p>
                
                <!-- Demo Table with Virtual Scrollbar -->
                <div class="kt_table_responsive_container" style="max-width: 100%; height: 400px;">
                    <table class="demo-table kt_table_responsive table align-middle">
                        <thead>
                            <tr>
                                <th>Column 1</th>
                                <th>Column 2</th>
                                <th>Column 3</th>
                                <th>Column 4</th>
                                <th>Column 5</th>
                                <th>Column 6</th>
                                <th>Column 7</th>
                                <th>Column 8</th>
                                <th>Column 9</th>
                                <th>Column 10</th>
                                <th>Column 11</th>
                                <th>Column 12</th>
                                <th>Column 13</th>
                                <th>Column 14</th>
                                <th>Column 15</th>
                            </tr>
                        </thead>
                        <tbody>
                            @for($i = 1; $i <= 20; $i++)
                            <tr>
                                <td>Data {{ $i }}-1</td>
                                <td>Data {{ $i }}-2</td>
                                <td>Data {{ $i }}-3</td>
                                <td>Data {{ $i }}-4</td>
                                <td>Data {{ $i }}-5</td>
                                <td>Data {{ $i }}-6</td>
                                <td>Data {{ $i }}-7</td>
                                <td>Data {{ $i }}-8</td>
                                <td>Data {{ $i }}-9</td>
                                <td>Data {{ $i }}-10</td>
                                <td>Data {{ $i }}-11</td>
                                <td>Data {{ $i }}-12</td>
                                <td>Data {{ $i }}-13</td>
                                <td>Data {{ $i }}-14</td>
                                <td>Data {{ $i }}-15</td>
                            </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>
                
                <div style="margin-top: 30px;">
                    <h4>Features:</h4>
                    <ul>
                        <li>✅ Scrollbar gốc đã bị ẩn hoàn toàn</li>
                        <li>✅ Virtual scrollbar với gradient background</li>
                        <li>✅ Hover effects và smooth transitions</li>
                        <li>✅ Drag & drop functionality</li>
                        <li>✅ Click to jump functionality</li>
                        <li>✅ Touch support cho mobile</li>
                        <li>✅ Auto-hide khi không cần scroll</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    // Additional demo functionality
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Virtual Scrollbar Demo loaded');
        
        // Log scroll events for debugging
        const container = document.querySelector('.kt_table_responsive_container');
        if (container) {
            container.addEventListener('scroll', function() {
                console.log('Scroll position:', this.scrollLeft, '/', this.scrollWidth - this.clientWidth);
            });
        }
    });
</script>
@endsection
