<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToTourDeparturesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tour_departures', function (Blueprint $table) {
            $table->unsignedInteger('adult_participants')->default(1)->after('notes');
            $table->unsignedInteger('child_participants')->default(0)->after('notes');
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
            $table->dropColumn('adult_participants');
            $table->dropColumn('child_participants');
        });
    }
}
