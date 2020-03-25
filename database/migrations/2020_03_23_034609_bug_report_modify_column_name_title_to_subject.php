<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class BugReportModifyColumnNameTitleToSubject extends Migration
{

    public function __construct()
    {
        DB::getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bug_reports', function (Blueprint $table) {
            $table->renameColumn('title', 'subject');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bug_reports', function (Blueprint $table) {
            $table->renameColumn('subject', 'title');
        });
    }
}
