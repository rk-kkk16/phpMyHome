<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGmSearchSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gm_search_settings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user_id');
            $table->string('subject', 100);
            $table->string('search_keyword', 100);
            $table->mediumText('filtering_keywords')->nullable();
            $table->integer('filtering_threshold')->default(0);
            $table->boolean('do_crawl_flg')->default(false);
            $table->timestamp('last_crawled')->nullable();
            $table->timestamp('next_crawl')->nullable();
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
        Schema::dropIfExists('gm_search_settings');
    }
}
