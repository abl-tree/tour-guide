<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTourInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('tour_infos'))
        Schema::create('tour_infos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('color')->nullable();
            $table->longText('image_link');
            $table->decimal('cash', 15, 2)->default(0);
            $table->decimal('invoice', 15, 2)->default(0);
            $table->decimal('payoneer', 15, 2)->default(0);
            $table->decimal('paypal', 15, 2)->default(0);
            $table->decimal('adult_price', 15, 2)->default(0);
            $table->decimal('children_price', 15, 2)->default(0);
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
        Schema::dropIfExists('tour_infos');
    }
}
