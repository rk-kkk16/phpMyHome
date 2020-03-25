<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ImpCommentsAddImagepostId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('imp_comments', function (Blueprint $table) {
            $table->bigInteger('imagepost_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('imp_comments', function (Blueprint $table) {
            $table->dropColumn('imagepost_id');
        });
    }
}
