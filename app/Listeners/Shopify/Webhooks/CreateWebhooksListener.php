<?php

namespace App\Listeners\Shopify\Webhooks;

use App\Events\Shopify\Webhooks\CreateWebhooksFire;
use App\Models\ShopifyShops;
use App\Models\ShopifyWebhooks;
use Carbon\Carbon;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Config;
use ZfrShopify\ShopifyClient;

class CreateWebhooksListener implements ShouldQueue
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
     * @param  CreateWebhooksFire  $event
     * @return void
     */
    public function handle(CreateWebhooksFire $event)
    {
        if($event->account) {
            try {
                $shop = ShopifyShops::where([
                    'account_id' => $event->account->id
                ])->first();

                if($shop) {
                    $shopifyClient = new ShopifyClient([
                        'private_app' => false,
                        'api_key' => env('SHOPIFY_APP_API_KEY'), // In public app, this is the app ID
                        'version' => env('SHOPIFY_API_VERSION'), // Put API Version
                        'access_token' => $shop->access_token,
                        'shop' => $shop->myshopify_domain
                    ]);

                    $old_webhooks = $shopifyClient->getWebhooks();

                    /**
                     * Remove all old webhooks
                     */
                    if(count($old_webhooks)) {
                        foreach ($old_webhooks as $old_webhook) {
                            $shopifyClient->deleteWebhook([
                                'id' => (int) $old_webhook['id']
                            ]);
                        }
                    }

                    /**
                     * Remove all webhooks and re-install
                     */
                    ShopifyWebhooks::where(['account_id' => $event->account->id])->forcedelete();

                    // No webhooks is available, go ahead and create them
                    $webhooks = Config::get('constants.webhooks');

                    if(count($webhooks)) {
                        foreach ($webhooks as $topic => $desc) {
                            $single_data = $shopifyClient->createWebhook(array(
                                'address' => env('APP_URL_TUNNEL') . '/webhooks/' . explode('/', $topic)[0],
                                'format' => 'json',
                                'topic' => $topic,
                            ));

                            $single_data['webhook_id'] = $single_data['id'];
                            unset($single_data['id']);
                            $single_data['account_id'] = $event->account->id;
                            $single_data['fields'] = json_encode($single_data['fields']);
                            $single_data['metafield_namespaces'] = json_encode($single_data['metafield_namespaces']);
                            $single_data['created_at'] = Carbon::parse($single_data['created_at'])->toDateTimeString();
                            $single_data['updated_at'] = Carbon::parse($single_data['updated_at'])->toDateTimeString();

                            ShopifyWebhooks::updateOrCreate(
                                ['webhook_id' => $single_data['webhook_id']],
                                $single_data
                            );
                        }

                        echo 'so far so good';
                    }
                }
            } catch (\Exception $exception) {

            }
        }
    }
}
