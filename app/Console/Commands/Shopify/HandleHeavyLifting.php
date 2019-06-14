<?php

namespace App\Console\Commands\Shopify;

use App\Models\ShopifyJobs;
use App\Models\ShopifyProductTags;
use App\Models\ShopifyProductVariants;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Config;
use App\Models\HeavyLifter;
use App\Models\ProductDiscountMaps;
use App\Models\ProductDiscountPrices;
use App\Models\ProductTypes;

class HandleHeavyLifting extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shopify:handle-heavy-lifting';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Handle large requests divided into chunks';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {

            $heavyLifts = HeavyLifter::limit(4)
                ->get();

            if($heavyLifts) {

                /**
                 * Variable holding shopify jobs
                 */
                $shopify_jobs = [];

                foreach($heavyLifts as $heavyLift) {

                    $payload = json_decode($heavyLift->payload, true);
                    $shop = $payload['shop'];

                    switch ($heavyLift->type) {
                        case 'upload-variants':

                            $productVariants = ShopifyProductVariants::where([
                                'account_id' => $shop['account_id']
                            ])
                                ->limit($payload['records_per_page'])
                                ->offset(($payload['offset'] - 1))
                                ->orderBy('id', 'asc')
                                ->get();

                            if($productVariants && $shop) {

                                /**
                                 * Variable holding shopify jobs
                                 */
                                $shopify_jobs = [];

                                foreach($productVariants as $productVariant) {

                                    $data = [
                                        'status' => false,
                                        'type' => array(
                                            'simple' => false,
                                        ),
                                        'simple_prices' => [],
                                    ];

                                    $product = array(
                                        'id' => $productVariant['product_id'],
                                        'account_id' => $productVariant['account_id'],
                                    );

                                    $data = $this->getProductDicountPrices($data, $product, $productVariant);

                                    if($data['status']) {
                                        /**
                                         * Payload
                                         */
                                        $payload = array(
                                            'variant' => array(
                                                'id' => $productVariant['variant_id'],
                                                'price' => $data['simple_price']['price'],
                                                'compare_at_price' => ($productVariant['compare_at_price']) ? $productVariant['compare_at_price'] : $productVariant['price']
                                            ),
                                            'shop' => $shop->toArray(),
                                        );
//                                        $payload = array(
//                                            'variant' => array(
//                                                'id' => $productVariant['variant_id'],
//                                                'compare_at_price' => ($productVariant['compare_at_price']) ? $productVariant['compare_at_price'] : $productVariant['price']
//                                            ),
//                                            'shop' => $shop,
//                                        );

                                        $shopify_jobs[] = array(
                                            'payload' => json_encode($payload),
                                            'type' => 'upload-variants',
                                            'created_at' => Carbon::now()->toDateTimeString(),
                                            'available_at' => Carbon::now()->toDateTimeString(),
                                            'account_id' => $shop['account_id'],
                                        );
                                    }
                                }

                                if(count($shopify_jobs)) {
                                    ShopifyJobs::insert($shopify_jobs);
                                }
                            }

                            break;
                        default:
                            break;
                    }

                    HeavyLifter::where([
                        'id' => $heavyLift->id
                    ])->forceDelete();
                }
            }

        } catch(\Exception $e) {
            echo "\n";
            echo 'Exception came';
            echo "\n";
            echo "\n";
        }

        return true;
    }




    private function getProductDicountPrices($data, $product, $product_variant) {

        $product_price = ($product_variant['compare_at_price']) ? $product_variant['compare_at_price'] : $product_variant['price'];

        $product_tags = ShopifyProductTags
            ::join('shopify_tags', 'shopify_tags.id', '=', 'shopify_product_tags.tag_id')
            ->where('shopify_product_tags.product_id', '=', $product['id'])
            ->where('shopify_product_tags.account_id', '=', $product['account_id'])
            ->select('shopify_tags.id', 'shopify_tags.name', 'shopify_product_tags.product_id', 'shopify_product_tags.account_id')
            ->get()->keyBy('id');

        if($product_tags) {
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
                    if($is_stored == 0) {
                        $product_mapSimpleFirst = $product_discount_mapSimple->toArray();
                        $is_stored = 1;
                    }

                    if(isset($product_tags[$product_discount_mapSimple->tag_id])) {

                        $product_mapSimple = $product_discount_mapSimple->toArray();
                        break;
                    }
                }

                /**
                 * Default tag not found let's provide default tag
                 */
                if(!count($product_mapSimple)) {
                    $product_mapSimple = $product_mapSimpleFirst;
                }

                // Data is present nwo process
                if(count($product_mapSimple)) {
                    $product_discount_priceSimple = ProductDiscountPrices
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
                        ->first();

                    if($product_discount_priceSimple) {

                        // Set type true
                        $data['type'][Config::get('constants.product_type_slug_simple')] = true;

                        $price_value = ($product_discount_priceSimple->price_type == 'percentage') ? ($product_price * ($product_discount_priceSimple->price_value / 100)) : $product_discount_priceSimple->price_value;

                        $data[Config::get('constants.product_type_slug_simple') . '_price'] = $product_discount_priceSimple->toArray();
                        $data[Config::get('constants.product_type_slug_simple') . '_price']['price'] = number_format(round($product_price - $price_value, 2), 2);
                    }
                }
            }

            $data['status'] = true;
        }

        return $data;
    }
}
