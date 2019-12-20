<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsInShopifyOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shopify_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('booking_id')->nullable();
            $table->string('cn_number')->nullable();
            $table->unsignedInteger('destination_city')->nullable();
            $table->text('consignment_address')->nullable();

            $table->foreign('booking_id', 'shopify_orders_booking')->references('id')->on('booked_packets');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shopify_orders', function (Blueprint $table) {
            $table->dropForeign('shopify_orders_booking');
            $table->dropColumn('booking_id');
            $table->dropColumn('consignment_address');
            $table->dropColumn('cn_number');
        });
    }
}
