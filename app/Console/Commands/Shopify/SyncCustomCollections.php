<?php

namespace App\Console\Commands\Shopify;

use App\Models\ShopifyCustomCollections;
use App\Models\ShopifyJobs;
use Illuminate\Console\Command;
use ZfrShopify\ShopifyClient;

class SyncCustomCollections extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shopify:sync-custom-collections';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Custom Collections from Shopify server';

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
                    'type' => 'sync-custom-collections'
                ])
                ->offset(0)
                ->limit(4)
                ->orderBy('id', 'asc')
                ->get();

            if($jobs) {
                foreach ($jobs as $job) {
                    $payload = json_decode($job->payload, true);
                    $result = $this->syncCustomCollections($payload['offset'], $payload['records_per_page'], $payload['shop']);
                    echo 'Result is: ' . ($result) ? 'true' : 'false';
                    if($result) {
                        ShopifyJobs::where([
                            'id' => $job->id
                        ])->delete();
                    }
                }
            }

        } catch(\Exception $e) {
            echo "\n";
            echo 'Exception came';
            echo "\n" . $e->getLine() . "\n";
            echo $e->getMessage();
            echo "\n";
            echo "\n";
        }

        return true;
    }


    /**
     * Sync Custom Collections from Shopify to System
     *
     * @param: void
     *
     * @return: true|false
     */
    private function syncCustomCollections($offset, $records_per_page, $shop) {
        if($shop['access_token']) {
            $shopifyClient = new ShopifyClient([
                'private_app' => false,
                'api_key' => env('SHOPIFY_APP_API_KEY'), // In public app, this is the app ID
                'version' => env('SHOPIFY_API_VERSION'), // Put API Version
                'access_token' => $shop['access_token'],
                'shop' => $shop['myshopify_domain']
            ]);

            $custom_collections = $shopifyClient->getCustomCollections([
                'limit' => $records_per_page,
                'page' => $offset
            ]);

            echo 'Limit: ' . $records_per_page . "\n";
            echo 'Offset: ' . $offset . "\n";

            if(count($custom_collections)) {

                foreach ($custom_collections as $custom_collection) {
                    /*
                     * Prepare record before insert
                     */
                    $custom_collection['collection_id'] = $custom_collection['id'];
                    unset($custom_collection['id']);
                    $custom_collection_processed = ShopifyCustomCollections::prepareRecord($custom_collection);
                    $custom_collection_processed['account_id'] = $shop['account_id'];

                    $custom_collection_record = ShopifyCustomCollections::where([
                        'collection_id' => $custom_collection_processed['collection_id'],
                        'account_id' => $custom_collection_processed['account_id'],
                    ])->select('id')->first();

                    if($custom_collection_record) {
                        ShopifyCustomCollections::where([
                            'collection_id' => $custom_collection_processed['collection_id'],
                            'account_id' => $custom_collection_processed['account_id'],
                        ])->update($custom_collection_processed);
                    } else {
                        ShopifyCustomCollections::create($custom_collection_processed);
                    }
                }
            } else {
                echo 'No Custom Collection fetched' . "\n";
            }
        }

        return true;
    }
}
