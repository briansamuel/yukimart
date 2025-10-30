/**
 * Product Attributes Manager
 * Handles product attributes selection and management
 */

class ProductAttributesManager {
    constructor() {
        this.attributes = []; // Array to store selected attributes with their values
        this.availableAttributes = []; // All available attributes from database
        this.currentAttributeId = null; // For "Chọn nhanh" modal
        this.init();
    }

    init() {
        this.loadAvailableAttributes();
        this.bindEvents();
    }

    bindEvents() {
        // Add attribute button
        const addAttributeBtn = document.getElementById('add_attribute_btn');
        if (addAttributeBtn) {
            addAttributeBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.addAttributeRow();
            });
        }

        // Confirm attribute values button (in select values modal)
        const confirmBtn = document.getElementById('confirm_attribute_values_btn');
        if (confirmBtn) {
            confirmBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.confirmAttributeValues();
            });
        }

        // Select all values link
        const selectAllLink = document.getElementById('select_all_values_link');
        if (selectAllLink) {
            selectAllLink.addEventListener('click', (e) => {
                e.preventDefault();
                this.selectAllValues();
            });
        }

        // Search attribute values
        const searchInput = document.getElementById('attribute_values_search');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                this.filterAttributeValues(e.target.value);
            });
        }

        // Sort attribute values
        const sortSelect = document.getElementById('attribute_values_sort');
        if (sortSelect) {
            sortSelect.addEventListener('change', (e) => {
                this.sortAttributeValues(e.target.value);
            });
        }
    }

    async loadAvailableAttributes() {
        try {
            const response = await fetch('/admin/attributes', {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                }
            });

            if (!response.ok) {
                throw new Error('Failed to load attributes');
            }

            const result = await response.json();
            
            if (result.success && result.data) {
                this.availableAttributes = result.data;
            }
        } catch (error) {
            console.error('Error loading attributes:', error);
        }
    }

    addAttributeRow() {
        const container = document.getElementById('attributes_container');
        if (!container) return;

        const rowId = `attribute_row_${Date.now()}`;
        
        const rowHtml = `
            <div class="mb-3 d-flex align-items-center gap-3" id="${rowId}">
                <div class="flex-grow-1">
                    <select class="form-select attribute-select" data-row-id="${rowId}">
                        <option value="">-- Chọn thuộc tính --</option>
                        ${this.availableAttributes.map(attr => 
                            `<option value="${attr.id}">${attr.name}</option>`
                        ).join('')}
                    </select>
                </div>
                <button type="button" class="btn btn-sm btn-light-primary" onclick="window.productAttributesManager.openSelectValuesModal('${rowId}')">
                    Chọn nhanh
                </button>
                <button type="button" class="btn btn-sm btn-icon btn-light-danger" onclick="window.productAttributesManager.removeAttributeRow('${rowId}')">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', rowHtml);

        // Initialize Tagify for the new select
        this.initializeTagifyForRow(rowId);
    }

    initializeTagifyForRow(rowId) {
        const select = document.querySelector(`#${rowId} .attribute-select`);
        if (!select) return;

        // Convert select to Tagify
        const tagify = new Tagify(select, {
            enforceWhitelist: true,
            whitelist: this.availableAttributes.map(attr => ({
                value: attr.id,
                name: attr.name
            })),
            dropdown: {
                maxItems: 20,
                classname: "tagify__dropdown",
                enabled: 0,
                closeOnSelect: false
            }
        });

        // Store tagify instance
        select.tagifyInstance = tagify;
    }

    removeAttributeRow(rowId) {
        const row = document.getElementById(rowId);
        if (row) {
            row.remove();
            
            // Remove from attributes array
            this.attributes = this.attributes.filter(attr => attr.rowId !== rowId);
            
            // Trigger variants regeneration
            if (window.productVariantsManager) {
                window.productVariantsManager.generateVariants();
            }
        }
    }

    async openSelectValuesModal(rowId) {
        const select = document.querySelector(`#${rowId} .attribute-select`);
        if (!select || !select.value) {
            Swal.fire({
                icon: 'warning',
                title: 'Chưa chọn thuộc tính',
                text: 'Vui lòng chọn thuộc tính trước',
            });
            return;
        }

        this.currentAttributeId = select.value;
        this.currentRowId = rowId;

        // Load attribute values
        await this.loadAttributeValues(this.currentAttributeId);

        // Open modal
        const modal = new bootstrap.Modal(document.getElementById('kt_modal_select_attribute_values'));
        modal.show();
    }

    async loadAttributeValues(attributeId) {
        const container = document.getElementById('attribute_values_container');
        if (!container) return;

        // Show loading
        container.innerHTML = `
            <div class="text-center text-muted w-100 py-10">
                <span class="spinner-border spinner-border-sm align-middle me-2"></span>
                Đang tải...
            </div>
        `;

        try {
            const response = await fetch(`/admin/attributes/${attributeId}/values`, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                }
            });

            if (!response.ok) {
                throw new Error('Failed to load attribute values');
            }

            const result = await response.json();
            
            if (result.success && result.data) {
                this.renderAttributeValues(result.data);
            }
        } catch (error) {
            console.error('Error loading attribute values:', error);
            container.innerHTML = `
                <div class="text-center text-danger w-100 py-10">
                    Có lỗi xảy ra khi tải dữ liệu
                </div>
            `;
        }
    }

    renderAttributeValues(values) {
        const container = document.getElementById('attribute_values_container');
        if (!container) return;

        if (values.length === 0) {
            container.innerHTML = '<p class="text-muted text-center w-100">Không có giá trị nào</p>';
            return;
        }

        let html = '';
        values.forEach(value => {
            html += `
                <div class="attribute-value-pill" data-value-id="${value.id}" onclick="window.productAttributesManager.toggleValueSelection(${value.id})">
                    <span class="badge badge-light-primary fs-6 px-4 py-3 cursor-pointer">
                        ${value.value}
                    </span>
                </div>
            `;
        });

        container.innerHTML = html;
    }

    toggleValueSelection(valueId) {
        const pill = document.querySelector(`.attribute-value-pill[data-value-id="${valueId}"]`);
        if (!pill) return;

        const badge = pill.querySelector('.badge');
        if (badge.classList.contains('badge-primary')) {
            badge.classList.remove('badge-primary');
            badge.classList.add('badge-light-primary');
        } else {
            badge.classList.remove('badge-light-primary');
            badge.classList.add('badge-primary');
        }
    }

    selectAllValues() {
        const pills = document.querySelectorAll('.attribute-value-pill .badge');
        pills.forEach(badge => {
            badge.classList.remove('badge-light-primary');
            badge.classList.add('badge-primary');
        });
    }

    confirmAttributeValues() {
        const selectedPills = document.querySelectorAll('.attribute-value-pill .badge.badge-primary');
        const selectedValues = Array.from(selectedPills).map(badge => {
            const pill = badge.closest('.attribute-value-pill');
            return {
                id: pill.dataset.valueId,
                value: badge.textContent.trim()
            };
        });

        if (selectedValues.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Chưa chọn giá trị',
                text: 'Vui lòng chọn ít nhất một giá trị',
            });
            return;
        }

        // Save to attributes array
        const attribute = this.availableAttributes.find(attr => attr.id == this.currentAttributeId);
        this.attributes.push({
            rowId: this.currentRowId,
            attributeId: this.currentAttributeId,
            attributeName: attribute ? attribute.name : '',
            values: selectedValues
        });

        // Close modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('kt_modal_select_attribute_values'));
        modal.hide();

        // Trigger variants regeneration
        if (window.productVariantsManager) {
            window.productVariantsManager.generateVariants();
        }
    }

    filterAttributeValues(keyword) {
        // Implementation for search filtering
    }

    sortAttributeValues(order) {
        // Implementation for sorting
    }

    getAttributes() {
        return this.attributes;
    }

    setAttributes(attributes) {
        this.attributes = attributes || [];
    }

    clearAttributes() {
        this.attributes = [];
        const container = document.getElementById('attributes_container');
        if (container) {
            container.innerHTML = '';
        }
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    window.productAttributesManager = new ProductAttributesManager();
});

