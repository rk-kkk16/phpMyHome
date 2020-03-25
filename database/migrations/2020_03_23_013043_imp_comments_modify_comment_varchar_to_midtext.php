<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ImpCommentsModifyCommentVarcharToMidtext extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('imp_comments', function (Blueprint $table) {
            $table->mediumText('comment')->change();
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
            $table->string('comment', 100)->change();
        });
    }
}
