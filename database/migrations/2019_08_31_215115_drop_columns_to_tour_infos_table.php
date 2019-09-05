<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropColumnsToTourInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tour_infos', function (Blueprint $table) {
            $table->dropColumn('cash');
            $table->dropColumn('invoice');
            $table->dropColumn('payoneer');
            $table->dropColumn('paypal');
            $table->dropColumn('adult_price');
            $table->dropColumn('children_price');
            $table->dropColumn('duration_day');
            $table->dropColumn('duration_time');
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
            $table->decimal('cash', 15, 2)->default(0);
            $table->decimal('invoice', 15, 2)->default(0);
            $table->decimal('payoneer', 15, 2)->default(0);
            $table->decimal('paypal', 15, 2)->default(0);
            $table->decimal('adult_price', 15, 2)->default(0);
            $table->decimal('children_price', 15, 2)->default(0);
        });
    }
}
