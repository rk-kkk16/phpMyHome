<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScGoodTrxs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sc_good_trxs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->biginteger('scrap_entry_id');
            $table->bigInteger('user_id');
            $table->string('date_idx', 8);
            $table->timestamps();
            $table->index(['user_id', 'date_idx']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sc_good_trxs');
    }
}
