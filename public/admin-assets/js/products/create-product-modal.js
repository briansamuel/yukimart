/**
 * Create Product Modal Manager
 * Handles product creation modal functionality
 */

class CreateProductModal {
    constructor() {
        this.modal = null;
        this.form = null;
        this.saveAndCreateNew = false;
        this.init();
    }

    init() {
        this.modal = new bootstrap.Modal(document.getElementById('kt_modal_create_product'));
        this.form = document.getElementById('kt_modal_create_product_form');

        // Initialize image upload manager
        window.createProductImageUpload = new ProductImageUpload('');

        this.bindEvents();
        this.initTooltips();
    }

    bindEvents() {
        // Open modal when clicking "Thêm mới" button
        const addProductBtn = document.getElementById('add_product_btn');
        if (addProductBtn) {
            addProductBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.openModal();
            });
        }

        // Form submit
        if (this.form) {
            this.form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.saveAndCreateNew = false;
                this.submitForm();
            });
        }

        // Save and create new button
        const saveAndCreateNewBtn = document.getElementById('save_and_create_new_btn');
        if (saveAndCreateNewBtn) {
            saveAndCreateNewBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.saveAndCreateNew = true;
                this.submitForm();
            });
        }

        // Create category link
        const createCategoryLink = document.getElementById('create_category_link');
        if (createCategoryLink) {
            createCategoryLink.addEventListener('click', (e) => {
                e.preventDefault();
                this.openCreateCategoryModal();
            });
        }

        // Create brand link
        const createBrandLink = document.getElementById('create_brand_link');
        if (createBrandLink) {
            createBrandLink.addEventListener('click', (e) => {
                e.preventDefault();
                this.openCreateBrandModal();
            });
        }

        // Setup price link
        const setupPriceLink = document.getElementById('setup_price_link');
        if (setupPriceLink) {
            setupPriceLink.addEventListener('click', (e) => {
                e.preventDefault();
                this.openSetupPriceModal();
            });
        }

        // Setup unit attribute button
        const setupUnitAttributeBtn = document.getElementById('setup_unit_attribute_btn');
        if (setupUnitAttributeBtn) {
            setupUnitAttributeBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.openSetupUnitAttributeModal();
            });
        }

        // Setup commission button
        const setupCommissionBtn = document.getElementById('setup_commission_btn');
        if (setupCommissionBtn) {
            setupCommissionBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.openSetupCommissionModal();
            });
        }

        // Enable points toggle
        const enablePointsToggle = document.getElementById('enable_points');
        if (enablePointsToggle) {
            enablePointsToggle.addEventListener('change', (e) => {
                const pointsSection = document.getElementById('points_section');
                if (e.target.checked) {
                    pointsSection.classList.add('show');
                } else {
                    pointsSection.classList.remove('show');
                }
            });
        }
    }

    openModal() {
        this.resetForm();
        this.modal.show();
    }

    closeModal() {
        this.modal.hide();
    }

    resetForm() {
        if (this.form) {
            this.form.reset();

            // Reset images using ProductImageUpload
            if (window.createProductImageUpload) {
                window.createProductImageUpload.clearAll();
            }

            // Reset collapsible sections
            document.querySelectorAll('.collapse').forEach(el => {
                el.classList.remove('show');
            });
        }
    }

    async submitForm() {
        if (!this.validateForm()) {
            return;
        }

        const formData = new FormData(this.form);
        const saveBtn = document.getElementById('save_product_btn');
        const indicatorLabel = saveBtn.querySelector('.indicator-label');
        const indicatorProgress = saveBtn.querySelector('.indicator-progress');

        // Show loading
        indicatorLabel.classList.add('d-none');
        indicatorProgress.classList.remove('d-none');
        saveBtn.disabled = true;

        try {
            const response = await fetch('/admin/products', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                // Show success message
                this.showNotification('success', data.message || 'Sản phẩm đã được tạo thành công!');

                if (this.saveAndCreateNew) {
                    // Reset form and keep modal open
                    this.resetForm();
                } else {
                    // Close modal
                    this.closeModal();
                }

                // Reload product table
                if (typeof window.productTableManager !== 'undefined') {
                    window.productTableManager.loadData();
                }
            } else {
                this.showNotification('error', data.message || 'Có lỗi xảy ra khi tạo sản phẩm!');
            }
        } catch (error) {
            console.error('Error creating product:', error);
            this.showNotification('error', 'Có lỗi xảy ra khi tạo sản phẩm!');
        } finally {
            // Hide loading
            indicatorLabel.classList.remove('d-none');
            indicatorProgress.classList.add('d-none');
            saveBtn.disabled = false;
        }
    }

    validateForm() {
        const productName = this.form.querySelector('[name="product_name"]');
        if (!productName || !productName.value.trim()) {
            this.showNotification('error', 'Vui lòng nhập tên hàng!');
            productName.focus();
            return false;
        }
        return true;
    }



    initTooltips() {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    openCreateCategoryModal() {
        const categoryModal = new bootstrap.Modal(document.getElementById('kt_modal_create_category'));
        categoryModal.show();
    }

    openCreateBrandModal() {
        const brandModal = new bootstrap.Modal(document.getElementById('kt_modal_create_brand'));
        brandModal.show();
    }

    openSetupPriceModal() {
        const priceModal = new bootstrap.Modal(document.getElementById('kt_modal_setup_price'));
        priceModal.show();
    }

    openSetupUnitAttributeModal() {
        const unitAttributeModal = new bootstrap.Modal(document.getElementById('kt_modal_setup_unit_attribute'));
        unitAttributeModal.show();
    }

    openSetupCommissionModal() {
        const commissionModal = new bootstrap.Modal(document.getElementById('kt_modal_setup_commission'));
        commissionModal.show();
    }

    showNotification(type, message) {
        // Use Toastr or SweetAlert2 for notifications
        if (typeof toastr !== 'undefined') {
            toastr[type](message);
        } else if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: type === 'success' ? 'success' : 'error',
                title: type === 'success' ? 'Thành công!' : 'Lỗi!',
                text: message,
                timer: 3000,
                showConfirmButton: false
            });
        } else {
            alert(message);
        }
    }
}

/**
 * Category Modal Manager
 */
class CategoryModalManager {
    constructor() {
        this.modal = null;
        this.form = null;
        this.init();
    }

    init() {
        this.modal = new bootstrap.Modal(document.getElementById('kt_modal_create_category'));
        this.form = document.getElementById('kt_modal_create_category_form');

        if (this.form) {
            this.form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.submitForm();
            });
        }

        // Load parent categories when modal is shown
        const modalElement = document.getElementById('kt_modal_create_category');
        if (modalElement) {
            modalElement.addEventListener('shown.bs.modal', () => {
                this.loadParentCategories();
            });
        }

        // Initialize Select2 for parent category dropdown
        const parentSelect = $('#parent_category_select');
        if (parentSelect.length) {
            parentSelect.select2({
                dropdownParent: $('#kt_modal_create_category'),
                placeholder: 'Chọn nhóm hàng cha (tùy chọn)',
                allowClear: true
            });
        }
    }

    async loadParentCategories() {
        try {
            const response = await fetch('/admin/categories/tree', {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                }
            });

            if (!response.ok) {
                throw new Error('Failed to load categories');
            }

            const result = await response.json();

            if (result.success && result.data) {
                const parentSelect = $('#parent_category_select');

                // Clear existing options except the first one
                parentSelect.find('option:not(:first)').remove();

                // Add category options with hierarchical structure
                result.data.forEach(category => {
                    const option = new Option(category.text, category.id, false, false);
                    parentSelect.append(option);
                });

                // Trigger change to update Select2
                parentSelect.trigger('change');
            }
        } catch (error) {
            console.error('Error loading parent categories:', error);
        }
    }

    async submitForm() {
        const formData = new FormData(this.form);
        const saveBtn = document.getElementById('save_category_btn');
        const indicatorLabel = saveBtn.querySelector('.indicator-label');
        const indicatorProgress = saveBtn.querySelector('.indicator-progress');

        indicatorLabel.classList.add('d-none');
        indicatorProgress.classList.remove('d-none');
        saveBtn.disabled = true;

        try {
            // Prepare data for category creation
            const categoryData = {
                name: formData.get('category_name'),
                description: formData.get('category_description'),
                parent_id: formData.get('parent_id') || null,
                is_active: true,
                show_in_menu: true,
                show_on_homepage: false
            };

            const response = await fetch('/admin/categories', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(categoryData)
            });

            const data = await response.json();

            if (data.success) {
                // Add new category to select
                const categorySelect = document.getElementById('category_select');
                if (categorySelect && data.data) {
                    const option = new Option(data.data.name, data.data.id, true, true);
                    categorySelect.add(option);
                }

                this.modal.hide();
                this.form.reset();

                if (typeof toastr !== 'undefined') {
                    toastr.success('Nhóm hàng đã được tạo thành công!');
                }
            } else {
                if (typeof toastr !== 'undefined') {
                    toastr.error(data.message || 'Có lỗi xảy ra!');
                }
            }
        } catch (error) {
            console.error('Error creating category:', error);
            if (typeof toastr !== 'undefined') {
                toastr.error('Có lỗi xảy ra khi tạo nhóm hàng!');
            }
        } finally {
            indicatorLabel.classList.remove('d-none');
            indicatorProgress.classList.add('d-none');
            saveBtn.disabled = false;
        }
    }
}

/**
 * Brand Modal Manager
 */
class BrandModalManager {
    constructor() {
        this.modal = null;
        this.form = null;
        this.init();
    }

    init() {
        this.modal = new bootstrap.Modal(document.getElementById('kt_modal_create_brand'));
        this.form = document.getElementById('kt_modal_create_brand_form');

        if (this.form) {
            this.form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.submitForm();
            });
        }
    }

    async submitForm() {
        const formData = new FormData(this.form);
        const saveBtn = document.getElementById('save_brand_btn');
        const indicatorLabel = saveBtn.querySelector('.indicator-label');
        const indicatorProgress = saveBtn.querySelector('.indicator-progress');

        indicatorLabel.classList.add('d-none');
        indicatorProgress.classList.remove('d-none');
        saveBtn.disabled = true;

        try {
            // For now, just add to select without backend
            const brandName = formData.get('brand_name');
            const brandSelect = document.getElementById('brand_select');

            if (brandSelect && brandName) {
                const option = new Option(brandName, brandName, true, true);
                brandSelect.add(option);
            }

            this.modal.hide();
            this.form.reset();

            if (typeof toastr !== 'undefined') {
                toastr.success('Thương hiệu đã được tạo thành công!');
            }
        } catch (error) {
            console.error('Error creating brand:', error);
            if (typeof toastr !== 'undefined') {
                toastr.error('Có lỗi xảy ra khi tạo thương hiệu!');
            }
        } finally {
            indicatorLabel.classList.remove('d-none');
            indicatorProgress.classList.add('d-none');
            saveBtn.disabled = false;
        }
    }
}

/**
 * Setup Modals Manager
 */
class SetupModalsManager {
    constructor() {
        this.initPriceModal();
        this.initUnitAttributeModal();
        this.initCommissionModal();
    }

    initPriceModal() {
        const form = document.getElementById('kt_modal_setup_price_form');
        if (form) {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                const costPrice = form.querySelector('[name="setup_cost_price"]').value;
                const salePrice = form.querySelector('[name="setup_sale_price"]').value;

                // Update main form
                document.querySelector('[name="cost_price"]').value = costPrice;
                document.querySelector('[name="sale_price"]').value = salePrice;

                bootstrap.Modal.getInstance(document.getElementById('kt_modal_setup_price')).hide();

                if (typeof toastr !== 'undefined') {
                    toastr.success('Đã áp dụng thiết lập giá!');
                }
            });
        }
    }

    initUnitAttributeModal() {
        const form = document.getElementById('kt_modal_setup_unit_attribute_form');
        if (form) {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                bootstrap.Modal.getInstance(document.getElementById('kt_modal_setup_unit_attribute')).hide();

                if (typeof toastr !== 'undefined') {
                    toastr.success('Đã áp dụng thiết lập đơn vị tính và thuộc tính!');
                }
            });
        }
    }

    initCommissionModal() {
        const form = document.getElementById('kt_modal_setup_commission_form');
        if (form) {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                bootstrap.Modal.getInstance(document.getElementById('kt_modal_setup_commission')).hide();

                if (typeof toastr !== 'undefined') {
                    toastr.success('Đã áp dụng thiết lập hoa hồng!');
                }
            });
        }
    }
}

/**
 * Edit Product Modal Manager
 */
class EditProductModal {
    constructor() {
        this.modal = null;
        this.form = null;
        this.productId = null;
        this.init();
    }

    init() {
        this.modal = new bootstrap.Modal(document.getElementById('kt_modal_edit_product'));
        this.form = document.getElementById('kt_modal_edit_product_form');

        // Initialize image upload manager for edit modal
        window.editProductImageUpload = new ProductImageUpload('edit_');

        this.bindEvents();
    }

    bindEvents() {
        // Form submit
        if (this.form) {
            this.form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.submitForm();
            });
        }

        // Create category link
        const createCategoryLink = document.getElementById('edit_create_category_link');
        if (createCategoryLink) {
            createCategoryLink.addEventListener('click', (e) => {
                e.preventDefault();
                window.categoryModalManager.modal.show();
            });
        }

        // Create brand link
        const createBrandLink = document.getElementById('edit_create_brand_link');
        if (createBrandLink) {
            createBrandLink.addEventListener('click', (e) => {
                e.preventDefault();
                window.brandModalManager.modal.show();
            });
        }

        // Setup price link
        const setupPriceLink = document.getElementById('edit_setup_price_link');
        if (setupPriceLink) {
            setupPriceLink.addEventListener('click', (e) => {
                e.preventDefault();
                this.openSetupPriceModal();
            });
        }

        // Setup unit attribute button
        const setupUnitAttributeBtn = document.getElementById('edit_setup_unit_attribute_btn');
        if (setupUnitAttributeBtn) {
            setupUnitAttributeBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.openSetupUnitAttributeModal();
            });
        }

        // Setup commission button
        const setupCommissionBtn = document.getElementById('edit_setup_commission_btn');
        if (setupCommissionBtn) {
            setupCommissionBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.openSetupCommissionModal();
            });
        }

        // Enable points toggle
        const enablePointsToggle = document.getElementById('edit_enable_points');
        if (enablePointsToggle) {
            enablePointsToggle.addEventListener('change', (e) => {
                const pointsSection = document.getElementById('edit_points_section');
                if (e.target.checked) {
                    pointsSection.classList.add('show');
                } else {
                    pointsSection.classList.remove('show');
                }
            });
        }
    }

    async openModal(productId) {
        this.productId = productId;

        // Show loading indicator
        this.showLoadingIndicator();

        await this.loadProductData(productId);

        // Hide loading indicator
        this.hideLoadingIndicator();

        this.modal.show();
    }

    showLoadingIndicator() {
        // Create loading overlay
        const loadingOverlay = document.createElement('div');
        loadingOverlay.id = 'edit_product_loading_overlay';
        loadingOverlay.className = 'position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center';
        loadingOverlay.style.cssText = 'background: rgba(0,0,0,0.5); z-index: 10000;';
        loadingOverlay.innerHTML = `
            <div class="text-center">
                <div class="spinner-border text-light" role="status" style="width: 3rem; height: 3rem;">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <div class="text-light mt-3 fw-bold">Đang tải dữ liệu sản phẩm...</div>
            </div>
        `;
        document.body.appendChild(loadingOverlay);
    }

    hideLoadingIndicator() {
        const loadingOverlay = document.getElementById('edit_product_loading_overlay');
        if (loadingOverlay) {
            loadingOverlay.remove();
        }
    }

    async loadProductData(productId) {
        try {
            const response = await fetch(`/admin/products/${productId}`, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                }
            });

            const data = await response.json();

            if (data.success && data.product) {
                this.populateForm(data.product);
            } else {
                this.showNotification('error', 'Không thể tải dữ liệu sản phẩm!');
            }
        } catch (error) {
            console.error('Error loading product:', error);
            this.showNotification('error', 'Có lỗi xảy ra khi tải dữ liệu sản phẩm!');
        }
    }

    populateForm(product) {
        // Set product ID
        document.getElementById('edit_product_id').value = product.id;

        // Set basic fields
        document.getElementById('edit_sku').value = product.sku || '';
        document.getElementById('edit_barcode').value = product.barcode || '';
        document.getElementById('edit_product_name').value = product.product_name || '';

        // Set category
        const categorySelect = document.getElementById('edit_category_select');
        if (categorySelect && product.category_id) {
            categorySelect.value = product.category_id;
        }

        // Set brand
        const brandSelect = document.getElementById('edit_brand_select');
        if (brandSelect && product.brand) {
            // Add brand option if not exists
            let brandOption = Array.from(brandSelect.options).find(opt => opt.value === product.brand);
            if (!brandOption) {
                brandOption = new Option(product.brand, product.brand, true, true);
                brandSelect.add(brandOption);
            } else {
                brandSelect.value = product.brand;
            }
        }

        // Set prices
        document.getElementById('edit_cost_price').value = product.cost_price || 0;
        document.getElementById('edit_sale_price').value = product.sale_price || 0;

        // Set stock fields
        document.getElementById('edit_reorder_point').value = product.reorder_point || 0;
        document.getElementById('edit_max_stock').value = product.max_stock || 0;

        // Set points
        if (product.points && product.points > 0) {
            document.getElementById('edit_enable_points').checked = true;
            document.getElementById('edit_points').value = product.points;
            document.getElementById('edit_points_section').classList.add('show');
        }

        // Set location and weight
        document.getElementById('edit_location').value = product.location || '';
        document.getElementById('edit_weight').value = product.weight || 0;

        // Set descriptions
        document.getElementById('edit_product_description').value = product.product_description || '';
        document.getElementById('edit_product_content').value = product.product_content || '';

        // Load images using ProductImageUpload
        if (window.editProductImageUpload) {
            window.editProductImageUpload.loadImages(product);
        }
    }

    async submitForm() {
        if (!this.validateForm()) {
            return;
        }

        const formData = new FormData(this.form);
        // Add _method for PUT request
        formData.append('_method', 'PUT');

        const updateBtn = document.getElementById('update_product_btn');
        const indicatorLabel = updateBtn.querySelector('.indicator-label');
        const indicatorProgress = updateBtn.querySelector('.indicator-progress');

        // Show loading
        indicatorLabel.classList.add('d-none');
        indicatorProgress.classList.remove('d-none');
        updateBtn.disabled = true;

        try {
            const response = await fetch(`/admin/products/${this.productId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                this.showNotification('success', data.message || 'Sản phẩm đã được cập nhật thành công!');
                this.modal.hide();

                // Reload product table
                if (typeof window.productTableManager !== 'undefined') {
                    window.productTableManager.loadData();
                }
            } else {
                this.showNotification('error', data.message || 'Có lỗi xảy ra khi cập nhật sản phẩm!');
            }
        } catch (error) {
            console.error('Error updating product:', error);
            this.showNotification('error', 'Có lỗi xảy ra khi cập nhật sản phẩm!');
        } finally {
            // Hide loading
            indicatorLabel.classList.remove('d-none');
            indicatorProgress.classList.add('d-none');
            updateBtn.disabled = false;
        }
    }

    validateForm() {
        const productName = this.form.querySelector('[name="product_name"]');
        if (!productName || !productName.value.trim()) {
            this.showNotification('error', 'Vui lòng nhập tên hàng!');
            productName.focus();
            return false;
        }
        return true;
    }



    closeModal() {
        this.modal.hide();
    }

    resetForm() {
        if (this.form) {
            this.form.reset();

            // Reset images using ProductImageUpload
            if (window.editProductImageUpload) {
                window.editProductImageUpload.clearAll();
            }

            // Reset collapsible sections
            document.querySelectorAll('.collapse').forEach(el => {
                el.classList.remove('show');
            });
        }
    }

    openSetupPriceModal() {
        const priceModal = new bootstrap.Modal(document.getElementById('kt_modal_setup_price'));
        priceModal.show();
    }

    openSetupUnitAttributeModal() {
        const unitAttributeModal = new bootstrap.Modal(document.getElementById('kt_modal_setup_unit_attribute'));
        unitAttributeModal.show();
    }

    openSetupCommissionModal() {
        const commissionModal = new bootstrap.Modal(document.getElementById('kt_modal_setup_commission'));
        commissionModal.show();
    }

    showNotification(type, message) {
        if (typeof toastr !== 'undefined') {
            toastr[type](message);
        } else if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: type === 'success' ? 'success' : 'error',
                title: type === 'success' ? 'Thành công!' : 'Lỗi!',
                text: message,
                timer: 3000,
                showConfirmButton: false
            });
        } else {
            alert(message);
        }
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    window.createProductModal = new CreateProductModal();
    window.editProductModal = new EditProductModal();
    window.categoryModalManager = new CategoryModalManager();
    window.brandModalManager = new BrandModalManager();
    window.setupModalsManager = new SetupModalsManager();
});

