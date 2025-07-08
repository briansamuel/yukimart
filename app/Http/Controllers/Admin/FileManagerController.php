<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\FileManagerService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class FileManagerController extends Controller
{
    protected $fileManagerService;

    public function __construct(FileManagerService $fileManagerService)
    {
        $this->fileManagerService = $fileManagerService;
    }

    /**
     * Display the file manager interface
     */
    public function index(Request $request): View
    {
        $type = $request->get('type', 'images');
        $path = $request->get('path', '');
        $editor = $request->get('editor');
        $fieldId = $request->get('field_id', 'thumbnail');

        return view('admin.filemanager.index', compact('type', 'path', 'editor', 'fieldId'));
    }

    /**
     * Get directory contents via AJAX
     */
    public function getContents(Request $request): JsonResponse
    {
        $path = $request->get('path', '');
        $type = $request->get('type', 'all');

        $result = $this->fileManagerService->getDirectoryContents($path, $type);

        return response()->json($result);
    }

    /**
     * Upload files
     */
    public function upload(Request $request): JsonResponse
    {
        // Initial validation without strict type checking
        $request->validate([
            'files.*' => 'required|file',
            'path' => 'nullable|string',
            'type' => 'nullable|string'
        ]);

        $path = $request->get('path', '');
        $requestedType = $request->get('type');
        $files = $request->file('files', []);

        // Validate type after auto-detection
        if ($requestedType && !in_array($requestedType, ['all', 'images', 'documents', 'videos', 'files'])) {
            return response()->json([
                'success' => false,
                'message' => "Invalid file type '{$requestedType}'. Allowed types: all, images, documents, videos, files"
            ], 400);
        }

        $results = [];
        $successCount = 0;
        $errorCount = 0;

        foreach ($files as $file) {
            // Auto-detect file type if not specified
            $type = $requestedType ?: $this->detectFileType($file);

            $result = $this->fileManagerService->uploadFile($file, $path, $type);
            $results[] = $result;

            if ($result['success']) {
                $successCount++;
            } else {
                $errorCount++;
            }
        }

        // Prepare detailed response
        $successFiles = array_filter($results, fn($result) => $result['success']);
        $errorFiles = array_filter($results, fn($result) => !$result['success']);

        $response = [
            'success' => $errorCount === 0,
            'message' => $this->getDetailedUploadMessage($successCount, $errorCount, $successFiles, $errorFiles),
            'results' => $results,
            'summary' => [
                'total' => count($files),
                'success' => $successCount,
                'errors' => $errorCount,
                'total_size' => $this->getTotalUploadSize($successFiles),
                'uploaded_files' => array_map(fn($result) => $result['data']['filename'] ?? 'Unknown', $successFiles)
            ]
        ];

        // Add detailed error information if there are any
        if ($errorCount > 0) {
            $response['error_details'] = array_map(function($result) {
                $errorDetail = [
                    'filename' => $result['data']['original_name'] ?? 'Unknown',
                    'error' => $result['message'],
                    'error_code' => $result['error_code'] ?? 'UNKNOWN_ERROR'
                ];

                // Add file information
                if (isset($result['data'])) {
                    $errorDetail['file_info'] = [
                        'size' => $result['data']['size_formatted'] ?? 'Unknown',
                        'extension' => $result['data']['extension'] ?? 'Unknown',
                        'mime_type' => $result['data']['mime_type'] ?? 'Unknown'
                    ];
                }

                // Add validation details if available
                if (isset($result['validation_details'])) {
                    $errorDetail['details'] = $result['validation_details'];
                }

                return $errorDetail;
            }, $errorFiles);

            // Add troubleshooting suggestions
            $response['troubleshooting'] = $this->getTroubleshootingSuggestions($errorFiles);
        }

        return response()->json($response);
    }

    /**
     * Single file upload (for direct uploads)
     */
    public function uploadSingle(Request $request): JsonResponse
    {
        // Initial validation without strict type checking
        $request->validate([
            'upload' => 'required|file',
            'path' => 'nullable|string',
            'type' => 'nullable|string'
        ]);

        $file = $request->file('upload');
        $path = $request->get('path', '');
        $requestedType = $request->get('type');

        // Validate type after auto-detection
        if ($requestedType && !in_array($requestedType, ['all', 'images', 'documents', 'videos', 'files'])) {
            return response()->json([
                'success' => false,
                'message' => "Invalid file type '{$requestedType}'. Allowed types: all, images, documents, videos, files"
            ], 400);
        }

        // Auto-detect file type if not specified
        $type = $requestedType ?: $this->detectFileType($file);

        $result = $this->fileManagerService->uploadFile($file, $path, $type);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'url' => $result['data']['url'],
                'path' => $result['data']['path'],
                'filename' => $result['data']['filename'],
                'message' => $result['message'],
                'file_details' => [
                    'original_name' => $result['data']['original_name'],
                    'size' => $result['data']['size_formatted'],
                    'type' => $result['data']['file_type'],
                    'extension' => $result['data']['extension'],
                    'uploaded_at' => $result['data']['uploaded_at']
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message'],
            'error_code' => $result['error_code'] ?? 'UPLOAD_FAILED',
            'error_details' => [
                'filename' => $file->getClientOriginalName(),
                'size' => $this->formatFileSize($file->getSize()),
                'extension' => $file->getClientOriginalExtension(),
                'mime_type' => $file->getMimeType(),
                'upload_error' => $file->getError()
            ],
            'validation_details' => $result['validation_details'] ?? null,
            'troubleshooting' => $this->getTroubleshootingSuggestions([$result])
        ], 400);
    }

    /**
     * Delete file or folder
     */
    public function delete(Request $request): JsonResponse
    {
        $request->validate([
            'path' => 'required|string'
        ]);

        $path = $request->get('path');
        $result = $this->fileManagerService->delete($path);

        return response()->json($result);
    }

    /**
     * Delete multiple files
     */
    public function deleteMultiple(Request $request): JsonResponse
    {
        $request->validate([
            'paths' => 'required|array',
            'paths.*' => 'required|string'
        ]);

        $paths = $request->get('paths');
        $results = [];
        $successCount = 0;
        $errorCount = 0;

        foreach ($paths as $path) {
            $result = $this->fileManagerService->delete($path);
            $results[] = array_merge($result, ['path' => $path]);

            if ($result['success']) {
                $successCount++;
            } else {
                $errorCount++;
            }
        }

        return response()->json([
            'success' => $errorCount === 0,
            'message' => $this->getDeleteMessage($successCount, $errorCount),
            'results' => $results,
            'summary' => [
                'total' => count($paths),
                'success' => $successCount,
                'errors' => $errorCount
            ]
        ]);
    }

    /**
     * Rename file or folder
     */
    public function rename(Request $request): JsonResponse
    {
        $request->validate([
            'path' => 'required|string',
            'name' => 'required|string|max:255'
        ]);

        $path = $request->get('path');
        $newName = $request->get('name');

        $result = $this->fileManagerService->rename($path, $newName);

        return response()->json($result);
    }

    /**
     * Create new folder
     */
    public function createFolder(Request $request): JsonResponse
    {
        $request->validate([
            'path' => 'nullable|string',
            'name' => 'required|string|max:255'
        ]);

        $path = $request->get('path', '');
        $name = $request->get('name');

        $result = $this->fileManagerService->createFolder($path, $name);

        return response()->json($result);
    }

    /**
     * Move files/folders
     */
    public function move(Request $request): JsonResponse
    {
        $request->validate([
            'source' => 'required|string',
            'destination' => 'required|string'
        ]);

        // This would be implemented in the service
        return response()->json([
            'success' => false,
            'message' => 'Move operation not yet implemented'
        ]);
    }

    /**
     * Copy files/folders
     */
    public function copy(Request $request): JsonResponse
    {
        $request->validate([
            'source' => 'required|string',
            'destination' => 'required|string'
        ]);

        // This would be implemented in the service
        return response()->json([
            'success' => false,
            'message' => 'Copy operation not yet implemented'
        ]);
    }

    /**
     * Get file information
     */
    public function getFileInfo(Request $request): JsonResponse
    {
        $request->validate([
            'path' => 'required|string'
        ]);

        $path = $request->get('path');
        
        // This would get detailed file information
        return response()->json([
            'success' => true,
            'data' => [
                'path' => $path,
                'name' => basename($path),
                'size' => 0,
                'modified' => time(),
                'type' => 'file'
            ]
        ]);
    }

    /**
     * Search files
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'query' => 'required|string|min:2',
            'path' => 'nullable|string',
            'type' => 'nullable|string'
        ]);

        $query = $request->get('query');
        $path = $request->get('path', '');
        $type = $request->get('type', 'all');

        try {
            $results = $this->fileManagerService->searchFiles($query, $path, $type);

            return response()->json([
                'success' => true,
                'data' => $results,
                'message' => count($results) > 0
                    ? 'Found ' . count($results) . ' file(s) matching "' . $query . '"'
                    : 'No files found matching "' . $query . '"',
                'search_query' => $query,
                'search_path' => $path,
                'search_type' => $type
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Search failed: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * Get upload message based on results
     */
    protected function getUploadMessage(int $successCount, int $errorCount): string
    {
        if ($errorCount === 0) {
            return $successCount === 1 
                ? 'File uploaded successfully' 
                : "{$successCount} files uploaded successfully";
        }

        if ($successCount === 0) {
            return $errorCount === 1 
                ? 'File upload failed' 
                : "All {$errorCount} file uploads failed";
        }

        return "{$successCount} files uploaded successfully, {$errorCount} failed";
    }

    /**
     * Get delete message based on results
     */
    protected function getDeleteMessage(int $successCount, int $errorCount): string
    {
        if ($errorCount === 0) {
            return $successCount === 1 
                ? 'Item deleted successfully' 
                : "{$successCount} items deleted successfully";
        }

        if ($successCount === 0) {
            return $errorCount === 1 
                ? 'Failed to delete item' 
                : "Failed to delete all {$errorCount} items";
        }

        return "{$successCount} items deleted successfully, {$errorCount} failed";
    }

    /**
     * Get detailed upload message with file information
     */
    protected function getDetailedUploadMessage(int $successCount, int $errorCount, array $successFiles, array $errorFiles): string
    {
        if ($errorCount === 0) {
            if ($successCount === 1) {
                $file = $successFiles[0]['data'];
                return "File '{$file['original_name']}' ({$file['size_formatted']}) uploaded successfully";
            } else {
                $totalSize = $this->getTotalUploadSize($successFiles);
                return "{$successCount} files uploaded successfully (Total: {$totalSize})";
            }
        }

        if ($successCount === 0) {
            if ($errorCount === 1) {
                $error = $errorFiles[0];
                $filename = $error['data']['original_name'] ?? 'Unknown file';
                return "Failed to upload '{$filename}': {$error['message']}";
            } else {
                return "All {$errorCount} file uploads failed";
            }
        }

        $totalSize = $this->getTotalUploadSize($successFiles);
        return "{$successCount} files uploaded successfully (Total: {$totalSize}), {$errorCount} failed";
    }

    /**
     * Calculate total size of successfully uploaded files
     */
    protected function getTotalUploadSize(array $successFiles): string
    {
        $totalBytes = array_sum(array_map(fn($file) => $file['data']['size'] ?? 0, $successFiles));
        return $this->formatFileSize($totalBytes);
    }

    /**
     * Format file size in human readable format
     */
    protected function formatFileSize($bytes): string
    {
        if ($bytes === 0) return '0 B';

        $k = 1024;
        $sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = floor(log($bytes) / log($k));

        return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
    }

    /**
     * Auto-detect file type based on extension
     */
    protected function detectFileType($file): string
    {
        $extension = strtolower($file->getClientOriginalExtension());

        // Get file manager configuration
        $config = config('filemanager.upload.allowed_extensions');

        // Check each type for the extension
        foreach ($config as $type => $extensions) {
            if (in_array($extension, $extensions)) {
                return $type;
            }
        }

        // Default to 'files' if not found in any specific category
        return 'files';
    }

    /**
     * Get troubleshooting suggestions based on error types
     */
    protected function getTroubleshootingSuggestions(array $errorFiles): array
    {
        $suggestions = [];
        $errorCodes = array_unique(array_map(fn($file) => $file['error_code'] ?? 'UNKNOWN', $errorFiles));

        foreach ($errorCodes as $errorCode) {
            switch ($errorCode) {
                case 'FILE_TOO_LARGE':
                    $suggestions[] = [
                        'issue' => 'File size too large',
                        'solution' => 'Reduce file size or compress the file before uploading',
                        'technical' => 'Check upload_max_filesize and post_max_size in php.ini'
                    ];
                    break;

                case 'INVALID_EXTENSION':
                    $suggestions[] = [
                        'issue' => 'File type not allowed',
                        'solution' => 'Convert file to an allowed format, select correct file type, or use "All Files" filter',
                        'technical' => 'Check allowed_extensions configuration in filemanager.php'
                    ];
                    break;

                case 'INVALID_MIME_TYPE':
                    $suggestions[] = [
                        'issue' => 'MIME type mismatch',
                        'solution' => 'File extension may not match actual file content',
                        'technical' => 'Verify file integrity and check mime_types configuration'
                    ];
                    break;

                case 'BLOCKED_EXTENSION':
                    $suggestions[] = [
                        'issue' => 'File type blocked for security',
                        'solution' => 'This file type is not allowed for security reasons',
                        'technical' => 'Check blocked_extensions in security configuration'
                    ];
                    break;

                case 'CONTENT_VALIDATION_FAILED':
                    $suggestions[] = [
                        'issue' => 'Suspicious file content detected',
                        'solution' => 'File contains potentially dangerous code',
                        'technical' => 'Content security scan failed - file may contain malicious code'
                    ];
                    break;

                case 'INVALID_UPLOAD':
                    $suggestions[] = [
                        'issue' => 'Upload process failed',
                        'solution' => 'Try uploading the file again or check file integrity',
                        'technical' => 'Check server upload configuration and disk space'
                    ];
                    break;

                default:
                    $suggestions[] = [
                        'issue' => 'Unknown upload error',
                        'solution' => 'Try uploading the file again or contact support',
                        'technical' => 'Check server logs for detailed error information'
                    ];
            }
        }

        return array_unique($suggestions, SORT_REGULAR);
    }
}
