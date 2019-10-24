<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConsigneesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('consignees', function (Blueprint $table) {
            $table->increments('id');

            $table->unsignedInteger('consignee_id')->nullable();
            $table->string('name');
            $table->string('email', 255);
            $table->string('phone', 255);
            $table->string('phone_2', 255)->nullable();
            $table->string('phone_3', 255)->nullable();
            $table->text('address');
            $table->unsignedTinyInteger('active')->default(1);
            $table->unsignedInteger('account_id')->nullable();
            $table->unsignedInteger('city_id')->nullable();

            // Add foreign key
            $table->foreign('account_id','consignees_account')->references('id')->on('accounts');
            $table->foreign('city_id','consignees_city')->references('id')->on('leopards_cities');

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
        Schema::dropIfExists('consignees');
    }
}
