<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateImageposts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('imageposts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title', 100);
            $table->bigInteger('user_id');
            $table->integer('imp_category_id')->nullable();
            $table->mediumText('tagtext');
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
        Schema::dropIfExists('imageposts');
    }
}
