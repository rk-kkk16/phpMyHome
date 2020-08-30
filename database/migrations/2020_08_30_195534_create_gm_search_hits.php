<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGmSearchHits extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gm_search_hits', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('gm_search_setting_id');
            $table->bigInteger('gm_search_history_id');
            $table->bigInteger('gm_search_game_data_id');
            $table->bigInteger('user_id');
            $table->string('stm_title', 100);
            $table->string('stm_release_date', 100);
            $table->enum('stm_release_status', ['yet','early','release']);
            $table->mediumText('stm_game_data')->nullable();
            $table->mediumText('stm_game_src')->nullable();
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
        Schema::dropIfExists('gm_search_hits');
    }
}
