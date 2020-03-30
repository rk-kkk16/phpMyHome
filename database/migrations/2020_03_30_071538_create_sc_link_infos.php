<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScLinkInfos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sc_link_infos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('url', 1000);
            $table->string('md5_url', 32)->unique();
            $table->string('title', 100)->default('無題');
            $table->string('description', 100)->nullable();
            $table->string('image_url', 1000)->nullable();
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
        Schema::dropIfExists('sc_link_infos');
    }
}
