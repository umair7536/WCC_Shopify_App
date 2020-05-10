<?php

namespace App\Listeners\Leopards\BookedPackets;

use App\Events\Leopards\BookedPackets\SingleOrderBookFire;
use App\Events\Shopify\Orders\SingleOrderFulfillmentFire;
use App\Models\BookedPackets;
use App\Models\LeopardsSettings;
use App\Models\OrderLogs;
use App\Models\ShopifyOrders;
use App\Models\ShopifyShops;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use ZfrShopify\ShopifyClient;
use Validator;
use Config;

class SingleOrderBookListener implements ShouldQueue
{
    public $queue = 'bookpacket';

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        echo 'Single Booked Packet Called';
    }

    /**
     * Handle the event.
     *
     * @param  SingleOrderBookFire  $event
     * @return void
     */
    public function handle(SingleOrderBookFire $event)
    {
        try {
            $order = $event->order;
            $shipment_type_id = $event->shipment_type_id;
            $account_id = $event->account_id;

            $booked_packets = BookedPackets::where([
                'account_id' => $account_id,
                'order_number' => $order['order_number']
            ])
                ->select('id')
                ->first();

            if($booked_packets) {

            } else {
                /**
                 * If Order ID is provided then prepare data to automatically be filled
                 */
                $prepared_packet = BookedPackets::prepareBooking($order['order_id'], $shipment_type_id, $account_id);

                if($prepared_packet['status']) {
                    $booking_packet_request = new Request();
                    $booking_packet_request->replace($prepared_packet['packet']);

                    /**
                     * Verify Fields before send
                     */
                    $validator = $this->verifyPacketFields($booking_packet_request);
                    if ($validator->fails()) {
                        OrderLogs::create([
                            'account_id' => $account_id,
                            'order_id' => $order['order_id'],
                            'name' => $order['name'],
                            'order_number' => $order['order_number'],
                            'message' => 'Order <b>' . $order['name'] . '</b> has some issues. [' . implode(', ', $validator->messages()->all()) . ']',
                        ]);
                    }

                    $result = BookedPackets::createRecord($booking_packet_request, $account_id);

                    /**
                     * Add Booking information into Order
                     */
                    if($result['status']) {
                        $booked_packet = BookedPackets::where([
                            'account_id' => $account_id,
                            'id' => $result['record_id'],
                        ])->first();

                        ShopifyOrders::where([
                            'order_id' => $order['order_id'],
                            'account_id' => $account_id,
                        ])->update(array(
                            'booking_id' => $booked_packet->id,
                            'cn_number' => $booked_packet->cn_number
                        ));

                        /**
                         * Dispatch to check if Auto Fulfillment is 'true' or 'false'
                         * if 'true' then order will be fulfilled automatically
                         * if 'false' then order will not be fulfilled
                         */
                        event(new SingleOrderFulfillmentFire($order, $booked_packet->cn_number));
                    } else {
                        OrderLogs::create([
                            'account_id' => $account_id,
                            'order_id' => $order['order_id'],
                            'name' => $order['name'],
                            'order_number' => $order['order_number'],
                            'message' => 'Order <b>' . $order['name'] . '</b> has some issues. [' . $result['error_msg'] . ']',
                        ]);
                    }
                } else {
                    OrderLogs::create([
                        'account_id' => $account_id,
                        'order_id' => $order['order_id'],
                        'name' => $order['name'],
                        'order_number' => $order['order_number'],
                        'message' => 'Order <b>' . $order['name'] . '</b> has consignee city / shipper information issue. Please select proper city or check shipper information from settings for this packet to book.',
                    ]);
                }
            }
        } catch (\Exception $exception) {
            echo $exception->getLine() . ' -  ' . $exception->getMessage() . ' - ' . $exception->getFile();
        }
    }

    /**
     * Validate form fields
     *
     * @param  \Illuminate\Http\Request $request
     * @return Validator $validator;
     */
    protected function verifyPacketFields(Request $request)
    {
        return $validator = Validator::make($request->all(), [
            'shipment_type_id' => 'required',
            'booking_date' => 'required',
            'packet_pieces' => 'required|numeric',
            'net_weight' => 'required|numeric',
            'collect_amount' => 'required|numeric',
            'order_number' => 'required|numeric',
            'order_id' => 'nullable',
            'vol_weight_w' => 'nullable|numeric',
            'vol_weight_h' => 'nullable|numeric',
            'vol_weight_l' => 'nullable|numeric',
            'shipper_id' => 'required',
            'origin_city' => 'required',
            'shipper_name' => 'required',
            'shipper_email' => 'nullable',
            'shipper_phone' => 'required',
            'shipper_address' => 'required',
            'consignee_id' => 'required',
            'destination_city' => 'required',
            'consignee_name' => 'required',
            'consignee_email' => 'nullable|email',
            'consignee_phone' => 'required',
            'consignee_address' => 'required',
            'comments' => 'required',
        ]);
    }
}
