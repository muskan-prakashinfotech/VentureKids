<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class BackfillCreatedByForExistingRecords extends Migration
{
    public function up()
    {
        $superAdminId = DB::table('users')
            ->where('group', 1)
            ->orderBy('id')
            ->value('id');

        // Leave records untouched when no Super Admin exists yet.
        if (!$superAdminId) {
            return;
        }

        foreach (['schools', 'trainers', 'external_session'] as $table) {
            DB::table($table)
                ->where(function ($query) {
                    $query->whereNull('created_by')
                        ->orWhere('created_by', 0)
                        ->orWhere('created_by', '');
                })
                ->update(['created_by' => $superAdminId]);
        }
    }

    public function down()
    {
        // Ownership backfills are intentionally not reversed.
    }
}
