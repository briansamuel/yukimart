<!--begin::Row Expansion Styles-->
<style>
/* DataTable Row Expansion Styles */
.dt-control {
    cursor: pointer;
    text-align: center;
    vertical-align: middle;
    user-select: none;
    position: relative;
    width: 30px;
    height: 30px;
    border-radius: 6px;
    transition: all 0.3s ease;
}

.dt-control:hover {
    background-color: rgba(54, 147, 255, 0.1);
    transform: scale(1.05);
}

/* Default state - Plus icon */
table.dataTable td.dt-control:before {
    content: "\f055"; /* fa-plus-circle */
    font-family: "Font Awesome 6 Free";
    font-weight: 900;
    font-size: 1.2rem;
    color: #3699ff;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    transition: all 0.3s ease;
}

/* Expanded state - Minus icon */
table.dataTable tr.shown td.dt-control:before {
    content: "\f056"; /* fa-minus-circle */
    color: #f1416c;
    transform: translate(-50%, -50%) rotate(180deg);
}

/* Hover effects */
.dt-control:hover:before {
    transform: translate(-50%, -50%) scale(1.2);
}

table.dataTable tr.shown td.dt-control:hover:before {
    transform: translate(-50%, -50%) rotate(180deg) scale(1.2);
}

/* Focus state for accessibility */
.dt-control:focus {
    outline: 2px solid #3699ff;
    outline-offset: 2px;
}

/* Loading state */
.dt-control.loading:before {
    content: "\f110"; /* fa-spinner */
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: translate(-50%, -50%) rotate(0deg); }
    100% { transform: translate(-50%, -50%) rotate(360deg); }
}

/* Expanded row styling */
tr.shown {
    background-color: rgba(0, 123, 255, 0.02);
}

tr.shown td {
    border-bottom: none;
}

/* Product details expansion container */
.product-details-expansion {
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    border-radius: 12px;
    padding: 0;
    margin: 0;
    animation: expandIn 0.3s ease-out;
}

@keyframes expandIn {
    0% {
        opacity: 0;
        transform: translateY(-10px);
    }
    100% {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Card styling within expansion */
.product-details-expansion .card {
    border: none;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    border-radius: 12px;
    overflow: hidden;
}

.product-details-expansion .card-body {
    padding: 2rem;
}

/* Information grid cards */
.product-details-expansion .card.bg-light-primary {
    background: linear-gradient(135deg, rgba(54, 147, 255, 0.1) 0%, rgba(54, 147, 255, 0.05) 100%);
    border-left: 4px solid #3699ff;
}

.product-details-expansion .card.bg-light-success {
    background: linear-gradient(135deg, rgba(80, 205, 137, 0.1) 0%, rgba(80, 205, 137, 0.05) 100%);
    border-left: 4px solid #50cd89;
}

.product-details-expansion .card.bg-light-warning {
    background: linear-gradient(135deg, rgba(255, 199, 0, 0.1) 0%, rgba(255, 199, 0, 0.05) 100%);
    border-left: 4px solid #ffc700;
}

.product-details-expansion .card.bg-light-info {
    background: linear-gradient(135deg, rgba(124, 58, 237, 0.1) 0%, rgba(124, 58, 237, 0.05) 100%);
    border-left: 4px solid #7c3aed;
}

/* Section headers */
.product-details-expansion h6 {
    font-size: 0.95rem;
    font-weight: 700;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
}

.product-details-expansion h6 i {
    font-size: 1rem;
    margin-right: 0.5rem;
}

/* Data rows */
.product-details-expansion .row.g-3 > div {
    margin-bottom: 0.75rem;
}

.product-details-expansion .text-muted.fs-7 {
    font-size: 0.75rem !important;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.25rem;
}

.product-details-expansion .fw-bold {
    font-weight: 600;
    color: #2b2b2b;
}

/* Stock status badges in expansion */
.product-details-expansion .stock-status-badge {
    padding: 0.375rem 0.75rem;
    border-radius: 6px;
    font-size: 0.8rem;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
}

.product-details-expansion .stock-status-badge i {
    margin-right: 0.375rem;
    font-size: 0.9rem;
}

/* Action buttons */
.product-details-expansion .btn {
    border-radius: 8px;
    font-weight: 600;
    padding: 0.5rem 1rem;
    transition: all 0.2s ease;
}

.product-details-expansion .btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.product-details-expansion .btn i {
    font-size: 0.875rem;
}

/* Symbol/Avatar styling */
.product-details-expansion .symbol img {
    border-radius: 8px;
    object-fit: cover;
    border: 2px solid #f1f3f6;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .product-details-expansion .card-body {
        padding: 1.5rem;
    }
    
    .product-details-expansion .row.g-6 {
        gap: 1rem !important;
    }
    
    .product-details-expansion .col-lg-6 {
        margin-bottom: 1rem;
    }
    
    .product-details-expansion .btn {
        width: 100%;
        margin-bottom: 0.5rem;
    }
    
    .product-details-expansion .d-flex.justify-content-end {
        flex-direction: column;
    }
}

@media (max-width: 576px) {
    .product-details-expansion .card-body {
        padding: 1rem;
    }
    
    .product-details-expansion h4 {
        font-size: 1.1rem;
    }
    
    .product-details-expansion .symbol {
        width: 50px !important;
        height: 50px !important;
    }
    
    .product-details-expansion .row.g-3 .col-6 {
        flex: 0 0 100%;
        max-width: 100%;
    }
}

/* Loading states */
.product-details-loading {
    text-align: center;
    padding: 2rem;
    color: #6c757d;
}

.product-details-loading i {
    font-size: 2rem;
    margin-bottom: 1rem;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Error states */
.product-details-error {
    text-align: center;
    padding: 2rem;
    color: #dc3545;
    background-color: rgba(220, 53, 69, 0.1);
    border-radius: 8px;
    border: 1px solid rgba(220, 53, 69, 0.2);
}

.product-details-error i {
    font-size: 2rem;
    margin-bottom: 1rem;
}

/* Hover effects for data items */
.product-details-expansion .row.g-3 > div:hover {
    background-color: rgba(255, 255, 255, 0.7);
    border-radius: 6px;
    padding: 0.5rem;
    transition: all 0.2s ease;
}

/* Badge enhancements */
.product-details-expansion .badge {
    font-size: 0.75rem;
    padding: 0.375rem 0.75rem;
    border-radius: 6px;
}

/* Timeline styles for history modal */
.product-history-modal .timeline {
    padding-left: 0;
}

.product-history-modal .timeline-item {
    position: relative;
    padding-left: 3rem;
    padding-bottom: 2rem;
}

.product-history-modal .timeline-line {
    position: absolute;
    left: 1.25rem;
    top: 2.5rem;
    bottom: -2rem;
    width: 2px;
    background-color: #e4e6ef;
}

.product-history-modal .timeline-icon {
    position: absolute;
    left: 0;
    top: 0;
}

.product-history-modal .timeline-content {
    background-color: #f8f9fa;
    border-radius: 8px;
    padding: 1rem;
    border-left: 3px solid #3699ff;
}

/* Print styles */
@media print {
    .product-details-expansion {
        background: white !important;
        box-shadow: none !important;
    }
    
    .product-details-expansion .card {
        border: 1px solid #000 !important;
        box-shadow: none !important;
    }
    
    .product-details-expansion .btn {
        display: none !important;
    }
    
    .dt-control {
        display: none !important;
    }
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
    .product-details-expansion {
        background: linear-gradient(135deg, #2a2a2a 0%, #1a1a1a 100%);
    }
    
    .product-details-expansion .card {
        background-color: #2a2a2a;
        color: #ffffff;
    }
    
    .product-details-expansion .fw-bold {
        color: #ffffff;
    }
    
    .product-details-expansion .text-muted {
        color: #9ca3af !important;
    }
}

/* Accessibility improvements */
.dt-control:focus {
    outline: 2px solid #3699ff;
    outline-offset: 2px;
}

.product-details-expansion .btn:focus {
    outline: 2px solid #3699ff;
    outline-offset: 2px;
}

/* High contrast mode */
@media (prefers-contrast: high) {
    .product-details-expansion .card {
        border: 2px solid #000;
    }

    .dt-control {
        border: 1px solid #000;
    }

    .product-details-expansion .text-muted {
        color: #000 !important;
    }
}

/* Action Dropdown Styles */
.action-dropdown-menu {
    display: none;
    position: fixed;
    z-index: 1050;
    min-width: 200px;
    background: #ffffff;
    border: 1px solid rgba(0, 0, 0, 0.1);
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    padding: 0.5rem 0;
    transition: all 0.2s ease;
    max-height: 400px;
    overflow-y: auto;
}

.action-dropdown-btn {
    position: relative;
    transition: all 0.2s ease;
    border: 1px solid #e4e6ef;
}

.action-dropdown-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    border-color: #3699ff;
}

.action-dropdown-btn.active {
    background-color: #3699ff !important;
    color: white !important;
    border-color: #3699ff !important;
    box-shadow: 0 4px 12px rgba(54, 147, 255, 0.3);
}

.dropdown-arrow {
    transition: transform 0.2s ease;
    font-size: 0.75rem;
}

.dropdown .menu {
    display: none;
    position: absolute;
    top: 100%;
    right: 0;
    z-index: 1050;
    min-width: 200px;
    background: #ffffff;
    border: 1px solid rgba(0, 0, 0, 0.1);
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    animation: dropdownFadeIn 0.2s ease-out;
}

.dropdown .menu.show {
    display: block;
}

@keyframes dropdownFadeIn {
    0% {
        opacity: 0;
        transform: translateY(-10px);
    }
    100% {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Dropdown Items */
.action-dropdown-menu .dropdown-item {
    display: flex;
    align-items: center;
    padding: 0.75rem 1rem;
    color: #5e6278;
    text-decoration: none;
    transition: all 0.2s ease;
    border: none;
    background: none;
    width: 100%;
    text-align: left;
    font-size: 0.875rem;
}

.action-dropdown-menu .dropdown-item:hover {
    background-color: rgba(54, 147, 255, 0.1);
    color: #3699ff;
    text-decoration: none;
}

.action-dropdown-menu .dropdown-item.text-danger:hover {
    background-color: rgba(241, 65, 108, 0.1);
    color: #f1416c;
}

.action-dropdown-menu .dropdown-item i {
    width: 16px;
    text-align: center;
    margin-right: 0.75rem;
}

.action-dropdown-menu .dropdown-header {
    padding: 0.5rem 1rem 0.25rem;
    margin-bottom: 0.25rem;
}

.action-dropdown-menu .dropdown-header small {
    font-size: 0.75rem;
    font-weight: 600;
    letter-spacing: 0.5px;
    color: #a1a5b7;
}

.action-dropdown-menu .dropdown-divider {
    height: 1px;
    background-color: rgba(0, 0, 0, 0.05);
    margin: 0.5rem 0;
    border: none;
}

/* Menu Items */
.menu .menu-item {
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}

.menu .menu-item:last-child {
    border-bottom: none;
}

.menu .menu-link {
    display: flex;
    align-items: center;
    padding: 0.75rem 1rem;
    color: #5e6278;
    text-decoration: none;
    transition: all 0.2s ease;
    border-radius: 4px;
    margin: 0 0.25rem;
}

.menu .menu-link:hover {
    background-color: rgba(54, 147, 255, 0.1);
    color: #3699ff;
    text-decoration: none;
}

.menu .menu-link.text-danger:hover {
    background-color: rgba(241, 65, 108, 0.1);
    color: #f1416c;
}

.menu .menu-icon {
    width: 20px;
    margin-right: 0.75rem;
    text-align: center;
}

.menu .menu-title {
    font-weight: 500;
    font-size: 0.875rem;
}

.menu .menu-content {
    padding: 0.5rem 1rem;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #a1a5b7;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    margin-bottom: 0.25rem;
}

.menu .separator {
    height: 1px;
    background-color: rgba(0, 0, 0, 0.05);
    margin: 0.5rem 0;
}

/* Action Button Styling */
.dropdown-toggle {
    position: relative;
    border-radius: 6px;
    font-weight: 500;
    transition: all 0.2s ease;
}

.dropdown-toggle:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.dropdown-toggle i {
    transition: transform 0.2s ease;
}

.dropdown-toggle[aria-expanded="true"] i {
    transform: rotate(180deg);
}

/* Loading state for action buttons */
.menu-link.loading {
    pointer-events: none;
    opacity: 0.6;
}

.menu-link.loading .menu-icon i {
    animation: spin 1s linear infinite;
}

/* Success/Error states */
.menu-link.success {
    background-color: rgba(80, 205, 137, 0.1);
    color: #50cd89;
}

.menu-link.error {
    background-color: rgba(241, 65, 108, 0.1);
    color: #f1416c;
}

/* Mobile responsive */
@media (max-width: 768px) {
    .dropdown .menu {
        min-width: 180px;
        right: -20px;
    }

    .menu .menu-link {
        padding: 1rem;
    }

    .menu .menu-title {
        font-size: 0.9rem;
    }
}

/* Dark mode support for dropdown */
@media (prefers-color-scheme: dark) {
    .dropdown .menu {
        background: #2a2a2a;
        border-color: rgba(255, 255, 255, 0.1);
    }

    .menu .menu-link {
        color: #ffffff;
    }

    .menu .menu-link:hover {
        background-color: rgba(54, 147, 255, 0.2);
    }

    .menu .menu-content {
        color: #9ca3af;
        border-bottom-color: rgba(255, 255, 255, 0.1);
    }

    .menu .separator {
        background-color: rgba(255, 255, 255, 0.1);
    }
}

/* Print styles - hide dropdowns */
@media print {
    .dropdown,
    .menu {
        display: none !important;
    }
}
</style>
<!--end::Row Expansion Styles-->
