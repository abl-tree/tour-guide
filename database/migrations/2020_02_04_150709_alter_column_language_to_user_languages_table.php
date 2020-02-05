<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterColumnLanguageToUserLanguagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_languages', function (Blueprint $table) {
            $table->dropColumn('language');
            $table->unsignedBigInteger('language_id')->after('user_id');
            $table->foreign('language_id')->references('id')->on('languages')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_languages', function (Blueprint $table) {
            $table->string('language')->after('user_id');
            $table->dropForeign(['language_id']);
            $table->dropColumn('language_id');
        });
    }
}
