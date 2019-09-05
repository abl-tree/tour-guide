<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterForeignKeyToTourRatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tour_rates', function (Blueprint $table) {
            $table->dropForeign(['tour_history_id']);
            $table->foreign('tour_history_id')->references('id')->on('tour_info_histories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tour_rates', function (Blueprint $table) {
            //
        });
    }
}
