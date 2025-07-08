<?php
// Simple upload handler to test server configuration
header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

$response = [
    'success' => false,
    'message' => '',
    'debug' => [
        'server_info' => [
            'software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'php_version' => PHP_VERSION,
            'sapi' => php_sapi_name(),
            'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'Unknown',
            'content_length' => $_SERVER['CONTENT_LENGTH'] ?? 'Unknown',
        ],
        'php_config' => [
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'max_execution_time' => ini_get('max_execution_time'),
            'memory_limit' => ini_get('memory_limit'),
            'max_input_time' => ini_get('max_input_time'),
        ],
        'request_info' => [
            'post_data_size' => strlen(file_get_contents('php://input')),
            'files_count' => count($_FILES),
            'post_count' => count($_POST),
        ]
    ]
];

try {
    // Check if file was uploaded
    if (!isset($_FILES['test_file'])) {
        $response['message'] = 'No file uploaded. Check if request reached server.';
        $response['debug']['files_received'] = $_FILES;
        echo json_encode($response, JSON_PRETTY_PRINT);
        exit;
    }
    
    $file = $_FILES['test_file'];
    
    // Add file info to debug
    $response['debug']['file_info'] = [
        'name' => $file['name'],
        'size' => $file['size'],
        'type' => $file['type'],
        'tmp_name' => $file['tmp_name'],
        'error' => $file['error'],
        'error_message' => getUploadErrorMessage($file['error']),
    ];
    
    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $response['message'] = 'Upload failed: ' . getUploadErrorMessage($file['error']);
        echo json_encode($response, JSON_PRETTY_PRINT);
        exit;
    }
    
    // Validate file
    if ($file['size'] <= 0) {
        $response['message'] = 'File is empty or corrupted.';
        echo json_encode($response, JSON_PRETTY_PRINT);
        exit;
    }
    
    // Check if temp file exists
    if (!file_exists($file['tmp_name'])) {
        $response['message'] = 'Temporary file not found. Upload may have been blocked.';
        echo json_encode($response, JSON_PRETTY_PRINT);
        exit;
    }
    
    // Success!
    $response['success'] = true;
    $response['message'] = 'File uploaded successfully!';
    $response['debug']['upload_result'] = [
        'file_exists' => file_exists($file['tmp_name']),
        'file_size_on_disk' => filesize($file['tmp_name']),
        'is_uploaded_file' => is_uploaded_file($file['tmp_name']),
        'temp_dir' => sys_get_temp_dir(),
    ];
    
    // Clean up temp file
    if (file_exists($file['tmp_name'])) {
        unlink($file['tmp_name']);
    }
    
} catch (Exception $e) {
    $response['message'] = 'Server error: ' . $e->getMessage();
    $response['debug']['exception'] = [
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
    ];
} catch (Error $e) {
    $response['message'] = 'PHP error: ' . $e->getMessage();
    $response['debug']['error'] = [
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
    ];
}

echo json_encode($response, JSON_PRETTY_PRINT);

function getUploadErrorMessage($error_code) {
    switch ($error_code) {
        case UPLOAD_ERR_OK:
            return 'No error, file uploaded successfully';
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
