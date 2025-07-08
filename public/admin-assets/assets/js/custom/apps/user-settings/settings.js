"use strict";

// Class definition
var KTUserSettings = function () {
    // Private variables
    var form;
    var submitButton;
    var resetButton;

    // Private functions
    var initForm = function () {
        // Handle save settings
        submitButton.addEventListener('click', function (e) {
            e.preventDefault();
            
            // Show loading
            submitButton.setAttribute('data-kt-indicator', 'on');
            submitButton.disabled = true;
            
            // Collect form data
            var formData = new FormData();
            
            // Theme mode
            var themeMode = document.querySelector('input[name="theme_mode"]:checked');
            if (themeMode) {
                formData.append('theme_mode', themeMode.value);
            }
            
            // Language
            var language = document.querySelector('select[name="language"]');
            if (language) {
                formData.append('language', language.value);
            }
            
            // Default branch shop
            var branchShop = document.querySelector('select[name="default_branch_shop"]');
            if (branchShop) {
                formData.append('default_branch_shop', branchShop.value);
            }
            
            // Email notifications
            var emailNotifications = [
                'email_order_created',
                'email_inventory_low', 
                'email_system_updates'
            ];
            
            emailNotifications.forEach(function(name) {
                var checkbox = document.querySelector('input[name="' + name + '"]');
                formData.append(name, checkbox && checkbox.checked ? '1' : '0');
            });
            
            // Web notifications
            var webNotifications = [
                'web_notifications',
                'notification_sound'
            ];
            
            webNotifications.forEach(function(name) {
                var checkbox = document.querySelector('input[name="' + name + '"]');
                formData.append(name, checkbox && checkbox.checked ? '1' : '0');
            });
            
            // Dashboard widgets
            var dashboardWidgets = [
                'widget_sales_today',
                'widget_revenue_chart',
                'widget_top_products',
                'widget_recent_activities'
            ];
            
            dashboardWidgets.forEach(function(name) {
                var checkbox = document.querySelector('input[name="' + name + '"]');
                formData.append(name, checkbox && checkbox.checked ? '1' : '0');
            });
            
            // Data display settings
            var itemsPerPage = document.querySelector('select[name="items_per_page"]');
            if (itemsPerPage) {
                formData.append('items_per_page', itemsPerPage.value);
            }
            
            var dateFormat = document.querySelector('select[name="date_format"]');
            if (dateFormat) {
                formData.append('date_format', dateFormat.value);
            }
            
            // Send AJAX request
            fetch('/admin/user-settings/store', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                // Hide loading
                submitButton.removeAttribute('data-kt-indicator');
                submitButton.disabled = false;
                
                if (data.success) {
                    // Show success message
                    Swal.fire({
                        text: data.message || "Cài đặt đã được lưu thành công!",
                        icon: "success",
                        buttonsStyling: false,
                        confirmButtonText: "OK",
                        customClass: {
                            confirmButton: "btn btn-primary"
                        }
                    }).then(function (result) {
                        if (result.isConfirmed) {
                            // Apply theme changes immediately
                            var selectedTheme = document.querySelector('input[name="theme_mode"]:checked');
                            if (selectedTheme) {
                                applyTheme(selectedTheme.value);
                            }
                            
                            // Apply language changes
                            var selectedLanguage = document.querySelector('select[name="language"]');
                            if (selectedLanguage && selectedLanguage.value !== getCurrentLanguage()) {
                                // Reload page to apply language changes
                                window.location.reload();
                            }
                        }
                    });
                } else {
                    // Show error message
                    Swal.fire({
                        text: data.message || "Có lỗi xảy ra khi lưu cài đặt!",
                        icon: "error",
                        buttonsStyling: false,
                        confirmButtonText: "OK",
                        customClass: {
                            confirmButton: "btn btn-primary"
                        }
                    });
                }
            })
            .catch(error => {
                // Hide loading
                submitButton.removeAttribute('data-kt-indicator');
                submitButton.disabled = false;
                
                console.error('Error:', error);
                
                // Show error message
                Swal.fire({
                    text: "Có lỗi xảy ra khi lưu cài đặt!",
                    icon: "error",
                    buttonsStyling: false,
                    confirmButtonText: "OK",
                    customClass: {
                        confirmButton: "btn btn-primary"
                    }
                });
            });
        });
        
        // Handle reset settings
        resetButton.addEventListener('click', function (e) {
            e.preventDefault();
            
            Swal.fire({
                text: "Bạn có chắc chắn muốn khôi phục cài đặt mặc định?",
                icon: "warning",
                showCancelButton: true,
                buttonsStyling: false,
                confirmButtonText: "Có, khôi phục!",
                cancelButtonText: "Hủy",
                customClass: {
                    confirmButton: "btn btn-danger",
                    cancelButton: "btn btn-active-light"
                }
            }).then(function (result) {
                if (result.isConfirmed) {
                    resetToDefaults();
                }
            });
        });
    };
    
    // Apply theme changes
    var applyTheme = function(theme) {
        var body = document.body;
        
        if (theme === 'dark') {
            body.setAttribute('data-bs-theme', 'dark');
            body.setAttribute('data-theme', 'dark');
        } else if (theme === 'light') {
            body.setAttribute('data-bs-theme', 'light');
            body.setAttribute('data-theme', 'light');
        } else if (theme === 'system') {
            // Use system preference
            var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (prefersDark) {
                body.setAttribute('data-bs-theme', 'dark');
                body.setAttribute('data-theme', 'dark');
            } else {
                body.setAttribute('data-bs-theme', 'light');
                body.setAttribute('data-theme', 'light');
            }
        }
    };
    
    // Get current language
    var getCurrentLanguage = function() {
        return document.documentElement.lang || 'vi';
    };
    
    // Reset to default settings
    var resetToDefaults = function() {
        // Reset theme to light
        document.querySelector('input[name="theme_mode"][value="light"]').checked = true;
        
        // Reset language to Vietnamese
        document.querySelector('select[name="language"]').value = 'vi';
        
        // Reset branch shop
        document.querySelector('select[name="default_branch_shop"]').value = '';
        
        // Reset email notifications to enabled
        document.querySelector('input[name="email_order_created"]').checked = true;
        document.querySelector('input[name="email_inventory_low"]').checked = true;
        document.querySelector('input[name="email_system_updates"]').checked = false;
        
        // Reset web notifications
        document.querySelector('input[name="web_notifications"]').checked = true;
        document.querySelector('input[name="notification_sound"]').checked = true;
        
        // Reset dashboard widgets to enabled
        document.querySelector('input[name="widget_sales_today"]').checked = true;
        document.querySelector('input[name="widget_revenue_chart"]').checked = true;
        document.querySelector('input[name="widget_top_products"]').checked = true;
        document.querySelector('input[name="widget_recent_activities"]').checked = true;
        
        // Reset data display
        document.querySelector('select[name="items_per_page"]').value = '25';
        document.querySelector('select[name="date_format"]').value = 'd/m/Y';
        
        // Trigger Select2 updates
        $(document).find('select[data-control="select2"]').trigger('change');
        
        // Show success message
        Swal.fire({
            text: "Cài đặt đã được khôi phục về mặc định!",
            icon: "success",
            buttonsStyling: false,
            confirmButtonText: "OK",
            customClass: {
                confirmButton: "btn btn-primary"
            }
        });
    };
    
    // Handle theme mode changes
    var initThemeHandlers = function() {
        var themeInputs = document.querySelectorAll('input[name="theme_mode"]');

        themeInputs.forEach(function(input) {
            input.addEventListener('change', function() {
                if (this.checked) {
                    applyTheme(this.value);
                }
            });
        });
    };

    // Handle branch shop selection
    var initBranchShopHandlers = function() {
        var branchShopSelect = document.querySelector('select[name="default_branch_shop"]');
        var branchShopInfo = document.getElementById('branch-shop-info');
        var branchAddress = document.getElementById('branch-address');
        var branchDelivery = document.getElementById('branch-delivery');
        var branchWarehouse = document.getElementById('branch-warehouse');

        if (branchShopSelect) {
            // Show info for initially selected branch shop
            var initialOption = branchShopSelect.querySelector('option:checked');
            if (initialOption && initialOption.value) {
                showBranchShopInfo(initialOption);
            }

            branchShopSelect.addEventListener('change', function() {
                var selectedOption = this.querySelector('option:checked');
                if (selectedOption && selectedOption.value) {
                    showBranchShopInfo(selectedOption);
                } else {
                    hideBranchShopInfo();
                }
            });
        }

        function showBranchShopInfo(option) {
            if (branchShopInfo && branchAddress && branchDelivery && branchWarehouse) {
                branchAddress.textContent = option.getAttribute('data-address') || 'Không có thông tin';
                branchDelivery.textContent = option.getAttribute('data-delivery') || 'Không có thông tin';
                branchWarehouse.textContent = 'Kho: ' + (option.getAttribute('data-warehouse') || 'Chưa gán kho');
                branchShopInfo.style.display = 'block';
            }
        }

        function hideBranchShopInfo() {
            if (branchShopInfo) {
                branchShopInfo.style.display = 'none';
            }
        }
    };

    // Public methods
    return {
        init: function () {
            form = document.querySelector('#kt_user_settings_form');
            submitButton = document.querySelector('#save-settings');
            resetButton = document.querySelector('#reset-settings');

            if (!submitButton || !resetButton) {
                return;
            }

            initForm();
            initThemeHandlers();
            initBranchShopHandlers();
        }
    };
}();

// On document ready
KTUtil.onDOMContentLoaded(function () {
    KTUserSettings.init();
});
