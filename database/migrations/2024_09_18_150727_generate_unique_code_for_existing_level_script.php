<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GenerateUniqueCodeForExistingLevelScript extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $getLevel = DB::table('grades')->get();

        foreach($getLevel as $level) {
            if(empty($level->unique_code)) {
                $gradeId = $level->id;
        
                $genrateRandomStringLength = 4;
                if(Str::length($gradeId) >= 2) {
                    $genrateRandomStringLength = 3;
                }
                $uniqueCode = 'L'.Str::random($genrateRandomStringLength).$gradeId;
                DB::table('grades')->where('id', $gradeId)->update(['unique_code' => $uniqueCode]);
            }
        }
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
