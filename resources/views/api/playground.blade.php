<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>YukiMart API Playground</title>
    <link rel="icon" type="image/png" href="https://unpkg.com/swagger-ui-dist@5.9.0/favicon-32x32.png" sizes="32x32" />
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f7fa;
            color: #333;
        }

        .header {
            background: linear-gradient(135deg, #009ef7 0%, #0056b3 100%);
            color: white;
            padding: 20px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 24px;
            font-weight: bold;
        }

        .nav-links {
            display: flex;
            gap: 20px;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 4px;
            transition: background 0.3s ease;
        }

        .nav-links a:hover {
            background: rgba(255,255,255,0.2);
        }

        .main-content {
            padding: 30px 0;
        }

        .playground-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }

        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .card-header {
            background: #009ef7;
            color: white;
            padding: 15px 20px;
            font-weight: bold;
            font-size: 16px;
        }

        .card-body {
            padding: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #555;
        }

        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: #009ef7;
            box-shadow: 0 0 0 3px rgba(0,158,247,0.1);
        }

        .form-control.code-editor {
            font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
            background: #f8f9fa;
            min-height: 120px;
            resize: vertical;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn-primary {
            background: #009ef7;
            color: white;
        }

        .btn-primary:hover {
            background: #0056b3;
            transform: translateY(-1px);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #545b62;
        }

        .btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }

        .btn-group {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .response-section {
            grid-column: 1 / -1;
        }

        .response-meta {
            display: flex;
            gap: 20px;
            margin-bottom: 15px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 6px;
            font-size: 14px;
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

        .response-content {
            background: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 20px;
            max-height: 400px;
            overflow-y: auto;
            font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
            font-size: 13px;
            line-height: 1.5;
        }

        .auth-section {
            background: #e8f5e8;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .auth-form {
            display: flex;
            gap: 10px;
            align-items: end;
        }

        .auth-form .form-control {
            flex: 1;
        }

        .token-display {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            padding: 10px;
            border-radius: 4px;
            font-family: monospace;
            font-size: 12px;
            word-break: break-all;
            margin-top: 10px;
        }

        .tabs {
            display: flex;
            border-bottom: 1px solid #ddd;
            margin-bottom: 20px;
        }

        .tab {
            padding: 12px 20px;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .tab.active {
            border-bottom-color: #009ef7;
            color: #009ef7;
        }

        .tab:hover {
            background: #f8f9fa;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .stat-number {
            font-size: 32px;
            font-weight: bold;
            color: #009ef7;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #666;
            font-size: 14px;
        }

        .footer {
            background: #2c3e50;
            color: white;
            text-align: center;
            padding: 20px 0;
            margin-top: 50px;
        }

        @media (max-width: 768px) {
            .playground-grid {
                grid-template-columns: 1fr;
            }
            
            .header-content {
                flex-direction: column;
                gap: 15px;
            }
            
            .nav-links {
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .btn-group {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">🧪 YukiMart API Playground</div>
                <nav class="nav-links">
                    <a href="{{ config('app.url') }}/api/v1/docs">📖 Documentation</a>
                    <a href="{{ config('app.url') }}/api/v1/health" target="_blank">💚 Health Check</a>
                    <a href="https://www.postman.com/collections/{{ config('api.documentation.postman_collection_id') }}" target="_blank">📮 Postman</a>
                </nav>
            </div>
        </div>
    </header>

    <main class="main-content">
        <div class="container">
            <!-- Authentication Section -->
            <div class="card">
                <div class="card-header">🔐 Authentication</div>
                <div class="card-body">
                    <div class="auth-section">
                        <div class="auth-form">
                            <input type="email" id="authEmail" class="form-control" placeholder="Email" value="yukimart@gmail.com">
                            <input type="password" id="authPassword" class="form-control" placeholder="Password" value="123456">
                            <button id="loginBtn" class="btn btn-primary">Login</button>
                            <button id="logoutBtn" class="btn btn-secondary" style="display: none;">Logout</button>
                        </div>
                        <div id="tokenDisplay" class="token-display" style="display: none;"></div>
                    </div>
                </div>
            </div>

            <!-- Main Playground -->
            <div class="playground-grid">
                <!-- Request Builder -->
                <div class="card">
                    <div class="card-header">📝 Request Builder</div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>HTTP Method</label>
                            <select id="httpMethod" class="form-control">
                                <option value="GET">GET</option>
                                <option value="POST">POST</option>
                                <option value="PUT">PUT</option>
                                <option value="PATCH">PATCH</option>
                                <option value="DELETE">DELETE</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Endpoint</label>
                            <input type="text" id="endpoint" class="form-control" placeholder="/health" value="health">
                        </div>

                        <div class="form-group">
                            <label>Headers (JSON)</label>
                            <textarea id="headers" class="form-control code-editor" placeholder='{"Authorization": "Bearer token"}'>{}</textarea>
                        </div>

                        <div class="form-group">
                            <label>Request Body (JSON)</label>
                            <textarea id="requestBody" class="form-control code-editor" placeholder='{"key": "value"}'></textarea>
                        </div>

                        <div class="form-group">
                            <label>Query Parameters (JSON)</label>
                            <textarea id="queryParams" class="form-control code-editor" placeholder='{"page": 1, "limit": 10}'></textarea>
                        </div>

                        <div class="btn-group">
                            <button id="executeRequest" class="btn btn-primary">🚀 Execute Request</button>
                            <button id="validateEndpoint" class="btn btn-secondary">✅ Validate</button>
                        </div>
                    </div>
                </div>

                <!-- Code Generator -->
                <div class="card">
                    <div class="card-header">💻 Code Generator</div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Select Language</label>
                            <select id="codeLanguage" class="form-control">
                                <option value="curl">cURL</option>
                                <option value="javascript">JavaScript</option>
                                <option value="dart">Dart/Flutter</option>
                                <option value="php">PHP</option>
                                <option value="python">Python</option>
                                <option value="java">Java</option>
                                <option value="swift">Swift</option>
                                <option value="kotlin">Kotlin</option>
                            </select>
                        </div>

                        <div class="btn-group">
                            <button id="generateCode" class="btn btn-primary">Generate Code</button>
                            <button id="copyCode" class="btn btn-secondary">📋 Copy</button>
                        </div>

                        <div class="form-group">
                            <textarea id="generatedCode" class="form-control code-editor" style="min-height: 300px;" readonly placeholder="Generated code will appear here..."></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Response Section -->
            <div class="card response-section">
                <div class="card-header">📊 Response Viewer</div>
                <div class="card-body">
                    <div id="responseMetaInfo" class="response-meta" style="display: none;">
                        <span>Status: <span id="responseStatus"></span></span>
                        <span>Time: <span id="responseTime"></span></span>
                        <span>Size: <span id="responseSize"></span></span>
                    </div>
                    <div id="responseContent" class="response-content">
                        <p style="color: #666; text-align: center; margin: 50px 0;">Execute a request to see the response here</p>
                    </div>
                </div>
            </div>

            <!-- Statistics Section -->
            <div class="card">
                <div class="card-header">📈 API Statistics</div>
                <div class="card-body">
                    <div id="playgroundStats">
                        <p style="color: #666; text-align: center; margin: 50px 0;">Loading statistics...</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 YukiMart API Playground. Built with ❤️ for developers.</p>
        </div>
    </footer>

    <script>
        // Same JavaScript functionality as in swagger.blade.php
        let currentToken = null;

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
                    document.getElementById('generatedCode').value = code;
                } else {
                    document.getElementById('generatedCode').value = 'Error: ' + result.message;
                }
                
            } catch (error) {
                document.getElementById('generatedCode').value = 'Error: ' + error.message;
            } finally {
                this.disabled = false;
                this.textContent = 'Generate Code';
            }
        });

        // Copy code
        document.getElementById('copyCode').addEventListener('click', function() {
            const codeTextarea = document.getElementById('generatedCode');
            codeTextarea.select();
            document.execCommand('copy');
            
            const originalText = this.textContent;
            this.textContent = '✅ Copied!';
            setTimeout(() => {
                this.textContent = originalText;
            }, 2000);
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
                        <div class="stats-grid">
                            <div class="stat-card">
                                <div class="stat-number">${stats.total_requests}</div>
                                <div class="stat-label">Total Requests</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-number">${stats.successful_requests}</div>
                                <div class="stat-label">Successful</div>
                            </div>
                            <div class="stat-card">
                                <div class="stat-number">${stats.average_response_time}</div>
                                <div class="stat-label">Avg Response</div>
                            </div>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <div>
                                <h4 style="margin-bottom: 15px; color: #009ef7;">Popular Endpoints</h4>
                                <div style="background: #f8f9fa; padding: 15px; border-radius: 8px;">
                                    ${Object.entries(stats.popular_endpoints).map(([endpoint, count]) => 
                                        `<div style="display: flex; justify-content: space-between; margin: 8px 0; padding: 5px 0; border-bottom: 1px solid #eee;">
                                            <span style="font-family: monospace;">${endpoint}</span>
                                            <span style="font-weight: bold; color: #009ef7;">${count}</span>
                                        </div>`
                                    ).join('')}
                                </div>
                            </div>
                            <div>
                                <h4 style="margin-bottom: 15px; color: #009ef7;">Popular Languages</h4>
                                <div style="background: #f8f9fa; padding: 15px; border-radius: 8px;">
                                    ${Object.entries(stats.popular_languages).map(([lang, percentage]) => 
                                        `<div style="display: flex; justify-content: space-between; margin: 8px 0; padding: 5px 0; border-bottom: 1px solid #eee;">
                                            <span style="text-transform: capitalize;">${lang}</span>
                                            <span style="font-weight: bold; color: #28a745;">${percentage}%</span>
                                        </div>`
                                    ).join('')}
                                </div>
                            </div>
                        </div>
                    `;
                }
            } catch (error) {
                document.getElementById('playgroundStats').innerHTML = 
                    '<p style="color: #dc3545; text-align: center;">Failed to load statistics</p>';
            }
        }

        // Load stats on page load
        loadPlaygroundStats();
    </script>
</body>
</html>
