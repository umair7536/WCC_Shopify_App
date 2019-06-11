<?phpreturn [    'user-management' => [        'title' => 'User Management',        'created_at' => 'Time',        'fields' => [        ],    ],    'permissions' => [        'title' => 'Permissions',        'created_at' => 'Time',        'fields' => [            'title' => 'Title',            'name' => 'Name',            'parent_id' => 'Parent Permission',            'actions' => 'Actions',        ],    ],    'settings' => [        'title' => 'Global Settings',        'created_at' => 'Time',        'fields' => [            'name' => 'Name',            'data' => 'Data',            'actions' => 'Actions'        ],    ],    'roles' => [        'title' => 'Roles',        'created_at' => 'Time',        'fields' => [            'name' => 'Name',            'commission' => 'Commission',            'permission' => 'Permissions',            'actions' => 'Actions',        ],    ],    'users' => [        'title' => 'Users',        'created_at' => 'Time',        'fields' => [            'name' => 'Name',            'email' => 'Email',            'password' => 'Password',            'change_password' => 'Change Password',            'roles' => 'Roles',            'commission' => 'Commission',            'remember-token' => 'Remember token',            'actions' => 'Actions',            'phone'=>'Phone',            'gender'=>'Gender',            'locations'=>'Center',            'created_at' =>'Created at',        ],    ],    'user_types' => [        'title' => 'User Type',        'created_at' => 'Time',        'fields' => [            'name' => 'Name',            'type' => 'Type',            'password' => 'Password',            'actions' => 'Actions',        ],    ],    'logs' => [        'title' => 'Logs',        'created_at' => 'Time',        'fields' => [            'id'=>'Id',            'datetime' => 'Date Time',            'screen'=>'Screen',            'user'=>'User',            'actions' => 'Actions',        ],    ],    'ticket_statuses' => [        'title' => 'Ticket Statuses',        'created_at' => 'Time',        'fields' => [            'name' => 'Name',            'slug' => 'Slug',            'actions' => 'Actions',            'sort' => 'Sort Statuses'        ],    ],    'shopify_webhooks' => [        'title' => 'Shopify Webhooks',        'created_at' => 'Time',        'fields' => [            'address' => 'Address',            'topic' => 'Topic',            'format' => 'Format',            'actions' => 'Actions',            'sort' => 'Sort Product Types'        ],    ],    'shopify_tags' => [        'title' => 'Tags',        'created_at' => 'Time',        'fields' => [            'name' => 'Name',            'products' => 'Associated Products',            'actions' => 'Actions'        ],    ],    'shopify_custom_collections' => [        'title' => 'Custom Collections',        'created_at' => 'Time',        'fields' => [            'name' => 'Name',            'actions' => 'Actions'        ],    ],    'shopify_products' => [        'title' => 'Products',        'created_at' => 'Time',        'fields' => [            'image_src' => 'Image',            'title' => 'Product',            'inventory' => 'Inventory',            'product_type' => 'Type',            'vendor' => 'Vendor',            'actions' => 'Actions'        ],    ],    'shopify_customers' => [        'title' => 'Customers',        'created_at' => 'Time',        'fields' => [            'first_name' => 'First Name',            'last_name' => 'Last name',            'email' => 'Email',            'phone' => 'Phone',            'city' => 'City',            'province' => 'Province',            'actions' => 'Actions'        ],    ],    'tickets' => [        'title' => 'Service Requests',        'created_at' => 'Time',        'fields' => [            'number' => 'Number',            'customer_name' => 'Customer',            'total_products' => 'Total Products',            'ticket_status_id' => 'Status',            'actions' => 'Actions',            'created_at' => 'Created At'        ],    ],    'app_create' => 'Create',    'app_Submit' => 'Submit',    'app_detail' => 'Detail',    'app_inactive' => 'Inactive',    'app_active' => 'Active',    'app_save' => 'Save',    'app_edit' => 'Edit',    'app_display' => 'Display',    'app_pdf' => 'Print',    'app_preview' => 'Preview',    'app_submit' => 'Submit',    'app_detail' => 'Details',    'app_warning'=>'Warning',    'app_view' => 'View',    'app_update' => 'Update',    'app_list' => 'List',    'app_no_entries_in_table' => 'No entries in table',    'custom_controller_index' => 'Custom controller index.',    'app_logout' => 'Logout',    'app_add_new' => 'Add New',    'app_sort' => 'Sort Data',    'app_back' => 'Back',    'app_are_you_sure' => 'Are you sure?',    'app_back_to_list' => 'Back to list',    'app_dashboard' => 'Dashboard',    'app_delete' => 'Delete',    'app_cancel' => 'Cancel',    'app_arrange' => 'Arrange List',    'global_title' => 'Developify Apps',];