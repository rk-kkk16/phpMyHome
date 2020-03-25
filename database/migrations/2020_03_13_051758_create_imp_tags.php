<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateImpTags extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('imp_tags', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('tag', 100);
            $table->bigInteger('imagepost_id');
            $table->timestamps();
            $table->index('tag');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('imp_tags');
    }
}
