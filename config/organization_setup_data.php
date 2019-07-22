<?php

return [

    // Settings
    'settings' => array(
        1 => array(
            'id' => 1,
            'name' => 'App Api Key',
            'data' => 'eaa01c8ae325faf7335ba5057aa08d90',
            'slug' => 'sys-app-api-key',
            'account_id' => '1',
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ),
        2 => array(
            'id' => 2,
            'name' => 'App Shared Secret',
            'data' => 'abde4d8e9500f43b307e5079b0f75e02',
            'slug' => 'sys-app-shared-secret',
            'account_id' => '1',
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ),
        3 => array(
            'id' => 3,
            'name' => 'App Default Store',
            'data' => 'omniblend.myshopify.com',
            'slug' => 'sys-app-default-store',
            'account_id' => '1',
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ),
        4 => array(
            'id' => 4,
            'name' => 'App Scopes',
            'data' => 'read_content,write_content,read_themes,write_themes,read_all_orders,read_products,write_products,read_product_listings,read_customers,read_draft_orders,write_draft_orders,read_checkouts,write_checkouts,write_price_rules,read_price_rules,read_script_tags,write_script_tags',
            'slug' => 'sys-spp-scopes',
            'account_id' => '1',
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ),
    ),

    // Ticket Statuses
    'ticket_statuses' => array(
        1 => array(
            'name' => 'Open',
            'slug' => 'open',
            'sort_number' => 0,
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ),
        2 => array(
            'name' => 'Repaired',
            'slug' => 'repaired',
            'sort_number' => 1,
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ),
        3 => array(
            'name' => 'Waiting on Customer',
            'slug' => 'default',
            'sort_number' => 2,
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ),
        4 => array(
            'name' => 'Complete',
            'slug' => 'complete',
            'sort_number' => 3,
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
        ),
    )
];
