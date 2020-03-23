<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCookingClassesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cooking_classes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->date('date');
            $table->unsignedInteger('no_of_chef');
            $table->decimal('cost_per_chef', 15, 4);
            $table->unsignedInteger('no_of_assistant');
            $table->decimal('cost_per_assistant', 15, 4);
            $table->decimal('fuel_cost', 15, 4);
            $table->decimal('ingredient_cost', 15, 4);
            $table->decimal('other_cost', 15, 4);
            $table->unsignedInteger('no_of_participant');
            $table->decimal('cost_per_participant', 15, 4)->default(0.48);
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
        Schema::dropIfExists('cooking_classes');
    }
}
