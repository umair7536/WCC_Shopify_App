<?php

namespace App\Http\Controllers\Admin;

use App\Models\ChargeColors;
use App\Models\ChargePerUnitPrices;
use App\Models\ChargePerUnits;
use App\Models\ChargePlacements;
use App\Models\ProductDiscountMaps;
use App\Models\ProductDiscountPrices;
use App\Models\ProductTypes;
use App\Models\SetupChargePrices;
use App\Models\SetupCharges;
use App\Models\SetupColors;
use App\Models\ShopifyJobs;
use App\Models\ShopifyProducts;
use App\Models\ShopifyProductTags;
use App\Models\ShopifyProductVariants;
use App\Models\ShopifyShops;
use App\Models\ShopifyTags;
use App\Models\UnitPlacements;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use Config;
use ZfrShopify\ShopifyClient;

class ShopifyPricesController extends Controller
{
    /**
     * Display a listing of Permission.
     *
     * @return \Illuminate\Http\Response
     */
    public function getPrices(Request $request)
    {
        $data = [
            'status' => false,
            'tags' => array(
                'simple' => null,
                'embroidery' => null,
                'print' => null
            ),
            'type' => array(
                'simple' => false,
                'embroidery' => false,
                'print' => false
            ),
            'simple_prices' => [],
            'embroidery_prices' => [],
            'print_prices' => [],
            'embroidery_charge_prices' => [],
            'print_charge_prices' => [],
            'embroidery_setup_prices' => [],
            'print_setup_prices' => [],
        ];

        if($request->get('id')) {
            $id = $request->get('id');

            $product = ShopifyProducts::where('product_id', '=', $id)->first();
            $product_variant = ShopifyProductVariants
                ::where('product_id', '=', $id)
                ->where('position', '=', '1')
                ->first();

            if($product && $product_variant) {
                // Convert product from object to an array
                $product = $product->toArray();
                $product_variant = $product_variant->toArray();

                /**
                 * Get Producd from Shopify
                 */
                $shop = ShopifyShops::where([
                    'account_id' => $product['account_id']
                ])->first();

                if($shop) {
                    $shopifyClient = new ShopifyClient([
                        'private_app' => false,
                        'api_key' => env('SHOPIFY_APP_API_KEY'), // In public app, this is the app ID
                        'access_token' => $shop->access_token,
                        'shop' => $shop->myshopify_domain
                    ]);

                    $shopifyProduct = $shopifyClient->getProduct([
                        'id' => (int) $request->get('id')
                    ]);


                    if(count($shopifyProduct)) {
                        // Set Account ID
                        $shopifyProduct['account_id'] = $product['account_id'];
                        $product = $shopifyProduct;

                        // If variant is set
                        if(
                            isset($shopifyProduct['variants']) &&
                            count($shopifyProduct['variants']) &&
                            $shopifyProduct['variants'][0]['position'] == '1'
                        ) {
                            $product_variant = $shopifyProduct['variants'][0];
                        }
                    }

                    /**
                     * Get Product Discount Prices
                     */
                    $data = $this->getProductDicountPrices($data, $product, $product_variant, $shop);
                }

            }
        }

        return response()->json($data);
    }

    private function getProductDicountPrices($data, $product, $product_variant, $shop) {

        $product_price = ($product_variant['compare_at_price']) ? $product_variant['compare_at_price'] : $product_variant['price'];

        $product_tags = ShopifyProductTags
            ::join('shopify_tags', 'shopify_tags.id', '=', 'shopify_product_tags.tag_id')
            ->where('shopify_product_tags.product_id', '=', $product['id'])
            ->where('shopify_product_tags.account_id', '=', $product['account_id'])
            ->select('shopify_tags.id', 'shopify_tags.name', 'shopify_product_tags.product_id', 'shopify_product_tags.account_id')
            ->get()->keyBy('id');

        if($product_tags) {
            $stored_price = false;
            $store_price = 0.00;

            /**
             * Product have Tags now
             * * Let's Prepare data for Simple, Emb and Print options
             */

            // Get Product Types
            $product_types = ProductTypes::where(array(
                'account_id' => $product['account_id'],
                'active' => 1
            ))
                ->orderBy('sort_number', 'asc')
                ->select('id', 'name', 'slug', 'account_id')
                ->get()->keyBy('slug');

            /**
             * Embroidery Product Discount Maps
             */
            $product_discount_mapsEmbroidery = ProductDiscountMaps
                ::where([
                    'account_id' => $product['account_id'],
                    'product_type_id' => $product_types[Config::get('constants.product_type_slug_embroidery')]->id,
                    'active' => 1,
                ])
                ->select('id', 'name', 'tag', 'tag_id', 'sort_number')
                ->orderBy('sort_number', 'asc')
                ->get()->keyBy('tag_id');

            $product_mapEmbroidery = [];

            if($product_discount_mapsEmbroidery) {
                foreach($product_discount_mapsEmbroidery as $product_discount_mapEmbroidery) {
                    if(isset($product_tags[$product_discount_mapEmbroidery->tag_id])) {

                        $product_mapEmbroidery = $product_discount_mapEmbroidery->toArray();
                        break;
                    }
                }

                // Data is present nwo process
                if(count($product_mapEmbroidery)) {
                    $product_discount_pricesEmbroidery = ProductDiscountPrices
                        ::join('product_discount_ranges', 'product_discount_ranges.id', '=', 'product_discount_prices.product_discount_range_id')
                        ->where([
                            'product_discount_prices.account_id' => $product['account_id'],
                            'product_discount_prices.product_discount_map_id' => $product_mapEmbroidery['id'],
                            'product_discount_prices.product_type_id' => $product_types[Config::get('constants.product_type_slug_embroidery')]->id,
                            'product_discount_ranges.active' => 1
                        ])
                        ->orderBy('product_discount_ranges.active', 'asc')
                        ->select(
                            'product_discount_prices.id as price_id'
                            , 'product_discount_prices.price as price_value'
                            , 'product_discount_prices.price_type'
                            , 'product_discount_prices.product_discount_range_id as range_id'
                            , 'product_discount_ranges.name'
                            , 'product_discount_ranges.qty_min'
                            , 'product_discount_ranges.qty_max'
                        )
                        ->get()
                        ->keyBy('price_id');

                    if($product_discount_pricesEmbroidery) {
                        /**
                         * Grab Tag Name based on Tag ID
                         */
                        $product_tag = ShopifyTags::where(array(
                            'id' => $product_mapEmbroidery['tag_id']
                        ))->first();
                        if($product_tag) {
                            $data['tags'][Config::get('constants.product_type_slug_embroidery')] = $product_tag->name;
                        }

                        // Set type true
                        $data['type'][Config::get('constants.product_type_slug_embroidery')] = true;

                        foreach($product_discount_pricesEmbroidery as $product_discount_priceEmbroidery) {
                            $price_value = ($product_discount_priceEmbroidery->price_type == 'percentage') ? ($product_price * ($product_discount_priceEmbroidery->price_value / 100)) : $product_discount_priceEmbroidery->price_value;

                            $data[Config::get('constants.product_type_slug_embroidery') . '_prices'][$product_discount_priceEmbroidery->price_id] = $product_discount_priceEmbroidery->toArray();
                            $data[Config::get('constants.product_type_slug_embroidery') . '_prices'][$product_discount_priceEmbroidery->price_id]['price'] = number_format(round($product_price - $price_value, 2), 2);

                            if(!$stored_price) {
                                $stored_price = true;
                                $store_price = $data[Config::get('constants.product_type_slug_embroidery') . '_prices'][$product_discount_priceEmbroidery->price_id]['price'];
                            }
                        }
                    }

                    $data[Config::get('constants.product_type_slug_embroidery') . '_charge_prices'] = $this->getMapping($product['account_id']);
                    $data[Config::get('constants.product_type_slug_embroidery') . '_setup_prices'] = $this->getSetupMapping($product['account_id']);
                }
            }

            /**
             * Embroidery Product Discount Maps
             */
            $product_discount_mapsPrint = ProductDiscountMaps
                ::where([
                    'account_id' => $product['account_id'],
                    'product_type_id' => $product_types[Config::get('constants.product_type_slug_print')]->id,
                    'active' => 1,
                ])
                ->select('id', 'name', 'tag', 'tag_id', 'sort_number')
                ->orderBy('sort_number', 'asc')
                ->get()->keyBy('tag_id');

            $product_mapPrint = [];

            if($product_discount_mapsPrint) {
                foreach($product_discount_mapsPrint as $product_discount_mapPrint) {
                    if(isset($product_tags[$product_discount_mapPrint->tag_id])) {

                        $product_mapPrint = $product_discount_mapPrint->toArray();
                        break;
                    }
                }

                // Data is present nwo process
                if(count($product_mapPrint)) {
                    $product_discount_pricesPrint = ProductDiscountPrices
                        ::join('product_discount_ranges', 'product_discount_ranges.id', '=', 'product_discount_prices.product_discount_range_id')
                        ->where([
                            'product_discount_prices.account_id' => $product['account_id'],
                            'product_discount_prices.product_discount_map_id' => $product_mapPrint['id'],
                            'product_discount_prices.product_type_id' => $product_types[Config::get('constants.product_type_slug_print')]->id,
                            'product_discount_ranges.active' => 1
                        ])
                        ->orderBy('product_discount_ranges.active', 'asc')
                        ->select(
                            'product_discount_prices.id as price_id'
                            , 'product_discount_prices.price as price_value'
                            , 'product_discount_prices.price_type'
                            , 'product_discount_prices.product_discount_range_id as range_id'
                            , 'product_discount_ranges.name'
                            , 'product_discount_ranges.qty_min'
                            , 'product_discount_ranges.qty_max'
                        )
                        ->get()
                        ->keyBy('price_id');

                    if($product_discount_pricesPrint) {

                        /**
                         * Grab Tag Name based on Tag ID
                         */
                        $product_tag = ShopifyTags::where(array(
                            'id' => $product_mapPrint['tag_id']
                        ))->first();
                        if($product_tag) {
                            $data['tags'][Config::get('constants.product_type_slug_print')] = $product_tag->name;
                        }

                        // Set type true
                        $data['type'][Config::get('constants.product_type_slug_print')] = true;

                        foreach($product_discount_pricesPrint as $product_discount_pricePrint) {
                            $price_value = ($product_discount_pricePrint->price_type == 'percentage') ? ($product_price * ($product_discount_pricePrint->price_value / 100)) : $product_discount_pricePrint->price_value;

                            $data[Config::get('constants.product_type_slug_print') . '_prices'][$product_discount_pricePrint->price_id] = $product_discount_pricePrint->toArray();
                            $data[Config::get('constants.product_type_slug_print') . '_prices'][$product_discount_pricePrint->price_id]['price'] = number_format(round($product_price - $price_value, 2), 2);

                            if(!$stored_price) {
                                $stored_price = true;
                                $store_price = $data[Config::get('constants.product_type_slug_print') . '_prices'][$product_discount_pricePrint->price_id]['price'];
                            }
                        }
                    }

                    $data[Config::get('constants.product_type_slug_print') . '_charge_prices'] = $this->getPrintMapping($product['account_id']);
                    $data[Config::get('constants.product_type_slug_print') . '_setup_prices'] = $this->getSetupPrintMapping($product['account_id']);
                }
            }

            /**
             * Simple Product Discount Maps
             */
            $product_discount_mapsSimple = ProductDiscountMaps
                ::where([
                    'account_id' => $product['account_id'],
                    'product_type_id' => $product_types[Config::get('constants.product_type_slug_simple')]->id,
                    'active' => 1,
                ])
                ->select('id', 'name', 'tag', 'tag_id', 'sort_number')
                ->orderBy('sort_number', 'asc')
                ->get()->keyBy('tag_id');

            $product_mapSimple = [];
            $product_mapSimpleFirst = [];

            if($product_discount_mapsSimple) {
                $is_stored = 0;
                foreach($product_discount_mapsSimple as $product_discount_mapSimple) {
                    /**
                     * Store first map to act like default tag if no tag is found
                     */
//                    if($is_stored == 0) {
//                        $product_mapSimpleFirst = $product_discount_mapSimple->toArray();
//                        $is_stored = 1;
//                    }

                    if(isset($product_tags[$product_discount_mapSimple->tag_id])) {
                        $product_mapSimple = $product_discount_mapSimple->toArray();
                        break;
                    }
                }

                /**
                 * Default tag not found let's provide default tag
                 */
//                if(!count($product_mapSimple)) {
//                    $product_mapSimple = $product_mapSimpleFirst;
//                }

                // Data is present nwo process
                if(count($product_mapSimple)) {
                    $product_discount_pricesSimple = ProductDiscountPrices
                        ::join('product_discount_ranges', 'product_discount_ranges.id', '=', 'product_discount_prices.product_discount_range_id')
                        ->where([
                            'product_discount_prices.account_id' => $product['account_id'],
                            'product_discount_prices.product_discount_map_id' => $product_mapSimple['id'],
                            'product_discount_prices.product_type_id' => $product_types[Config::get('constants.product_type_slug_simple')]->id,
                            'product_discount_ranges.active' => 1
                        ])
                        ->orderBy('product_discount_ranges.active', 'asc')
                        ->select(
                            'product_discount_prices.id as price_id'
                            , 'product_discount_prices.price as price_value'
                            , 'product_discount_prices.price_type'
                            , 'product_discount_prices.product_discount_range_id as range_id'
                            , 'product_discount_ranges.name'
                            , 'product_discount_ranges.qty_min'
                            , 'product_discount_ranges.qty_max'
                        )
                        ->get()
                        ->keyBy('price_id');

                    if($product_discount_pricesSimple) {
                        /**
                         * Grab Tag Name based on Tag ID
                         */
                        $product_tag = ShopifyTags::where(array(
                            'id' => $product_mapSimple['tag_id']
                        ))->first();
                        if($product_tag) {
                            $data['tags'][Config::get('constants.product_type_slug_simple')] = $product_tag->name;
                        }

                        // Set type true
                        $data['type'][Config::get('constants.product_type_slug_simple')] = true;

                        foreach($product_discount_pricesSimple as $product_discount_priceSimple) {
                            $price_value = ($product_discount_priceSimple->price_type == 'percentage') ? ($product_price * ($product_discount_priceSimple->price_value / 100)) : $product_discount_priceSimple->price_value;

                            $data[Config::get('constants.product_type_slug_simple') . '_prices'][$product_discount_priceSimple->price_id] = $product_discount_priceSimple->toArray();
                            $data[Config::get('constants.product_type_slug_simple') . '_prices'][$product_discount_priceSimple->price_id]['price'] = number_format(round($product_price - $price_value, 2), 2);

                            if(!$stored_price) {
                                $stored_price = true;
                                $store_price = $data[Config::get('constants.product_type_slug_simple') . '_prices'][$product_discount_priceSimple->price_id]['price'];
                            }
                        }
                    }
                }
            }

            /**
             * Update informatioin in Shopify End
             */
            if($store_price) {

                $payload = array(
                    'variant' => array(
                        'id' => $product_variant['id'],
                        'price' => $store_price,
                        'compare_at_price' => ($product_variant['compare_at_price']) ? $product_variant['compare_at_price'] : $product_variant['price']
                    ),
                    'shop' => $shop->toArray(),
                );

//                ShopifyJobs::insert(array(
//                    'payload' => json_encode($payload),
//                    'type' => 'upload-variants',
//                    'created_at' => Carbon::now()->toDateTimeString(),
//                    'available_at' => Carbon::now()->toDateTimeString(),
//                    'account_id' => $shop['account_id']
//                ));
            }

            $data['status'] = true;
        }

        return $data;
    }

    /**
     * Display a Price Mapping.
     *
     * @return \Illuminate\Http\Response
     */
    public function getMap(Request $request)
    {
        $data = [
            'status' => false,
            'simple_prices' => [],
            'embroidery_prices' => [],
            'print_prices' => [],
        ];

        if($request->get('domain')) {
            $store = $request->get('domain');

            /**
             * Get Store from Database
             */
            $shop = ShopifyShops::where([
                'myshopify_domain' => $request->get('domain')
            ])->first();

            if($shop) {

                /**
                 * Get Product Discount Prices
                 */
                $data = $this->getStoreMapping($shop->account_id);
            }
        }

        return response()->json($data);
    }

    private function getStoreMapping($account_id) {

        /**
         * Product have Tags now
         * * Let's Prepare data for Simple, Emb and Print options
         */

        // Get Product Types
        $product_types = ProductTypes::where(array(
            'account_id' => $account_id,
            'active' => 1
        ))
            ->orderBy('sort_number', 'asc')
            ->select('id', 'name', 'slug', 'account_id')
            ->get()->keyBy('slug');

        /**
         * Simple Product Discount Maps
         */
        $product_discount_mapsSimple = ProductDiscountMaps
            ::where([
                'account_id' => $account_id,
                'product_type_id' => $product_types[Config::get('constants.product_type_slug_simple')]->id,
                'active' => 1,
            ])
            ->select('id', 'name', 'tag', 'tag_id', 'sort_number')
            ->orderBy('sort_number', 'asc')
            ->get()->keyBy('tag_id');

        if($product_discount_mapsSimple) {

            $tag_ids = [];
            foreach($product_discount_mapsSimple as $product_discount_mapSimple) {
                $tag_ids[] = $product_discount_mapSimple->tag_id;
            }

            $product_tagsSimple = ShopifyTags::where(array(
                'account_id' => $account_id
            ))->whereIn('id', $tag_ids)->select('id', 'name')->get()->keyBy('id');


            foreach($product_discount_mapsSimple as $product_discount_mapSimple) {

                $product_discount_pricesSimple = ProductDiscountPrices
                    ::join('product_discount_ranges', 'product_discount_ranges.id', '=', 'product_discount_prices.product_discount_range_id')
                    ->where([
                        'product_discount_prices.account_id' => $account_id,
                        'product_discount_prices.product_discount_map_id' => $product_discount_mapSimple['id'],
                        'product_discount_prices.product_type_id' => $product_types[Config::get('constants.product_type_slug_simple')]->id,
                        'product_discount_ranges.active' => 1
                    ])
                    ->orderBy('product_discount_ranges.active', 'asc')
                    ->select(
                        'product_discount_prices.id as price_id'
                        , 'product_discount_prices.price as price_value'
                        , 'product_discount_prices.price_type'
                        , 'product_discount_prices.product_discount_range_id as range_id'
                        , 'product_discount_ranges.name'
                        , 'product_discount_ranges.qty_min'
                        , 'product_discount_ranges.qty_max'
                    )
                    ->get()
                    ->keyBy('price_id');

                if($product_discount_pricesSimple && isset($product_tagsSimple[$product_discount_mapSimple['tag_id']])) {

                    foreach($product_discount_pricesSimple as $product_discount_priceSimple) {

                        $data[Config::get('constants.product_type_slug_simple') . '_prices'][$product_tagsSimple[$product_discount_mapSimple['tag_id']]->name][$product_discount_priceSimple->price_id] = $product_discount_priceSimple->toArray();
                    }
                }

            }
        }

        /**
         * Embroidery Product Discount Maps
         */
        $product_discount_mapsEmbroidery = ProductDiscountMaps
            ::where([
                'account_id' => $account_id,
                'product_type_id' => $product_types[Config::get('constants.product_type_slug_embroidery')]->id,
                'active' => 1,
            ])
            ->select('id', 'name', 'tag', 'tag_id', 'sort_number')
            ->orderBy('sort_number', 'asc')
            ->get()->keyBy('tag_id');

        $product_mapEmbroidery = [];

        if($product_discount_mapsEmbroidery) {

            $tag_ids = [];
            foreach($product_discount_mapsEmbroidery as $product_discount_mapEmbroidery) {
                $tag_ids[] = $product_discount_mapEmbroidery->tag_id;
            }

            $product_tagsEmbroidery = ShopifyTags::where(array(
                'account_id' => $account_id
            ))->whereIn('id', $tag_ids)->select('id', 'name')->get()->keyBy('id');

            foreach($product_discount_mapsEmbroidery as $product_discount_mapEmbroidery) {

                $product_discount_pricesEmbroidery = ProductDiscountPrices
                    ::join('product_discount_ranges', 'product_discount_ranges.id', '=', 'product_discount_prices.product_discount_range_id')
                    ->where([
                        'product_discount_prices.account_id' => $account_id,
                        'product_discount_prices.product_discount_map_id' => $product_discount_mapEmbroidery['id'],
                        'product_discount_prices.product_type_id' => $product_types[Config::get('constants.product_type_slug_embroidery')]->id,
                        'product_discount_ranges.active' => 1
                    ])
                    ->orderBy('product_discount_ranges.active', 'asc')
                    ->select(
                        'product_discount_prices.id as price_id'
                        , 'product_discount_prices.price as price_value'
                        , 'product_discount_prices.price_type'
                        , 'product_discount_prices.product_discount_range_id as range_id'
                        , 'product_discount_ranges.name'
                        , 'product_discount_ranges.qty_min'
                        , 'product_discount_ranges.qty_max'
                    )
                    ->get()
                    ->keyBy('price_id');

                if($product_discount_pricesEmbroidery && isset($product_tagsEmbroidery[$product_discount_mapEmbroidery['tag_id']])) {

                    foreach($product_discount_pricesEmbroidery as $product_discount_priceEmbroidery) {
                        $data[Config::get('constants.product_type_slug_embroidery') . '_prices'][$product_tagsEmbroidery[$product_discount_mapEmbroidery['tag_id']]->name][$product_discount_priceEmbroidery->price_id] = $product_discount_priceEmbroidery->toArray();
                    }
                }
            }

            $data[Config::get('constants.product_type_slug_embroidery') . '_charge_prices'] = $this->getMapping($account_id);
            $data[Config::get('constants.product_type_slug_embroidery') . '_setup_prices'] = $this->getSetupMapping($account_id);
        }

        /**
         * Embroidery Product Discount Maps
         */
        $product_discount_mapsPrint = ProductDiscountMaps
            ::where([
                'account_id' => $account_id,
                'product_type_id' => $product_types[Config::get('constants.product_type_slug_print')]->id,
                'active' => 1,
            ])
            ->select('id', 'name', 'tag', 'tag_id', 'sort_number')
            ->orderBy('sort_number', 'asc')
            ->get()->keyBy('tag_id');

        $product_mapPrint = [];

        if($product_discount_mapsPrint) {

            $tag_ids = [];
            foreach($product_discount_mapsPrint as $product_discount_mapPrint) {
                $tag_ids[] = $product_discount_mapPrint->tag_id;
            }

            $product_tagsPrint = ShopifyTags::where(array(
                'account_id' => $account_id
            ))->whereIn('id', $tag_ids)->select('id', 'name')->get()->keyBy('id');

            foreach($product_discount_mapsPrint as $product_discount_mapPrint) {
                $product_discount_pricesPrint = ProductDiscountPrices
                    ::join('product_discount_ranges', 'product_discount_ranges.id', '=', 'product_discount_prices.product_discount_range_id')
                    ->where([
                        'product_discount_prices.account_id' => $account_id,
                        'product_discount_prices.product_discount_map_id' => $product_discount_mapPrint['id'],
                        'product_discount_prices.product_type_id' => $product_types[Config::get('constants.product_type_slug_print')]->id,
                        'product_discount_ranges.active' => 1
                    ])
                    ->orderBy('product_discount_ranges.active', 'asc')
                    ->select(
                        'product_discount_prices.id as price_id'
                        , 'product_discount_prices.price as price_value'
                        , 'product_discount_prices.price_type'
                        , 'product_discount_prices.product_discount_range_id as range_id'
                        , 'product_discount_ranges.name'
                        , 'product_discount_ranges.qty_min'
                        , 'product_discount_ranges.qty_max'
                    )
                    ->get()
                    ->keyBy('price_id');

                if($product_discount_pricesEmbroidery && isset($product_tagsPrint[$product_discount_mapPrint['tag_id']])) {

                    foreach($product_discount_pricesPrint as $product_discount_pricePrint) {

                        $data[Config::get('constants.product_type_slug_print') . '_prices'][$product_tagsPrint[$product_discount_mapPrint['tag_id']]->name][$product_discount_pricePrint->price_id] = $product_discount_pricePrint->toArray();
                    }
                }
            }

            $data[Config::get('constants.product_type_slug_print') . '_charge_prices'] = $this->getPrintMapping($account_id);
            $data[Config::get('constants.product_type_slug_print') . '_setup_prices'] = $this->getSetupPrintMapping($account_id);
        }

        $data['status'] = true;

        return $data;
    }

    /*
     * Get Mapping with Account ID
     *
     * @param: integer $account_id
     *
     * @return: mixed
     */
    private function getPriceMapping($account_id, $product_type_slug = false) {
        $charge_per_unit_price_mapping = [];

        if(!$product_type_slug) {
            return $charge_per_unit_price_mapping;
        }

        $product_type = ProductTypes::where(array(
            'account_id' => $account_id,
            'active' => 1,
            'slug' => $product_type_slug
        ))
            ->select('id')
            ->first();

        if(!$product_type) {
            return $charge_per_unit_price_mapping;
        }

        $charge_per_unit_prices = ChargePerUnitPrices::where([
            'account_id' => $account_id,
            'product_type_id' => $product_type->id,
        ])->get();

        if($charge_per_unit_prices) {
            foreach($charge_per_unit_prices as $charge_per_unit_price) {
                // Set Account Type ID
                if(!isset($charge_per_unit_price_mapping[$charge_per_unit_price->product_type_id])) {
                    $charge_per_unit_price_mapping[$charge_per_unit_price->product_type_id] = array();
                }

                // Set Charge Per Unit ID
                if(!isset(
                    $charge_per_unit_price_mapping[$charge_per_unit_price->product_type_id]
                    [$charge_per_unit_price->charge_per_unit_id]
                )) {
                    $charge_per_unit_price_mapping[$charge_per_unit_price->product_type_id]
                    [$charge_per_unit_price->charge_per_unit_id] = array();
                }

                // Unit Placements
                if(!isset(
                    $charge_per_unit_price_mapping[$charge_per_unit_price->product_type_id]
                    [$charge_per_unit_price->charge_per_unit_id]
                    [$charge_per_unit_price->unit_placement_id]
                )) {
                    $charge_per_unit_price_mapping[$charge_per_unit_price->product_type_id]
                    [$charge_per_unit_price->charge_per_unit_id]
                    [$charge_per_unit_price->unit_placement_id] = $charge_per_unit_price->price;
                }
            }
        }

        return $charge_per_unit_price_mapping;
    }

    /*
     * Get Mapping with Account ID
     *
     * @param: integer $account_id
     *
     * @return: mixed
     */
    private function getMapping($account_id) {
        $product_type = ProductTypes::where(array(
            'account_id' => $account_id,
            'active' => 1
        ))
            ->where('slug', '!=', Config::get('constants.product_type_slug_simple'))
            ->where('slug', '!=', Config::get('constants.product_type_slug_print'))
            ->orderBy('sort_number', 'asc')
            ->select('id', 'name', 'slug', 'account_id')
            ->first();

        $charge_per_unit_price_mapping = $this->getPriceMapping($account_id, Config::get('constants.product_type_slug_embroidery'));

        $price_mapping = [];

        if ($product_type) {
            $price_mapping[$product_type->id] = array(
                'name' => $product_type->name,
                'charge_per_units' => [],
                'unit_placements' => [],
            );

            $charge_per_units = ChargePerUnits::where(array(
                'product_type_id' => $product_type->id,
                'account_id' => $product_type->account_id,
                'active' => 1
            ))
                ->orderBy('sort_number', 'asc')
                ->select('id', 'name', 'qty_min', 'qty_max', 'product_type_id', 'account_id')
                ->get();

            $unit_placements = UnitPlacements::where(array(
                'account_id' => $product_type->account_id,
                'product_type_id' => $product_type->id,
                'active' => 1
            ))
                ->orderBy('sort_number', 'asc')
                ->select('id', 'name', 'qty', 'product_type_id', 'account_id')
                ->get();

            if ($charge_per_units && $unit_placements) {
                /*
                 * Arrange Mapping and Pricing
                 */
                foreach ($unit_placements as $unit_placement) {
                    $price_mapping[$product_type->id]['unit_placements'][$unit_placement->id] = array(
                        'id' => $unit_placement->id,
                        'name' => $unit_placement->name,
                        'qty' => $unit_placement->qty,
                    );
                }
                foreach ($charge_per_units as $charge_per_unit) {
                    $price_mapping[$product_type->id]['charge_per_units'][$charge_per_unit->id]
                        = array(
                        'name' => $charge_per_unit->name,
                        'qty_min' => $charge_per_unit->qty_min,
                        'qty_max' => $charge_per_unit->qty_max,
                        'unit_placements' => [],
                    );

                    foreach ($unit_placements as $unit_placement) {
                        $price_mapping[$product_type->id]
                        ['charge_per_units'][$charge_per_unit->id]
                        ['unit_placements'][$unit_placement->id] = array(
                            'name' => $unit_placement->name,
                            'qty' => $unit_placement->qty,
                        );

                        if(
                            isset($charge_per_unit_price_mapping[$product_type->id])
                            && isset($charge_per_unit_price_mapping[$product_type->id][$charge_per_unit->id])
                            && isset(
                                $charge_per_unit_price_mapping[$product_type->id]
                                [$charge_per_unit->id]
                                [$unit_placement->id]
                            )
                        ) {
                            $price_mapping[$product_type->id]
                            ['charge_per_units'][$charge_per_unit->id]
                            ['unit_placements'][$unit_placement->id]['price'] = $charge_per_unit_price_mapping
                            [$product_type->id]
                            [$charge_per_unit->id]
                            [$unit_placement->id];
                        } else {
                            $price_mapping[$product_type->id]
                            ['charge_per_units'][$charge_per_unit->id]
                            ['unit_placements'][$unit_placement->id]['price'] = 0;
                        }
                    }
                }
            }

            return $price_mapping[$product_type->id];

        } else {
            return [];
        }
    }

    /*
     * Get Mapping with Account ID
     *
     * @param: integer $account_id
     *
     * @return: mixed
     */
    private function getPrintPriceMapping($account_id, $product_type_slug = false) {

        $charge_per_unit_price_mapping = [];

        if(!$product_type_slug) {
            return $charge_per_unit_price_mapping;
        }

        $product_type = ProductTypes::where(array(
            'account_id' => $account_id,
            'active' => 1,
            'slug' => $product_type_slug
        ))
            ->select('id')
            ->first();

        if(!$product_type) {
            return $charge_per_unit_price_mapping;
        }

        $charge_per_unit_prices = ChargePerUnitPrices::where([
            'account_id' => $account_id,
            'product_type_id' => $product_type->id,
        ])->get();

        if($charge_per_unit_prices) {
            foreach($charge_per_unit_prices as $charge_per_unit_price) {
                // Set Account Type ID
                if(!isset($charge_per_unit_price_mapping[$charge_per_unit_price->product_type_id])) {
                    $charge_per_unit_price_mapping[$charge_per_unit_price->product_type_id] = array();
                }

                // Set Charge Per Unit
                if(!isset(
                    $charge_per_unit_price_mapping[$charge_per_unit_price->product_type_id]
                    [$charge_per_unit_price->charge_per_unit_id]
                )) {
                    $charge_per_unit_price_mapping[$charge_per_unit_price->product_type_id]
                    [$charge_per_unit_price->charge_per_unit_id] = array();
                }

                // Set Charge Color
                if(!isset(
                    $charge_per_unit_price_mapping[$charge_per_unit_price->product_type_id]
                    [$charge_per_unit_price->charge_per_unit_id]
                    [$charge_per_unit_price->charge_color_id]
                )) {
                    $charge_per_unit_price_mapping[$charge_per_unit_price->product_type_id]
                    [$charge_per_unit_price->charge_per_unit_id]
                    [$charge_per_unit_price->charge_color_id] = array();
                }

                // Unit Placements
                if(!isset(
                    $charge_per_unit_price_mapping[$charge_per_unit_price->product_type_id]
                    [$charge_per_unit_price->charge_per_unit_id]
                    [$charge_per_unit_price->charge_color_id]
                    [$charge_per_unit_price->unit_placement_id]
                )) {
                    $charge_per_unit_price_mapping[$charge_per_unit_price->product_type_id]
                    [$charge_per_unit_price->charge_per_unit_id]
                    [$charge_per_unit_price->charge_color_id]
                    [$charge_per_unit_price->unit_placement_id] = $charge_per_unit_price->price;
                }
            }
        }

        return $charge_per_unit_price_mapping;
    }

    /*
     * Get Print Mapping with Account ID
     *
     * @param: integer $account_id
     *
     * @return: mixed
     */
    private function getPrintMapping($account_id) {
        $product_type = ProductTypes::where(array(
            'account_id' => $account_id,
            'active' => 1
        ))
            ->where('slug', '!=', Config::get('constants.product_type_slug_simple'))
            ->where('slug', '!=', Config::get('constants.product_type_slug_embroidery'))
            ->orderBy('sort_number', 'asc')
            ->select('id', 'name', 'slug', 'account_id')
            ->first();

        $charge_per_unit_price_mapping = $this->getPrintPriceMapping($account_id, Config::get('constants.product_type_slug_print'));

        $price_mapping = [];

        if ($product_type) {

            $price_mapping[$product_type->id] = array(
                'id' => $product_type->id,
                'name' => $product_type->name,
                'charge_per_units' => [],
                'unit_placements' => [],
            );

            $charge_per_units = ChargePerUnits::where(array(
                'product_type_id' => $product_type->id,
                'account_id' => $product_type->account_id,
                'active' => 1
            ))
                ->orderBy('sort_number', 'asc')
                ->select('id', 'name', 'qty_min', 'qty_max', 'product_type_id', 'account_id')
                ->get();

            $unit_placements = UnitPlacements::where(array(
                'account_id' => $product_type->account_id,
                'product_type_id' => $product_type->id,
                'active' => 1
            ))
                ->orderBy('sort_number', 'asc')
                ->select('id', 'name', 'qty', 'product_type_id', 'account_id')
                ->get();

            if ($charge_per_units && $unit_placements) {
                /*
                 * Arrange Mapping and Pricing
                 */
                foreach ($unit_placements as $unit_placement) {
                    $price_mapping[$product_type->id]['unit_placements'][$unit_placement->id] = array(
                        'name' => $unit_placement->name,
                        'qty' => $unit_placement->qty,
                    );
                }

                foreach ($charge_per_units as $charge_per_unit) {
                    $price_mapping[$product_type->id]['charge_per_units'][$charge_per_unit->id]
                        = array(
                        'name' => $charge_per_unit->name,
                        'qty_min' => $charge_per_unit->qty_min,
                        'qty_max' => $charge_per_unit->aty_max,
                        'charge_colors' => [],
                    );

                    $charge_colors = ChargeColors::where(array(
                        'account_id' => $product_type->account_id,
                        'product_type_id' => $product_type->id,
                        'charge_per_unit_id' => $charge_per_unit->id,
                        'active' => 1
                    ))
                        ->orderBy('sort_number', 'asc')
                        ->select('id', 'name', 'qty', 'product_type_id', 'charge_per_unit_id', 'account_id')
                        ->get();

                    if($charge_colors) {
                        foreach($charge_colors as $charge_color) {
                            $price_mapping[$product_type->id]['charge_per_units'][$charge_per_unit->id]['charge_colors'][$charge_color->id] = array(
                                'name' => $charge_color->name,
                                'qty' => $charge_color->qty,
                                'unit_placements' => [],
                            );

                            foreach ($unit_placements as $unit_placement) {
                                $price_mapping[$product_type->id]
                                ['charge_per_units'][$charge_per_unit->id]
                                ['charge_colors'][$charge_color->id]
                                ['unit_placements'][$unit_placement->id] = array(
                                    'name' => $unit_placement->name,
                                    'qty' => $unit_placement->qty,
                                );

                                if(
                                    isset($charge_per_unit_price_mapping[$product_type->id])
                                    && isset($charge_per_unit_price_mapping[$product_type->id][$charge_per_unit->id])
                                    && isset(
                                        $charge_per_unit_price_mapping[$product_type->id]
                                        [$charge_per_unit->id]
                                        [$charge_color->id]
                                    )
                                    && isset(
                                        $charge_per_unit_price_mapping[$product_type->id]
                                        [$charge_per_unit->id]
                                        [$charge_color->id]
                                        [$unit_placement->id]
                                    )
                                ) {
                                    $price_mapping[$product_type->id]
                                    ['charge_per_units'][$charge_per_unit->id]
                                    ['charge_colors'][$charge_color->id]
                                    ['unit_placements'][$unit_placement->id]['price'] = $charge_per_unit_price_mapping
                                    [$product_type->id]
                                    [$charge_per_unit->id]
                                    [$charge_color->id]
                                    [$unit_placement->id];
                                } else {
                                    $price_mapping[$product_type->id]
                                    ['charge_per_units'][$charge_per_unit->id]
                                    ['charge_colors'][$charge_color->id]
                                    ['unit_placements'][$unit_placement->id]['price'] = 0;
                                }
                            }
                        }
                    }
                }
            }

            return $price_mapping[$product_type->id];
        } else {
            return [];
        }
    }


    /**********************
     * Setup Charges Started
     */

    /*
     * Get Mapping with Account ID
     *
     * @param: integer $account_id
     *
     * @return: mixed
     */
    private function getSetupPriceMapping($account_id, $product_type_slug = false) {
        $setup_charge_price_mapping = [];

        if(!$product_type_slug) {
            return $setup_charge_price_mapping;
        }

        $product_type = ProductTypes::where(array(
            'account_id' => $account_id,
            'active' => 1,
            'slug' => $product_type_slug
        ))
            ->select('id')
            ->first();

        if(!$product_type) {
            return $setup_charge_price_mapping;
        }

        $setup_charge_prices = SetupChargePrices::where([
            'account_id' => $account_id,
            'product_type_id' => $product_type->id,
        ])->get();

        if($setup_charge_prices) {
            foreach($setup_charge_prices as $setup_charge_price) {
                // Set Account Type ID
                if(!isset($setup_charge_price_mapping[$setup_charge_price->product_type_id])) {
                    $setup_charge_price_mapping[$setup_charge_price->product_type_id] = array();
                }

                // Set Setup Charge ID
                if(!isset(
                    $setup_charge_price_mapping[$setup_charge_price->product_type_id]
                    [$setup_charge_price->setup_charge_id]
                )) {
                    $setup_charge_price_mapping[$setup_charge_price->product_type_id]
                    [$setup_charge_price->setup_charge_id] = array();
                }

                // Charge Placements
                if(!isset(
                    $setup_charge_price_mapping[$setup_charge_price->product_type_id]
                    [$setup_charge_price->setup_charge_id]
                    [$setup_charge_price->charge_placement_id]
                )) {
                    $setup_charge_price_mapping[$setup_charge_price->product_type_id]
                    [$setup_charge_price->setup_charge_id]
                    [$setup_charge_price->charge_placement_id] = $setup_charge_price->price;
                }
            }
        }

        return $setup_charge_price_mapping;
    }

    /*
     * Get Mapping with Account ID
     *
     * @param: integer $account_id
     *
     * @return: mixed
     */
    private function getSetupMapping($account_id) {
        $product_type = ProductTypes::where(array(
            'account_id' => $account_id,
            'active' => 1
        ))
            ->where('slug', '!=', Config::get('constants.product_type_slug_simple'))
            ->where('slug', '!=', Config::get('constants.product_type_slug_print'))
            ->orderBy('sort_number', 'asc')
            ->select('id', 'name', 'slug', 'account_id')
            ->first();

        $setup_charge_price_mapping = $this->getSetupPriceMapping($account_id, Config::get('constants.product_type_slug_embroidery'));

        $price_mapping = [];

        if ($product_type) {
            $price_mapping[$product_type->id] = array(
                'name' => $product_type->name,
                'setup_charges' => [],
                'charge_placements' => [],
            );

            $setup_charges = SetupCharges::where(array(
                'product_type_id' => $product_type->id,
                'account_id' => $product_type->account_id,
                'active' => 1
            ))
                ->orderBy('sort_number', 'asc')
                ->select('id', 'name', 'qty_min', 'qty_max', 'product_type_id', 'account_id')
                ->get();

            $charge_placements = ChargePlacements::where(array(
                'account_id' => $product_type->account_id,
                'product_type_id' => $product_type->id,
                'active' => 1
            ))
                ->orderBy('sort_number', 'asc')
                ->select('id', 'name', 'qty', 'product_type_id', 'account_id')
                ->get();

            if ($setup_charges && $charge_placements) {
                /*
                 * Arrange Mapping and Pricing
                 */
                foreach ($charge_placements as $charge_placement) {
                    $price_mapping[$product_type->id]['charge_placements'][$charge_placement->id] = array(
                        'name' => $charge_placement->name,
                        'qty' => $charge_placement->qty,
                    );
                }
                foreach ($setup_charges as $setup_charge) {
                    $price_mapping[$product_type->id]['setup_charges'][$setup_charge->id]
                        = array(
                        'name' => $setup_charge->name,
                        'qty_min' => $setup_charge->qty_min,
                        'qty_max' => $setup_charge->qty_max,
                        'charge_placements' => [],
                    );

                    foreach ($charge_placements as $charge_placement) {
                        $price_mapping[$product_type->id]
                        ['setup_charges'][$setup_charge->id]
                        ['charge_placements'][$charge_placement->id] = array(
                            'name' => $charge_placement->name,
                            'qty' => $charge_placement->qty,
                        );

                        if(
                            isset($setup_charge_price_mapping[$product_type->id])
                            && isset($setup_charge_price_mapping[$product_type->id][$setup_charge->id])
                            && isset(
                                $setup_charge_price_mapping[$product_type->id]
                                [$setup_charge->id]
                                [$charge_placement->id]
                            )
                        ) {
                            $price_mapping[$product_type->id]
                            ['setup_charges'][$setup_charge->id]
                            ['charge_placements'][$charge_placement->id]['price'] = $setup_charge_price_mapping
                            [$product_type->id]
                            [$setup_charge->id]
                            [$charge_placement->id];
                        } else {
                            $price_mapping[$product_type->id]
                            ['setup_charges'][$setup_charge->id]
                            ['charge_placements'][$charge_placement->id]['price'] = 0;
                        }
                    }
                }
            }

            return $price_mapping[$product_type->id];

        } else {
            return [];
        }
    }




    /*
     * Get Mapping with Account ID
     *
     * @param: integer $account_id
     *
     * @return: mixed
     */
    private function getSetupPrintPriceMapping($account_id, $product_type_slug = false) {
        $setup_charge_price_mapping = [];

        if(!$product_type_slug) {
            return $setup_charge_price_mapping;
        }

        $product_type = ProductTypes::where(array(
            'account_id' => $account_id,
            'active' => 1,
            'slug' => $product_type_slug
        ))
            ->select('id')
            ->first();

        if(!$product_type) {
            return $setup_charge_price_mapping;
        }

        $setup_charge_prices = SetupChargePrices::where([
            'account_id' => $account_id,
            'product_type_id' => $product_type->id,
        ])->get();

        if($setup_charge_prices) {
            foreach($setup_charge_prices as $setup_charge_price) {
                // Set Account Type ID
                if(!isset($setup_charge_price_mapping[$setup_charge_price->product_type_id])) {
                    $setup_charge_price_mapping[$setup_charge_price->product_type_id] = array();
                }

                // Set Setup Charge
                if(!isset(
                    $setup_charge_price_mapping[$setup_charge_price->product_type_id]
                    [$setup_charge_price->setup_charge_id]
                )) {
                    $setup_charge_price_mapping[$setup_charge_price->product_type_id]
                    [$setup_charge_price->setup_charge_id] = array();
                }

                // Set Setup Color
                if(!isset(
                    $setup_charge_price_mapping[$setup_charge_price->product_type_id]
                    [$setup_charge_price->setup_charge_id]
                    [$setup_charge_price->setup_color_id]
                )) {
                    $setup_charge_price_mapping[$setup_charge_price->product_type_id]
                    [$setup_charge_price->setup_charge_id]
                    [$setup_charge_price->setup_color_id]= array();
                }

                // Charge Placements
                if(!isset(
                    $setup_charge_price_mapping[$setup_charge_price->product_type_id]
                    [$setup_charge_price->setup_charge_id]
                    [$setup_charge_price->setup_color_id]
                    [$setup_charge_price->charge_placement_id]
                )) {
                    $setup_charge_price_mapping[$setup_charge_price->product_type_id]
                    [$setup_charge_price->setup_charge_id]
                    [$setup_charge_price->setup_color_id]
                    [$setup_charge_price->charge_placement_id] = $setup_charge_price->price;
                }
            }
        }

        return $setup_charge_price_mapping;
    }

    /*
     * Get Mapping with Account ID
     *
     * @param: integer $account_id
     *
     * @return: mixed
     */
    private function getSetupPrintMapping($account_id) {
        $product_type = ProductTypes::where(array(
            'account_id' => $account_id,
            'active' => 1
        ))
            ->where('slug', '!=', Config::get('constants.product_type_slug_simple'))
            ->where('slug', '!=', Config::get('constants.product_type_slug_embroidery'))
            ->orderBy('sort_number', 'asc')
            ->select('id', 'name', 'slug', 'account_id')
            ->first();

        $setup_charge_price_mapping = $this->getSetupPrintPriceMapping($account_id, Config::get('constants.product_type_slug_print'));

        $price_mapping = [];

        if ($product_type) {
            $price_mapping[$product_type->id] = array(
                'name' => $product_type->name,
                'setup_charges' => [],
                'charge_placements' => [],
            );

            $setup_charges = SetupCharges::where(array(
                'product_type_id' => $product_type->id,
                'account_id' => $product_type->account_id,
                'active' => 1
            ))
                ->orderBy('sort_number', 'asc')
                ->select('id', 'name', 'qty_min', 'qty_max', 'product_type_id', 'account_id')
                ->get();

            $charge_placements = ChargePlacements::where(array(
                'account_id' => $product_type->account_id,
                'product_type_id' => $product_type->id,
                'active' => 1
            ))
                ->orderBy('sort_number', 'asc')
                ->select('id', 'name', 'qty', 'product_type_id', 'account_id')
                ->get();

            if ($setup_charges && $charge_placements) {
                /*
                 * Arrange Mapping and Pricing
                 */
                foreach ($charge_placements as $charge_placement) {
                    $price_mapping[$product_type->id]['charge_placements'][$charge_placement->id] = array(
                        'name' => $charge_placement->name,
                        'qty' => $charge_placement->qty,
                    );
                }
                foreach ($setup_charges as $setup_charge) {
                    $price_mapping[$product_type->id]['setup_charges'][$setup_charge->id]
                        = array(
                        'name' => $setup_charge->name,
                        'qty_min' => $setup_charge->qty_min,
                        'qty_max' => $setup_charge->qty_max,
                        'setup_colors' => [],
                    );

                    $setup_colors = SetupColors::where(array(
                        'account_id' => $product_type->account_id,
                        'product_type_id' => $product_type->id,
                        'setup_charge_id' => $setup_charge->id,
                        'active' => 1
                    ))
                        ->orderBy('sort_number', 'asc')
                        ->select('id', 'name', 'qty', 'product_type_id', 'setup_charge_id', 'account_id')
                        ->get();

                    if($setup_colors) {
                        foreach($setup_colors as $setup_color) {
                            $price_mapping[$product_type->id]['setup_charges'][$setup_charge->id]['setup_colors'][$setup_color->id] = array(
                                'name' => $setup_color->name,
                                'qty' => $setup_color->qty,
                                'unit_placements' => [],
                            );

                            foreach ($charge_placements as $charge_placement) {
                                $price_mapping[$product_type->id]
                                ['setup_charges'][$setup_charge->id]
                                ['setup_colors'][$setup_color->id]
                                ['charge_placements'][$charge_placement->id] = array(
                                    'name' => $charge_placement->name,
                                    'qty' => $charge_placement->qty,
                                    'price' => 0,
                                );

                                if(
                                    isset($setup_charge_price_mapping[$product_type->id])
                                    && isset($setup_charge_price_mapping[$product_type->id][$setup_charge->id])
                                    && isset(
                                        $setup_charge_price_mapping[$product_type->id]
                                        [$setup_charge->id]
                                        [$setup_color->id]
                                    )
                                    && isset(
                                        $setup_charge_price_mapping[$product_type->id]
                                        [$setup_charge->id]
                                        [$setup_color->id]
                                        [$charge_placement->id]
                                    )
                                ) {
                                    $price_mapping[$product_type->id]
                                    ['setup_charges'][$setup_charge->id]
                                    ['setup_colors'][$setup_color->id]
                                    ['charge_placements'][$charge_placement->id]['price'] = $setup_charge_price_mapping
                                    [$product_type->id]
                                    [$setup_charge->id]
                                    [$setup_color->id]
                                    [$charge_placement->id];
                                } else {
                                    $price_mapping[$product_type->id]
                                    ['setup_charges'][$setup_charge->id]
                                    ['setup_colors'][$setup_color->id]
                                    ['charge_placements'][$charge_placement->id]['price'] = 0;
                                }
                            }

                        }
                    }
                }
            }

            return $price_mapping[$product_type->id];
        } else {
            return [];
        }
    }
}
