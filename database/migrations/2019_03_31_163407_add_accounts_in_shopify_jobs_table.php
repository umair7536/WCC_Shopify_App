<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAccountsInShopifyJobsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('shopify_jobs', function (Blueprint $table) {
            $table->unsignedInteger('account_id')->nullable();
            $table->foreign('account_id', 'shopify_jobs_account')->references('id')->on('accounts');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shopify_jobs', function (Blueprint $table) {
            $table->dropForeign('shopify_jobs_account');
            $table->dropColumn('account_id');
        });
    }
}
