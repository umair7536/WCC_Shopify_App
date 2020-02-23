<?php

namespace App\Console\Commands\Shopify;

use App\Models\ShopifyJobs;
use App\Models\ShopifyProductImages;
use App\Models\ShopifyProductOptions;
use App\Models\ShopifyProducts;
use App\Models\ShopifyProductTags;
use App\Models\ShopifyProductVariants;
use App\Models\ShopifyTags;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Config;
use ZfrShopify\ShopifyClient;

class SyncProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shopify:sync-products';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Products from Shopify server';

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

            $jobs = ShopifyJobs
                ::where([
                    'is_processing' => 0,
                    'type' => 'sync-products'
                ])
                ->offset(0)
                ->limit(1)
                ->orderBy('id', 'asc')
                ->get();

            if($jobs) {
                foreach ($jobs as $job) {

                    /**
                     * Put current job in processing state
                     */
                    ShopifyJobs::where([
                        'id' => $job->id
                    ])->update([
                        'is_processing' => 1,
                    ]);

                    $payload = json_decode($job->payload, true);
                    $result = $this->syncProducts($payload['shop']);

                    echo 'Result is: ' . ($result) ? 'true' : 'false';

                    if($result) {
                        ShopifyJobs::where([
                            'id' => $job->id
                        ])->delete();
                    } else {
                        ShopifyJobs::where([
                            'id' => $job->id
                        ])->update(array(
                            'attempts' => 1
                        ));
                    }
                }
            }

        } catch(\Exception $e) {
            echo "\n";
            echo 'Exception came';
            echo "\n";
            echo "\n";
        }
    }


    /**
     * Sync Products from Shopify to System
     *
     * @param: void
     *
     * @return: true|false
     */
    private function syncProducts($shop) {
        if($shop['access_token']) {
            $shopifyClient = new ShopifyClient([
                'private_app' => false,
                'api_key' => env('SHOPIFY_APP_API_KEY'), // In public app, this is the app ID
                'version' => env('SHOPIFY_API_VERSION'), // Put API Version
                'access_token' => $shop['access_token'],
                'shop' => $shop['myshopify_domain']
            ]);

            $products = $shopifyClient->getProductsIterator([
                'since_id' => 0
            ]);

            foreach ($products as $product) {
                if(!isset($product['id'])) {
                    break;
                }

                /*
                 * Prepare record before insert
                 */
                $product['product_id'] = $product['id'];
                unset($product['id']);
                $product_processed = ShopifyProducts::prepareRecord($product);
                $product_processed['account_id'] = $shop['account_id'];

                $product_record = ShopifyProducts::where([
                    'product_id' => $product_processed['product_id'],
                    'account_id' => $product_processed['account_id'],
                ])->select('id')->first();

                if($product_record) {
                    //echo 'Product Updated: ' . $product_processed['title'] . "\n";
                    ShopifyProducts::where([
                        'product_id' => $product_processed['product_id'],
                        'account_id' => $product_processed['account_id'],
                    ])->update($product_processed);
                } else {
                    //echo 'Product Created: ' . $product_processed['title'] . "\n";
                    ShopifyProducts::create($product_processed);
                }

                /*
                 * Create Tag based on Product Tags
                 */
                $tags = explode(', ', $product['tags']);

                if(count($tags)) {

                    $tags_for_relationshop = [];

                    foreach ($tags as $tag) {

                        $tag_processed = array(
                            'account_id' => $product_processed['account_id'],
                            'name' => $tag,
                            'active' => 1,
                            'updated_at' => Carbon::now()->toDateTimeString(),
                        );

                        $tag_record = ShopifyTags::where([
                            'name' => $tag,
                            'account_id' => $product_processed['account_id'],
                        ])->select('id')->first();

                        if($tag_record) {
                            //echo 'Tag Updated: ' . $tag . "\n";
                            ShopifyTags::where([
                                'name' => $tag,
                                'account_id' => $product_processed['account_id'],
                            ])->update($tag_processed);
                        } else {
                            //echo 'Tag Created: ' . $tag . "\n";
                            $tag_processed['created_at'] = Carbon::now()->toDateTimeString();
                            $tag_record = ShopifyTags::create($tag_processed);
                        }

                        /*
                         * Prepare Product Tag Relationshops
                         */
                        $tags_for_relationshop[] = array(
                            'product_id' => $product_processed['product_id'],
                            'account_id' => $product_processed['account_id'],
                            'tag_id' => $tag_record->id,
                        );
                    }

                    /*
                     * Delete previous added product tags
                     */
                    ShopifyProductTags::where([
                        'product_id' => $product_processed['product_id'],
                        'account_id' => $shop['account_id'],
                    ])->delete();

                    if(count($tags_for_relationshop)) {
                        ShopifyProductTags::insert($tags_for_relationshop);
                    }
                }


                /*
                 * Sync Product Images
                 */
                if(count($product['images'])) {

                    $images = [];

                    /**
                     * Delete records
                     */
                    ShopifyProductImages::where(array(
                        'product_id' => $product['product_id'],
                        'account_id' => $shop['account_id'],
                    ))->forceDelete();

                    foreach($product['images'] as $image) {

                        $image['image_id'] = $image['id'];
                        unset($image['id']);
                        $image_processed = ShopifyProductImages::prepareRecord($image);
                        $image_processed['account_id'] = $shop['account_id'];

                        $images[$image['image_id']] = $image_processed;
                    }

                    if(count($images)) {
                        ShopifyProductImages::insert($images);
                    }
                }

                /*
                 * Sync Product Options
                 */
                if(count($product['options'])) {

                    $options = [];

                    /**
                     * Delete records
                     */
                    ShopifyProductOptions::where(array(
                        'product_id' => $product['product_id'],
                        'account_id' => $shop['account_id'],
                    ))->forceDelete();

                    foreach($product['options'] as $option) {

                        $option['option_id'] = $option['id'];
                        unset($option['id']);
                        $option_processed = ShopifyProductOptions::prepareRecord($option);
                        $option_processed['account_id'] = $shop['account_id'];

                        $options[$option['option_id']] = $option_processed;
                    }

                    if(count($options)) {
                        ShopifyProductOptions::insert($options);
                    }
                }

                /*
                 * Sync Product Variants
                 */
                if(count($product['variants'])) {

                    $variants = [];

                    /**
                     * Delete records
                     */
                    ShopifyProductVariants::where(array(
                        'product_id' => $product['product_id'],
                        'account_id' => $shop['account_id'],
                    ))->forceDelete();

                    foreach($product['variants'] as $variant) {
                        $variant['variant_id'] = $variant['id'];
                        unset($variant['id']);
                        $variant_processed = ShopifyProductVariants::prepareRecord($variant);
                        $variant_processed['account_id'] = $shop['account_id'];

                        $variants[$variant['variant_id']] = $variant_processed;
                    }

                    if(count($variants)) {
                        ShopifyProductVariants::insert($variants);
                    }
                }

                //echo '---- Product End ----' . "\n\n\n";
            }
        }

        return true;
    }
}
