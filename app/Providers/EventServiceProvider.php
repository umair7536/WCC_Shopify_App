<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'App\Events\Shopify\Products\SyncProductsFire' => [
            'App\Listeners\Shopify\Products\SyncProdductsListener',
        ],
        'App\Events\Shopify\Products\UploadVariantsFire' => [
            'App\Listeners\Shopify\Products\UploadVariantsListener',
        ],
        'App\Events\Shopify\Products\SyncCustomersFire' => [
            'App\Listeners\Shopify\Products\SyncCustomersListener',
        ],
        'App\Events\Shopify\Locations\SyncLocationsFire' => [
            'App\Listeners\Shopify\Locations\SyncLocationsListener',
        ],
        'App\Events\Shopify\Products\SyncCustomCollecionsFire' => [
            'App\Listeners\Shopify\Products\SyncCustomCollectionsListener',
        ],
        'App\Events\Shopify\Products\SyncCollectsFire' => [
            'App\Listeners\Shopify\Products\SyncCollectsListener',
        ],
        'App\Events\Shopify\Orders\SyncOrdersFire' => [
            'App\Listeners\Shopify\Orders\SyncOrdersListener',
        ],
        'App\Events\Leopards\SyncLeopardsCitiesFire' => [
            'App\Listeners\Leopards\SyncLeopardsCitiesListener',
        ],
        'App\Events\Shopify\Orders\SingleOrderFire' => [
            'App\Listeners\Shopify\Orders\SingleOrderListener',
        ],
        'App\Events\Shopify\Orders\SingleOrderFulfillmentFire' => [
            'App\Listeners\Shopify\Orders\SingleOrderFulfillmentListener',
        ],
        'App\Events\Shopify\Webhooks\CreateWebhooksFire' => [
            'App\Listeners\Shopify\Webhooks\CreateWebhooksListener',
        ],
        'App\Events\Leopards\BookedPackets\FullSyncPacketStatusFire' => [
            'App\Listeners\Leopards\BookedPackets\FullSyncPacketStatusListener',
        ],
        'App\Events\Leopards\BookedPackets\SingleOrderBookFire' => [
            'App\Listeners\Leopards\BookedPackets\SingleOrderBookListener',
        ],
        'App\Events\Shopify\Customers\SingleCustomerFire' => [
            'App\Listeners\Shopify\Customers\SingleCustomerListener',
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
