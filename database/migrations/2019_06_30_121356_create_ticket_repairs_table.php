<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTicketRepairsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ticket_repairs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('product_id');
            $table->string('variant_id');
            $table->string('serial_number')->nullable();
            $table->text('customer_feedback')->nullable();
            $table->unsignedInteger('ticket_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ticket_repairs');
    }
}
