<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRecurringFieldsToExternalSessionTable extends Migration
{
    public function up()
    {
        Schema::table('external_session', function (Blueprint $table) {
            if (!Schema::hasColumn('external_session', 'recurrence_type')) {
                $table->string('recurrence_type')->nullable()->after('date_time');
            }
            if (!Schema::hasColumn('external_session', 'recurrence_interval')) {
                $table->unsignedSmallInteger('recurrence_interval')->default(1)->after('recurrence_type');
            }
            if (!Schema::hasColumn('external_session', 'recurrence_days')) {
                $table->string('recurrence_days')->nullable()->after('recurrence_interval');
            }
            if (!Schema::hasColumn('external_session', 'recurrence_end_date')) {
                $table->date('recurrence_end_date')->nullable()->after('recurrence_days');
            }
            if (!Schema::hasColumn('external_session', 'recurrence_count')) {
                $table->unsignedSmallInteger('recurrence_count')->nullable()->after('recurrence_end_date');
            }
            if (!Schema::hasColumn('external_session', 'parent_session_id')) {
                $table->unsignedBigInteger('parent_session_id')->nullable()->after('recurrence_count');
            }
            if (!Schema::hasColumn('external_session', 'is_exception')) {
                $table->boolean('is_exception')->default(false)->after('parent_session_id');
            }
            if (!Schema::hasColumn('external_session', 'original_date_time')) {
                $table->dateTime('original_date_time')->nullable()->after('is_exception');
            }
            if (!Schema::hasColumn('external_session', 'is_cancelled')) {
                $table->boolean('is_cancelled')->default(false)->after('original_date_time');
            }
            if (!Schema::hasColumn('external_session', 'recurrence_custom_dates')) {
                $table->text('recurrence_custom_dates')->nullable()->after('recurrence_count');
            }
        });
    }

    public function down()
    {
        Schema::table('external_session', function (Blueprint $table) {
            $table->dropColumn([
                'recurrence_type',
                'recurrence_interval',
                'recurrence_days',
                'recurrence_end_date',
                'recurrence_count',
                'parent_session_id',
                'is_exception',
                'original_date_time',
                'is_cancelled',
                'recurrence_custom_dates'
            ]);
        });
    }
}
