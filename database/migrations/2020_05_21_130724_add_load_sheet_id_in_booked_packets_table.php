<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLoadSheetIdInBookedPacketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('booked_packets', function (Blueprint $table) {
            $table->unsignedInteger('load_sheet_id')->nullable()->after('account_id');
            $table->foreign('load_sheet_id', 'booked_packets_load_sheet')->references('id')->on('load_sheets');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('booked_packets', function (Blueprint $table) {
            $table->dropForeign('booked_packets_load_sheet');
            $table->dropColumn('load_sheet_id');
        });
    }
}
