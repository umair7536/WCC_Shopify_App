<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsInBookedPacketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('booked_packets', function (Blueprint $table) {
            $table->unsignedTinyInteger('marked_paid')->default(0)->after('invoice_number');
            $table->unsignedTinyInteger('status_check_count')->default(0)->after('marked_paid');
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
            $table->dropColumn('marked_paid');
            $table->dropColumn('status_check_count');
        });
    }
}
