<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBookPacketUpdateColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('booked_packets');
        Schema::create('booked_packets', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('booked_packet_id')->nullable();
            $table->text('cn_number')->nullable();
            $table->string('shipment_type_id')->nullable();
            $table->date('booking_date')->nullable();
            $table->unsignedInteger('packet_pieces')->nullable();
            $table->double('net_weight', 11, 2)->default(0.00);
            $table->double('collect_amount', 11, 2)->default(0.00);
            $table->string('order_id')->nullable();

            $table->string('vol_weight_w')->nullable();
            $table->string('vol_weight_h')->nullable();
            $table->string('vol_weight_l')->nullable();

            /**
             * Shipper Information
             */
            $table->unsignedInteger('shipper_id')->nullable();
            $table->string('shipper_name');
            $table->string('shipper_email', 255)->nullable();
            $table->string('shipper_phone', 255);
            $table->text('shipper_address');
            $table->unsignedInteger('origin_city')->nullable();

            $table->string('special_handling');
            $table->string('product_description');
            $table->string('InsuranceValue');

            /**
             * Consignee Information
             */
            $table->unsignedInteger('consignee_id')->nullable();
            $table->string('consignee_name');
            $table->string('consignee_email', 255)->nullable();
            $table->string('consignee_phone', 255);
            $table->string('consignee_phone_2', 255)->nullable();
            $table->string('consignee_phone_3', 255)->nullable();
            $table->text('consignee_address');

            $table->unsignedInteger('destination_city')->nullable();

            $table->text('comments')->nullable();

            /**
             * Handle Packet as Production or Test
             * '1' as Test Mode
             * '2' as Production Mode
             */
            $table->tinyInteger('booking_type')->default(1);

            /**
             * Tracking Information
             */
            $table->string('track_number')->nullable();
            $table->text('slip_link')->nullable();

            /**
             * Status Information
             */
            $table->string('status')->default(0);
            $table->text('history')->nullable();

            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->unsignedInteger('account_id')->nullable();

            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->foreign('account_id')->references('id')->on('accounts');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('booked_packets');
    }
}



