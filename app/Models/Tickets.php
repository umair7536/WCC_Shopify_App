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

    protected $fillable = ['number', 'technician_remarks', 'customer_complain', 'total_products', 'total_repairs', 'ticket_status_id', 'customer_id', 'created_by', 'updated_by',  'active', 'created_at', 'updated_at','account_id'];

    protected static $_fillable = ['number', 'technician_remarks', 'customer_complain', 'total_products', 'total_repairs', 'ticket_status_id', 'customer_id', 'created_by', 'updated_by',];

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
     * Get the Ticket Statuses for Ticket.
     */
    public function ticket_repairs()
    {
        return $this->hasMany('App\Models\TicketRepairs', 'ticket_id');
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
        $orderBy = 'tickets.created_at';
        $order = 'desc';

        if ($request->get('order')[0]['dir']) {
            $orderColumn = $request->get('order')[0]['column'];
            $orderBy = $request->get('columns')[$orderColumn]['data'];
            $order = $request->get('order')[0]['dir'];
        }

        $query = self
            ::join('shopify_customers','shopify_customers.customer_id', '=', 'tickets.customer_id')
            ->join('ticket_products','ticket_products.ticket_id', '=', 'tickets.id');

        if ($account_id) {
            $query->where('tickets.account_id', '=', $account_id);
        }

        if ($request->get('number')) {
            $query->where('tickets.number', 'like', '%' . $request->get('number') . '%');
        }

        if ($request->get('customer_name')) {
            $customer_name = $request->get('customer_name');
            $query->where(function ($sub_query) use($customer_name) {
                $sub_query->orWhere('shopify_customers.first_name', 'like', '%' . $customer_name . '%');
                $sub_query->orWhere('shopify_customers.last_name', 'like', '%' . $customer_name . '%');
                $sub_query->orWhere('shopify_customers.email', 'like', '%' . $customer_name . '%');
                $sub_query->orWhere('shopify_customers.phone', 'like', '%' . $customer_name . '%');
            });
        }

        if ($request->get('serial_number')) {
            $query->where('ticket_products.serial_number', 'like', '%' . $request->get('serial_number') . '%');
        }

        if ($request->get('total_products')) {
            $query->where('tickets.total_products', '=', $request->get('total_products'));
        }

        if ($request->get('ticket_status_id')) {
            $query->where('tickets.ticket_status_id', '=', $request->get('ticket_status_id'));
        }

        return $query
            ->select('tickets.*', 'shopify_customers.first_name', 'shopify_customers.last_name', 'shopify_customers.email', 'shopify_customers.phone')
            ->groupBy('tickets.id')
            ->orderBy($orderBy, $order)
            ->get();
        '';

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

        if(isset($data['repair_product_id'])) {
            if(is_array($data['repair_product_id']) && count($data['repair_product_id'])) {
                $data['total_repairs'] = count($data['repair_product_id']);
            }
        } else {
            $data['total_repairs'] = 0;
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
                    'serial_number' => $data['serial_number'][$key],
                    'customer_feedback' => $data['customer_feedback'][$key],
                    'variant_id' => $data['variant_id'][$key],
                    'product_id' => $product_id
                );
            }
            if(count($ticket_products)) {
                TicketProducts::insert($ticket_products);
            }
        }

        /**
         * Create Ticket Repairs Records
         */
        TicketRepairs::where([
            'ticket_id' => $record->id
        ])->forceDelete();

        if(isset($data['repair_product_id'])) {
            if(is_array($data['repair_product_id']) && count($data['repair_product_id'])) {
                $ticket_products = [];
                foreach($data['repair_product_id'] as $key => $product_id) {
                    $ticket_products[] = array(
                        'ticket_id' => $record->id,
                        'serial_number' => $data['repair_serial_number'][$key],
                        'customer_feedback' => $data['repair_customer_feedback'][$key],
                        'variant_id' => $data['repair_variant_id'][$key],
                        'product_id' => $product_id
                    );
                }
                if(count($ticket_products)) {
                    TicketRepairs::insert($ticket_products);
                }
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
        $data = $request->all();

        // Set Account ID
        $data['account_id'] = $account_id;

        if(is_array($data['product_id']) && count($data['product_id'])) {
            $data['total_products'] = count($data['product_id']);
        }

        if(isset($data['repair_product_id'])) {
            if(is_array($data['repair_product_id']) && count($data['repair_product_id'])) {
                $data['total_repairs'] = count($data['repair_product_id']);
            }
        } else {
            $data['total_repairs'] = 0;
        }

        $record = self::where([
            'id' => $id,
            'account_id' => $account_id
        ])->first();

        if (!$record) {
            return null;
        }

        $record->update($data);

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
                    'serial_number' => $data['serial_number'][$key],
                    'customer_feedback' => $data['customer_feedback'][$key],
                    'variant_id' => $data['variant_id'][$key],
                    'product_id' => $product_id
                );
            }
            if(count($ticket_products)) {
                TicketProducts::insert($ticket_products);
            }
        }

        /**
         * Create Ticket Repairs Records
         */
        TicketRepairs::where([
            'ticket_id' => $record->id
        ])->forceDelete();

        if(isset($data['repair_product_id'])) {
            if(is_array($data['repair_product_id']) && count($data['repair_product_id'])) {
                $ticket_products = [];
                foreach($data['repair_product_id'] as $key => $product_id) {
                    $ticket_products[] = array(
                        'ticket_id' => $record->id,
                        'serial_number' => $data['repair_serial_number'][$key],
                        'customer_feedback' => $data['repair_customer_feedback'][$key],
                        'variant_id' => $data['repair_variant_id'][$key],
                        'product_id' => $product_id
                    );
                }
                if(count($ticket_products)) {
                    TicketRepairs::insert($ticket_products);
                }
            }
        }

        /**
         * If repaired status found, set status to ticket
         */
        if($request->get('repaired') == '1') {
            $ticket_status = TicketStatuses::where(array(
                'account_id' => $account_id,
                'slug' => 'repaired',
            ))->first();

            if($ticket_status) {
                self::where(array(
                    'account_id' => $account_id,
                    'id' => $record->id
                ))->update(array(
                    'ticket_status_id' => $ticket_status->id
                ));
            }
        }
        /**
         * Ticket status modification ends here
         */

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
