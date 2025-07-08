<?php

return [
    // General
    'title' => 'Permission Management',
    'subtitle' => 'Manage system permissions',
    'permission' => 'Permission',
    'permissions' => 'Permissions',
    'permission_management' => 'Permission Management',
    'permission_list' => 'Permission List',
    'permission_details' => 'Permission Details',
    'permission_roles' => 'Roles with Permission',

    // Actions
    'add_permission' => 'Add Permission',
    'create_permission' => 'Create New Permission',
    'edit_permission' => 'Edit Permission',
    'update_permission' => 'Update Permission',
    'delete_permission' => 'Delete Permission',
    'view_permission' => 'View Permission',
    'assign_to_roles' => 'Assign to Roles',
    'remove_from_roles' => 'Remove from Roles',
    'toggle_status' => 'Toggle Status',
    'bulk_delete' => 'Bulk Delete',
    'generate_permissions' => 'Generate Permissions',
    'export_permissions' => 'Export Permissions',

    // Fields
    'name' => 'Permission Name',
    'display_name' => 'Display Name',
    'module' => 'Module',
    'action' => 'Action',
    'description' => 'Description',
    'status' => 'Status',
    'is_active' => 'Active',
    'sort_order' => 'Sort Order',
    'roles_count' => 'Roles Count',
    'users_count' => 'Users Count',
    'created_at' => 'Created At',
    'updated_at' => 'Updated At',

    // Modules
    'modules' => [
        'pages' => 'Pages',
        'products' => 'Products',
        'categories' => 'Categories',
        'orders' => 'Orders',
        'customers' => 'Customers',
        'inventory' => 'Inventory',
        'transactions' => 'Transactions',
        'suppliers' => 'Suppliers',
        'branches' => 'Branches',
        'branch_shops' => 'Branch Shops',
        'users' => 'Users',
        'roles' => 'Roles',
        'settings' => 'Settings',
        'reports' => 'Reports',
        'notifications' => 'Notifications',
    ],

    // Actions
    'actions' => [
        'view' => 'View',
        'create' => 'Create',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'export' => 'Export',
        'import' => 'Import',
        'manage' => 'Manage',
    ],

    // Status
    'active' => 'Active',
    'inactive' => 'Inactive',
    'enabled' => 'Enabled',
    'disabled' => 'Disabled',

    // Placeholders
    'search_permissions' => 'Search permissions...',
    'select_permission' => 'Select Permission',
    'select_module' => 'Select Module',
    'select_action' => 'Select Action',
    'enter_permission_name' => 'Enter permission name',
    'enter_display_name' => 'Enter display name',
    'enter_description' => 'Enter permission description',

    // Messages
    'messages' => [
        'created_success' => 'Permission created successfully!',
        'created_error' => 'Error occurred while creating permission!',
        'updated_success' => 'Permission updated successfully!',
        'updated_error' => 'Error occurred while updating permission!',
        'deleted_success' => 'Permission deleted successfully!',
        'deleted_error' => 'Error occurred while deleting permission!',
        'status_updated' => 'Status updated successfully!',
        'status_error' => 'Error occurred while updating status!',
        'bulk_delete_success' => 'Bulk delete completed successfully!',
        'bulk_delete_error' => 'Error occurred during bulk delete!',
        'generate_success' => 'Permissions generated successfully!',
        'generate_error' => 'Error occurred while generating permissions!',
        'not_found' => 'Permission not found!',
        'cannot_delete_assigned_permission' => 'Cannot delete assigned permission!',
        'module_error' => 'Error occurred while fetching module permissions!',
        'permission_exists' => 'Permission already exists!',
    ],

    // Validation
    'validation' => [
        'name_required' => 'Permission name is required',
        'name_unique' => 'Permission name already exists',
        'name_max' => 'Permission name must not exceed 255 characters',
        'display_name_required' => 'Display name is required',
        'display_name_max' => 'Display name must not exceed 255 characters',
        'module_required' => 'Module is required',
        'module_max' => 'Module must not exceed 255 characters',
        'action_required' => 'Action is required',
        'action_max' => 'Action must not exceed 255 characters',
        'description_max' => 'Description must not exceed 1000 characters',
        'sort_order_numeric' => 'Sort order must be numeric',
    ],

    // Confirmations
    'confirmations' => [
        'delete_permission' => 'Are you sure you want to delete this permission?',
        'delete_permissions' => 'Are you sure you want to delete selected permissions?',
        'toggle_status' => 'Are you sure you want to toggle this permission status?',
        'generate_permissions' => 'Are you sure you want to generate permissions for this module?',
        'assign_to_role' => 'Are you sure you want to assign this permission to role?',
        'remove_from_role' => 'Are you sure you want to remove this permission from role?',
    ],

    // Tooltips
    'tooltips' => [
        'add_permission' => 'Add new permission',
        'edit_permission' => 'Edit permission',
        'delete_permission' => 'Delete permission',
        'view_roles' => 'View roles with this permission',
        'toggle_status' => 'Toggle status',
        'generate_permissions' => 'Generate permissions for module',
        'active_permission' => 'Active permission',
        'inactive_permission' => 'Inactive permission',
        'assigned_permission' => 'Assigned permission',
    ],

    // Filters
    'filters' => [
        'all_permissions' => 'All Permissions',
        'active_permissions' => 'Active Permissions',
        'inactive_permissions' => 'Inactive Permissions',
        'assigned_permissions' => 'Assigned Permissions',
        'unassigned_permissions' => 'Unassigned Permissions',
        'filter_by_module' => 'Filter by Module',
        'filter_by_action' => 'Filter by Action',
        'filter_by_status' => 'Filter by Status',
        'sort_by_name' => 'Sort by Name',
        'sort_by_module' => 'Sort by Module',
        'sort_by_action' => 'Sort by Action',
        'sort_by_created' => 'Sort by Created Date',
    ],

    // Statistics
    'statistics' => [
        'total_permissions' => 'Total Permissions',
        'active_permissions' => 'Active Permissions',
        'inactive_permissions' => 'Inactive Permissions',
        'permissions_by_module' => 'Permissions by Module',
        'permissions_by_action' => 'Permissions by Action',
        'assigned_permissions' => 'Assigned Permissions',
        'unassigned_permissions' => 'Unassigned Permissions',
    ],

    // Generator
    'generator' => [
        'title' => 'Permission Generator',
        'description' => 'Generate permissions automatically for selected module and actions',
        'select_module' => 'Select Module',
        'select_actions' => 'Select Actions',
        'generate_button' => 'Generate Permissions',
        'preview' => 'Preview',
        'will_create' => 'Will create the following permissions:',
        'already_exists' => 'Already exists:',
        'success_message' => 'Created {count} permissions for module {module}',
    ],

    // Help Text
    'help' => [
        'permission_name' => 'Unique permission name, usually in module.action format',
        'display_name' => 'User-friendly display name',
        'module' => 'Module that this permission applies to',
        'action' => 'Specific action that this permission allows',
        'description' => 'Detailed description of this permission',
        'status' => 'Only active permissions can be assigned to roles',
        'sort_order' => 'Display order in lists (lower numbers appear first)',
        'auto_generate' => 'System can automatically generate permission name from module and action',
    ],

    // Breadcrumbs
    'breadcrumbs' => [
        'home' => 'Home',
        'permissions' => 'Permissions',
        'create' => 'Create',
        'edit' => 'Edit',
        'view' => 'View',
        'generate' => 'Generate',
    ],

    // Tabs
    'tabs' => [
        'general' => 'General',
        'roles' => 'Roles',
        'users' => 'Users',
        'history' => 'History',
    ],

    // Empty States
    'empty_states' => [
        'no_permissions' => 'No permissions found',
        'no_roles' => 'No roles found',
        'no_users' => 'No users found',
        'no_search_results' => 'No matching results found',
        'create_first_permission' => 'Create your first permission',
        'no_module_permissions' => 'No permissions found for this module',
    ],

    // Permission Groups
    'groups' => [
        'content_management' => 'Content Management',
        'user_management' => 'User Management',
        'system_management' => 'System Management',
        'sales_management' => 'Sales Management',
        'inventory_management' => 'Inventory Management',
        'report_management' => 'Report Management',
    ],
];
