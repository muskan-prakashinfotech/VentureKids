<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ScriptToDeleteRecordsFromUsersTableByEmail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Get users not in students table and Delete them
        $orphanUsers = DB::table('users')
            ->whereNotIn('id', function ($query) {
                $query->select('user_id')->from('students');
            })
            ->whereIn('email', ['anandvarun84@gmail.com', 'shalender2k@gmail.com', 'ranbirishere@yahoo.com'])
            ->delete();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
       
    }
}
