<!-- File Info Modal -->
<div class="modal fade" id="fileInfoModal" tabindex="-1" aria-labelledby="fileInfoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="fileInfoModalLabel">
                    <i class="fas fa-info-circle me-2"></i>File Information
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4 text-center">
                        <div id="filePreview" class="mb-3">
                            <!-- File preview will be inserted here -->
                        </div>
                    </div>
                    <div class="col-md-8">
                        <table class="table table-borderless">
                            <tbody>
                                <tr>
                                    <td><strong>Name:</strong></td>
                                    <td id="fileName">-</td>
                                </tr>
                                <tr>
                                    <td><strong>Size:</strong></td>
                                    <td id="fileSize">-</td>
                                </tr>
                                <tr>
                                    <td><strong>Type:</strong></td>
                                    <td id="fileType">-</td>
                                </tr>
                                <tr>
                                    <td><strong>Modified:</strong></td>
                                    <td id="fileModified">-</td>
                                </tr>
                                <tr>
                                    <td><strong>Path:</strong></td>
                                    <td id="filePath">-</td>
                                </tr>
                                <tr>
                                    <td><strong>URL:</strong></td>
                                    <td>
                                        <div class="input-group">
                                            <input type="text" class="form-control form-control-sm" id="fileUrl" readonly>
                                            <button class="btn btn-outline-secondary btn-sm" type="button" id="copyUrlBtn">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="selectFileBtn">
                    <i class="fas fa-check me-1"></i>Select This File
                </button>
            </div>
        </div>
    </div>
</div>
