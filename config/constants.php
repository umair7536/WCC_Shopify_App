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
    )
];
