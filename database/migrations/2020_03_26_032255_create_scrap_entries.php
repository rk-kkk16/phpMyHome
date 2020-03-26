<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScrapEntries extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('scrap_entries', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('subject', 100);
            $table->longText('body');
            $table->bigInteger('user_id');
            $table->bigInteger('sc_category_id')->default(1);
            $table->integer('good_point')->default(0);
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
        Schema::dropIfExists('scrap_entries');
    }
}
