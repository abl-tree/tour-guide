<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterColumnsToCookingClassesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cooking_classes', function (Blueprint $table) {
            $table->unsignedInteger('no_of_assistant')->default(0)->change();
            $table->decimal('cost_per_assistant', 15, 4)->default(0)->change();
            $table->decimal('fuel_cost', 15, 4)->default(0)->change();
            $table->decimal('ingredient_cost', 15, 4)->default(0)->change();
            $table->decimal('other_cost', 15, 4)->default(0)->change();
            $table->enum('category', ['am', 'pm'])->default('am')->after('date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cooking_classes', function (Blueprint $table) {
            $table->dropColumn('category');
        });

        Schema::table('cooking_classes', function (Blueprint $table) {
            $table->unsignedInteger('no_of_assistant')->nullable(false)->change();
            $table->decimal('cost_per_assistant', 15, 4)->nullable(false)->change();
            $table->decimal('fuel_cost', 15, 4)->nullable(false)->change();
            $table->decimal('ingredient_cost', 15, 4)->nullable(false)->change();
            $table->decimal('other_cost', 15, 4)->nullable(false)->change();
        });
    }
}
