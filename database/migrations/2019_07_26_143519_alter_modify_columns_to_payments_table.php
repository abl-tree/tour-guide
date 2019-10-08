<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterModifyColumnsToPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payments', function (Blueprint $table) {
            // $table->dropForeign(['schedule_id']);
            // $table->dropColumn('schedule_id');
            // $table->unsignedBigInteger('receipt_id')->after('id');
            // $table->foreign('receipt_id')->references('id')->on('receipts')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->unsignedBigInteger('schedule_id');
            $table->foreign('schedule_id')->references('id')->on('schedules')->onDelete('cascade');
            $table->dropForeign(['receipt_id']);
            $table->dropColumn('receipt_id');
        });
    }
}
