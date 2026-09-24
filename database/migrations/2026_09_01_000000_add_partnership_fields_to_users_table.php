<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'partnership_start_date')) {
                $table->date('partnership_start_date')->nullable()->after('no_of_license_purchased');
            }
            if (!Schema::hasColumn('users', 'partnership_end_date')) {
                $table->date('partnership_end_date')->nullable()->after('partnership_start_date');
            }
            if (!Schema::hasColumn('users', 'currency_id')) {
                $table->unsignedBigInteger('currency_id')->nullable()->after('partnership_end_date');
            }
            if (!Schema::hasColumn('users', 'partnership_reminder_job_ids')) {
                $table->json('partnership_reminder_job_ids')->nullable()->after('currency_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            foreach ([
                'partnership_start_date',
                'partnership_end_date',
                'currency_id',
                'partnership_reminder_job_ids',
            ] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
