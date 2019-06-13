<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTicketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->increments('id');
            $table->string('number', 500)->nullable();
            $table->string('draft_order_id', 500)->nullable();
            $table->text('technician_remarks')->nullable();
            $table->text('customer_complain')->nullable();
            $table->integer('total_products')->default(0);
            $table->unsignedTinyInteger('active')->default(1);

            $table->unsignedInteger('ticket_status_id')->nullable();
            $table->string('customer_id')->nullable();
            $table->unsignedInteger('created_by')->nullable();
            $table->unsignedInteger('updated_by')->nullable();
            $table->unsignedInteger('account_id')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('ticket_status_id')->references('id')->on('ticket_statuses');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
            $table->foreign('account_id')->references('id')->on('accounts');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tickets');
    }
}
