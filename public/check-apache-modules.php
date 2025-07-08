<?php
// Check Apache modules and configuration
echo "<h2>Apache Configuration Check</h2>";

// Check if running on Apache
if (strpos($_SERVER['SERVER_SOFTWARE'] ?? '', 'Apache') === false) {
    echo "<p style='color: orange;'>⚠️ Not running on Apache server.</p>";
    echo "<p>Server: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "</p>";
    exit;
}

echo "<p style='color: green;'>✅ Apache server detected</p>";
echo "<p>Server: " . $_SERVER['SERVER_SOFTWARE'] . "</p>";

// Check Apache modules
echo "<h3>Apache Modules</h3>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Module</th><th>Status</th><th>Description</th></tr>";

$modules = [
    'mod_rewrite' => 'URL rewriting (required for Laravel)',
    'mod_php' => 'PHP processing',
    'mod_ssl' => 'SSL/HTTPS support',
    'mod_headers' => 'HTTP headers manipulation',
    'mod_deflate' => 'Content compression',
];

if (function_exists('apache_get_modules')) {
    $loaded_modules = apache_get_modules();
    
    foreach ($modules as $module => $description) {
        $status = in_array($module, $loaded_modules) ? '✅ Loaded' : '❌ Not loaded';
        $color = in_array($module, $loaded_modules) ? 'green' : 'red';
        echo "<tr><td><strong>{$module}</strong></td><td style='color: {$color};'>{$status}</td><td>{$description}</td></tr>";
    }
    
    echo "</table>";
    
    echo "<h3>All Loaded Modules</h3>";
    echo "<div style='background: #f5f5f5; padding: 10px; max-height: 200px; overflow-y: auto;'>";
    foreach ($loaded_modules as $module) {
        echo $module . "<br>";
    }
    echo "</div>";
    
} else {
    echo "<tr><td colspan='3'>❌ apache_get_modules() function not available</td></tr>";
    echo "</table>";
    echo "<p>This might indicate PHP is not running as Apache module.</p>";
}

// Check .htaccess
echo "<h3>.htaccess File Check</h3>";
$htaccess_path = __DIR__ . '/.htaccess';

if (file_exists($htaccess_path)) {
    echo "<p style='color: green;'>✅ .htaccess file exists</p>";
    echo "<p>Path: {$htaccess_path}</p>";
    
    $htaccess_content = file_get_contents($htaccess_path);
    $has_upload_config = strpos($htaccess_content, 'upload_max_filesize') !== false;
    
    if ($has_upload_config) {
        echo "<p style='color: green;'>✅ Upload configuration found in .htaccess</p>";
    } else {
        echo "<p style='color: red;'>❌ No upload configuration in .htaccess</p>";
    }
    
    echo "<h4>.htaccess Content:</h4>";
    echo "<pre style='background: #f5f5f5; padding: 10px; max-height: 300px; overflow-y: auto;'>";
    echo htmlspecialchars($htaccess_content);
    echo "</pre>";
    
} else {
    echo "<p style='color: red;'>❌ .htaccess file not found</p>";
    echo "<p>Expected path: {$htaccess_path}</p>";
}

// Check if .htaccess is being processed
echo "<h3>.htaccess Processing Test</h3>";
if (isset($_GET['htaccess_test'])) {
    echo "<p style='color: green;'>✅ .htaccess is being processed (this page loaded with custom parameter)</p>";
} else {
    echo "<p><a href='?htaccess_test=1'>Click here to test if .htaccess is working</a></p>";
}

// Apache configuration recommendations
echo "<h3>Recommendations</h3>";
echo "<div style='background: #e7f3ff; padding: 15px; border-left: 4px solid #2196F3;'>";
echo "<h4>If still getting 413 errors:</h4>";
echo "<ol>";
echo "<li><strong>Restart Apache:</strong> <code>sudo systemctl restart apache2</code></li>";
echo "<li><strong>Check Apache error log:</strong> <code>tail -f /var/log/apache2/error.log</code></li>";
echo "<li><strong>Verify .htaccess is readable:</strong> <code>chmod 644 .htaccess</code></li>";
echo "<li><strong>Test with minimal .htaccess:</strong> Temporarily rename .htaccess and test</li>";
echo "<li><strong>Check Apache virtual host config</strong> for any conflicting directives</li>";
echo "</ol>";
echo "</div>";

// XAMPP/WAMP specific
if (strpos(__DIR__, 'xampp') !== false || strpos(__DIR__, 'wamp') !== false) {
    echo "<div style='background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin-top: 15px;'>";
    echo "<h4>XAMPP/WAMP Detected</h4>";
    echo "<p><strong>Additional steps:</strong></p>";
    echo "<ul>";
    echo "<li>Edit <code>php.ini</code> in XAMPP/WAMP control panel</li>";
    echo "<li>Restart Apache from control panel</li>";
    echo "<li>Check if .htaccess override is enabled in httpd.conf</li>";
    echo "</ul>";
    echo "</div>";
}

echo "<h3>Quick Commands</h3>";
echo "<div style='background: #f8f9fa; padding: 15px; font-family: monospace;'>";
echo "# Check Apache status<br>";
echo "sudo systemctl status apache2<br><br>";
echo "# Restart Apache<br>";
echo "sudo systemctl restart apache2<br><br>";
echo "# Check Apache error log<br>";
echo "sudo tail -f /var/log/apache2/error.log<br><br>";
echo "# Test Apache configuration<br>";
echo "sudo apache2ctl configtest<br>";
echo "</div>";
?>
