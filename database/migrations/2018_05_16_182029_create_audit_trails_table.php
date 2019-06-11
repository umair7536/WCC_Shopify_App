<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAuditTrailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('audit_trails', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('audit_trail_action_name');
            $table->unsignedInteger('audit_trail_table_name');
            $table->unsignedInteger('table_record_id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('parent_id')->nullable();

            // Manage Foreing Key Relationshops
            $table->foreign('user_id')
                ->references('id')
                ->on('users');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('audit_trails');
    }
}
