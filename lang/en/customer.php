<?php

return [
    // General
    'customer' => 'Customer',
    'customers' => 'Customers',
    'customer_management' => 'Customer Management',
    'customer_list' => 'Customer List',
    'customer_details' => 'Customer Details',
    'new_customer' => 'New Customer',
    'create_customer' => 'Create Customer',
    'edit_customer' => 'Edit Customer',
    'delete_customer' => 'Delete Customer',

    // Fields
    'name' => 'Full Name',
    'email' => 'Email',
    'phone' => 'Phone Number',
    'address' => 'Address',
    'city' => 'City',
    'district' => 'District',
    'ward' => 'Ward',
    'postal_code' => 'Postal Code',
    'customer_type' => 'Customer Type',
    'status' => 'Status',
    'date_of_birth' => 'Date of Birth',
    'gender' => 'Gender',
    'notes' => 'Notes',
    'avatar' => 'Avatar',
    'created_at' => 'Created At',
    'updated_at' => 'Updated At',

    // Customer Types
    'individual' => 'Individual',
    'business' => 'Business',

    // Status
    'active' => 'Active',
    'inactive' => 'Inactive',

    // Gender
    'male' => 'Male',
    'female' => 'Female',
    'other' => 'Other',

    // Statistics
    'total_customers' => 'Total Customers',
    'active_customers' => 'Active Customers',
    'inactive_customers' => 'Inactive Customers',
    'individual_customers' => 'Individual Customers',
    'business_customers' => 'Business Customers',
    'new_customers_this_month' => 'New Customers This Month',
    'customers_with_orders' => 'Customers with Orders',
    'top_customers' => 'VIP Customers',
    'customer_statistics' => 'Customer Statistics',

    // Order Statistics
    'total_orders' => 'Total Orders',
    'total_spent' => 'Total Spent',
    'avg_order_value' => 'Avg Order Value',
    'last_order_date' => 'Last Order',
    'first_order_date' => 'First Order',
    'recent_orders' => 'Recent Orders',
    'order_history' => 'Order History',
    'monthly_orders' => 'Monthly Orders',

    // Actions
    'add_customer' => 'Add Customer',
    'save_customer' => 'Save Customer',
    'update_customer' => 'Update Customer',
    'view_customer' => 'View Customer',
    'edit_customer' => 'Edit Customer',
    'delete_customer' => 'Delete Customer',
    'view_orders' => 'View Orders',
    'create_order' => 'Create Order',
    'send_email' => 'Send Email',
    'call_customer' => 'Call Customer',

    // Filters
    'filter_by_status' => 'Filter by Status',
    'filter_by_type' => 'Filter by Type',
    'filter_by_city' => 'Filter by City',
    'all_customers' => 'All Customers',
    'all_types' => 'All Types',
    'all_cities' => 'All Cities',

    // Placeholders
    'enter_name' => 'Enter full name',
    'enter_email' => 'Enter email address',
    'enter_phone' => 'Enter phone number',
    'enter_address' => 'Enter address',
    'enter_city' => 'Enter city',
    'enter_district' => 'Enter district',
    'enter_ward' => 'Enter ward',
    'enter_postal_code' => 'Enter postal code',
    'select_type' => 'Select customer type',
    'select_status' => 'Select status',
    'select_gender' => 'Select gender',
    'enter_notes' => 'Enter notes',
    'choose_avatar' => 'Choose avatar',
    'search_customers' => 'Search customers...',

    // Messages
    'no_customers' => 'No customers found',
    'customer_created' => 'Customer created successfully',
    'customer_updated' => 'Customer updated successfully',
    'customer_deleted' => 'Customer deleted successfully',
    'no_orders' => 'Customer has no orders yet',

    // Validation
    'name_required' => 'Full name is required',
    'name_max' => 'Full name may not be greater than 255 characters',
    'email_required' => 'Email is required',
    'email_invalid' => 'Email is invalid',
    'email_unique' => 'Email has already been taken',
    'phone_max' => 'Phone number may not be greater than 20 characters',
    'address_max' => 'Address may not be greater than 500 characters',
    'type_required' => 'Customer type is required',
    'status_required' => 'Status is required',
    'date_of_birth_date' => 'Date of birth must be a valid date',
    'notes_max' => 'Notes may not be greater than 1000 characters',
    'avatar_image' => 'File must be an image',
    'avatar_max' => 'Image size may not be greater than 2MB',

    // Errors
    'validation_failed' => 'Validation failed',
    'create_failed' => 'Failed to create customer',
    'update_failed' => 'Failed to update customer',
    'delete_failed' => 'Failed to delete customer',
    'has_orders' => 'Cannot delete customer with orders',
    'not_found' => 'Customer not found',

    // Success messages
    'created_successfully' => 'Customer created successfully',
    'updated_successfully' => 'Customer updated successfully',
    'deleted_successfully' => 'Customer deleted successfully',

    // Confirmations
    'confirm_delete' => 'Are you sure you want to delete this customer?',
    'confirm_delete_with_orders' => 'This customer has {count} orders. Are you sure you want to delete?',
    'confirm_status_change' => 'Are you sure you want to change this customer status?',

    // Tooltips
    'name_tooltip' => 'Full name of the customer',
    'email_tooltip' => 'Email address for contact and notifications',
    'phone_tooltip' => 'Phone number for contact',
    'address_tooltip' => 'Customer delivery address',
    'type_tooltip' => 'Customer classification: individual or business',
    'status_tooltip' => 'Customer activity status',
    'avatar_tooltip' => 'Customer profile picture',

    // Customer Profile
    'customer_profile' => 'Customer Profile',
    'personal_info' => 'Personal Information',
    'contact_info' => 'Contact Information',
    'address_info' => 'Address Information',
    'order_summary' => 'Order Summary',
    'activity_timeline' => 'Activity Timeline',

    // Customer Segments
    'customer_segments' => 'Customer Segments',
    'vip_customers' => 'VIP Customers',
    'loyal_customers' => 'Loyal Customers',
    'new_customers' => 'New Customers',
    'inactive_customers_segment' => 'Inactive Customers',

    // Import/Export
    'import_customers' => 'Import Customers',
    'export_customers' => 'Export Customers',
    'import_template' => 'Download Import Template',
    'export_selected' => 'Export Selected Customers',

    // Bulk actions
    'bulk_actions' => 'Bulk Actions',
    'select_all' => 'Select All',
    'deselect_all' => 'Deselect All',
    'bulk_activate' => 'Activate',
    'bulk_deactivate' => 'Deactivate',
    'bulk_delete' => 'Bulk Delete',
    'bulk_export' => 'Bulk Export',
    'selected_count' => 'selected',

    // Communication
    'communication' => 'Communication',
    'send_notification' => 'Send Notification',
    'email_history' => 'Email History',
    'sms_history' => 'SMS History',
    'call_history' => 'Call History',
    'last_contact' => 'Last Contact',

    // Preferences
    'preferences' => 'Preferences',
    'email_notifications' => 'Email Notifications',
    'sms_notifications' => 'SMS Notifications',
    'marketing_emails' => 'Marketing Emails',
    'newsletter' => 'Newsletter',

    // Tags
    'tags' => 'Tags',
    'add_tag' => 'Add Tag',
    'remove_tag' => 'Remove Tag',
    'popular_tags' => 'Popular Tags',

    // Notes
    'customer_notes' => 'Customer Notes',
    'add_note' => 'Add Note',
    'edit_note' => 'Edit Note',
    'delete_note' => 'Delete Note',
    'note_date' => 'Note Date',
    'note_author' => 'Author',

    // Advanced
    'advanced_search' => 'Advanced Search',
    'custom_fields' => 'Custom Fields',
    'customer_groups' => 'Customer Groups',
    'loyalty_program' => 'Loyalty Program',
    'credit_limit' => 'Credit Limit',
    'payment_terms' => 'Payment Terms',

    // Reports
    'customer_reports' => 'Customer Reports',
    'customer_analysis' => 'Customer Analysis',
    'customer_lifetime_value' => 'Customer Lifetime Value',
    'customer_acquisition' => 'Customer Acquisition',
    'customer_retention' => 'Customer Retention',
    'churn_rate' => 'Churn Rate',

    // Buttons
    'save' => 'Save',
    'save_and_continue' => 'Save and Continue',
    'save_and_new' => 'Save and New',
    'cancel' => 'Cancel',
    'back' => 'Back',
    'reset' => 'Reset',
    'preview' => 'Preview',
    'print' => 'Print',
    'download' => 'Download',

    // Navigation
    'previous_customer' => 'Previous Customer',
    'next_customer' => 'Next Customer',
    'customer_list_link' => 'Customer List',

    // Time
    'never' => 'Never',
    'today' => 'Today',
    'yesterday' => 'Yesterday',
    'this_week' => 'This Week',
    'this_month' => 'This Month',
    'this_year' => 'This Year',
];
