<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class RemoveMultipleAssignmentSubmissionData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //  Get duplicate assignment submission data
        $duplicateSubmission = DB::select("SELECT assignment_id, student_id, COUNT(assignment_id) FROM submissions GROUP BY assignment_id, student_id HAVING COUNT(*) > 1");

        $delRewardsds = $delSubmissionds = [];

        if(!empty($duplicateSubmission)) {
            foreach($duplicateSubmission as $key => $rec) {
                
                $getDuplicateSubmissionData = DB::table('submissions')->where('student_id', $rec->student_id)->where('assignment_id', $rec->assignment_id)->orderBy('created_at', 'desc')->get();
                $getDuplicateSubmissionData->shift();
                foreach($getDuplicateSubmissionData as $sId){
                    $delSubmissionds[] = $sId->id;
                }
                $duplicateAssignmentRewarsPoints = DB::select("SELECT item_id, student_id, COUNT(item_id) FROM student_reward_points where student_id = {$rec->student_id} AND item_id = {$rec->assignment_id} AND reward_type = 'assignment_submission' GROUP BY item_id, student_id HAVING COUNT(*) > 1");
                if(!empty($duplicateAssignmentRewarsPoints)) {
                    $getDuplicateRewardData = DB::table('student_reward_points')->where('student_id', $rec->student_id)->where('item_id', $rec->assignment_id)->where
                    ('reward_type', 'assignment_submission')->orderBy('created_at', 'desc')->get();
                    $getDuplicateRewardData->shift();
                    foreach($getDuplicateRewardData as $rId){
                        $delRewardsds[] = $rId->id;
                    }
                }
            }
        }
        
        // Delete duplicate assignment submission data
        if(!empty($delSubmissionds)) {
            DB::table('submissions')->whereIn('id', $delSubmissionds)->delete();
        }
        
        // Delete duplicate assignment submission reward points data
        if(!empty($delRewardsds)) {
            DB::table('student_reward_points')->whereIn('id', $delRewardsds)->delete();
        }

        // Delete older assignment submission data if assignment is deleted
        DB::statement("DELETE FROM submissions WHERE assignment_id NOT IN (SELECT id FROM assignments)");

        // Delete older assignment submission data reward points if assignment is deleted
        DB::statement("DELETE FROM student_reward_points WHERE reward_type = 'assignment_submission' AND item_id NOT IN (SELECT id FROM assignments)");
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
