<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Custom File Manager Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration options for the custom file manager
    | system including upload settings, file types, and storage options.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Storage Configuration
    |--------------------------------------------------------------------------
    */
    'storage' => [
        'disk' => 'public',
        'base_path' => 'uploads',
        'folders' => [
            'images' => 'images',
            'files' => 'files',
            'documents' => 'documents',
            'videos' => 'videos',
            'products' => 'products',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Upload Configuration
    |--------------------------------------------------------------------------
    */
    'upload' => [
        'max_file_size' => 10 * 1024 * 1024, // 10MB in bytes
        'chunk_size' => 1024 * 1024, // 1MB chunks for large file uploads
        'max_concurrent_uploads' => 1, // Maximum concurrent uploads
        'max_files_per_batch' => 3, // Maximum files per batch upload
        'show_progress' => true, // Show upload progress modal and progress bars
        'progress_update_interval' => 100, // Progress update interval in milliseconds
        'show_individual_progress' => true, // Show progress for each individual file
        'show_overall_progress' => true, // Show overall batch progress
        'show_upload_speed' => true, // Show upload speed indicator
        'show_time_remaining' => true, // Show estimated time remaining
        'allowed_extensions' => [
            'images' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp'],
            'documents' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'rtf'],
            'videos' => ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm'],
            'files' => ['zip', 'rar', '7z', 'tar', 'gz'],
        ],
        'mime_types' => [
            'images' => [
                'image/jpeg', 'image/png', 'image/gif', 'image/webp', 
                'image/svg+xml', 'image/bmp', 'image/jpg'
            ],
            'documents' => [
                'application/pdf', 'application/msword', 
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'text/plain', 'application/rtf'
            ],
            'videos' => [
                'video/mp4', 'video/avi', 'video/quicktime', 
                'video/x-ms-wmv', 'video/x-flv', 'video/webm'
            ],
            'files' => [
                'application/zip', 'application/x-rar-compressed',
                'application/x-7z-compressed', 'application/x-tar',
                'application/gzip'
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Image Processing
    |--------------------------------------------------------------------------
    */
    'image' => [
        'create_thumbnails' => true,
        'thumbnail_sizes' => [
            'small' => [150, 150],
            'medium' => [300, 300],
            'large' => [600, 600],
        ],
        'quality' => 85,
        'auto_orient' => true,
        'strip_metadata' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    */
    'security' => [
        'scan_uploads' => true,
        'check_file_content' => false, // Check file content for security threats
        'allowed_domains' => [], // Empty means all domains allowed
        'blocked_extensions' => ['php', 'exe', 'bat', 'cmd', 'com', 'scr', 'vbs', 'js'],
        'max_filename_length' => 255,
        'sanitize_filenames' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | UI Configuration
    |--------------------------------------------------------------------------
    */
    'ui' => [
        'default_view' => 'grid', // 'grid' or 'list'
        'items_per_page' => 20,
        'show_hidden_files' => false,
        'enable_drag_drop' => true,
        'enable_multiple_selection' => true,
        'enable_context_menu' => true,
        'show_file_info' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Integration Settings
    |--------------------------------------------------------------------------
    */
    'integration' => [
        'tinymce' => [
            'enabled' => true,
            'callback_function' => 'SetUrl',
        ],
        'ckeditor' => [
            'enabled' => true,
            'callback_function' => 'CKEDITOR.tools.callFunction',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Permissions
    |--------------------------------------------------------------------------
    */
    'permissions' => [
        'upload' => true,
        'delete' => true,
        'rename' => true,
        'move' => true,
        'copy' => true,
        'create_folder' => true,
        'delete_folder' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    */
    'cache' => [
        'enabled' => true,
        'ttl' => 3600, // 1 hour
        'key_prefix' => 'filemanager_',
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    */
    'logging' => [
        'enabled' => true,
        'log_uploads' => true,
        'log_deletions' => true,
        'log_operations' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Error Reporting Configuration
    |--------------------------------------------------------------------------
    */
    'error_reporting' => [
        'detailed_errors' => true, // Show detailed error messages
        'show_technical_details' => true, // Show technical details in errors
        'include_troubleshooting' => true, // Include troubleshooting suggestions
        'show_file_content_errors' => true, // Show file content related errors
        'max_error_message_length' => 1000,
    ],
];
