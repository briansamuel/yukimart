<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Exception;

class FileManagerService
{
    protected $config;
    protected $disk;

    public function __construct()
    {
        $this->config = config('filemanager');
        $this->disk = Storage::disk($this->config['storage']['disk']);
    }

    /**
     * Get files and folders in a directory
     */
    public function getDirectoryContents($path = '', $type = 'all')
    {
        try {
            $contents = [];

            // Determine the full path to browse
            if ($type === 'images' || $type === 'documents' || $type === 'videos') {
                // For specific types, if path is empty (home), look in the type-specific folder
                if (empty($path)) {
                    $basePath = $this->config['storage']['base_path'] . '/' . $this->config['storage']['folders'][$type];
                    $fullPath = $basePath;
                } else {
                    // If path is provided, use it as-is (user is browsing within a folder)
                    $fullPath = $this->getFullPath($path);
                }
            } else {
                // For 'all', 'files', or 'folders' types
                $fullPath = $this->getFullPath($path);
            }

            // Ensure the directory exists
            if (!$this->disk->exists($fullPath)) {
                $this->disk->makeDirectory($fullPath, 0755, true);
            }

            // Get directories (always show folders unless type is specifically 'files')
            if ($type !== 'files') {
                $directories = $this->disk->directories($fullPath);
                foreach ($directories as $dir) {
                    $contents[] = [
                        'type' => 'folder',
                        'name' => basename($dir),
                        'path' => $this->getRelativePath($dir),
                        'url' => '',
                        'size' => 0,
                        'modified' => $this->disk->lastModified($dir),
                        'permissions' => $this->getPermissions($dir),
                        'file_type' => 'folder',
                        'is_image' => false,
                    ];
                }
            }

            // Get files and filter by type
            if ($type !== 'folders') {
                $files = $this->disk->files($fullPath);
                foreach ($files as $file) {
                    if ($this->shouldShowFile($file)) {
                        $fileInfo = $this->getFileInfo($file);

                        // Filter files based on selected type
                        $shouldInclude = false;

                        if ($type === 'all') {
                            $shouldInclude = true;
                        } elseif ($type === 'files') {
                            $shouldInclude = true;
                        } elseif ($type === 'images') {
                            $shouldInclude = ($fileInfo['file_type'] === 'images');
                        } elseif ($type === 'documents') {
                            $shouldInclude = ($fileInfo['file_type'] === 'documents');
                        } elseif ($type === 'videos') {
                            $shouldInclude = ($fileInfo['file_type'] === 'videos');
                        }

                        if ($shouldInclude) {
                            $contents[] = $fileInfo;
                        }
                    }
                }
            }

            return [
                'success' => true,
                'data' => $contents,
                'path' => $path,
                'breadcrumbs' => $this->getBreadcrumbs($path)
            ];

        } catch (Exception $e) {
            Log::error('FileManager: Error getting directory contents', [
                'path' => $path,
                'type' => $type,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to load directory contents',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Upload a file
     */
    public function uploadFile(UploadedFile $file, $path = '', $type = 'images')
    {
        try {
            // Validate file
            $validation = $this->validateFile($file, $type);
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'message' => $validation['message'],
                    'error_code' => $validation['error_code'] ?? 'VALIDATION_FAILED',
                    'data' => $validation['file_info'] ?? [
                        'original_name' => $file->getClientOriginalName(),
                        'size' => $file->getSize(),
                        'size_formatted' => $this->formatFileSize($file->getSize()),
                        'extension' => $file->getClientOriginalExtension(),
                        'mime_type' => $file->getMimeType()
                    ],
                    'validation_details' => [
                        'validation_info' => $validation['validation_info'] ?? null,
                        'security_info' => $validation['security_info'] ?? null,
                        'limits' => $validation['limits'] ?? null
                    ]
                ];
            }

            // Determine upload path - don't add type folder if user is already in a specific path
            $uploadPath = $this->getUploadPath($path, $type);

            // Log upload path for debugging
            Log::info('FileManager: Upload path determination', [
                'original_path' => $path,
                'file_type' => $type,
                'upload_path' => $uploadPath,
                'filename' => $file->getClientOriginalName()
            ]);

            // Generate unique filename
            $filename = $this->generateUniqueFilename($file, $uploadPath);

            $fullPath = $uploadPath;

            // Store the file
            $storedPath = $file->storeAs($fullPath, $filename, $this->config['storage']['disk']);

            // Create thumbnails for images
            if ($type === 'images' && $this->config['image']['create_thumbnails']) {
                $this->createThumbnails($storedPath);
            }

            // Log upload
            if ($this->config['logging']['log_uploads']) {
                Log::info('FileManager: File uploaded', [
                    'filename' => $filename,
                    'path' => $storedPath,
                    'size' => $file->getSize()
                ]);
            }

            return [
                'success' => true,
                'message' => "File '{$filename}' uploaded successfully",
                'data' => [
                    'filename' => $filename,
                    'original_name' => $file->getClientOriginalName(),
                    'path' => '/storage/' . $storedPath,
                    'url' => asset('storage/' . $storedPath),
                    'size' => $file->getSize(),
                    'size_formatted' => $this->formatFileSize($file->getSize()),
                    'type' => $file->getMimeType(),
                    'extension' => $file->getClientOriginalExtension(),
                    'file_type' => $this->getFileType($file->getClientOriginalExtension()),
                    'uploaded_at' => now()->format('Y-m-d H:i:s')
                ]
            ];

        } catch (Exception $e) {
            Log::error('FileManager: Upload failed', [
                'error' => $e->getMessage(),
                'file' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'type' => $type
            ]);

            return [
                'success' => false,
                'message' => "Upload failed for '{$file->getClientOriginalName()}': " . $e->getMessage(),
                'data' => [
                    'original_name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'size_formatted' => $this->formatFileSize($file->getSize()),
                    'extension' => $file->getClientOriginalExtension(),
                    'mime_type' => $file->getMimeType(),
                    'error_type' => 'exception'
                ]
            ];
        }
    }

    /**
     * Delete a file or folder
     */
    public function delete($path)
    {
        try {
            $fullPath = $this->getFullPath($path);

            if ($this->disk->exists($fullPath)) {
                // Delete thumbnails if it's an image
                $this->deleteThumbnails($fullPath);

                // Delete the file/folder
                if ($this->disk->exists($fullPath) && is_dir(storage_path('app/public/' . $fullPath))) {
                    $this->disk->deleteDirectory($fullPath);
                } else {
                    $this->disk->delete($fullPath);
                }

                // Log deletion
                if ($this->config['logging']['log_deletions']) {
                    Log::info('FileManager: Item deleted', ['path' => $path]);
                }

                return [
                    'success' => true,
                    'message' => 'Item deleted successfully'
                ];
            }

            return [
                'success' => false,
                'message' => 'Item not found'
            ];

        } catch (Exception $e) {
            Log::error('FileManager: Delete failed', [
                'path' => $path,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Delete failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Rename a file or folder
     */
    public function rename($oldPath, $newName)
    {
        try {
            $oldFullPath = $this->getFullPath($oldPath);
            $directory = dirname($oldFullPath);
            $newFullPath = $directory . '/' . $this->sanitizeFilename($newName);

            if (!$this->disk->exists($oldFullPath)) {
                return [
                    'success' => false,
                    'message' => 'Item not found'
                ];
            }

            if ($this->disk->exists($newFullPath)) {
                return [
                    'success' => false,
                    'message' => 'An item with this name already exists'
                ];
            }

            $this->disk->move($oldFullPath, $newFullPath);

            return [
                'success' => true,
                'message' => 'Item renamed successfully',
                'data' => [
                    'old_path' => $oldPath,
                    'new_path' => $this->getRelativePath($newFullPath)
                ]
            ];

        } catch (Exception $e) {
            Log::error('FileManager: Rename failed', [
                'old_path' => $oldPath,
                'new_name' => $newName,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Rename failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Create a new folder
     */
    public function createFolder($path, $name)
    {
        try {
            $sanitizedName = $this->sanitizeFilename($name);
            $fullPath = $this->getFullPath($path . '/' . $sanitizedName);

            if ($this->disk->exists($fullPath)) {
                return [
                    'success' => false,
                    'message' => 'A folder with this name already exists'
                ];
            }

            $this->disk->makeDirectory($fullPath);

            return [
                'success' => true,
                'message' => 'Folder created successfully',
                'data' => [
                    'name' => $sanitizedName,
                    'path' => $this->getRelativePath($fullPath)
                ]
            ];

        } catch (Exception $e) {
            Log::error('FileManager: Create folder failed', [
                'path' => $path,
                'name' => $name,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to create folder: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get file information
     */
    protected function getFileInfo($filePath)
    {
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        $fileType = $this->getFileType($extension);

        // Generate proper URL for the file
        $fileUrl = asset('storage/' . $filePath);

        return [
            'type' => 'file',
            'name' => basename($filePath),
            'path' => '/storage/' . $filePath, // Ensure path starts with /storage/
            'url' => $fileUrl,
            'size' => $this->disk->size($filePath),
            'modified' => $this->disk->lastModified($filePath),
            'extension' => $extension,
            'file_type' => $fileType,
            'mime_type' => $this->getMimeType($filePath),
            'is_image' => $fileType === 'images',
            'thumbnail' => $this->getThumbnailUrl($filePath, 'small'),
            'permissions' => $this->getPermissions($filePath),
        ];
    }

    /**
     * Validate uploaded file
     */
    protected function validateFile(UploadedFile $file, $type)
    {
        $fileInfo = [
            'original_name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'size_formatted' => $this->formatFileSize($file->getSize()),
            'extension' => strtolower($file->getClientOriginalExtension()),
            'mime_type' => $file->getMimeType(),
            'upload_error' => $file->getError(),
            'upload_error_message' => $this->getUploadErrorMessage($file->getError())
        ];

        // Check if file is valid
        if (!$file->isValid()) {
            return [
                'valid' => false,
                'message' => "Invalid file upload: {$fileInfo['upload_error_message']}",
                'error_code' => 'INVALID_UPLOAD',
                'file_info' => $fileInfo
            ];
        }

        // Check file size
        $maxSize = $this->config['upload']['max_file_size'];
        if ($file->getSize() > $maxSize) {
            $maxSizeMB = round($maxSize / 1024 / 1024, 2);
            return [
                'valid' => false,
                'message' => "File size ({$fileInfo['size_formatted']}) exceeds maximum allowed size of {$maxSizeMB}MB",
                'error_code' => 'FILE_TOO_LARGE',
                'file_info' => $fileInfo,
                'limits' => [
                    'max_size' => $maxSize,
                    'max_size_formatted' => $this->formatFileSize($maxSize)
                ]
            ];
        }

        // Get extension for security checks
        $extension = $fileInfo['extension'];

        // Skip extension and MIME type validation for 'all' type
        if ($type !== 'all') {
            // Check extension
            $allowedExtensions = $this->config['upload']['allowed_extensions'][$type] ?? [];

            if (!in_array($extension, $allowedExtensions)) {
                return [
                    'valid' => false,
                    'message' => "File type '{$extension}' is not allowed for type '{$type}'",
                    'error_code' => 'INVALID_EXTENSION',
                    'file_info' => $fileInfo,
                    'validation_info' => [
                        'type' => $type,
                        'allowed_extensions' => $allowedExtensions,
                        'all_allowed_types' => array_keys($this->config['upload']['allowed_extensions'])
                    ]
                ];
            }

            // Check MIME type
            $mimeType = $file->getMimeType();
            $allowedMimeTypes = $this->config['upload']['mime_types'][$type] ?? [];

            if (!in_array($mimeType, $allowedMimeTypes)) {
                return [
                    'valid' => false,
                    'message' => "MIME type '{$mimeType}' is not allowed for type '{$type}'",
                    'error_code' => 'INVALID_MIME_TYPE',
                    'file_info' => $fileInfo,
                    'validation_info' => [
                        'type' => $type,
                        'allowed_mime_types' => $allowedMimeTypes,
                        'detected_mime_type' => $mimeType
                    ]
                ];
            }
        }

        // Check blocked extensions (always enforced for security)
        $blockedExtensions = $this->config['security']['blocked_extensions'];
        if (in_array($extension, $blockedExtensions)) {
            return [
                'valid' => false,
                'message' => "File type '{$extension}' is blocked for security reasons",
                'error_code' => 'BLOCKED_EXTENSION',
                'file_info' => $fileInfo,
                'security_info' => [
                    'blocked_extensions' => $blockedExtensions,
                    'reason' => 'Security policy violation'
                ]
            ];
        }

        // Additional security checks
        if ($this->config['security']['check_file_content']) {
            $contentCheck = $this->validateFileContent($file);
            if (!$contentCheck['valid']) {
                return [
                    'valid' => false,
                    'message' => $contentCheck['message'],
                    'error_code' => 'CONTENT_VALIDATION_FAILED',
                    'file_info' => $fileInfo,
                    'security_info' => $contentCheck['details'] ?? []
                ];
            }
        }

        return ['valid' => true];
    }

    /**
     * Generate unique filename
     */
    protected function generateUniqueFilename(UploadedFile $file, $fullPath)
    {
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $basename = pathinfo($originalName, PATHINFO_FILENAME);

        // Sanitize filename
        $basename = $this->sanitizeFilename($basename);

        // Generate unique name
        $filename = $basename . '.' . $extension;
        $counter = 1;

        while ($this->disk->exists($fullPath . '/' . $filename)) {
            $filename = $basename . '_' . $counter . '.' . $extension;
            $counter++;
        }

        return $filename;
    }

    /**
     * Sanitize filename
     */
    protected function sanitizeFilename($filename)
    {
        if (!$this->config['security']['sanitize_filenames']) {
            return $filename;
        }

        // Remove special characters and limit length
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
        $filename = preg_replace('/_+/', '_', $filename);
        $filename = trim($filename, '_');

        $maxLength = $this->config['security']['max_filename_length'];
        if (strlen($filename) > $maxLength) {
            $filename = substr($filename, 0, $maxLength);
        }

        return $filename;
    }

    /**
     * Get full storage path
     */
    protected function getFullPath($path = '', $type = null)
    {
        $basePath = $this->config['storage']['base_path'];
        
        if ($type && isset($this->config['storage']['folders'][$type])) {
            $basePath .= '/' . $this->config['storage']['folders'][$type];
        }

        return $path ? $basePath . '/' . ltrim($path, '/') : $basePath;
    }

    /**
     * Get upload path - handles the logic for where files should be uploaded
     */
    protected function getUploadPath($path, $type)
    {
        if (empty($path)) {
            // User is at root
            if ($type === 'all') {
                // For 'all' type, upload to base path without type folder
                return $this->getFullPath('');
            } else {
                // Use type-specific folder
                return $this->getFullPath('', $type);
            }
        } else {
            // User is in a specific folder, upload to current folder
            return $this->getFullPath($path);
        }
    }

    /**
     * Get relative path from full path
     */
    protected function getRelativePath($fullPath)
    {
        $basePath = $this->config['storage']['base_path'];
        return str_replace($basePath . '/', '', $fullPath);
    }

    /**
     * Search files by query
     */
    public function searchFiles($query, $path = '', $type = 'all')
    {
        $results = [];
        $searchPath = $this->getFullPath($path);

        // If searching in a specific type and no path provided, search in type folder
        if (($type === 'images' || $type === 'documents' || $type === 'videos') && empty($path)) {
            $searchPath = $this->config['storage']['base_path'] . '/' . $this->config['storage']['folders'][$type];
        }

        if (!Storage::disk('public')->exists($searchPath)) {
            return [];
        }

        // Get all files recursively
        $allFiles = Storage::disk('public')->allFiles($searchPath);

        foreach ($allFiles as $filePath) {
            $fileName = basename($filePath);

            // Check if filename contains the search query (case-insensitive)
            if (stripos($fileName, $query) !== false) {
                $fileInfo = $this->getFileInfo($filePath);

                // Filter by type if specified
                if ($type !== 'all' && $fileInfo['file_type'] !== $type) {
                    continue;
                }

                $results[] = $fileInfo;
            }
        }

        // Sort results by relevance (exact matches first, then by name)
        usort($results, function($a, $b) use ($query) {
            $aExact = stripos($a['name'], $query) === 0 ? 0 : 1;
            $bExact = stripos($b['name'], $query) === 0 ? 0 : 1;

            if ($aExact !== $bExact) {
                return $aExact - $bExact;
            }

            return strcasecmp($a['name'], $b['name']);
        });

        return $results;
    }

    /**
     * Get file type based on extension
     */
    protected function getFileType($extension)
    {
        foreach ($this->config['upload']['allowed_extensions'] as $type => $extensions) {
            if (in_array(strtolower($extension), $extensions)) {
                return $type;
            }
        }
        return 'files';
    }

    /**
     * Check if file should be shown
     */
    protected function shouldShowFile($filePath)
    {
        $filename = basename($filePath);
        
        // Hide hidden files if configured
        if (!$this->config['ui']['show_hidden_files'] && str_starts_with($filename, '.')) {
            return false;
        }

        return true;
    }

    /**
     * Get breadcrumbs for navigation
     */
    protected function getBreadcrumbs($path)
    {
        $breadcrumbs = [['name' => 'Home', 'path' => '']];
        
        if ($path) {
            $parts = explode('/', trim($path, '/'));
            $currentPath = '';
            
            foreach ($parts as $part) {
                $currentPath .= '/' . $part;
                $breadcrumbs[] = [
                    'name' => $part,
                    'path' => ltrim($currentPath, '/')
                ];
            }
        }

        return $breadcrumbs;
    }

    /**
     * Get permissions for file/folder
     */
    protected function getPermissions($path)
    {
        return [
            'read' => true,
            'write' => $this->config['permissions']['rename'],
            'delete' => $this->config['permissions']['delete'],
        ];
    }

    /**
     * Create thumbnails for images
     */
    protected function createThumbnails($imagePath)
    {
        if (!class_exists('Intervention\Image\Facades\Image')) {
            return; // Skip if Intervention Image is not installed
        }

        try {
            $fullPath = $this->disk->path($imagePath);
            $thumbnailSizes = $this->config['image']['thumbnail_sizes'];

            foreach ($thumbnailSizes as $size => $dimensions) {
                $thumbnailPath = $this->getThumbnailPath($imagePath, $size);
                $thumbnailDir = dirname($this->disk->path($thumbnailPath));

                if (!is_dir($thumbnailDir)) {
                    mkdir($thumbnailDir, 0755, true);
                }

                $image = Image::make($fullPath);
                $image->fit($dimensions[0], $dimensions[1]);
                $image->save($this->disk->path($thumbnailPath), $this->config['image']['quality']);
            }
        } catch (Exception $e) {
            Log::warning('FileManager: Failed to create thumbnails', [
                'path' => $imagePath,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Delete thumbnails for an image
     */
    protected function deleteThumbnails($imagePath)
    {
        $thumbnailSizes = $this->config['image']['thumbnail_sizes'];

        foreach ($thumbnailSizes as $size => $dimensions) {
            $thumbnailPath = $this->getThumbnailPath($imagePath, $size);
            if ($this->disk->exists($thumbnailPath)) {
                $this->disk->delete($thumbnailPath);
            }
        }
    }

    /**
     * Get thumbnail path
     */
    protected function getThumbnailPath($imagePath, $size)
    {
        $pathInfo = pathinfo($imagePath);
        return $pathInfo['dirname'] . '/thumbnails/' . $size . '/' . $pathInfo['basename'];
    }

    /**
     * Get thumbnail URL
     */
    protected function getThumbnailUrl($imagePath, $size)
    {
        $thumbnailPath = $this->getThumbnailPath($imagePath, $size);

        if ($this->disk->exists($thumbnailPath)) {
            return asset('storage/' . $thumbnailPath);
        }

        return asset('storage/' . $imagePath); // Fallback to original image
    }

    /**
     * Get MIME type of a file
     */
    protected function getMimeType($filePath)
    {
        try {
            $fullPath = storage_path('app/public/' . $filePath);
            if (file_exists($fullPath)) {
                return mime_content_type($fullPath);
            }
        } catch (Exception) {
            // Fallback to extension-based detection
        }

        // Fallback based on extension
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'txt' => 'text/plain',
            'mp4' => 'video/mp4',
            'zip' => 'application/zip',
        ];

        return $mimeTypes[$extension] ?? 'application/octet-stream';
    }

    /**
     * Format file size in human readable format
     */
    protected function formatFileSize($bytes)
    {
        if ($bytes === 0) return '0 B';

        $k = 1024;
        $sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = floor(log($bytes) / log($k));

        return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
    }

    /**
     * Get upload error message from error code
     */
    protected function getUploadErrorMessage($errorCode)
    {
        $errors = [
            UPLOAD_ERR_OK => 'No error',
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize directive in php.ini',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE directive in HTML form',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
        ];

        return $errors[$errorCode] ?? "Unknown upload error (code: {$errorCode})";
    }

    /**
     * Validate file content for security
     */
    protected function validateFileContent(UploadedFile $file)
    {
        try {
            // Basic content validation
            $content = file_get_contents($file->getPathname());

            // Check for suspicious patterns
            $suspiciousPatterns = [
                '/<\?php/i' => 'PHP code detected',
                '/<script/i' => 'JavaScript code detected',
                '/eval\s*\(/i' => 'Eval function detected',
                '/exec\s*\(/i' => 'Exec function detected',
                '/system\s*\(/i' => 'System function detected',
                '/shell_exec\s*\(/i' => 'Shell exec function detected'
            ];

            foreach ($suspiciousPatterns as $pattern => $description) {
                if (preg_match($pattern, $content)) {
                    return [
                        'valid' => false,
                        'message' => "Suspicious content detected: {$description}",
                        'details' => [
                            'pattern' => $pattern,
                            'description' => $description,
                            'file_size' => strlen($content)
                        ]
                    ];
                }
            }

            return ['valid' => true];

        } catch (Exception $e) {
            return [
                'valid' => false,
                'message' => 'Failed to validate file content: ' . $e->getMessage(),
                'details' => ['error' => $e->getMessage()]
            ];
        }
    }
}
