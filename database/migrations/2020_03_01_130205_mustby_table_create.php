<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MustbyTableCreate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        schema::create('mustbuys', function(Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('item_name', 30);
            $table->integer('quantity');
            $table->tinyInteger('level');
            $table->string('memo', 100)->default('');
            $table->enum('state', ['yet', 'done'])->default('yet');
            $table->integer('create_user_id');
            $table->integer('buy_user_id')->nullable();
            $table->timestamp('buy_at')->nullable();
            $table->timestamp('edited_at')->nullable();
            $table->integer('edited_user_id')->nullable();
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
        Schema::dropIfExists('mustbuys');
    }
}
