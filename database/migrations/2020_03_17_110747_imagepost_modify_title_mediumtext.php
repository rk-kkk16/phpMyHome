<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ImagepostModifyTitleMediumtext extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('imageposts', function(Blueprint $table) {
            $table->mediumText('title')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('imageposts', function(Blueprint $table) {
            $table->string('title', 100)->change();
        });
    }
}
