<?php // Code within app\Helpers\Helper.php

namespace App\Helpers;

use App\Models\SchoolAcademicYear;
use App\Models\StudentRewardPoints;
use App\Models\Students;
use Carbon\Carbon;
use DB;

class StudentRewardPointsHelper
{
    public static function storeRewardPoints($rewardPointsData = array())
    {
        $reward_points = new StudentRewardPoints;
        $reward_points->student_id = $rewardPointsData['student_id'];
        $reward_points->reward_type = $rewardPointsData['reward_type'];
        $reward_points->item_id = $rewardPointsData['item_id'];
        if(array_key_exists('item_type', $rewardPointsData)) {
            $reward_points->item_type = $rewardPointsData['item_type'];
        }
        $reward_points->reward_points = $rewardPointsData['reward_points'];
        $reward_points->save();
    }

    public static function getRewardPoints($student_id, $academicYearFilter = null, $month = null)
    {
        $exclude_reward_type = ['scorm_learning_reward', 'scorm_completion', 'external_reward','assignment_scorm_point','youtube_completion'];
        $rewardPointsQuery = self::rewardPointsQuery($student_id, $academicYearFilter, $month);
        $reward_pts_total = (clone $rewardPointsQuery)
            ->whereNotIn('reward_type', $exclude_reward_type)
            ->groupBy('reward_type')
            ->selectRaw('reward_type, sum(reward_points) as reward_points')
            ->pluck('reward_points', 'reward_type')
            ->toArray();
        
        $completion_pt = 0;

        $scorm_score_pts_total = (clone $rewardPointsQuery)
            ->where('reward_type', 'scorm_learning_reward')
            ->groupBy('item_id')
            ->selectRaw('item_id, sum(reward_points)/count(item_id)  as reward_points')
            ->get();
        if($scorm_score_pts_total->count()) {
            $completion_pt = round($scorm_score_pts_total->sum('reward_points'));
        }

        // $youtube_score_pts_total = StudentRewardPoints::where('student_id', $student_id)->where('reward_type', 'youtube_completion')->groupBy('reward_type')->selectRaw('reward_type, sum(reward_points) as reward_points')->pluck('reward_points', 'reward_type');
        // if($youtube_score_pts_total->count()) {
        //     $completion_pt += $youtube_score_pts_total['youtube_completion'];
        // }

        $assignment_scorm_pts_total = (clone $rewardPointsQuery)
            ->where('reward_type', 'assignment_scorm_point')
            ->groupBy('reward_type')
            ->selectRaw('reward_type, sum(reward_points) as reward_points')
            ->pluck('reward_points', 'reward_type');
        if ($assignment_scorm_pts_total ->count()) {
            $completion_pt += $assignment_scorm_pts_total ['assignment_scorm_point'];
        }

        if($completion_pt) {
            $reward_pts_total['scorm_learning_reward'] = $completion_pt;
        }

        if(array_key_exists('daily_challenge', $reward_pts_total)) {
            $reward_pts_total['weekly_challenge'] = ($reward_pts_total['weekly_challenge'] ?? 0) + $reward_pts_total['daily_challenge'];
            unset($reward_pts_total['daily_challenge']);
        }
        
        return $reward_pts_total;
    }

    protected static function rewardPointsQuery($student_id, $academicYearFilter = null, $month = null)
    {
        $query = StudentRewardPoints::where('student_id', $student_id);
        $student = Students::select('id', 'school_id')->find($student_id);
        $pointsTable = (new StudentRewardPoints())->getTable();

        if (!empty($academicYearFilter) && $academicYearFilter !== 'past') {
            if (!empty($student) && !empty($student->school_id)) {
                $academicYear = SchoolAcademicYear::where('school_id', $student->school_id)
                    ->where('id', $academicYearFilter)
                    ->first();

                if (!empty($academicYear)) {
                    $query->whereDate("{$pointsTable}.created_at", '>=', Carbon::parse($academicYear->start_date)->toDateString())
                        ->whereDate("{$pointsTable}.created_at", '<=', Carbon::parse($academicYear->end_date)->toDateString());

                    if (!empty($month)) {
                        $query->whereMonth("{$pointsTable}.created_at", (int) $month);
                    }
                }
            }

            return $query;
        }

        if ($academicYearFilter === 'past' && !empty($student) && !empty($student->school_id)) {
            $query->whereNotExists(function ($subQuery) use ($student, $pointsTable) {
                $subQuery->select(DB::raw(1))
                    ->from((new SchoolAcademicYear())->getTable())
                    ->where('school_id', $student->school_id)
                    ->whereRaw("DATE({$pointsTable}.created_at) BETWEEN school_academic_years.start_date AND school_academic_years.end_date");
            });
        }

        return $query;
    }

    public static function checkRewardTypeExist($student_id, $reward_type, $item_id)
    {
        return StudentRewardPoints::select('id')->where('student_id', $student_id)->where('reward_type', $reward_type)->where('item_id', $item_id)->get();
    }

    public static function updateRewardPoints($rewardPointsData = array())
    {
        $reward_points = StudentRewardPoints::find($rewardPointsData['id']);
        $reward_points->reward_points = $rewardPointsData['reward_points'];
        $reward_points->save();
    }

    public static function deleteRewardPoints($rewardPointsData = array())
    {
        StudentRewardPoints::find($rewardPointsData['id'])->delete();
    }

    public static function getAcademicYearDateRange($schoolId, $academicYearFilter)
    {
        if (empty($schoolId) || empty($academicYearFilter) || $academicYearFilter === 'past') {
            return [null, null];
        }
        $ay = SchoolAcademicYear::where('school_id', $schoolId)->where('id', $academicYearFilter)->first();
        if (!$ay) {
            return [null, null];
        }
        return [
            Carbon::parse($ay->start_date)->toDateString(),
            Carbon::parse($ay->end_date)->toDateString(),
        ];
    }

    public static function getRewardTypes($student_id, $reward_type = array())
    {
        return StudentRewardPoints::whereIn('reward_type', $reward_type)->where('student_id', $student_id)->get();
    }

    public static function getRewardCountByType($student_id = array(), $reward_type = array())
    {
        return StudentRewardPoints::whereIn('student_id', $student_id)->whereIn('reward_type', $reward_type)->count();
    }


}
