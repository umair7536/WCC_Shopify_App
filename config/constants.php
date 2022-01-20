<?php

return [

    //Constant for user type start

    'administrator_id' => 1,
    'application_user_id' => 2,
    'team_player_id' => 3,

    'role_application_user' => 2,
    'role_team_player' => 3,

    /*
     * Product Type IDs
     */
    'product_type_slug_simple' => 'simple',
    'product_type_slug_print' => 'print',
    'product_type_slug_embroidery' => 'embroidery',

    'months_array' => array(
        1 => 'January',
        2 => 'February',
        3 => 'March',
        4 => 'April',
        5 => 'May',
        6 => 'June',
        7 => 'July',
        8 => 'August',
        9 => 'September',
        10 => 'October',
        11 => 'November',
        12 => 'December'
    ),

    'webhooks' => array(
//        'products/create' => 'Product Creation',
//        'products/delete' => 'Product Deletion',
//        'products/update' => 'Product Update',
        'customers/create' => 'Customer Creation',
        'customers/update' => 'Customer Update',
        'orders/cancelled' => 'Order Cancellation',
        'orders/create' => 'Order Creation',
        'orders/delete' => 'Order Deletion',
        'orders/fulfilled' => 'Order Fulfillment',
        'orders/paid' => 'Order Payment',
        'orders/updated' => 'Order Update',
        'orders/edited' => 'Order Edited',
        'app/uninstalled' => 'App Uninstalled',
    ),

    'shopify_payment_status' => array(
        'authorized' => 'Authorized',
        'paid' => 'Paid',
        'partially_refunded' => 'Partially Refunded',
        'partially_paid' => 'Partially Paid',
        'pending' => 'Pending',
        'refunded' => 'Refunded',
        'unpaid' => 'Unpaid',
        'voided' => 'Voided',
    ),

    /**
     * ********************************
     * Leopards Mapping
     * ********************************
     */
    'shipment_type' => array(
        2 => 'Detain',
        3 => 'Overland',
        10 => 'Overnight',
    ),
    'wcc_shipment_type' => array(
        2 => 'COD',
    ),

    'lcs_dummy_email' => 'noemail@lcs.packet',
    'shipment_type_overnight' => 10,
    'shipment_type_cod' => 2,
    'status' => array(
        0 => 'Booked',
        1 => 'Confirmed',
        // 2 => 'Pickup Request Sent',
        3 => 'Cancelled',
        // 4 => 'Consignment Booked',
        // 5 => 'Assign to Courier',
        // 6 => 'Arrived at Station',
        // 7 => 'Returned to shipper',
        // 8 => 'Missroute',
        // 9 => 'Pending',
        // 12 => 'Delivered',
        // 14 => 'Dispatched',
        // 16 => 'Refused',
        // 17 => 'Being Return',
    ),
    'status_sync' => array(
        0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 12, 14, 16, 17
    ),
    'status_delivered' => 12,
    'shipment_mode' => array(
//        'self' => 'Use Shipper info from LCS Account',
        'other' => 'Provide Custom Shipper Information'
    ),
    'status_cancel' => 3,
    'status_pickup_request_sent' => 2,
    'request_sent' => 0,
    'financial_status' => [
        'pending' => 'Pending',
        'authorized' => 'Authorized',
        'partially_paid' => 'Partially Paid',
        'paid' => 'Paid',
        'partially_refunded' => 'Partially Refunded',
        'refunded' => 'Refunded',
        'voided' => 'Voided'
    ],
    'fulfillment_status' => [
        'null' => 'Pending',
        'fulfilled' => 'Fulfilled',
        // 'partial' => 'Partial',
        // 'restocked' => 'Restocked',
    ],
];
