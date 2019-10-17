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
        'products/create' => 'Product Creation',
        'products/delete' => 'Product Deletion',
        'products/update' => 'Product Update',
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
    'status' => array(
        0 => 'Pickup Request not Send',
        1 => '48 Hours Auto Canceled',
        2 => 'Pickup Request Sent',
        3 => 'Cancelled',
        4 => 'Consignment Booked',
        5 => 'Assign to Courier',
        6 => 'Arrived at Station',
        7 => 'Returned to shipper',
        8 => 'Missroute',
        9 => 'Pending',
        12 => 'Delivered',
        14 => 'Dispatched',
        16 => 'Refused',
        17 => 'Being Return',
    ),
    'status_cancel' => 3
];
