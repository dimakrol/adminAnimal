<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFosterToRabbitKitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rabbit_kits', function (Blueprint $table) {
			$table->boolean('foster')->default(0)->after('archived');
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
            $table->dropColumn('foster');
        });
    }
}
