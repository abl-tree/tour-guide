<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropColumnsFromUserInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_infos', function (Blueprint $table) {
            $table->dropColumn('birthdate');
            $table->dropForeign(['gender_id']);
            $table->dropColumn('gender_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_infos', function (Blueprint $table) {
            $table->date('birthdate')->nullable();
            $table->unsignedBigInteger('gender_id')->after('birthdate');
            $table->foreign('gender_id')->references('id')->on('genders')->onDelete('cascade');
        });
    }
}
