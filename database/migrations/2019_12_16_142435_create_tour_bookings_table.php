<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTourBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tour_bookings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tour_departure_id');
            $table->foreign('tour_departure_id')->references('id')->on('tour_departures')->onDelete('cascade');
            $table->string('name');
            $table->unsignedInteger('party_size');
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
        Schema::dropIfExists('tour_bookings');
    }
}
