<?php

namespace App\Console\Commands\Shopify;

use App\Models\ShopifyCollects;
use App\Models\ShopifyCustomCollections;
use App\Models\ShopifyJobs;
use Illuminate\Console\Command;
use ZfrShopify\ShopifyClient;

class SyncCollects extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shopify:sync-collects';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Collects from Shopify server';

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
                    'type' => 'sync-collects'
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
                    $result = $this->syncCollects($payload['shop']);

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
    private function syncCollects($shop) {
        if($shop['access_token']) {
            $shopifyClient = new ShopifyClient([
                'private_app' => false,
                'api_key' => env('SHOPIFY_APP_API_KEY'), // In public app, this is the app ID
                'version' => env('SHOPIFY_API_VERSION'), // Put API Version
                'access_token' => $shop['access_token'],
                'shop' => $shop['myshopify_domain']
            ]);

            $collects = $shopifyClient->getCollectsIterator([
                'since_id' => 0
            ]);

            foreach ($collects as $collect) {
                if(!isset($collect['id'])) {
                    break;
                }

                /*
                 * Prepare record before insert
                 */
                $collect['collect_id'] = $collect['id'];
                unset($collect['id']);
                $collect_processed = ShopifyCollects::prepareRecord($collect);
                $collect_processed['account_id'] = $shop['account_id'];

                $collect_record = ShopifyCollects::where([
                    'collect_id' => $collect_processed['collect_id'],
                    'account_id' => $collect_processed['account_id'],
                ])->select('id')->first();

                if($collect_record) {
                    ShopifyCollects::where([
                        'collect_id' => $collect_processed['collect_id'],
                        'account_id' => $collect_processed['account_id'],
                    ])->update($collect_processed);
                } else {
                    ShopifyCollects::create($collect_processed);
                }
            }
        }

        return true;
    }
}
