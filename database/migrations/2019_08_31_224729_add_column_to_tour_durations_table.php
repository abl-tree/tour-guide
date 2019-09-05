<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnToTourDurationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tour_durations', function (Blueprint $table) {
            $table->unsignedBigInteger('tour_history_id')->after('id');
            $table->foreign('tour_history_id')->references('id')->on('tour_titles')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tour_durations', function (Blueprint $table) {
            $table->dropForeign(['tour_history_id']);
            $table->dropColumn('tour_history_id');
        });
    }
}
