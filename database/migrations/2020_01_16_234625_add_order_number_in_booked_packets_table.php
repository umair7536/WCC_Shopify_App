<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOrderNumberInBookedPacketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('booked_packets', function (Blueprint $table) {
            $table->string('order_number')->nullable()->after('collect_amount');
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
            $table->dropColumn('order_number');
        });
    }
}
