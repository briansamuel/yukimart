<?php
// Tạo file này trong thư mục public để kiểm tra cấu hình PHP
echo "<h2>PHP Upload Configuration Check</h2>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Setting</th><th>Current Value</th><th>Recommended</th><th>Status</th></tr>";

$settings = [
    'upload_max_filesize' => ['current' => ini_get('upload_max_filesize'), 'recommended' => '100M'],
    'post_max_size' => ['current' => ini_get('post_max_size'), 'recommended' => '100M'],
    'max_execution_time' => ['current' => ini_get('max_execution_time'), 'recommended' => '300'],
    'memory_limit' => ['current' => ini_get('memory_limit'), 'recommended' => '512M'],
    'max_input_time' => ['current' => ini_get('max_input_time'), 'recommended' => '300'],
    'max_file_uploads' => ['current' => ini_get('max_file_uploads'), 'recommended' => '20'],
];

foreach ($settings as $setting => $values) {
    $status = "❌ Need Fix";
    if ($setting === 'upload_max_filesize' || $setting === 'post_max_size') {
        $current_bytes = return_bytes($values['current']);
        $recommended_bytes = return_bytes($values['recommended']);
        if ($current_bytes >= $recommended_bytes) {
            $status = "✅ OK";
        }
    } elseif ($setting === 'memory_limit') {
        $current_bytes = return_bytes($values['current']);
        $recommended_bytes = return_bytes($values['recommended']);
        if ($current_bytes >= $recommended_bytes || $values['current'] === '-1') {
            $status = "✅ OK";
        }
    } else {
        if ((int)$values['current'] >= (int)$values['recommended']) {
            $status = "✅ OK";
        }
    }
    
    echo "<tr>";
    echo "<td><strong>{$setting}</strong></td>";
    echo "<td>{$values['current']}</td>";
    echo "<td>{$values['recommended']}</td>";
    echo "<td>{$status}</td>";
    echo "</tr>";
}

echo "</table>";

echo "<h3>PHP Info Location</h3>";
echo "<p>PHP Config File: " . php_ini_loaded_file() . "</p>";
echo "<p>Additional Config Files: " . php_ini_scanned_files() . "</p>";

echo "<h3>Server Info</h3>";
echo "<p>Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "</p>";
echo "<p>PHP Version: " . PHP_VERSION . "</p>";
echo "<p>PHP SAPI: " . php_sapi_name() . "</p>";

echo "<h3>Test Upload Form</h3>";
echo '<form method="post" enctype="multipart/form-data">';
echo '<input type="file" name="test_file" accept=".xlsx,.xls,.csv">';
echo '<input type="submit" value="Test Upload" name="test_upload">';
echo '</form>';

if (isset($_POST['test_upload']) && isset($_FILES['test_file'])) {
    echo "<h3>Upload Test Result</h3>";
    if ($_FILES['test_file']['error'] === UPLOAD_ERR_OK) {
        echo "<p>✅ Upload successful!</p>";
        echo "<p>File size: " . formatBytes($_FILES['test_file']['size']) . "</p>";
        echo "<p>File type: " . $_FILES['test_file']['type'] . "</p>";
        // Clean up
        unlink($_FILES['test_file']['tmp_name']);
    } else {
        echo "<p>❌ Upload failed with error code: " . $_FILES['test_file']['error'] . "</p>";
        echo "<p>Error meaning: " . getUploadErrorMessage($_FILES['test_file']['error']) . "</p>";
    }
}

function return_bytes($val) {
    $val = trim($val);
    $last = strtolower($val[strlen($val)-1]);
    $val = (int)$val;
    switch($last) {
        case 'g':
            $val *= 1024;
        case 'm':
            $val *= 1024;
        case 'k':
            $val *= 1024;
    }
    return $val;
}

function formatBytes($size, $precision = 2) {
    $base = log($size, 1024);
    $suffixes = array('B', 'KB', 'MB', 'GB', 'TB');
    return round(pow(1024, $base - floor($base)), $precision) . ' ' . $suffixes[floor($base)];
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
