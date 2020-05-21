<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLoadSheetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('load_sheets', function (Blueprint $table) {
            $table->increments('id');

            $table->string('load_sheet_id')->nullable();
            $table->string('total_packets')->nullable();

            $table->unsignedInteger('account_id')->nullable();
            $table->foreign('account_id', 'load_sheets_account')->references('id')->on('accounts');

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
        Schema::dropIfExists('load_sheets');
    }
}
