<?php

namespace App\Models;

use Carbon\Carbon;
use Developifynet\LeopardsCOD\LeopardsCODClient;
use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use Auth;
use Config;
use Illuminate\Support\Facades\Schema;


class BookedPackets extends BaseModal
{
    use SoftDeletes;

    protected $fillable = [
        'booked_packet_id', 'shipment_type_id', 'booking_date', 'packet_pieces', 'net_weight', 'collect_amount', 'order_id', 'order_number',
        'vol_weight_w', 'vol_weight_h', 'vol_weight_l', 'shipper_id', 'shipper_name', 'shipper_email', 'shipper_phone', 'shipper_address',
        'consignee_id', 'consignee_name', 'consignee_email', 'consignee_phone', 'consignee_phone_2', 'consignee_phone_3', 'consignee_address',
        'comments', 'track_number', 'slip_link', 'status', 'history', 'origin_city', 'destination_city', 'cn_number', 'booking_type', 'marked_paid', 'status_check_count',
        'created_at', 'updated_at', 'created_by', 'updated_by', 'account_id'
    ];

    protected static $skip_columns = ['variants', 'options', 'images'];

    protected static $_fillable = [
        'booked_packet_id', 'shipment_type_id', 'booking_date', 'packet_pieces', 'net_weight', 'collect_amount', 'order_id', 'order_number',
        'vol_weight_w', 'vol_weight_h', 'vol_weight_l', 'shipper_id', 'shipper_name', 'shipper_email', 'shipper_phone', 'shipper_address',
        'consignee_id', 'consignee_name', 'consignee_email', 'consignee_phone', 'consignee_phone_2', 'consignee_phone_3', 'consignee_address',
        'comments', 'track_number', 'slip_link', 'status', 'history', 'origin_city', 'destination_city', 'cn_number', 'booking_type', 'marked_paid', 'status_check_count',
    ];

    protected $table = 'booked_packets';

    protected static $_table = 'booked_packets';

    /**
     * Get Total Records
     *
     * @param \Illuminate\Http\Request $request
     * @param (int) $account_id Current Organization's ID
     *
     * @return (mixed)
     */
    static public function getTotalRecords(Request $request, $account_id = false, $booking_type = false)
    {
        $where = array();

        if($account_id) {
            $where[] = array(
                'account_id',
                '=',
                $account_id
            );
        }

        if($booking_type) {
            $where[] = array(
                'booking_type',
                '=',
                $booking_type
            );
        }

        if($request->get('status')) {
            $where[] = array(
                'status',
                '=',
                $request->get('status')
            );
        }

        if($request->get('order_id')) {
            $where[] = array(
                'order_id',
                'like',
                '%' . $request->get('order_id') . '%'
            );
        }

        if($request->get('shipment_type_id')) {
            $where[] = array(
                'shipment_type_id',
                '=',
                $request->get('shipment_type_id')
            );
        }

        if($request->get('cn_number')) {
            $where[] = array(
                'cn_number',
                'like',
                '%' . $request->get('cn_number') . '%'
            );
        }

        if($request->get('origin_city')) {
            $where[] = array(
                'origin_city',
                '=',
                $request->get('origin_city')
            );
        }

        if($request->get('destination_city')) {
            $where[] = array(
                'destination_city',
                '=',
                $request->get('destination_city')
            );
        }

        if($request->get('shipper_name')) {
            $where[] = array(
                'shipper_name',
                'like',
                '%' . $request->get('shipper_name') . '%'
            );
        }

        if($request->get('consignee_name')) {
            $where[] = array(
                'consignee_name',
                'like',
                '%' . $request->get('consignee_name') . '%'
            );
        }

        if($request->get('consignee_phone')) {
            $where[] = array(
                'consignee_phone',
                'like',
                '%' . $request->get('consignee_phone') . '%'
            );
        }

        if($request->get('consignee_email')) {
            $where[] = array(
                'consignee_email',
                'like',
                '%' . $request->get('consignee_email') . '%'
            );
        }


        if ($request->get('booking_date_from') && $request->get('booking_date_from') != '') {
            $where[] = array(
                'booking_date',
                '>=',
                $request->get('booking_date_from') . ' 00:00:00'
            );
        }

        if ($request->get('booking_date_to') && $request->get('booking_date_to') != '') {
            $where[] = array(
                'booking_date',
                '<=',
                $request->get('booking_date_to') . ' 23:59:59'
            );
        }


        if($request->get('collect_amount')) {
            $where[] = array(
                'collect_amount',
                'like',
                '%' . $request->get('collect_amount') . '%'
            );
        }


        if ($request->get('invoice_date_from') && $request->get('invoice_date_from') != '') {
            $where[] = array(
                'invoice_date',
                '>=',
                $request->get('invoice_date_from') . ' 00:00:00'
            );
        }

        if ($request->get('invoice_date_to') && $request->get('invoice_date_to') != '') {
            $where[] = array(
                'invoice_date',
                '<=',
                $request->get('invoice_date_to') . ' 23:59:59'
            );
        }


        if($request->get('invoice_number')) {
            $where[] = array(
                'invoice_number',
                'like',
                '%' . $request->get('invoice_number') . '%'
            );
        }

        if(count($where)) {
            return self::where($where)->count();
        } else {
            return self::count();
        }
    }

    /**
     * Get Records
     *
     * @param \Illuminate\Http\Request $request
     * @param (int) $iDisplayStart Start Index
     * @param (int) $iDisplayLength Total Records Length
     * @param (int) $account_id Current Organization's ID
     *
     * @return (mixed)
     */
    static public function getRecords(Request $request, $iDisplayStart, $iDisplayLength, $account_id = false, $booking_type = false)
    {
        $where = array();

        $orderBy = 'booking_date';
        $order = 'desc';

        if ($request->get('order')[0]['dir']) {
            $orderColumn = $request->get('order')[0]['column'];
            $orderBy = $request->get('columns')[$orderColumn]['data'];
            $order = $request->get('order')[0]['dir'];
        }

        if($account_id) {
            $where[] = array(
                'account_id',
                '=',
                $account_id
            );
        }

        if($booking_type) {
            $where[] = array(
                'booking_type',
                '=',
                $booking_type
            );
        }

        if($request->get('status')) {
            $where[] = array(
                'status',
                '=',
                $request->get('status')
            );
        }

        if($request->get('order_id')) {
            $where[] = array(
                'order_id',
                'like',
                '%' . $request->get('order_id') . '%'
            );
        }

        if($request->get('shipment_type_id')) {
            $where[] = array(
                'shipment_type_id',
                '=',
                $request->get('shipment_type_id')
            );
        }

        if($request->get('cn_number')) {
            $where[] = array(
                'cn_number',
                'like',
                '%' . $request->get('cn_number') . '%'
            );
        }

        if($request->get('origin_city')) {
            $where[] = array(
                'origin_city',
                '=',
                $request->get('origin_city')
            );
        }

        if($request->get('destination_city')) {
            $where[] = array(
                'destination_city',
                '=',
                $request->get('destination_city')
            );
        }

        if($request->get('shipper_name')) {
            $where[] = array(
                'shipper_name',
                'like',
                '%' . $request->get('shipper_name') . '%'
            );
        }

        if($request->get('consignee_name')) {
            $where[] = array(
                'consignee_name',
                'like',
                '%' . $request->get('consignee_name') . '%'
            );
        }

        if($request->get('consignee_phone')) {
            $where[] = array(
                'consignee_phone',
                'like',
                '%' . $request->get('consignee_phone') . '%'
            );
        }

        if($request->get('consignee_email')) {
            $where[] = array(
                'consignee_email',
                'like',
                '%' . $request->get('consignee_email') . '%'
            );
        }


        if ($request->get('booking_date_from') && $request->get('booking_date_from') != '') {
            $where[] = array(
                'booking_date',
                '>=',
                $request->get('booking_date_from') . ' 00:00:00'
            );
        }

        if ($request->get('booking_date_to') && $request->get('booking_date_to') != '') {
            $where[] = array(
                'booking_date',
                '<=',
                $request->get('booking_date_to') . ' 23:59:59'
            );
        }


        if($request->get('collect_amount')) {
            $where[] = array(
                'collect_amount',
                'like',
                '%' . $request->get('collect_amount') . '%'
            );
        }

        if ($request->get('invoice_date_from') && $request->get('invoice_date_from') != '') {
            $where[] = array(
                'invoice_date',
                '>=',
                $request->get('invoice_date_from') . ' 00:00:00'
            );
        }

        if ($request->get('invoice_date_to') && $request->get('invoice_date_to') != '') {
            $where[] = array(
                'invoice_date',
                '<=',
                $request->get('invoice_date_to') . ' 23:59:59'
            );
        }


        if($request->get('invoice_number')) {
            $where[] = array(
                'invoice_number',
                'like',
                '%' . $request->get('invoice_number') . '%'
            );
        }

        if(count($where)) {
            return self::where($where)->limit($iDisplayLength)->offset($iDisplayStart)->orderBy($orderBy,$order)->get();
        } else {
            return self::limit($iDisplayLength)->offset($iDisplayStart)->orderBy($orderBy,$order)->get();
        }
    }

    /**
     * Create Record
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return (mixed)
     */
    static public function createRecord($request, $account_id)
    {

        $data = $request->all();

        /**
         * If consignment emails is not provided, put a dummy email
         */
        if(
            array_key_exists('consignee_email', $data) && !$data['consignee_email']
        ) {
            $data['consignee_email'] = Config::get('constants.lcs_dummy_email');
            $request = new Request();
            $request->replace($data);
        }

        /**
         * Book Packet in Leopards System
         */
        $response = BookedPackets::bookPacket($request, $account_id);

        if($response['status']) {
            // Set Tracking Information
            $data['track_number'] = $response['track_number'];
            $data['cn_number'] = $response['track_number'];
            $data['slip_link'] = $response['slip_link'];
        } else {
            return [
                'status' => false,
                'record_id' => 0,
                'error_msg' => $response['error_msg']
            ];
        }

        // Set Account ID
        $data['account_id'] = $account_id;
        $data = self::prepareRecord($data);

        /**
         * If Consigneee is 'other' then create this consignee into system
         */
        if($data['consignee_id'] == 'other') {

            $destination_city = LeopardsCities::where([
                'city_id' => $data['destination_city']
            ])->first();

            if(!$destination_city) {
                return [
                    'status' => false,
                    'record_id' => 0,
                    'error_msg' => 'Unable to find destination city'
                ];
            }

            $consignee = new Request();
            $consignee->replace([
                'name' => $data['consignee_name'],
                'email' => $data['consignee_email'],
                'phone' => $data['consignee_phone'],
                'phone_2' => $data['consignee_phone_2'],
                'phone_3' => $data['consignee_phone_3'],
                'address' => $data['consignee_address'],
                'city_id' => $destination_city->id,
            ]);

            if($consignee_created = Consignees::createRecord($consignee, $data['account_id'])) {
                $data['consignee_id'] = $consignee_created->id;
            } else {
                return [
                    'status' => false,
                    'record_id' => 0,
                    'error_msg' => 'Unable to create consignee'
                ];
            }
        }

        /**
         * Handle Packet as Production or Test
         * '1' as Test Mode
         * '2' as Production Mode
         */
        $leopards_setting = LeopardsSettings::where([
            'account_id' => Auth::User()->account_id,
            'slug' => 'mode'
        ])
            ->select('slug', 'data')
            ->first();
        $data['booking_type'] = ($leopards_setting->data) ? 1 : 2;

        $record = self::create($data);

        /**
         * Packet is booked now update Order
         */
        if(isset($data['order_id']) && $data['order_id']) {
            ShopifyOrders::where([
                'name' => $data['order_id'],
                'account_id' => $account_id,
            ])->update(array(
                'booking_id' => $record->id,
                'cn_number' => $data['cn_number']
            ));
        }

        AuditTrails::addEventLogger(self::$_table, 'create', $data, self::$_fillable, $record);

        return [
            'status' => true,
            'test_mode' => ($leopards_setting->data) ? 1 : 0,
            'record_id' => $record->id,
            'record' => $record,
            'error_msg' => null
        ];
    }

    /**
     * Cancel Booked Packet in Leopards COD
     *
     * @param $id
     *
     * @return (mixed)
     */
    static public function cancelPacket($id)
    {

        $booked_packet = BookedPackets::getData($id);

        if (!$booked_packet) {
            flash('Resource not found.')->error()->important();
            return redirect()->route('admin.booked_packets.index');
        }

        /**
         * Check if provided packet is from production mode or not
         * '1' as Test Mode
         * '2' as Production Mode
         */
        if($booked_packet->booking_type == '1') {
            flash('Test Packet can not be cancel.')->error()->important();
            return redirect()->route('admin.booked_packets.index');
        }

        try {

            $leopards_settings = LeopardsSettings::where([
                'account_id' => Auth::User()->account_id
            ])
                ->select('slug', 'data')
                ->orderBy('id', 'asc')
                ->get()->keyBy('slug');

            /**
             * Merge Booked Packet with LCS Credentials
             */
            $cn_data = array_merge(array(
                'api_key' => $leopards_settings['api-key']->data,              // API Key provided by LCS
                'api_password' => $leopards_settings['api-password']->data,    // API Password provided by LCS
            ), [
                'cn_numbers' => $booked_packet->cn_number
            ]);

            $client = new Client();
            $response = $client->post(env('LCS_URL') . 'webservice/cancelBookedPackets/format/json/ ', array(
                'form_params' => $cn_data
            ));

            if($response->getStatusCode() == 200) {
                if($response->getBody()) {

                    $result = json_decode($response->getBody(), true);

                    if(
                                $result['status']
                            ||  (
                                    !$result['status']
                                    && (
                                                count($result['error'])
                                            &&  isset($result['error'][$booked_packet->cn_number])
                                    )
                                )
                    ) {

                        $booked_packet->update(['status' => Config::get('constants.status_cancel')]);
                        flash('Packet has been cancelled successfully.')->success()->important();
                        AuditTrails::inactiveEventLogger(self::$_table, 'inactive', self::$_fillable, $id);

                    } else {
                        flash($result['error'])->error()->important();
                        return redirect()->route('admin.booked_packets.index');
                    }

                } else {
                    flash('Error in cancel.')->error()->important();
                    return redirect()->route('admin.booked_packets.index');
                }
            } else {
                flash('Error in cancel.')->error()->important();
                return redirect()->route('admin.booked_packets.index');
            }
        } catch (\Exception $exception) {
            flash('Error in cancel.')->error()->important();
            return redirect()->route('admin.booked_packets.index');
        }
    }

    static public function cancelBookedPacket($cn_number, $account_id) {
        try {

            $leopards_settings = LeopardsSettings::where([
                'account_id' => $account_id
            ])
                ->select('slug', 'data')
                ->orderBy('id', 'asc')
                ->get()->keyBy('slug');

            /**
             * Merge Booked Packet with LCS Credentials
             */
            $cn_data = array_merge(array(
                'api_key' => $leopards_settings['api-key']->data,              // API Key provided by LCS
                'api_password' => $leopards_settings['api-password']->data,    // API Password provided by LCS
            ), [
                'cn_numbers' => $cn_number
            ]);

            $client = new Client();
            $response = $client->post(env('LCS_URL') . 'webservice/cancelBookedPackets/format/json/ ', array(
                'form_params' => $cn_data
            ));

            if($response->getStatusCode() == 200) {
                if($response->getBody()) {

                    $result = json_decode($response->getBody(), true);

                    if(
                        $result['status']
                    ) {
                        return array(
                            'status' => true,
                            'message' => 'Packet has been cancelled successfully.',
                        );
                    } else {
                        if(
                            !$result['status']
                            && (
                                count($result['error'])
                                &&  isset($result['error'][$cn_number])
                            )
                        ) {
                            return array(
                                'status' => false,
                                'message' => $result['error'][$cn_number],
                            );
                        }
                    }
                }
            }
        } catch (\Exception $exception) {}

        return array(
            'status' => false,
            'message' => 'Something went wrong.',
        );
    }


    /**
     * Delete Record
     *
     * @param $id
     *
     * @return (mixed)
     */
    static public function deleteRecord($id)
    {

        $booked_packet = BookedPackets::getData($id);

        if (!$booked_packet) {
            flash('Resource not found.')->error()->important();
            return redirect()->route('admin.booked_packets.index');
        }

        /**
         * Check if provided packet is from production mode or not
         * '1' as Test Mode
         * '2' as Production Mode
         */
        if($booked_packet->booking_type == '2') {
            flash('Packet can not be delete, only option available is cancel this packet.')->error()->important();
            return redirect()->route('admin.booked_packets.index');
        }

        // Check if child records exists or not, If exist then disallow to delete it.
        if (BookedPackets::isChildExists($id, Auth::User()->account_id)) {
            flash('Child records exist, unable to delete resource')->error()->important();
            return redirect()->route('admin.booked_packets.index');
        }

        $record = $booked_packet->delete();

        AuditTrails::deleteEventLogger(self::$_table, 'delete', self::$_fillable, $id);

        flash('Record has been deleted successfully.')->success()->important();

        return $record;
    }


    /**
     * Check if child records exist
     *
     * @param (int) $id
     * @param
     *
     * @return (boolean)
     */
    static public function isChildExists($id, $account_id)
    {
        return false;
    }

    /**
     * Prepare provided record
     *
     * @param $record
     * @return array
     */
    static public function prepareRecord($record) {

        $prepared_record = [];

        /*
         * Get table columns and prepare record
         */
        $columns = Schema::getColumnListing(self::$_table); // users table
        foreach($record as $column => $value) {
            // Skip those records which are in skipped columns array
            if(count(self::$skip_columns)) {
                if(in_array($column, self::$skip_columns)) {
                    continue;
                }
            }

            /*
             * Remove records which are not in columns
             */
            if(!in_array($column, $columns)) {
                continue;
            }

            /*
             * Set DateTimes format
             */
            $timestamps = ['created_at', 'updated_at'];
            if(in_array($column, $timestamps)) {
                $value = Carbon::parse($value)->toDateTimeString();
            }

            if(is_array($value)) {
                $prepared_record[$column] = json_encode($value);
            } else {
                $prepared_record[$column] = $value;
            }
        }

        return $prepared_record;
    }


    public static function prepareBookingOrder($order_id = false, $account_id) {
        $fill_fields = array(
            'packet_pieces' => 'quantity',
            'net_weight' => 'total_weight',
            'collect_amount' => 'total_price',
            'order_number' => 'order_number',
            'order_id' => 'name',
            'comments' => 'note',
            'title' => 'title',
            'consignee_name' => 'name',
            'consignee_email' => 'email',
            'consignee_phone' => 'phone',
            'consignee_address' => 'address1',
            'destination_city' => 'city'
        );

        $order_fields = array(
            'note' => 'comments',
        );

        $booked_packet = array();

        foreach($fill_fields as $key => $value) {
            if(in_array($key, ['packet_pieces', 'net_weight', 'collect_amount'])) {
                $booked_packet[$key] = 0;
            } else {
                $booked_packet[$key] = null;
            }
        }

        if($order_id) {
            $order = ShopifyOrders::where([
                'account_id' => $account_id,
                'order_id' => $order_id,
            ])->first();

            if($order) {
                $order = $order->toArray();

                $customer = ShopifyCustomers::where([
                    'customer_id' => $order['customer_id']
                ])->first();

                if($customer) {
                    $customer = $customer->toArray();
                } else {
                    $customer = [];
                }

                $shipping_address = ShippingAddresses::where([
                    'order_id' => $order['order_id']
                ])->first();

                if($shipping_address) {
                    $shipping_address = $shipping_address->toArray();
                } else {
                    $shipping_address = [];
                }

                $order_items = ShopifyOrderItems::where([
                   'order_id' => $order['order_id']
                ])->get();

                if($order_items) {
                    $order_items = $order_items->toArray();
                } else {
                    $order_items = [];
                }

                foreach($fill_fields as $key => $value) {
                    if($key == 'consignee_name' && $value == 'name') {
                        continue;
                    }
                    if(array_key_exists($value, $order)) {
                        if($value == 'total_weight') {
                            $booked_packet[$key] = ($order[$value]) ? $order[$value] : '';
                        } elseif($value == 'total_price') {
                            /**
                             * If 'financial_status' is 'paid' then COD amount will be 0.00
                             */
                            if(array_key_exists('financial_status', $order) && $order['financial_status'] == 'paid') {
                                $booked_packet[$key] = 0.001;
                            } else {
                                $booked_packet[$key] = $order[$value];
                            }
                        } else {
                            $booked_packet[$key] = $order[$value];
                        }
                    }
                }

                if(count($shipping_address)) {
                    foreach($fill_fields as $key => $value) {
                        if(array_key_exists($value, $shipping_address)) {
                            if($key != 'consignee_name' && $value == 'name') {
                                continue;
                            }
                            if($key == 'consignee_address') {
                                $booked_packet[$key] = trim($shipping_address['address1']) . ' ' . trim($shipping_address['address2']);
                            } else if($key == 'destination_city') {
                                /**
                                 * Grab City from Leopards System
                                 */
                                $city = LeopardsCities::where([
                                    'account_id' => $account_id
                                ])
                                    ->where('name',
                                        '=',
                                        trim($shipping_address[$value])
                                    )
                                    ->select('city_id', 'name')
                                    ->first();

                                if($city) {
                                    $booked_packet[$key] = $city->city_id;
                                }
                            } else {
                                if($value == 'name') {
                                    $booked_packet[$key] = ($shipping_address[$value]) ? trim(ucwords($shipping_address[$value])) : trim(ucwords($shipping_address['first_name'])) . ' ' . trim(ucwords($shipping_address['last_name']));
                                } else {
                                    $booked_packet[$key] = $shipping_address[$value];
                                }
                            }
                        }
                    }
                } else {
                    if(count($customer)) {
                        foreach($fill_fields as $key => $value) {
                            if(array_key_exists($value, $customer)) {
                                if($key != 'consignee_name' && $value == 'name') {
                                    continue;
                                }
                                if($key == 'consignee_address') {
                                    $booked_packet[$key] = trim($customer['address1']) . ' ' . trim($customer['address2']);
                                } else if($key == 'destination_city') {
                                    /**
                                     * Grab City from Leopards System
                                     */
                                    $city = LeopardsCities::where([
                                        'account_id' => $account_id
                                    ])
                                        ->where('name',
                                            '=',
                                            trim($customer[$value])
                                        )
                                        ->select('city_id', 'name')
                                        ->first();

                                    if($city) {
                                        $booked_packet[$key] = $city->city_id;
                                    }
                                } else {
                                    if($value == 'name') {
                                        $booked_packet[$key] = ($customer[$value]) ? trim(ucwords($customer[$value])) : trim(ucwords($customer['first_name'])) . ' ' . trim(ucwords($customer['last_name']));
                                    } else {
                                        $booked_packet[$key] = $customer[$value];
                                    }
                                }
                            }
                        }
                    }
                }

                if(count($order_items)) {
                    $items = [];
                    foreach($fill_fields as $key => $value) {
                        if($value == 'name') {
                            continue;
                        }
                        foreach($order_items as $order_item) {
                            if(array_key_exists($value, $order_item)) {
                                if($key == 'title') {
                                    /**
                                     * Add Line item name and qty into comments section
                                     */
                                    $single_item = substr($order_item['title'], 0, 60);
                                    // Set SKU
                                    if($order_item['sku']) {
                                        $single_item .= ' ' . $order_item['sku'];
                                    }
                                    // Set Variant Title
                                    if($order_item['variant_title']) {
                                        $single_item .= ' (' . $order_item['variant_title'] . ')';
                                    }
                                    $single_item .= ' ' . $order_item['quantity'] . ' pc';
                                    $items[] = $single_item;
//                                    $items[] = $order_item['title'] . ' ' . $order_item['quantity'] . ' pc';
                                } else {
                                    $booked_packet[$key] += $order_item[$value];
                                }
                            }
                        }
                    }

                    /**
                     * if line items found then add them into comments
                     */
                    if(array_key_exists('comments', $booked_packet) && count($items)) {
                        $booked_packet['comments'] = trim($booked_packet['comments'] . ' ' . implode(', ', $items));
                    }
                }
            }
        }

        /**
         * Adjust Shipper Information as per LCS Settings
         */
        $leopards_settings = LeopardsSettings::where([
            'account_id' => Auth::User()->account_id
        ])
            ->select('slug', 'data')
            ->orderBy('id', 'asc')
            ->get()->keyBy('slug');

        /**
         * If Type is 'self' then all fields will have 'self' word
         * If Type is 'other' then all shipper information will be filled
         */
        if($leopards_settings['shipper-type']->data == 'self') {
            // Shipper Information
            $booked_packet['origin_city'] = '';
            $booked_packet['shipper_id'] = 'self';
            $booked_packet['shipper_name'] = '';
            $booked_packet['shipper_email'] = '';
            $booked_packet['shipper_phone'] = '';
            $booked_packet['shipper_address'] = '';
        } else {
            // Shipper Information
            $booked_packet['origin_city'] = $leopards_settings['shipper-city']->data;
            $booked_packet['shipper_id'] = 'other';
            $booked_packet['shipper_name'] = $leopards_settings['shipper-name']->data;
            $booked_packet['shipper_email'] = $leopards_settings['shipper-email']->data;
            $booked_packet['shipper_phone'] = $leopards_settings['shipper-phone']->data;
            $booked_packet['shipper_address'] = $leopards_settings['shipper-address']->data;
        }

        return $booked_packet;
    }


    /**
     * Prepare Booking
     *
     * @param bool $order_id
     * @param int $shipment_type_id
     * @param $account_id
     * @return array
     */
    public static function prepareBooking($order_id = false, $shipment_type_id = 10, $account_id) {

        /**
         * Set Status
         */
        $status = true;

        $fill_fields = array(
            'packet_pieces' => 'quantity',
            'net_weight' => 'total_weight',
            'collect_amount' => 'total_price',
            'order_number' => 'order_number',
            'order_id' => 'name',
            'comments' => 'note',
            'title' => 'title',
            'consignee_name' => 'name',
            'consignee_email' => 'email',
            'consignee_phone' => 'phone',
            'consignee_address' => 'address1',
            'destination_city' => 'city'
        );

        $booked_packet = array();

        foreach($fill_fields as $key => $value) {
            if(in_array($key, ['packet_pieces', 'net_weight', 'collect_amount'])) {
                $booked_packet[$key] = 0;
            } else {
                $booked_packet[$key] = null;
            }
        }

        if($order_id) {
            $order = ShopifyOrders::where([
                'account_id' => $account_id,
                'order_id' => $order_id,
            ])->first();

            if($order) {
                $order = $order->toArray();

                $customer = ShopifyCustomers::where([
                    'customer_id' => $order['customer_id']
                ])->first();

                if($customer) {
                    $customer = $customer->toArray();
                } else {
                    $customer = [];
                }

                $shipping_address = ShippingAddresses::where([
                    'order_id' => $order['order_id']
                ])->first();

                if($shipping_address) {
                    $shipping_address = $shipping_address->toArray();
                } else {
                    $shipping_address = [];
                }

                $order_items = ShopifyOrderItems::where([
                    'order_id' => $order['order_id']
                ])->get();

                if($order_items) {
                    $order_items = $order_items->toArray();
                } else {
                    $order_items = [];
                }

                foreach($fill_fields as $key => $value) {
                    if($key == 'consignee_name' && $value == 'name') {
                        continue;
                    }
                    if(array_key_exists($value, $order)) {
                        if($value == 'total_weight') {
                            $booked_packet[$key] = ($order[$value]) ? $order[$value] : '500';
                        } elseif($value == 'total_price') {
                            /**
                             * If 'financial_status' is 'paid' then COD amount will be 0.00
                             */
                            if(array_key_exists('financial_status', $order) && $order['financial_status'] == 'paid') {
                                $booked_packet[$key] = 0.001;
                            } else {
                                $booked_packet[$key] = $order[$value];
                            }
                        } else {
                            $booked_packet[$key] = $order[$value];
                        }
                    }
                }

                if(count($shipping_address)) {
                    foreach($fill_fields as $key => $value) {
                        if($key == 'consignee_email') {
                            if(count($customer) && isset($customer['email'])) {
                                if($customer['email']) {
                                    $booked_packet[$key] = $customer['email'];
                                } else {
                                    $booked_packet[$key] = 'no@email.com';
                                }
                            } else {
                                $booked_packet[$key] = 'no@email.com';
                            }
                        }
                        if($key != 'consignee_name' && $value == 'name') {
                            continue;
                        }
                        if(array_key_exists($value, $shipping_address)) {
                            if($key == 'consignee_address') {
                                $booked_packet[$key] = trim($shipping_address['address1']) . ' ' . trim($shipping_address['address2']);
                            } else if($key == 'destination_city') {
                                /**
                                 * Grab City from Leopards System
                                 */
                                $city = LeopardsCities::where([
                                    'account_id' => $account_id
                                ])
                                    ->where('name',
                                        '=',
                                        trim($shipping_address[$value])
                                    )
                                    ->select('city_id', 'name')
                                    ->first();

                                if($city) {
                                    $booked_packet[$key] = $city->city_id;
                                } else {
                                    $status = false;
                                }
                            } else {
                                if($value == 'name') {
                                    $booked_packet[$key] = ($shipping_address[$value]) ? trim(ucwords($shipping_address[$value])) : trim(ucwords($shipping_address['first_name'])) . ' ' . trim(ucwords($shipping_address['last_name']));
                                } else {
                                    $booked_packet[$key] = $shipping_address[$value];
                                }
                            }
                        }
                    }
                } else {
                    if(count($customer)) {
                        foreach($fill_fields as $key => $value) {
                            if($key != 'consignee_name' && $value == 'name') {
                                continue;
                            }
                            if(array_key_exists($value, $customer)) {
                                if($key == 'consignee_address') {
                                    $booked_packet[$key] = trim($customer['address1']) . ' ' . trim($customer['address2']);
                                } else if($key == 'destination_city') {
                                    /**
                                     * Grab City from Leopards System
                                     */
                                    $city = LeopardsCities::where([
                                        'account_id' => $account_id
                                    ])
                                        ->where('name',
                                            '=',
                                            trim($customer[$value])
                                        )
                                        ->select('city_id', 'name')
                                        ->first();

                                    if($city) {
                                        $booked_packet[$key] = $city->city_id;
                                    } else {
                                        $status = false;
                                    }
                                } else {
                                    if($value == 'name') {
                                        $booked_packet[$key] = ($customer[$value]) ? trim(ucwords($customer[$value])) : trim(ucwords($customer['first_name'])) . ' ' . trim(ucwords($customer['last_name']));
                                    } else {
                                        $booked_packet[$key] = $customer[$value];
                                    }
                                }
                            }
                        }
                    }
                }

                if(count($order_items)) {
                    $items = [];
                    foreach($fill_fields as $key => $value) {
                        if($value == 'name') {
                            continue;
                        }
                        foreach($order_items as $order_item) {
                            if(array_key_exists($value, $order_item)) {
                                if($key == 'title') {
                                    /**
                                     * Add Line item name and qty into comments section
                                     */
                                    $single_item = substr($order_item['title'], 0, 60);
                                    // Set SKU
                                    if($order_item['sku']) {
                                        $single_item .= ' ' . $order_item['sku'];
                                    }
                                    // Set Variant Title
                                    if($order_item['variant_title']) {
                                        $single_item .= ' (' . $order_item['variant_title'] . ')';
                                    }
                                    $single_item .= ' ' . $order_item['quantity'] . ' pc';
                                    $items[] = $single_item;
//                                    $items[] = $order_item['title'] . ' ' . $order_item['quantity'] . ' pc';
                                } else {
                                    $booked_packet[$key] += $order_item[$value];
                                }
                            }
                        }
                    }

                    /**
                     * if line items found then add them into comments
                     */
                    if(array_key_exists('comments', $booked_packet) && count($items)) {
                        $booked_packet['comments'] = trim($booked_packet['comments'] . ' ' . implode(', ', $items));
                    }
                }
            }
        }

        $booked_packet['booking_date'] = Carbon::now()->format('Y-m-d');
        $booked_packet['shipment_type_id'] = $shipment_type_id;
        if(!isset($booked_packet['comments']) || !$booked_packet['comments']) {
            $booked_packet['comments'] = 'n/a';
        }

        /**
         * Adjust Shipper Information as per LCS Settings
         */
        $leopards_settings = LeopardsSettings::where([
            'account_id' => Auth::User()->account_id
        ])
            ->select('slug', 'data')
            ->orderBy('id', 'asc')
            ->get()->keyBy('slug');

        // Consignee Information
        $booked_packet['consignee_id'] = 'self';

        /**
         * If Type is 'self' then all fields will have 'self' word
         * If Type is 'other' then all shipper information will be filled
         */
        if($leopards_settings['shipper-type']->data == 'self') {
            // Shipper Information
            $booked_packet['origin_city'] = 'self';
            $booked_packet['shipper_id'] = 'self';
            $booked_packet['shipper_name'] = 'self';
            $booked_packet['shipper_email'] = 'self';
            $booked_packet['shipper_phone'] = 'self';
            $booked_packet['shipper_address'] = 'self';
        } else {
            // Shipper Information
            $booked_packet['origin_city'] = $leopards_settings['shipper-city']->data;
            $booked_packet['shipper_id'] = 'other';
            $booked_packet['shipper_name'] = $leopards_settings['shipper-name']->data;
            $booked_packet['shipper_email'] = $leopards_settings['shipper-email']->data;
            $booked_packet['shipper_phone'] = $leopards_settings['shipper-phone']->data;
            $booked_packet['shipper_address'] = $leopards_settings['shipper-address']->data;
        }

        return array(
            'status' => $status,
            'packet' => $booked_packet,
        );
    }


    /**
     * Prepare provided record
     *
     * @param $record
     * @return array
     */
    static public function prepareBookingRecord($record) {

        $booking_mappings = array(
            'shipment_type_id' => 'shipment_type',
            'packet_pieces' => 'booked_packet_no_piece',
            'net_weight' => 'booked_packet_weight',
            'collect_amount' => 'booked_packet_collect_amount',
            'order_id' => 'booked_packet_order_id',
            'vol_weight_w' => 'booked_packet_vol_weight_w',
            'vol_weight_h' => 'booked_packet_vol_weight_h',
            'vol_weight_l' => 'booked_packet_vol_weight_l',
            'shipper_name' => 'shipment_name_eng',
            'shipper_email' => 'shipment_email',
            'shipper_phone' => 'shipment_phone',
            'shipper_address' => 'shipment_address',
            'consignee_name' => 'consignment_name_eng',
            'consignee_email' => 'consignment_email',
            'consignee_phone' => 'consignment_phone',
            'consignee_phone_2' => 'consignment_phone_two',
            'consignee_phone_3' => 'consignment_phone_three',
            'consignee_address' => 'consignment_address',
            'comments' => 'special_instructions',
            'origin_city' => 'origin_city',
            'destination_city' => 'destination_city',
        );

        $shipper_array = ['shipment_name_eng', 'shipment_email', 'shipment_phone', 'shipment_address', 'origin_city'];

        $prepared_record = [];

        $shipment_type = Config::get('constants.shipment_type');

        foreach($booking_mappings as $system_id => $lcs_id) {
            if(array_key_exists($system_id, $record)) {
                if($system_id == 'shipment_type_id') {
                    $prepared_record[$lcs_id] = strtolower($shipment_type[$record[$system_id]]);
                } else if($system_id == 'shipment_type_id' && $record[$system_id] < 0) {
                    $prepared_record[$lcs_id] = 0;
                } else {
                    $prepared_record[$lcs_id] = $record[$system_id];
                }
            }
        }

        /**
         * Change to self all shipper fields if 'shipper_id' is set to 'self'
         */
        if(isset($record['shipper_id']) && $record['shipper_id'] == 'self') {
            foreach($shipper_array as $single_column) {
                $prepared_record[$single_column] = 'self';
            }
        }

        return $prepared_record;
    }


    /**
     * Book a Packet with LCS COD
     *
     * @param \Illuminate\Http\Request $request
     * @param int
     *
     * @return (mixed)
     */
    static public function bookPacket($request, $account_id)
    {

        $data = $request->all();

        // Set Account ID
        $data['account_id'] = $account_id;
        $data = self::prepareRecord($data);
        $booked_packet = self::prepareBookingRecord($data);

        try {
            $leopards_settings = LeopardsSettings::where([
                'account_id' => Auth::User()->account_id
            ])
                ->select('slug', 'data')
                ->orderBy('id', 'asc')
                ->get()->keyBy('slug');

            $leopards = new LeopardsCODClient();

            /**
             * Merge Booked Packet with LCS Credentials
             */
            $booked_packet = array_merge(array(
                'api_key' => $leopards_settings['api-key']->data,              // API Key provided by LCS
                'api_password' => $leopards_settings['api-password']->data,    // API Password provided by LCS
                'enable_test_mode' => ($leopards_settings['mode']->data) ? true : false,                 // [Optional] default value is 'false', true|false to set mode test or live
            ), $booked_packet);

            return $leopards->bookPacket($booked_packet);

        } catch (\Exception $exception) {
            return array(
                'status' => 0,
                'track_number' => '',
                'slip_link' => '',
                'error_msg' => $exception->getMessage(),
            );
        }
    }
}
