<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddInvoiceColumnsInBookedPacketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('booked_packets', function (Blueprint $table) {
            $table->string('invoice_number')->nullable()->after('destination_city');
            $table->date('invoice_date')->nullable()->after('invoice_number');
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
            $table->dropColumn('invoice_number');
            $table->dropColumn('invoice_date');
        });
    }
}
