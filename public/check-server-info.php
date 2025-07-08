<?php
// Tạo file này trong thư mục public để kiểm tra server info
echo "<h2>Server Information</h2>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Setting</th><th>Value</th></tr>";

$server_info = [
    'Server Software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
    'Server Name' => $_SERVER['SERVER_NAME'] ?? 'Unknown',
    'Server Port' => $_SERVER['SERVER_PORT'] ?? 'Unknown',
    'Request Method' => $_SERVER['REQUEST_METHOD'] ?? 'Unknown',
    'HTTP Host' => $_SERVER['HTTP_HOST'] ?? 'Unknown',
    'Document Root' => $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown',
    'Script Name' => $_SERVER['SCRIPT_NAME'] ?? 'Unknown',
    'PHP Version' => PHP_VERSION,
    'PHP SAPI' => php_sapi_name(),
    'Operating System' => PHP_OS,
    'PHP Config File' => php_ini_loaded_file(),
];

foreach ($server_info as $key => $value) {
    echo "<tr><td><strong>{$key}</strong></td><td>{$value}</td></tr>";
}

echo "</table>";

echo "<h3>Environment Detection</h3>";
if (strpos($_SERVER['SERVER_SOFTWARE'] ?? '', 'Apache') !== false) {
    echo "<p>🔴 <strong>Apache Server Detected</strong></p>";
    echo "<p>Check .htaccess file and Apache configuration</p>";
} elseif (strpos($_SERVER['SERVER_SOFTWARE'] ?? '', 'nginx') !== false) {
    echo "<p>🔵 <strong>Nginx Server Detected</strong></p>";
    echo "<p>Check nginx.conf configuration</p>";
} elseif (isset($_SERVER['SERVER_SOFTWARE']) && strpos($_SERVER['SERVER_SOFTWARE'], 'Microsoft-IIS') !== false) {
    echo "<p>🟡 <strong>IIS Server Detected</strong></p>";
    echo "<p>Check web.config file</p>";
} else {
    echo "<p>❓ <strong>Unknown Server</strong></p>";
    echo "<p>Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Not detected') . "</p>";
}

echo "<h3>Request Headers</h3>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Header</th><th>Value</th></tr>";

$headers = getallheaders();
foreach ($headers as $name => $value) {
    echo "<tr><td><strong>{$name}</strong></td><td>{$value}</td></tr>";
}

echo "</table>";

echo "<h3>Upload Test</h3>";
echo '<form method="post" enctype="multipart/form-data" style="margin: 20px 0;">';
echo '<p>Select a small file (< 1MB) to test:</p>';
echo '<input type="file" name="test_file" accept=".txt,.csv,.xlsx" style="margin: 10px 0;">';
echo '<br><input type="submit" value="Test Upload" name="test_upload" style="padding: 10px 20px;">';
echo '</form>';

if (isset($_POST['test_upload'])) {
    echo "<h3>Upload Test Result</h3>";
    
    if (isset($_FILES['test_file'])) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>Property</th><th>Value</th></tr>";
        echo "<tr><td>File Name</td><td>" . ($_FILES['test_file']['name'] ?? 'N/A') . "</td></tr>";
        echo "<tr><td>File Size</td><td>" . ($_FILES['test_file']['size'] ?? 'N/A') . " bytes</td></tr>";
        echo "<tr><td>File Type</td><td>" . ($_FILES['test_file']['type'] ?? 'N/A') . "</td></tr>";
        echo "<tr><td>Temp Name</td><td>" . ($_FILES['test_file']['tmp_name'] ?? 'N/A') . "</td></tr>";
        echo "<tr><td>Error Code</td><td>" . ($_FILES['test_file']['error'] ?? 'N/A') . "</td></tr>";
        echo "</table>";
        
        if ($_FILES['test_file']['error'] === UPLOAD_ERR_OK) {
            echo "<p style='color: green;'>✅ <strong>Upload successful!</strong></p>";
            // Clean up
            if (file_exists($_FILES['test_file']['tmp_name'])) {
                unlink($_FILES['test_file']['tmp_name']);
            }
        } else {
            echo "<p style='color: red;'>❌ <strong>Upload failed!</strong></p>";
            echo "<p>Error code: " . $_FILES['test_file']['error'] . "</p>";
            echo "<p>Error meaning: " . getUploadErrorMessage($_FILES['test_file']['error']) . "</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ <strong>No file data received!</strong></p>";
        echo "<p>This indicates a server-level issue blocking the upload.</p>";
    }
}

echo "<h3>Recommended Actions</h3>";
if (strpos($_SERVER['SERVER_SOFTWARE'] ?? '', 'Apache') !== false) {
    echo "<div style='background: #f0f0f0; padding: 15px; margin: 10px 0;'>";
    echo "<h4>Apache Server - Next Steps:</h4>";
    echo "<ol>";
    echo "<li>Restart Apache server</li>";
    echo "<li>Check Apache error logs</li>";
    echo "<li>Verify .htaccess is being read</li>";
    echo "<li>Check Apache modules (mod_rewrite, mod_php)</li>";
    echo "</ol>";
    echo "</div>";
} elseif (strpos($_SERVER['SERVER_SOFTWARE'] ?? '', 'nginx') !== false) {
    echo "<div style='background: #f0f0f0; padding: 15px; margin: 10px 0;'>";
    echo "<h4>Nginx Server - Next Steps:</h4>";
    echo "<ol>";
    echo "<li>Add client_max_body_size 100M; to nginx.conf</li>";
    echo "<li>Restart Nginx server</li>";
    echo "<li>Check Nginx error logs</li>";
    echo "<li>Verify PHP-FPM configuration</li>";
    echo "</ol>";
    echo "</div>";
}

function getUploadErrorMessage($error_code) {
    switch ($error_code) {
        case UPLOAD_ERR_INI_SIZE:
            return 'File exceeds upload_max_filesize directive in php.ini';
        case UPLOAD_ERR_FORM_SIZE:
            return 'File exceeds MAX_FILE_SIZE directive in HTML form';
        case UPLOAD_ERR_PARTIAL:
            return 'File was only partially uploaded';
        case UPLOAD_ERR_NO_FILE:
            return 'No file was uploaded';
        case UPLOAD_ERR_NO_TMP_DIR:
            return 'Missing temporary folder';
        case UPLOAD_ERR_CANT_WRITE:
            return 'Failed to write file to disk';
        case UPLOAD_ERR_EXTENSION:
            return 'File upload stopped by extension';
        default:
            return 'Unknown upload error';
    }
}
?>
