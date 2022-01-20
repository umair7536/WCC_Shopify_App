<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWccCitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wcc_cities', function (Blueprint $table) {
            Schema::dropIfExists('wcc_cities');
            $table->increments('id');

            $table->string('city_id')->nullable();
            $table->string('name')->nullable();
//            $table->text('shipment_type')->nullable();

            $table->unsignedInteger('account_id')->nullable();

            // Manage Foreign Key Relationships
            // $table->foreign('account_id')->references('id')->on('accounts');

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
        Schema::dropIfExists('wcc_cities');
    }
}

