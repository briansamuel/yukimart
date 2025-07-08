<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>File Manager</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="{{ asset('admin-assets/assets/css/filemanager.css') }}" rel="stylesheet">
</head>
<body>
    <div id="filemanager-app" class="container-fluid h-100">
        <!-- Header -->
        <div class="row bg-light border-bottom p-3">
            <div class="col-md-6">
                <h4 class="mb-0">
                    <i class="fas fa-folder-open me-2"></i>
                    File Manager
                </h4>
            </div>
            <div class="col-md-6 text-end">
                <div class="btn-group me-2" role="group">
                    <button type="button" class="btn btn-outline-secondary" id="view-grid" title="Grid View">
                        <i class="fas fa-th"></i>
                    </button>
                    <button type="button" class="btn btn-outline-secondary" id="view-list" title="List View">
                        <i class="fas fa-list"></i>
                    </button>
                </div>
                <button type="button" class="btn btn-secondary" onclick="window.close()">
                    <i class="fas fa-times me-1"></i> Close
                </button>
            </div>
        </div>

        <!-- Toolbar -->
        <div class="row bg-white border-bottom p-2">
            <div class="col-md-8">
                <!-- Breadcrumb -->
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0" id="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="#" data-path=""><i class="fas fa-home"></i> Home</a>
                        </li>
                    </ol>
                </nav>
            </div>
            <div class="col-md-4 text-end">
                <!-- Search -->
                <div class="input-group input-group-sm">
                    <input type="text" class="form-control" id="search-input" placeholder="Search files...">
                    <button class="btn btn-outline-secondary" type="button" id="search-btn" title="Search">
                        <i class="fas fa-search"></i>
                    </button>
                    <button class="btn btn-outline-secondary" type="button" id="clear-search-btn" title="Clear Search" style="display: none;">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="row flex-fill">
            <!-- Sidebar -->
            <div class="col-md-3 bg-light border-end p-3">
                <div class="mb-3">
                    <button type="button" class="btn btn-primary w-100" id="upload-btn">
                        <i class="fas fa-upload me-1"></i> Upload Files
                    </button>
                </div>
                
                <div class="mb-3">
                    <button type="button" class="btn btn-outline-primary w-100" id="create-folder-btn">
                        <i class="fas fa-folder-plus me-1"></i> New Folder
                    </button>
                </div>

                <!-- File Type Filter -->
                <div class="mb-3">
                    <label class="form-label">File Type</label>
                    <select class="form-select form-select-sm" id="type-filter">
                        <option value="all">All Files</option>
                        <option value="images" {{ $type === 'images' ? 'selected' : '' }}>Images</option>
                        <option value="documents">Documents</option>
                        <option value="videos">Videos</option>
                        <option value="files">Other Files</option>
                    </select>
                </div>

                <!-- Quick Actions -->
                <div class="mb-3">
                    <label class="form-label">Quick Actions</label>
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="select-all-btn">
                            <i class="fas fa-check-square me-1"></i> Select All
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-sm" id="delete-selected-btn" disabled>
                            <i class="fas fa-trash me-1"></i> Delete Selected
                        </button>
                    </div>
                </div>
            </div>

            <!-- File Area -->
            <div class="col-md-9 p-0">
                <!-- Drop Zone -->
                <div id="drop-zone" class="drop-zone h-100 p-3">
                    <div class="drop-overlay">
                        <div class="drop-content">
                            <i class="fas fa-cloud-upload-alt fa-3x mb-3"></i>
                            <h5>Drop files here to upload</h5>
                            <p>Or click the upload button</p>
                        </div>
                    </div>

                    <!-- Loading Spinner -->
                    <div id="loading" class="text-center py-5" style="display: none;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading files...</p>
                    </div>

                    <!-- File Grid -->
                    <div id="file-grid" class="file-grid">
                        <!-- Files will be loaded here -->
                    </div>

                    <!-- File List -->
                    <div id="file-list" class="file-list" style="display: none;">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th width="30">
                                        <input type="checkbox" id="select-all-checkbox">
                                    </th>
                                    <th>Name</th>
                                    <th width="100">Size</th>
                                    <th width="150">Modified</th>
                                    <th width="100">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="file-list-body">
                                <!-- Files will be loaded here -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Empty State -->
                    <div id="empty-state" class="text-center py-5" style="display: none;">
                        <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No files found</h5>
                        <p class="text-muted">Upload some files to get started</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Bar -->
        <div class="row bg-light border-top p-2">
            <div class="col-md-6">
                <small class="text-muted" id="status-text">Ready</small>
                <!-- Simple progress bar for uploads -->
                <div id="simple-upload-progress" class="mt-1" style="display: none;">
                    <div class="progress" style="height: 3px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                             role="progressbar"
                             id="simpleProgressBar"
                             style="width: 0%"
                             aria-valuenow="0"
                             aria-valuemin="0"
                             aria-valuemax="100">
                        </div>
                    </div>
                    <small class="text-muted" id="simple-progress-text" style="font-size: 0.7rem;">Uploading...</small>
                </div>
            </div>
            <div class="col-md-6 text-end">
                <small class="text-muted" id="selection-info"></small>
            </div>
        </div>
    </div>

    <!-- Hidden File Input -->
    <input type="file" id="file-input" multiple style="display: none;">

    <!-- Modals -->
    @include('admin.filemanager.modals.rename')
    @include('admin.filemanager.modals.create-folder')
    @include('admin.filemanager.modals.file-info')
    @include('admin.filemanager.modals.confirm-delete')
    @include('admin.filemanager.modals.upload-progress')

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Global configuration
        window.FileManagerConfig = {
            type: '{{ $type }}',
            path: '{{ $path }}',
            editor: '{{ $editor }}',
            fieldId: '{{ $fieldId }}',
            baseUrl: '{{ url('/admin/filemanager') }}',
            csrfToken: '{{ csrf_token() }}',
            isPopup: window.opener !== null,
            maxConcurrentUploads: {{ config('filemanager.upload.max_concurrent_uploads') }},
            maxFilesPerBatch: {{ config('filemanager.upload.max_files_per_batch') }},
            showProgress: {{ config('filemanager.upload.show_progress') ? 'true' : 'false' }},
            maxFileSize: {{ config('filemanager.upload.max_file_size', 10485760) }}
        };
    </script>
    <script src="{{ asset('admin-assets/assets/js/filemanager.js') }}"></script>
</body>
</html>
