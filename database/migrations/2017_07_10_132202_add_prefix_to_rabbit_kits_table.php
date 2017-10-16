<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPrefixToRabbitKitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rabbit_kits', function (Blueprint $table) {
            $table->string('prefix',255)->nullable()->after('given_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rabbit_kits', function (Blueprint $table) {
            $table->dropColumn('prefix');
        });
    }
}
