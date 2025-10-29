<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subdomain Tenant Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-12">
                <h1 class="mb-4">🌐 Subdomain Tenant Test</h1>
                
                <!-- Current URL Info -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Current URL Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Full URL:</strong> <span class="text-primary">{{ request()->fullUrl() }}</span><br>
                                <strong>Host:</strong> <span class="text-info">{{ request()->getHost() }}</span><br>
                                <strong>Subdomain:</strong> <span class="text-success">{{ explode('.', request()->getHost())[0] ?? 'None' }}</span>
                            </div>
                            <div class="col-md-6">
                                <strong>Request Path:</strong> {{ request()->path() }}<br>
                                <strong>Route Name:</strong> {{ request()->route()->getName() ?? 'None' }}<br>
                                <strong>Timestamp:</strong> {{ now()->format('Y-m-d H:i:s') }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tenant Context Info -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Tenant Context Information</h5>
                    </div>
                    <div class="card-body">
                        @php
                            $tenant = request()->attributes->get('tenant');
                        @endphp
                        
                        @if($tenant)
                            <div class="alert alert-success">
                                <h6>✅ Tenant Resolved Successfully!</h6>
                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <strong>Tenant ID:</strong> {{ $tenant->id }}<br>
                                        <strong>Name:</strong> {{ $tenant->name }}<br>
                                        <strong>Slug:</strong> {{ $tenant->slug }}<br>
                                        <strong>Subdomain:</strong> {{ $tenant->subdomain }}
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Status:</strong> <span class="badge bg-{{ $tenant->status === 'active' ? 'success' : 'warning' }}">{{ $tenant->status }}</span><br>
                                        <strong>Plan:</strong> <span class="badge bg-info">{{ $tenant->plan_type }}</span><br>
                                        <strong>Users:</strong> {{ $tenant->current_users }}/{{ $tenant->max_users }}<br>
                                        <strong>Products:</strong> {{ $tenant->current_products }}/{{ $tenant->max_products }}
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-warning">
                                <h6>⚠️ No Tenant Context Found</h6>
                                <p>This could mean:</p>
                                <ul>
                                    <li>Subdomain middleware is not working</li>
                                    <li>Tenant not found for this subdomain</li>
                                    <li>Accessing from main domain</li>
                                </ul>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Subdomain Links -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Available Tenant Subdomains</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @php
                                $allTenants = \App\Models\Tenant::where('status', 'active')->get();
                            @endphp
                            
                            @foreach($allTenants as $tenantItem)
                                <div class="col-md-6 mb-3">
                                    <div class="card {{ $tenant && $tenant->id === $tenantItem->id ? 'border-primary' : '' }}">
                                        <div class="card-body">
                                            <h6 class="card-title">
                                                {{ $tenantItem->name }}
                                                @if($tenant && $tenant->id === $tenantItem->id)
                                                    <span class="badge bg-primary">Current</span>
                                                @endif
                                            </h6>
                                            <p class="card-text">
                                                <strong>Subdomain:</strong> {{ $tenantItem->subdomain }}<br>
                                                <strong>Plan:</strong> {{ $tenantItem->plan_type }}<br>
                                                <strong>Users:</strong> {{ $tenantItem->current_users }}/{{ $tenantItem->max_users }}
                                            </p>
                                            <a href="http://{{ $tenantItem->subdomain }}.yukimart.local/tenant/subdomain-test" 
                                               class="btn btn-sm btn-outline-primary">
                                                Visit {{ $tenantItem->subdomain }}.yukimart.local
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- API Test -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>API Test</h5>
                    </div>
                    <div class="card-body">
                        <button id="test-api" class="btn btn-primary">Test Tenant API</button>
                        <div id="api-results" class="mt-3"></div>
                    </div>
                </div>

                <!-- Navigation Links -->
                <div class="card">
                    <div class="card-header">
                        <h5>Navigation Links</h5>
                    </div>
                    <div class="card-body">
                        @if($tenant)
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Admin Panel Links:</h6>
                                    <ul class="list-unstyled">
                                        <li><a href="/admin/login" class="btn btn-sm btn-outline-success mb-1">Admin Login</a></li>
                                        <li><a href="/admin/dashboard" class="btn btn-sm btn-outline-info mb-1">Dashboard</a></li>
                                        <li><a href="/admin/products" class="btn btn-sm btn-outline-secondary mb-1">Products</a></li>
                                        <li><a href="/admin/customers" class="btn btn-sm btn-outline-secondary mb-1">Customers</a></li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h6>API Links:</h6>
                                    <ul class="list-unstyled">
                                        <li><a href="/api/tenant/current" class="btn btn-sm btn-outline-warning mb-1" target="_blank">Current Tenant API</a></li>
                                        <li><a href="/api/tenant/info" class="btn btn-sm btn-outline-warning mb-1" target="_blank">Tenant Info API</a></li>
                                    </ul>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-info">
                                <p>No tenant context available. Try accessing from a tenant subdomain:</p>
                                <ul>
                                    <li><a href="http://yukimart.yukimart.local/tenant/subdomain-test">yukimart.yukimart.local</a></li>
                                    <li><a href="http://tenant1.yukimart.local/tenant/subdomain-test">tenant1.yukimart.local</a></li>
                                    <li><a href="http://tenant2.yukimart.local/tenant/subdomain-test">tenant2.yukimart.local</a></li>
                                    <li><a href="http://hellomart.yukimart.local/tenant/subdomain-test">hellomart.yukimart.local</a></li>
                                </ul>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Setup CSRF token for AJAX requests
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Test API button
            $('#test-api').click(function() {
                $('#api-results').html('<div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div>');
                
                // Test current tenant API
                $.get('/api/tenant/info')
                    .done(function(response) {
                        let html = '<div class="alert alert-success"><h6>✅ API Test Successful</h6>';
                        html += '<pre>' + JSON.stringify(response, null, 2) + '</pre></div>';
                        $('#api-results').html(html);
                    })
                    .fail(function(xhr) {
                        let html = '<div class="alert alert-danger"><h6>❌ API Test Failed</h6>';
                        html += '<p>Status: ' + xhr.status + '</p>';
                        html += '<p>Response: ' + xhr.responseText + '</p></div>';
                        $('#api-results').html(html);
                    });
            });

            // Auto-test API on page load
            setTimeout(function() {
                $('#test-api').click();
            }, 1000);
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
