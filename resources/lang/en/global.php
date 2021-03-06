<?php

return [

    'user-management' => [
        'title' => 'User Management',
        'created_at' => 'Time',
        'fields' => [
        ],
    ],

    'permissions' => [
        'title' => 'Permissions',
        'created_at' => 'Time',
        'fields' => [
            'title' => 'Title',
            'name' => 'Name',
            'parent_id' => 'Parent Permission',
            'actions' => 'Actions',
        ],
    ],

    'settings' => [
        'title' => 'Global Settings',
        'created_at' => 'Time',
        'fields' => [
            'name' => 'Name',
            'data' => 'Data',
            'actions' => 'Actions'
        ],
    ],

    'load_sheets' => [
        'title' => 'Load Sheets',
        'created_at' => 'Time',
        'fields' => [
            'load_sheet_id' => 'Sheet ID',
            'total_packets' => 'Total Packets',
            'created_at' => 'Created At',
            'actions' => 'Actions'
        ],
    ],

    'roles' => [
        'title' => 'Roles',
        'created_at' => 'Time',
        'fields' => [
            'name' => 'Name',
            'commission' => 'Commission',
            'permission' => 'Permissions',
            'actions' => 'Actions',
        ],
    ],

    'users' => [
        'title' => 'Users',
        'created_at' => 'Time',
        'fields' => [
            'name' => 'Name',
            'email' => 'Email',
            'password' => 'Password',
            'change_password' => 'Change Password',
            'roles' => 'Roles',
            'commission' => 'Commission',
            'remember-token' => 'Remember token',
            'actions' => 'Actions',
            'phone'=>'Phone',
            'gender'=>'Gender',
            'locations'=>'Center',
            'created_at' =>'Created at',
        ],
    ],

    'user_types' => [
        'title' => 'User Type',
        'created_at' => 'Time',
        'fields' => [
            'name' => 'Name',
            'type' => 'Type',
            'password' => 'Password',
            'actions' => 'Actions',
        ],
    ],

    'logs' => [
        'title' => 'Logs',
        'created_at' => 'Time',
        'fields' => [
            'id'=>'Id',
            'datetime' => 'Date Time',
            'screen'=>'Screen',
            'user'=>'User',
            'actions' => 'Actions',
        ],
    ],

    'ticket_statuses' => [
        'title' => 'Ticket Statuses',
        'created_at' => 'Time',
        'fields' => [
            'name' => 'Name',
            'show_color' => 'Apply Color',
            'color' => 'Color',
            'slug' => 'Slug',
            'actions' => 'Actions',
            'sort' => 'Sort Statuses'
        ],
    ],

    'general_settings' => [
        'title' => 'General Settings',
        'created_at' => 'Time',
        'fields' => [
            'name' => 'Name',
            'data' => 'Data',
            'slug' => 'Slug',
            'actions' => 'Actions',
            'sort' => 'Sort General Settings'
        ],
    ],

    'leopards_settings' => [
        'management' => 'Leopards',
        'title' => 'Integration',
        'created_at' => 'Time',
        'fields' => [
            'name' => 'Name',
            'data' => 'Data',
            'slug' => 'Slug',
            'actions' => 'Actions',
            'sort' => 'Sort Leopards Settings'
        ],
    ],

    'leopards_cities' => [
        'title' => 'Leopards Cities',
        'created_at' => 'Time',
        'fields' => [
            'name' => 'Name',
            'actions' => 'Actions',
            'sort' => 'Sort Leopards Cities'
        ],
    ],
    'wcc_settings' => [
        'management' => 'Wcc',
        'title' => 'Integration',
        'created_at' => 'Time',
        'fields' => [
            'name' => 'Name',
            'data' => 'Data',
            'slug' => 'Slug',
            'actions' => 'Actions',
            'sort' => 'Sort Wcc Settings'
        ],
    ],

    'wcc_cities' => [
        'title' => 'Wcc Cities',
        'created_at' => 'Time',
        'fields' => [
            'name' => 'Name',
            'actions' => 'Actions',
            'sort' => 'Sort Wcc Cities'
        ],
    ],

    'shopify_webhooks' => [
        'title' => 'Shopify Webhooks',
        'created_at' => 'Time',
        'fields' => [
            'address' => 'Address',
            'topic' => 'Topic',
            'format' => 'Format',
            'actions' => 'Actions',
            'sort' => 'Sort Product Types'
        ],
    ],

    'shopify_tags' => [
        'title' => 'Tags',
        'created_at' => 'Time',
        'fields' => [
            'name' => 'Name',
            'products' => 'Associated Products',
            'actions' => 'Actions'
        ],
    ],

    'shopify_custom_collections' => [
        'title' => 'Custom Collections',
        'created_at' => 'Time',
        'fields' => [
            'name' => 'Name',
            'actions' => 'Actions'
        ],
    ],

    'shopify_products' => [
        'title' => 'Products',
        'created_at' => 'Time',
        'fields' => [
            'image_src' => 'Image',
            'title' => 'Product',
            'inventory' => 'Inventory',
            'product_type' => 'Type',
            'vendor' => 'Vendor',
            'actions' => 'Actions'
        ],
    ],

    'shopify_customers' => [
        'title' => 'Customers',
        'created_at' => 'Time',
        'fields' => [
            'first_name' => 'First Name',
            'last_name' => 'Last name',
            'email' => 'Email',
            'phone' => 'Phone',
            'city' => 'City',
            'province' => 'Province',
            'actions' => 'Actions'
        ],
    ],

    'shopify_orders' => [
        'title' => 'Orders',
        'created_at' => 'Time',
        'fields' => [
            'name' => 'Order',
            'closed_at' => 'Date',
            'customer_name' => 'Customer',
            'customer_email' => 'Customer',
            'financial_status' => 'Payment',
            'fulfillment_status' => 'Fulfillment',
            'tags' => 'Tags',
            'cn_number' => 'CN#',
            'destination_city' => 'City',
            'consignment_address' => 'Address',
            'total_price' => 'Amount',
            'actions' => 'Actions'
        ],
    ],

    'tickets' => [
        'title' => 'Repair Requests',
        'single' => 'Repair Request',
        'created_at' => 'Time',
        'fields' => [
            'ticket_single' => 'Repair',
            'ticket_plural' => 'Repairs',
            'number' => 'Number',
            'customer_name' => 'Customer',
            'serial_number' => 'Serial Number',
            'total_products' => 'Total Products',
            'ticket_status_id' => 'Status',
            'actions' => 'Actions',
            'created_at' => 'Created At'
        ],
    ],

    'shopify_plans' => [
        'title' => 'Shopify Plans',
        'created_at' => 'Time',
        'fields' => [
            'name' => 'Name',
            'price' => 'Price',
            'quota' => 'Quota',
            'actions' => 'Actions',
            'sort' => 'Sort Plans'
        ],
    ],

    'shopify_billings' => [
        'title' => 'Shopify Billings',
        'plans' => 'Choose a Plan',
        'created_at' => 'Time',
        'fields' => [
            'name' => 'Name',
            'price' => 'Price',
            'quota' => 'Quota',
            'actions' => 'Actions',
        ],
    ],

    'shippers' => [
        'title' => 'Shippers',
        'created_at' => 'Time',
        'fields' => [
            'city_id' => 'City',
            'name' => 'Name',
            'email' => 'Email',
            'phone' => 'Phone',
            'address' => 'Address',
            'actions' => 'Actions',
        ],
    ],

    'consignees' => [
        'title' => 'Consignees',
        'created_at' => 'Time',
        'fields' => [
            'city_id' => 'City',
            'name' => 'Name',
            'email' => 'Email',
            'phone' => 'Phone',
            'address' => 'Address',
            'actions' => 'Actions',
        ],
    ],

    'booked_packets' => [
        'api_title' => 'API Booked Packets',
        'title' => 'Booked Packets',
        'created_at' => 'Time',
        'fields' => [
            'status' => 'Status',
            'order_id' => 'Order ID',
            'shipment_type_id' => 'Shipment Type',
            'cn_number' => 'CN#',
            'origin_city' => 'From',
            'destination_city' => 'To',
            'shipper_name' => 'Shipper',
            'consignee_name' => 'Consignee',
            'consignee_phone' => 'Phone',
            'consignee_email' => 'Email',
            'booking_date' => 'Booking',
            'collect_amount' => 'Amount (PKR)',
            'invoice_number' => 'Invoice #',
            'invoice_date' => 'Invoice Date',
            'actions' => 'Actions',
        ],
    ],

    'app_create' => 'Create',
    'app_Submit' => 'Submit',
    'app_detail' => 'Details',
    'app_inactive' => 'Inactive',
    'app_active' => 'Active',
    'app_save' => 'Save',
    'app_edit' => 'Edit',
    'app_display' => 'Display',
    'app_pdf' => 'Print',
    'app_preview' => 'Preview',
    'app_submit' => 'Submit',
    'app_detail' => 'Details',
    'app_warning'=>'Warning',
    'app_view' => 'View',
    'app_update' => 'Update',
    'app_list' => 'List',
    'app_no_entries_in_table' => 'No entries in table',
    'custom_controller_index' => 'Custom controller index.',
    'app_logout' => 'Logout',
    'app_add_new' => 'Add New',
    'app_sort' => 'Sort Data',
    'app_back' => 'Back',
    'app_are_you_sure' => 'Are you sure?',
    'app_back_to_list' => 'Back to list',
    'app_dashboard' => 'Dashboard',
    'app_instructions' => 'Setup Instructions',
    'our_apps' => 'Our Apps',
    'app_delete' => 'Delete',
    'app_cancel' => 'Cancel',
    'app_arrange' => 'Arrange List',
    'global_title' => 'WCC | World Commerce Courier',

];