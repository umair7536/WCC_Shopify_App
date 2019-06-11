<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CrateShopifyWebhooksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shopify_webhooks', function (Blueprint $table) {
            $table->increments('id');
            $table->text('webhook_id', 255)->nullable();
            $table->longText('address')->nullable();
            $table->string('topic', 255)->nullable();
            $table->string('format', 255)->nullable();
            $table->longText('fields')->nullable();
            $table->longText('metafield_namespaces')->nullable();
            $table->unsignedInteger('account_id')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Add foreign key
            $table->foreign('account_id','shopify_webhooks_account')->references('id')->on('accounts');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shopify_webhooks');
    }
}
