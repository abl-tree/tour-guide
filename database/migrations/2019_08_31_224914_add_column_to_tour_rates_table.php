<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnToTourRatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tour_rates', function (Blueprint $table) {
            $table->unsignedBigInteger('tour_history_id')->after('id');
            $table->foreign('tour_history_id')->references('id')->on('tour_titles')->onDelete('cascade');
            $table->dropForeign(['tour_id']);
            $table->dropColumn('tour_id');
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
            $table->dropForeign(['tour_history_id']);
            $table->dropColumn('tour_history_id');
        });
    }
}
