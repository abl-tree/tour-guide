<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToUserInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_infos', function (Blueprint $table) {
            $table->longText('note')->nullable()->after('last_name');
            $table->string('contact_number')->nullable()->after('note');
            $table->unsignedInteger('rating')->nullable()->after('note');
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
            $table->dropColumn('note');
            $table->dropColumn('contact_number');
            $table->dropColumn('rating');
        });
    }
}
