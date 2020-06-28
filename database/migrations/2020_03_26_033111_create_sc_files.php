<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScFiles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sc_files', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('id_range', 30);
            $table->bigInteger('scrap_entry_id');
            $table->bigInteger('user_id');
            $table->string('file_name', 100);
            $table->string('file_type', 10);
            $table->boolean('is_image')->default(false);
            $table->integer('file_size');
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
        Schema::dropIfExists('sc_files');
    }
}
