<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsInPermissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('permissions', function($table) {
            $table->string('title')->nullable();
            $table->unsignedSmallInteger('main_group')->default(1);
            $table->unsignedSmallInteger('parent_id')->default(0);
            $table->unsignedSmallInteger('status')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('permissions', function($table) {
            $table->dropColumn('title');
            $table->dropColumn('main_group');
            $table->dropColumn('parent_id');
            $table->dropColumn('status');
        });
    }
}
