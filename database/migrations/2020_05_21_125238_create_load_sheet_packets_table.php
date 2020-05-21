<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLoadSheetPacketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('load_sheet_packets', function (Blueprint $table) {
            $table->increments('id');

            $table->string('sheet_id')->nullable();
            $table->string('cn_number')->nullable();
            $table->string('order_id')->nullable();
            $table->string('order_number')->nullable();

            $table->unsignedInteger('load_sheet_id')->nullable();
            $table->foreign('load_sheet_id', 'load_sheet_packets_load_sheet')->references('id')->on('load_sheets');

            $table->unsignedInteger('booked_packet_id')->nullable();
            $table->foreign('booked_packet_id', 'load_sheet_packets_booked_packet')->references('id')->on('booked_packets');

            $table->unsignedInteger('account_id')->nullable();
            $table->foreign('account_id', 'load_sheet_packets_account')->references('id')->on('accounts');

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
        Schema::dropIfExists('load_sheet_packets');
    }
}
