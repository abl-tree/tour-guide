<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterColumnToTourInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tour_infos', function (Blueprint $table) {
            // $table->unsignedBigInteger('tour_id')->after('id');
            // $table->foreign('tour_id')->references('id')->on('tour_titles')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tour_infos', function (Blueprint $table) {
            $table->foreign(['tour_id']);
            $table->foreign('tour_id');
        });
    }
}
