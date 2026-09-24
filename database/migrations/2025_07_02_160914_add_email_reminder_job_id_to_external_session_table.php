<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEmailReminderJobIdToExternalSessionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('external_session', function (Blueprint $table) {
            $table->unsignedBigInteger('email_reminder_job_id')->nullable()->after('email_job_id');
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
            $table->dropColumn('email_reminder_job_id');
        });
    }
}
