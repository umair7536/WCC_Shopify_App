<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketProducts extends Model
{

    protected $fillable = ['ticket_id', 'product_id'];

    protected $table = 'ticket_products';

    public $timestamps = false;

    /**
     * Get Bundle Service belong to Service.
     */
    public function product()
    {
        return $this->belongsTo('App\Models\ShopifyProducts', 'product_id');
    }

    /**
     * Get Bundle Service belong to Bundle.
     */
    public function ticket()
    {
        return $this->belongsTo('App\Models\Tickets', 'ticket_id');
    }

    /**
     * Create Record
     *
     * @param data
     *
     * @return (mixed)
     */
    static public function createRecord($data)
    {
        return self::create($data);
    }

    /**
     * update Record
     *
     * @param data ,parent_data
     *
     * @return (mixed)
     */
    static public function updateRecord($data)
    {
        return self::create($data);
    }
}
