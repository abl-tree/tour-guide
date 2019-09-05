<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropColumnPaymentTypeIdToTourDeparturesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tour_departures', function (Blueprint $table) {
           $table->dropForeign(['payment_type_id']);
           $table->dropColumn('payment_type_id');
           $table->unsignedBigInteger('tour_rate_id')->after('schedule_id')->nullable();
           $table->foreign('tour_rate_id')->references('id')->on('tour_rates')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tour_departures', function (Blueprint $table) {
            $table->unsignedBigInteger('payment_type_id')->after('schedule_id')->nullable();
            $table->foreign('payment_type_id')->references('id')->on('payment_types')->onDelete('cascade');
            $table->dropForeign(['tour_rate_id']);
            $table->dropColumn('tour_rate_id');
        });
    }
}
