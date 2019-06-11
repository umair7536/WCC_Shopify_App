<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use App\Models\AuditTrails;
use Auth;


class Tickets extends BaseModal
{
    use SoftDeletes;

    protected $fillable = ['number', 'technician_remarks', 'customer_complain', 'total_products', 'ticket_status_id', 'customer_id', 'created_by', 'updated_by',  'active', 'created_at', 'updated_at','account_id'];

    protected static $_fillable = ['number', 'technician_remarks', 'customer_complain', 'total_products', 'ticket_status_id', 'customer_id', 'created_by', 'updated_by',];

    protected $table = 'tickets';

    protected static $_table = 'tickets';

    /**
     * Get the Ticket Statuses for Ticket.
     */
    public function ticket_status()
    {
        return $this->belongsTo('App\Models\TicketStatuses', 'ticket_status_id');
    }

    /**
     * Get the Ticket Statuses for Ticket.
     */
    public function customer()
    {
        return $this->belongsTo('App\Models\ShopifyCustomers', 'customer_id');
    }

    /**
     * Get Total Records
     *
     * @param \Illuminate\Http\Request $request
     * @param (int) $account_id Current Organization's ID
     *
     * @return (mixed)
     */
    static public function getTotalRecords(Request $request, $account_id = false)
    {
        $where = array();

        if ($account_id) {
            $where[] = array(
                'account_id',
                '=',
                $account_id
            );
        }

        if ($request->get('number')) {
            $where[] = array(
                'number',
                'like',
                '%' . $request->get('number') . '%'
            );
        }

        if ($request->get('total_products')) {
            $where[] = array(
                'total_products',
                'like',
                '%' . $request->get('total_products') . '%'
            );
        }

        if ($request->get('ticket_status_id')) {
            $where[] = array(
                'ticket_status_id',
                '=',
                $request->get('ticket_status_id')
            );
        }

        if (count($where)) {
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
    static public function getRecords(Request $request, $iDisplayStart, $iDisplayLength, $account_id = false)
    {
        $orderBy = 'created_at';
        $order = 'desc';

        if ($request->get('order')[0]['dir']) {
            $orderColumn = $request->get('order')[0]['column'];
            $orderBy = $request->get('columns')[$orderColumn]['data'];
            $order = $request->get('order')[0]['dir'];
        }

        $where = array();

        if ($account_id) {
            $where[] = array(
                'account_id',
                '=',
                $account_id
            );
        }

        if ($request->get('number')) {
            $where[] = array(
                'number',
                'like',
                '%' . $request->get('number') . '%'
            );
        }

        if ($request->get('total_products')) {
            $where[] = array(
                'total_products',
                'like',
                '%' . $request->get('total_products') . '%'
            );
        }

        if ($request->get('ticket_status_id')) {
            $where[] = array(
                'ticket_status_id',
                '=',
                $request->get('ticket_status_id')
            );
        }

        if (count($where)) {
            return self::where($where)
                ->limit($iDisplayLength)
                ->offset($iDisplayStart)
                ->orderBy($orderBy, $order)
                ->get();
        } else {
            return self::limit($iDisplayLength)
                ->offset($iDisplayStart)
                ->orderBy($orderBy, $order)
                ->get();
        }
    }

    /**
     * Calculate Price based on ticket price
     *
     * @param (array) $services
     * @param (double) $services_price
     * @param (double) $price
     *
     * @return (array) $services
     */
    static function calculatePrices($services, $services_price, $price)
    {

        $calculated_services = array();

        /*
         * Case 1: $services_price is greater than $price
         */
        if ($services_price == $price) {
            foreach ($services as $key => $service) {
                $services[$key]['calculated_price'] = $services[$key]['service_price'];
            }
        } else if ($services_price > $price) {
            $ratio = (1 - round(($price / $services_price), 8));
            foreach ($services as $key => $service) {
                $services[$key]['calculated_price'] = round($services[$key]['service_price'] - ($services[$key]['service_price'] * $ratio), 2);
            }
        } else {
            $ratio = -1 * (1 - round(($price / $services_price), 8));
            foreach ($services as $key => $service) {
                $services[$key]['calculated_price'] = round($services[$key]['service_price'] + ($services[$key]['service_price'] * $ratio), 2);
            }
        }

        return $services;
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

        // Set Account ID
        $data['account_id'] = $account_id;

        // Set Max Number
        $data['number'] = (Tickets::where(['account_id' => $account_id])->max('number') + 1);

        if(is_array($data['product_id']) && count($data['product_id'])) {
            $data['total_products'] = count($data['product_id']);
        }

        $record = self::create($data);

        /**
         * Create Ticket Products Records
         */
        TicketProducts::where([
            'ticket_id' => $record->id
        ])->forceDelete();

        if(is_array($data['product_id']) && count($data['product_id'])) {
            $ticket_products = [];
            foreach($data['product_id'] as $key => $product_id) {
                $ticket_products[] = array(
                    'ticket_id' => $record->id,
                    'product_id' => $product_id
                );
            }
            if(count($ticket_products)) {
                TicketProducts::insert($ticket_products);
            }
        }

        return $record;
    }

    /**
     * Delete Record
     *
     * @param id
     *
     * @return (mixed)
     */
    static public function DeleteRecord($id)
    {
        $ticket = Tickets::getData($id);

        if (!$ticket) {

            flash('Resource not found.')->error()->important();
            return redirect()->route('admin.tickets.index');
        }

        // Check if child records exists or not, If exist then disallow to delete it.
        if (Tickets::isChildExists($id, Auth::User()->account_id)) {

            flash('Child records exist, unable to delete resource')->error()->important();
            return redirect()->route('admin.tickets.index');
        }

        $record = $ticket->delete();

        // Delete Old Ticket relationships
        TicketProducts::where(['ticket_id' => $id])->delete();

        flash('Record has been deleted successfully.')->success()->important();

        return $record;

    }

    /**
     * inactive Record
     *
     * @param id
     *
     * @return (mixed)
     */
    static public function inactiveRecord($id)
    {

        $ticket = Tickets::getData($id);

        if (!$ticket) {
            flash('Resource not found.')->error()->important();
            return redirect()->route('admin.tickets.index');
        }

        $record = $ticket->update(['active' => 0]);

        flash('Record has been inactivated successfully.')->success()->important();

        AuditTrails::InactiveEventLogger(self::$_table, 'inactive', self::$_fillable, $id);

        return $record;
    }

    /**
     * active Record
     *
     * @param id
     *
     * @return (mixed)
     */
    static function activeRecord($id)
    {

        $ticket = Tickets::getData($id);

        if (!$ticket) {

            flash('Resource not found.')->error()->important();
            return redirect()->route('admin.tickets.index');
        }

        $record = $ticket->update(['active' => 1]);

        flash('Record has been activated successfully.')->success()->important();

        AuditTrails::activeEventLogger(self::$_table, 'active', self::$_fillable, $id);

        return $record;
    }

    /**
     * Update Record
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return (mixed)
     */
    static public function updateRecord($id, $request, $account_id)
    {
        $old_data = (Tickets::find($id))->toArray();

        $data = $request->all();
        // Set Account ID
        $data['account_id'] = $account_id;
        $data['type'] = 'multiple';

        if(!isset($data['apply_discount'])) {
            $data['apply_discount'] = 0;
        } else if($data['apply_discount'] == '') {
            $data['apply_discount'] = 0;
        }

        if(is_array($data['service_id']) && count($data['service_id'])) {
            $data['total_services'] = count($data['service_id']);

            $data['services_price'] = 0.00;
            foreach($data['service_price'] as $service_price) {
                $data['services_price'] = $data['services_price'] + $service_price;
            }
        }

        $record = self::where([
            'id' => $id,
            'account_id' => $account_id
        ])->first();

        if (!$record) {
            return null;
        }

        $record->update($data);

        AuditTrails::EditEventLogger(self::$_table, 'edit', $data, self::$_fillable, $old_data, $id);

        // Delete Old Ticket relationships
        TicketHasServices::where(['ticket_id' => $record->id])->delete();

        // Deactivate Previous Price History
        TicketServicesPriceHistory::where(['ticket_id' => $record->id])
            ->whereNull('effective_to')
            ->update(array(
                'effective_to' => Carbon::now()->format('Y-m-d'),
                'active' => 0,
                'updated_by' => Auth::User()->id,
            ));

        // Create New Ticket Services
        if(is_array($data['service_id']) && count($data['service_id'])) {
            $services = Services::whereIn('id', $data['service_id'])->where(['account_id' => $account_id])->get()->getDictionary();

            // Calculate New Service Prices
            $services_calculation = array();
            foreach ($data['service_id'] as $key => $service_id) {
                if (array_key_exists($service_id, $services)) {
                    $services_calculation[$key] = array(
                        'service_id' => $service_id,
                        'service_price' => $data['service_price'][$key],
                        'calculated_price' => 0.00,
                    );
                }
            }
            $calculated_services = self::calculatePrices($services_calculation, $data['services_price'], $data['price']);

            foreach($data['service_id'] as $key => $service_id) {
                if(array_key_exists($service_id, $services)) {
                    TicketHasServices::createRecord(array(
                        'ticket_id' => $record->id,
                        'service_id' => $service_id,
                        'service_price' => $calculated_services[$key]['service_price'],
                        'calculated_price' => $calculated_services[$key]['calculated_price'],
                        'end_node' => $services[$service_id]->end_node,
                    ), $record->id);

                    TicketServicesPriceHistory::createRecord(array(
                        'ticket_id' => $record->id,
                        'ticket_price' => $record->price,
                        'service_id' => $service_id,
//                        'service_price' => $data['service_price'][$key],
                        'service_price' => $calculated_services[$key]['calculated_price'],
                        'effective_from' => \Carbon\Carbon::now()->format('Y-m-d'),
                        'created_by' => Auth::User()->id,
                        'updated_by' => Auth::User()->id,
                    ), $account_id);
                }
            }
        }

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

    static public function getTickets()
    {

        return self::where([
            ['account_id', '=', session('account_id')],
            ['active', '=', '1'],
        ])->OrderBy('sort_number', 'asc')->get()->pluck('name', 'id');

    }
}
