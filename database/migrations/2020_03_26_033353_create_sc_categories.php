<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScCategories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sc_categories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('category_name', 30);
            $table->bigInteger('user_id');
            $table->boolean('is_primary')->default(false);
            $table->tinyInteger('depth')->default(1);
            $table->bigInteger('parent_category_id')->nullable();
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
        Schema::dropIfExists('sc_categories');
    }
}
