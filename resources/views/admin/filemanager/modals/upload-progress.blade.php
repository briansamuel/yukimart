<!-- Upload Progress Modal -->
<div class="modal fade" id="uploadProgressModal" tabindex="-1" aria-labelledby="uploadProgressModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="uploadProgressModalLabel">
                    <i class="fas fa-upload me-2"></i>Uploading Files
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="uploadCloseBtn" style="display: none;"></button>
            </div>
            <div class="modal-body">
                <!-- Overall Progress -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-bold">Overall Progress</span>
                        <span id="overallProgressText">0 / 0 files</span>
                    </div>
                    <div class="progress mb-2" style="height: 12px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                             role="progressbar"
                             id="overallProgressBar"
                             style="width: 0%"
                             aria-valuenow="0"
                             aria-valuemin="0"
                             aria-valuemax="100">
                        </div>
                    </div>
                    <div class="d-flex justify-content-between text-muted small">
                        <span id="uploadSpeed">0 KB/s</span>
                        <span id="timeRemaining">Calculating...</span>
                    </div>
                </div>

                <!-- Individual File Progress -->
                <div class="upload-files-container">
                    <h6 class="mb-3">File Progress</h6>
                    <div id="uploadFilesList" class="upload-files-list">
                        <!-- Individual file progress items will be inserted here -->
                    </div>
                </div>

                <!-- Upload Summary -->
                <div id="uploadSummary" class="mt-4" style="display: none;">
                    <div class="alert alert-success">
                        <h6 class="alert-heading">
                            <i class="fas fa-check-circle me-2"></i>Upload Complete!
                        </h6>
                        <div id="uploadSummaryText"></div>
                    </div>
                </div>

                <!-- Upload Errors -->
                <div id="uploadErrors" class="mt-4" style="display: none;">
                    <div class="alert alert-danger">
                        <h6 class="alert-heading">
                            <i class="fas fa-exclamation-triangle me-2"></i>Upload Errors
                        </h6>
                        <div id="uploadErrorsList"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" id="cancelUploadBtn">
                    <i class="fas fa-times me-1"></i>Cancel Upload
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="closeUploadBtn" style="display: none;">
                    <i class="fas fa-check me-1"></i>Close
                </button>
            </div>
        </div>
    </div>
</div>
