<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignKeyToTourParticipantRatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tour_participant_rates', function (Blueprint $table) {
            $table->unsignedBigInteger('tour_history_id')->after('id');
            $table->foreign('tour_history_id')->references('id')->on('tour_info_histories')->onDelete('cascade');
            $table->unsignedBigInteger('participant_type_id')->after('id');
            $table->foreign('participant_type_id')->references('id')->on('participant_types')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tour_participant_rates', function (Blueprint $table) {
            $table->dropForeign(['tour_history_id']);
            $table->dropColumn('tour_history_id');
            $table->dropForeign(['participant_type_id']);
            $table->dropColumn('participant_type_id');
        });
    }
}
