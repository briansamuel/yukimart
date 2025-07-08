"use strict";

// Class definition
var KTProductVariantManager = function () {
    // Shared variables
    var form;
    var productTypeSelect;
    var variantContainer;
    var attributeSelectionContainer;
    var variantListContainer;
    var generateVariantsBtn;
    var availableAttributes = [];
    var selectedAttributes = {};
    var currentVariants = [];

    // Private functions
    var initVariantManager = function () {
        // Get form elements
        form = document.querySelector('#kt_add_product_form, #kt_edit_product_form');
        productTypeSelect = document.querySelector('#product_type');
        variantContainer = document.querySelector('#attribute_selection_container');
        
        if (!form || !productTypeSelect) {
            return;
        }

        // Create variant container if it doesn't exist
        if (!variantContainer) {
            createVariantContainer();
        }

        // Load available attributes
        loadAvailableAttributes();

        // Handle product type change
        productTypeSelect.addEventListener('change', handleProductTypeChange);

        // Initialize based on current product type
        handleProductTypeChange();
    };

    var createVariantContainer = function () {
        // Find the inventory card to insert after it
        var inventoryCard = null;
        var cards = document.querySelectorAll('.card');

        for (var i = 0; i < cards.length; i++) {
            var cardTitle = cards[i].querySelector('.card-title');
            if (cardTitle) {
                var titleText = cardTitle.textContent.toLowerCase();
                if (titleText.includes('inventory') || titleText.includes('tồn kho') || titleText.includes('details')) {
                    inventoryCard = cards[i];
                    break;
                }
            }
        }

        // Create variant container HTML
        var variantHTML = `
            <div id="variant_container" class="card shadow-sm mb-5" style="display: none;">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fa fa-cogs"></i> Thuộc tính
                    </h3>
                </div>
                <div class="card-body">
                    <!-- Attribute Selection -->
                    <div id="attribute_selection_container">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">Thuộc tính</h6>
                            <button type="button" id="add_new_attribute_row_btn" class="btn btn-sm btn-light-primary">
                                <i class="fa fa-plus"></i> Thêm thuộc tính
                            </button>
                        </div>
                        <div id="attribute_rows_container">
                            <!-- Attribute rows will be added here -->
                        </div>
                    </div>

                    <!-- Variant Details Table -->
                    <div id="variant_details_container" class="mt-5" style="display: none;">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0">Danh sách hàng hóa cùng loại</h6>
                        </div>
                        <div id="variant_details_table">
                            <!-- Variant details table will be displayed here -->
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Insert after inventory card or fallback to product type group
        if (inventoryCard) {
            inventoryCard.insertAdjacentHTML('afterend', variantHTML);
        } else {
            var productTypeGroup = productTypeSelect.closest('.form-group');
            productTypeGroup.insertAdjacentHTML('afterend', variantHTML);
        }

        // Update references
        variantContainer = document.querySelector('#variant_container');
        attributeSelectionContainer = document.querySelector('#attribute_selection_container');
        variantListContainer = document.querySelector('#variant_list_container');
        generateVariantsBtn = document.querySelector('#generate_variants_btn');

        // Add event listener for add new attribute row button (with duplicate prevention)
        var addNewAttributeRowBtn = document.querySelector('#add_new_attribute_row_btn');
        console.log('Looking for add_new_attribute_row_btn:', addNewAttributeRowBtn);

        if (addNewAttributeRowBtn && !addNewAttributeRowBtn.hasAttribute('data-listener-attached')) {
            console.log('Found add_new_attribute_row_btn, adding click listener');
            addNewAttributeRowBtn.addEventListener('click', function(e) {
                e.preventDefault();
                console.log('Add attribute button clicked');
                addAttributeRow();
            });
            addNewAttributeRowBtn.setAttribute('data-listener-attached', 'true');
        } else if (addNewAttributeRowBtn) {
            console.log('add_new_attribute_row_btn already has listener attached');
        } else {
            console.warn('add_new_attribute_row_btn not found in DOM');
        }

        // Create attribute modal
        createAttributeModal();
    };

    var handleProductTypeChange = function () {
        var productType = productTypeSelect.value;
        
        if (productType === 'variable') {
            showVariantContainer();
        } else {
            hideVariantContainer();
        }
    };

    var showVariantContainer = function () {
        if (variantContainer) {
            variantContainer.style.display = 'block';
            loadAvailableAttributes();

            // Re-attach button listener after container is shown
            setTimeout(function() {
                attachAddButtonListener();
            }, 100);
        }
    };

    var attachAddButtonListener = function () {
        var addNewAttributeRowBtn = document.querySelector('#add_new_attribute_row_btn');
        console.log('Re-checking for add_new_attribute_row_btn:', addNewAttributeRowBtn);

        if (addNewAttributeRowBtn && !addNewAttributeRowBtn.hasAttribute('data-listener-attached')) {
            console.log('Attaching click listener to add_new_attribute_row_btn');
            addNewAttributeRowBtn.addEventListener('click', function(e) {
                e.preventDefault();
                console.log('Add attribute button clicked (re-attached)');
                addAttributeRow();
            });
            addNewAttributeRowBtn.setAttribute('data-listener-attached', 'true');
        } else if (addNewAttributeRowBtn) {
            console.log('Button already has listener, skipping re-attach');
        }
    };

    var hideVariantContainer = function () {
        if (variantContainer) {
            variantContainer.style.display = 'none';
            clearAttributeRows();
        }
    };

    var clearAttributeRows = function () {
        var container = document.querySelector('#attribute_rows_container');
        if (container) {
            container.innerHTML = '';
        }

        var variantContainer = document.querySelector('#variant_details_container');
        if (variantContainer) {
            variantContainer.style.display = 'none';
        }
    };

    var createAttributeModal = function () {
        var modalHTML = `
            <div class="modal fade" id="kt_modal_add_attribute" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered mw-650px">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h2 class="fw-bolder">Thêm thuộc tính</h2>
                            <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                                <i class="fa fa-times fs-1"></i>
                            </div>
                        </div>
                        <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                            <form id="kt_modal_add_attribute_form" class="form">
                                <div class="fv-row mb-7">
                                    <label class="required fw-bold fs-6 mb-2">Tên thuộc tính</label>
                                    <input type="text" name="attribute_name" class="form-control form-control-solid mb-3 mb-lg-0" placeholder="Nhập tên thuộc tính" />
                                </div>
                                <div class="fv-row mb-7">
                                    <label class="fw-bold fs-6 mb-2">Loại thuộc tính</label>
                                    <select name="attribute_type" class="form-select form-select-solid">
                                        <option value="select">Lựa chọn</option>
                                        <option value="color">Màu sắc</option>
                                        <option value="text">Văn bản</option>
                                        <option value="number">Số</option>
                                    </select>
                                </div>
                                <div class="fv-row mb-7">
                                    <label class="fw-bold fs-6 mb-2">Mô tả</label>
                                    <textarea name="attribute_description" class="form-control form-control-solid" rows="3" placeholder="Mô tả thuộc tính (tùy chọn)"></textarea>
                                </div>
                                <div class="fv-row mb-7">
                                    <div class="form-check form-check-custom form-check-solid">
                                        <input class="form-check-input" type="checkbox" name="is_variation" value="1" checked />
                                        <label class="form-check-label fw-bold text-gray-700">
                                            Sử dụng cho biến thể
                                        </label>
                                    </div>
                                </div>
                                <div class="fv-row mb-7">
                                    <div class="form-check form-check-custom form-check-solid">
                                        <input class="form-check-input" type="checkbox" name="is_visible" value="1" checked />
                                        <label class="form-check-label fw-bold text-gray-700">
                                            Hiển thị trên trang sản phẩm
                                        </label>
                                    </div>
                                </div>
                                <div class="text-center pt-15">
                                    <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">Bỏ qua</button>
                                    <button type="submit" class="btn btn-primary">
                                        Lưu
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', modalHTML);

        // Initialize form submission
        var form = document.querySelector('#kt_modal_add_attribute_form');
        if (form) {
            form.addEventListener('submit', handleAddAttributeSubmit);
        }
    };

    var showAddAttributeModal = function () {
        var modal = new bootstrap.Modal(document.querySelector('#kt_modal_add_attribute'));
        modal.show();
    };

    var handleAddAttributeSubmit = function (e) {
        e.preventDefault();

        var form = e.target;
        var formData = new FormData(form);
        var submitBtn = form.querySelector('button[type="submit"]');

        // Disable button to prevent double submission
        submitBtn.disabled = true;

        // Convert FormData to JSON
        var data = {
            name: formData.get('attribute_name'),
            type: formData.get('attribute_type'),
            description: formData.get('attribute_description') || '',
            is_variation: formData.get('is_variation') ? 1 : 0,
            is_visible: formData.get('is_visible') ? 1 : 0
        };

        fetch('/admin/products/attributes', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            submitBtn.disabled = false;

            if (data.success) {
                // Close modal
                var modal = bootstrap.Modal.getInstance(document.querySelector('#kt_modal_add_attribute'));
                modal.hide();

                // Reset form
                form.reset();

                // Reload attributes
                loadAvailableAttributes();

                Swal.fire({
                    text: "Tạo thuộc tính thành công!",
                    icon: "success",
                    buttonsStyling: false,
                    confirmButtonText: "Đã hiểu",
                    customClass: {
                        confirmButton: "btn btn-primary"
                    }
                });
            } else {
                Swal.fire({
                    text: data.message || "Có lỗi xảy ra khi tạo thuộc tính.",
                    icon: "error",
                    buttonsStyling: false,
                    confirmButtonText: "Đã hiểu",
                    customClass: {
                        confirmButton: "btn btn-primary"
                    }
                });
            }
        })
        .catch(error => {
            submitBtn.disabled = false;

            console.error('Error creating attribute:', error);
            Swal.fire({
                text: "Có lỗi xảy ra khi tạo thuộc tính.",
                icon: "error",
                buttonsStyling: false,
                confirmButtonText: "Đã hiểu",
                customClass: {
                    confirmButton: "btn btn-primary"
                }
            });
        });
    };

    var addAttributeRow = function () {
        var container = document.querySelector('#attribute_rows_container');
        var rowIndex = container.children.length;

        var rowHTML = `
            <div class="attribute-row mb-4 p-3 border rounded" data-row-index="${rowIndex}">
                <div class="row align-items-center">
                    <div class="col-md-3">
                        <select class="form-select attribute-select" data-row-index="${rowIndex}">
                            <option value="">Chọn thuộc tính</option>
                        </select>
                    </div>
                    <div class="col-md-1 text-center">
                        <i class="fa fa-edit text-muted"></i>
                    </div>
                    <div class="col-md-7">
                        <div class="attribute-values-container">
                            <input type="text" class="form-control attribute-values-tagify"
                                   placeholder="Nhập giá trị và enter"
                                   data-row-index="${rowIndex}" disabled>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-sm btn-light-danger remove-attribute-row"
                                data-row-index="${rowIndex}">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', rowHTML);

        // Load attributes for the new select
        loadAttributesForSelect(rowIndex);

        // Add event listeners for the new row
        addAttributeRowEventListeners(rowIndex);
    };

    var loadAttributesForSelect = function (rowIndex) {
        var select = document.querySelector(`select.attribute-select[data-row-index="${rowIndex}"]`);
        if (select && availableAttributes.length > 0) {
            // Clear existing options except the first one
            select.innerHTML = '<option value="">Chọn thuộc tính</option>';

            availableAttributes.forEach(function (attribute) {
                var option = document.createElement('option');
                option.value = attribute.id;
                option.textContent = attribute.name;
                option.setAttribute('data-attribute-type', attribute.type);
                select.appendChild(option);
            });
        } else {
            // Load from API if not available
            fetch('/admin/products/attributes', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    availableAttributes = data.data;
                    loadAttributesForSelect(rowIndex); // Recursive call with loaded data
                }
            })
            .catch(error => {
                console.error('Error loading attributes:', error);
            });
        }
    };

    var addAttributeRowEventListeners = function (rowIndex) {
        // Attribute select change
        var attributeSelect = document.querySelector(`select.attribute-select[data-row-index="${rowIndex}"]`);
        if (attributeSelect) {
            attributeSelect.addEventListener('change', function () {
                handleAttributeSelectChange(rowIndex, this.value);
            });
        }

        // Remove row button
        var removeBtn = document.querySelector(`button.remove-attribute-row[data-row-index="${rowIndex}"]`);
        if (removeBtn) {
            removeBtn.addEventListener('click', function (e) {
                e.preventDefault();
                console.log('Remove attribute row clicked:', rowIndex);
                removeAttributeRow(rowIndex);
            });
        }
    };

    var handleAttributeSelectChange = function (rowIndex, attributeId) {
        var valuesInput = document.querySelector(`input.attribute-values-tagify[data-row-index="${rowIndex}"]`);

        if (attributeId) {
            valuesInput.disabled = false;
            valuesInput.placeholder = "Nhập giá trị và enter";

            // Initialize or update Tagify for this input
            initializeTagifyForAttribute(rowIndex, attributeId);
        } else {
            valuesInput.disabled = true;
            valuesInput.placeholder = "Chọn thuộc tính trước";

            // Destroy existing Tagify if any
            destroyTagifyForRow(rowIndex);
        }

        // Update variant table when attributes change
        updateVariantTable();
    };

    // Store Tagify instances for cleanup
    var tagifyInstances = new Map();

    var initializeTagifyForAttribute = function (rowIndex, attributeId) {
        var valuesInput = document.querySelector(`input.attribute-values-tagify[data-row-index="${rowIndex}"]`);
        if (!valuesInput) return;

        // Destroy existing Tagify if any
        destroyTagifyForRow(rowIndex);

        // Find the attribute in availableAttributes
        var attribute = availableAttributes.find(attr => attr.id == attributeId);
        var whitelist = [];

        if (attribute && attribute.values && attribute.values.length > 0) {
            whitelist = attribute.values.map(v => v.value);
            setupTagify(rowIndex, attributeId, whitelist);
        } else {
            // Load values from API
            fetch(`/admin/products/attributes/${attributeId}/values`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data && data.data.length > 0) {
                    // Update the attribute in availableAttributes
                    if (attribute) {
                        attribute.values = data.data;
                    }
                    whitelist = data.data.map(v => v.value);
                }
                setupTagify(rowIndex, attributeId, whitelist);
            })
            .catch(error => {
                console.error('Error loading attribute values:', error);
                setupTagify(rowIndex, attributeId, []);
            });
        }
    };

    var setupTagify = function (rowIndex, attributeId, whitelist) {
        var valuesInput = document.querySelector(`input.attribute-values-tagify[data-row-index="${rowIndex}"]`);
        if (!valuesInput) return;

        var tagify = new Tagify(valuesInput, {
            whitelist: whitelist,
            placeholder: "Nhập giá trị và enter",
            enforceWhitelist: false, // Allow new values
            dropdown: {
                maxItems: 20,
                classname: "tagify__inline__suggestions",
                enabled: 0,
                closeOnSelect: false
            },
            editTags: false
        });

        // Store instance for cleanup
        tagifyInstances.set(rowIndex, tagify);

        // Handle tag addition
        tagify.on('add', function(e) {
            updateVariantTable();
        });

        // Handle tag removal
        tagify.on('remove', function(e) {
            updateVariantTable();
        });

        // Handle new tag creation (when value doesn't exist in whitelist)
        tagify.on('add', function(e) {
            var tagValue = e.detail.data.value;
            var existsInWhitelist = whitelist.includes(tagValue);

            if (!existsInWhitelist) {
                // Mark this as a new value to be created
                e.detail.data.isNew = true;
                console.log('New attribute value will be created:', tagValue);
            }
        });
    };

    var destroyTagifyForRow = function (rowIndex) {
        if (tagifyInstances.has(rowIndex)) {
            var tagify = tagifyInstances.get(rowIndex);
            tagify.destroy();
            tagifyInstances.delete(rowIndex);
        }
    };

    var getTagifyValues = function (rowIndex) {
        if (tagifyInstances.has(rowIndex)) {
            var tagify = tagifyInstances.get(rowIndex);
            return tagify.value.map(tag => tag.value);
        }
        return [];
    };

    // Legacy functions - no longer needed with Tagify
    // Keeping for backward compatibility

    var removeAttributeValue = function (rowIndex, value) {
        console.log('removeAttributeValue called:', rowIndex, value);
        // With Tagify, we need to remove the tag from the Tagify instance
        if (tagifyInstances.has(rowIndex)) {
            var tagify = tagifyInstances.get(rowIndex);
            var tagToRemove = tagify.value.find(tag => tag.value === value);
            if (tagToRemove) {
                tagify.removeTag(tagToRemove);
                updateVariantTable();
            }
        }
    };

    var removeAttributeRow = function (rowIndex) {
        console.log('removeAttributeRow called:', rowIndex);
        // Destroy Tagify instance first
        destroyTagifyForRow(rowIndex);

        var row = document.querySelector(`div.attribute-row[data-row-index="${rowIndex}"]`);
        if (row) {
            row.remove();
            updateVariantTable();
        }
    };

    var updateVariantTable = function () {
        var attributeRows = document.querySelectorAll('.attribute-row');
        var hasValidAttributes = false;
        var combinations = [];

        // Check if we have valid attributes with values
        attributeRows.forEach(function (row) {
            var rowIndex = row.getAttribute('data-row-index');
            var select = row.querySelector('.attribute-select');
            var tagifyValues = getTagifyValues(parseInt(rowIndex));

            if (select.value && tagifyValues.length > 0) {
                hasValidAttributes = true;
            }
        });

        var variantContainer = document.querySelector('#variant_details_container');

        if (hasValidAttributes) {
            combinations = generateVariantCombinations();
            renderVariantTable(combinations);
            variantContainer.style.display = 'block';
        } else {
            variantContainer.style.display = 'none';
        }
    };

    var generateVariantCombinations = function () {
        var attributeRows = document.querySelectorAll('.attribute-row');
        var attributesData = [];

        attributeRows.forEach(function (row) {
            var rowIndex = parseInt(row.getAttribute('data-row-index'));
            var select = row.querySelector('.attribute-select');
            var tagifyValues = getTagifyValues(rowIndex);

            if (select.value && tagifyValues.length > 0) {
                var attributeName = select.options[select.selectedIndex].text;

                attributesData.push({
                    id: select.value,
                    name: attributeName,
                    values: tagifyValues
                });
            }
        });

        if (attributesData.length === 0) return [];

        // Generate all combinations
        var combinations = [[]];

        attributesData.forEach(function (attribute) {
            var newCombinations = [];
            combinations.forEach(function (combination) {
                attribute.values.forEach(function (value) {
                    newCombinations.push([...combination, {
                        attributeId: attribute.id,
                        attributeName: attribute.name,
                        value: value
                    }]);
                });
            });
            combinations = newCombinations;
        });

        return combinations;
    };

    var renderVariantTable = function (combinations) {
        var tableContainer = document.querySelector('#variant_details_table');

        if (combinations.length === 0) {
            tableContainer.innerHTML = '';
            return;
        }

        var tableHTML = `
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Tên</th>
                            <th>Mã hàng</th>
                            <th>Mã vạch</th>
                            <th>Giá vốn</th>
                            <th>Giá bán</th>
                            <th>Tồn kho</th>
                            <th>Điểm</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
        `;

        combinations.forEach(function (combination, index) {
            var variantName = combination.map(attr => attr.value).join(' ');
            var variantSku = 'Mã hàng tự động';

            tableHTML += `
                <tr data-variant-index="${index}">
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <div class="variant-thumbnail-container">
                                    <img src="/admin-assets/assets/images/upload-thumbnail.png"
                                         class="variant-thumbnail"
                                         style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;">
                                    <input type="file" class="variant-thumbnail-input d-none"
                                           accept="image/*" multiple data-variant-index="${index}">
                                    <button type="button" class="btn btn-sm btn-light-primary mt-1 upload-variant-images"
                                            data-variant-index="${index}">
                                        <i class="fa fa-camera"></i>
                                    </button>
                                </div>
                            </div>
                            <div>
                                <div class="fw-bold">${variantName}</div>
                                <small class="text-muted">${combination.map(attr => `${attr.attributeName}: ${attr.value}`).join(', ')}</small>
                            </div>
                        </div>
                    </td>
                    <td>
                        <input type="text" class="form-control form-control-sm"
                               placeholder="${variantSku}" data-field="sku" data-variant-index="${index}">
                    </td>
                    <td>
                        <input type="text" class="form-control form-control-sm"
                               placeholder="Mã vạch" data-field="barcode" data-variant-index="${index}">
                    </td>
                    <td>
                        <input type="number" class="form-control form-control-sm"
                               placeholder="0" data-field="cost_price" data-variant-index="${index}">
                    </td>
                    <td>
                        <input type="number" class="form-control form-control-sm"
                               placeholder="0" data-field="sale_price" data-variant-index="${index}">
                    </td>
                    <td>
                        <input type="number" class="form-control form-control-sm"
                               placeholder="0" data-field="stock_quantity" data-variant-index="${index}">
                    </td>
                    <td>
                        <input type="number" class="form-control form-control-sm"
                               placeholder="0" data-field="points" data-variant-index="${index}">
                    </td>
                    <td>
                        <button type="button" class="btn btn-sm btn-light-danger remove-variant"
                                data-variant-index="${index}">
                            <i class="fa fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
        });

        tableHTML += `
                    </tbody>
                </table>
                <div class="text-end mt-2">
                    <small class="text-muted">Danh sách bao gồm ${combinations.length} hàng hóa cùng loại</small>
                </div>
            </div>
        `;

        tableContainer.innerHTML = tableHTML;

        // Add event listeners for variant table
        addVariantTableEventListeners();
    };

    var addVariantTableEventListeners = function () {
        // Upload variant images
        document.querySelectorAll('.upload-variant-images').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var variantIndex = this.getAttribute('data-variant-index');
                var fileInput = document.querySelector(`input.variant-thumbnail-input[data-variant-index="${variantIndex}"]`);
                fileInput.click();
            });
        });

        // Handle file selection
        document.querySelectorAll('.variant-thumbnail-input').forEach(function (input) {
            input.addEventListener('change', function () {
                var variantIndex = this.getAttribute('data-variant-index');
                handleVariantImageUpload(variantIndex, this.files);
            });
        });

        // Remove variant
        document.querySelectorAll('.remove-variant').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var variantIndex = this.getAttribute('data-variant-index');
                removeVariantRow(variantIndex);
            });
        });
    };

    var handleVariantImageUpload = function (variantIndex, files) {
        if (files.length === 0) return;

        var thumbnail = document.querySelector(`img.variant-thumbnail[data-variant-index="${variantIndex}"]`) ||
                       document.querySelector(`tr[data-variant-index="${variantIndex}"] img.variant-thumbnail`);

        // For now, just show the first image as preview
        var file = files[0];
        var reader = new FileReader();

        reader.onload = function (e) {
            if (thumbnail) {
                thumbnail.src = e.target.result;
            }
        };

        reader.readAsDataURL(file);

        // Store files for later upload
        if (!window.variantImages) {
            window.variantImages = {};
        }
        window.variantImages[variantIndex] = files;
    };

    var removeVariantRow = function (variantIndex) {
        var row = document.querySelector(`tr[data-variant-index="${variantIndex}"]`);
        if (row) {
            row.remove();
        }
    };

    // Make functions global so they can be called from onclick handlers
    window.removeAttributeValue = removeAttributeValue;
    window.removeAttributeRow = removeAttributeRow;
    window.removeVariantRow = removeVariantRow;

    var getVariantDataForSubmission = function () {
        var variantRows = document.querySelectorAll('#variant_details_table tbody tr');
        var variantData = [];
        var newAttributeValues = collectNewAttributeValues();

        variantRows.forEach(function (row) {
            var variantIndex = row.getAttribute('data-variant-index');
            var variantInfo = {
                attributes: [],
                sku: row.querySelector('input[data-field="sku"]').value || '',
                barcode: row.querySelector('input[data-field="barcode"]').value || '',
                cost_price: parseFloat(row.querySelector('input[data-field="cost_price"]').value) || 0,
                sale_price: parseFloat(row.querySelector('input[data-field="sale_price"]').value) || 0,
                stock_quantity: parseInt(row.querySelector('input[data-field="stock_quantity"]').value) || 0,
                points: parseInt(row.querySelector('input[data-field="points"]').value) || 0,
                images: window.variantImages && window.variantImages[variantIndex] ? Array.from(window.variantImages[variantIndex]) : []
            };

            // Get attributes for this variant from the combinations
            var combinations = generateVariantCombinations();
            if (combinations[variantIndex]) {
                variantInfo.attributes = combinations[variantIndex].map(function (attr) {
                    return {
                        attribute_id: attr.attributeId,
                        value: attr.value
                    };
                });
            }

            variantData.push(variantInfo);
        });

        return {
            variants: variantData,
            new_attribute_values: newAttributeValues
        };
    };

    var collectNewAttributeValues = function () {
        var newValues = [];
        var attributeRows = document.querySelectorAll('.attribute-row');

        attributeRows.forEach(function (row) {
            var rowIndex = parseInt(row.getAttribute('data-row-index'));
            var select = row.querySelector('.attribute-select');
            var attributeId = select.value;

            if (attributeId && tagifyInstances.has(rowIndex)) {
                var tagify = tagifyInstances.get(rowIndex);
                var attribute = availableAttributes.find(attr => attr.id == attributeId);
                var existingValues = attribute && attribute.values ? attribute.values.map(v => v.value) : [];

                tagify.value.forEach(function (tag) {
                    var tagValue = tag.value;
                    if (!existingValues.includes(tagValue)) {
                        newValues.push({
                            attribute_id: attributeId,
                            value: tagValue
                        });
                    }
                });
            }
        });

        return newValues;
    };

    var submitVariants = function (productId) {
        var submissionData = getVariantDataForSubmission();

        if (submissionData.variants.length === 0) {
            return Promise.resolve(); // No variants to submit
        }

        return fetch(`/admin/products/${productId}/variants/from-form`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(submissionData)
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                throw new Error(data.message || 'Failed to create variants');
            }
            return data;
        });
    };

    // Expose functions globally for form submission
    window.KTProductVariantManager = {
        getVariantData: getVariantDataForSubmission,
        submitVariants: submitVariants,
        hasVariants: function () {
            return document.querySelectorAll('#variant_details_table tbody tr').length > 0;
        },
        loadVariants: function () {
            // Load existing variants for edit mode
            loadAvailableAttributes();
        },
        getSelectedAttributes: function () {
            return generateVariantCombinations();
        }
    };

    var loadAvailableAttributes = function () {
        fetch('/admin/products/attributes', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                availableAttributes = data.data;
                renderAttributeSelection();
            } else {
                console.error('Failed to load attributes:', data.message);
            }
        })
        .catch(error => {
            console.error('Error loading attributes:', error);
        });
    };

    var renderAttributeSelection = function () {
        var attributeList = document.querySelector('#attribute_list');
        if (!attributeList || !availableAttributes.length) {
            return;
        }

        var html = '';
        availableAttributes.forEach(function (attribute) {
            html += `
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card border">
                        <div class="card-body p-3">
                            <div class="form-check">
                                <input class="form-check-input attribute-checkbox" 
                                       type="checkbox" 
                                       value="${attribute.id}" 
                                       id="attr_${attribute.id}"
                                       data-attribute-name="${attribute.name}">
                                <label class="form-check-label fw-bold" for="attr_${attribute.id}">
                                    ${attribute.name}
                                </label>
                            </div>
                            <div class="attribute-values mt-2" id="values_${attribute.id}" style="display: none;">
                                <small class="text-muted">Chọn giá trị:</small>
                                <div class="mt-1">
                                    ${renderAttributeValues(attribute)}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });

        attributeList.innerHTML = html;

        // Add event listeners for attribute checkboxes
        document.querySelectorAll('.attribute-checkbox').forEach(function (checkbox) {
            checkbox.addEventListener('change', handleAttributeSelection);
        });

        // Add event listeners for value checkboxes
        document.querySelectorAll('.value-checkbox').forEach(function (checkbox) {
            checkbox.addEventListener('change', handleValueSelection);
        });
    };

    var renderAttributeValues = function (attribute) {
        var html = '';
        
        if (attribute.values && attribute.values.length > 0) {
            attribute.values.forEach(function (value) {
                var displayValue = value.value;
                if (value.price_adjustment && value.price_adjustment != 0) {
                    var adjustment = value.price_adjustment > 0 ? '+' : '';
                    displayValue += ` (${adjustment}${formatPrice(value.price_adjustment)})`;
                }

                html += `
                    <div class="form-check form-check-sm">
                        <input class="form-check-input value-checkbox" 
                               type="checkbox" 
                               value="${value.id}" 
                               id="value_${value.id}"
                               data-attribute-id="${attribute.id}">
                        <label class="form-check-label small" for="value_${value.id}">
                            ${displayValue}
                        </label>
                    </div>
                `;
            });
        }

        return html;
    };

    var handleAttributeSelection = function (e) {
        var attributeId = e.target.value;
        var isChecked = e.target.checked;
        var valuesContainer = document.querySelector(`#values_${attributeId}`);

        if (isChecked) {
            valuesContainer.style.display = 'block';
            selectedAttributes[attributeId] = [];
        } else {
            valuesContainer.style.display = 'none';
            delete selectedAttributes[attributeId];
            
            // Uncheck all values for this attribute
            document.querySelectorAll(`input[data-attribute-id="${attributeId}"]`).forEach(function (checkbox) {
                checkbox.checked = false;
            });
        }

        updateGenerateButton();
    };

    var handleValueSelection = function (e) {
        var valueId = e.target.value;
        var attributeId = e.target.getAttribute('data-attribute-id');
        var isChecked = e.target.checked;

        if (!selectedAttributes[attributeId]) {
            selectedAttributes[attributeId] = [];
        }

        if (isChecked) {
            selectedAttributes[attributeId].push(valueId);
        } else {
            var index = selectedAttributes[attributeId].indexOf(valueId);
            if (index > -1) {
                selectedAttributes[attributeId].splice(index, 1);
            }
        }

        // If no values selected for this attribute, uncheck the attribute
        if (selectedAttributes[attributeId].length === 0) {
            document.querySelector(`#attr_${attributeId}`).checked = false;
            delete selectedAttributes[attributeId];
        }

        updateGenerateButton();
    };

    var updateGenerateButton = function () {
        var hasSelectedAttributes = Object.keys(selectedAttributes).length > 0;
        var hasSelectedValues = Object.values(selectedAttributes).some(values => values.length > 0);

        if (generateVariantsBtn) {
            generateVariantsBtn.style.display = hasSelectedAttributes && hasSelectedValues ? 'block' : 'none';
        }
    };

    var generateVariants = function () {
        if (Object.keys(selectedAttributes).length === 0) {
            Swal.fire({
                text: "Vui lòng chọn ít nhất một thuộc tính và giá trị.",
                icon: "warning",
                buttonsStyling: false,
                confirmButtonText: "Đã hiểu",
                customClass: {
                    confirmButton: "btn btn-primary"
                }
            });
            return;
        }

        // Disable button to prevent double clicks
        generateVariantsBtn.disabled = true;

        // Get product ID if editing
        var productId = getProductId();
        var url = productId ? `/admin/products/${productId}/variants` : '#';

        if (!productId) {
            // For new products, we'll handle this in form submission
            showVariantPreview();
            generateVariantsBtn.disabled = false;
            return;
        }

        // Create variants via API
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                attributes: selectedAttributes
            })
        })
        .then(response => response.json())
        .then(data => {
            generateVariantsBtn.disabled = false;

            if (data.success) {
                currentVariants = data.data.variants;
                showVariantList();
                
                Swal.fire({
                    text: `Đã tạo thành công ${data.data.variants_count} biến thể!`,
                    icon: "success",
                    buttonsStyling: false,
                    confirmButtonText: "Đã hiểu",
                    customClass: {
                        confirmButton: "btn btn-primary"
                    }
                });
            } else {
                Swal.fire({
                    text: data.message || "Có lỗi xảy ra khi tạo biến thể.",
                    icon: "error",
                    buttonsStyling: false,
                    confirmButtonText: "Đã hiểu",
                    customClass: {
                        confirmButton: "btn btn-primary"
                    }
                });
            }
        })
        .catch(error => {
            generateVariantsBtn.disabled = false;

            console.error('Error generating variants:', error);
            Swal.fire({
                text: "Có lỗi xảy ra khi tạo biến thể.",
                icon: "error",
                buttonsStyling: false,
                confirmButtonText: "Đã hiểu",
                customClass: {
                    confirmButton: "btn btn-primary"
                }
            });
        });
    };

    var showVariantPreview = function () {
        // Generate preview of variants that will be created
        var combinations = generateCombinations(selectedAttributes);
        var html = `
            <div class="alert alert-info">
                <i class="fa fa-info-circle"></i>
                Sẽ tạo ${combinations.length} biến thể khi lưu sản phẩm.
            </div>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Tên biến thể</th>
                            <th>Thuộc tính</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

        combinations.forEach(function (combination, index) {
            var variantName = getVariantName(combination);
            var attributeText = getAttributeText(combination);

            html += `
                <tr>
                    <td>${variantName}</td>
                    <td>${attributeText}</td>
                </tr>
            `;
        });

        html += `
                    </tbody>
                </table>
            </div>
        `;

        document.querySelector('#variant_list').innerHTML = html;
        variantListContainer.style.display = 'block';
    };

    var showVariantList = function () {
        if (!currentVariants.length) {
            variantListContainer.style.display = 'none';
            return;
        }

        var html = `
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Tên biến thể</th>
                            <th>SKU</th>
                            <th>Giá bán</th>
                            <th>Thuộc tính</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

        currentVariants.forEach(function (variant) {
            var attributeText = variant.attributes.map(attr =>
                `${attr.attribute_name}: ${attr.value_name}`
            ).join(', ');

            html += `
                <tr data-variant-id="${variant.id}">
                    <td>${variant.name}</td>
                    <td>${variant.sku}</td>
                    <td>${variant.formatted_price}</td>
                    <td>${attributeText}</td>
                    <td>
                        <button type="button" class="btn btn-sm btn-primary edit-variant-btn"
                                data-variant-id="${variant.id}">
                            <i class="fa fa-edit"></i> Sửa
                        </button>
                        <button type="button" class="btn btn-sm btn-danger delete-variant-btn"
                                data-variant-id="${variant.id}">
                            <i class="fa fa-trash"></i> Xóa
                        </button>
                    </td>
                </tr>
            `;
        });

        html += `
                    </tbody>
                </table>
            </div>
        `;

        document.querySelector('#variant_list').innerHTML = html;
        variantListContainer.style.display = 'block';

        // Add event listeners for variant actions
        addVariantActionListeners();
    };

    var addVariantActionListeners = function () {
        // Edit variant buttons
        document.querySelectorAll('.edit-variant-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var variantId = this.getAttribute('data-variant-id');
                editVariant(variantId);
            });
        });

        // Delete variant buttons
        document.querySelectorAll('.delete-variant-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var variantId = this.getAttribute('data-variant-id');
                deleteVariant(variantId);
            });
        });
    };

    var editVariant = function (variantId) {
        var variant = currentVariants.find(v => v.id == variantId);
        if (!variant) return;

        // Create edit modal (simplified version)
        Swal.fire({
            title: 'Chỉnh sửa biến thể',
            html: `
                <div class="form-group mb-3">
                    <label class="form-label">Giá bán:</label>
                    <input type="number" id="edit_sale_price" class="form-control"
                           value="${variant.price}" step="1000">
                </div>
                <div class="form-group mb-3">
                    <label class="form-label">SKU:</label>
                    <input type="text" id="edit_sku" class="form-control"
                           value="${variant.sku}">
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Cập nhật',
            cancelButtonText: 'Hủy',
            customClass: {
                confirmButton: "btn btn-primary",
                cancelButton: "btn btn-secondary"
            },
            preConfirm: () => {
                var salePrice = document.getElementById('edit_sale_price').value;
                var sku = document.getElementById('edit_sku').value;

                if (!salePrice || !sku) {
                    Swal.showValidationMessage('Vui lòng điền đầy đủ thông tin');
                    return false;
                }

                return {
                    sale_price: parseFloat(salePrice),
                    sku: sku
                };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                updateVariant(variantId, result.value);
            }
        });
    };

    var updateVariant = function (variantId, data) {
        var productId = getProductId();
        if (!productId) return;

        fetch(`/admin/products/${productId}/variants/${variantId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Reload variants
                loadCurrentVariants();

                Swal.fire({
                    text: "Cập nhật biến thể thành công!",
                    icon: "success",
                    buttonsStyling: false,
                    confirmButtonText: "Đã hiểu",
                    customClass: {
                        confirmButton: "btn btn-primary"
                    }
                });
            } else {
                Swal.fire({
                    text: data.message || "Có lỗi xảy ra khi cập nhật biến thể.",
                    icon: "error",
                    buttonsStyling: false,
                    confirmButtonText: "Đã hiểu",
                    customClass: {
                        confirmButton: "btn btn-primary"
                    }
                });
            }
        })
        .catch(error => {
            console.error('Error updating variant:', error);
        });
    };

    var deleteVariant = function (variantId) {
        Swal.fire({
            text: "Bạn có chắc chắn muốn xóa biến thể này?",
            icon: "warning",
            showCancelButton: true,
            buttonsStyling: false,
            confirmButtonText: "Có, xóa!",
            cancelButtonText: "Hủy",
            customClass: {
                confirmButton: "btn btn-danger",
                cancelButton: "btn btn-secondary"
            }
        }).then((result) => {
            if (result.isConfirmed) {
                var productId = getProductId();
                if (!productId) return;

                fetch(`/admin/products/${productId}/variants/${variantId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Reload variants
                        loadCurrentVariants();

                        Swal.fire({
                            text: "Xóa biến thể thành công!",
                            icon: "success",
                            buttonsStyling: false,
                            confirmButtonText: "Đã hiểu",
                            customClass: {
                                confirmButton: "btn btn-primary"
                            }
                        });
                    } else {
                        Swal.fire({
                            text: data.message || "Có lỗi xảy ra khi xóa biến thể.",
                            icon: "error",
                            buttonsStyling: false,
                            confirmButtonText: "Đã hiểu",
                            customClass: {
                                confirmButton: "btn btn-primary"
                            }
                        });
                    }
                })
                .catch(error => {
                    console.error('Error deleting variant:', error);
                });
            }
        });
    };

    // Utility functions
    var generateCombinations = function (attributes) {
        var combinations = [{}];

        for (var attributeId in attributes) {
            var values = attributes[attributeId];
            var newCombinations = [];

            combinations.forEach(function (combination) {
                values.forEach(function (valueId) {
                    var newCombination = Object.assign({}, combination);
                    newCombination[attributeId] = valueId;
                    newCombinations.push(newCombination);
                });
            });

            combinations = newCombinations;
        }

        return combinations;
    };

    var getVariantName = function (combination) {
        var productName = document.querySelector('#product_name').value || 'Sản phẩm';
        var parts = [productName];

        for (var attributeId in combination) {
            var valueId = combination[attributeId];
            var attribute = availableAttributes.find(attr => attr.id == attributeId);
            var value = attribute ? attribute.values.find(val => val.id == valueId) : null;

            if (value) {
                parts.push(value.value);
            }
        }

        return parts.join(' - ');
    };

    var getAttributeText = function (combination) {
        var parts = [];

        for (var attributeId in combination) {
            var valueId = combination[attributeId];
            var attribute = availableAttributes.find(attr => attr.id == attributeId);
            var value = attribute ? attribute.values.find(val => val.id == valueId) : null;

            if (attribute && value) {
                parts.push(`${attribute.name}: ${value.value}`);
            }
        }

        return parts.join(', ');
    };

    var getProductId = function () {
        // Try to get product ID from URL or form
        var pathParts = window.location.pathname.split('/');
        var editIndex = pathParts.indexOf('edit');

        if (editIndex !== -1 && pathParts[editIndex + 1]) {
            return pathParts[editIndex + 1];
        }

        // Try to get from hidden input
        var productIdInput = document.querySelector('input[name="product_id"]');
        if (productIdInput) {
            return productIdInput.value;
        }

        return null;
    };

    var loadCurrentVariants = function () {
        var productId = getProductId();
        if (!productId) return;

        fetch(`/admin/products/${productId}/variants`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                currentVariants = data.data;
                showVariantList();
            }
        })
        .catch(error => {
            console.error('Error loading variants:', error);
        });
    };

    var clearSelectedAttributes = function () {
        selectedAttributes = {};

        // Uncheck all checkboxes
        document.querySelectorAll('.attribute-checkbox').forEach(function (checkbox) {
            checkbox.checked = false;
        });

        document.querySelectorAll('.value-checkbox').forEach(function (checkbox) {
            checkbox.checked = false;
        });

        // Hide all value containers
        document.querySelectorAll('[id^="values_"]').forEach(function (container) {
            container.style.display = 'none';
        });

        updateGenerateButton();
    };

    var formatPrice = function (price) {
        return new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND'
        }).format(price);
    };

    // Public methods
    return {
        init: function () {
            initVariantManager();
        },

        getSelectedAttributes: function () {
            return selectedAttributes;
        },

        loadVariants: function () {
            loadCurrentVariants();
        },

        // Debug function to show variants container
        showVariantsContainer: function () {
            var container = document.querySelector('#attribute_selection_container');
            if (container) {
                container.style.display = 'block';
                console.log('Variants container shown manually');

                // Re-check for button
                var btn = document.querySelector('#add_new_attribute_row_btn');
                console.log('Button after showing container:', btn);

                if (btn && !btn.hasAttribute('data-listener-attached')) {
                    btn.addEventListener('click', function(e) {
                        e.preventDefault();
                        console.log('Manual button click handler');
                        addAttributeRow();
                    });
                    btn.setAttribute('data-listener-attached', 'true');
                }
            }
        },

        // Debug function to add attribute row manually
        addAttributeRow: function () {
            addAttributeRow();
        }
    };
}();

// On document ready - Multiple fallbacks for compatibility
document.addEventListener('DOMContentLoaded', function () {
    KTProductVariantManager.init();
});

// jQuery fallback
if (typeof $ !== 'undefined') {
    $(document).ready(function() {
        KTProductVariantManager.init();
    });
}

// KTUtil fallback if available
if (typeof KTUtil !== 'undefined' && KTUtil.onDOMContentLoaded) {
    KTUtil.onDOMContentLoaded(function () {
        KTProductVariantManager.init();
    });
}
