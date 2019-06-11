<?php
/**
 * Created by PhpStorm.
 * User: macbook
 * Date: 17/01/2019
 * Time: 11:08 PM
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Default Ticket Statuses Array
    |--------------------------------------------------------------------------
    */
    'ticket_statuses' => array(
        [
            'name' => 'Open',
            'slug' => 'open',
        ],
        [
            'name' => 'Repaired',
            'slug' => 'default',
        ],
        [
            'name' => 'Waiting on Customer',
            'slug' => 'default',
        ],
        [
            'name' => 'Complete',
            'slug' => 'complete',
        ]
    ),

    /*
    |--------------------------------------------------------------------------
    | Default Product Types Array
    |--------------------------------------------------------------------------
    */
    'shopify_name_prices' => array(
        [
            'name' => 'Simple Name Price',
            'amount' => '10.00',
            'slug' => 'single'
        ]
    ),

    /*
    |--------------------------------------------------------------------------
    | Embroidery Per Units Array
    |--------------------------------------------------------------------------
    */
    'embroidery_charge_per_units' => array(
        [
            'name' => '6-12',
            'qty_min' => '6',
            'qty_max' => '12',
            'sort_number' => '1'
        ],
        [
            'name' => '13-24',
            'qty_min' => '12',
            'qty_max' => '24',
            'sort_number' => '2'
        ],
        [
            'name' => '25-48',
            'qty_min' => '25',
            'qty_max' => '48',
            'sort_number' => '3'
        ],
        [
            'name' => '49-72',
            'qty_min' => '49',
            'qty_max' => '72',
            'sort_number' => '4'
        ],
        [
            'name' => '73-150',
            'qty_min' => '73',
            'qty_max' => '150',
            'sort_number' => '5'
        ],
        [
            'name' => '151-300',
            'qty_min' => '151',
            'qty_max' => '300',
            'sort_number' => '6'
        ],
        [
            'name' => '301+',
            'qty_min' => '301',
            'qty_max' => '9999999',
            'sort_number' => '7'
        ],
    ),

    /*
    |--------------------------------------------------------------------------
    | Print Per Units Array
    |--------------------------------------------------------------------------
    */
    'print_charge_per_units' => array(
        [
            'name' => '6-12',
            'qty_min' => '6',
            'qty_max' => '12',
            'sort_number' => '1'
        ],
        [
            'name' => '13-24',
            'qty_min' => '12',
            'qty_max' => '24',
            'sort_number' => '2'
        ],
        [
            'name' => '25-48',
            'qty_min' => '25',
            'qty_max' => '48',
            'sort_number' => '3'
        ],
        [
            'name' => '49-72',
            'qty_min' => '49',
            'qty_max' => '72',
            'sort_number' => '4'
        ],
        [
            'name' => '73-150',
            'qty_min' => '73',
            'qty_max' => '150',
            'sort_number' => '5'
        ],
        [
            'name' => '151-300',
            'qty_min' => '151',
            'qty_max' => '300',
            'sort_number' => '6'
        ],
        [
            'name' => '301+',
            'qty_min' => '301',
            'qty_max' => '9999999',
            'sort_number' => '7'
        ],
    ),

    /*
    |--------------------------------------------------------------------------
    | Embroidery Per Setup Array
    |--------------------------------------------------------------------------
    */
    'embroidery_setup_charges' => array(
        [
            'name' => 'Universal',
            'qty_min' => '1',
            'qty_max' => '9999999',
            'sort_number' => '1'
        ]
    ),

    /*
    |--------------------------------------------------------------------------
    | Print Per Setup Array
    |--------------------------------------------------------------------------
    */
    'print_setup_charges' => array(
        [
            'name' => '6-12',
            'qty_min' => '6',
            'qty_max' => '12',
            'sort_number' => '1'
        ],
        [
            'name' => '13-24',
            'qty_min' => '12',
            'qty_max' => '24',
            'sort_number' => '2'
        ],
        [
            'name' => '25-48',
            'qty_min' => '25',
            'qty_max' => '48',
            'sort_number' => '3'
        ],
        [
            'name' => '49-72',
            'qty_min' => '49',
            'qty_max' => '72',
            'sort_number' => '4'
        ],
        [
            'name' => '73-150',
            'qty_min' => '73',
            'qty_max' => '150',
            'sort_number' => '5'
        ],
        [
            'name' => '151-300',
            'qty_min' => '151',
            'qty_max' => '300',
            'sort_number' => '6'
        ],
        [
            'name' => '301+',
            'qty_min' => '301',
            'qty_max' => '9999999',
            'sort_number' => '7'
        ],
    ),

    /*
    |--------------------------------------------------------------------------
    | Simple Product Discount Ranges Array
    |--------------------------------------------------------------------------
    */
    'simple_product_discount_ranges' => array(
        [
            'name' => '1-5',
            'qty_min' => '1',
            'qty_max' => '5',
            'sort_number' => '0'
        ],
        [
            'name' => '6-12',
            'qty_min' => '6',
            'qty_max' => '12',
            'sort_number' => '1'
        ],
        [
            'name' => '13-24',
            'qty_min' => '13',
            'qty_max' => '24',
            'sort_number' => '2'
        ],
        [
            'name' => '25-48',
            'qty_min' => '25',
            'qty_max' => '48',
            'sort_number' => '3'
        ],
        [
            'name' => '49-72',
            'qty_min' => '49',
            'qty_max' => '72',
            'sort_number' => '4'
        ],
        [
            'name' => '73-150',
            'qty_min' => '73',
            'qty_max' => '150',
            'sort_number' => '5'
        ],
        [
            'name' => '151-300',
            'qty_min' => '151',
            'qty_max' => '300',
            'sort_number' => '7'
        ],
        [
            'name' => '300+',
            'qty_min' => '300',
            'qty_max' => '9999999',
            'sort_number' => '7'
        ],
    ),

    /*
    |--------------------------------------------------------------------------
    | Embroidery Product Discount Ranges Array
    |--------------------------------------------------------------------------
    */
    'embroidery_product_discount_ranges' => array(
        [
            'name' => '1-5',
            'qty_min' => '1',
            'qty_max' => '5',
            'sort_number' => '0'
        ],
        [
            'name' => '6-12',
            'qty_min' => '6',
            'qty_max' => '12',
            'sort_number' => '1'
        ],
        [
            'name' => '13-24',
            'qty_min' => '13',
            'qty_max' => '24',
            'sort_number' => '2'
        ],
        [
            'name' => '25-48',
            'qty_min' => '25',
            'qty_max' => '48',
            'sort_number' => '3'
        ],
        [
            'name' => '49-72',
            'qty_min' => '49',
            'qty_max' => '72',
            'sort_number' => '4'
        ],
        [
            'name' => '73-150',
            'qty_min' => '73',
            'qty_max' => '150',
            'sort_number' => '5'
        ],
        [
            'name' => '151-300',
            'qty_min' => '151',
            'qty_max' => '300',
            'sort_number' => '7'
        ],
        [
            'name' => '300+',
            'qty_min' => '300',
            'qty_max' => '9999999',
            'sort_number' => '7'
        ],
    ),

    /*
    |--------------------------------------------------------------------------
    | Print Product Discount Ranges Array
    |--------------------------------------------------------------------------
    */
    'print_product_discount_ranges' => array(
        [
            'name' => '1-5',
            'qty_min' => '1',
            'qty_max' => '5',
            'sort_number' => '0'
        ],
        [
            'name' => '6-12',
            'qty_min' => '6',
            'qty_max' => '12',
            'sort_number' => '1'
        ],
        [
            'name' => '13-24',
            'qty_min' => '13',
            'qty_max' => '24',
            'sort_number' => '2'
        ],
        [
            'name' => '25-48',
            'qty_min' => '25',
            'qty_max' => '48',
            'sort_number' => '3'
        ],
        [
            'name' => '49-72',
            'qty_min' => '49',
            'qty_max' => '72',
            'sort_number' => '4'
        ],
        [
            'name' => '73-150',
            'qty_min' => '73',
            'qty_max' => '150',
            'sort_number' => '5'
        ],
        [
            'name' => '151-300',
            'qty_min' => '151',
            'qty_max' => '300',
            'sort_number' => '7'
        ],
        [
            'name' => '300+',
            'qty_min' => '300',
            'qty_max' => '9999999',
            'sort_number' => '7'
        ],
    ),

    /*
    |--------------------------------------------------------------------------
    | Simple Product Discount Maps Array
    |--------------------------------------------------------------------------
    */
    'simple_product_discount_maps' => array(
        [
            'name' => 'Map BLANK',
            'tag' => 'MAP_BLANK'
        ],
        [
            'name' => 'Non-Map Under $19.99 BLANK',
            'tag' => 'NON_MAP_BLANK_19'
        ],
        [
            'name' => 'Non-Map Over $20 BLANK',
            'tag' => 'NON_MAP_BLANK_20'
        ],
    ),

    /*
    |--------------------------------------------------------------------------
    | Embroidery Product Discount Ranges Array
    |--------------------------------------------------------------------------
    */
    'embroidery_product_discount_maps' => array(
        [
            'name' => 'Map DECORATED',
            'tag' => 'MAP_DECOR'
        ],
        [
            'name' => 'Non-Map Under $19.99 DECORATED',
            'tag' => 'NON_MAP_DECOR_19'
        ],
        [
            'name' => 'Non-Map Over $20 DECORATED',
            'tag' => 'NON_MAP_DECOR_20'
        ],
    ),

    /*
    |--------------------------------------------------------------------------
    | Print Product Discount Ranges Array
    |--------------------------------------------------------------------------
    */
    'print_product_discount_maps' => array(
        [
            'name' => 'Map DECORATED',
            'tag' => 'MAP_DECOR'
        ],
        [
            'name' => 'Non-Map Under $19.99 DECORATED',
            'tag' => 'NON_MAP_DECOR_19'
        ],
        [
            'name' => 'Non-Map Over $20 DECORATED',
            'tag' => 'NON_MAP_DECOR_20'
        ],
    ),

    /*
    |--------------------------------------------------------------------------
    | Print Charge Colors Array
    |--------------------------------------------------------------------------
    */
    'print_charge_colors' => array(
        [
            'name' => '1 Color',
            'qty' => '1',
            'sort_number' => '1'
        ],
        [
            'name' => '2 Color',
            'qty' => '2',
            'sort_number' => '2'
        ],
        [
            'name' => '3 Color',
            'qty' => '3',
            'sort_number' => '3'
        ],
        [
            'name' => '4 Color',
            'qty' => '4',
            'sort_number' => '4'
        ],
        [
            'name' => '5 Color',
            'qty' => '5',
            'sort_number' => '5'
        ],
        [
            'name' => '6 Color',
            'qty' => '6',
            'sort_number' => '6'
        ],
        [
            'name' => '7 Color',
            'qty' => '7',
            'sort_number' => '7'
        ],
    ),

    /*
    |--------------------------------------------------------------------------
    | Print Setup Colors Array
    |--------------------------------------------------------------------------
    */
    'print_setup_colors' => array(
        [
            'name' => '1 Color',
            'qty' => '1',
            'sort_number' => '1'
        ],
        [
            'name' => '2 Color',
            'qty' => '2',
            'sort_number' => '2'
        ],
        [
            'name' => '3 Color',
            'qty' => '3',
            'sort_number' => '3'
        ],
        [
            'name' => '4 Color',
            'qty' => '4',
            'sort_number' => '4'
        ],
        [
            'name' => '5 Color',
            'qty' => '5',
            'sort_number' => '5'
        ],
        [
            'name' => '6 Color',
            'qty' => '6',
            'sort_number' => '6'
        ],
        [
            'name' => '7 Color',
            'qty' => '7',
            'sort_number' => '7'
        ],
    ),


];