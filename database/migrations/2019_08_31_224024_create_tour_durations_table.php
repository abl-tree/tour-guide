<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTourDurationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tour_durations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tour_id');
            $table->foreign('tour_id')->references('id')->on('tour_titles')->onDelete('cascade');
            $table->bigInteger('duration_day')->default(0);
            $table->string('duration_time');
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
        Schema::dropIfExists('tour_durations');
    }
}
