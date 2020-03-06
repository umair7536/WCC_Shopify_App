<?php

namespace App\Console\Commands\Shopify;

use App\Helpers\LeopardsHelper;
use App\Models\ShopifyJobs;
use Illuminate\Console\Command;

class MarkOrderAsPaid extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shopify:mark-orders-paid';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark Orders as paid in Shopify';

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
                    'is_processing' => 0,
                    'type' => 'mark-order-status'
                ])
                ->offset(0)
                ->limit(50)
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
                    $result = $this->markOrderAsPaid($payload);

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
            echo $e->getLine() . "\n";
            echo $e->getMessage() . "\n";
            echo 'Exception came';
            echo "\n";
            echo "\n";
        }
    }


    /**
     * Sync Orders from Shopify to System
     *
     * @param: void
     *
     * @return: true|false
     */
    private function markOrderAsPaid($payload) {

        LeopardsHelper::markPacketAsPaid($payload['invoice_date'], $payload['invoice_number'], $payload['track_number'], $payload['booked_packet_status']);

        return true;
    }
}
