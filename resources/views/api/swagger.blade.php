<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>YukiMart API Documentation</title>
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/swagger-ui-dist@5.9.0/swagger-ui.css" />
    <link rel="icon" type="image/png" href="https://unpkg.com/swagger-ui-dist@5.9.0/favicon-32x32.png" sizes="32x32" />
    <link rel="icon" type="image/png" href="https://unpkg.com/swagger-ui-dist@5.9.0/favicon-16x16.png" sizes="16x16" />
    <style>
        html {
            box-sizing: border-box;
            overflow: -moz-scrollbars-vertical;
            overflow-y: scroll;
        }

        *, *:before, *:after {
            box-sizing: inherit;
        }

        body {
            margin: 0;
            background: #fafafa;
        }

        .swagger-ui .topbar {
            background-color: #009ef7;
            border-bottom: 1px solid #009ef7;
        }

        .swagger-ui .topbar .download-url-wrapper .select-label {
            color: #fff;
        }

        .swagger-ui .topbar .download-url-wrapper input[type=text] {
            border: 2px solid #fff;
        }

        .swagger-ui .topbar .download-url-wrapper .download-url-button {
            background: #fff;
            color: #009ef7;
            border: 2px solid #fff;
        }

        .swagger-ui .info .title {
            color: #009ef7;
        }

        .swagger-ui .scheme-container {
            background: #fff;
            box-shadow: 0 1px 2px 0 rgba(0,0,0,.15);
        }

        .custom-header {
            background: linear-gradient(135deg, #009ef7 0%, #0056b3 100%);
            color: white;
            padding: 20px;
            text-align: center;
            margin-bottom: 20px;
        }

        .custom-header h1 {
            margin: 0;
            font-size: 2.5em;
            font-weight: 300;
        }

        .custom-header p {
            margin: 10px 0 0 0;
            font-size: 1.2em;
            opacity: 0.9;
        }

        .api-stats {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-top: 20px;
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            font-size: 2em;
            font-weight: bold;
            display: block;
        }

        .stat-label {
            font-size: 0.9em;
            opacity: 0.8;
        }

        .quick-links {
            background: white;
            padding: 20px;
            margin: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .quick-links h3 {
            color: #009ef7;
            margin-top: 0;
        }

        .link-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }

        .link-item {
            padding: 15px;
            background: #f8f9fa;
            border-radius: 6px;
            text-decoration: none;
            color: #333;
            border-left: 4px solid #009ef7;
            transition: all 0.3s ease;
        }

        .link-item:hover {
            background: #e3f2fd;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .link-title {
            font-weight: bold;
            color: #009ef7;
            margin-bottom: 5px;
        }

        .link-desc {
            font-size: 0.9em;
            color: #666;
        }

        .playground-section {
            background: white;
            margin: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .playground-header {
            background: linear-gradient(135deg, #009ef7 0%, #0056b3 100%);
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .playground-content {
            padding: 20px;
        }

        .playground-tabs {
            display: flex;
            border-bottom: 1px solid #e0e0e0;
            margin-bottom: 20px;
        }

        .playground-tab {
            padding: 10px 20px;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            transition: all 0.3s ease;
        }

        .playground-tab.active {
            border-bottom-color: #009ef7;
            color: #009ef7;
            font-weight: bold;
        }

        .playground-tab:hover {
            background: #f5f5f5;
        }

        .playground-panel {
            display: none;
        }

        .playground-panel.active {
            display: block;
        }

        .code-editor {
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
            font-size: 14px;
            min-height: 200px;
            padding: 15px;
            background: #f8f9fa;
        }

        .response-viewer {
            border: 1px solid #ddd;
            border-radius: 4px;
            background: #f8f9fa;
            padding: 15px;
            max-height: 400px;
            overflow-y: auto;
        }

        .btn-playground {
            background: #009ef7;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: background 0.3s ease;
        }

        .btn-playground:hover {
            background: #0056b3;
        }

        .btn-playground:disabled {
            background: #ccc;
            cursor: not-allowed;
        }

        .language-selector {
            margin-bottom: 15px;
        }

        .language-selector select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background: white;
        }

        .status-indicator {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 8px;
        }

        .status-success { background: #28a745; }
        .status-error { background: #dc3545; }
        .status-warning { background: #ffc107; }

        .response-meta {
            display: flex;
            gap: 20px;
            margin-bottom: 15px;
            font-size: 14px;
            color: #666;
        }

        .auth-section {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .auth-form {
            display: flex;
            gap: 10px;
            align-items: end;
        }

        .auth-form input {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            flex: 1;
        }

        .token-display {
            background: #e8f5e8;
            padding: 10px;
            border-radius: 4px;
            font-family: monospace;
            font-size: 12px;
            word-break: break-all;
            margin-top: 10px;
        }
    </style>
</head>

<body>
    <div class="custom-header">
        <h1>🚀 YukiMart API v1</h1>
        <p>Complete RESTful API Documentation for Mobile Application</p>
        <div class="api-stats">
            <div class="stat-item">
                <span class="stat-number">65+</span>
                <span class="stat-label">Endpoints</span>
            </div>
            <div class="stat-item">
                <span class="stat-number">8</span>
                <span class="stat-label">Modules</span>
            </div>
            <div class="stat-item">
                <span class="stat-number">100%</span>
                <span class="stat-label">Coverage</span>
            </div>
        </div>
    </div>

    <div class="quick-links">
        <h3>🔗 Quick Links</h3>
        <div class="link-grid">
            <a href="{{ config('app.url') }}/api/v1/docs/openapi" class="link-item" target="_blank">
                <div class="link-title">📄 OpenAPI Specification</div>
                <div class="link-desc">Download OpenAPI 3.0 JSON specification</div>
            </a>
            <a href="https://www.postman.com/collections/{{ config('api.documentation.postman_collection_id') }}" class="link-item" target="_blank">
                <div class="link-title">📮 Postman Collection</div>
                <div class="link-desc">Ready-to-use Postman collection with examples</div>
            </a>
            <a href="{{ config('app.url') }}/api/v1/health" class="link-item" target="_blank">
                <div class="link-title">💚 Health Check</div>
                <div class="link-desc">API health status and system information</div>
            </a>
            <a href="{{ config('app.url') }}/api/v1/docs/info" class="link-item" target="_blank">
                <div class="link-title">ℹ️ API Information</div>
                <div class="link-desc">Detailed API statistics and endpoint counts</div>
            </a>
        </div>
    </div>

    <!-- API Playground Section -->
    <div class="playground-section">
        <div class="playground-header">
            <h3>🧪 API Playground</h3>
            <button id="togglePlayground" class="btn-playground">Show Playground</button>
        </div>
        <div id="playgroundContent" class="playground-content" style="display: none;">
            <!-- Authentication Section -->
            <div class="auth-section">
                <h4>🔐 Authentication</h4>
                <div class="auth-form">
                    <input type="email" id="authEmail" placeholder="Email" value="yukimart@gmail.com">
                    <input type="password" id="authPassword" placeholder="Password" value="123456">
                    <button id="loginBtn" class="btn-playground">Login</button>
                    <button id="logoutBtn" class="btn-playground" style="display: none;">Logout</button>
                </div>
                <div id="tokenDisplay" class="token-display" style="display: none;"></div>
            </div>

            <!-- Playground Tabs -->
            <div class="playground-tabs">
                <div class="playground-tab active" data-tab="request">📝 Request Builder</div>
                <div class="playground-tab" data-tab="response">📊 Response Viewer</div>
                <div class="playground-tab" data-tab="code">💻 Code Generator</div>
                <div class="playground-tab" data-tab="stats">📈 Statistics</div>
            </div>

            <!-- Request Builder Panel -->
            <div id="requestPanel" class="playground-panel active">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div>
                        <label>HTTP Method:</label>
                        <select id="httpMethod" style="width: 100%; padding: 8px; margin-top: 5px;">
                            <option value="GET">GET</option>
                            <option value="POST">POST</option>
                            <option value="PUT">PUT</option>
                            <option value="PATCH">PATCH</option>
                            <option value="DELETE">DELETE</option>
                        </select>
                    </div>
                    <div>
                        <label>Endpoint:</label>
                        <input type="text" id="endpoint" placeholder="/products" style="width: 100%; padding: 8px; margin-top: 5px;">
                    </div>
                </div>

                <div style="margin-bottom: 20px;">
                    <label>Headers (JSON):</label>
                    <textarea id="headers" class="code-editor" style="height: 100px;" placeholder='{"Authorization": "Bearer your-token"}'></textarea>
                </div>

                <div style="margin-bottom: 20px;">
                    <label>Request Body (JSON):</label>
                    <textarea id="requestBody" class="code-editor" placeholder='{"key": "value"}'></textarea>
                </div>

                <div style="margin-bottom: 20px;">
                    <label>Query Parameters (JSON):</label>
                    <textarea id="queryParams" class="code-editor" style="height: 80px;" placeholder='{"page": 1, "limit": 10}'></textarea>
                </div>

                <button id="executeRequest" class="btn-playground">🚀 Execute Request</button>
                <button id="validateEndpoint" class="btn-playground" style="margin-left: 10px;">✅ Validate Endpoint</button>
            </div>

            <!-- Response Viewer Panel -->
            <div id="responsePanel" class="playground-panel">
                <div id="responseMetaInfo" class="response-meta" style="display: none;">
                    <span>Status: <span id="responseStatus"></span></span>
                    <span>Time: <span id="responseTime"></span></span>
                    <span>Size: <span id="responseSize"></span></span>
                </div>
                <div id="responseContent" class="response-viewer">
                    <p style="color: #666; text-align: center; margin: 50px 0;">Execute a request to see the response here</p>
                </div>
            </div>

            <!-- Code Generator Panel -->
            <div id="codePanel" class="playground-panel">
                <div class="language-selector">
                    <label>Select Language:</label>
                    <select id="codeLanguage">
                        <option value="curl">cURL</option>
                        <option value="javascript">JavaScript</option>
                        <option value="dart">Dart/Flutter</option>
                        <option value="php">PHP</option>
                        <option value="python">Python</option>
                        <option value="java">Java</option>
                        <option value="swift">Swift</option>
                        <option value="kotlin">Kotlin</option>
                    </select>
                    <button id="generateCode" class="btn-playground" style="margin-left: 10px;">Generate Code</button>
                </div>
                <div id="generatedCode" class="code-editor" style="min-height: 300px;">
                    <p style="color: #666; text-align: center; margin: 50px 0;">Configure your request and click "Generate Code" to see examples</p>
                </div>
            </div>

            <!-- Statistics Panel -->
            <div id="statsPanel" class="playground-panel">
                <div id="playgroundStats">
                    <p style="color: #666; text-align: center; margin: 50px 0;">Loading statistics...</p>
                </div>
            </div>
        </div>
    </div>

    <div id="swagger-ui"></div>

    <script src="https://unpkg.com/swagger-ui-dist@5.9.0/swagger-ui-bundle.js"></script>
    <script src="https://unpkg.com/swagger-ui-dist@5.9.0/swagger-ui-standalone-preset.js"></script>
    <script>
        window.onload = function() {
            // Begin Swagger UI call region
            const ui = SwaggerUIBundle({
                url: '{{ config("app.url") }}/api/v1/docs/openapi',
                dom_id: '#swagger-ui',
                deepLinking: true,
                presets: [
                    SwaggerUIBundle.presets.apis,
                    SwaggerUIStandalonePreset
                ],
                plugins: [
                    SwaggerUIBundle.plugins.DownloadUrl
                ],
                layout: "StandaloneLayout",
                validatorUrl: null,
                tryItOutEnabled: true,
                supportedSubmitMethods: ['get', 'post', 'put', 'delete', 'patch'],
                onComplete: function() {
                    console.log('YukiMart API Documentation loaded successfully!');
                },
                requestInterceptor: function(request) {
                    // Add custom headers if needed
                    request.headers['Accept'] = 'application/json';
                    return request;
                },
                responseInterceptor: function(response) {
                    // Log API responses for debugging
                    console.log('API Response:', response);
                    return response;
                },
                docExpansion: 'list',
                apisSorter: 'alpha',
                operationsSorter: 'alpha',
                defaultModelsExpandDepth: 1,
                defaultModelExpandDepth: 1,
                showExtensions: true,
                showCommonExtensions: true,
                filter: true,
                syntaxHighlight: {
                    activated: true,
                    theme: 'agate'
                }
            });

            // Custom styling after load
            setTimeout(function() {
                // Add custom logo
                const topbar = document.querySelector('.swagger-ui .topbar');
                if (topbar) {
                    const logo = document.createElement('div');
                    logo.innerHTML = '<span style="color: white; font-weight: bold; font-size: 1.2em;">🛒 YukiMart API</span>';
                    logo.style.cssText = 'position: absolute; left: 20px; top: 50%; transform: translateY(-50%);';
                    topbar.style.position = 'relative';
                    topbar.appendChild(logo);
                }

                // Add version badge
                const info = document.querySelector('.swagger-ui .info');
                if (info) {
                    const badge = document.createElement('div');
                    badge.innerHTML = '<span style="background: #009ef7; color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.8em;">v1.0.0</span>';
                    badge.style.cssText = 'margin-top: 10px;';
                    info.appendChild(badge);
                }
            }, 1000);

            // End Swagger UI call region
            window.ui = ui;
        };

        // API Playground functionality
        let currentToken = null;

        // Toggle playground visibility
        document.getElementById('togglePlayground').addEventListener('click', function() {
            const content = document.getElementById('playgroundContent');
            const button = this;

            if (content.style.display === 'none') {
                content.style.display = 'block';
                button.textContent = 'Hide Playground';
                loadPlaygroundStats();
            } else {
                content.style.display = 'none';
                button.textContent = 'Show Playground';
            }
        });

        // Tab switching
        document.querySelectorAll('.playground-tab').forEach(tab => {
            tab.addEventListener('click', function() {
                const targetTab = this.dataset.tab;

                // Update active tab
                document.querySelectorAll('.playground-tab').forEach(t => t.classList.remove('active'));
                this.classList.add('active');

                // Update active panel
                document.querySelectorAll('.playground-panel').forEach(p => p.classList.remove('active'));
                document.getElementById(targetTab + 'Panel').classList.add('active');

                // Load stats when stats tab is clicked
                if (targetTab === 'stats') {
                    loadPlaygroundStats();
                }
            });
        });

        // Authentication
        document.getElementById('loginBtn').addEventListener('click', async function() {
            const email = document.getElementById('authEmail').value;
            const password = document.getElementById('authPassword').value;

            if (!email || !password) {
                alert('Please enter email and password');
                return;
            }

            this.disabled = true;
            this.textContent = 'Logging in...';

            try {
                const response = await fetch('{{ config("app.url") }}/api/v1/playground/auth', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ email, password })
                });

                const data = await response.json();

                if (data.success) {
                    currentToken = data.data.token;
                    document.getElementById('tokenDisplay').style.display = 'block';
                    document.getElementById('tokenDisplay').textContent = `Token: ${currentToken}`;
                    document.getElementById('loginBtn').style.display = 'none';
                    document.getElementById('logoutBtn').style.display = 'inline-block';

                    // Update headers with token
                    const headersTextarea = document.getElementById('headers');
                    const headers = headersTextarea.value ? JSON.parse(headersTextarea.value) : {};
                    headers.Authorization = `Bearer ${currentToken}`;
                    headersTextarea.value = JSON.stringify(headers, null, 2);

                    alert('Login successful!');
                } else {
                    alert('Login failed: ' + data.message);
                }
            } catch (error) {
                alert('Login error: ' + error.message);
            } finally {
                this.disabled = false;
                this.textContent = 'Login';
            }
        });

        // Logout
        document.getElementById('logoutBtn').addEventListener('click', function() {
            currentToken = null;
            document.getElementById('tokenDisplay').style.display = 'none';
            document.getElementById('loginBtn').style.display = 'inline-block';
            document.getElementById('logoutBtn').style.display = 'none';

            // Clear token from headers
            const headersTextarea = document.getElementById('headers');
            try {
                const headers = JSON.parse(headersTextarea.value || '{}');
                delete headers.Authorization;
                headersTextarea.value = JSON.stringify(headers, null, 2);
            } catch (e) {
                headersTextarea.value = '{}';
            }
        });

        // Execute API request
        document.getElementById('executeRequest').addEventListener('click', async function() {
            const method = document.getElementById('httpMethod').value;
            const endpoint = document.getElementById('endpoint').value;
            const headersText = document.getElementById('headers').value;
            const bodyText = document.getElementById('requestBody').value;
            const queryParamsText = document.getElementById('queryParams').value;

            if (!endpoint) {
                alert('Please enter an endpoint');
                return;
            }

            this.disabled = true;
            this.textContent = 'Executing...';

            try {
                const requestData = {
                    method: method,
                    endpoint: endpoint,
                    headers: headersText ? JSON.parse(headersText) : {},
                    body: bodyText ? JSON.parse(bodyText) : {},
                    query_params: queryParamsText ? JSON.parse(queryParamsText) : {}
                };

                const response = await fetch('{{ config("app.url") }}/api/v1/playground/execute', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(requestData)
                });

                const result = await response.json();
                displayResponse(result);

                // Switch to response tab
                document.querySelector('[data-tab="response"]').click();

            } catch (error) {
                displayResponse({
                    success: false,
                    message: 'Request failed',
                    data: { error: error.message }
                });
            } finally {
                this.disabled = false;
                this.textContent = '🚀 Execute Request';
            }
        });

        // Validate endpoint
        document.getElementById('validateEndpoint').addEventListener('click', async function() {
            const method = document.getElementById('httpMethod').value;
            const endpoint = document.getElementById('endpoint').value;

            if (!endpoint) {
                alert('Please enter an endpoint');
                return;
            }

            this.disabled = true;
            this.textContent = 'Validating...';

            try {
                const response = await fetch('{{ config("app.url") }}/api/v1/playground/validate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ method, endpoint })
                });

                const result = await response.json();

                if (result.success && result.data.is_valid) {
                    alert('✅ Endpoint is valid!');
                } else {
                    alert('❌ Endpoint not found. Check the endpoint path and method.');
                }

            } catch (error) {
                alert('Validation error: ' + error.message);
            } finally {
                this.disabled = false;
                this.textContent = '✅ Validate Endpoint';
            }
        });

        // Generate code
        document.getElementById('generateCode').addEventListener('click', async function() {
            const method = document.getElementById('httpMethod').value;
            const endpoint = document.getElementById('endpoint').value;
            const headersText = document.getElementById('headers').value;
            const bodyText = document.getElementById('requestBody').value;
            const queryParamsText = document.getElementById('queryParams').value;
            const language = document.getElementById('codeLanguage').value;

            if (!endpoint) {
                alert('Please configure your request first');
                return;
            }

            this.disabled = true;
            this.textContent = 'Generating...';

            try {
                const requestData = {
                    method: method,
                    endpoint: endpoint,
                    headers: headersText ? JSON.parse(headersText) : {},
                    body: bodyText ? JSON.parse(bodyText) : {},
                    query_params: queryParamsText ? JSON.parse(queryParamsText) : {},
                    languages: [language]
                };

                const response = await fetch('{{ config("app.url") }}/api/v1/playground/generate-code', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(requestData)
                });

                const result = await response.json();

                if (result.success) {
                    const code = result.data.code_examples[language];
                    document.getElementById('generatedCode').textContent = code;
                } else {
                    document.getElementById('generatedCode').textContent = 'Error: ' + result.message;
                }

            } catch (error) {
                document.getElementById('generatedCode').textContent = 'Error: ' + error.message;
            } finally {
                this.disabled = false;
                this.textContent = 'Generate Code';
            }
        });

        // Display response
        function displayResponse(result) {
            const metaInfo = document.getElementById('responseMetaInfo');
            const content = document.getElementById('responseContent');

            if (result.success && result.data) {
                const data = result.data;

                // Update meta info
                document.getElementById('responseStatus').innerHTML =
                    `<span class="status-indicator status-${data.success ? 'success' : 'error'}"></span>${data.status_code}`;
                document.getElementById('responseTime').textContent = data.response_time_ms + 'ms';
                document.getElementById('responseSize').textContent =
                    new Blob([JSON.stringify(data.body)]).size + ' bytes';

                metaInfo.style.display = 'flex';

                // Update content
                content.innerHTML = `<pre><code>${JSON.stringify(data.body, null, 2)}</code></pre>`;
            } else {
                metaInfo.style.display = 'none';
                content.innerHTML = `<pre><code>${JSON.stringify(result, null, 2)}</code></pre>`;
            }
        }

        // Load playground statistics
        async function loadPlaygroundStats() {
            try {
                const response = await fetch('{{ config("app.url") }}/api/v1/playground/stats');
                const result = await response.json();

                if (result.success) {
                    const stats = result.data;
                    document.getElementById('playgroundStats').innerHTML = `
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                            <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; text-align: center;">
                                <h4 style="margin: 0; color: #009ef7;">Total Requests</h4>
                                <p style="font-size: 24px; font-weight: bold; margin: 10px 0;">${stats.total_requests}</p>
                            </div>
                            <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; text-align: center;">
                                <h4 style="margin: 0; color: #28a745;">Successful</h4>
                                <p style="font-size: 24px; font-weight: bold; margin: 10px 0;">${stats.successful_requests}</p>
                            </div>
                            <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; text-align: center;">
                                <h4 style="margin: 0; color: #ffc107;">Avg Response</h4>
                                <p style="font-size: 24px; font-weight: bold; margin: 10px 0;">${stats.average_response_time}</p>
                            </div>
                        </div>
                        <div style="margin-top: 20px;">
                            <h4>Popular Endpoints</h4>
                            <div style="background: #f8f9fa; padding: 15px; border-radius: 8px;">
                                ${Object.entries(stats.popular_endpoints).map(([endpoint, count]) =>
                                    `<div style="display: flex; justify-content: space-between; margin: 5px 0;">
                                        <span>${endpoint}</span>
                                        <span style="font-weight: bold;">${count} requests</span>
                                    </div>`
                                ).join('')}
                            </div>
                        </div>
                        <div style="margin-top: 20px;">
                            <h4>Popular Languages</h4>
                            <div style="background: #f8f9fa; padding: 15px; border-radius: 8px;">
                                ${Object.entries(stats.popular_languages).map(([lang, percentage]) =>
                                    `<div style="display: flex; justify-content: space-between; margin: 5px 0;">
                                        <span>${lang}</span>
                                        <span style="font-weight: bold;">${percentage}%</span>
                                    </div>`
                                ).join('')}
                            </div>
                        </div>
                    `;
                }
            } catch (error) {
                document.getElementById('playgroundStats').innerHTML =
                    '<p style="color: #dc3545; text-align: center;">Failed to load statistics</p>';
            }
        }
    </script>
</body>
</html>
