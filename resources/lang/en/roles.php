<?php

return [
    // General
    'title' => 'Role Management',
    'subtitle' => 'Manage user roles and permissions',
    'role' => 'Role',
    'roles' => 'Roles',
    'role_management' => 'Role Management',
    'role_list' => 'Role List',
    'role_details' => 'Role Details',
    'role_permissions' => 'Role Permissions',

    // Actions
    'add_role' => 'Add Role',
    'create_role' => 'Create New Role',
    'edit_role' => 'Edit Role',
    'update_role' => 'Update Role',
    'delete_role' => 'Delete Role',
    'view_role' => 'View Role',
    'assign_permissions' => 'Assign Permissions',
    'manage_permissions' => 'Manage Permissions',
    'toggle_status' => 'Toggle Status',
    'bulk_delete' => 'Bulk Delete',
    'export_roles' => 'Export Roles',

    // Fields
    'name' => 'Role Name',
    'display_name' => 'Display Name',
    'description' => 'Description',
    'permissions' => 'Permissions',
    'status' => 'Status',
    'is_active' => 'Active',
    'sort_order' => 'Sort Order',
    'settings' => 'Settings',
    'users_count' => 'Users Count',
    'permissions_count' => 'Permissions Count',
    'created_at' => 'Created At',
    'updated_at' => 'Updated At',

    // Role Types
    'role_types' => [
        'admin' => 'Administrator',
        'shop_manager' => 'Shop Manager',
        'staff' => 'Staff',
        'partime' => 'Part-time Staff',
    ],

    // Status
    'active' => 'Active',
    'inactive' => 'Inactive',
    'enabled' => 'Enabled',
    'disabled' => 'Disabled',

    // Placeholders
    'search_roles' => 'Search roles...',
    'select_role' => 'Select Role',
    'select_permissions' => 'Select Permissions',
    'enter_role_name' => 'Enter role name',
    'enter_display_name' => 'Enter display name',
    'enter_description' => 'Enter role description',

    // Messages
    'messages' => [
        'created_success' => 'Role created successfully!',
        'created_error' => 'Error occurred while creating role!',
        'updated_success' => 'Role updated successfully!',
        'updated_error' => 'Error occurred while updating role!',
        'deleted_success' => 'Role deleted successfully!',
        'deleted_error' => 'Error occurred while deleting role!',
        'status_updated' => 'Status updated successfully!',
        'status_error' => 'Error occurred while updating status!',
        'permissions_updated' => 'Permissions updated successfully!',
        'permissions_error' => 'Error occurred while updating permissions!',
        'bulk_delete_success' => 'Bulk delete completed successfully!',
        'bulk_delete_error' => 'Error occurred during bulk delete!',
        'not_found' => 'Role not found!',
        'cannot_delete_system_role' => 'Cannot delete system role!',
        'cannot_delete_role_with_users' => 'Cannot delete role with assigned users!',
        'cannot_modify_system_role' => 'Cannot modify system role!',
        'cannot_deactivate_admin_role' => 'Cannot deactivate admin role!',
        'role_assigned_success' => 'Role assigned successfully!',
        'role_removed_success' => 'Role removed successfully!',
    ],

    // Validation
    'validation' => [
        'name_required' => 'Role name is required',
        'name_unique' => 'Role name already exists',
        'name_max' => 'Role name must not exceed 255 characters',
        'display_name_required' => 'Display name is required',
        'display_name_max' => 'Display name must not exceed 255 characters',
        'description_max' => 'Description must not exceed 1000 characters',
        'permissions_array' => 'Permissions must be an array',
        'permission_exists' => 'Permission does not exist',
        'sort_order_numeric' => 'Sort order must be numeric',
    ],

    // Confirmations
    'confirmations' => [
        'delete_role' => 'Are you sure you want to delete this role?',
        'delete_roles' => 'Are you sure you want to delete selected roles?',
        'toggle_status' => 'Are you sure you want to toggle this role status?',
        'remove_permission' => 'Are you sure you want to remove this permission?',
        'assign_role' => 'Are you sure you want to assign this role to user?',
        'remove_role' => 'Are you sure you want to remove this role from user?',
    ],

    // Tooltips
    'tooltips' => [
        'add_role' => 'Add new role',
        'edit_role' => 'Edit role',
        'delete_role' => 'Delete role',
        'view_permissions' => 'View permissions',
        'toggle_status' => 'Toggle status',
        'system_role' => 'System role - Cannot be deleted',
        'active_role' => 'Active role',
        'inactive_role' => 'Inactive role',
    ],

    // Filters
    'filters' => [
        'all_roles' => 'All Roles',
        'active_roles' => 'Active Roles',
        'inactive_roles' => 'Inactive Roles',
        'system_roles' => 'System Roles',
        'custom_roles' => 'Custom Roles',
        'filter_by_status' => 'Filter by Status',
        'filter_by_type' => 'Filter by Type',
        'sort_by_name' => 'Sort by Name',
        'sort_by_created' => 'Sort by Created Date',
        'sort_by_users' => 'Sort by Users Count',
    ],

    // Statistics
    'statistics' => [
        'total_roles' => 'Total Roles',
        'active_roles' => 'Active Roles',
        'inactive_roles' => 'Inactive Roles',
        'roles_with_users' => 'Roles with Users',
        'roles_without_users' => 'Roles without Users',
        'system_roles' => 'System Roles',
        'custom_roles' => 'Custom Roles',
    ],

    // Permissions by Module
    'permission_modules' => [
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

    // Permission Actions
    'permission_actions' => [
        'view' => 'View',
        'create' => 'Create',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'export' => 'Export',
        'import' => 'Import',
        'manage' => 'Manage',
    ],

    // Help Text
    'help' => [
        'role_name' => 'Unique role name, only letters, numbers and underscores',
        'display_name' => 'User-friendly display name',
        'description' => 'Brief description of the role and its functions',
        'permissions' => 'Select permissions that this role is allowed to perform',
        'status' => 'Only active roles can be assigned to users',
        'sort_order' => 'Display order in lists (lower numbers appear first)',
        'system_role' => 'System roles cannot be deleted or modified',
    ],

    // Breadcrumbs
    'breadcrumbs' => [
        'home' => 'Home',
        'roles' => 'Roles',
        'create' => 'Create',
        'edit' => 'Edit',
        'view' => 'View',
        'permissions' => 'Permissions',
    ],

    // Tabs
    'tabs' => [
        'general' => 'General',
        'permissions' => 'Permissions',
        'users' => 'Users',
        'settings' => 'Settings',
        'history' => 'History',
    ],

    // Empty States
    'empty_states' => [
        'no_roles' => 'No roles found',
        'no_permissions' => 'No permissions found',
        'no_users' => 'No users found',
        'no_search_results' => 'No matching results found',
        'create_first_role' => 'Create your first role',
    ],
];
