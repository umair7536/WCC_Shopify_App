<?php

//Route::get('/', function () { return redirect('/admin/home'); });
Route::get('/', 'Admin\ShopifyController@verifyShopify')->name('shopify.verify_install');

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
Route::get('install', 'Admin\ShopifyController@install')->name('shopify.install');
Route::get('redirect', 'Admin\ShopifyController@redirect')->name('shopify.redirect');
Route::get('shopify-login', 'Admin\ShopifyController@login')->name('shopify.login');
Route::get('verify', 'Admin\ShopifyController@verifyShopify')->name('shopify.verify');


// Shopify Webhooks Area
Route::post('webhooks/app', 'Admin\WebhooksController@app')->name('webhooks.app');
Route::post('webhooks/orders', 'Admin\WebhooksController@orders')->name('webhooks.orders');
Route::post('webhooks/customers', 'Admin\WebhooksController@customers')->name('webhooks.customers');

// GDPR related Webhooks
Route::post('customers/data_request', 'Admin\WebhooksController@customersDataRequest')->name('webhooks.customers_data_request');
Route::post('customers/redact', 'Admin\WebhooksController@customersRedact')->name('webhooks.customers_redact');
Route::post('shop/redact', 'Admin\WebhooksController@shopRedact')->name('webhooks.customers_redact');
Route::get('track/{id}', ['uses' => 'Admin\WebhooksController@track', 'as' => 'track']);

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
    Route::get('/updatesettings', ['uses' => 'HomeController@updateSettings', 'as' => 'update_settings']);
    Route::get('/instructions', ['uses' => 'HomeController@instructions', 'as' => 'instructions']);
    Route::get('/our-apps', ['uses' => 'HomeController@ourApps', 'as' => 'our_apps']);
    Route::get('/run', ['uses' => 'HomeController@runQueue', 'as' => 'run_queue']);
    Route::get('/variant', ['uses' => 'HomeController@runVariantsQueue', 'as' => 'run_variant']);
    Route::post('/clear-processes', ['uses' => 'HomeController@clearProcesses', 'as' => 'clear_processes']);

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
    Route::post('shopify_webhooks/refresh', ['uses' => 'Admin\ShopifyWebhooksController@refresh', 'as' => 'shopify_webhooks.refresh']);
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
    Route::get('shopify_custom_collections/sync-custom-collections', ['uses' => 'Admin\ShopifyCustomCollectionsController@syncCustomCollections', 'as' => 'shopify_custom_collections.custom_collections']);
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
    Route::get('tickets/showserialnumberhistory', ['uses' => 'Admin\TicketsController@showSerialNumberHistory', 'as' => 'tickets.showserialnumberhistory']);
    Route::put('tickets/storeticketstatus', ['uses' => 'Admin\TicketsController@storeTicketStatuses', 'as' => 'tickets.storeticketstatus']);
    Route::get('tickets/get-customer', ['uses' => 'Admin\TicketsController@getCustomer', 'as' => 'tickets.get_customer']);
    Route::get('tickets/get-product', ['uses' => 'Admin\TicketsController@getProduct', 'as' => 'tickets.get_product']);
    Route::get('tickets/get-product-detail', ['uses' => 'Admin\TicketsController@getProductDetail', 'as' => 'tickets.get_product_detail']);
    Route::get('tickets/get-customer-detail', ['uses' => 'Admin\TicketsController@getCustomerDetail', 'as' => 'tickets.get_customer_detail']);
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

    // GeneralSettings Routes start
    Route::post('general_settings/datatable', ['uses' => 'Admin\GeneralSettingsController@datatable', 'as' => 'general_settings.datatable']);
    Route::patch('general_settings/active/{id}', ['uses' => 'Admin\GeneralSettingsController@active', 'as' => 'general_settings.active']);
    Route::patch('general_settings/inactive/{id}', ['uses' => 'Admin\GeneralSettingsController@inactive', 'as' => 'general_settings.inactive']);
    Route::get('general_settings/sort', ['uses' => 'Admin\GeneralSettingsController@sortorder', 'as' => 'general_settings.sort']);
    Route::get('general_settings/sort-save', ['uses' => 'Admin\GeneralSettingsController@sortorder_save', 'as' => 'general_settings.sort_save']);
    Route::resource('general_settings', 'Admin\GeneralSettingsController');
    // GeneralSettings Routes end

    // Shopify Orders Routes start
    Route::get('shopify_orders/book-packet', ['uses' => 'Admin\ShopifyOrdersController@bookPacket', 'as' => 'shopify_orders.book_packet']);
    Route::get('shopify_orders/book', ['uses' => 'Admin\ShopifyOrdersController@book', 'as' => 'shopify_orders.book']);
    Route::post('shopify_orders/datatable', ['uses' => 'Admin\ShopifyOrdersController@datatable', 'as' => 'shopify_orders.datatable']);
    Route::patch('shopify_orders/active/{id}', ['uses' => 'Admin\ShopifyOrdersController@active', 'as' => 'shopify_orders.active']);
    Route::patch('shopify_orders/inactive/{id}', ['uses' => 'Admin\ShopifyOrdersController@inactive', 'as' => 'shopify_orders.inactive']);
    Route::get('shopify_orders/sync-custom-collections', ['uses' => 'Admin\ShopifyOrdersController@syncOrders', 'as' => 'shopify_orders.orders']);
    Route::resource('shopify_orders', 'Admin\ShopifyOrdersController');
    // Shopify Orders Routes end

    // Leopards Settings Routes start
    Route::post('leopards_settings/datatable', ['uses' => 'Admin\LeopardsSettingsController@datatable', 'as' => 'leopards_settings.datatable']);
    Route::patch('leopards_settings/active/{id}', ['uses' => 'Admin\LeopardsSettingsController@active', 'as' => 'leopards_settings.active']);
    Route::patch('leopards_settings/inactive/{id}', ['uses' => 'Admin\LeopardsSettingsController@inactive', 'as' => 'leopards_settings.inactive']);
    Route::get('leopards_settings/sort', ['uses' => 'Admin\LeopardsSettingsController@sortorder', 'as' => 'leopards_settings.sort']);
    Route::get('leopards_settings/sort-save', ['uses' => 'Admin\LeopardsSettingsController@sortorder_save', 'as' => 'leopards_settings.sort_save']);
    Route::resource('leopards_settings', 'Admin\LeopardsSettingsController');
    // Leopards Settings Routes end

    // Leopards Cities Routes start
    Route::post('leopards_cities/datatable', ['uses' => 'Admin\LeopardsCitiesController@datatable', 'as' => 'leopards_cities.datatable']);
    Route::patch('leopards_cities/active/{id}', ['uses' => 'Admin\LeopardsCitiesController@active', 'as' => 'leopards_cities.active']);
    Route::patch('leopards_cities/inactive/{id}', ['uses' => 'Admin\LeopardsCitiesController@inactive', 'as' => 'leopards_cities.inactive']);
    Route::get('leopards_cities/sort', ['uses' => 'Admin\LeopardsCitiesController@sortorder', 'as' => 'leopards_cities.sort']);
    Route::get('leopards_cities/sort-save', ['uses' => 'Admin\LeopardsCitiesController@sortorder_save', 'as' => 'leopards_cities.sort_save']);
    Route::get('leopards_cities/sync-leopards-cities', ['uses' => 'Admin\LeopardsCitiesController@syncLeopardsCities', 'as' => 'leopards_cities.sync_leopards_cities']);
    Route::resource('leopards_cities', 'Admin\LeopardsCitiesController');
    // Leopards Cities Routes end

    // Shippers Routes start
    Route::post('shippers/datatable', ['uses' => 'Admin\ShippersController@datatable', 'as' => 'shippers.datatable']);
    Route::patch('shippers/active/{id}', ['uses' => 'Admin\ShippersController@active', 'as' => 'shippers.active']);
    Route::patch('shippers/inactive/{id}', ['uses' => 'Admin\ShippersController@inactive', 'as' => 'shippers.inactive']);
    Route::get('shippers/sync-custom-collections', ['uses' => 'Admin\ShippersController@syncCustomCollections', 'as' => 'shippers.custom_collections']);
    Route::resource('shippers', 'Admin\ShippersController');
    // Shippers Routes end

    // Consignees Routes start
    Route::post('consignees/datatable', ['uses' => 'Admin\ConsigneesController@datatable', 'as' => 'consignees.datatable']);
    Route::patch('consignees/active/{id}', ['uses' => 'Admin\ConsigneesController@active', 'as' => 'consignees.active']);
    Route::patch('consignees/inactive/{id}', ['uses' => 'Admin\ConsigneesController@inactive', 'as' => 'consignees.inactive']);
    Route::get('consignees/sync-custom-collections', ['uses' => 'Admin\ConsigneesController@syncCustomCollections', 'as' => 'consignees.custom_collections']);
    Route::resource('consignees', 'Admin\ConsigneesController');
    // Consignees Routes end

    // Booked Packets start
    Route::get('booked_packets/fulfill/{id}', ['uses' => 'Admin\BookedPacketsController@fulfill', 'as' => 'booked_packets.fulfill']);
    Route::post('booked_packets/savefulfillment/{id}', ['uses' => 'Admin\BookedPacketsController@savefulfillment', 'as' => 'booked_packets.savefulfillment']);
    Route::get('booked_packets/sync-status', ['uses' => 'Admin\BookedPacketsController@syncStatus', 'as' => 'booked_packets.sync_status']);
    Route::get('booked_packets/api', ['uses' => 'Admin\BookedPacketsController@api', 'as' => 'booked_packets.api']);
    Route::post('booked_packets/apidatatable', ['uses' => 'Admin\BookedPacketsController@apidatatable', 'as' => 'booked_packets.apidatatable']);
    Route::patch('booked_packets/cancel/{id}', ['uses' => 'Admin\BookedPacketsController@cancel', 'as' => 'booked_packets.cancel']);
    Route::get('booked_packets/detail/{id}', ['uses' => 'Admin\BookedPacketsController@detail', 'as' => 'booked_packets.detail']);
    Route::get('booked_packets/track/{id}', ['uses' => 'Admin\BookedPacketsController@track', 'as' => 'booked_packets.track']);
    Route::post('booked_packets/datatable', ['uses' => 'Admin\BookedPacketsController@datatable', 'as' => 'booked_packets.datatable']);
    Route::resource('booked_packets', 'Admin\BookedPacketsController');
    // Booked Packets end
});

