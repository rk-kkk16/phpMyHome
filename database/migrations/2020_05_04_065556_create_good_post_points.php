<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGoodPostPoints extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('good_post_points', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('good_post_id');
            $table->bigInteger('user_id');
            $table->integer('point');
            $table->timestamps();
            $table->unique(['good_post_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('good_post_points');
    }
}
