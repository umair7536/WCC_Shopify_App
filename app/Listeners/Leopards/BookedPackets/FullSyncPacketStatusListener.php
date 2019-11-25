<?php

namespace App\Listeners\Leopards\BookedPackets;

use App\Events\Leopards\BookedPackets\FullSyncPacketStatusFire;
use App\Models\BookedPackets;
use App\Models\LeopardsSettings;
use App\Models\ShopifyJobs;
use Carbon\Carbon;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Config;

class FullSyncPacketStatusListener implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        echo 'I have been called';
    }

    /**
     * Handle the event.
     *
     * @param  FullSyncPacketStatusFire  $event
     * @return void
     */
    public function handle(FullSyncPacketStatusFire $event)
    {
        if($event->account->id) {

            $status_sync = Config::get('constants.status_sync');

            $leopards_settings = LeopardsSettings::where([
                'account_id' => $event->account->id
            ])
                ->select('slug', 'data')
                ->get()->keyBy('slug');

            if($leopards_settings->count()) {

                $status_sync = Config::get('constants.status_sync');

                $total_records = BookedPackets::where([
                    'account_id' => $event->account->id,
                    'booking_type' => 2 /** '1' for Test, '2' for Live Packets */
                ])
                    ->whereIn('status', $status_sync)
                    ->count();

                echo 'total records: ' . $total_records;

                if($total_records) {
                    $records_per_page = 20;

                    $total_calls = ceil($total_records / $records_per_page);

                    if($total_calls) {

                        $jobs = [];

                        for($i = 0; $i < $total_calls; $i++) {
                            $offset = $i;

                            /**
                             * Payload
                             */
                            $payload = array(
                                'offset' => $offset,
                                'records_per_page' => $records_per_page,
                                'leopards' => array(
                                    'api_key' => $leopards_settings['api-key']->data,
                                    'api_password' => $leopards_settings['api-password']->data,
                                ),
                            );

                            $jobs[$i] = array(
                                'payload' => json_encode($payload),
                                'type' => 'sync-packet-status',
                                'created_at' => Carbon::now()->toDateTimeString(),
                                'available_at' => Carbon::now()->toDateTimeString(),
                                'account_id' => $event->account->id,
                            );
                        }

                        ShopifyJobs::insert($jobs);
                    }
                }

                echo 'Queue dispatched for sync products';
            }
        }
    }
}
