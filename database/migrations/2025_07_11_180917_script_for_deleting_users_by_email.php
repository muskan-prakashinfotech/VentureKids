<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ScriptForDeletingUsersByEmail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Get users not in students table
        /*
        $orphanUsers = DB::table('users')
            ->whereNotIn('id', function ($query) {
                $query->select('user_id')->from('students');
            })
            ->get();
        */

        // Print them (this will show in console or logs during migration)
        /*
        foreach ($orphanUsers as $user) {
            echo "Deleting User: ID {$user->id}, Name: {$user->name}, Email: {$user->email}\n";
        }
        */

        $orphanUsers = DB::table('users')
            ->whereNotIn('id', function ($query) {
                $query->select('user_id')->from('students');
            })
            ->whereIn('email', ['chuahhv@gmail.com'])
            ->delete();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
