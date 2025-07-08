/**
 * Custom File Manager JavaScript
 * Handles drag & drop upload, file operations, and UI interactions
 */

class FileManager {
    constructor() {
        this.config = window.FileManagerConfig;
        this.currentPath = this.config.path || '';
        this.currentType = this.config.type || 'images';
        this.selectedFiles = new Set();
        this.viewMode = 'grid';
        this.isUploading = false;
        this.currentFileInfo = null;

        // Upload progress tracking
        this.uploadQueue = [];
        this.activeUploads = new Map();
        this.completedUploads = [];
        this.failedUploads = [];
        this.uploadStartTime = null;
        this.totalBytesUploaded = 0;
        this.totalBytesToUpload = 0;
        this.maxConcurrentUploads = this.config.maxConcurrentUploads || 3;
        this.maxFilesPerBatch = this.config.maxFilesPerBatch || 10;
        this.uploadCancelled = false;
        this.uploadProgressModal = null;

        // Store current files for view mode switching
        this.currentFiles = [];

        this.init();
    }

    init() {
        this.setupEventListeners();
        this.setupDragAndDrop();
        this.loadFiles();
        this.updateUI();
    }

    setupEventListeners() {
        // View mode toggles
        document.getElementById('view-grid').addEventListener('click', () => this.setViewMode('grid'));
        document.getElementById('view-list').addEventListener('click', () => this.setViewMode('list'));

        // Upload button
        document.getElementById('upload-btn').addEventListener('click', () => this.triggerFileUpload());
        document.getElementById('file-input').addEventListener('change', (e) => this.handleFileSelect(e));

        // Create folder
        document.getElementById('create-folder-btn').addEventListener('click', () => this.showCreateFolderModal());
        document.getElementById('confirmCreateFolder').addEventListener('click', () => this.createFolder());

        // Type filter
        document.getElementById('type-filter').addEventListener('change', (e) => {
            this.currentType = e.target.value;
            this.loadFiles();
        });

        // Search
        document.getElementById('search-btn').addEventListener('click', () => this.performSearch());
        document.getElementById('clear-search-btn').addEventListener('click', () => this.clearSearch());
        document.getElementById('search-input').addEventListener('keypress', (e) => {
            if (e.key === 'Enter') this.performSearch();
        });
        document.getElementById('search-input').addEventListener('input', (e) => {
            const clearBtn = document.getElementById('clear-search-btn');
            if (e.target.value.trim()) {
                clearBtn.style.display = 'inline-block';
            } else {
                clearBtn.style.display = 'none';
            }
        });

        // Selection
        document.getElementById('select-all-btn').addEventListener('click', () => this.toggleSelectAll());
        document.getElementById('delete-selected-btn').addEventListener('click', () => this.deleteSelected());

        // Rename modal
        document.getElementById('confirmRename').addEventListener('click', () => this.performRename());

        // Delete confirmation
        document.getElementById('confirmDeleteBtn').addEventListener('click', () => this.performDelete());

        // File info modal
        document.getElementById('selectFileBtn').addEventListener('click', () => this.selectCurrentFile());
        document.getElementById('copyUrlBtn').addEventListener('click', () => this.copyFileUrl());

        // Upload progress modal
        document.getElementById('cancelUploadBtn').addEventListener('click', () => this.cancelUpload());
        document.getElementById('closeUploadBtn').addEventListener('click', () => this.closeUploadModal());

        // Breadcrumb navigation
        document.addEventListener('click', (e) => {
            if (e.target.closest('.breadcrumb-item a')) {
                e.preventDefault();
                const path = e.target.closest('a').dataset.path;
                this.navigateToPath(path);
            }
        });

        // Context menu
        document.addEventListener('contextmenu', (e) => this.handleContextMenu(e));
        document.addEventListener('click', () => this.hideContextMenu());
    }

    setupDragAndDrop() {
        const dropZone = document.getElementById('drop-zone');
        
        // Prevent default drag behaviors
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, this.preventDefaults, false);
            document.body.addEventListener(eventName, this.preventDefaults, false);
        });

        // Highlight drop zone when item is dragged over it
        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, () => this.highlight(dropZone), false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, () => this.unhighlight(dropZone), false);
        });

        // Handle dropped files
        dropZone.addEventListener('drop', (e) => this.handleDrop(e), false);
    }

    preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    highlight(element) {
        element.classList.add('drag-over');
    }

    unhighlight(element) {
        element.classList.remove('drag-over');
    }

    handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        this.handleFiles(files);
    }

    triggerFileUpload() {
        document.getElementById('file-input').click();
    }

    handleFileSelect(e) {
        const files = e.target.files;
        this.handleFiles(files);
    }

    async handleFiles(files) {
        if (this.isUploading) {
            this.showNotification('Upload already in progress', 'warning');
            return;
        }

        if (files.length === 0) return;

        // Check file limits
        if (files.length > this.maxFilesPerBatch) {
            this.showNotification(`Maximum ${this.maxFilesPerBatch} files allowed per batch`, 'error');
            return;
        }

        // Initialize upload process
        this.initializeUpload(Array.from(files));
    }

    async loadFiles() {
        this.showLoading();
        
        try {
            const response = await this.makeRequest(`/contents?path=${encodeURIComponent(this.currentPath)}&type=${this.currentType}`);
            
            if (response.success) {
                this.renderFiles(response.data);
                this.updateBreadcrumbs(response.breadcrumbs);
                this.updateStatusText(`${response.data.length} items`);
            } else {
                this.showNotification(response.message, 'error');
                this.renderFiles([]);
            }
        } catch (error) {
            this.showNotification('Failed to load files: ' + error.message, 'error');
            this.renderFiles([]);
        } finally {
            this.hideLoading();
        }
    }

    renderFiles(files) {
        // Store current files for view mode switching
        this.currentFiles = files;

        const gridContainer = document.getElementById('file-grid');
        const listContainer = document.getElementById('file-list-body');
        const emptyState = document.getElementById('empty-state');

        if (files.length === 0) {
            gridContainer.innerHTML = '';
            listContainer.innerHTML = '';
            emptyState.style.display = 'block';
            return;
        }

        emptyState.style.display = 'none';

        if (this.viewMode === 'grid') {
            this.renderGridView(files, gridContainer);
            listContainer.innerHTML = ''; // Clear list view
        } else {
            this.renderListView(files, listContainer);
            gridContainer.innerHTML = ''; // Clear grid view
        }
    }

    renderGridView(files, container) {
        container.innerHTML = files.map(file => this.createFileGridItem(file)).join('');
        this.attachFileEventListeners();
    }

    renderListView(files, container) {
        container.innerHTML = files.map(file => this.createFileListItem(file)).join('');
        this.attachFileEventListeners();
    }

    createFileGridItem(file) {
        const isImage = file.file_type === 'images' && file.is_image;
        const icon = this.getFileIcon(file);
        const thumbnail = isImage ? `<img src="${file.thumbnail || file.url}" class="file-thumbnail" alt="${file.name}">` : `<i class="${icon} file-icon"></i>`;

        return `
            <div class="file-item"
                 data-path="${file.path}"
                 data-type="${file.type}"
                 data-name="${file.name}"
                 data-url="${file.url || ''}"
                 data-size="${file.size || 0}"
                 data-modified="${file.modified || 0}"
                 data-is-image="${isImage}">
                <input type="checkbox" class="file-checkbox" data-path="${file.path}">
                <div class="file-actions">
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-outline-secondary file-info-btn" data-path="${file.path}" title="Info">
                            <i class="fas fa-info"></i>
                        </button>
                        <button type="button" class="btn btn-outline-secondary file-rename-btn" data-path="${file.path}" data-name="${file.name}" title="Rename">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-outline-danger file-delete-btn" data-path="${file.path}" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                ${thumbnail}
                <div class="file-name" title="${file.name}">${file.name}</div>
                <div class="file-size">${this.formatFileSize(file.size)}</div>
            </div>
        `;
    }

    createFileListItem(file) {
        const isImage = file.file_type === 'images' && file.is_image;
        const icon = this.getFileIcon(file);
        const thumbnail = isImage ? `<img src="${file.thumbnail || file.url}" class="file-thumbnail" alt="${file.name}">` : `<i class="${icon} file-icon"></i>`;

        return `
            <tr class="file-item"
                data-path="${file.path}"
                data-type="${file.type}"
                data-name="${file.name}"
                data-url="${file.url || ''}"
                data-size="${file.size || 0}"
                data-modified="${file.modified || 0}"
                data-is-image="${isImage}">
                <td>
                    <input type="checkbox" class="file-checkbox" data-path="${file.path}">
                </td>
                <td>
                    <div class="d-flex align-items-center">
                        ${thumbnail}
                        <span title="${file.name}">${file.name}</span>
                    </div>
                </td>
                <td class="file-size">${this.formatFileSize(file.size)}</td>
                <td>${this.formatDate(file.modified)}</td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-outline-secondary file-info-btn" data-path="${file.path}" title="Info">
                            <i class="fas fa-info"></i>
                        </button>
                        <button type="button" class="btn btn-outline-secondary file-rename-btn" data-path="${file.path}" data-name="${file.name}" title="Rename">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-outline-danger file-delete-btn" data-path="${file.path}" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }

    attachFileEventListeners() {
        // File selection
        document.querySelectorAll('.file-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', (e) => {
                const path = e.target.dataset.path;
                if (e.target.checked) {
                    this.selectedFiles.add(path);
                } else {
                    this.selectedFiles.delete(path);
                }
                this.updateSelectionUI();
            });
        });

        // File double-click
        document.querySelectorAll('.file-item').forEach(item => {
            item.addEventListener('dblclick', (e) => {
                e.preventDefault();
                const path = item.dataset.path;
                const type = item.dataset.type;

                if (type === 'folder') {
                    this.navigateToPath(path);
                } else {
                    this.selectFile(path);
                }
            });
        });

        // File action buttons
        document.querySelectorAll('.file-info-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                const path = btn.dataset.path;
                this.showFileInfo(path);
            });
        });

        document.querySelectorAll('.file-rename-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                const path = btn.dataset.path;
                const name = btn.dataset.name;
                this.showRenameModal(path, name);
            });
        });

        document.querySelectorAll('.file-delete-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                const path = btn.dataset.path;
                this.deleteFile(path);
            });
        });
    }

    setViewMode(mode) {
        this.viewMode = mode;
        
        // Update button states
        document.getElementById('view-grid').classList.toggle('active', mode === 'grid');
        document.getElementById('view-list').classList.toggle('active', mode === 'list');
        
        // Show/hide containers
        document.getElementById('file-grid').style.display = mode === 'grid' ? 'grid' : 'none';
        document.getElementById('file-list').style.display = mode === 'list' ? 'block' : 'none';
        
        // Re-render if files are loaded
        const currentFiles = this.getCurrentFiles();
        if (currentFiles.length > 0) {
            this.renderFiles(currentFiles);
        }
    }

    getCurrentFiles() {
        return this.currentFiles || [];
    }

    navigateToPath(path) {
        this.currentPath = path;
        this.selectedFiles.clear();
        this.loadFiles();
        this.updateSelectionUI();
    }

    updateBreadcrumbs(breadcrumbs) {
        const container = document.getElementById('breadcrumb');
        container.innerHTML = breadcrumbs.map(crumb => {
            if (crumb.path === this.currentPath) {
                return `<li class="breadcrumb-item active">${crumb.name}</li>`;
            } else {
                return `<li class="breadcrumb-item"><a href="#" data-path="${crumb.path}">${crumb.name}</a></li>`;
            }
        }).join('');
    }

    updateSelectionUI() {
        const count = this.selectedFiles.size;
        const deleteBtn = document.getElementById('delete-selected-btn');
        const selectionInfo = document.getElementById('selection-info');

        deleteBtn.disabled = count === 0;
        selectionInfo.textContent = count > 0 ? `${count} item(s) selected` : '';

        // Update checkboxes
        document.querySelectorAll('.file-checkbox').forEach(checkbox => {
            const path = checkbox.dataset.path;
            checkbox.checked = this.selectedFiles.has(path);

            const fileItem = checkbox.closest('.file-item');
            if (fileItem) {
                fileItem.classList.toggle('selected', checkbox.checked);
            }
        });
    }

    updateStatusText(text) {
        document.getElementById('status-text').textContent = text;
    }

    updateUI() {
        // Set initial view mode
        this.setViewMode(this.viewMode);

        // Set type filter
        document.getElementById('type-filter').value = this.currentType;
    }

    // Utility methods
    getFileIcon(file) {
        if (file.type === 'folder') return 'fas fa-folder folder';

        switch (file.file_type) {
            case 'images': return 'fas fa-image image';
            case 'documents': return 'fas fa-file-alt document';
            case 'videos': return 'fas fa-video video';
            case 'files': return 'fas fa-file-archive archive';
            default: return 'fas fa-file';
        }
    }

    formatFileSize(bytes) {
        if (bytes === 0) return '0 B';
        const k = 1024;
        const sizes = ['B', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
    }

    formatDate(timestamp) {
        return new Date(timestamp * 1000).toLocaleDateString();
    }

    async makeRequest(url, options = {}) {
        const defaultOptions = {
            headers: {
                'X-CSRF-TOKEN': this.config.csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            }
        };

        // Don't set Content-Type for FormData
        if (!(options.body instanceof FormData)) {
            defaultOptions.headers['Content-Type'] = 'application/json';
        }

        // Handle GET request parameters
        if (options.params && (!options.method || options.method === 'GET')) {
            const params = new URLSearchParams(options.params);
            url += '?' + params.toString();
            delete options.params;
        }

        const response = await fetch(this.config.baseUrl + url, {
            ...defaultOptions,
            ...options
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        return await response.json();
    }

    showLoading() {
        document.getElementById('loading').style.display = 'block';
        document.getElementById('file-grid').style.display = 'none';
        document.getElementById('file-list').style.display = 'none';
        document.getElementById('empty-state').style.display = 'none';
    }

    hideLoading() {
        document.getElementById('loading').style.display = 'none';
        this.setViewMode(this.viewMode); // Restore view mode
    }

    showUploadProgress() {
        // Implementation for upload progress UI
        this.updateStatusText('Uploading files...');
    }

    hideUploadProgress() {
        this.updateStatusText('Ready');
    }

    // Upload Progress System
    initializeUpload(files) {
        this.isUploading = true;
        this.uploadCancelled = false;
        this.uploadQueue = [];
        this.activeUploads.clear();
        this.completedUploads = [];
        this.failedUploads = [];
        this.totalBytesUploaded = 0;
        this.totalBytesToUpload = 0;
        this.uploadStartTime = Date.now();

        // Calculate total bytes
        files.forEach(file => {
            this.totalBytesToUpload += file.size;
            this.uploadQueue.push({
                id: this.generateUploadId(),
                file: file,
                status: 'pending',
                progress: 0,
                bytesUploaded: 0,
                error: null
            });
        });

        // Always show upload progress modal if enabled in config
        if (this.config.showProgress !== false) {
            this.showUploadProgressModal();
            this.renderUploadList();
        } else {
            // Fallback: show simple progress in status bar
            this.showSimpleProgress();
            this.updateStatusText('Preparing upload...');
        }

        this.startUploads();
    }

    generateUploadId() {
        return 'upload_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }

    showUploadProgressModal() {
        this.uploadProgressModal = new bootstrap.Modal(document.getElementById('uploadProgressModal'), {
            backdrop: 'static',
            keyboard: false
        });
        this.uploadProgressModal.show();
    }

    showSimpleProgress() {
        const simpleProgress = document.getElementById('simple-upload-progress');
        if (simpleProgress) {
            simpleProgress.style.display = 'block';
        }
    }

    hideSimpleProgress() {
        const simpleProgress = document.getElementById('simple-upload-progress');
        if (simpleProgress) {
            simpleProgress.style.display = 'none';
        }
    }

    updateSimpleProgress(percentage, text) {
        const progressBar = document.getElementById('simpleProgressBar');
        const progressText = document.getElementById('simple-progress-text');

        if (progressBar) {
            progressBar.style.width = percentage + '%';
            progressBar.setAttribute('aria-valuenow', percentage);
        }

        if (progressText && text) {
            progressText.textContent = text;
        }
    }

    renderUploadList() {
        const container = document.getElementById('uploadFilesList');
        container.innerHTML = this.uploadQueue.map(upload => this.createUploadItem(upload)).join('');
        this.updateOverallProgress();
    }

    createUploadItem(upload) {
        const statusIcon = this.getUploadStatusIcon(upload.status);
        const statusClass = this.getUploadStatusClass(upload.status);

        return `
            <div class="upload-item mb-3" id="upload-${upload.id}">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <div class="d-flex align-items-center">
                        <i class="${statusIcon} me-2 ${statusClass}"></i>
                        <span class="upload-filename" title="${upload.file.name}">${this.truncateFilename(upload.file.name)}</span>
                    </div>
                    <span class="upload-size text-muted">${this.formatFileSize(upload.file.size)}</span>
                </div>
                <div class="progress mb-1" style="height: 8px;">
                    <div class="progress-bar progress-bar-animated ${statusClass}"
                         role="progressbar"
                         style="width: ${upload.progress}%"
                         aria-valuenow="${upload.progress}"
                         aria-valuemin="0"
                         aria-valuemax="100">
                    </div>
                </div>
                <div class="upload-status text-muted small">
                    ${this.getUploadStatusText(upload)}
                </div>
            </div>
        `;
    }

    getUploadStatusIcon(status) {
        switch (status) {
            case 'pending': return 'fas fa-clock';
            case 'uploading': return 'fas fa-spinner fa-spin';
            case 'completed': return 'fas fa-check-circle';
            case 'failed': return 'fas fa-exclamation-circle';
            default: return 'fas fa-file';
        }
    }

    getUploadStatusClass(status) {
        switch (status) {
            case 'pending': return 'text-warning';
            case 'uploading': return 'text-primary';
            case 'completed': return 'text-success';
            case 'failed': return 'text-danger';
            default: return 'text-muted';
        }
    }

    getUploadStatusText(upload) {
        switch (upload.status) {
            case 'pending': return 'Waiting...';
            case 'uploading': return `Uploading... ${upload.progress}%`;
            case 'completed': return 'Upload complete';
            case 'failed': return upload.error || 'Upload failed';
            default: return 'Unknown status';
        }
    }

    truncateFilename(filename, maxLength = 30) {
        if (filename.length <= maxLength) return filename;
        const extension = filename.split('.').pop();
        const nameWithoutExt = filename.substring(0, filename.lastIndexOf('.'));
        const truncatedName = nameWithoutExt.substring(0, maxLength - extension.length - 4) + '...';
        return truncatedName + '.' + extension;
    }

    async startUploads() {
        while (this.uploadQueue.length > 0 && this.activeUploads.size < this.maxConcurrentUploads && !this.uploadCancelled) {
            const upload = this.uploadQueue.shift();
            this.activeUploads.set(upload.id, upload);
            this.uploadFile(upload);
        }
    }

    async uploadFile(upload) {
        try {
            upload.status = 'uploading';
            this.updateUploadItem(upload);

            const formData = new FormData();
            formData.append('upload', upload.file);
            formData.append('path', this.currentPath);

            // Only send type if it's a valid specific type
            if (['images', 'documents', 'videos', 'files'].includes(this.currentType)) {
                formData.append('type', this.currentType);
            }

            const response = await this.makeRequestWithProgress('/upload-single', {
                method: 'POST',
                body: formData
            }, (progressEvent) => {
                if (progressEvent.lengthComputable) {
                    upload.progress = Math.round((progressEvent.loaded / progressEvent.total) * 100);
                    upload.bytesUploaded = progressEvent.loaded;
                    this.updateUploadItem(upload);
                    this.updateOverallProgress();
                }
            });

            if (response.success) {
                upload.status = 'completed';
                this.completedUploads.push(upload);
            } else {
                upload.status = 'failed';
                upload.error = response.message;
                this.failedUploads.push(upload);
            }

        } catch (error) {
            upload.status = 'failed';
            upload.error = error.message;
            this.failedUploads.push(upload);
        } finally {
            this.activeUploads.delete(upload.id);
            this.updateUploadItem(upload);
            this.updateOverallProgress();

            // Start next upload if available
            if (!this.uploadCancelled) {
                this.startUploads();
            }

            // Check if all uploads are complete
            if (this.activeUploads.size === 0 && this.uploadQueue.length === 0) {
                this.finishUpload();
            }
        }
    }

    async makeRequestWithProgress(url, options = {}, onProgress = null) {
        return new Promise((resolve, reject) => {
            const xhr = new XMLHttpRequest();

            // Set up progress tracking
            if (onProgress && xhr.upload) {
                xhr.upload.addEventListener('progress', onProgress);
            }

            // Set up response handling
            xhr.addEventListener('load', () => {
                if (xhr.status >= 200 && xhr.status < 300) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        resolve(response);
                    } catch (e) {
                        reject(new Error('Invalid JSON response'));
                    }
                } else {
                    reject(new Error(`HTTP error! status: ${xhr.status}`));
                }
            });

            xhr.addEventListener('error', () => {
                reject(new Error('Network error'));
            });

            xhr.addEventListener('abort', () => {
                reject(new Error('Upload cancelled'));
            });

            // Open and send request
            xhr.open(options.method || 'GET', this.config.baseUrl + url);
            xhr.setRequestHeader('X-CSRF-TOKEN', this.config.csrfToken);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.send(options.body);
        });
    }

    updateUploadItem(upload) {
        const element = document.getElementById(`upload-${upload.id}`);
        if (element) {
            element.outerHTML = this.createUploadItem(upload);
        }
    }

    updateOverallProgress() {
        const totalFiles = this.completedUploads.length + this.failedUploads.length + this.activeUploads.size + this.uploadQueue.length;
        const completedFiles = this.completedUploads.length + this.failedUploads.length;
        const overallProgress = totalFiles > 0 ? Math.round((completedFiles / totalFiles) * 100) : 0;

        // Update modal progress bar
        const progressBar = document.getElementById('overallProgressBar');
        if (progressBar) {
            progressBar.style.width = overallProgress + '%';
            progressBar.setAttribute('aria-valuenow', overallProgress);
        }

        // Update modal progress text
        const progressText = document.getElementById('overallProgressText');
        if (progressText) {
            progressText.textContent = `${completedFiles} / ${totalFiles} files`;
        }

        // Update simple progress bar (fallback)
        this.updateSimpleProgress(overallProgress, `Uploading ${completedFiles}/${totalFiles} files`);

        // Update speed and time remaining
        this.updateUploadStats();
    }

    updateUploadStats() {
        const elapsed = (Date.now() - this.uploadStartTime) / 1000; // seconds
        const speed = elapsed > 0 ? this.totalBytesUploaded / elapsed : 0; // bytes per second

        const speedElement = document.getElementById('uploadSpeed');
        if (speedElement) {
            speedElement.textContent = this.formatFileSize(speed) + '/s';
        }

        const remaining = this.totalBytesToUpload - this.totalBytesUploaded;
        const timeRemaining = speed > 0 ? remaining / speed : 0;

        const timeElement = document.getElementById('timeRemaining');
        if (timeElement) {
            if (timeRemaining > 0 && timeRemaining < Infinity) {
                timeElement.textContent = this.formatTime(timeRemaining);
            } else {
                timeElement.textContent = 'Calculating...';
            }
        }
    }

    formatTime(seconds) {
        if (seconds < 60) {
            return Math.round(seconds) + 's';
        } else if (seconds < 3600) {
            return Math.round(seconds / 60) + 'm';
        } else {
            return Math.round(seconds / 3600) + 'h';
        }
    }

    finishUpload() {
        this.isUploading = false;

        // Hide simple progress bar
        this.hideSimpleProgress();

        // Show summary
        this.showUploadSummary();

        // Enable close button (only if modal exists)
        const uploadCloseBtn = document.getElementById('uploadCloseBtn');
        const closeUploadBtn = document.getElementById('closeUploadBtn');
        const cancelUploadBtn = document.getElementById('cancelUploadBtn');

        if (uploadCloseBtn) uploadCloseBtn.style.display = 'inline-block';
        if (closeUploadBtn) closeUploadBtn.style.display = 'inline-block';
        if (cancelUploadBtn) cancelUploadBtn.style.display = 'none';

        // Refresh file list
        this.loadFiles();

        // Reset file input
        document.getElementById('file-input').value = '';

        // Update status text
        this.updateStatusText('Upload complete');
    }

    showUploadSummary() {
        const summaryElement = document.getElementById('uploadSummary');
        const summaryText = document.getElementById('uploadSummaryText');

        if (this.failedUploads.length === 0) {
            // All successful
            summaryText.innerHTML = `
                <strong>${this.completedUploads.length} files uploaded successfully!</strong><br>
                <small>Total size: ${this.formatFileSize(this.totalBytesToUpload)}</small>
            `;
            summaryElement.style.display = 'block';
        } else if (this.completedUploads.length === 0) {
            // All failed
            this.showUploadErrors();
        } else {
            // Mixed results
            summaryText.innerHTML = `
                <strong>${this.completedUploads.length} files uploaded successfully</strong><br>
                <small>${this.failedUploads.length} files failed</small>
            `;
            summaryElement.style.display = 'block';
            this.showUploadErrors();
        }
    }

    showUploadErrors() {
        if (this.failedUploads.length === 0) return;

        const errorsElement = document.getElementById('uploadErrors');
        const errorsList = document.getElementById('uploadErrorsList');

        const errorsHtml = this.failedUploads.map(upload => `
            <div class="mb-2">
                <strong>${upload.file.name}</strong><br>
                <small class="text-muted">${upload.error}</small>
            </div>
        `).join('');

        errorsList.innerHTML = errorsHtml;
        errorsElement.style.display = 'block';
    }

    cancelUpload() {
        this.uploadCancelled = true;

        // Cancel active uploads
        this.activeUploads.forEach(upload => {
            upload.status = 'failed';
            upload.error = 'Cancelled by user';
        });

        // Clear queue
        this.uploadQueue = [];

        this.finishUpload();
    }

    closeUploadModal() {
        if (this.uploadProgressModal) {
            this.uploadProgressModal.hide();
        }
    }

    showNotification(message, type = 'info') {
        // Enhanced notification system with support for detailed messages
        const alertClass = {
            'success': 'alert-success',
            'error': 'alert-danger',
            'warning': 'alert-warning',
            'info': 'alert-info'
        }[type] || 'alert-info';

        // Format message for HTML display
        const formattedMessage = message.replace(/\n/g, '<br>');

        const notification = document.createElement('div');
        notification.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;

        // Adjust size based on message length and type
        const isError = type === 'error';
        const isLongMessage = message.length > 200;
        const maxWidth = isError && isLongMessage ? '600px' : '500px';

        notification.style.cssText = `
            top: 20px;
            right: 20px;
            z-index: 1060;
            min-width: 350px;
            max-width: ${maxWidth};
            max-height: 80vh;
            overflow-y: auto;
        `;

        notification.innerHTML = `
            <div style="white-space: pre-line; font-family: 'Segoe UI', system-ui, sans-serif; line-height: 1.4;">
                ${formattedMessage}
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" style="position: sticky; top: 0;"></button>
        `;

        document.body.appendChild(notification);

        // Auto-remove after longer time for detailed messages
        const autoRemoveTime = message.length > 100 ? 8000 : 5000;
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, autoRemoveTime);
    }

    // Modal methods
    showCreateFolderModal() {
        const modal = new bootstrap.Modal(document.getElementById('createFolderModal'));
        modal.show();
        document.getElementById('folderName').value = '';
    }

    async createFolder() {
        const name = document.getElementById('folderName').value.trim();
        if (!name) return;

        try {
            const response = await this.makeRequest('/create-folder', {
                method: 'POST',
                body: JSON.stringify({
                    path: this.currentPath,
                    name: name
                })
            });

            if (response.success) {
                this.showNotification(response.message, 'success');
                this.loadFiles();
                bootstrap.Modal.getInstance(document.getElementById('createFolderModal')).hide();
            } else {
                this.showNotification(response.message, 'error');
            }
        } catch (error) {
            this.showNotification('Failed to create folder: ' + error.message, 'error');
        }
    }

    showRenameModal(path, currentName) {
        const modal = new bootstrap.Modal(document.getElementById('renameModal'));
        document.getElementById('renamePath').value = path;
        document.getElementById('newName').value = currentName;
        modal.show();
    }

    async performRename() {
        const path = document.getElementById('renamePath').value;
        const newName = document.getElementById('newName').value.trim();

        if (!newName) return;

        try {
            const response = await this.makeRequest('/rename', {
                method: 'PUT',
                body: JSON.stringify({
                    path: path,
                    name: newName
                })
            });

            if (response.success) {
                this.showNotification(response.message, 'success');
                this.loadFiles();
                bootstrap.Modal.getInstance(document.getElementById('renameModal')).hide();
            } else {
                this.showNotification(response.message, 'error');
            }
        } catch (error) {
            this.showNotification('Failed to rename: ' + error.message, 'error');
        }
    }

    deleteFile(path) {
        this.showDeleteConfirmation([path]);
    }

    deleteSelected() {
        if (this.selectedFiles.size === 0) return;
        this.showDeleteConfirmation(Array.from(this.selectedFiles));
    }

    showDeleteConfirmation(paths) {
        const modal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
        const messageEl = document.getElementById('deleteMessage');
        const itemsListEl = document.getElementById('deleteItemsList');
        const itemsEl = document.getElementById('deleteItems');

        if (paths.length === 1) {
            messageEl.textContent = `Are you sure you want to delete "${paths[0].split('/').pop()}"?`;
            itemsListEl.style.display = 'none';
        } else {
            messageEl.textContent = `Are you sure you want to delete ${paths.length} items?`;
            itemsEl.innerHTML = paths.map(path => `<li>${path.split('/').pop()}</li>`).join('');
            itemsListEl.style.display = 'block';
        }

        // Store paths for deletion
        this.pathsToDelete = paths;
        modal.show();
    }

    async performDelete() {
        if (!this.pathsToDelete || this.pathsToDelete.length === 0) return;

        try {
            const endpoint = this.pathsToDelete.length === 1 ? '/delete' : '/delete-multiple';
            const body = this.pathsToDelete.length === 1
                ? { path: this.pathsToDelete[0] }
                : { paths: this.pathsToDelete };

            const response = await this.makeRequest(endpoint, {
                method: 'DELETE',
                body: JSON.stringify(body)
            });

            if (response.success) {
                this.showNotification(response.message, 'success');
                this.selectedFiles.clear();
                this.loadFiles();
                this.updateSelectionUI();
                bootstrap.Modal.getInstance(document.getElementById('confirmDeleteModal')).hide();
            } else {
                this.showNotification(response.message, 'error');
            }
        } catch (error) {
            this.showNotification('Failed to delete: ' + error.message, 'error');
        }
    }

    async showFileInfo(path) {
        try {
            // Get file info from the currently loaded files
            const fileItem = document.querySelector(`[data-path="${path}"]`);

            if (!fileItem) {
                this.showNotification('File not found', 'error');
                return;
            }

            // Get file details from the file item
            const fileData = this.getFileDataFromElement(fileItem);

            // Populate modal with file information
            this.populateFileInfoModal(fileData);

            // Show the modal
            const modal = new bootstrap.Modal(document.getElementById('fileInfoModal'));
            modal.show();

            // Store current file for selection
            this.currentFileInfo = fileData;

        } catch (error) {
            this.showNotification('Failed to load file info: ' + error.message, 'error');
        }
    }

    getFileDataFromElement(fileItem) {
        const path = fileItem.dataset.path;
        const name = fileItem.dataset.name;
        const type = fileItem.dataset.type;
        const url = fileItem.dataset.url || this.getFileUrl(path);
        const size = fileItem.dataset.size;
        const modified = fileItem.dataset.modified;
        const isImage = fileItem.dataset.isImage === 'true';

        // Fallback to element content if data attributes are not available
        const sizeElement = fileItem.querySelector('.file-size');
        const displaySize = size ? this.formatFileSize(parseInt(size)) : (sizeElement ? sizeElement.textContent : '0 B');

        return {
            name: name,
            path: path,
            url: url,
            size: displaySize,
            type: type,
            isImage: isImage,
            extension: this.getFileExtension(name),
            modified: modified ? this.formatDate(parseInt(modified)) : new Date().toLocaleDateString()
        };
    }

    populateFileInfoModal(fileData) {
        // Update file name
        const fileNameEl = document.getElementById('fileName');
        if (fileNameEl) fileNameEl.textContent = fileData.name;

        // Update file size
        const fileSizeEl = document.getElementById('fileSize');
        if (fileSizeEl) fileSizeEl.textContent = fileData.size;

        // Update file type
        const fileTypeEl = document.getElementById('fileType');
        if (fileTypeEl) fileTypeEl.textContent = fileData.extension.toUpperCase() + ' File';

        // Update modified date
        const fileModifiedEl = document.getElementById('fileModified');
        if (fileModifiedEl) fileModifiedEl.textContent = fileData.modified;

        // Update file path
        const filePathEl = document.getElementById('filePath');
        if (filePathEl) filePathEl.textContent = fileData.path;

        // Update file URL
        const fileUrlEl = document.getElementById('fileUrl');
        if (fileUrlEl) fileUrlEl.value = fileData.url;

        // Update preview
        this.updateFilePreview(fileData);
    }

    updateFilePreview(fileData) {
        const previewContainer = document.getElementById('filePreview');

        if (fileData.isImage) {
            // Show image preview with loading state
            previewContainer.innerHTML = `
                <img src="${fileData.url}"
                     class="img-fluid rounded"
                     style="max-width: 100%; max-height: 200px; object-fit: contain; opacity: 0; transition: opacity 0.3s;"
                     alt="${fileData.name}"
                     onload="this.style.opacity=1"
                     onerror="this.style.display='none'; this.nextElementSibling.style.display='block'">
                <div class="text-center" style="display: none;">
                    <i class="fas fa-image file-icon-large file-type-image"></i>
                    <p class="mt-2 text-muted">Image preview unavailable</p>
                </div>
            `;
        } else {
            // Show file icon based on type
            const fileType = this.getFileTypeFromExtension(fileData.extension);
            const icon = this.getFileIcon({
                type: fileData.type,
                file_type: fileType
            });

            const typeClass = `file-type-${fileType}`;

            previewContainer.innerHTML = `
                <div class="text-center">
                    <i class="${icon} file-icon-large ${typeClass}"></i>
                    <p class="mt-2 text-muted">${fileData.extension.toUpperCase()} File</p>
                    <small class="text-muted">${this.getFileTypeLabel(fileType)}</small>
                </div>
            `;
        }
    }

    getFileTypeLabel(fileType) {
        const labels = {
            'images': 'Image File',
            'documents': 'Document',
            'videos': 'Video File',
            'files': 'Archive/Other'
        };
        return labels[fileType] || 'File';
    }

    isImageFile(filename) {
        const imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp'];
        const extension = this.getFileExtension(filename);
        return imageExtensions.includes(extension.toLowerCase());
    }

    getFileExtension(filename) {
        return filename.split('.').pop() || '';
    }

    getFileTypeFromExtension(extension) {
        const ext = extension.toLowerCase();
        if (['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp'].includes(ext)) {
            return 'images';
        } else if (['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'rtf'].includes(ext)) {
            return 'documents';
        } else if (['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm'].includes(ext)) {
            return 'videos';
        } else {
            return 'files';
        }
    }

    selectFile(path) {
        if (this.config.isPopup && window.opener) {
            // Get file info first
            const fileItem = document.querySelector(`[data-path="${path}"]`);
            if (fileItem) {
                const fileUrl = this.getFileUrl(path);

                // Call parent window callback
                if (typeof window.opener.SetUrl === 'function') {
                    window.opener.SetUrl(fileUrl, path);
                } else {
                    // Fallback for form fields
                    const parentInput = window.opener.document.getElementById(this.config.fieldId);
                    const parentPreview = window.opener.document.getElementById('preview_thumbnail');

                    if (parentInput) {
                        parentInput.value = path;
                        parentInput.dispatchEvent(new Event('change', { bubbles: true }));
                    }

                    if (parentPreview) {
                        parentPreview.src = fileUrl;
                    }
                }

                window.close();
            }
        }
    }

    selectCurrentFile() {
        // Select the file that's currently shown in the info modal
        if (this.currentFileInfo) {
            this.selectFile(this.currentFileInfo.path);
            // Close the modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('fileInfoModal'));
            if (modal) {
                modal.hide();
            }
        }
    }

    async copyFileUrl() {
        const urlInput = document.getElementById('fileUrl');
        const url = urlInput.value;

        try {
            if (navigator.clipboard && window.isSecureContext) {
                // Use modern clipboard API
                await navigator.clipboard.writeText(url);
            } else {
                // Fallback for older browsers
                urlInput.select();
                document.execCommand('copy');
            }
            this.showNotification('URL copied to clipboard', 'success');
        } catch (error) {
            this.showNotification('Failed to copy URL', 'error');
        }
    }

    getFileUrl(path) {
        // Convert path to URL
        return window.location.origin + '/storage/' + path.replace(/^\/storage\//, '');
    }

    toggleSelectAll() {
        const checkboxes = document.querySelectorAll('.file-checkbox');
        const allSelected = this.selectedFiles.size === checkboxes.length;

        if (allSelected) {
            this.selectedFiles.clear();
        } else {
            checkboxes.forEach(checkbox => {
                this.selectedFiles.add(checkbox.dataset.path);
            });
        }

        this.updateSelectionUI();
    }

    async performSearch() {
        const query = document.getElementById('search-input').value.trim();
        if (query.length < 2) {
            this.showNotification('Search query must be at least 2 characters', 'warning');
            return;
        }

        try {
            this.showLoading();
            this.updateStatusText(`Searching for "${query}"...`);

            // Disable search button during search
            const searchBtn = document.getElementById('search-btn');
            searchBtn.disabled = true;
            searchBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

            const response = await this.makeRequest('/search', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                },
                params: {
                    query: query,
                    path: this.currentPath,
                    type: this.currentType
                }
            });

            if (response.success) {
                this.renderFiles(response.data);
                this.updateStatusText(response.message);
                this.showNotification(response.message, 'success');

                // Show clear search button
                document.getElementById('clear-search-btn').style.display = 'inline-block';

                // Update breadcrumb to show search results
                this.updateSearchBreadcrumb(query);
            } else {
                this.showNotification(response.message, 'error');
                this.updateStatusText('Search failed');
            }

        } catch (error) {
            this.showNotification('Search failed: ' + error.message, 'error');
            this.updateStatusText('Search failed');
        } finally {
            this.hideLoading();

            // Restore search button
            const searchBtn = document.getElementById('search-btn');
            searchBtn.disabled = false;
            searchBtn.innerHTML = '<i class="fas fa-search"></i>';
        }
    }

    updateSearchBreadcrumb(query) {
        const breadcrumb = document.getElementById('breadcrumb');
        breadcrumb.innerHTML = `
            <li class="breadcrumb-item">
                <a href="#" data-path="" onclick="fileManager.clearSearch()">
                    <i class="fas fa-home"></i> Home
                </a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
                <i class="fas fa-search"></i> Search: "${query}"
            </li>
        `;
    }

    clearSearch() {
        document.getElementById('search-input').value = '';
        document.getElementById('clear-search-btn').style.display = 'none';
        this.loadFiles();
        this.updateBreadcrumbs();
    }

    handleContextMenu(e) {
        // Implementation for context menu
        e.preventDefault();
        // Show context menu at cursor position
    }

    hideContextMenu() {
        // Implementation for hiding context menu
    }
}

// Initialize file manager when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.fileManager = new FileManager();
});
