<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCageCardTemplatesDefaultTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cage_card_templates_default', function (Blueprint $table) {
            $table->increments('id');
            // name: varchar 255
            // type: varchar 255
            // size: varchar 255
            // hole: varchar 255
            // orientation: varchar 255
            // fields: varchar 512
            // user_id: int 10
            $table->string('name', 255);
            $table->string('type', 255);
            $table->string('size', 255);
            $table->string('hole', 255);
            $table->string('orientation', 255);
            $table->string('fields', 512);
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
        Schema::drop('cage_card_templates_default');
    }
}
