<?php

namespace App\Console\Commands\Leopards;

use App\Models\BookedPackets;
use App\Models\ShopifyJobs;
use Carbon\Carbon;
use Developifynet\LeopardsCOD\LeopardsCODClient;
use Illuminate\Console\Command;
use Config;

class SyncPacketStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lcs:sync-packet-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Packet Status from LCS server';

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
                    'type' => 'sync-packet-status'
                ])
                ->offset(0)
                ->limit(4)
                ->orderBy('id', 'asc')
                ->get();

            if($jobs) {
                foreach ($jobs as $job) {
                    $payload = json_decode($job->payload, true);

                    $result = $this->syncPacketStatus($payload['offset'], $payload['records_per_page'], $payload['leopards'], $job->account_id);
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
    private function syncPacketStatus($offset, $records_per_page, $lcs, $account_id) {

        $status_sync = Config::get('constants.status_sync');
        $status = Config::get('constants.status');
        $status_delivered = Config::get('constants.status_delivered');

        $booked_packets = BookedPackets::where([
            'account_id' => $account_id,
            'booking_type' => 2, /** '1' for Test, '2' for Live Packets */
            'marked_paid' => 0
        ])
            ->where('status_check_count', '<=', 50)
            ->whereIn('status', $status_sync)
            ->limit($records_per_page)
            ->offset($offset)
            ->select('id', 'track_number')
            ->get()->pluck('track_number', 'id');

        try {

            if($booked_packets->count()) {

                foreach($booked_packets as $id => $track_number) {
                    BookedPackets::where([
                        'id' => $id,
                    ])->increment('status_check_count');
                }

                $leopards = new LeopardsCODClient();

                $response = $leopards->trackPacket(array(
                    'api_key' => $lcs['api_key'],               // API Key provided by LCS
                    'api_password' => $lcs['api_password'],     // API Password provided by LCS
                    'enable_test_mode' => false,                // [Optional] default value is 'false', true|false to set mode test or live
                    'track_numbers' => implode(',', $booked_packets->toArray())
                ));

                if($response['status']) {
                    if(isset($response['packet_list']) && count($response['packet_list'])) {
                        foreach ($response['packet_list'] as $booked_packet) {

                            $status_id = 0;

                            foreach ($status as $key => $value) {
                                if(strtolower($booked_packet['booked_packet_status']) == strtolower($value)) {
                                    $status_id = $key;
                                }
                            }

                            if(
                                    array_key_exists('invoice_number', $booked_packet)
                                &&  array_key_exists('invoice_date', $booked_packet)
                            ) {
                                BookedPackets::where([
                                    'track_number' => $booked_packet['track_number']
                                ])->update(array(
                                    'status' => $status_id,
                                    'invoice_number' => $booked_packet['invoice_number'],
                                    'invoice_date' => $booked_packet['invoice_date']
                                ));

                                if(
                                        $booked_packet['invoice_date']
                                    &&  $booked_packet['invoice_number']
                                    &&  ($status_delivered == $status_id)
                                ) {
                                    /**
                                     * Packet is delivered, this is time to mark payment as paid
                                     */
                                    if($status_delivered == $status_id) {
                                        /**
                                         * Put Change Paid Status into Jobs
                                         */
                                        $payload = array(
                                            'invoice_date' => $booked_packet['invoice_date'],
                                            'invoice_number' => $booked_packet['invoice_number'],
                                            'track_number' => $booked_packet['track_number'],
                                            'booked_packet_status' => $booked_packet['booked_packet_status'],
                                        );

                                        ShopifyJobs::insert(array(
                                            'payload' => json_encode($payload),
                                            'type' => 'mark-order-status',
                                            'created_at' => Carbon::now()->toDateTimeString(),
                                            'available_at' => Carbon::now()->toDateTimeString(),
                                            'account_id' => $account_id,
                                        ));
                                    }
                                }
                            } else {
                                BookedPackets::where([
                                    'track_number' => $booked_packet['track_number']
                                ])->update(array(
                                    'status' => $status_id
                                ));
                            }
                        }
                    }
                }
            }
        } catch (\Exception $exception) {

        }

        return true;
    }
}
