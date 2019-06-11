<?php

namespace App\Jobs\Shopify\Products;

use App\Models\ShopifyProductImages;
use App\Models\ShopifyProductOptions;
use App\Models\ShopifyProducts;
use App\Models\ShopifyProductTags;
use App\Models\ShopifyProductVariants;
use App\Models\ShopifyShops;
use App\Models\ShopifyTags;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Redis;
use ZfrShopify\ShopifyClient;

class SyncProductsProcess implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Offset of Products
     */
    protected $offset;

    /**
     * Products Sync Limit
     */
    protected $limit;

    /**
     * Hold shopify shop object
     */
    protected $shop;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($offset, $limit, ShopifyShops $shop)
    {
        $this->offset = $offset;
        $this->limit = $limit;
        $this->shop = $shop;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Redis::throttle('SyncProductsProcess')->allow(40)->every(60)->then(function () {

            try {
                $this->syncProducts();
            } catch(Exception $e) {
                echo "\n";
                echo 'Exception came';
                echo "\n";
                echo "\n";
            }

            return true;

        }, function () {
            // Could not obtain lock...
            return $this->release(5);
        });
    }


    /**
     * Sync Products from Shopify to System
     *
     * @param: void
     *
     * @return: true|false
     */
    private function syncProducts() {
        if($this->shop->access_token) {
            $shopifyClient = new ShopifyClient([
                'private_app' => false,
                'api_key' => env('SHOPIFY_APP_API_KEY'), // In public app, this is the app ID
                'access_token' => $this->shop->access_token,
                'shop' => $this->shop->myshopify_domain
            ]);

            $products = $shopifyClient->getProducts([
                'limit' => $this->limit,
                'page' => $this->offset
            ]);

            echo 'Limit: ' . $this->limit . "\n";
            echo 'Offset: ' . $this->offset . "\n";

            if(count($products)) {

                //echo 'Total Products: ' . count($products) . "\n";

                foreach ($products as $product) {

                    //echo '---- Product Start ----' . "\n";

                    /*
                     * Prepare record before insert
                     */
                    $product['product_id'] = $product['id'];
                    unset($product['id']);
                    $product_processed = ShopifyProducts::prepareRecord($product);
                    $product_processed['account_id'] = $this->shop->account_id;

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
                            'account_id' => $this->shop->account_id,
                        ])->delete();

                        if(count($tags_for_relationshop)) {
                            ShopifyProductTags::insert($tags_for_relationshop);
                        }
                    }


                    /*
                     * Sync Product Images
                     */
                    if(count($product['images'])) {
                        foreach($product['images'] as $image) {
                            /*
                             * Prepare record before insert
                             */
                            $image['image_id'] = $image['id'];
                            unset($image['id']);
                            $image_processed = ShopifyProductImages::prepareRecord($image);
                            $image_processed['account_id'] = $this->shop->account_id;

                            $image_record = ShopifyProductImages::where([
                                'image_id' => $image_processed['image_id'],
                                'account_id' => $image_processed['account_id'],
                            ])->first();

                            if($image_record) {
                                ////echo 'Image Updated: ' . $image_processed['image_id'] . "\n";
                                ShopifyProductImages::where([
                                    'image_id' => $image_processed['image_id'],
                                    'account_id' => $image_processed['account_id'],
                                ])->update($image_processed);
                            } else {
                                //echo 'Image Created: ' . $image_processed['image_id'] . "\n";
                                ShopifyProductImages::create($image_processed);
                            }
                        }
                    }

                    /*
                     * Sync Product Options
                     */
                    if(count($product['options'])) {
                        foreach($product['options'] as $option) {
                            /*
                             * Prepare record before insert
                             */
                            $option['option_id'] = $option['id'];
                            unset($option['id']);
                            $option_processed = ShopifyProductOptions::prepareRecord($option);
                            $option_processed['account_id'] = $this->shop->account_id;

                            $option_record = ShopifyProductOptions::where([
                                'option_id' => $option_processed['option_id'],
                                'account_id' => $option_processed['account_id'],
                            ])->first();

                            if($option_record) {

                                //echo 'Option Updated: ' . $option_processed['option_id'] . "\n";

                                ShopifyProductOptions::where([
                                    'option_id' => $option_processed['option_id'],
                                    'account_id' => $option_processed['account_id'],
                                ])->update($option_processed);
                            } else {

                                //echo 'Option Created: ' . $option_processed['option_id'] . "\n";

                                ShopifyProductOptions::create($option_processed);
                            }
                        }
                    }

                    /*
                     * Sync Product Variants
                     */
                    if(count($product['variants'])) {
                        foreach($product['variants'] as $variant) {
                            /*
                             * Prepare record before insert
                             */
                            $variant['variant_id'] = $variant['id'];
                            unset($variant['id']);
                            $variant_processed = ShopifyProductVariants::prepareRecord($variant);
                            $variant_processed['account_id'] = $this->shop->account_id;

                            $variant_record = ShopifyProductVariants::where([
                                'variant_id' => $variant_processed['variant_id'],
                                'account_id' => $variant_processed['account_id'],
                            ])->first();

                            if($variant_record) {

                                //echo 'Variant Updated: ' . $variant_processed['variant_id'] . "\n";

                                ShopifyProductVariants::where([
                                    'variant_id' => $variant_processed['variant_id'],
                                    'account_id' => $variant_processed['account_id'],
                                ])->update($variant_processed);
                            } else {

                                //echo 'Variant Created: ' . $variant_processed['variant_id'] . "\n";

                                ShopifyProductVariants::create($variant_processed);
                            }
                        }
                    }

                    //echo '---- Product End ----' . "\n\n\n";
                }
            } else {
                echo 'No Products fetched' . "\n";
            }
        }

        return;
    }
}
