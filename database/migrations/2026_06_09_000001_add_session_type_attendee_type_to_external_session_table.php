<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSessionTypeAttendeeTypeToExternalSessionTable extends Migration
{
    public function up()
    {
        Schema::table('external_session', function (Blueprint $table) {
            if (!Schema::hasColumn('external_session', 'attendee_type')) {
                $table->string('attendee_type')->default('schools')->after('attendees');
            }
            if (!Schema::hasColumn('external_session', 'session_type')) {
                $table->tinyInteger('session_type')->default(1)->comment('1=online 0=offline')->after('title');
            }
            if (!Schema::hasColumn('external_session', 'trainer_ids')) {
                $table->string('trainer_ids', 512)->nullable()->after('attendee_type');
            }
            if (!Schema::hasColumn('external_session', 'notify_recipients')) {
                $table->string('notify_recipients', 255)->nullable()->after('batches');
            }
        });
    }

    public function down()
    {
        Schema::table('external_session', function (Blueprint $table) {
            $table->dropColumn(array_filter(
                ['attendee_type', 'session_type', 'trainer_ids', 'notify_recipients'],
                fn($col) => Schema::hasColumn('external_session', $col)
            ));
        });
    }
}
