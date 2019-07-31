<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTitleIdColumnToReceiptsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('receipts', function (Blueprint $table) {
            $table->unsignedBigInteger('title_id')->after('user_id');
            $table->foreign('title_id')->references('id')->on('tour_titles')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('receipts', function (Blueprint $table) {
            $table->dropForeign(['title_id']);
            $table->dropColumn('title_id');
        });
    }
}
