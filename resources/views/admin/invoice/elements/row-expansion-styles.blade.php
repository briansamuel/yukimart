<!--begin::Row Expansion Styles-->
<style>
/* DataTable Row Expansion Styles for Invoice */
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
    background-color: rgba(0, 158, 247, 0.1);
    transform: scale(1.05);
}

/* Default state - Plus icon */
table.dataTable td.dt-control:before {
    content: "\f055"; /* fa-plus-circle */
    font-family: "Font Awesome 6 Free";
    font-weight: 900;
    font-size: 1.2rem;
    color: #009ef7;
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
    outline: 2px solid #009ef7;
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
    background-color: rgba(0, 158, 247, 0.02);
}

tr.shown td {
    border-bottom: none;
}

/* Invoice details expansion container */
.invoice-details-expansion {
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
.invoice-details-expansion .card {
    border: none;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    border-radius: 12px;
    overflow: hidden;
}

.invoice-details-expansion .card-body {
    padding: 2rem;
}

/* Tab styling */
.invoice-details-expansion .nav-tabs {
    border-bottom: 2px solid #e4e6ef;
    margin-bottom: 1.5rem;
}

.invoice-details-expansion .nav-tabs .nav-link {
    border: none;
    border-bottom: 2px solid transparent;
    color: #7e8299;
    font-weight: 600;
    padding: 1rem 1.5rem;
    transition: all 0.3s ease;
}

.invoice-details-expansion .nav-tabs .nav-link:hover {
    border-color: transparent;
    color: #009ef7;
}

.invoice-details-expansion .nav-tabs .nav-link.active {
    background-color: transparent;
    border-color: #009ef7;
    color: #009ef7;
}

/* Form styling in expansion */
.invoice-details-expansion .form-label {
    font-weight: 600;
    color: #5e6278;
    margin-bottom: 0.5rem;
    font-size: 0.875rem;
}

.invoice-details-expansion .form-control {
    border: 1px solid #e4e6ef;
    border-radius: 0.5rem;
    padding: 0.75rem 1rem;
    font-size: 0.875rem;
    transition: all 0.3s ease;
}

.invoice-details-expansion .form-control:focus {
    border-color: #009ef7;
    box-shadow: 0 0 0 0.2rem rgba(0, 158, 247, 0.25);
}

/* Table styling in expansion */
.invoice-details-expansion .table {
    margin-bottom: 0;
}

.invoice-details-expansion .table thead th {
    background-color: #f8f9fa;
    border-bottom: 2px solid #e4e6ef;
    font-weight: 600;
    color: #5e6278;
    padding: 1rem 0.75rem;
    font-size: 0.875rem;
}

.invoice-details-expansion .table tbody td {
    padding: 0.75rem;
    border-bottom: 1px solid #f1f1f2;
    vertical-align: middle;
    font-size: 0.875rem;
}

.invoice-details-expansion .table tbody tr:hover {
    background-color: rgba(0, 158, 247, 0.05);
}

/* Badge styling */
.invoice-details-expansion .badge {
    font-size: 0.75rem;
    padding: 0.375rem 0.75rem;
    border-radius: 6px;
    font-weight: 600;
}

/* Button styling */
.invoice-details-expansion .btn {
    border-radius: 8px;
    font-weight: 600;
    padding: 0.5rem 1rem;
    transition: all 0.2s ease;
    font-size: 0.875rem;
}

.invoice-details-expansion .btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.invoice-details-expansion .btn i {
    font-size: 0.875rem;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .invoice-details-expansion .card-body {
        padding: 1.5rem;
    }
    
    .invoice-details-expansion .nav-tabs .nav-link {
        padding: 0.75rem 1rem;
        font-size: 0.875rem;
    }
    
    .invoice-details-expansion .btn {
        width: 100%;
        margin-bottom: 0.5rem;
    }
    
    .invoice-details-expansion .table {
        font-size: 0.8rem;
    }
    
    .invoice-details-expansion .table thead th,
    .invoice-details-expansion .table tbody td {
        padding: 0.5rem 0.25rem;
    }
}

@media (max-width: 576px) {
    .invoice-details-expansion .card-body {
        padding: 1rem;
    }
    
    .invoice-details-expansion .nav-tabs {
        flex-wrap: wrap;
    }
    
    .invoice-details-expansion .nav-tabs .nav-link {
        flex: 1;
        text-align: center;
        min-width: 0;
    }
}

/* Loading states */
.invoice-details-loading {
    text-align: center;
    padding: 2rem;
    color: #6c757d;
}

.invoice-details-loading i {
    font-size: 2rem;
    margin-bottom: 1rem;
    animation: spin 1s linear infinite;
}

/* Error states */
.invoice-details-error {
    text-align: center;
    padding: 2rem;
    color: #dc3545;
    background-color: rgba(220, 53, 69, 0.1);
    border-radius: 8px;
    border: 1px solid rgba(220, 53, 69, 0.2);
}

.invoice-details-error i {
    font-size: 2rem;
    margin-bottom: 1rem;
}

/* Print styles */
@media print {
    .invoice-details-expansion {
        background: white !important;
        box-shadow: none !important;
    }
    
    .invoice-details-expansion .card {
        border: 1px solid #000 !important;
        box-shadow: none !important;
    }
    
    .invoice-details-expansion .btn {
        display: none !important;
    }
    
    .dt-control {
        display: none !important;
    }
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
    .invoice-details-expansion {
        background: linear-gradient(135deg, #2a2a2a 0%, #1a1a1a 100%);
    }
    
    .invoice-details-expansion .card {
        background-color: #2a2a2a;
        color: #ffffff;
    }
    
    .invoice-details-expansion .form-control {
        background-color: #2a2a2a;
        border-color: #404040;
        color: #ffffff;
    }
    
    .invoice-details-expansion .table thead th {
        background-color: #404040;
        color: #ffffff;
    }
}
</style>
<!--end::Row Expansion Styles-->
