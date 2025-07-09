<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8" />
    <title>{{ __('order.quick_order') }} - {{ config('app.name') }}</title>
    <meta name="description" content="{{ __('order.quick_order') }}" />
    <meta name="keywords" content="{{ __('order.quick_order') }}" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />

    <!-- Global Stylesheets Bundle -->
    <link href="{{ asset('admin-assets/assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('admin-assets/assets/css/style.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('admin/css/quick-order-tabs.css') }}" rel="stylesheet" type="text/css" />

    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />

    <style>
        /* Full width layout */
        body {
            margin: 0;
            padding: 0;
            overflow: hidden; /* Disable body scroll */
            height: 100vh;
        }

        .quick-order-container {
            height: 100vh;
            display: flex;
            flex-direction: column;
            background: #f8f9fa;
        }

        /* Header */
        .quick-order-header {
            background: #009ef7;
            color: white;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            z-index: 1000;
            min-height: 60px;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 10px;
            flex: 1;
        }

        /* Barcode Input */
        .barcode-input-container {
            display: flex;
            align-items: center;
            gap: 10px;
            background: white;
            border-radius: 8px;
            padding: 8px 12px;
            min-width: 450px;
            max-width: 650px;
            position: relative;
        }

        .barcode-input {
            border: none;
            outline: none;
            font-size: 16px;
            font-weight: 500;
            flex: 1;
            padding: 4px 0;
        }



        /* Product Suggestions Dropdown */
        .product-suggestions {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #e4e6ef;
            border-top: none;
            border-radius: 0 0 8px 8px;
            max-height: 300px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .product-suggestions.show {
            display: block;
        }

        .product-suggestion {
            padding: 12px 15px;
            cursor: pointer;
            border-bottom: 1px solid #f1f1f1;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .product-suggestion:hover {
            background: #f8f9fa;
        }

        .product-suggestion:last-child {
            border-bottom: none;
        }

        .product-suggestion-image {
            width: 40px;
            height: 40px;
            border-radius: 6px;
            object-fit: cover;
            background: #f1f1f1;
            flex-shrink: 0;
        }

        .product-suggestion-info {
            flex: 1;
            min-width: 0;
        }

        .product-suggestion-name {
            font-weight: 600;
            font-size: 14px;
            color: #1e1e2d;
            margin-bottom: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .product-suggestion-details {
            font-size: 12px;
            color: #7e8299;
            display: flex;
            gap: 10px;
            margin-bottom: 4px;
        }

        .product-suggestion-stock {
            font-size: 12px;
            color: #50cd89;
            font-weight: 500;
        }

        .product-suggestion-stock.low {
            color: #f1416c;
        }

        .product-suggestion-stock.out {
            color: #7e8299;
        }

        .product-suggestion-price {
            font-weight: 600;
            font-size: 14px;
            color: #009ef7;
            flex-shrink: 0;
            text-align: right;
        }

        .product-suggestion-price-container {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 2px;
        }

        /* Seller and Channel Dropdowns */
        .header-info-section {
            background: white;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            gap: 15px;
        }



        .dropdowns-row {
            display: flex;
            gap: 15px;
        }

        .branch-time-row {
            display: flex;
            gap: 12px;
            margin-bottom: 16px;
        }

        .branch-time-row .col-6 {
            flex: 1;
        }

        .info-dropdown {
            position: relative;
            min-width: 120px;
            flex: 1;
        }

        .info-time-display {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 2px;
            min-width: 120px;
            height: 100%;
            justify-content: center;
        }

        .current-time {
            font-size: 14px;
            color: #1e1e2d;
            font-weight: 500;
        }

        .order-time {
            font-size: 12px;
            color: #7e8299;
        }

        .info-dropdown-toggle {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            background: white;
            border: 1px solid #e4e6ef;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 14px;
            color: #1e1e2d;
        }

        .info-dropdown-toggle:hover {
            border-color: #009ef7;
            box-shadow: 0 0 0 0.2rem rgba(0, 158, 247, 0.25);
        }

        .info-dropdown-toggle.disabled {
            background: #f5f8fa;
            cursor: not-allowed;
            color: #7e8299;
        }

        .info-dropdown-toggle.disabled:hover {
            border-color: #e4e6ef;
            box-shadow: none;
        }

        .info-dropdown-menu {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #e4e6ef;
            border-radius: 6px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            display: none;
            max-height: 300px;
            overflow-y: auto;
        }

        .info-dropdown-menu.show {
            display: block;
        }

        .info-dropdown-search {
            padding: 10px;
            border-bottom: 1px solid #f1f1f1;
        }

        .info-dropdown-search input {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #e4e6ef;
            border-radius: 4px;
            font-size: 13px;
        }

        .info-dropdown-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            cursor: pointer;
            transition: all 0.2s;
            border-bottom: 1px solid #f8f9fa;
        }

        .info-dropdown-item:hover {
            background: #f8f9fa;
        }

        .info-dropdown-item:last-child {
            border-bottom: none;
        }

        .info-dropdown-item.selected {
            background: #e1f0ff;
            color: #009ef7;
        }

        .info-dropdown-icon {
            width: 20px;
            height: 20px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            flex-shrink: 0;
        }

        .info-dropdown-text {
            display: flex;
            flex-direction: column;
            flex: 1;
        }

        .info-dropdown-title {
            font-weight: 500;
            font-size: 14px;
            line-height: 1.2;
        }

        .info-dropdown-subtitle {
            font-size: 12px;
            color: #7e8299;
            line-height: 1.2;
            margin-top: 2px;
        }

        .channel-icon {
            background: #f1f1f1;
            color: #7e8299;
        }

        .channel-icon.offline {
            background: #e1f0ff;
            color: #009ef7;
        }

        .channel-icon.online {
            background: #e8f5e8;
            color: #50cd89;
        }

        .channel-icon.marketplace {
            background: #fff5e6;
            color: #f1416c;
        }

        .channel-icon.social_media {
            background: #1877f2;
            color: white;
        }

        .channel-icon.phone_order {
            background: #f1f1f1;
            color: #7e8299;
        }

        .branch-icon {
            background: #009ef7;
            color: white;
        }

        .info-label {
            font-size: 12px;
            color: #7e8299;
            margin-bottom: 2px;
        }



        /* Tabs */
        .order-tabs {
            display: flex;
            align-items: center;
            gap: 5px;
            margin-left: 15px;
        }

        .order-tab {
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.2);
            color: rgba(255,255,255,0.8);
            padding: 8px 16px;
            border-radius: 6px 6px 0 0;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
            min-width: 120px;
            max-width: 200px;
        }

        .order-tab.active {
            background: white;
            color: #1e1e2d;
            border-color: white;
        }

        .order-tab[data-tab-type="order"] .tab-title {
            color: #FF8800;
            font-weight: bold;
        }

        .order-tab[data-tab-type="order"].active .tab-title {
            color: #FF8800;
            font-weight: bold;
        }

        .order-tab:hover:not(.active) {
            background: rgba(255,255,255,0.2);
            color: white;
        }

        .tab-title {
            flex: 1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            font-size: 14px;
            font-weight: 500;
        }

        .tab-count {
            background: #009ef7;
            color: white;
            font-size: 11px;
            padding: 2px 6px;
            border-radius: 10px;
            min-width: 18px;
            text-align: center;
        }

        .order-tab.active .tab-count {
            background: #009ef7;
        }

        .tab-close {
            background: none;
            border: none;
            color: inherit;
            font-size: 16px;
            cursor: pointer;
            padding: 0;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.2s;
        }

        .tab-close:hover {
            background: rgba(255,0,0,0.2);
        }



        .tab-controls-group {
            display: flex;
            align-items: center;
            gap: 2px;
        }

        .add-tab-btn {
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.3);
            color: white;
            width: 32px;
            height: 32px;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
            font-size: 14px;
        }

        .add-tab-btn:hover {
            background: rgba(255,255,255,0.2);
        }

        .add-tab-btn.dropdown-toggle::after {
            display: none;
        }

        #addNewInvoiceBtn {
            border-radius: 6px 0 0 6px;
            border-right: none;
        }

        #addTabDropdownBtn {
            border-radius: 0 6px 6px 0;
            width: 24px;
            font-size: 12px;
        }

        .dropdown-menu {
            min-width: 180px;
        }

        .dropdown-item {
            padding: 8px 16px;
            font-size: 14px;
        }

        .dropdown-item:hover {
            background-color: #f8f9fa;
        }

        .order-text {
            color: #FF8800 !important;
            font-weight: 600;
        }

        /* Main Content */
        .quick-order-content {
            flex: 1;
            overflow: hidden;
            padding: 0;
        }

        .main-row {
            height: 100%;
            margin: 0;
        }

        .main-col {
            height: 100%;
            padding: 0;
        }

        .col-4.main-col {
            position: relative;
            padding-bottom: 180px; /* Space for sticky summary */
        }

        /* Left Column - Order Items */
        .order-items-col {
            background: white;
            border-right: 1px solid #e4e6ef;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .order-items-header {
            padding: 20px;
            border-bottom: 1px solid #e4e6ef;
            background: #f8f9fa;
            flex-shrink: 0;
        }

        .order-items-list {
            flex: 1;
            overflow-y: auto;
            padding: 0;
        }

        .order-notes-section {
            padding: 15px 20px;
            border-top: 1px solid #e4e6ef;
            background: #f8f9fa;
            flex-shrink: 0;
        }

        .order-notes-section .form-label {
            font-weight: 600;
            font-size: 14px;
            color: #1e1e2d;
            margin-bottom: 8px;
            display: block;
        }

        .order-notes-section textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #e4e6ef;
            border-radius: 6px;
            font-size: 14px;
            resize: vertical;
            min-height: 60px;
            max-height: 120px;
        }

        .order-item {
            padding: 15px 20px;
            border-bottom: 1px solid #f1f1f1;
            display: flex;
            align-items: center;
            gap: 15px;
            transition: all 0.2s;
        }

        .order-item:hover {
            background: #f8f9fa;
        }

        .order-item.out-of-stock {
            background: #ffeaea;
            border-left: 4px solid #f1416c;
        }

        .order-item.out-of-stock:hover {
            background: #ffdddd;
        }

        .order-item.out-of-stock .item-name {
            color: #f1416c;
        }

        .stock-warning {
            color: #f1416c;
            font-size: 11px;
            font-weight: 500;
            margin-top: 2px;
        }

        .item-number {
            width: 30px;
            height: 30px;
            background: #009ef7;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 14px;
            flex-shrink: 0;
        }

        .item-image {
            width: 50px;
            height: 50px;
            border-radius: 8px;
            object-fit: cover;
            background: #f1f1f1;
            flex-shrink: 0;
        }

        .item-info {
            flex: 1;
            min-width: 0;
        }

        .item-name {
            font-weight: 600;
            font-size: 14px;
            color: #1e1e2d;
            margin-bottom: 4px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .item-sku {
            font-size: 12px;
            color: #7e8299;
        }

        .item-unit-price {
            font-size: 12px;
            color: #7e8299;
            margin-top: 2px;
        }

        .item-quantity {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .qty-btn {
            width: 28px;
            height: 28px;
            border: 1px solid #e4e6ef;
            background: white;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        .qty-btn:hover {
            background: #f8f9fa;
            border-color: #009ef7;
        }

        .qty-input {
            width: 50px;
            text-align: center;
            border: 1px solid #e4e6ef;
            border-radius: 4px;
            padding: 4px;
            font-size: 14px;
        }

        .item-price {
            font-weight: 600;
            font-size: 16px;
            color: #009ef7;
            min-width: 80px;
            text-align: right;
        }

        .item-remove {
            background: #f1416c;
            border: none;
            color: white;
            width: 32px;
            height: 32px;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        .item-remove:hover {
            background: #d1293d;
        }

        /* Right Column - Customer Info */
        .customer-col {
            background: white;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .customer-header {
            padding: 20px;
            border-bottom: 1px solid #e4e6ef;
            background: #f8f9fa;
            flex-shrink: 0;
        }

        .customer-form {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            font-weight: 600;
            font-size: 14px;
            color: #1e1e2d;
            margin-bottom: 8px;
            display: block;
        }

        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid #e4e6ef;
            border-radius: 6px;
            font-size: 14px;
            transition: all 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: #009ef7;
            box-shadow: 0 0 0 3px rgba(0, 158, 247, 0.1);
        }

        /* Payment Method Radio Buttons */
        .payment-methods {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
        }

        .payment-method {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            padding: 8px 12px;
            border-radius: 6px;
            transition: all 0.2s;
            border: 1px solid #e4e6ef;
            background: white;
            min-width: 80px;
            justify-content: center;
        }

        .payment-method:hover {
            background: #f8f9fa;
            border-color: #009ef7;
        }

        .payment-method.active {
            background: #009ef7;
            border-color: #009ef7;
            color: white;
        }

        .payment-method input[type="radio"] {
            display: none;
        }

        .payment-method-icon {
            font-size: 16px;
        }

        .payment-method-text {
            font-size: 14px;
            font-weight: 500;
        }

        /* Bank Account Selection */
        .bank-account-section {
            display: none;
            margin-top: 15px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            border: 1px solid #e4e6ef;
        }

        .bank-account-section.show {
            display: block;
        }

        .bank-transfer-layout {
            display: flex;
            gap: 20px;
            align-items: flex-start;
        }

        .qr-code-container {
            flex-shrink: 0;
        }

        .qr-code-image {
            width: 120px;
            height: 120px;
            border: 1px solid #e4e6ef;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: white;
            margin-bottom: 10px;
        }

        .qr-code-image img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .qr-toggle-btn {
            width: 100%;
            padding: 8px 12px;
            background: #009ef7;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 12px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            transition: all 0.2s;
        }

        .qr-toggle-btn:hover {
            background: #0084d3;
        }

        .bank-account-dropdown {
            flex: 1;
        }

        .autocomplete-container {
            position: relative;
        }

        .bank-account-select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #e4e6ef;
            border-radius: 6px;
            background: white;
            font-size: 14px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: all 0.2s;
        }

        .bank-account-select:hover {
            border-color: #009ef7;
        }

        .bank-account-select.active {
            border-color: #009ef7;
            box-shadow: 0 0 0 3px rgba(0, 158, 247, 0.1);
        }

        .bank-account-text {
            font-weight: 600;
            color: #1e1e2d;
        }

        .bank-account-dropdown-icon {
            color: #7e8299;
            transition: transform 0.2s;
        }

        .bank-account-select.active .bank-account-dropdown-icon {
            transform: rotate(180deg);
        }

        .bank-account-options {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #e4e6ef;
            border-top: none;
            border-radius: 0 0 6px 6px;
            max-height: 200px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
        }

        .bank-account-options.show {
            display: block;
        }

        .bank-account-option {
            padding: 12px 15px;
            cursor: pointer;
            border-bottom: 1px solid #f1f1f1;
            transition: all 0.2s;
        }

        .bank-account-option:hover {
            background: #f8f9fa;
        }

        .bank-account-option:last-child {
            border-bottom: none;
        }

        .bank-account-option.selected {
            background: #e7f3ff;
            color: #009ef7;
        }

        .bank-option-name {
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 2px;
        }

        .bank-option-details {
            font-size: 12px;
            color: #7e8299;
        }

        /* Autocomplete Styles */
        .autocomplete-container {
            position: relative;
        }

        .autocomplete-suggestions {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #e4e6ef;
            border-top: none;
            border-radius: 0 0 6px 6px;
            max-height: 200px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .autocomplete-suggestions.show {
            display: block;
        }

        .autocomplete-suggestion {
            padding: 12px;
            cursor: pointer;
            border-bottom: 1px solid #f1f1f1;
            transition: all 0.2s;
        }

        .autocomplete-suggestion:hover,
        .autocomplete-suggestion.highlighted {
            background: #f8f9fa;
        }

        .autocomplete-suggestion:last-child {
            border-bottom: none;
        }

        .suggestion-name {
            font-weight: 600;
            font-size: 14px;
            color: #1e1e2d;
        }

        .suggestion-details {
            font-size: 12px;
            color: #7e8299;
            margin-top: 2px;
        }

        /* Order Summary */
        .order-summary {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 20px;
            border-top: 1px solid #e4e6ef;
            background: white;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
            z-index: 10;
        }

        .summary-input {
            transition: border-color 0.3s ease;
        }

        .summary-input:focus {
            border-bottom-color: #009ef7 !important;
            box-shadow: 0 2px 0 rgba(0, 158, 247, 0.3);
        }

        .summary-input:hover {
            border-bottom-color: #009ef7 !important;
        }

        /* Customer display styling */
        .selected-customer .customer-name-phone strong {
            color: #009ef7 !important;
            text-decoration: underline;
            font-size: 1.1rem;
            cursor: pointer;
            font-weight: 600;
        }

        .selected-customer .customer-name-phone strong:hover {
            color: #0056b3 !important;
            text-decoration: underline;
        }

        .selected-customer .customer-name-phone .text-muted {
            font-size: 0.95rem;
            margin-left: 8px;
        }

        /* Discount modal styling */
        #discountModal .d-flex.gap-2 {
            gap: 8px !important;
        }

        #discountModal .btn {
            border-radius: 8px !important;
            font-weight: 600;
            transition: all 0.2s ease;
            border: 2px solid #009ef7;
            padding: 8px 16px;
            font-size: 0.95rem;
        }

        #discountModal .btn-primary {
            background-color: #009ef7;
            border-color: #009ef7;
            color: white;
            box-shadow: 0 2px 4px rgba(0, 158, 247, 0.3);
        }

        #discountModal .btn-outline-primary {
            background-color: transparent;
            border-color: #009ef7;
            color: #009ef7;
        }

        #discountModal .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 158, 247, 0.4);
        }

        #discountModal .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }

        #discountModal .btn-outline-primary:hover {
            background-color: #009ef7;
            border-color: #009ef7;
            color: white;
        }

        #discountModal .form-control-lg {
            font-size: 1.25rem;
            font-weight: 600;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .summary-row.total {
            font-weight: 700;
            font-size: 18px;
            color: #1e1e2d;
            padding-top: 10px;
            border-top: 1px solid #e4e6ef;
            margin-top: 10px;
        }

        .summary-row.payment {
            align-items: center;
            margin-top: 10px;
        }

        .summary-row.payment input {
            border: 1px solid #e4e6ef;
            border-radius: 4px;
            padding: 5px 8px;
            font-size: 14px;
        }

        /* Customer Selection */
        .customer-selection {
            position: relative;
        }

        .selected-customer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px 16px;
            background: #f8f9fa;
            border: 1px solid #e4e6ef;
            border-radius: 8px;
            font-size: 16px;
            min-height: 60px;
        }

        .customer-info {
            display: flex;
            align-items: center;
            flex: 1;
        }

        .customer-info i {
            font-size: 20px;
            margin-right: 12px;
        }

        .customer-name-phone {
            font-weight: 600;
            color: #009ef7;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 16px;
        }

        .customer-name-phone:hover {
            text-decoration: underline;
            color: #0077c7;
        }

        .btn-remove-customer {
            background: none;
            border: none;
            color: #a1a5b7;
            cursor: pointer;
            padding: 6px;
            border-radius: 4px;
            transition: all 0.3s;
            font-size: 16px;
        }

        .btn-remove-customer:hover {
            background: #e4e6ef;
            color: #f1416c;
        }

        .create-order-btn {
            width: 100%;
            background: #50cd89;
            border: none;
            color: white;
            padding: 20px;
            border-radius: 8px;
            font-size: 18px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 20px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .create-order-btn:hover {
            background: #47be7d;
        }

        .create-order-btn:disabled {
            background: #e4e6ef;
            color: #a1a5b7;
            cursor: not-allowed;
        }

        /* Empty State */
        .empty-order {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #a1a5b7;
            padding: 40px;
            min-height: 300px;
        }

        .empty-icon {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.5;
            color: #e4e6ef;
        }

        .empty-text {
            font-size: 16px;
            text-align: center;
            line-height: 1.5;
            max-width: 300px;
        }

        .empty-hint {
            font-size: 14px;
            color: #7e8299;
            margin-top: 10px;
            text-align: center;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .main-row {
                flex-direction: column !important;
            }

            .main-col {
                width: 100% !important;
                max-width: 100% !important;
                flex: none !important;
            }

            .customer-col {
                max-height: 40vh;
            }

            .quick-order-header {
                flex-direction: column;
                gap: 10px;
                padding: 10px;
            }

            .header-left,
            .header-right {
                width: 100%;
                justify-content: center;
            }

            .barcode-input-container {
                min-width: 350px;
                max-width: 100%;
            }

            .col-4.main-col {
                padding-bottom: 160px; /* Reduced space for mobile */
            }

            .order-summary {
                padding: 15px; /* Reduced padding for mobile */
            }

            .order-tabs {
                justify-content: center;
                flex-wrap: wrap;
            }
        }
    </style>
</head>

<body>
    <div class="quick-order-container">
        <!-- Header -->
        <div class="quick-order-header">
            <div class="header-left">
                <!-- Barcode Input -->
                <div class="barcode-input-container">
                    <i class="fas fa-search" style="color: #7e8299;"></i>
                    <input type="text"
                           id="globalBarcodeInput"
                           class="barcode-input"
                           placeholder="{{ __('order.search_product_name_sku_barcode') }}"
                           autocomplete="off">

                    <!-- Product Suggestions Dropdown -->
                    <div class="product-suggestions" id="productSuggestions"></div>
                </div>
            </div>

            <div class="header-right">
                <!-- Order Tabs -->
                <div class="order-tabs" id="orderTabsContainer">
                    <!-- Tabs will be added here dynamically -->
                </div>

                <!-- Add Tab Buttons -->
                <div class="tab-controls-group">
                    <button type="button" id="addNewInvoiceBtn" class="add-tab-btn" onclick="addNewTab('invoice')" title="Tạo hóa đơn mới">
                        <i class="fas fa-plus"></i>
                    </button>
                    <div class="dropdown">
                        <button type="button" id="addTabDropdownBtn" class="add-tab-btn dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" title="Tùy chọn thêm">
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item order-item" href="#" onclick="addNewTab('order')">
                                    <i class="fas fa-shopping-cart me-2"></i>
                                    <span class="order-text">Tạo đơn hàng mới</span>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="#" onclick="addNewTab('invoice')">
                                    <i class="fas fa-file-invoice me-2"></i>
                                    Tạo hóa đơn mới
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="quick-order-content">
            <!-- Tab Content Container -->
            <div id="orderTabsContent" class="h-100">
                <!-- Tab content will be added here dynamically -->
            </div>
        </div>
    </div>

    <!-- Template for Order Tab Content -->
    <div id="orderTabTemplate" style="display: none;">
        <div class="row main-row h-100">
            <!-- Left Column - Order Items -->
            <div class="col-8 main-col">
                <div class="order-items-col">
                    <div class="order-items-header">
                        <h4 style="margin: 0; font-size: 16px; font-weight: 600;">{{ __('order.order_items') }}</h4>
                        <p style="margin: 5px 0 0 0; font-size: 14px; color: #7e8299;">
                            <span class="items-count">0</span> {{ __('order.items') }}
                        </p>
                    </div>

                    <div class="order-items-list" id="orderItemsList">
                        <div class="empty-order" id="emptyOrderState">
                            <div class="empty-icon">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <div class="empty-text">
                                {{ __('order.scan_barcodes_to_start') }}
                            </div>
                            <div class="empty-hint">
                                Nhấn F3 để focus vào ô nhập mã vạch
                            </div>
                        </div>
                    </div>

                    <!-- Order Notes Section -->
                    <div class="order-notes-section">
                        <label class="form-label">{{ __('order.notes') }}</label>
                        <textarea name="notes" id="orderNotes" placeholder="{{ __('order.order_notes_placeholder') }}"></textarea>
                    </div>
                </div>
            </div>

            <!-- Right Column - Customer Info -->
            <div class="col-4 main-col">
                <!-- Header Info Section -->
                <div class="header-info-section">
                    <!-- Dropdowns Row -->
                    <div class="dropdowns-row">
                        <!-- Seller Dropdown -->
                        <div class="info-dropdown" id="TAB_ID_sellerDropdown">
                            <div class="info-label">Người bán</div>
                            <div class="info-dropdown-toggle {{ Auth::user()->is_root == 1 ? '' : 'disabled' }}" id="TAB_ID_sellerToggle">
                                <span id="TAB_ID_sellerName">{{ Auth::user()->full_name ?? Auth::user()->name }}</span>
                                @if(Auth::user()->is_root == 1)
                                    <i class="fas fa-chevron-down"></i>
                                @endif
                            </div>
                            @if(Auth::user()->is_root == 1)
                                <div class="info-dropdown-menu" id="TAB_ID_sellerMenu">
                                    <div class="info-dropdown-search">
                                        <input type="text" placeholder="Tìm người bán..." id="TAB_ID_sellerSearch">
                                    </div>
                                    <div id="TAB_ID_sellerList">
                                        <!-- Seller list will be populated by JavaScript -->
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Channel Dropdown -->
                        <div class="info-dropdown" id="TAB_ID_channelDropdown">
                            <div class="info-label">Kênh bán hàng</div>
                            <div class="info-dropdown-toggle" id="TAB_ID_channelToggle">
                                <div class="info-dropdown-icon channel-icon offline">
                                    <i class="fas fa-store"></i>
                                </div>
                                <span id="TAB_ID_channelName">Cửa hàng</span>
                                <i class="fas fa-chevron-down"></i>
                            </div>
                            <div class="info-dropdown-menu" id="TAB_ID_channelMenu">
                                <div class="info-dropdown-search">
                                    <input type="text" placeholder="Tìm kênh bán hàng..." id="TAB_ID_channelSearch">
                                </div>
                                <div id="TAB_ID_channelList">
                                    <div class="info-dropdown-item selected" data-channel="offline">
                                        <div class="info-dropdown-icon channel-icon offline">
                                            <i class="fas fa-store"></i>
                                        </div>
                                        <span>Cửa hàng</span>
                                        <i class="fas fa-check ms-auto"></i>
                                    </div>
                                    <div class="info-dropdown-item" data-channel="online">
                                        <div class="info-dropdown-icon channel-icon online">
                                            <i class="fas fa-globe"></i>
                                        </div>
                                        <span>Website</span>
                                    </div>
                                    <div class="info-dropdown-item" data-channel="marketplace">
                                        <div class="info-dropdown-icon channel-icon marketplace">
                                            <i class="fas fa-shopping-cart"></i>
                                        </div>
                                        <span>Marketplace</span>
                                    </div>
                                    <div class="info-dropdown-item" data-channel="social_media">
                                        <div class="info-dropdown-icon channel-icon social_media">
                                            <i class="fab fa-facebook-f"></i>
                                        </div>
                                        <span>Mạng xã hội</span>
                                    </div>
                                    <div class="info-dropdown-item" data-channel="phone_order">
                                        <div class="info-dropdown-icon channel-icon phone_order">
                                            <i class="fas fa-phone"></i>
                                        </div>
                                        <span>Điện thoại</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Branch Shop and Time Row -->
                    <div class="branch-time-row">
                        <!-- Branch Shop Column -->
                        <div class="col-6">
                            <div class="info-dropdown" id="TAB_ID_branchShopDropdown">
                                <div class="info-label">Chi nhánh</div>
                                <div class="info-dropdown-toggle" id="TAB_ID_branchShopToggle">
                                    <div class="info-dropdown-icon branch-icon">
                                        <i class="fas fa-building"></i>
                                    </div>
                                    <span id="TAB_ID_branchShopName">{{ $defaultBranchShop->name ?? 'Chọn chi nhánh' }}</span>
                                    <i class="fas fa-chevron-down"></i>
                                </div>
                                <div class="info-dropdown-menu" id="TAB_ID_branchShopMenu">
                                    <div class="info-dropdown-search">
                                        <input type="text" placeholder="Tìm chi nhánh..." id="TAB_ID_branchShopSearch">
                                    </div>
                                    <div id="TAB_ID_branchShopList">
                                        @foreach($branchShops as $branchShop)
                                            <div class="info-dropdown-item {{ ($defaultBranchShop && $defaultBranchShop->id == $branchShop->id) ? 'selected' : '' }}"
                                                 data-branch-id="{{ $branchShop->id }}"
                                                 data-branch-name="{{ $branchShop->name }}">
                                                <div class="info-dropdown-icon branch-icon">
                                                    <i class="fas fa-building"></i>
                                                </div>
                                                <div class="info-dropdown-text">
                                                    <span class="info-dropdown-title">{{ $branchShop->name }}</span>
                                                    @if($branchShop->address)
                                                        <span class="info-dropdown-subtitle">{{ $branchShop->address }}</span>
                                                    @endif
                                                </div>
                                                @if($defaultBranchShop && $defaultBranchShop->id == $branchShop->id)
                                                    <i class="fas fa-check ms-auto"></i>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Time Column -->
                        <div class="col-6">
                            <div class="info-time-display">
                                <div class="current-time" id="currentTime">{{ now()->format('d/m/Y H:i:s') }}</div>
                                <div class="order-time" id="orderCreatedTime">Thời gian tạo đơn: <span id="orderTime">--:--</span></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="customer-col">
                    <div class="customer-header">
                        <h4 style="margin: 0; font-size: 16px; font-weight: 600;">{{ __('order.order_info') }}</h4>
                    </div>

                    <div class="customer-form">
                        <div class="form-group">
                            <div class="customer-selection">
                                <!-- Customer search input (shown when no customer selected) -->
                                <div class="autocomplete-container" id="customerSearchContainer">
                                    <input type="text" class="form-control" name="customer_search" id="customerSearch"
                                           placeholder="Tìm khách hàng (để trống = Khách lẻ)" autocomplete="off">
                                    <div class="autocomplete-suggestions" id="customerSuggestions"></div>
                                </div>

                                <!-- Selected customer display (shown when customer selected) -->
                                <div class="selected-customer" id="selectedCustomerDisplay" style="display: none;">
                                    <div class="customer-info">
                                        <i class="fas fa-user-circle text-primary"></i>
                                        <span class="customer-name-phone" id="selectedCustomerText" data-customer-id=""></span>
                                    </div>
                                    <button type="button" class="btn-remove-customer" id="removeCustomerBtn">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>

                                <input type="hidden" name="customer_id" id="customerSelect">
                            </div>
                        </div>

                        <!-- Hidden branch shop field - use default -->
                        <input type="hidden" name="branch_shop_id" id="branchShopSelect" value="{{ $defaultBranchShop->id ?? '' }}">

                        <div class="form-group">
                            <div class="payment-methods">
                                <label class="payment-method active" data-method="cash">
                                    <input type="radio" name="payment_method" value="cash" checked>
                                    <span class="payment-method-icon"><i class="fas fa-money-bill"></i></span>
                                    <span class="payment-method-text">{{ __('order.cash') }}</span>
                                </label>
                                <label class="payment-method" data-method="transfer">
                                    <input type="radio" name="payment_method" value="transfer">
                                    <span class="payment-method-icon"><i class="fas fa-university"></i></span>
                                    <span class="payment-method-text">{{ __('order.transfer') }}</span>
                                </label>
                                <label class="payment-method" data-method="card">
                                    <input type="radio" name="payment_method" value="card">
                                    <span class="payment-method-icon"><i class="fas fa-credit-card"></i></span>
                                    <span class="payment-method-text">{{ __('order.card') }}</span>
                                </label>
                                <label class="payment-method" data-method="wallet">
                                    <input type="radio" name="payment_method" value="wallet">
                                    <span class="payment-method-icon"><i class="fas fa-wallet"></i></span>
                                    <span class="payment-method-text">{{ __('order.wallet') }}</span>
                                </label>
                            </div>

                            <!-- Bank Account Selection (shown when transfer is selected) -->
                            <div class="bank-account-section" id="bankAccountSection">
                                <div class="bank-transfer-layout">
                                    <!-- QR Code -->
                                    <div class="qr-code-container">
                                        <div class="qr-code-image" id="qrCodeImage">
                                            <i class="fas fa-qrcode" style="font-size: 48px; color: #e4e6ef;"></i>
                                        </div>
                                        <button type="button" class="qr-toggle-btn" id="showQrBtn">
                                            <i class="fas fa-qrcode"></i>
                                            <span>{{ __('order.show_qr_code') }}</span>
                                        </button>
                                    </div>

                                    <!-- Bank Account Dropdown -->
                                    <div class="bank-account-dropdown">
                                        <div class="autocomplete-container">
                                            <div class="bank-account-select" id="bankAccountSelect">
                                                <span class="bank-account-text" id="selectedBankText">{{ __('order.select_bank_account') }}</span>
                                                <i class="fas fa-chevron-down bank-account-dropdown-icon"></i>
                                            </div>
                                            <div class="bank-account-options" id="bankAccountOptions">
                                                @foreach($bankAccounts as $account)
                                                    <div class="bank-account-option {{ $account->is_default ? 'selected' : '' }}"
                                                         data-account-id="{{ $account->id }}"
                                                         data-bank-name="{{ $account->bank_name }}"
                                                         data-account-number="{{ $account->account_number }}"
                                                         data-account-holder="{{ $account->account_holder }}">
                                                        <div class="bank-option-name">{{ $account->bank_name }}</div>
                                                        <div class="bank-option-details">{{ $account->account_number }} - {{ $account->account_holder }}</div>
                                                    </div>
                                                @endforeach
                                            </div>
                                            <input type="hidden" name="bank_account_id" id="bankAccountId" value="{{ $bankAccounts->where('is_default', true)->first()->id ?? '' }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Summary - Sticky Bottom -->
                <div class="order-summary">
                    <div class="summary-row">
                        <span class="fs-5">Tổng tiền hàng</span>
                        <span id="subtotalAmount" class="fs-5 fw-bold">0 ₫</span>
                    </div>
                    <div class="summary-row">
                        <span class="fs-5">Giảm giá</span>
                        <input type="text" id="discountAmount" class="summary-input text-primary fw-bold fs-5"
                               value="0" readonly onclick="openDiscountModal()"
                               style="cursor: pointer; background: transparent; border: none; border-bottom: 2px solid #009ef7; outline: none; text-align: right; width: 120px;">
                    </div>
                    <div class="summary-row">
                        <span class="fs-5">Thu khác</span>
                        <input type="text" id="otherAmount" class="summary-input fw-bold fs-5"
                               value="0" readonly onclick="openOtherChargesModal()"
                               style="cursor: pointer; background: transparent; border: none; border-bottom: 2px solid #6c757d; outline: none; text-align: right; width: 120px;">
                    </div>
                    <div class="summary-row total">
                        <span class="fs-4 text-primary">Khách cần trả</span>
                        <span id="totalAmount" class="fs-3 fw-bold text-primary">0 ₫</span>
                    </div>
                    <div class="summary-row payment">
                        <span class="fs-5">Khách thanh toán</span>
                        <input type="text" id="paidAmount" class="summary-input text-success fw-bold fs-5"
                               value="0"
                               style="background: transparent; border: none; border-bottom: 2px solid #198754; outline: none; text-align: right; width: 120px;">
                    </div>

                    <button type="button" id="createOrderBtn" class="create-order-btn" disabled>
                        <span class="btn-text">THANH TOÁN</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Hidden inputs for order data -->
        <input type="hidden" id="soldBy" value="{{ Auth::id() }}">
        <input type="hidden" id="channel" value="offline">
    </div>

    <!-- Discount Modal -->
    <div class="modal fade" id="discountModal" tabindex="-1" aria-labelledby="discountModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title" id="discountModalLabel">Giảm giá</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-2">
                    <div class="row mb-3">
                        <div class="col-8">
                            <input type="text" id="discountInput" class="form-control form-control-lg border-0 border-bottom border-2 border-primary"
                                   placeholder="0" style="background: transparent; outline: none; text-align: right; font-size: 1.2rem;"
                                   oninput="formatDiscountInput(this)">
                        </div>
                        <div class="col-4 ps-3">
                            <div class="d-flex gap-2">
                                <input type="radio" class="btn-check" name="discountType" id="discountVND" value="VND" checked>
                                <label class="btn btn-primary flex-fill" for="discountVND">VND</label>

                                <input type="radio" class="btn-check" name="discountType" id="discountPercent" value="PERCENT">
                                <label class="btn btn-outline-primary flex-fill" for="discountPercent">%</label>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="form-label text-muted">Khuyến mại:</label>
                            <div id="promotionAmount" class="fw-bold fs-5">0</div>
                        </div>
                        <div class="col-6">
                            <div class="text-end">
                                <div class="text-muted small">Tổng giảm giá:</div>
                                <div id="totalDiscountAmount" class="fw-bold fs-4 text-primary">0</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-primary" onclick="applyDiscount()">Áp dụng</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Other Charges Modal -->
    <div class="modal fade" id="otherChargesModal" tabindex="-1" aria-labelledby="otherChargesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="otherChargesModalLabel">Các khoản thu khác</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Add new charge form -->
                    <div class="row mb-4 p-3 bg-light rounded">
                        <div class="col-3">
                            <input type="text" id="newChargeCode" class="form-control" placeholder="Mã thu khác">
                        </div>
                        <div class="col-4">
                            <input type="text" id="newChargeDescription" class="form-control" placeholder="Loại thu">
                        </div>
                        <div class="col-3">
                            <input type="text" id="newChargeAmount" class="form-control" placeholder="Số tiền" oninput="formatCurrencyInput(this)">
                        </div>
                        <div class="col-2">
                            <button type="button" class="btn btn-primary w-100" onclick="addOtherCharge()">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Charges table -->
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead class="table-primary">
                                <tr>
                                    <th width="5%">
                                        <input type="checkbox" id="selectAllCharges" onchange="toggleAllCharges(this)">
                                    </th>
                                    <th width="20%">Mã thu khác</th>
                                    <th width="35%">Loại thu</th>
                                    <th width="25%">Mức thu</th>
                                    <th width="15%">Thu trên hóa đơn</th>
                                </tr>
                            </thead>
                            <tbody id="otherChargesTableBody">
                                <!-- Sample data -->
                                <tr>
                                    <td><input type="checkbox" class="charge-checkbox" data-amount="4085"></td>
                                    <td>TLTS_846031</td>
                                    <td>Thu lệch vận chuyển</td>
                                    <td class="text-end">4,085</td>
                                    <td class="text-end">0</td>
                                </tr>
                                <tr>
                                    <td><input type="checkbox" class="charge-checkbox" data-amount="20000"></td>
                                    <td>THSP_846031</td>
                                    <td>Trợ giá Shopee</td>
                                    <td class="text-end">20,000</td>
                                    <td class="text-end">0</td>
                                </tr>
                                <tr>
                                    <td><input type="checkbox" class="charge-checkbox" data-amount="32400"></td>
                                    <td>TLSP_846031</td>
                                    <td>Thu lệch vận chuyển</td>
                                    <td class="text-end">32,400</td>
                                    <td class="text-end">0</td>
                                </tr>
                                <tr>
                                    <td><input type="checkbox" class="charge-checkbox" data-amount="10000"></td>
                                    <td>THK000001</td>
                                    <td>Phí ship</td>
                                    <td class="text-end">10,000</td>
                                    <td class="text-end">0</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Total -->
                    <div class="row mt-3">
                        <div class="col-12 text-end">
                            <h5>Tổng thu khác: <span class="text-primary" id="totalOtherCharges">0</span></h5>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-primary" onclick="applyOtherCharges()">Áp dụng</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Customer Info Modal -->
    <div class="modal fade" id="customerInfoModal" tabindex="-1" aria-labelledby="customerInfoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="customerInfoModalLabel">
                        <span id="customerModalName"></span>
                        <span id="customerModalCode" class="text-muted"></span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Customer Stats -->
                    <div class="row mb-4">
                        <div class="col-md-2 text-center">
                            <div class="bg-light rounded p-3">
                                <div class="fw-bold text-danger fs-4" id="customerDebtAmount">0</div>
                                <div class="text-muted small">Nợ</div>
                            </div>
                        </div>
                        <div class="col-md-2 text-center">
                            <div class="bg-light rounded p-3">
                                <div class="fw-bold text-warning fs-4" id="customerPointCount">0</div>
                                <div class="text-muted small">Điểm</div>
                            </div>
                        </div>
                        <div class="col-md-2 text-center">
                            <div class="bg-light rounded p-3">
                                <div class="fw-bold text-success fs-4" id="customerTotalSpent">0</div>
                                <div class="text-muted small">Tổng điểm</div>
                            </div>
                        </div>
                        <div class="col-md-2 text-center">
                            <div class="bg-light rounded p-3">
                                <div class="fw-bold text-info fs-4" id="customerPurchaseCount">0</div>
                                <div class="text-muted small">Số lần mua</div>
                            </div>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="bg-light rounded p-3">
                                <div class="fw-bold text-primary fs-4" id="customerNetSales">0</div>
                                <div class="text-muted small">Tổng bán trừ trả hàng</div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabs Navigation -->
                    <ul class="nav nav-tabs" id="customerInfoTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="customer-info-tab" data-bs-toggle="tab" data-bs-target="#customer-info" type="button" role="tab">
                                Thông tin
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="customer-history-tab" data-bs-toggle="tab" data-bs-target="#customer-history" type="button" role="tab">
                                Lịch sử bán/trả hàng
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="customer-debt-tab" data-bs-toggle="tab" data-bs-target="#customer-debt" type="button" role="tab">
                                Dư nợ
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="customer-points-tab" data-bs-toggle="tab" data-bs-target="#customer-points" type="button" role="tab">
                                Lịch sử điểm
                            </button>
                        </li>
                    </ul>

                    <!-- Tabs Content -->
                    <div class="tab-content mt-3" id="customerInfoTabsContent">

                        <!-- Tab 1: Thông tin -->
                        <div class="tab-pane fade show active" id="customer-info" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Mã khách hàng</label>
                                        <div id="customerModalCustomerCode"></div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Tên khách hàng</label>
                                        <div id="customerModalFullName"></div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Điện thoại</label>
                                        <div id="customerModalPhone"></div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Địa chỉ</label>
                                        <div id="customerModalAddress"></div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Khu vực</label>
                                        <div id="customerModalArea"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Loại khách</label>
                                        <div id="customerModalType"></div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Mã số thuế</label>
                                        <div id="customerModalTaxCode"></div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Email</label>
                                        <div id="customerModalEmail"></div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Facebook</label>
                                        <div id="customerModalFacebook"></div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Nhóm</label>
                                        <div id="customerModalGroup"></div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Ghi chú</label>
                                        <div id="customerModalNotes"></div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Ngày sinh</label>
                                        <div id="customerModalBirthday"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tab 2: Lịch sử bán/trả hàng -->
                        <div class="tab-pane fade" id="customer-history" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead class="table-primary">
                                        <tr>
                                            <th>Mã hóa đơn</th>
                                            <th>Thời gian</th>
                                            <th>Người bán</th>
                                            <th>Tổng cộng</th>
                                            <th>Trạng thái</th>
                                        </tr>
                                    </thead>
                                    <tbody id="customerOrderHistoryTable">
                                        <!-- Data will be loaded here -->
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Tab 3: Dư nợ -->
                        <div class="tab-pane fade" id="customer-debt" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead class="table-warning">
                                        <tr>
                                            <th>Mã hóa đơn</th>
                                            <th>Thời gian</th>
                                            <th>Người bán</th>
                                            <th>Tổng cộng</th>
                                            <th>Đã trả</th>
                                            <th>Còn nợ</th>
                                        </tr>
                                    </thead>
                                    <tbody id="customerDebtTable">
                                        <!-- Data will be loaded here -->
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Tab 4: Lịch sử điểm -->
                        <div class="tab-pane fade" id="customer-points" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead class="table-success">
                                        <tr>
                                            <th>Thời gian</th>
                                            <th>Loại giao dịch</th>
                                            <th>Điểm</th>
                                            <th>Ghi chú</th>
                                            <th>Số dư</th>
                                        </tr>
                                    </thead>
                                    <tbody id="customerPointsTable">
                                        <!-- Data will be loaded here -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Global Stylesheets Bundle -->
    <script src="{{ asset('admin-assets/assets/plugins/global/plugins.bundle.js') }}"></script>
    <script src="{{ asset('admin-assets/assets/js/scripts.bundle.js') }}"></script>

    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script>
        let orderTabs = [];
        let activeTabId = null;
        let tabCounter = 0;

        // Data from server
        const bankAccounts = @json($bankAccounts);
        let customers = @json($customers);
        let branchShops = @json($branchShops);
        let defaultBranchShop = @json($defaultBranchShop ?? null);
        let searchTimeout = null;
        let currentSearchRequest = null;
        let selectedSeller = @json(Auth::user());
        let selectedChannel = 'offline';
        let allUsers = []; // Will be populated if user is admin
        let orderCreatedTime = null; // Time when order was created
        let autoSaveInterval;

        // Helper functions
        function showNotification(type, message) {
            if (type === 'success') {
                toastr.success(message);
            } else if (type === 'error') {
                toastr.error(message);
            } else if (type === 'warning') {
                toastr.warning(message);
            } else {
                toastr.info(message);
            }
        }

        function formatCurrency(amount) {
            return new Intl.NumberFormat('vi-VN', {
                style: 'currency',
                currency: 'VND'
            }).format(amount);
        }

        // Load drafts function
        function loadDrafts() {
            try {
                const savedDrafts = localStorage.getItem('quickOrderDrafts');
                if (savedDrafts) {
                    const drafts = JSON.parse(savedDrafts);
                    if (drafts && drafts.length > 0) {
                        orderTabs = drafts;
                        tabCounter = Math.max(...orderTabs.map(tab => parseInt(tab.id.replace('tab_', '')))) + 1;

                        // Recreate tabs
                        orderTabs.forEach(function(tabData) {
                            createTabFromData(tabData);
                        });

                        // Activate first tab
                        if (orderTabs.length > 0) {
                            switchTab(orderTabs[0].id);
                        }
                    }
                }
            } catch (e) {
                console.error('Failed to load drafts:', e);
                orderTabs = [];
            }
        }

        function createTabFromData(tabData) {
            const tabId = tabData.id;

            // Create tab button
            const tabButton = $(`
                <div class="order-tab" data-tab-id="${tabId}" id="${tabId}_tab">
                    <span class="tab-title">${tabData.name}</span>
                    <span class="tab-count">(${tabData.items.length})</span>
                    <button type="button" class="tab-close" onclick="closeTab('${tabId}')">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `);

            $('#orderTabsContainer').append(tabButton);

            // Create tab content
            let tabContent = $('#orderTabTemplate').clone();
            tabContent.attr('id', tabId + '_content');
            tabContent.attr('data-tab-id', tabId);
            tabContent.addClass('h-100');
            tabContent.show();

            // Replace TAB_ID placeholders with actual tab ID using string replacement
            let htmlContent = tabContent.html();
            htmlContent = htmlContent.replace(/TAB_ID_/g, tabId + '_');
            tabContent.html(htmlContent);

            // Add tab content to container
            $('#orderTabsContent').append(tabContent);

            // Bind events for this tab
            bindTabEvents(tabId);

            // Restore tab data
            restoreTabData(tabId, tabData);
        }

        function restoreTabData(tabId, tabData) {
            let tabContent = $(`#${tabId}_content`);

            // Restore customer
            if (tabData.customer_search && tabData.customer_id) {
                // Hide search input and show selected customer display
                const searchContainer = tabContent.find('#customerSearchContainer');
                const selectedDisplay = tabContent.find('#selectedCustomerDisplay');
                const selectedText = tabContent.find('#selectedCustomerText');

                // Set formatted customer display
                const customerName = tabData.customer_search;
                const customerPhone = tabData.customer_phone || '';
                selectedText.html(`<strong class="text-primary" style="color: #009ef7 !important; text-decoration: underline; font-size: 1.2rem; cursor: pointer; font-weight: 700;" onclick="showCustomerInfoModal(${tabData.customer_id})">${customerName} <span style="color: #009ef7 !important; font-weight: 700; font-size: 1.1rem;">${customerPhone}</span></strong>`);
                selectedText.attr('data-customer-id', tabData.customer_id);

                // Show selected display, hide search
                searchContainer.hide();
                selectedDisplay.show();

                // Set hidden input
                tabContent.find('#customerSelect').val(tabData.customer_id);
            } else {
                // Show search input if no customer selected
                tabContent.find('#customerSearchContainer').show();
                tabContent.find('#selectedCustomerDisplay').hide();
            }

            // Restore branch shop
            if (tabData.branch_shop_search) {
                tabContent.find('#branchShopSearch').val(tabData.branch_shop_search);
                tabContent.find('#branchShopSelect').val(tabData.branch_shop_id);
            } else if (defaultBranchShop) {
                // Set default branch shop if no saved data
                tabContent.find('#branchShopSearch').val(defaultBranchShop.name);
                tabContent.find('#branchShopSelect').val(defaultBranchShop.id);

                // Update tab data
                const tab = orderTabs.find(t => t.id === tabId);
                if (tab) {
                    tab.branch_shop_id = defaultBranchShop.id;
                    tab.branch_shop_search = defaultBranchShop.name;
                }
            }

            // Restore payment method
            if (tabData.payment_method) {
                tabContent.find(`[data-method="${tabData.payment_method}"]`).addClass('active').siblings().removeClass('active');
                tabContent.find(`input[name="payment_method"][value="${tabData.payment_method}"]`).prop('checked', true);
            } else {
                // Set default payment method (cash)
                tabContent.find('[data-method="cash"]').addClass('active');
                tabContent.find('input[name="payment_method"][value="cash"]').prop('checked', true);

                // Update tab data
                const tab = orderTabs.find(t => t.id === tabId);
                if (tab) {
                    tab.payment_method = 'cash';
                }
            }

            // Show/hide bank account section
            if (tabData.payment_method === 'transfer') {
                tabContent.find('#bankAccountSection').addClass('show');
                if (tabData.bank_account_id) {
                    // Update dropdown selection
                    const selectedOption = tabContent.find(`[data-account-id="${tabData.bank_account_id}"]`);
                    if (selectedOption.length) {
                        const bankName = selectedOption.data('bank-name');
                        const accountNumber = selectedOption.data('account-number');
                        const accountHolder = selectedOption.data('account-holder');

                        tabContent.find('.bank-account-option').removeClass('selected');
                        selectedOption.addClass('selected');
                        tabContent.find('#selectedBankText').text(`${bankName} - ${accountNumber} - ${accountHolder}`);
                        tabContent.find('#bankAccountId').val(tabData.bank_account_id);
                    }
                } else {
                    // Set default bank account if transfer method but no account selected
                    const defaultAccount = bankAccounts.find(acc => acc.is_default);
                    if (defaultAccount) {
                        const selectedOption = tabContent.find(`[data-account-id="${defaultAccount.id}"]`);
                        if (selectedOption.length) {
                            selectedOption.addClass('selected');
                            tabContent.find('#selectedBankText').text(`${defaultAccount.bank_name} - ${defaultAccount.account_number} - ${defaultAccount.account_holder}`);
                            tabContent.find('#bankAccountId').val(defaultAccount.id);

                            // Update tab data
                            const tab = orderTabs.find(t => t.id === tabId);
                            if (tab) {
                                tab.bank_account_id = defaultAccount.id;
                            }
                        }
                    }
                }
            }

            // Restore notes
            if (tabData.notes) {
                tabContent.find('#orderNotes').val(tabData.notes);
            }

            // Restore items
            updateTabUI(tabId);
        }

        // Autocomplete functions
        function setupAutocomplete(tabId) {
            let tabContent = $(`#${tabId}_content`);

            // Customer autocomplete
            setupCustomerAutocomplete(tabContent);

            // Branch shop autocomplete
            setupBranchShopAutocomplete(tabContent);

            // Paid amount input
            setupPaidAmountInput(tabContent);
        }

        function setupCustomerAutocomplete(tabContent) {
            const searchInput = tabContent.find('#customerSearch');
            const hiddenInput = tabContent.find('#customerSelect');
            const suggestionsContainer = tabContent.find('#customerSuggestions');
            const searchContainer = tabContent.find('#customerSearchContainer');
            const selectedDisplay = tabContent.find('#selectedCustomerDisplay');
            const selectedText = tabContent.find('#selectedCustomerText');
            const removeBtn = tabContent.find('#removeCustomerBtn');

            searchInput.on('input', function() {
                const query = $(this).val().toLowerCase();
                if (query.length < 1) {
                    suggestionsContainer.removeClass('show');
                    return;
                }

                const filteredCustomers = customers.filter(customer =>
                    customer.name.toLowerCase().includes(query) ||
                    customer.phone.includes(query) ||
                    (customer.email && customer.email.toLowerCase().includes(query)) ||
                    (customer.customer_code && customer.customer_code.toLowerCase().includes(query))
                );

                if (filteredCustomers.length > 0) {
                    let html = '';
                    filteredCustomers.forEach(customer => {
                        html += `
                            <div class="autocomplete-suggestion" data-id="${customer.id}" data-name="${customer.name}" data-phone="${customer.phone}" data-code="${customer.customer_code || ''}">
                                <div class="suggestion-name">${customer.name}</div>
                                <div class="suggestion-details">${customer.customer_code || 'N/A'} - ${customer.phone}</div>
                            </div>
                        `;
                    });
                    suggestionsContainer.html(html).addClass('show');
                } else {
                    suggestionsContainer.removeClass('show');
                }
            });

            // Handle suggestion click
            suggestionsContainer.on('click', '.autocomplete-suggestion', function() {
                const customerId = $(this).data('id');
                const customerName = $(this).data('name');
                const customerPhone = $(this).data('phone');

                // Show selected customer display with formatted style
                selectedText.html(`<strong class="text-primary" style="color: #009ef7 !important; text-decoration: underline; font-size: 1.2rem; cursor: pointer; font-weight: 700;" onclick="showCustomerInfoModal(${customerId})">${customerName} <span style="color: #009ef7 !important; font-weight: 700; font-size: 1.1rem;">${customerPhone}</span></strong>`);
                selectedText.attr('data-customer-id', customerId);
                searchContainer.hide();
                selectedDisplay.show();
                hiddenInput.val(customerId);
                suggestionsContainer.removeClass('show');

                // Update tab data
                const tabId = tabContent.attr('data-tab-id');
                updateTabCustomer(tabId, customerId, customerName, customerPhone);
            });

            // Handle remove customer
            removeBtn.on('click', function() {
                // Show search input again
                searchContainer.show();
                selectedDisplay.hide();
                searchInput.val('');
                hiddenInput.val('');
                selectedText.attr('data-customer-id', '');

                // Update tab data
                const tabId = tabContent.attr('data-tab-id');
                updateTabCustomer(tabId, null, '');
            });

            // Handle customer name click to show info modal
            selectedText.on('click', function() {
                const customerId = $(this).attr('data-customer-id');
                if (customerId) {
                    showCustomerInfoModal(customerId);
                }
            });

            // Hide suggestions when clicking outside
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.autocomplete-container').length) {
                    suggestionsContainer.removeClass('show');
                }
            });
        }

        function setupBranchShopAutocomplete(tabContent) {
            const searchInput = tabContent.find('#branchShopSearch');
            const hiddenInput = tabContent.find('#branchShopSelect');
            const suggestionsContainer = tabContent.find('#branchShopSuggestions');

            searchInput.on('input', function() {
                const query = $(this).val().toLowerCase();
                if (query.length < 1) {
                    suggestionsContainer.removeClass('show');
                    return;
                }

                const filteredShops = branchShops.filter(shop =>
                    shop.name.toLowerCase().includes(query) ||
                    (shop.address && shop.address.toLowerCase().includes(query))
                );

                if (filteredShops.length > 0) {
                    let html = '';
                    filteredShops.forEach(shop => {
                        html += `
                            <div class="autocomplete-suggestion" data-id="${shop.id}" data-name="${shop.name}">
                                <div class="suggestion-name">${shop.name}</div>
                                <div class="suggestion-details">${shop.address || ''}</div>
                            </div>
                        `;
                    });
                    suggestionsContainer.html(html).addClass('show');
                } else {
                    suggestionsContainer.removeClass('show');
                }
            });

            // Handle suggestion click
            suggestionsContainer.on('click', '.autocomplete-suggestion', function() {
                const shopId = $(this).data('id');
                const shopName = $(this).data('name');

                searchInput.val(shopName);
                hiddenInput.val(shopId);
                suggestionsContainer.removeClass('show');

                // Update tab data
                const tabId = tabContent.attr('data-tab-id');
                updateTabBranchShop(tabId, shopId, shopName);
            });

            // Hide suggestions when clicking outside
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.autocomplete-container').length) {
                    suggestionsContainer.removeClass('show');
                }
            });
        }

        // Setup default values for new tab
        function setupTabDefaults(tabId) {
            let tabContent = $(`#${tabId}_content`);

            // Set default branch shop
            if (defaultBranchShop) {
                tabContent.find('#branchShopSearch').val(defaultBranchShop.name);
                tabContent.find('#branchShopSelect').val(defaultBranchShop.id);

                // Update tab data
                const tab = orderTabs.find(t => t.id === tabId);
                if (tab) {
                    tab.branch_shop_id = defaultBranchShop.id;
                    tab.branch_shop_search = defaultBranchShop.name;
                }
            }
        }

        // Auto-save functions
        function setupAutoSave() {
            // Auto-save every 5 seconds
            autoSaveInterval = setInterval(function() {
                saveDrafts();
            }, 5000);

            // Save before page unload
            $(window).on('beforeunload', function() {
                saveDrafts();
            });
        }

        function saveDrafts() {
            try {
                localStorage.setItem('quickOrderDrafts', JSON.stringify(orderTabs));
            } catch (e) {
                console.error('Failed to save drafts:', e);
            }
        }

        // Update tab data functions
        function updateTabCustomer(tabId, customerId, customerName, customerPhone = '') {
            const tab = orderTabs.find(t => t.id === tabId);
            if (tab) {
                tab.customer_id = customerId;
                tab.customer_search = customerName;
                tab.customer_phone = customerPhone;
                saveDrafts();
            }
        }

        function updateTabBranchShop(tabId, shopId, shopName) {
            const tab = orderTabs.find(t => t.id === tabId);
            if (tab) {
                tab.branch_shop_id = shopId;
                tab.branch_shop_search = shopName;
                saveDrafts();
            }
        }

        function updateTabPaymentMethod(tabId, method) {
            const tab = orderTabs.find(t => t.id === tabId);
            if (tab) {
                tab.payment_method = method;
                if (method !== 'transfer') {
                    tab.bank_account_id = '';
                }
                saveDrafts();
            }
        }

        function updateTabBankAccount(tabId, accountId) {
            const tab = orderTabs.find(t => t.id === tabId);
            if (tab) {
                tab.bank_account_id = accountId;
                saveDrafts();
            }
        }

        function updateTabNotes(tabId, notes) {
            const tab = orderTabs.find(t => t.id === tabId);
            if (tab) {
                tab.notes = notes;
                saveDrafts();
            }
        }

        // QR Code functions
        function toggleQRCode(tabContent, accountId) {
            const qrBtn = tabContent.find('#showQrBtn');

            // Find account data
            const account = bankAccounts.find(acc => acc.id == accountId);
            if (!account) return;

            // Always show modal when clicking the button
            showQRCodeModal(tabContent, account);
        }

        function initializeDefaultQR(tabContent) {
            // Find default account or first account
            const defaultAccount = bankAccounts.find(acc => acc.is_default) || bankAccounts[0];
            if (!defaultAccount) return;

            // Calculate total amount from current tab
            const tabId = tabContent.attr('data-tab-id');
            const tab = orderTabs.find(t => t.id === tabId);
            const totalAmount = tab ? tab.items.reduce((sum, item) => sum + (item.price * item.quantity), 0) : 0;

            // Generate small QR URL
            const bankCode = defaultAccount.bank_code || 'vietinbank';
            const accountNumber = defaultAccount.account_number;
            const accountName = encodeURIComponent(defaultAccount.account_holder);

            const qrSmallUrl = `https://img.vietqr.io/image/${bankCode}-${accountNumber}-qr_only.jpg?amount=${totalAmount}&accountName=${accountName}`;

            // Show small QR in the interface
            const qrImage = tabContent.find('#qrCodeImage');
            qrImage.html(`<img src="${qrSmallUrl}" alt="QR Code" style="cursor: pointer; width: 100%; height: 100%; object-fit: contain;" onclick="showQRCodeModal($(this).closest('[data-tab-id]'), ${JSON.stringify(defaultAccount).replace(/"/g, '&quot;')})">`);
        }

        function showQRCodeModal(tabContent, account) {
            // Calculate total amount from current tab
            const tabId = tabContent.attr('data-tab-id');
            const tab = orderTabs.find(t => t.id === tabId);
            const totalAmount = tab ? tab.items.reduce((sum, item) => sum + (item.price * item.quantity), 0) : 0;

            // Generate VietQR URLs
            const bankCode = account.bank_code || 'vietinbank';
            const accountNumber = account.account_number;
            const accountName = encodeURIComponent(account.account_holder);

            const qrLargeUrl = `https://img.vietqr.io/image/${bankCode}-${accountNumber}-compact2.jpg?amount=${totalAmount}&accountName=${accountName}`;

            // Open modal directly
            openQRModal(qrLargeUrl, account.bank_name, accountNumber, account.account_holder, totalAmount);
        }

        function updateQRCode(tabContent) {
            // Update QR code when order changes
            const tabId = tabContent.attr('data-tab-id');
            const tab = orderTabs.find(t => t.id === tabId);

            // Get selected account or default account
            const selectedAccountId = tabContent.find('#bankAccountId').val();
            const account = bankAccounts.find(acc => acc.id == selectedAccountId) ||
                          bankAccounts.find(acc => acc.is_default) ||
                          bankAccounts[0];

            if (!account) return;

            const totalAmount = tab ? tab.items.reduce((sum, item) => sum + (item.price * item.quantity), 0) : 0;

            // Generate small QR URL
            const bankCode = account.bank_code || 'vietinbank';
            const accountNumber = account.account_number;
            const accountName = encodeURIComponent(account.account_holder);

            const qrSmallUrl = `https://img.vietqr.io/image/${bankCode}-${accountNumber}-qr_only.jpg?amount=${totalAmount}&accountName=${accountName}`;

            // Update small QR in the interface
            const qrImage = tabContent.find('#qrCodeImage');
            qrImage.html(`<img src="${qrSmallUrl}" alt="QR Code" style="cursor: pointer; width: 100%; height: 100%; object-fit: contain;" onclick="showQRCodeModal($(this).closest('[data-tab-id]'), ${JSON.stringify(account).replace(/"/g, '&quot;')})">`);
        }

        function openQRModal(qrUrl, bankName, accountNumber, accountHolder, amount) {
            const modalHtml = `
                <div class="modal fade" id="qrCodeModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">
                                    <i class="fas fa-qrcode me-2"></i>
                                    Mã QR Thanh Toán
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body text-center">
                                <div class="qr-info mb-3">
                                    <h6 class="fw-bold">${bankName}</h6>
                                    <p class="mb-1">STK: ${accountNumber}</p>
                                    <p class="mb-1">Tên: ${accountHolder}</p>
                                    <p class="mb-3 text-primary fw-bold fs-5">${formatCurrency(amount)}</p>
                                </div>
                                <div class="qr-image-container">
                                    <img src="${qrUrl}" alt="QR Code" class="img-fluid" style="max-width: 300px;">
                                </div>
                                <p class="text-muted mt-3 small">
                                    Quét mã QR bằng ứng dụng ngân hàng để thanh toán
                                </p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            // Remove existing modal if any
            $('#qrCodeModal').remove();

            // Add modal to body and show
            $('body').append(modalHtml);
            $('#qrCodeModal').modal('show');

            // Remove modal from DOM when hidden
            $('#qrCodeModal').on('hidden.bs.modal', function() {
                $(this).remove();
            });
        }

        // Initialize
        $(document).ready(function() {
            // Configure toastr
            toastr.options = {
                "closeButton": true,
                "debug": false,
                "newestOnTop": true,
                "progressBar": true,
                "positionClass": "toast-top-right",
                "preventDuplicates": false,
                "onclick": null,
                "showDuration": "300",
                "hideDuration": "1000",
                "timeOut": "5000",
                "extendedTimeOut": "1000",
                "showEasing": "swing",
                "hideEasing": "linear",
                "showMethod": "fadeIn",
                "hideMethod": "fadeOut"
            };

            // Load saved drafts
            loadDrafts();

            // Create first tab if no drafts
            if (orderTabs.length === 0) {
                addNewTab();
            }

            // Emergency fix: Clear localStorage if too many tabs
            if (orderTabs.length > 10) {
                console.warn('Too many tabs detected, clearing localStorage');
                localStorage.removeItem('quickOrderDrafts');
                orderTabs = [];
                $('#orderTabsContainer').empty();
                $('#orderTabsContent').empty();
                addNewTab();
            }

            // Setup auto-save
            setupAutoSave();

            // Initialize header dropdowns
            initializeHeaderDropdowns();

            // Update time displays
            updateCurrentTime();
            updateOrderCreatedTime();

            // Update current time every second
            setInterval(updateCurrentTime, 1000);

            // F3 key to focus barcode input
            $(document).on('keydown', function(e) {
                if (e.key === 'F3') {
                    e.preventDefault();
                    $('#globalBarcodeInput').focus();
                }
            });

            // Add new tab button
            $('#addNewOrderTabBtn').on('click', function() {
                addNewTab();
            });

            // Global product search input
            $('#globalBarcodeInput').on('keypress', function(e) {
                if (e.which === 13) { // Enter key
                    const suggestions = $('#productSuggestions');
                    const firstSuggestion = suggestions.find('.product-suggestion').first();
                    if (firstSuggestion.length && suggestions.hasClass('show')) {
                        // Select first suggestion if dropdown is open
                        firstSuggestion.click();
                    } else {
                        // If no suggestions, try exact search
                        const query = $(this).val().trim();
                        if (query.length > 0) {
                            searchProductExact(query);
                        }
                    }
                }
            });

            // Product search with suggestions (with debounce)
            $('#globalBarcodeInput').on('input', function() {
                const query = $(this).val().trim();

                // Clear previous timeout
                if (searchTimeout) {
                    clearTimeout(searchTimeout);
                }

                // Cancel previous request if still pending
                if (currentSearchRequest) {
                    currentSearchRequest.abort();
                    currentSearchRequest = null;
                }

                if (query.length >= 2) {
                    // Set new timeout for debounce (300ms delay)
                    searchTimeout = setTimeout(function() {
                        searchProductSuggestions(query);
                    }, 300);
                } else {
                    $('#productSuggestions').removeClass('show');
                }
            });

            // Hide suggestions when clicking outside
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.barcode-input-container').length) {
                    $('#productSuggestions').removeClass('show');
                }
            });

            // Auto focus on barcode input
            $('#globalBarcodeInput').focus();

            // Add default order tab (commented out - already created above)
            // addNewTab('order');
        });

        function addNewTab(type = 'order') {
            tabCounter++;
            const tabId = 'tab_' + tabCounter;
            const tabName = type === 'invoice' ? 'Hóa đơn ' + tabCounter : '{{ __("order.order") }} ' + tabCounter;

            // Create tab data
            const tabData = {
                id: tabId,
                name: tabName,
                type: type, // 'order' or 'invoice'
                number: tabCounter,
                items: [],
                customer_id: '',
                customer_search: '',
                branch_shop_id: defaultBranchShop ? defaultBranchShop.id : '',
                branch_shop_search: defaultBranchShop ? defaultBranchShop.name : '',
                payment_method: 'cash',
                bank_account_id: '',
                channel: 'offline',
                notes: ''
            };

            orderTabs.push(tabData);

            // Create tab element
            const tabIcon = type === 'invoice' ? '<i class="fas fa-file-invoice me-1"></i>' : '<i class="fas fa-shopping-cart me-1"></i>';
            const tabElement = $(`
                <div class="order-tab" data-tab-id="${tabId}" data-tab-type="${type}">
                    ${tabIcon}
                    <span class="tab-title">${tabName}</span>
                    <span class="tab-count">0</span>
                    <button type="button" class="tab-close" onclick="closeTab('${tabId}')">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `);

            // Add tab to container
            $('#orderTabsContainer').append(tabElement);

            // Create tab content
            let tabContent = $('#orderTabTemplate').clone();
            tabContent.attr('id', tabId + '_content');
            tabContent.attr('data-tab-id', tabId);
            tabContent.addClass('h-100');
            tabContent.show();

            // Replace TAB_ID placeholders with actual tab ID using string replacement
            let htmlContent = tabContent.html();
            htmlContent = htmlContent.replace(/TAB_ID_/g, tabId + '_');
            tabContent.html(htmlContent);

            // Add tab content to container
            $('#orderTabsContent').append(tabContent);

            // Switch to new tab
            switchTab(tabId);

            // Bind events for this tab
            bindTabEvents(tabId);

            // Setup default values
            setupTabDefaults(tabId);

            // Initialize default QR code
            initializeDefaultQR($(`#${tabId}_content`));
        }

        function switchTab(tabId) {
            // Update active tab
            activeTabId = tabId;

            // Update tab appearance
            $('.order-tab').removeClass('active');
            $(`.order-tab[data-tab-id="${tabId}"]`).addClass('active');

            // Update content visibility
            $('#orderTabsContent > div').hide();
            $(`#${tabId}_content`).show();

            // Restore customer display for this tab
            restoreCustomerDisplay(tabId);

            // Update order summary
            updateOrderSummary(tabId);

            // Update button text based on tab type
            updateCreateButtonText(tabId);
        }

        // Update create button text based on tab type
        function updateCreateButtonText(tabId) {
            const tab = orderTabs.find(t => t.id === tabId);
            if (!tab) return;

            const buttonText = tab.type === 'invoice' ? 'THANH TOÁN' : 'ĐẶT HÀNG';
            const tabContent = $(`#${tabId}_content`);
            tabContent.find('#createOrderBtn .btn-text').text(buttonText);
        }

        // Restore customer display when switching tabs
        function restoreCustomerDisplay(tabId) {
            const tab = orderTabs.find(t => t.id === tabId);
            if (!tab) return;

            const tabContent = $(`#${tabId}_content`);
            const searchContainer = tabContent.find('#customerSearchContainer');
            const selectedDisplay = tabContent.find('#selectedCustomerDisplay');
            const selectedText = tabContent.find('#selectedCustomerText');

            if (tab.customer_search && tab.customer_id) {
                // Show formatted customer display
                const customerName = tab.customer_search;
                const customerPhone = tab.customer_phone || '';
                selectedText.html(`<strong class="text-primary" style="color: #009ef7 !important; text-decoration: underline; font-size: 1.2rem; cursor: pointer; font-weight: 700;" onclick="showCustomerInfoModal(${tab.customer_id})">${customerName} <span style="color: #009ef7 !important; font-weight: 700; font-size: 1.1rem;">${customerPhone}</span></strong>`);
                selectedText.attr('data-customer-id', tab.customer_id);

                searchContainer.hide();
                selectedDisplay.show();
                tabContent.find('#customerSelect').val(tab.customer_id);
            } else {
                // Show search input
                searchContainer.show();
                selectedDisplay.hide();
                tabContent.find('#customerSearch').val('');
                tabContent.find('#customerSelect').val('');
            }
        }

        function closeTab(tabId) {
            const tab = orderTabs.find(t => t.id === tabId);
            if (!tab) return;

            // Check if tab has items
            if (tab.items && tab.items.length > 0) {
                // Show modal confirmation for tabs with items
                const tabName = tab.type === 'invoice' ? `Hóa đơn ${tab.number}` : `Đặt hàng ${tab.number}`;
                $('#closeTabName').text(tabName);
                $('#closeTabNameInBody').text(tabName);

                // Store tabId for confirmation
                $('#confirmCloseTabBtn').off('click').on('click', function() {
                    $('#confirmCloseTabModal').modal('hide');
                    performCloseTab(tabId);
                });

                $('#confirmCloseTabModal').modal('show');
                return;
            }

            // If no items, close directly
            performCloseTab(tabId);
        }

        function performCloseTab(tabId) {
            // If it's the only tab, create new invoice tab instead of closing
            if (orderTabs.length <= 1) {
                // Clear the current tab and reset to invoice
                const tab = orderTabs.find(t => t.id === tabId);
                if (tab) {
                    tab.items = [];
                    tab.customer_id = '';
                    tab.customer_search = '';
                    tab.notes = '';
                    tab.type = 'invoice';
                    tab.number = 1;
                    tab.name = 'Hóa đơn 1';

                    // Reset tab counter
                    tabCounter = 1;

                    // Update tab display
                    const tabElement = $(`.order-tab[data-tab-id="${tabId}"]`);
                    tabElement.attr('data-tab-type', 'invoice');
                    tabElement.find('.tab-title').text('Hóa đơn 1');
                    tabElement.find('i').removeClass('fas fa-shopping-cart').addClass('fas fa-file-invoice');

                    updateTabUI(tabId);
                    restoreCustomerDisplay(tabId);
                    saveDrafts();
                }
                return;
            }

            // Remove from array
            orderTabs = orderTabs.filter(tab => tab.id !== tabId);

            // Remove elements
            $(`.order-tab[data-tab-id="${tabId}"]`).remove();
            $(`#${tabId}_content`).remove();

            // Save drafts after deletion
            saveDrafts();

            // Switch to first available tab if current was closed
            if (activeTabId === tabId) {
                switchTab(orderTabs[0].id);
            }
        }

        function bindTabEvents(tabId) {
            let tabContent = $(`#${tabId}_content`);

            // Tab click
            $(`.order-tab[data-tab-id="${tabId}"]`).on('click', function() {
                switchTab(tabId);
            });

            // Setup autocomplete
            setupAutocomplete(tabId);

            // Payment method change
            tabContent.on('click', '.payment-method', function() {
                const method = $(this).data('method');

                // Update UI
                tabContent.find('.payment-method').removeClass('active');
                $(this).addClass('active');
                tabContent.find(`input[name="payment_method"][value="${method}"]`).prop('checked', true);

                // Show/hide bank account section
                if (method === 'transfer') {
                    tabContent.find('#bankAccountSection').addClass('show');
                } else {
                    tabContent.find('#bankAccountSection').removeClass('show');
                }

                // Update tab data
                updateTabPaymentMethod(tabId, method);
            });

            // Bank account dropdown
            tabContent.on('click', '#bankAccountSelect', function() {
                const dropdown = tabContent.find('#bankAccountOptions');
                const select = $(this);

                if (dropdown.hasClass('show')) {
                    dropdown.removeClass('show');
                    select.removeClass('active');
                } else {
                    dropdown.addClass('show');
                    select.addClass('active');
                }
            });

            // Bank account option selection
            tabContent.on('click', '.bank-account-option', function() {
                const accountId = $(this).data('account-id');
                const bankName = $(this).data('bank-name');
                const accountNumber = $(this).data('account-number');
                const accountHolder = $(this).data('account-holder');

                // Update UI
                tabContent.find('.bank-account-option').removeClass('selected');
                $(this).addClass('selected');
                tabContent.find('#selectedBankText').text(`${bankName} - ${accountNumber} - ${accountHolder}`);
                tabContent.find('#bankAccountId').val(accountId);
                tabContent.find('#bankAccountOptions').removeClass('show');
                tabContent.find('#bankAccountSelect').removeClass('active');

                // Update tab data
                updateTabBankAccount(tabId, accountId);

                // Update QR code with new account
                updateQRCode(tabContent);
            });

            // QR Code toggle
            tabContent.on('click', '#showQrBtn', function() {
                const accountId = tabContent.find('#bankAccountId').val();
                if (accountId) {
                    toggleQRCode(tabContent, accountId);
                } else {
                    toastr.warning('Vui lòng chọn tài khoản ngân hàng trước');
                }
            });

            // Notes change
            tabContent.on('input', '#orderNotes', function() {
                const notes = $(this).val();
                updateTabNotes(tabId, notes);
            });

            // Create order/invoice button
            tabContent.find('#createOrderBtn').on('click', function() {
                createOrderOrInvoice(tabId);
            });

            // Summary input events
            tabContent.find('#paidAmount').on('input', function() {
                formatCurrencyInput(this);
                markPaidAmountAsUserEdited();
                updateOrderTotals();
            });

            tabContent.find('#discountAmount').on('input', function() {
                formatCurrencyInput(this);
                updateOrderTotals();
            });

            tabContent.find('#otherAmount').on('input', function() {
                formatCurrencyInput(this);
                updateOrderTotals();
            });
        }

        function searchProductSuggestions(query) {
            // Store the current request so we can cancel it if needed
            currentSearchRequest = $.ajax({
                url: '{{ route("admin.quick-order.search-product") }}',
                method: 'POST',
                data: {
                    query: query,
                    limit: 10,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    currentSearchRequest = null; // Clear the request reference

                    console.log('Product search response:', response);

                    if (response.success && response.data && response.data.length > 0) {
                        let html = '';
                        response.data.forEach(product => {
                            // Determine stock status
                            const stock = product.stock_quantity || 0;
                            let stockClass = 'product-suggestion-stock';
                            let stockText = `Tồn kho: ${stock}`;

                            if (stock <= 0) {
                                stockClass += ' out';
                                stockText = 'Hết hàng';
                            } else if (stock <= 10) {
                                stockClass += ' low';
                                stockText = `Còn ${stock} sản phẩm`;
                            } else {
                                stockText = `Tồn kho: ${stock}`;
                            }

                            html += `
                                <div class="product-suggestion" data-product-id="${product.id}">
                                    <img src="${product.image || '/admin/media/svg/files/blank-image.svg'}"
                                         class="product-suggestion-image" alt="">
                                    <div class="product-suggestion-info">
                                        <div class="product-suggestion-name">${product.name}</div>
                                        <div class="product-suggestion-details">
                                            <span>SKU: ${product.sku || 'N/A'}</span>
                                            ${product.barcode ? `<span>Barcode: ${product.barcode}</span>` : ''}
                                        </div>
                                        <div class="${stockClass}">${stockText}</div>
                                    </div>
                                    <div class="product-suggestion-price-container">
                                        <div class="product-suggestion-price">${formatCurrency(product.price)}</div>
                                    </div>
                                </div>
                            `;
                        });

                        $('#productSuggestions').html(html).addClass('show');

                        // Handle suggestion click
                        $('.product-suggestion').on('click', function() {
                            const productId = $(this).data('product-id');
                            const product = response.data.find(p => p.id == productId);
                            if (product && activeTabId) {
                                addProductToTab(activeTabId, product);
                                $('#globalBarcodeInput').val('');
                                $('#productSuggestions').removeClass('show');
                            }
                        });
                    } else {
                        console.log('No products found or empty response');
                        $('#productSuggestions').removeClass('show');
                    }
                },
                error: function(xhr) {
                    currentSearchRequest = null; // Clear the request reference

                    console.error('Product search error:', xhr.status, xhr.responseText);

                    // Only hide suggestions if it's not an aborted request
                    if (xhr.statusText !== 'abort') {
                        $('#productSuggestions').removeClass('show');
                        console.error('Product search error:', xhr);
                    }
                }
            });
        }

        function searchProductExact(query) {
            if (!query) return;

            // Hide suggestions
            $('#productSuggestions').removeClass('show');

            // Search product via API (try exact match first)
            $.ajax({
                url: '{{ route("admin.quick-order.search-product") }}',
                method: 'POST',
                data: {
                    barcode: query, // Try as barcode first
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success && response.data && activeTabId) {
                        addProductToTab(activeTabId, response.data);
                        $('#globalBarcodeInput').val('');
                    } else {
                        // If barcode search fails, try query search
                        searchProductByQuery(query);
                    }
                },
                error: function(xhr) {
                    // If barcode search fails, try query search
                    searchProductByQuery(query);
                }
            });
        }

        function searchProductByQuery(query) {
            $.ajax({
                url: '{{ route("admin.quick-order.search-product") }}',
                method: 'POST',
                data: {
                    query: query,
                    limit: 1, // Only get first result
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success && response.data && response.data.length > 0 && activeTabId) {
                        const product = response.data[0];
                        addProductToTab(activeTabId, product);
                        $('#globalBarcodeInput').val('');
                    } else {
                        showNotification('error', 'Không tìm thấy sản phẩm');
                    }
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    showNotification('error', response?.message || 'Lỗi khi tìm kiếm sản phẩm');
                }
            });
        }

        function addProductToTab(tabId, product) {
            // Find tab data
            const tab = orderTabs.find(t => t.id === tabId);
            if (!tab) return;

            // Set order creation time if this is the first item
            if (tab.items.length === 0) {
                setOrderCreatedTime();
            }

            // Check if product already exists
            const existingItem = tab.items.find(item => item.sku === product.sku);
            if (existingItem) {
                existingItem.quantity++;
                showNotification('success', `Đã tăng số lượng ${product.name}`);
            } else {
                tab.items.push({
                    id: product.id,
                    name: product.name,
                    sku: product.sku,
                    barcode: product.barcode,
                    price: product.sale_price || product.price,
                    image: product.image,
                    stock_quantity: product.stock_quantity,
                    quantity: 1
                });
                showNotification('success', `Đã thêm ${product.name} vào đơn hàng`);
            }

            // Update UI
            updateTabUI(tabId);

            // Focus barcode input after adding product
            setTimeout(function() {
                $('#globalBarcodeInput').focus();
            }, 100);
        }

        function updateTabUI(tabId) {
            const tab = orderTabs.find(t => t.id === tabId);
            if (!tab) return;

            let tabContent = $(`#${tabId}_content`);
            let itemsList = tabContent.find('.order-items-list');
            const itemsCount = tabContent.find('.items-count');
            const tabCountElement = $(`.order-tab[data-tab-id="${tabId}"] .tab-count`);

            // Update count
            const totalItems = tab.items.reduce((sum, item) => sum + item.quantity, 0);
            itemsCount.text(totalItems);
            tabCountElement.text(totalItems);

            // Update items list
            if (tab.items.length === 0) {
                itemsList.html(`
                    <div class="empty-order">
                        <div class="empty-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="empty-text">
                            {{ __('order.scan_barcodes_to_start') }}
                        </div>
                        <div class="empty-hint">
                            Nhấn F3 để focus vào ô nhập mã vạch
                        </div>
                    </div>
                `);
            } else {
                let itemsHtml = '';
                tab.items.forEach((item, index) => {
                    const isOutOfStock = item.stock_quantity <= 0;
                    const stockWarning = item.quantity > item.stock_quantity ?
                        `<div class="stock-warning">Vượt quá tồn kho (${item.stock_quantity})</div>` : '';

                    itemsHtml += `
                        <div class="order-item ${isOutOfStock ? 'out-of-stock' : ''}" data-index="${index}">
                            <div class="item-number">${index + 1}</div>
                            <img src="${item.image || '/admin/media/svg/files/blank-image.svg'}" class="item-image" alt="">
                            <div class="item-info">
                                <div class="item-name">${item.name}</div>
                                <div class="item-sku">${item.sku}</div>
                                <div class="item-unit-price">Đơn giá: ${formatCurrency(item.price)}</div>
                                ${stockWarning}
                                ${isOutOfStock ? '<div class="stock-warning">Hết hàng</div>' : ''}
                            </div>
                            <div class="item-quantity">
                                <button type="button" class="qty-btn" onclick="updateQuantity('${tabId}', ${index}, -1)">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <input type="number" class="qty-input" value="${item.quantity}"
                                       onchange="setQuantity('${tabId}', ${index}, this.value)">
                                <button type="button" class="qty-btn" onclick="updateQuantity('${tabId}', ${index}, 1)">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                            <div class="item-price">${formatCurrency(item.price * item.quantity)}</div>
                            <button type="button" class="item-remove" onclick="removeItem('${tabId}', ${index})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    `;
                });
                itemsList.html(itemsHtml);
            }

            // Update summary
            updateOrderSummary(tabId);

            // Update QR code with new total
            updateQRCode($(`#${tabId}_content`));
        }

        function updateQuantity(tabId, itemIndex, change) {
            const tab = orderTabs.find(t => t.id === tabId);
            if (!tab || !tab.items[itemIndex]) return;

            tab.items[itemIndex].quantity += change;
            if (tab.items[itemIndex].quantity <= 0) {
                tab.items.splice(itemIndex, 1);
            }

            updateTabUI(tabId);
        }

        function setQuantity(tabId, itemIndex, quantity) {
            const tab = orderTabs.find(t => t.id === tabId);
            if (!tab || !tab.items[itemIndex]) return;

            const qty = parseInt(quantity) || 0;
            if (qty <= 0) {
                tab.items.splice(itemIndex, 1);
            } else {
                tab.items[itemIndex].quantity = qty;
            }

            updateTabUI(tabId);
        }

        function removeItem(tabId, itemIndex) {
            const tab = orderTabs.find(t => t.id === tabId);
            if (!tab || !tab.items[itemIndex]) return;

            tab.items.splice(itemIndex, 1);
            updateTabUI(tabId);
        }

        function updateOrderSummary(tabId, skipPaidAmountUpdate = false) {
            const tab = orderTabs.find(t => t.id === tabId);
            if (!tab) return;

            let tabContent = $(`#${tabId}_content`);
            let subtotal = tab.items.reduce((sum, item) => sum + (item.price * item.quantity), 0);

            // Get values from inputs or tab data
            const discount = tab.discount || 0;
            const otherAmount = tab.otherAmount || 0;
            const total = subtotal - discount + otherAmount;

            // Update display - target elements within the current tab
            tabContent.find('#subtotalAmount').text(formatCurrency(subtotal));
            tabContent.find('#discountAmount').val(formatCurrency(discount));
            tabContent.find('#otherAmount').val(formatCurrency(otherAmount));
            tabContent.find('#totalAmount').text(formatCurrency(total));

            // Auto-set paid amount to total when total changes (unless user is manually editing)
            if (!skipPaidAmountUpdate) {
                const paidAmountInput = tabContent.find('#paidAmount');
                const previousTotal = tab.previousTotal || 0;

                // If user hasn't manually edited paid amount, auto-update it
                if (!tab.paidAmountUserEdited && (!tab.paidAmount || tab.paidAmount === 0 || tab.paidAmount === previousTotal)) {
                    tab.paidAmount = total;
                    paidAmountInput.val(formatCurrency(total));
                } else {
                    // Keep user's custom paid amount
                    paidAmountInput.val(formatCurrency(tab.paidAmount));
                }

                // Store current total for next comparison
                tab.previousTotal = total;
            }

            // Enable/disable create order button
            const createBtn = tabContent.find('#createOrderBtn');
            if (tab.items.length > 0) {
                createBtn.prop('disabled', false);
            } else {
                createBtn.prop('disabled', true);
            }
        }

        // Calculate subtotal for a tab
        function calculateSubtotal(tab) {
            return tab.items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        }

        // Get current active tab
        function getCurrentTab() {
            const activeTabId = $('.order-tab.active').data('tab-id');
            return orderTabs.find(t => t.id === activeTabId);
        }

        // Update tab totals (alias for updateOrderSummary)
        function updateTabTotals(tabId, skipPaidAmountUpdate = false) {
            updateOrderSummary(tabId, skipPaidAmountUpdate);
        }

        function createOrderOrInvoice(tabId) {
            const tab = orderTabs.find(t => t.id === tabId);
            if (!tab || tab.items.length === 0) return;

            let tabContent = $(`#${tabId}_content`);
            let createBtn = tabContent.find('#createOrderBtn');

            // Determine if this is order or invoice
            const isInvoice = tab.type === 'invoice';

            // Get form data
            const customerId = tab.customer_id;
            const branchShopId = tab.branch_shop_id;
            const paymentMethod = tab.payment_method;
            const bankAccountId = tab.bank_account_id;
            const notes = tab.notes;
            const soldBy = tabContent.find('#soldBy').val();
            const channel = tabContent.find('#channel').val();

            // Calculate totals
            const subtotal = tab.items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            const discount = tab.discount || 0;
            const otherAmount = tab.otherAmount || 0;
            const totalAmount = subtotal - discount + otherAmount;
            const paidAmount = parseCurrency(tabContent.find('#paidAmount').val()) || 0;

            // Determine payment status
            let paymentStatus = 'unpaid';
            if (paidAmount >= totalAmount) {
                paymentStatus = 'paid';
            } else if (paidAmount > 0) {
                paymentStatus = 'partial';
            }

            // Validate required fields
            // Customer is optional - if empty, will be treated as walk-in customer (customer_id = 0)

            if (!branchShopId) {
                showNotification('error', 'Vui lòng chọn cửa hàng');
                return;
            }

            if (paymentMethod === 'transfer' && !bankAccountId) {
                showNotification('error', 'Vui lòng chọn tài khoản ngân hàng');
                return;
            }

            // Show loading state
            createBtn.prop('disabled', true);
            const loadingText = isInvoice ? 'Đang thanh toán...' : 'Đang đặt hàng...';
            createBtn.html(`<i class="fas fa-spinner fa-spin"></i> ${loadingText}`);

            // Prepare data
            const requestData = {
                customer_id: customerId || null, // Use null for walk-in customer if empty
                branch_shop_id: branchShopId,
                sold_by: soldBy,
                channel: channel,
                payment_method: paymentMethod,
                payment_status: paymentStatus,
                amount_paid: paidAmount,
                bank_account_id: bankAccountId,
                discount_amount: discount,
                other_amount: otherAmount,
                notes: notes,
                items: tab.items.map(item => ({
                    product_id: item.id,
                    quantity: item.quantity,
                    price: item.price
                })),
                _token: $('meta[name="csrf-token"]').attr('content')
            };

            // Determine API endpoint
            const apiUrl = isInvoice ? '{{ route("admin.quick-invoice.store") }}' : '{{ route("admin.quick-order.store") }}';

            // Create order/invoice via API
            $.ajax({
                url: apiUrl,
                method: 'POST',
                data: requestData,
                success: function(response) {
                    if (response.success) {
                        const successMessage = isInvoice ? 'Thanh toán thành công!' : 'Đặt hàng thành công!';
                        showNotification('success', successMessage);

                        // Reset tab completely
                        resetTabAfterOrder(tabId);

                        // Reset order creation time
                        resetOrderCreatedTime();

                        // Show details if available
                        if (response.data && response.data.redirect_url) {
                            setTimeout(() => {
                                window.open(response.data.redirect_url, '_blank');
                            }, 1000);
                        }
                    } else {
                        const errorMessage = isInvoice ? 'Lỗi khi thanh toán' : 'Lỗi khi đặt hàng';
                        showNotification('error', response.message || errorMessage);
                    }
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    const errorMessage = isInvoice ? 'Lỗi khi thanh toán' : 'Lỗi khi đặt hàng';
                    showNotification('error', response?.message || errorMessage);
                },
                complete: function() {
                    // Reset button state
                    createBtn.prop('disabled', false);
                    const buttonText = isInvoice ? 'THANH TOÁN' : 'ĐẶT HÀNG';
                    createBtn.html(`<span class="btn-text">${buttonText}</span>`);
                }
            });









        function updateTabNotes(tabId, notes) {
            const tab = orderTabs.find(t => t.id === tabId);
            if (tab) {
                tab.notes = notes;
                saveDrafts();
            }
        }
        }



        // Initialize header dropdowns
        function initializeHeaderDropdowns() {
            // Load users for super admin
            @if(Auth::user()->is_root == 1)
                loadUsers();
            @endif

            // Branch shop dropdown toggle - use delegation for dynamic tabs
            $(document).on('click', '[id$="_branchShopToggle"]', function(e) {
                e.stopPropagation();
                const tabId = $(this).attr('id').replace('_branchShopToggle', '');
                $(`#${tabId}_branchShopMenu`).toggleClass('show');
                $(`[id$="_channelMenu"]`).removeClass('show');
                $(`[id$="_sellerMenu"]`).removeClass('show');
            });

            // Channel dropdown events - use delegation for dynamic tabs
            $(document).on('click', '[id$="_channelToggle"]', function(e) {
                e.stopPropagation();
                const tabId = $(this).attr('id').replace('_channelToggle', '');
                $(`#${tabId}_channelMenu`).toggleClass('show');
                $(`[id$="_branchShopMenu"]`).removeClass('show');
                $(`[id$="_sellerMenu"]`).removeClass('show');
            });

            // Branch shop search - use delegation for dynamic tabs
            $(document).on('input', '[id$="_branchShopSearch"]', function() {
                const query = $(this).val().toLowerCase();
                const tabId = $(this).attr('id').replace('_branchShopSearch', '');
                $(`#${tabId}_branchShopList .info-dropdown-item`).each(function() {
                    const title = $(this).find('.info-dropdown-title').text().toLowerCase();
                    const subtitle = $(this).find('.info-dropdown-subtitle').text().toLowerCase();
                    $(this).toggle(title.includes(query) || subtitle.includes(query));
                });
            });

            // Channel search - use delegation for dynamic tabs
            $(document).on('input', '[id$="_channelSearch"]', function() {
                const query = $(this).val().toLowerCase();
                const tabId = $(this).attr('id').replace('_channelSearch', '');
                $(`#${tabId}_channelList .info-dropdown-item`).each(function() {
                    const text = $(this).find('span').text().toLowerCase();
                    $(this).toggle(text.includes(query));
                });
            });

            // Branch shop selection - use delegation for dynamic tabs
            $(document).on('click', '[id$="_branchShopList"] .info-dropdown-item', function() {
                const branchId = $(this).data('branch-id');
                const branchName = $(this).data('branch-name');
                const tabId = $(this).closest('[id$="_branchShopList"]').attr('id').replace('_branchShopList', '');

                // Update selected branch shop
                $(`#${tabId}_branchShopName`).text(branchName);

                // Update UI
                $(`#${tabId}_branchShopList .info-dropdown-item`).removeClass('selected');
                $(this).addClass('selected');
                $(`#${tabId}_branchShopMenu`).removeClass('show');

                // Update all tabs with new branch shop
                updateAllTabsBranchShop(branchId, branchName);
            });

            // Channel selection - use delegation for dynamic tabs
            $(document).on('click', '[id$="_channelList"] .info-dropdown-item', function() {
                const channel = $(this).data('channel');
                const channelName = $(this).find('span').text();
                const iconHtml = $(this).find('.info-dropdown-icon').clone();
                const tabId = $(this).closest('[id$="_channelList"]').attr('id').replace('_channelList', '');

                // Update selected channel
                selectedChannel = channel;
                $(`#${tabId}_channelName`).text(channelName);
                $(`#${tabId}_channelToggle .info-dropdown-icon`).replaceWith(iconHtml);

                // Update UI
                $(`#${tabId}_channelList .info-dropdown-item`).removeClass('selected');
                $(this).addClass('selected');
                $(`#${tabId}_channelMenu`).removeClass('show');

                // Update all tabs with new channel
                updateAllTabsChannel(channel);
            });

            // Seller dropdown events (only for super admin) - use delegation for dynamic tabs
            @if(Auth::user()->is_root == 1)
                $(document).on('click', '[id$="_sellerToggle"]', function(e) {
                    e.stopPropagation();
                    const tabId = $(this).attr('id').replace('_sellerToggle', '');
                    $(`#${tabId}_sellerMenu`).toggleClass('show');
                    $(`[id$="_branchShopMenu"]`).removeClass('show');
                    $(`[id$="_channelMenu"]`).removeClass('show');
                });

                // Seller search - use delegation for dynamic tabs
                $(document).on('input', '[id$="_sellerSearch"]', function() {
                    const query = $(this).val().toLowerCase();
                    const tabId = $(this).attr('id').replace('_sellerSearch', '');
                    $(`#${tabId}_sellerList .info-dropdown-item`).each(function() {
                        const text = $(this).find('span').text().toLowerCase();
                        $(this).toggle(text.includes(query));
                    });
                });

                // Seller selection - use delegation for dynamic tabs
                $(document).on('click', '[id$="_sellerList"] .info-dropdown-item', function() {
                    const userId = $(this).data('user-id');
                    const userName = $(this).find('span').text();
                    const tabId = $(this).closest('[id$="_sellerList"]').attr('id').replace('_sellerList', '');

                    // Update selected seller
                    selectedSeller = allUsers.find(u => u.id == userId);
                    $(`#${tabId}_sellerName`).text(userName);

                    // Update UI
                    $(`#${tabId}_sellerList .info-dropdown-item`).removeClass('selected');
                    $(this).addClass('selected');
                    $(`#${tabId}_sellerMenu`).removeClass('show');

                    // Update all tabs with new seller
                    updateAllTabsSeller(userId);
                });
            @endif
        }

        @if(Auth::user()->is_root == 1)
        function loadUsers() {
            // Load users via AJAX (you'll need to create this endpoint)
            $.ajax({
                url: '/admin/users/dropdown/list', // API endpoint for users dropdown
                method: 'GET',
                success: function(response) {
                    if (response.success && response.data) {
                        allUsers = response.data;
                        populateSellerList(allUsers);
                    }
                },
                error: function(xhr) {
                    console.error('Failed to load users:', xhr);
                    // Fallback to current user only
                    allUsers = [selectedSeller];
                    populateSellerList(allUsers);
                }
            });
        }

        function populateSellerList(users) {
            let html = '';
            users.forEach(user => {
                const isSelected = user.id === selectedSeller.id ? 'selected' : '';
                html += `
                    <div class="info-dropdown-item ${isSelected}" data-user-id="${user.id}">
                        <span>${user.name}</span>
                        ${isSelected ? '<i class="fas fa-check ms-auto"></i>' : ''}
                    </div>
                `;
            });

            // Populate seller list for all tabs
            $('[id$="_sellerList"]').html(html);
        }
        @endif

        function updateAllTabsBranchShop(branchId, branchName) {
            // Update branch shop for all existing tabs
            orderTabs.forEach(tab => {
                tab.branch_shop_id = branchId;
                tab.branch_shop_search = branchName;
            });

            // Update hidden input in all tab contents
            $('.tab-content').each(function() {
                $(this).find('#branchShopSelect').val(branchId);
            });

            saveDrafts();
        }

        function updateAllTabsChannel(channel) {
            // Update channel for all existing tabs
            orderTabs.forEach(tab => {
                tab.channel = channel;
            });

            // Update hidden input in all tab contents
            $('.tab-content').each(function() {
                $(this).find('#channel').val(channel);
            });

            saveDrafts();
        }

        function updateAllTabsSeller(sellerId) {
            // Update seller for all existing tabs
            orderTabs.forEach(tab => {
                tab.sold_by = sellerId;
            });

            // Update hidden input in all tab contents
            $('.tab-content').each(function() {
                $(this).find('#soldBy').val(sellerId);
            });

            saveDrafts();
        }

        // Update current time display
        function updateCurrentTime() {
            const now = new Date();
            const timeString = now.toLocaleString('vi-VN', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
            $('#currentTime').text(timeString);
        }

        // Update order creation time
        function updateOrderCreatedTime() {
            if (orderCreatedTime) {
                const timeString = orderCreatedTime.toLocaleString('vi-VN', {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit'
                });
                $('#orderTime').text(timeString);
            } else {
                $('#orderTime').text('--:--');
            }
        }

        // Set order creation time when first item is added
        function setOrderCreatedTime() {
            if (!orderCreatedTime) {
                orderCreatedTime = new Date();
                updateOrderCreatedTime();
            }
        }

        // Reset order creation time when order is completed/cleared
        function resetOrderCreatedTime() {
            orderCreatedTime = null;
            updateOrderCreatedTime();
        }

        // Hide dropdowns when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.info-dropdown').length) {
                $('.info-dropdown-menu').removeClass('show');
            }

            if (!$(e.target).closest('.bank-account-dropdown').length) {
                $('.bank-account-options').removeClass('show');
                $('.bank-account-select').removeClass('active');
            }
        });

        function removeItem(tabId, itemIndex) {
            const tab = orderTabs.find(t => t.id === tabId);
            if (!tab || !tab.items[itemIndex]) return;

            tab.items.splice(itemIndex, 1);

            updateTabUI(tabId);
            saveDrafts();
        }

        // Setup paid amount input
        function setupPaidAmountInput(tabContent) {
            const paidAmountInput = tabContent.find('#paidAmount');

            paidAmountInput.on('input change', function() {
                // Payment status will be calculated when creating order
                // Auto-format the input value
                const value = parseFloat($(this).val()) || 0;
                if (value < 0) {
                    $(this).val(0);
                }
            });
        }

        // Show customer info modal
        function showCustomerInfoModal(customerId) {
            const customer = customers.find(c => c.id == customerId);
            if (!customer) {
                showNotification('error', 'Không tìm thấy thông tin khách hàng');
                return;
            }

            // Update modal header
            $('#customerModalName').text(customer.name || '');
            $('#customerModalCode').text(customer.customer_code || '');

            // Update basic info tab
            $('#customerModalCustomerCode').text(customer.customer_code || 'N/A');
            $('#customerModalFullName').text(customer.name || 'N/A');
            $('#customerModalPhone').text(customer.phone || 'N/A');
            $('#customerModalAddress').text(customer.address || 'N/A');
            $('#customerModalArea').text(customer.area || 'N/A');
            $('#customerModalType').html(customer.customer_type === 'individual' ?
                '<span class="badge badge-light-primary">Cá nhân</span>' :
                '<span class="badge badge-light-info">Công ty</span>');
            $('#customerModalTaxCode').text(customer.tax_code || 'N/A');
            $('#customerModalEmail').text(customer.email || 'N/A');
            $('#customerModalFacebook').text(customer.facebook || 'N/A');
            $('#customerModalGroup').text(customer.customer_group || 'N/A');
            $('#customerModalNotes').text(customer.notes || 'N/A');
            $('#customerModalBirthday').text(customer.birthday || 'N/A');

            // Load customer statistics via AJAX
            loadCustomerStatistics(customerId);

            // Show modal
            $('#customerInfoModal').modal('show');
        }

        // Load customer statistics and data
        function loadCustomerStatistics(customerId) {
            // Show loading state
            $('#customerDebtAmount').html('<i class="fas fa-spinner fa-spin"></i>');
            $('#customerPointCount').html('<i class="fas fa-spinner fa-spin"></i>');
            $('#customerTotalSpent').html('<i class="fas fa-spinner fa-spin"></i>');
            $('#customerPurchaseCount').html('<i class="fas fa-spinner fa-spin"></i>');
            $('#customerNetSales').html('<i class="fas fa-spinner fa-spin"></i>');

            // Load customer statistics
            $.ajax({
                url: `/admin/customers/${customerId}/statistics`,
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Accept': 'application/json'
                },
                success: function(response) {
                    console.log('Customer statistics response:', response);

                    if (response.success) {
                        const stats = response.data;

                        // Update stats cards
                        $('#customerDebtAmount').text(formatCurrency(stats.total_debt || 0));
                        $('#customerPointCount').text(stats.total_points || 0);
                        $('#customerTotalSpent').text(formatCurrency(stats.total_spent || 0));
                        $('#customerPurchaseCount').text(stats.purchase_count || 0);
                        $('#customerNetSales').text(formatCurrency(stats.net_sales || 0));

                        // Load order history
                        loadCustomerOrderHistory(stats.order_history || []);

                        // Load debt details
                        loadCustomerDebtDetails(stats.debt_details || []);

                        // Load points history
                        loadCustomerPointsHistory(stats.points_history || []);
                    } else {
                        console.error('API returned error:', response.message);
                        showNotification('error', response.message || 'Không thể tải thông tin khách hàng');
                        setDefaultStats();
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', {
                        status: xhr.status,
                        statusText: xhr.statusText,
                        responseText: xhr.responseText,
                        error: error
                    });

                    let errorMessage = 'Lỗi kết nối';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.status === 404) {
                        errorMessage = 'Không tìm thấy API endpoint';
                    } else if (xhr.status === 500) {
                        errorMessage = 'Lỗi server';
                    }

                    showNotification('error', errorMessage);
                    setDefaultStats();
                }
            });
        }

        // Set default stats values
        function setDefaultStats() {
            $('#customerDebtAmount').text('0');
            $('#customerPointCount').text('0');
            $('#customerTotalSpent').text('0');
            $('#customerPurchaseCount').text('0');
            $('#customerNetSales').text('0');

            // Clear tables
            $('#customerOrderHistoryTable').html('<tr><td colspan="5" class="text-center text-muted">Không thể tải dữ liệu</td></tr>');
            $('#customerDebtTable').html('<tr><td colspan="6" class="text-center text-muted">Không thể tải dữ liệu</td></tr>');
            $('#customerPointsTable').html('<tr><td colspan="5" class="text-center text-muted">Không thể tải dữ liệu</td></tr>');
        }
        // Load customer order history
        function loadCustomerOrderHistory(orderHistory) {
            const tbody = $('#customerOrderHistoryTable');
            tbody.empty();

            if (orderHistory.length === 0) {
                tbody.append('<tr><td colspan="5" class="text-center text-muted">Chưa có đơn hàng nào</td></tr>');
                return;
            }

            orderHistory.forEach(order => {
                const statusBadge = getOrderStatusBadge(order.status);
                const row = `
                    <tr>
                        <td><a href="#" class="text-primary">${order.order_code}</a></td>
                        <td>${order.date}</td>
                        <td>${order.seller}</td>
                        <td class="text-end">${formatCurrency(order.total)}</td>
                        <td>${statusBadge}</td>
                    </tr>
                `;
                tbody.append(row);
            });
        }

        // Load customer debt details
        function loadCustomerDebtDetails(debtDetails) {
            const tbody = $('#customerDebtTable');
            tbody.empty();

            if (debtDetails.length === 0) {
                tbody.append('<tr><td colspan="6" class="text-center text-muted">Không có công nợ</td></tr>');
                return;
            }

            debtDetails.forEach(debt => {
                const remaining = debt.total - debt.paid;
                const row = `
                    <tr>
                        <td><a href="#" class="text-primary">${debt.order_code}</a></td>
                        <td>${debt.date}</td>
                        <td>${debt.seller}</td>
                        <td class="text-end">${formatCurrency(debt.total)}</td>
                        <td class="text-end">${formatCurrency(debt.paid)}</td>
                        <td class="text-end text-danger">${formatCurrency(remaining)}</td>
                    </tr>
                `;
                tbody.append(row);
            });
        }

        // Load customer points history
        function loadCustomerPointsHistory(pointsHistory) {
            const tbody = $('#customerPointsTable');
            tbody.empty();

            if (pointsHistory.length === 0) {
                tbody.append('<tr><td colspan="5" class="text-center text-muted">Chưa có giao dịch điểm nào</td></tr>');
                return;
            }

            pointsHistory.forEach(point => {
                const pointsClass = point.points > 0 ? 'text-success' : 'text-danger';
                const pointsText = point.points > 0 ? `+${point.points}` : point.points;
                const row = `
                    <tr>
                        <td>${point.date}</td>
                        <td>${point.type}</td>
                        <td class="${pointsClass}">${pointsText}</td>
                        <td>${point.note}</td>
                        <td class="text-end">${point.balance}</td>
                    </tr>
                `;
                tbody.append(row);
            });
        }

        // Get order status badge
        function getOrderStatusBadge(status) {
            const badges = {
                'pending': '<span class="badge badge-light-warning">Chờ xử lý</span>',
                'processing': '<span class="badge badge-light-info">Đang xử lý</span>',
                'completed': '<span class="badge badge-light-success">Hoàn thành</span>',
                'cancelled': '<span class="badge badge-light-danger">Đã hủy</span>',
                'returned': '<span class="badge badge-light-secondary">Trả hàng</span>'
            };
            return badges[status] || '<span class="badge badge-light-secondary">N/A</span>';
        }

        // Format currency input
        function formatCurrencyInput(input) {
            let value = input.value.replace(/[^\d]/g, '');
            if (value) {
                input.value = formatCurrency(parseInt(value));
            } else {
                input.value = '0';
            }
        }

        // Parse currency value to number
        function parseCurrency(value) {
            if (typeof value === 'string') {
                return parseInt(value.replace(/[^\d]/g, '')) || 0;
            }
            return value || 0;
        }

        // Open discount modal
        function openDiscountModal() {
            // Reset modal
            $('#discountInput').val('');
            $('#discountVND').prop('checked', true);
            $('#discountPercent').prop('checked', false);
            $('#promotionAmount').text('0');
            $('#totalDiscountAmount').text('0');

            // Reset button styles
            $('#discountVND').next('label').removeClass('btn-outline-primary').addClass('btn-primary');
            $('#discountPercent').next('label').removeClass('btn-primary').addClass('btn-outline-primary');

            // Show modal
            $('#discountModal').modal('show');

            // Focus on input
            setTimeout(() => {
                $('#discountInput').focus();
            }, 300);
        }

        // Format discount input
        function formatDiscountInput(input) {
            const discountType = $('input[name="discountType"]:checked').val();
            let value = input.value.replace(/[^\d]/g, '');

            if (discountType === 'VND') {
                if (value) {
                    input.value = formatCurrency(parseInt(value));
                } else {
                    input.value = '';
                }
            } else {
                // Percent - limit to 100
                if (value) {
                    let percent = parseInt(value);
                    if (percent > 100) percent = 100;
                    input.value = percent;
                } else {
                    input.value = '';
                }
            }

            updateDiscountPreview();
        }

        // Update discount preview
        function updateDiscountPreview() {
            const discountType = $('input[name="discountType"]:checked').val();
            const inputValue = $('#discountInput').val();

            if (!inputValue) {
                $('#promotionAmount').text('0');
                $('#totalDiscountAmount').text('0');
                return;
            }

            let discountAmount = 0;
            const currentTab = getCurrentTab();
            const subtotal = currentTab ? calculateSubtotal(currentTab) : 0;

            if (discountType === 'VND') {
                discountAmount = parseCurrency(inputValue);
            } else {
                const percent = parseInt(inputValue.replace(/[^\d]/g, '')) || 0;
                discountAmount = Math.round(subtotal * percent / 100);
            }

            // Don't allow discount greater than subtotal
            if (discountAmount > subtotal) {
                discountAmount = subtotal;
            }

            $('#promotionAmount').text(formatCurrency(discountAmount));
            $('#totalDiscountAmount').text(formatCurrency(discountAmount));
        }

        // Apply discount
        function applyDiscount() {
            const discountAmount = parseCurrency($('#totalDiscountAmount').text());

            // Update current tab
            const currentTab = getCurrentTab();
            if (currentTab) {
                const tabContent = $(`#${currentTab.id}_content`);
                tabContent.find('#discountAmount').val(formatCurrency(discountAmount));

                currentTab.discount = discountAmount;
                updateTabTotals(currentTab.id);
            }

            $('#discountModal').modal('hide');
        }

        // Open other charges modal
        function openOtherChargesModal() {
            // Reset modal
            $('#newChargeCode').val('');
            $('#newChargeDescription').val('');
            $('#newChargeAmount').val('');
            $('#selectAllCharges').prop('checked', false);
            $('.charge-checkbox').prop('checked', false);
            updateOtherChargesTotal();

            // Show modal
            $('#otherChargesModal').modal('show');
        }

        // Add new other charge
        function addOtherCharge() {
            const code = $('#newChargeCode').val().trim();
            const description = $('#newChargeDescription').val().trim();
            const amount = parseCurrency($('#newChargeAmount').val());

            if (!code || !description || amount <= 0) {
                alert('Vui lòng nhập đầy đủ thông tin');
                return;
            }

            // Add to table
            const newRow = `
                <tr>
                    <td><input type="checkbox" class="charge-checkbox" data-amount="${amount}"></td>
                    <td>${code}</td>
                    <td>${description}</td>
                    <td class="text-end">${formatCurrency(amount)}</td>
                    <td class="text-end">0</td>
                </tr>
            `;
            $('#otherChargesTableBody').append(newRow);

            // Clear form
            $('#newChargeCode').val('');
            $('#newChargeDescription').val('');
            $('#newChargeAmount').val('');

            // Bind events to new checkbox
            bindChargeCheckboxEvents();
        }

        // Toggle all charges
        function toggleAllCharges(checkbox) {
            $('.charge-checkbox').prop('checked', checkbox.checked);
            updateOtherChargesTotal();
        }

        // Update other charges total
        function updateOtherChargesTotal() {
            let total = 0;
            $('.charge-checkbox:checked').each(function() {
                total += parseInt($(this).data('amount')) || 0;
            });
            $('#totalOtherCharges').text(formatCurrency(total));
        }

        // Bind events to charge checkboxes
        function bindChargeCheckboxEvents() {
            $('.charge-checkbox').off('change').on('change', function() {
                updateOtherChargesTotal();

                // Update select all checkbox
                const totalCheckboxes = $('.charge-checkbox').length;
                const checkedCheckboxes = $('.charge-checkbox:checked').length;
                $('#selectAllCharges').prop('checked', totalCheckboxes === checkedCheckboxes);
            });
        }

        // Apply other charges
        function applyOtherCharges() {
            const totalAmount = parseCurrency($('#totalOtherCharges').text());

            // Update current tab
            const currentTab = getCurrentTab();
            if (currentTab) {
                const tabContent = $(`#${currentTab.id}_content`);
                tabContent.find('#otherAmount').val(formatCurrency(totalAmount));

                currentTab.otherAmount = totalAmount;
                updateTabTotals(currentTab.id);
            }

            $('#otherChargesModal').modal('hide');
        }

        // Reset tab completely after order creation
        function resetTabAfterOrder(tabId) {
            const tab = orderTabs.find(t => t.id === tabId);
            if (!tab) return;

            const tabContent = $(`#${tabId}_content`);

            // Clear all tab data
            tab.items = [];
            tab.customer_id = '';
            tab.customer_search = '';
            tab.customer_phone = '';
            tab.branch_shop_id = defaultBranchShop ? defaultBranchShop.id : '';
            tab.branch_shop_search = defaultBranchShop ? defaultBranchShop.name : '';
            tab.payment_method = 'cash';
            tab.bank_account_id = '';
            tab.notes = '';
            tab.discount = 0;
            tab.otherAmount = 0;
            tab.paidAmount = 0;
            tab.previousTotal = 0;
            tab.paidAmountUserEdited = false;

            // Reset form elements
            tabContent.find('#customerSearch').val('');
            tabContent.find('#customerSelect').val('');
            tabContent.find('#branchShopSearch').val(defaultBranchShop ? defaultBranchShop.name : '');
            tabContent.find('#branchShopSelect').val(defaultBranchShop ? defaultBranchShop.id : '');
            tabContent.find('.payment-method').removeClass('active');
            tabContent.find('[data-method="cash"]').addClass('active');
            tabContent.find('input[name="payment_method"][value="cash"]').prop('checked', true);
            tabContent.find('#bankAccountSection').removeClass('show');
            tabContent.find('#orderNotes').val('');

            // Reset customer display
            tabContent.find('#customerSearchContainer').show();
            tabContent.find('#selectedCustomerDisplay').hide();

            // Reset summary within the tab
            tabContent.find('#discountAmount').val('0');
            tabContent.find('#otherAmount').val('0');
            tabContent.find('#paidAmount').val('0');
            tabContent.find('#subtotalAmount').text('0 ₫');
            tabContent.find('#totalAmount').text('0 ₫');

            // Update UI and save
            updateTabUI(tabId);
            saveDrafts();
        }

        // Mark paid amount as user edited
        function markPaidAmountAsUserEdited() {
            const currentTab = getCurrentTab();
            if (currentTab) {
                currentTab.paidAmountUserEdited = true;
            }
        }

        // Update order totals when summary inputs change
        function updateOrderTotals() {
            const currentTab = getCurrentTab();
            if (!currentTab) return;

            const tabContent = $(`#${currentTab.id}_content`);

            // Get values from inputs within the current tab
            const otherAmount = parseCurrency(tabContent.find('#otherAmount').val());
            const paidAmount = parseCurrency(tabContent.find('#paidAmount').val());

            // Update tab data
            currentTab.otherAmount = otherAmount;
            currentTab.paidAmount = paidAmount;

            // Update totals but skip paid amount auto-update since user is manually editing
            updateOrderSummary(currentTab.id, true);
        }

        // Handle discount type change
        $(document).on('change', 'input[name="discountType"]', function() {
            const selectedType = $(this).val();

            // Update button styles
            if (selectedType === 'VND') {
                $('#discountVND').next('label').removeClass('btn-outline-primary').addClass('btn-primary');
                $('#discountPercent').next('label').removeClass('btn-primary').addClass('btn-outline-primary');
            } else {
                $('#discountPercent').next('label').removeClass('btn-outline-primary').addClass('btn-primary');
                $('#discountVND').next('label').removeClass('btn-primary').addClass('btn-outline-primary');
            }

            // Clear input and focus
            $('#discountInput').val('').focus();
            updateDiscountPreview();
        });



        // Function to fix existing tabs with TAB_ID_ placeholders
        function fixExistingTabIds() {
            // Find all tab contents
            $('#orderTabsContent > div[id$="_content"]').each(function() {
                const tabContent = $(this);
                const tabId = tabContent.attr('data-tab-id');

                if (tabId) {
                    // Fix all elements with TAB_ID_ in their IDs
                    tabContent.find('[id*="TAB_ID_"]').each(function() {
                        const element = $(this);
                        const oldId = element.attr('id');
                        const newId = oldId.replace('TAB_ID_', tabId + '_');
                        element.attr('id', newId);
                        console.log('Fixed ID:', oldId, '->', newId);
                    });
                }
            });
        }

    </script>

    <!-- Confirm Close Tab Modal -->
    <div class="modal fade" id="confirmCloseTabModal" tabindex="-1" aria-labelledby="confirmCloseTabModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title text-danger fw-bold" id="confirmCloseTabModalLabel">Đóng <span id="closeTabName"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-2">
                    <p class="mb-0">Thông tin của <strong id="closeTabNameInBody"></strong> sẽ không được lưu lại. Bạn có chắc chắn muốn đóng không?</p>
                </div>
                <div class="modal-footer border-0 pt-2">
                    <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">Bỏ qua</button>
                    <button type="button" class="btn btn-danger" id="confirmCloseTabBtn">Đồng ý</button>
                </div>
            </div>
        </div>
    </div>

</body>
</html>