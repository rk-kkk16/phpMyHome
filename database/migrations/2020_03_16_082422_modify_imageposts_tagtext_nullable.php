<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyImagepostsTagtextNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('imageposts', function(Blueprint $table) {
            $table->mediumText('tagtext')->nullable()->change();
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
            $table->mediumText('tagtext')->change();
        });
    }
}
