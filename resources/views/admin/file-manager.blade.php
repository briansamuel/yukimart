<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .image-item {
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        .image-item:hover {
            border-color: #007bff;
            transform: scale(1.05);
        }
        .image-item.selected {
            border-color: #28a745;
            background-color: #f8f9fa;
        }
        .image-thumbnail {
            width: 150px;
            height: 150px;
            object-fit: cover;
        }
    </style>
</head>
<body>
    <div class="container-fluid p-3">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4>Select Image</h4>
                    <button type="button" class="btn btn-secondary" onclick="window.close()">Close</button>
                </div>
                
                @if(count($images) > 0)
                    <div class="row">
                        @foreach($images as $image)
                            <div class="col-md-3 col-sm-4 col-6 mb-3">
                                <div class="card image-item" data-path="{{ $image['path'] }}" data-url="{{ $image['url'] }}">
                                    <img src="{{ $image['url'] }}" class="card-img-top image-thumbnail" alt="{{ $image['name'] }}">
                                    <div class="card-body p-2">
                                        <p class="card-text small mb-1">{{ $image['name'] }}</p>
                                        <small class="text-muted">{{ number_format($image['size'] / 1024, 1) }} KB</small>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="alert alert-info">
                        <h5>No images found</h5>
                        <p>No images have been uploaded yet. You can upload images using the "Upload New Image" option in the product form.</p>
                    </div>
                @endif
                
                <div class="mt-4">
                    <div class="row">
                        <div class="col-12">
                            <button id="selectBtn" class="btn btn-primary" disabled>Select Image</button>
                            <button type="button" class="btn btn-secondary ms-2" onclick="window.close()">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let selectedImage = null;
        const fieldId = '{{ $field_id }}';
        const editorField = new URLSearchParams(window.location.search).get('editor');
        
        // Handle image selection
        document.querySelectorAll('.image-item').forEach(item => {
            item.addEventListener('click', function() {
                // Remove previous selection
                document.querySelectorAll('.image-item').forEach(i => i.classList.remove('selected'));
                
                // Add selection to clicked item
                this.classList.add('selected');
                
                // Store selected image data
                selectedImage = {
                    path: this.dataset.path,
                    url: this.dataset.url
                };
                
                // Enable select button
                document.getElementById('selectBtn').disabled = false;
            });
        });
        
        // Handle select button click
        document.getElementById('selectBtn').addEventListener('click', function() {
            if (selectedImage && window.opener) {
                // Check if this is for TinyMCE editor
                if (editorField) {
                    // For TinyMCE integration
                    if (typeof window.opener.SetUrl === 'function') {
                        window.opener.SetUrl(selectedImage.url, selectedImage.path);
                    }
                } else {
                    // For regular form fields
                    if (typeof window.opener.SetUrl === 'function') {
                        window.opener.SetUrl(selectedImage.url, selectedImage.path);
                    } else {
                        // Fallback: try to set the value directly
                        const parentInput = window.opener.document.getElementById(fieldId);
                        const parentPreview = window.opener.document.getElementById('preview_thumbnail');
                        
                        if (parentInput) {
                            parentInput.value = selectedImage.path;
                            // Trigger change event
                            const event = new Event('change', { bubbles: true });
                            parentInput.dispatchEvent(event);
                        }
                        
                        if (parentPreview) {
                            parentPreview.src = selectedImage.url;
                        }
                    }
                }
                
                // Close the popup
                window.close();
            }
        });
        
        // Handle double-click to select
        document.querySelectorAll('.image-item').forEach(item => {
            item.addEventListener('dblclick', function() {
                this.click(); // Select the image
                document.getElementById('selectBtn').click(); // Trigger select
            });
        });
    </script>
</body>
</html>
