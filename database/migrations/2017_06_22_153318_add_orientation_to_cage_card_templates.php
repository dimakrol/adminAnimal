<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOrientationToCageCardTemplates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cage_card_templates', function (Blueprint $table) {
            $table->string('orientation', 255);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cage_card_templates', function (Blueprint $table) {
            $table->dropColumn('orientation');
        });
    }
}
