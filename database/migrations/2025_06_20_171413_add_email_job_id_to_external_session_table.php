<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEmailJobIdToExternalSessionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('external_session', function (Blueprint $table) {
            $table->unsignedBigInteger('email_job_id')->after('is_cancelled');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('external_session', function (Blueprint $table) {
            $table->dropColumn('email_job_id');
        });
    }
}
