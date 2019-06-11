<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAuditTrailChangesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('audit_trail_changes', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('audit_trail_id');

            $table->string('field_name', 500);
            $table->text('field_before');
            $table->text('field_after');

            // Manage Foreing Key Relationshops
            $table->foreign('audit_trail_id')
                ->references('id')
                ->on('audit_trails');

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
        Schema::dropIfExists('audit_trail_changes');
    }
}
