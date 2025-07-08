/**
 * Product Import JavaScript
 * Handles file upload, column mapping, validation, and import processing
 */

class ProductImport {
    constructor() {
        this.stepper = null;
        this.fileData = null;
        this.availableFields = {};
        this.columnMapping = {};
        this.validationResults = null;
        this.currentPreviewPage = 1;
        this.previewPerPage = 50;
        this.totalPreviewPages = 1;

        this.init();
    }

    init() {
        this.initStepper();
        this.bindEvents();
        this.loadAvailableFields();
    }

    initStepper() {
        const stepperElement = document.querySelector('#kt_import_stepper');
        if (stepperElement) {
            this.stepper = new KTStepper(stepperElement);
            console.log('Stepper initialized:', {
                element: stepperElement,
                stepper: this.stepper,
                currentStep: this.stepper?.getCurrentStepIndex()
            });
        } else {
            console.error('Stepper element not found: #kt_import_stepper');
        }
    }

    bindEvents() {
        // File upload
        $('#import_file').on('change', (e) => this.handleFileUpload(e));

        // Stepper navigation
        if (this.stepper) {
            this.stepper.on('kt.stepper.click', (stepper) => {
                this.handleStepperClick(stepper);
            });

            this.stepper.on('kt.stepper.changed', (stepper) => {
                this.handleStepperChanged(stepper);
            });
        }

        // Form submission
        $('#kt_import_form').on('submit', (e) => {
            e.preventDefault();
            console.log('Form submitted, calling processImport');
            this.processImport();
        });

        // Column mapping changes
        $(document).on('change', '.column-mapping-select', (e) => {
            this.handleColumnMappingChange(e);
        });

        // Import options
        $(document).on('change', '#update_existing', () => {
            this.handleImportOptionsChange();
        });

        // File statistics and preview buttons
        $(document).on('click', '#viewFileStatsBtn', () => {
            this.showFileStatistics();
        });

        $(document).on('click', '#viewDetailedPreviewBtn', () => {
            this.showDetailedPreview();
        });

        $(document).on('click', '#refreshPreviewBtn', () => {
            this.refreshDetailedPreview();
        });

        $(document).on('click', '#prevPageBtn', () => {
            this.loadPreviewPage(this.currentPreviewPage - 1);
        });

        $(document).on('click', '#nextPageBtn', () => {
            this.loadPreviewPage(this.currentPreviewPage + 1);
        });

        // Previous step button
        $(document).on('click', '[data-kt-stepper-action="previous"]', () => {
            console.log('Previous button clicked');

            if (this.stepper) {
                this.stepper.goPrevious();
            } else {
                // Fallback navigation if stepper not working
                console.log('Stepper not available, using fallback navigation');
                this.goToPreviousStepManual();
            }
        });

        // Next step button
        $(document).on('click', '[data-kt-stepper-action="next"]', (e) => {
            console.log('Next button clicked');

            if (this.stepper) {
                const currentStep = this.stepper.getCurrentStepIndex();
                console.log('Moving from step:', currentStep);

                const validationResult = this.validateCurrentStep(currentStep);
                if (validationResult === true) {
                    this.stepper.goNext();
                } else {
                    e.preventDefault();
                    console.log('Step validation failed:', validationResult);

                    // Show specific error message to user
                    if (typeof validationResult === 'string') {
                        this.showError(validationResult);
                    } else {
                        this.showError('Vui lòng hoàn thành các thông tin bắt buộc trước khi tiếp tục.');
                    }
                }
            } else {
                // Fallback navigation if stepper not working
                console.log('Stepper not available, using fallback navigation');
                this.goToNextStepManual();
            }
        });
    }

    async handleFileUpload(e) {
        const file = e.target.files[0];
        if (!file) return;

        // Check file size (100MB = 104857600 bytes)
        const maxSize = 104857600;
        if (file.size > maxSize) {
            this.showError('File size exceeds 100MB limit. Please choose a smaller file.');
            this.resetFileUpload();
            return;
        }

        try {
            this.showLoading(`Uploading file (${this.formatFileSize(file.size)})...`);

            const formData = new FormData();
            formData.append('import_file', file);

            const response = await fetch('/admin/products/import/upload', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                body: formData
            });

            // Debug response
            console.log('Response status:', response.status);
            console.log('Response headers:', response.headers);

            if (!response.ok) {
                this.hideLoading();
                if (response.status === 413) {
                    this.showError('File too large. Server limit exceeded. Please check server configuration.');
                } else if (response.status === 500) {
                    this.showError('Server error. Please check server logs.');
                } else {
                    this.showError(`Upload failed with status: ${response.status}`);
                }
                this.resetFileUpload();
                return;
            }

            const data = await response.json();
            this.hideLoading();

            if (data.success) {
                this.fileData = data.data;
                this.showFileInfo(file, data.data);
                this.enableNextStep();

                console.log('File uploaded successfully:', {
                    fileData: this.fileData !== null,
                    headers: this.fileData?.headers?.length || 0,
                    rows: this.fileData?.total_rows || 0
                });

                this.showSuccess('File uploaded successfully');
            } else {
                this.showError(data.message);
                this.resetFileUpload();
            }

        } catch (error) {
            this.hideLoading();
            this.showError('Error uploading file: ' + error.message);
            this.resetFileUpload();
        }
    }

    showFileInfo(file, data) {
        const fileInfo = $('#file_info');
        const fileDetails = $('#file_details');

        const details = `
            <strong>File:</strong> ${file.name}<br>
            <strong>Size:</strong> ${this.formatFileSize(file.size)}<br>
            <strong>Type:</strong> ${data.file_type.toUpperCase()}<br>
            <strong>Rows:</strong> ${data.total_rows} (showing first 10)
        `;

        fileDetails.html(details);
        fileInfo.removeClass('d-none');
    }

    async loadAvailableFields() {
        try {
            console.log('Loading available fields...');
            const response = await fetch('/admin/products/import/fields');

            if (!response.ok) {
                console.warn(`API failed with status ${response.status}, using default fields`);
                this.setDefaultFields();
                return;
            }

            const data = await response.json();
            console.log('Fields response:', data);

            if (data.success && data.data && Object.keys(data.data).length > 0) {
                this.availableFields = data.data;
                console.log('Available fields loaded from API:', Object.keys(this.availableFields).length, 'fields');
            } else {
                console.warn('API response invalid or empty, using default fields');
                this.setDefaultFields();
            }
        } catch (error) {
            console.warn('Error loading available fields, using default fields:', error);
            this.setDefaultFields();
        }
    }

    setDefaultFields() {
        console.log('Setting default fields as fallback');
        this.availableFields = {
            'product_name': {
                'label': 'Tên sản phẩm',
                'required': true,
                'type': 'string',
                'description': 'Tên của sản phẩm (bắt buộc)',
            },
            'sku': {
                'label': 'SKU',
                'required': true,
                'type': 'string',
                'description': 'Mã SKU duy nhất của sản phẩm (bắt buộc)',
            },
            'sale_price': {
                'label': 'Giá bán',
                'required': true,
                'type': 'number',
                'description': 'Giá bán của sản phẩm (bắt buộc)',
            },
            'product_description': {
                'label': 'Mô tả sản phẩm',
                'required': false,
                'type': 'text',
                'description': 'Mô tả ngắn về sản phẩm',
            },
            'barcode': {
                'label': 'Mã vạch',
                'required': false,
                'type': 'string',
                'description': 'Mã vạch của sản phẩm',
            },
            'compare_price': {
                'label': 'Giá so sánh',
                'required': false,
                'type': 'number',
                'description': 'Giá so sánh (giá gốc)',
            },
            'cost_price': {
                'label': 'Giá vốn',
                'required': false,
                'type': 'number',
                'description': 'Giá vốn của sản phẩm',
            },
            'category_name': {
                'label': 'Danh mục',
                'required': false,
                'type': 'string',
                'description': 'Tên danh mục sản phẩm',
            },
            'stock_quantity': {
                'label': 'Số lượng tồn kho',
                'required': false,
                'type': 'number',
                'description': 'Số lượng tồn kho',
            },
            'product_status': {
                'label': 'Trạng thái',
                'required': false,
                'type': 'select',
                'options': {
                    'publish': 'Đã xuất bản',
                    'draft': 'Bản nháp',
                },
                'description': 'Trạng thái sản phẩm (publish/draft)',
            },
            'product_thumbnail': {
                'label': 'Ảnh sản phẩm',
                'required': false,
                'type': 'string',
                'description': 'URL hoặc đường dẫn ảnh đại diện sản phẩm',
            },
            'reorder_point': {
                'label': 'Tồn kho tối thiểu',
                'required': false,
                'type': 'number',
                'description': 'Số lượng tồn kho tối thiểu để cảnh báo nhập hàng',
            },
            'points': {
                'label': 'Điểm tích lũy',
                'required': false,
                'type': 'number',
                'description': 'Số điểm tích lũy khi mua sản phẩm này',
            },
        };
        console.log('Default fields set:', Object.keys(this.availableFields).length, 'fields');
    }

    handleStepperClick(stepper) {
        const currentStepIndex = stepper.getCurrentStepIndex();
        const targetStepIndex = stepper.getClickedStepIndex();

        console.log('Stepper click:', {
            currentStepIndex,
            targetStepIndex,
            fileData: this.fileData !== null,
            availableFields: Object.keys(this.availableFields).length
        });

        // Validate before moving to next step
        if (targetStepIndex > currentStepIndex) {
            const isValid = this.validateCurrentStep(currentStepIndex);
            console.log('Step validation:', {
                stepIndex: currentStepIndex,
                isValid: isValid
            });

            if (!isValid) {
                return false;
            }
        }

        return true;
    }

    async handleStepperChanged(stepper) {
        const currentStepIndex = stepper.getCurrentStepIndex();

        console.log('Step changed to:', currentStepIndex, {
            columnMapping: this.columnMapping,
            mappedFieldsCount: Object.keys(this.columnMapping).length
        });

        switch (currentStepIndex) {
            case 1: // Step 2: Column mapping
                await this.setupColumnMapping();
                break;
            case 2: // Step 3: Validation & Import
                // Check if we have column mapping before validating
                if (Object.keys(this.columnMapping).length === 0) {
                    console.warn('No column mapping found when entering step 3');
                    this.showError('Vui lòng quay lại bước 2 để map các cột trước khi validate.');
                    return;
                }
                this.validateImportData();
                break;
        }
    }

    validateCurrentStep(stepIndex) {
        console.log('Validating step:', stepIndex, {
            fileData: this.fileData !== null,
            columnMapping: Object.keys(this.columnMapping).length,
            validationResults: this.validationResults !== null
        });

        switch (stepIndex) {
            case 0: // Step 1: File upload step - validate file uploaded
                if (!this.fileData) {
                    return 'Vui lòng upload file trước khi tiếp tục.';
                }
                return true;

            case 1: // Step 2: Column mapping step - no validation, allow free navigation
                return true;

            case 2: // Step 3: Validation step - validate mapping before showing validation
                return this.validateColumnMapping();

            default:
                return true;
        }
    }

    async setupColumnMapping() {
        console.log('setupColumnMapping called', {
            fileData: this.fileData,
            availableFields: this.availableFields,
            containerExists: $('#column_mapping_container').length > 0
        });

        if (!this.fileData) {
            console.error('No file data available for column mapping');
            return;
        }

        // Ensure available fields are loaded
        if (!this.availableFields || Object.keys(this.availableFields).length === 0) {
            console.log('Available fields not loaded, loading now...');
            await this.loadAvailableFields();

            // Check again after loading
            if (!this.availableFields || Object.keys(this.availableFields).length === 0) {
                console.error('Failed to load available fields');
                this.showError('Không thể tải danh sách trường. Vui lòng refresh trang.');
                return;
            }
        }

        const container = $('#column_mapping_container');
        if (container.length === 0) {
            console.error('Column mapping container not found');
            return;
        }

        // Build column mapping interface
        let mappingHtml = `
            <div class="alert alert-info">
                <h5><i class="ki-duotone ki-information fs-2 me-2"></i>Ánh xạ cột</h5>
                <p class="mb-0">Chọn trường trong bảng products tương ứng với mỗi cột trong file Excel/CSV của bạn. Các trường có dấu (*) là bắt buộc.</p>
            </div>
            <div class="row">
        `;

        this.fileData.headers.forEach((header, index) => {
            // Clean header display
            const cleanHeader = header.trim();
            const headerDisplay = cleanHeader || `Cột ${index + 1}`;

            mappingHtml += `
                <div class="col-md-6 mb-5">
                    <div class="card card-bordered">
                        <div class="card-body p-4">
                            <label class="form-label fw-bold text-primary">
                                <i class="ki-duotone ki-file-up fs-3 me-2"></i>
                                Cột ${index + 1}: "${headerDisplay}"
                            </label>
                            <select class="form-select form-select-solid column-mapping-select" data-column-index="${index}">
                                <option value="">-- Bỏ qua cột này --</option>
                                ${this.buildFieldOptions()}
                            </select>
                            <div class="form-text text-muted mt-2">
                                Chọn trường tương ứng trong bảng products
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });

        mappingHtml += '</div>';
        container.html(mappingHtml);

        // Build preview table
        this.buildPreviewTable();

        // No auto-mapping - user will manually select mappings
        // Update visual indicators
        setTimeout(() => {
            this.updateMappingIndicators();
        }, 100);
    }

    buildFieldOptions() {
        let options = '';

        // Group required and optional fields
        const requiredFields = [];
        const optionalFields = [];

        Object.keys(this.availableFields).forEach(fieldKey => {
            const field = this.availableFields[fieldKey];
            if (field.required) {
                requiredFields.push({key: fieldKey, field: field});
            } else {
                optionalFields.push({key: fieldKey, field: field});
            }
        });

        // Add required fields first
        if (requiredFields.length > 0) {
            options += '<optgroup label="⭐ Trường bắt buộc">';
            requiredFields.forEach(({key, field}) => {
                options += `<option value="${key}">⭐ ${field.label}</option>`;
            });
            options += '</optgroup>';
        }

        // Add optional fields
        if (optionalFields.length > 0) {
            options += '<optgroup label="Trường tùy chọn">';
            optionalFields.forEach(({key, field}) => {
                options += `<option value="${key}">${field.label}</option>`;
            });
            options += '</optgroup>';
        }

        return options;
    }

    buildPreviewTable() {
        const table = $('#preview_table');
        const thead = table.find('thead tr');
        const tbody = table.find('tbody');

        // Build headers with cleaned display
        let headerHtml = '<th class="text-center">#</th>';
        this.fileData.headers.forEach((header, index) => {
            const cleanHeader = header.trim();
            const headerDisplay = cleanHeader || `Cột ${index + 1}`;
            headerHtml += `<th class="text-nowrap">${this.escapeHtml(headerDisplay)}</th>`;
        });
        thead.html(headerHtml);

        // Build preview rows
        let bodyHtml = '';
        this.fileData.data.forEach((row, index) => {
            bodyHtml += `<tr><td class="text-center fw-bold">${index + 1}</td>`;
            row.forEach(cell => {
                const cellValue = cell ? String(cell).trim() : '';
                const displayValue = this.escapeHtml(cellValue) || '<span class="text-muted">--</span>';
                bodyHtml += `<td class="text-nowrap" title="${this.escapeHtml(cellValue)}">${displayValue}</td>`;
            });
            bodyHtml += '</tr>';
        });
        tbody.html(bodyHtml);
    }

    autoMapColumns() {
        console.log('Auto-mapping columns:', {
            headers: this.fileData.headers,
            availableFields: Object.keys(this.availableFields)
        });

        this.fileData.headers.forEach((header, index) => {
            const normalizedHeader = header.toLowerCase().replace(/[^a-z0-9]/g, '_');

            console.log(`Mapping header "${header}" (normalized: "${normalizedHeader}")`);

            // Try to find matching field
            const matchingField = Object.keys(this.availableFields).find(fieldKey => {
                const fieldLabel = this.availableFields[fieldKey].label.toLowerCase();
                const labelMatch = fieldLabel.includes(normalizedHeader);
                const keyMatch = normalizedHeader.includes(fieldKey.replace('_', ''));
                const customMatch = this.isHeaderMatch(normalizedHeader, fieldKey);

                console.log(`  Checking field "${fieldKey}" (${fieldLabel}):`, {
                    labelMatch, keyMatch, customMatch
                });

                return labelMatch || keyMatch || customMatch;
            });

            if (matchingField) {
                console.log(`  ✅ Mapped "${header}" → "${matchingField}"`);
                $(`.column-mapping-select[data-column-index="${index}"]`).val(matchingField);
                this.columnMapping[index] = matchingField;
            } else {
                console.log(`  ❌ No mapping found for "${header}"`);
            }
        });

        console.log('Final column mapping:', this.columnMapping);
    }

    isHeaderMatch(header, fieldKey) {
        const mappings = {
            'name': 'product_name',
            'title': 'product_name',
            'description': 'product_description',
            'price': 'sale_price',
            'cost': 'cost_price',
            'category': 'category_name',
            'stock': 'stock_quantity',
            'quantity': 'stock_quantity',
            'status': 'product_status',
        };

        return mappings[header] === fieldKey;
    }

    handleColumnMappingChange(e) {
        const columnIndex = $(e.target).data('column-index');
        const fieldKey = $(e.target).val();

        console.log('Column mapping changed:', {
            columnIndex: columnIndex,
            fieldKey: fieldKey,
            previousMapping: this.columnMapping[columnIndex]
        });

        if (fieldKey) {
            this.columnMapping[columnIndex] = fieldKey;
        } else {
            delete this.columnMapping[columnIndex];
        }

        console.log('Updated column mapping:', this.columnMapping);

        // Remove any existing error when user makes changes
        $('.mapping-error').remove();

        // Update visual indicators
        this.updateMappingIndicators();
    }

    updateMappingIndicators() {
        if (!this.availableFields) return;

        const mappedFields = Object.values(this.columnMapping).filter(field => field);
        const requiredFields = Object.keys(this.availableFields).filter(key =>
            this.availableFields[key] && this.availableFields[key].required
        );

        // Reset all indicators
        $('.column-mapping-select').removeClass('border-success border-warning border-danger');
        $('.mapping-status').remove();

        // Add indicators for each select
        $('.column-mapping-select').each((_, select) => {
            const $select = $(select);
            const selectedValue = $select.val();
            const $card = $select.closest('.card');

            if (selectedValue) {
                // Field is mapped
                const isRequired = requiredFields.includes(selectedValue);
                $select.addClass(isRequired ? 'border-success' : 'border-info');

                const statusHtml = `<div class="mapping-status mt-2">
                    <span class="badge badge-light-${isRequired ? 'success' : 'info'} fs-8">
                        ${isRequired ? '⭐ Trường bắt buộc' : '✓ Đã ánh xạ'}
                    </span>
                </div>`;
                $card.find('.card-body').append(statusHtml);
            }
        });

        // Show summary of missing required fields
        const missingRequired = requiredFields.filter(field => !mappedFields.includes(field));
        if (missingRequired.length > 0) {
            const missingLabels = missingRequired.map(field =>
                this.availableFields[field] ? this.availableFields[field].label : field
            );

            const summaryHtml = `
                <div class="alert alert-warning mapping-summary mt-4" role="alert">
                    <h6><i class="ki-duotone ki-information-5 fs-3 me-2"></i>Trường bắt buộc chưa ánh xạ:</h6>
                    <ul class="mb-0">
                        ${missingLabels.map(label => `<li>⭐ ${label}</li>`).join('')}
                    </ul>
                </div>
            `;

            $('#column_mapping_container').append(summaryHtml);
        } else if (mappedFields.length > 0) {
            const summaryHtml = `
                <div class="alert alert-success mapping-summary mt-4" role="alert">
                    <h6><i class="ki-duotone ki-check fs-3 me-2"></i>Tất cả trường bắt buộc đã được ánh xạ!</h6>
                    <p class="mb-0">Bạn có thể tiếp tục sang bước tiếp theo.</p>
                </div>
            `;

            $('#column_mapping_container').append(summaryHtml);
        }
    }

    validateColumnMapping() {
        console.log('Validating column mapping:', {
            columnMapping: this.columnMapping,
            availableFields: this.availableFields
        });

        const mappedFields = Object.values(this.columnMapping).filter(field => field); // Remove empty values

        // Check if availableFields is loaded
        if (!this.availableFields || Object.keys(this.availableFields).length === 0) {
            console.error('Available fields not loaded');
            return 'Danh sách trường không được tải. Vui lòng refresh trang.';
        }

        // Get required fields
        const requiredFields = Object.keys(this.availableFields).filter(key =>
            this.availableFields[key] && this.availableFields[key].required
        );

        console.log('Required fields:', requiredFields);
        console.log('Mapped fields:', mappedFields);

        // If no mappings at all, show specific error
        if (mappedFields.length === 0) {
            if (requiredFields.length > 0) {
                const requiredLabels = requiredFields.map(field =>
                    this.availableFields[field] ? this.availableFields[field].label : field
                );
                return `Vui lòng map ít nhất các trường bắt buộc: ${requiredLabels.join(', ')}`;
            }
            return 'Vui lòng map ít nhất một cột với trường trong database.';
        }

        // Check for duplicates
        const duplicates = mappedFields.filter((field, index) => mappedFields.indexOf(field) !== index);
        if (duplicates.length > 0) {
            const duplicateLabels = duplicates.map(field =>
                this.availableFields[field] ? this.availableFields[field].label : field
            );
            return `Phát hiện trường bị map trùng: ${duplicateLabels.join(', ')}. Mỗi trường chỉ được map một lần.`;
        }

        // Check required fields
        const missingRequired = requiredFields.filter(field =>
            !mappedFields.includes(field)
        );

        if (missingRequired.length > 0) {
            const missingLabels = missingRequired.map(field =>
                this.availableFields[field] ? this.availableFields[field].label : field
            );
            console.log('Missing required fields:', missingRequired, 'Labels:', missingLabels);
            return `Thiếu các trường bắt buộc: ${missingLabels.join(', ')}. Vui lòng map các trường này trước khi tiếp tục.`;
        }

        console.log('Column mapping validation passed');
        return true;
    }

    async validateImportData() {
        try {
            console.log('validateImportData called', {
                columnMapping: this.columnMapping,
                mappingKeys: Object.keys(this.columnMapping),
                mappingValues: Object.values(this.columnMapping)
            });

            // Check if we have any column mapping
            if (!this.columnMapping || Object.keys(this.columnMapping).length === 0) {
                console.warn('No column mapping available, cannot validate');
                this.showError('Vui lòng quay lại bước 2 để map các cột trước khi validate.');
                return;
            }

            this.showLoading('Đang validate dữ liệu...');

            const requestData = {
                column_mapping: this.columnMapping
            };

            console.log('Sending validation request:', requestData);

            const response = await fetch('/admin/products/import/validate', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                body: JSON.stringify(requestData)
            });

            const data = await response.json();
            this.hideLoading();

            console.log('Validation response:', data);

            if (data.success) {
                this.validationResults = data.data;
                this.showValidationResults(data.data);
            } else {
                this.showError(data.message);
            }

        } catch (error) {
            this.hideLoading();
            console.error('Validation error:', error);
            this.showError('Lỗi khi validate dữ liệu: ' + error.message);
        }
    }

    showValidationResults(results) {
        const container = $('#validation_results');
        
        let html = `
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Validation Results</h5>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-40px me-3">
                                    <div class="symbol-label bg-light-primary">
                                        <i class="ki-duotone ki-file-up fs-2 text-primary"></i>
                                    </div>
                                </div>
                                <div>
                                    <div class="fw-bold">${results.total_rows}</div>
                                    <div class="text-muted fs-7">Total Rows</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-40px me-3">
                                    <div class="symbol-label bg-light-success">
                                        <i class="ki-duotone ki-check fs-2 text-success"></i>
                                    </div>
                                </div>
                                <div>
                                    <div class="fw-bold text-success">${results.valid_rows}</div>
                                    <div class="text-muted fs-7">Valid Rows</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-40px me-3">
                                    <div class="symbol-label bg-light-danger">
                                        <i class="ki-duotone ki-cross fs-2 text-danger"></i>
                                    </div>
                                </div>
                                <div>
                                    <div class="fw-bold text-danger">${results.invalid_rows}</div>
                                    <div class="text-muted fs-7">Invalid Rows</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-40px me-3">
                                    <div class="symbol-label bg-light-warning">
                                        <i class="ki-duotone ki-information fs-2 text-warning"></i>
                                    </div>
                                </div>
                                <div>
                                    <div class="fw-bold text-warning">${results.warnings.length}</div>
                                    <div class="text-muted fs-7">Warnings</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Show errors if any
        if (results.errors.length > 0) {
            html += `
                <div class="card mt-5">
                    <div class="card-header">
                        <h5 class="card-title text-danger">Validation Errors</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled">
                            ${results.errors.map(error => `<li class="text-danger mb-2">• ${error}</li>`).join('')}
                        </ul>
                    </div>
                </div>
            `;
        }

        // Show warnings if any
        if (results.warnings.length > 0) {
            html += `
                <div class="card mt-5">
                    <div class="card-header">
                        <h5 class="card-title text-warning">Warnings</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled">
                            ${results.warnings.map(warning => `<li class="text-warning mb-2">• ${warning}</li>`).join('')}
                        </ul>
                    </div>
                </div>
            `;
        }

        container.html(html);
    }

    async processImport() {
        console.log('processImport called', {
            validationResults: this.validationResults,
            columnMapping: this.columnMapping,
            validRows: this.validationResults?.valid_rows
        });

        // Check if we have column mapping (more important than validation results)
        if (!this.columnMapping || Object.keys(this.columnMapping).length === 0) {
            console.error('No column mapping available');
            this.showError('Vui lòng quay lại bước 2 để map các cột trước khi import.');
            return;
        }

        // Validate that we have validation results (optional for now)
        if (!this.validationResults) {
            console.warn('No validation results available, proceeding anyway');
            // Don't return, allow import to proceed
        } else if (this.validationResults.valid_rows === 0) {
            console.error('No valid rows to import');
            this.showError('Không có dòng dữ liệu hợp lệ để import.');
            return;
        }

        try {
            // Show progress modal
            this.showImportProgress();

            const importOptions = {
                update_existing: $('#update_existing').is(':checked')
            };

            const response = await fetch('/admin/products/import/process', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                body: JSON.stringify({
                    column_mapping: this.columnMapping,
                    import_options: importOptions
                })
            });

            const data = await response.json();
            this.hideImportProgress();

            if (data.success) {
                this.showImportResults(data.data);
                this.stepper.goNext(); // Move to step 4 (results)
                this.showSuccess('Import hoàn thành thành công!');
            } else {
                this.showError(data.message);
            }

        } catch (error) {
            this.hideImportProgress();
            this.showError('Lỗi khi import: ' + error.message);
        }
    }

    showImportProgress() {
        Swal.fire({
            title: 'Đang import sản phẩm...',
            html: `
                <div class="progress mb-3">
                    <div class="progress-bar progress-bar-striped progress-bar-animated"
                         role="progressbar" style="width: 100%"
                         aria-valuenow="100" aria-valuemin="0" aria-valuemax="100">
                    </div>
                </div>
                <p class="text-muted">Vui lòng đợi trong khi hệ thống xử lý dữ liệu...</p>
            `,
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    }

    hideImportProgress() {
        Swal.close();
    }

    showImportResults(results) {
        const container = $('#import_results');
        
        let html = `
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title text-success">Import Completed Successfully!</h5>
                    <div class="row mt-5">
                        <div class="col-md-3">
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-40px me-3">
                                    <div class="symbol-label bg-light-primary">
                                        <i class="ki-duotone ki-file-up fs-2 text-primary"></i>
                                    </div>
                                </div>
                                <div>
                                    <div class="fw-bold">${results.total_rows}</div>
                                    <div class="text-muted fs-7">Total Rows</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-40px me-3">
                                    <div class="symbol-label bg-light-success">
                                        <i class="ki-duotone ki-plus fs-2 text-success"></i>
                                    </div>
                                </div>
                                <div>
                                    <div class="fw-bold text-success">${results.created}</div>
                                    <div class="text-muted fs-7">Created</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-40px me-3">
                                    <div class="symbol-label bg-light-info">
                                        <i class="ki-duotone ki-pencil fs-2 text-info"></i>
                                    </div>
                                </div>
                                <div>
                                    <div class="fw-bold text-info">${results.updated}</div>
                                    <div class="text-muted fs-7">Updated</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-40px me-3">
                                    <div class="symbol-label bg-light-warning">
                                        <i class="ki-duotone ki-information fs-2 text-warning"></i>
                                    </div>
                                </div>
                                <div>
                                    <div class="fw-bold text-warning">${results.skipped}</div>
                                    <div class="text-muted fs-7">Skipped</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-8">
                        <a href="/admin/products" class="btn btn-primary">
                            <i class="ki-duotone ki-arrow-left fs-2 me-2"></i>
                            Back to Products
                        </a>
                        <a href="/admin/products/import" class="btn btn-light-primary ms-3">
                            <i class="ki-duotone ki-file-up fs-2 me-2"></i>
                            Import More Products
                        </a>
                    </div>
                </div>
            </div>
        `;

        // Show errors if any
        if (results.errors && results.errors.length > 0) {
            html += `
                <div class="card mt-5">
                    <div class="card-header">
                        <h5 class="card-title text-danger">Import Errors</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled">
                            ${results.errors.map(error => `<li class="text-danger mb-2">• ${error}</li>`).join('')}
                        </ul>
                    </div>
                </div>
            `;
        }

        container.html(html);
    }

    // Utility methods
    enableNextStep() {
        const nextBtn = $('[data-kt-stepper-action="next"]');
        nextBtn.prop('disabled', false).removeClass('disabled');

        console.log('Next step enabled:', {
            buttonFound: nextBtn.length > 0,
            isDisabled: nextBtn.prop('disabled'),
            hasDisabledClass: nextBtn.hasClass('disabled')
        });

        // Also enable stepper navigation
        if (this.stepper) {
            // Force enable next step in stepper
            const currentStep = this.stepper.getCurrentStepIndex();
            console.log('Current stepper step:', currentStep);
        }
    }

    resetFileUpload() {
        $('#import_file').val('');
        $('#file_info').addClass('d-none');
        this.fileData = null;
    }

    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    setButtonLoading(selector, loading) {
        const btn = $(selector);
        if (loading) {
            btn.attr('data-kt-indicator', 'on').prop('disabled', true);
        } else {
            btn.removeAttr('data-kt-indicator').prop('disabled', false);
        }
    }

    showLoading(message = 'Loading...') {
        Swal.fire({
            title: message,
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    }

    hideLoading() {
        Swal.close();
    }

    showSuccess(message) {
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: message,
            timer: 3000,
            showConfirmButton: false
        });
    }

    showError(message) {
        // Show SweetAlert
        Swal.fire({
            icon: 'error',
            title: 'Lỗi',
            text: message,
            confirmButtonText: 'OK',
            confirmButtonColor: '#d33'
        });

        // Also show inline error if we're on step 2 (column mapping)
        if (this.stepper && this.stepper.getCurrentStepIndex() === 1) {
            this.showInlineError(message);
        }
    }

    showInlineError(message) {
        // Remove existing error
        $('.mapping-error').remove();

        // Add error message above mapping container
        const errorHtml = `
            <div class="alert alert-danger mapping-error" role="alert">
                <div class="d-flex align-items-center">
                    <i class="ki-duotone ki-cross-circle fs-2 text-danger me-3">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    <div>
                        <h5 class="mb-1">Lỗi ánh xạ cột</h5>
                        <p class="mb-0">${message}</p>
                    </div>
                </div>
            </div>
        `;

        $('#column_mapping_container').prepend(errorHtml);

        // Scroll to error
        $('.mapping-error')[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    handleImportOptionsChange() {
        // Handle import options changes if needed
        console.log('Import options changed');
    }

    async showFileStatistics() {
        try {
            this.showLoading('Loading file statistics...');

            const response = await fetch('/admin/products/import/stats');
            const data = await response.json();
            this.hideLoading();

            if (data.success) {
                this.displayFileStatistics(data.data);
                $('#fileStatsModal').modal('show');
            } else {
                this.showError(data.message);
            }

        } catch (error) {
            this.hideLoading();
            this.showError('Error loading file statistics: ' + error.message);
        }
    }

    displayFileStatistics(stats) {
        const container = $('#file_stats_content');

        let html = `
            <!-- File Information -->
            <div class="card mb-5">
                <div class="card-header">
                    <h3 class="card-title">File Information</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-3">
                                <i class="ki-duotone ki-document fs-2 text-primary me-3"></i>
                                <div>
                                    <div class="fw-bold">${stats.file_info.name}</div>
                                    <div class="text-muted fs-7">File Name</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex align-items-center mb-3">
                                <i class="ki-duotone ki-file fs-2 text-info me-3"></i>
                                <div>
                                    <div class="fw-bold">${stats.file_info.size_formatted}</div>
                                    <div class="text-muted fs-7">File Size</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex align-items-center mb-3">
                                <i class="ki-duotone ki-file-up fs-2 text-success me-3"></i>
                                <div>
                                    <div class="fw-bold">${stats.file_info.type.toUpperCase()}</div>
                                    <div class="text-muted fs-7">File Type</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Data Summary -->
            <div class="card mb-5">
                <div class="card-header">
                    <h3 class="card-title">Data Summary</h3>
                    <div class="card-toolbar">
                        <div class="badge badge-light-${this.getQualityBadgeColor(stats.data_summary.data_quality_score)} fs-7">
                            Quality Score: ${stats.data_summary.data_quality_score}%
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="d-flex align-items-center mb-3">
                                <div class="symbol symbol-40px me-3">
                                    <div class="symbol-label bg-light-primary">
                                        <i class="ki-duotone ki-row-horizontal fs-2 text-primary"></i>
                                    </div>
                                </div>
                                <div>
                                    <div class="fw-bold">${stats.data_summary.total_rows}</div>
                                    <div class="text-muted fs-7">Total Rows</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex align-items-center mb-3">
                                <div class="symbol symbol-40px me-3">
                                    <div class="symbol-label bg-light-info">
                                        <i class="ki-duotone ki-row-vertical fs-2 text-info"></i>
                                    </div>
                                </div>
                                <div>
                                    <div class="fw-bold">${stats.data_summary.total_columns}</div>
                                    <div class="text-muted fs-7">Total Columns</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex align-items-center mb-3">
                                <div class="symbol symbol-40px me-3">
                                    <div class="symbol-label bg-light-success">
                                        <i class="ki-duotone ki-check fs-2 text-success"></i>
                                    </div>
                                </div>
                                <div>
                                    <div class="fw-bold">${stats.data_summary.data_rows}</div>
                                    <div class="text-muted fs-7">Data Rows</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex align-items-center mb-3">
                                <div class="symbol symbol-40px me-3">
                                    <div class="symbol-label bg-light-warning">
                                        <i class="ki-duotone ki-information fs-2 text-warning"></i>
                                    </div>
                                </div>
                                <div>
                                    <div class="fw-bold">${stats.data_summary.empty_rows}</div>
                                    <div class="text-muted fs-7">Empty Rows</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Column Analysis -->
            <div class="card mb-5">
                <div class="card-header">
                    <h3 class="card-title">Column Analysis</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-rounded table-striped border gy-7 gs-7">
                            <thead>
                                <tr class="fw-semibold fs-6 text-gray-800 border-bottom-2 border-gray-200">
                                    <th>Column</th>
                                    <th>Data Type</th>
                                    <th>Fill Rate</th>
                                    <th>Filled</th>
                                    <th>Empty</th>
                                    <th>Unique Values</th>
                                </tr>
                            </thead>
                            <tbody>
        `;

        stats.column_analysis.forEach(column => {
            html += `
                <tr>
                    <td class="fw-bold">${column.name}</td>
                    <td>
                        <span class="badge badge-light-${this.getDataTypeBadgeColor(column.primary_type)}">
                            ${column.primary_type}
                        </span>
                    </td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="progress h-6px w-100px me-2">
                                <div class="progress-bar bg-${this.getFillRateColor(column.fill_percentage)}"
                                     style="width: ${column.fill_percentage}%"></div>
                            </div>
                            <span class="fw-bold fs-7">${column.fill_percentage}%</span>
                        </div>
                    </td>
                    <td class="text-success fw-bold">${column.filled_count}</td>
                    <td class="text-danger fw-bold">${column.empty_count}</td>
                    <td class="text-info fw-bold">${column.unique_count}</td>
                </tr>
            `;
        });

        html += `
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        `;

        // Show issues if any
        if (stats.issues.empty_rows > 0 || stats.issues.total_duplicates > 0) {
            html += `
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title text-warning">Data Issues</h3>
                    </div>
                    <div class="card-body">
            `;

            if (stats.issues.empty_rows > 0) {
                html += `
                    <div class="alert alert-warning mb-3">
                        <i class="ki-duotone ki-information-5 fs-2 text-warning me-3"></i>
                        <strong>Empty Rows:</strong> ${stats.issues.empty_rows} empty rows detected
                    </div>
                `;
            }

            if (stats.issues.total_duplicates > 0) {
                html += `
                    <div class="alert alert-warning mb-3">
                        <i class="ki-duotone ki-information-5 fs-2 text-warning me-3"></i>
                        <strong>Duplicate Rows:</strong> ${stats.issues.total_duplicates} duplicate rows detected
                        ${stats.issues.duplicate_rows.length > 0 ?
                            `<br><small>First few duplicates at rows: ${stats.issues.duplicate_rows.join(', ')}</small>` :
                            ''
                        }
                    </div>
                `;
            }

            html += `
                    </div>
                </div>
            `;
        }

        container.html(html);
    }

    async showDetailedPreview() {
        this.currentPreviewPage = 1;
        await this.loadPreviewPage(1);
        $('#detailedPreviewModal').modal('show');
    }

    async refreshDetailedPreview() {
        await this.loadPreviewPage(this.currentPreviewPage);
    }

    async loadPreviewPage(page) {
        try {
            this.showLoading('Loading preview data...');

            const response = await fetch(`/admin/products/import/preview?page=${page}&limit=${this.previewPerPage}`);
            const data = await response.json();
            this.hideLoading();

            if (data.success) {
                this.currentPreviewPage = page;
                this.displayDetailedPreview(data.data);
            } else {
                this.showError(data.message);
            }

        } catch (error) {
            this.hideLoading();
            this.showError('Error loading preview: ' + error.message);
        }
    }

    displayDetailedPreview(previewData) {
        const table = $('#detailed_preview_table');
        const thead = table.find('thead tr');
        const tbody = table.find('tbody');

        // Build headers
        let headerHtml = '<th class="min-w-50px">Row #</th>';
        previewData.headers.forEach(header => {
            headerHtml += `<th class="min-w-150px">${header}</th>`;
        });
        thead.html(headerHtml);

        // Build preview rows
        let bodyHtml = '';
        previewData.data.forEach(item => {
            bodyHtml += `<tr><td class="fw-bold text-primary">${item.row_number}</td>`;
            item.data.forEach(cell => {
                const cellValue = cell || '';
                const displayValue = cellValue.length > 50 ? cellValue.substring(0, 50) + '...' : cellValue;
                bodyHtml += `<td title="${this.escapeHtml(cellValue)}">${this.escapeHtml(displayValue)}</td>`;
            });
            bodyHtml += '</tr>';
        });
        tbody.html(bodyHtml);

        // Update pagination info
        const pagination = previewData.pagination;
        this.totalPreviewPages = pagination.last_page;

        $('#preview_showing_info').text(`${pagination.from}-${pagination.to} of ${pagination.total} rows`);
        $('#preview_pagination_info').text(`Page ${pagination.current_page} of ${pagination.last_page}`);

        // Update pagination buttons
        $('#prevPageBtn').prop('disabled', pagination.current_page <= 1);
        $('#nextPageBtn').prop('disabled', pagination.current_page >= pagination.last_page);
    }

    getQualityBadgeColor(score) {
        if (score >= 90) return 'success';
        if (score >= 70) return 'warning';
        return 'danger';
    }

    getDataTypeBadgeColor(type) {
        const colors = {
            'numeric': 'primary',
            'text': 'info',
            'email': 'success',
            'date': 'warning',
            'empty': 'secondary'
        };
        return colors[type] || 'secondary';
    }

    getFillRateColor(percentage) {
        if (percentage >= 90) return 'success';
        if (percentage >= 70) return 'warning';
        return 'danger';
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    goToNextStepManual() {
        const steps = document.querySelectorAll('[data-kt-stepper-element="content"]');
        const navItems = document.querySelectorAll('[data-kt-stepper-element="nav"]');

        let currentStepIndex = -1;

        // Find current step
        steps.forEach((step, index) => {
            if (step.classList.contains('current')) {
                currentStepIndex = index;
            }
        });

        console.log('Manual navigation - current step:', currentStepIndex);

        // Move to next step
        if (currentStepIndex >= 0 && currentStepIndex < steps.length - 1) {
            // Remove current class from current step
            steps[currentStepIndex].classList.remove('current');
            navItems[currentStepIndex].classList.remove('current');

            // Add current class to next step
            const nextStepIndex = currentStepIndex + 1;
            steps[nextStepIndex].classList.add('current');
            navItems[nextStepIndex].classList.add('current');

            console.log('Moved to step:', nextStepIndex);

            // Trigger step changed event
            this.handleStepperChanged({ getCurrentStepIndex: () => nextStepIndex });
        }
    }

    goToPreviousStepManual() {
        const steps = document.querySelectorAll('[data-kt-stepper-element="content"]');
        const navItems = document.querySelectorAll('[data-kt-stepper-element="nav"]');

        let currentStepIndex = -1;

        // Find current step
        steps.forEach((step, index) => {
            if (step.classList.contains('current')) {
                currentStepIndex = index;
            }
        });

        console.log('Manual navigation - current step:', currentStepIndex);

        // Move to previous step
        if (currentStepIndex > 0) {
            // Remove current class from current step
            steps[currentStepIndex].classList.remove('current');
            navItems[currentStepIndex].classList.remove('current');

            // Add current class to previous step
            const prevStepIndex = currentStepIndex - 1;
            steps[prevStepIndex].classList.add('current');
            navItems[prevStepIndex].classList.add('current');

            console.log('Moved to step:', prevStepIndex);

            // Trigger step changed event
            this.handleStepperChanged({ getCurrentStepIndex: () => prevStepIndex });
        }
    }
}

// Initialize Product Import when document is ready
$(document).ready(function() {
    window.productImport = new ProductImport();
});
