/**
 * Product Image Upload Manager
 * Handles image upload, preview, remove, and pin functionality
 */

class ProductImageUpload {
    constructor(prefix = '') {
        this.prefix = prefix; // 'edit_' for edit modal, '' for create modal
        this.maxFileSize = 2 * 1024 * 1024; // 2MB
        this.init();
    }

    init() {
        this.bindMainImageEvents();
        this.bindAdditionalImageEvents();
    }

    /**
     * Bind events for main image upload
     */
    bindMainImageEvents() {
        const mainImageArea = document.getElementById(`${this.prefix}product_main_image_area`);
        const mainImageFile = document.getElementById(`${this.prefix}product_thumbnail_file`);
        const mainImagePreview = document.getElementById(`${this.prefix}product_main_image_preview`);

        if (mainImageArea && mainImageFile) {
            // Click on area to trigger file input (only when placeholder is visible)
            mainImageArea.addEventListener('click', (e) => {
                // Don't trigger if clicking on remove button
                if (e.target.closest('.btn-danger')) {
                    return;
                }

                // Only trigger file input if no image is shown
                if (mainImagePreview && mainImagePreview.classList.contains('d-none')) {
                    mainImageFile.click();
                }
            });

            // Handle file selection
            mainImageFile.addEventListener('change', (e) => {
                const file = e.target.files[0];
                if (file) {
                    this.handleImageUpload(file, 'main');
                }
            });
        }
    }

    /**
     * Bind events for additional images upload
     */
    bindAdditionalImageEvents() {
        for (let i = 0; i < 4; i++) {
            const imageArea = document.getElementById(`${this.prefix}product_additional_image_area_${i}`);
            const imageFile = document.getElementById(`${this.prefix}product_image_file_${i}`);
            const imagePreview = document.getElementById(`${this.prefix}product_additional_image_preview_${i}`);

            if (imageArea && imageFile) {
                // Click on area to trigger file input
                imageArea.addEventListener('click', (e) => {
                    // Don't trigger if clicking on remove or pin buttons
                    if (e.target.closest('.btn-danger') || e.target.closest('.btn-primary')) {
                        return;
                    }

                    imageFile.click();
                });

                // Handle file selection
                imageFile.addEventListener('change', (e) => {
                    const file = e.target.files[0];
                    if (file) {
                        this.handleImageUpload(file, i);
                    }
                });
            }
        }
    }

    /**
     * Handle image upload and preview
     * @param {File} file - The image file
     * @param {string|number} index - 'main' or 0-3
     */
    handleImageUpload(file, index) {
        // Validate file size
        if (file.size > this.maxFileSize) {
            Swal.fire({
                icon: 'error',
                title: 'Lỗi',
                text: 'Kích thước ảnh không được vượt quá 2MB',
            });
            return;
        }

        // Validate file type
        if (!file.type.startsWith('image/')) {
            Swal.fire({
                icon: 'error',
                title: 'Lỗi',
                text: 'Vui lòng chọn file ảnh',
            });
            return;
        }

        // Read and preview image
        const reader = new FileReader();
        reader.onload = (e) => {
            this.previewImage(e.target.result, index);
        };
        reader.readAsDataURL(file);
    }

    /**
     * Preview image
     * @param {string} imageUrl - Base64 or URL of image
     * @param {string|number} index - 'main' or 0-3
     */
    previewImage(imageUrl, index) {
        if (index === 'main') {
            const preview = document.getElementById(`${this.prefix}product_main_image_preview`);
            const placeholder = document.getElementById(`${this.prefix}product_main_image_placeholder`);
            const img = document.getElementById(`${this.prefix}product_main_image_img`);
            const hiddenInput = document.getElementById(`${this.prefix}product_thumbnail`);

            if (preview && placeholder && img && hiddenInput) {
                img.src = imageUrl;
                preview.classList.remove('d-none');
                placeholder.classList.add('d-none');
                hiddenInput.value = imageUrl;
            }
        } else {
            const preview = document.getElementById(`${this.prefix}product_additional_image_preview_${index}`);
            const placeholder = document.getElementById(`${this.prefix}product_additional_image_placeholder_${index}`);
            const img = document.getElementById(`${this.prefix}product_additional_image_img_${index}`);
            const hiddenInput = document.getElementById(`${this.prefix}product_image_${index}`);

            if (preview && placeholder && img && hiddenInput) {
                img.src = imageUrl;
                preview.classList.remove('d-none');
                placeholder.classList.add('d-none');
                hiddenInput.value = imageUrl;
            }
        }
    }

    /**
     * Remove image
     * @param {string|number} index - 'main' or 0-3
     */
    removeImage(index) {
        if (index === 'main') {
            const preview = document.getElementById(`${this.prefix}product_main_image_preview`);
            const placeholder = document.getElementById(`${this.prefix}product_main_image_placeholder`);
            const img = document.getElementById(`${this.prefix}product_main_image_img`);
            const hiddenInput = document.getElementById(`${this.prefix}product_thumbnail`);
            const fileInput = document.getElementById(`${this.prefix}product_thumbnail_file`);

            if (preview && placeholder && img && hiddenInput && fileInput) {
                img.src = '';
                preview.classList.add('d-none');
                placeholder.classList.remove('d-none');
                hiddenInput.value = '';
                fileInput.value = '';
            }
        } else {
            const preview = document.getElementById(`${this.prefix}product_additional_image_preview_${index}`);
            const placeholder = document.getElementById(`${this.prefix}product_additional_image_placeholder_${index}`);
            const img = document.getElementById(`${this.prefix}product_additional_image_img_${index}`);
            const hiddenInput = document.getElementById(`${this.prefix}product_image_${index}`);
            const fileInput = document.getElementById(`${this.prefix}product_image_file_${index}`);

            if (preview && placeholder && img && hiddenInput && fileInput) {
                img.src = '';
                preview.classList.add('d-none');
                placeholder.classList.remove('d-none');
                hiddenInput.value = '';
                fileInput.value = '';
            }
        }
    }

    /**
     * Pin image - swap additional image with main image
     * @param {number} index - 0-3
     */
    pinImage(index) {
        // Get main image data
        const mainImg = document.getElementById(`${this.prefix}product_main_image_img`);
        const mainHiddenInput = document.getElementById(`${this.prefix}product_thumbnail`);
        const mainFileInput = document.getElementById(`${this.prefix}product_thumbnail_file`);

        // Get additional image data
        const additionalImg = document.getElementById(`${this.prefix}product_additional_image_img_${index}`);
        const additionalHiddenInput = document.getElementById(`${this.prefix}product_image_${index}`);
        const additionalFileInput = document.getElementById(`${this.prefix}product_image_file_${index}`);

        if (!mainImg || !additionalImg || !mainHiddenInput || !additionalHiddenInput) {
            return;
        }

        // Swap image sources
        const tempSrc = mainImg.src;
        const tempValue = mainHiddenInput.value;

        mainImg.src = additionalImg.src;
        mainHiddenInput.value = additionalHiddenInput.value;

        additionalImg.src = tempSrc;
        additionalHiddenInput.value = tempValue;

        // Update visibility
        if (mainHiddenInput.value) {
            document.getElementById(`${this.prefix}product_main_image_preview`).classList.remove('d-none');
            document.getElementById(`${this.prefix}product_main_image_placeholder`).classList.add('d-none');
        } else {
            document.getElementById(`${this.prefix}product_main_image_preview`).classList.add('d-none');
            document.getElementById(`${this.prefix}product_main_image_placeholder`).classList.remove('d-none');
        }

        if (additionalHiddenInput.value) {
            document.getElementById(`${this.prefix}product_additional_image_preview_${index}`).classList.remove('d-none');
            document.getElementById(`${this.prefix}product_additional_image_placeholder_${index}`).classList.add('d-none');
        } else {
            document.getElementById(`${this.prefix}product_additional_image_preview_${index}`).classList.add('d-none');
            document.getElementById(`${this.prefix}product_additional_image_placeholder_${index}`).classList.remove('d-none');
        }

        // Clear file inputs (since we're swapping base64 data)
        if (mainFileInput) mainFileInput.value = '';
        if (additionalFileInput) additionalFileInput.value = '';
    }

    /**
     * Load existing images (for edit modal)
     * @param {object} product - Product data with images
     */
    loadImages(product) {
        // Load main thumbnail
        if (product.product_thumbnail) {
            const thumbnailUrl = this.getImageUrl(product.product_thumbnail);
            this.previewImage(thumbnailUrl, 'main');
        }

        // Load additional images
        if (product.images && Array.isArray(product.images)) {
            product.images.forEach((image, index) => {
                if (index < 4 && image.image_path) {
                    const imageUrl = this.getImageUrl(image.image_path);
                    this.previewImage(imageUrl, index);
                }
            });
        }
    }

    /**
     * Get full image URL from path
     * @param {string} path - Image path
     * @return {string} Full URL
     */
    getImageUrl(path) {
        // If it's already a full URL or base64, return as is
        if (path.startsWith('http') || path.startsWith('data:')) {
            return path;
        }
        // Otherwise, prepend storage URL
        return '/storage/' + path;
    }

    /**
     * Clear all images
     */
    clearAll() {
        this.removeImage('main');
        for (let i = 0; i < 4; i++) {
            this.removeImage(i);
        }
    }
}

// Global functions for onclick handlers
function removeProductImage(index) {
    if (window.createProductImageUpload) {
        window.createProductImageUpload.removeImage(index);
    }
}

function pinProductImage(index) {
    if (window.createProductImageUpload) {
        window.createProductImageUpload.pinImage(index);
    }
}

function removeEditProductImage(index) {
    if (window.editProductImageUpload) {
        window.editProductImageUpload.removeImage(index);
    }
}

function pinEditProductImage(index) {
    if (window.editProductImageUpload) {
        window.editProductImageUpload.pinImage(index);
    }
}

