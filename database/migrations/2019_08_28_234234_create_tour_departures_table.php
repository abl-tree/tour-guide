<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTourDeparturesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tour_departures'))
        Schema::create('tour_departures', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tour_id');
            $table->foreign('tour_id')->references('id')->on('tour_titles')->onDelete('cascade');
            $table->unsignedBigInteger('schedule_id')->nullable();
            $table->foreign('schedule_id')->references('id')->on('schedules')->onDelete('cascade');
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
        Schema::dropIfExists('tour_departures');
    }
}
