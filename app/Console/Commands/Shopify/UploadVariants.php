<?php

namespace App\Console\Commands\Shopify;

use App\Models\ShopifyJobs;
use App\Models\ShopifyProductVariants;
use Illuminate\Console\Command;
use Config;
use ZfrShopify\ShopifyClient;

class UploadVariants extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shopify:upload-variants';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Upload Variants to Shopify server';

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
                    'attempts' => 0,
                    'type' => 'upload-variants'
                ])
                ->offset(0)
                ->limit(100)
                ->orderBy('id', 'asc')
                ->get();

            if($jobs) {
                foreach ($jobs as $job) {

                    $payload = json_decode($job->payload, true);

                    $result = $this->uploadVariants($payload['variant'], $payload['shop']);

                    if(count($result) && $result['id']) {
                        ShopifyJobs::where([
                            'id' => $job->id
                        ])->delete();

                        ShopifyProductVariants::where(array(
                            'variant_id' => $result['id']
                        ))->update(array(
                            'price' => $result['price'],
                            'compare_at_price' => $result['compare_at_price'],
                        ));
                    }
                }
            }

        } catch(\Exception $e) {
            echo $e->getMessage();
            echo "\n";
            echo 'Exception came';
            echo "\n";
            echo "\n";
        }

        return true;
    }


    /**
     * Upload Variant to Shopify System
     *
     * @param: void
     *
     * @return: true|false
     */
    private function uploadVariants($variant, $shop) {
        $response = [];

        if($shop['access_token']) {

            $shopifyClient = new ShopifyClient([
                'private_app' => false,
                'api_key' => env('SHOPIFY_APP_API_KEY'), // In public app, this is the app ID
                'access_token' => $shop['access_token'],
                'shop' => $shop['myshopify_domain']
            ]);

            $variant['id'] = (int) $variant['id'];
            $response = $shopifyClient->updateProductVariant($variant);
        }

        return $response;
    }
}
