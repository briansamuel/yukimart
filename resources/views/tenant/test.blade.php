<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenant Integration Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-12">
                <h1 class="mb-4">🏢 Tenant Integration Test</h1>
                
                <!-- Current Tenant Info -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Current Tenant Information</h5>
                    </div>
                    <div class="card-body">
                        <div id="current-tenant-info">
                            <div class="spinner-border" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Available Tenants -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Available Tenants</h5>
                    </div>
                    <div class="card-body">
                        <div id="available-tenants">
                            <div class="spinner-border" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tenant Switcher -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Tenant Switcher</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="tenant-select" class="form-label">Switch to Tenant:</label>
                                <select id="tenant-select" class="form-select">
                                    <option value="">Select a tenant...</option>
                                </select>
                            </div>
                            <div class="col-md-6 d-flex align-items-end">
                                <button id="switch-tenant-btn" class="btn btn-primary">Switch Tenant</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tenant Statistics -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Tenant Statistics</h5>
                    </div>
                    <div class="card-body">
                        <div id="tenant-statistics">
                            <div class="spinner-border" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Test Results -->
                <div class="card">
                    <div class="card-header">
                        <h5>Test Results</h5>
                    </div>
                    <div class="card-body">
                        <div id="test-results">
                            <!-- Test results will be displayed here -->
                        </div>
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

            // Load initial data
            loadCurrentTenant();
            loadAvailableTenants();
            loadTenantStatistics();

            // Tenant switcher
            $('#switch-tenant-btn').click(function() {
                const tenantId = $('#tenant-select').val();
                if (!tenantId) {
                    alert('Please select a tenant');
                    return;
                }
                switchTenant(tenantId);
            });

            function loadCurrentTenant() {
                $.get('/api/tenant/current')
                    .done(function(response) {
                        displayCurrentTenant(response.tenant);
                        addTestResult('✅ Current tenant loaded successfully', 'success');
                    })
                    .fail(function(xhr) {
                        $('#current-tenant-info').html('<div class="alert alert-danger">Failed to load current tenant</div>');
                        addTestResult('❌ Failed to load current tenant: ' + xhr.responseText, 'danger');
                    });
            }

            function loadAvailableTenants() {
                $.get('/api/tenant/available')
                    .done(function(response) {
                        displayAvailableTenants(response.tenants);
                        populateTenantSelect(response.tenants);
                        addTestResult('✅ Available tenants loaded successfully', 'success');
                    })
                    .fail(function(xhr) {
                        $('#available-tenants').html('<div class="alert alert-danger">Failed to load available tenants</div>');
                        addTestResult('❌ Failed to load available tenants: ' + xhr.responseText, 'danger');
                    });
            }

            function loadTenantStatistics() {
                $.get('/api/tenant/statistics')
                    .done(function(response) {
                        displayTenantStatistics(response.statistics);
                        addTestResult('✅ Tenant statistics loaded successfully', 'success');
                    })
                    .fail(function(xhr) {
                        $('#tenant-statistics').html('<div class="alert alert-danger">Failed to load tenant statistics</div>');
                        addTestResult('❌ Failed to load tenant statistics: ' + xhr.responseText, 'danger');
                    });
            }

            function switchTenant(tenantId) {
                $.post('/api/tenant/switch', { tenant_id: tenantId })
                    .done(function(response) {
                        if (response.success) {
                            addTestResult('✅ Tenant switched successfully', 'success');
                            // Reload all data
                            loadCurrentTenant();
                            loadTenantStatistics();
                        } else {
                            addTestResult('❌ Failed to switch tenant: ' + response.message, 'danger');
                        }
                    })
                    .fail(function(xhr) {
                        addTestResult('❌ Failed to switch tenant: ' + xhr.responseText, 'danger');
                    });
            }

            function displayCurrentTenant(tenant) {
                if (tenant) {
                    $('#current-tenant-info').html(`
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Name:</strong> ${tenant.name}<br>
                                <strong>Slug:</strong> ${tenant.slug}<br>
                                <strong>Status:</strong> <span class="badge bg-${tenant.status === 'active' ? 'success' : 'warning'}">${tenant.status}</span>
                            </div>
                            <div class="col-md-6">
                                <strong>Plan:</strong> ${tenant.plan_type}<br>
                                <strong>Users:</strong> ${tenant.current_users}/${tenant.max_users}<br>
                                <strong>Products:</strong> ${tenant.current_products}/${tenant.max_products}
                            </div>
                        </div>
                    `);
                } else {
                    $('#current-tenant-info').html('<div class="alert alert-warning">No current tenant</div>');
                }
            }

            function displayAvailableTenants(tenants) {
                if (tenants && tenants.length > 0) {
                    let html = '<div class="row">';
                    tenants.forEach(function(tenant) {
                        html += `
                            <div class="col-md-6 mb-3">
                                <div class="card">
                                    <div class="card-body">
                                        <h6 class="card-title">${tenant.name}</h6>
                                        <p class="card-text">
                                            <small class="text-muted">
                                                Role: ${tenant.role} | Status: ${tenant.status}
                                            </small>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    html += '</div>';
                    $('#available-tenants').html(html);
                } else {
                    $('#available-tenants').html('<div class="alert alert-warning">No available tenants</div>');
                }
            }

            function populateTenantSelect(tenants) {
                const select = $('#tenant-select');
                select.empty().append('<option value="">Select a tenant...</option>');
                
                if (tenants && tenants.length > 0) {
                    tenants.forEach(function(tenant) {
                        select.append(`<option value="${tenant.id}">${tenant.name} (${tenant.role})</option>`);
                    });
                }
            }

            function displayTenantStatistics(statistics) {
                if (statistics) {
                    $('#tenant-statistics').html(`
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card bg-primary text-white">
                                    <div class="card-body">
                                        <h5>Users</h5>
                                        <h3>${statistics.users.current}/${statistics.users.max}</h3>
                                        <small>${statistics.users.percentage}% used</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-success text-white">
                                    <div class="card-body">
                                        <h5>Products</h5>
                                        <h3>${statistics.products.current}/${statistics.products.max}</h3>
                                        <small>${statistics.products.percentage}% used</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-info text-white">
                                    <div class="card-body">
                                        <h5>Storage</h5>
                                        <h3>${formatBytes(statistics.storage.current)}/${formatBytes(statistics.storage.max)}</h3>
                                        <small>${statistics.storage.percentage}% used</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `);
                } else {
                    $('#tenant-statistics').html('<div class="alert alert-warning">No statistics available</div>');
                }
            }

            function addTestResult(message, type) {
                const timestamp = new Date().toLocaleTimeString();
                $('#test-results').prepend(`
                    <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                        <strong>[${timestamp}]</strong> ${message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `);
            }

            function formatBytes(bytes) {
                if (bytes === 0) return '0 Bytes';
                const k = 1024;
                const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
