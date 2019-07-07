<?php

Route::get('/', function () { return redirect('/admin/home'); });

//use Route for register:R
Auth::routes();


// Authentication Routes...

$this->get('register', 'Auth\RegisterController@index')->name('auth.register');

$this->get('login', 'Auth\LoginController@showLoginForm')->name('auth.login');
$this->post('login', 'Auth\LoginController@login')->name('auth.login');

$this->post('logout', 'Auth\LoginController@logout')->name('auth.logout');

// Check Session
Route::get('check-session', 'Auth\LoginController@checkSession')->name('check_session');


Route::get('shop', 'Admin\ShopifyController@index')->name('shopify.shop');

Route::post('install', 'Admin\ShopifyController@install')->name('shopify.install');
Route::get('redirect', 'Admin\ShopifyController@redirect')->name('shopify.redirect');
Route::get('shopify-login', 'Admin\ShopifyController@login')->name('shopify.login');
Route::get('verify', 'Admin\ShopifyController@verifyShopify')->name('shopify.verify');


// Change Password Routes...
$this->get('change_password', 'Auth\ChangePasswordController@showChangePasswordForm')->name('auth.change_password');
$this->patch('change_password', 'Auth\ChangePasswordController@changePassword')->name('auth.change_password');
$this->patch('relogin/{id}', 'Auth\ChangePasswordController@relogin')->name('auth.relogin');

// Password Reset Routes...
$this->get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('auth.password.reset');
$this->post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('auth.password.reset');
$this->get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');
$this->post('password/reset', 'Auth\ResetPasswordController@reset')->name('auth.password.reset');

Route::group(['middleware' => ['auth'], 'prefix' => 'admin', 'as' => 'admin.'], function () {
    Route::get('/home', ['uses' => 'HomeController@index', 'as' => 'home']);
    Route::get('/run', ['uses' => 'HomeController@runQueue', 'as' => 'run_queue']);
    Route::get('/variant', ['uses' => 'HomeController@runVariantsQueue', 'as' => 'run_variant']);
    // Permissions Routes
    Route::post('permissions/datatable', ['uses' => 'Admin\PermissionsController@datatable', 'as' => 'permissions.datatable']);
    Route::resource('permissions', 'Admin\PermissionsController');
    Route::post('permissions_mass_destroy', ['uses' => 'Admin\PermissionsController@massDestroy', 'as' => 'permissions.mass_destroy']);

    // Roles Routes
    Route::post('roles/datatable', ['uses' => 'Admin\RolesController@datatable', 'as' => 'roles.datatable']);
    Route::resource('roles', 'Admin\RolesController');
    Route::post('roles_mass_destroy', ['uses' => 'Admin\RolesController@massDestroy', 'as' => 'roles.mass_destroy']);

    // Users Routes
    Route::get('users/password/{id}', ['uses' => 'Admin\UsersController@changePassword', 'as' => 'users.change_password']);
    Route::patch('users/password', ['uses' => 'Admin\UsersController@savePassword', 'as' => 'users.save_password']);
    Route::post('users/datatable', ['uses' => 'Admin\UsersController@datatable', 'as' => 'users.datatable']);
    Route::resource('users', 'Admin\UsersController');
    Route::post('users_mass_destroy', ['uses' => 'Admin\UsersController@massDestroy', 'as' => 'users.mass_destroy']);

    //user Type route start
    Route::post('user_types/datatable', ['uses' => 'Admin\UserTypesController@datatable', 'as' => 'user_types.datatable']);
    Route::patch('user_types/active/{id}', ['uses' => 'Admin\UserTypesController@active', 'as' => 'user_types.active']);
    Route::patch('user_types/inactive/{id}', ['uses' => 'Admin\UserTypesController@inactive', 'as' => 'user_types.inactive']);
    Route::resource('user_types', 'Admin\UserTypesController');
    //user type route end

    // Settings
    Route::post('settings/datatable', ['uses' => 'Admin\SettingsController@datatable', 'as' => 'settings.datatable']);
    Route::patch('settings/active/{id}', ['uses' => 'Admin\SettingsController@active', 'as' => 'settings.active']);
    Route::patch('settings/inactive/{id}', ['uses' => 'Admin\SettingsController@inactive', 'as' => 'settings.inactive']);
    Route::resource('settings', 'Admin\SettingsController');

    //Logs Routes
    Route::post('logs/datatable', ['uses' => 'Admin\LogsController@datatable', 'as' => 'logs.datatable']);
    Route::resource('logs', 'Admin\LogsController');

    // TicketStatuses Routes start
    Route::post('ticket_statuses/datatable', ['uses' => 'Admin\TicketStatusesController@datatable', 'as' => 'ticket_statuses.datatable']);
    Route::patch('ticket_statuses/active/{id}', ['uses' => 'Admin\TicketStatusesController@active', 'as' => 'ticket_statuses.active']);
    Route::patch('ticket_statuses/inactive/{id}', ['uses' => 'Admin\TicketStatusesController@inactive', 'as' => 'ticket_statuses.inactive']);
    Route::get('ticket_statuses/sort', ['uses' => 'Admin\TicketStatusesController@sortorder', 'as' => 'ticket_statuses.sort']);
    Route::get('ticket_statuses/sort-save', ['uses' => 'Admin\TicketStatusesController@sortorder_save', 'as' => 'ticket_statuses.sort_save']);
    Route::resource('ticket_statuses', 'Admin\TicketStatusesController');
    // TicketStatuses Routes end

    // ShopifyWebhooks Routes start
    Route::post('shopify_webhooks/datatable', ['uses' => 'Admin\ShopifyWebhooksController@datatable', 'as' => 'shopify_webhooks.datatable']);
    Route::post('shopify_webhooks/sync', ['uses' => 'Admin\ShopifyWebhooksController@sync', 'as' => 'shopify_webhooks.sync']);
    Route::resource('shopify_webhooks', 'Admin\ShopifyWebhooksController');
    // ShopifyWebhooks Routes end

    // ShopifyTags Routes start
    Route::post('shopify_tags/datatable', ['uses' => 'Admin\ShopifyTagsController@datatable', 'as' => 'shopify_tags.datatable']);
    Route::patch('shopify_tags/active/{id}', ['uses' => 'Admin\ShopifyTagsController@active', 'as' => 'shopify_tags.active']);
    Route::patch('shopify_tags/inactive/{id}', ['uses' => 'Admin\ShopifyTagsController@inactive', 'as' => 'shopify_tags.inactive']);
    Route::resource('shopify_tags', 'Admin\ShopifyTagsController');
    // ShopifyTags Routes end

    // Shopify Custom Collections Routes start
    Route::post('shopify_custom_collections/datatable', ['uses' => 'Admin\ShopifyCustomCollectionsController@datatable', 'as' => 'shopify_custom_collections.datatable']);
    Route::patch('shopify_custom_collections/active/{id}', ['uses' => 'Admin\ShopifyCustomCollectionsController@active', 'as' => 'shopify_custom_collections.active']);
    Route::patch('shopify_custom_collections/inactive/{id}', ['uses' => 'Admin\ShopifyCustomCollectionsController@inactive', 'as' => 'shopify_custom_collections.inactive']);
    Route::resource('shopify_custom_collections', 'Admin\ShopifyCustomCollectionsController');
    // Shopify Custom Collections Routes end

    // ShopifyProducts Routes start
    Route::post('shopify_products/datatable', ['uses' => 'Admin\ShopifyProductsController@datatable', 'as' => 'shopify_products.datatable']);
    Route::patch('shopify_products/active/{id}', ['uses' => 'Admin\ShopifyProductsController@active', 'as' => 'shopify_products.active']);
    Route::patch('shopify_products/inactive/{id}', ['uses' => 'Admin\ShopifyProductsController@inactive', 'as' => 'shopify_products.inactive']);
    Route::get('shopify_products/detail/{id}', ['uses' => 'Admin\ShopifyProductsController@detail', 'as' => 'shopify_products.detail']);
    Route::get('shopify_products/sync-products', ['uses' => 'Admin\ShopifyProductsController@syncProducts', 'as' => 'shopify_products.sync_products']);
    Route::resource('shopify_products', 'Admin\ShopifyProductsController');
    // ShopifyProducts Routes end

    // Shopify Customers Routes start
    Route::post('shopify_customers/datatable', ['uses' => 'Admin\ShopifyCustomersController@datatable', 'as' => 'shopify_customers.datatable']);
    Route::get('shopify_customers/detail/{id}', ['uses' => 'Admin\ShopifyCustomersController@detail', 'as' => 'shopify_customers.detail']);
    Route::get('shopify_customers/sync-customers', ['uses' => 'Admin\ShopifyCustomersController@syncCustomers', 'as' => 'shopify_customers.sync_customers']);
    Route::resource('shopify_customers', 'Admin\ShopifyCustomersController');
    // Shopify Customers Routes end

    // Tickets Routes start
    Route::get('tickets/showticketstatus', ['uses' => 'Admin\TicketsController@showTicketStatuses', 'as' => 'tickets.showticketstatus']);
    Route::put('tickets/storeticketstatus', ['uses' => 'Admin\TicketsController@storeTicketStatuses', 'as' => 'tickets.storeticketstatus']);
    Route::get('tickets/get-customer', ['uses' => 'Admin\TicketsController@getCustomer', 'as' => 'tickets.get_customer']);
    Route::get('tickets/get-product', ['uses' => 'Admin\TicketsController@getProduct', 'as' => 'tickets.get_product']);
    Route::get('tickets/get-product-detail', ['uses' => 'Admin\TicketsController@getProductDetail', 'as' => 'tickets.get_product_detail']);
    Route::post('tickets/datatable', ['uses' => 'Admin\TicketsController@datatable', 'as' => 'tickets.datatable']);
    Route::patch('tickets/active/{id}', ['uses' => 'Admin\TicketsController@active', 'as' => 'tickets.active']);
    Route::patch('tickets/inactive/{id}', ['uses' => 'Admin\TicketsController@inactive', 'as' => 'tickets.inactive']);
    Route::get('tickets/detail/{id}', ['uses' => 'Admin\TicketsController@detail', 'as' => 'tickets.detail']);
    Route::resource('tickets', 'Admin\TicketsController');
    Route::get('tickets/draft/{id}', ['uses' => 'Admin\TicketsController@createDraftOrder', 'as' => 'tickets.draft_order']);
    // Tickets Routes end

    // Shopify Plans Routes start
    Route::post('shopify_plans/datatable', ['uses' => 'Admin\ShopifyPlansController@datatable', 'as' => 'shopify_plans.datatable']);
    Route::patch('shopify_plans/active/{id}', ['uses' => 'Admin\ShopifyPlansController@active', 'as' => 'shopify_plans.active']);
    Route::patch('shopify_plans/inactive/{id}', ['uses' => 'Admin\ShopifyPlansController@inactive', 'as' => 'shopify_plans.inactive']);
    Route::get('shopify_plans/sort', ['uses' => 'Admin\ShopifyPlansController@sortorder', 'as' => 'shopify_plans.sort']);
    Route::get('shopify_plans/sort-save', ['uses' => 'Admin\ShopifyPlansController@sortorder_save', 'as' => 'shopify_plans.sort_save']);
    Route::resource('shopify_plans', 'Admin\ShopifyPlansController');
    // Shopify Plans Routes end

    // Shopify Billings Routes start
    Route::get('shopify_billings/callback', ['uses' => 'Admin\ShopifyBillingsController@callback', 'as' => 'shopify_billings.callback']);
    Route::resource('shopify_billings', 'Admin\ShopifyBillingsController');
    // Shopify Billings Routes end
});

