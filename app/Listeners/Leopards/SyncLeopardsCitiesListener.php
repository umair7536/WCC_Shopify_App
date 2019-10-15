<?php

namespace App\Listeners\Leopards;

use App\Events\Leopards\SyncLeopardsCitiesFire;
use App\Models\LeopardsCities;
use App\Models\LeopardsSettings;
use Carbon\Carbon;
use Developifynet\LeopardsCOD\LeopardsCODClient;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use dispatch;

class SyncLeopardsCitiesListener implements ShouldQueue
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
     * @param  SyncLeopardsCitiesFire  $event
     * @return void
     */
    public function handle(SyncLeopardsCitiesFire $event)
    {

        if($event->account->id) {

            $leopards_settings = LeopardsSettings::where(array(
                'account_id' => $event->account->id
            ))->get()->keyBy('slug');

            $leopards = new LeopardsCODClient();
            $cities = $leopards->getAllCities(array(
                'api_key' => $leopards_settings['api-key']->data,
                'api_password' => $leopards_settings['api-password']->data,
                'enable_test_mode' => ($leopards_settings['mode']->data) ? true : false,
            ));

            if($cities['status'] && count($cities['city_list'])) {

                $city_list = [];
                foreach($cities['city_list'] as $city) {
                    $city_data = array(
                        'city_id' => $city['id'],
                        'name' => $city['name'],
                        'shipment_type' => json_encode($city['shipment_type']),
                        'account_id' => $event->account->id,
                        'updated_at' => Carbon::now()->toDateTimeString(),
                    );

                    $city_record = LeopardsCities::where([
                        'city_id' => $city['id'],
                        'account_id' => $event->account->id,
                    ])->select('id')->first();

                    if($city_record) {
                        //echo 'Product Updated: ' . $product_processed['title'] . "\n";
                        LeopardsCities::where([
                            'city_id' => $city['id'],
                            'account_id' => $event->account->id,
                        ])->update($city_data);
                    } else {
                        $city_data['created_at'] = Carbon::now()->toDateTimeString();
                        LeopardsCities::create($city_data);
                    }
                }
            }
        }
    }
}
