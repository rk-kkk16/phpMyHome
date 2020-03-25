<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBugReports extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bug_reports', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title', 100);
            $table->tinyInteger('level');
            $table->mediumText('memo')->nullable();
            $table->enum('state', ['yet','done'])->default('yet');
            $table->integer('create_user_id');
            $table->integer('done_user_id')->nullable();
            $table->timestamp('done_at')->nullable();
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
        Schema::dropIfExists('bug_reports');
    }
}
