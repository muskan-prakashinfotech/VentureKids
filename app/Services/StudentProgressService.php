<?php

namespace App\Services;

use App\Helpers\StudentRewardPointsHelper;
use App\Models\Event;
use App\Models\EventChallenge;
use App\Models\Project;
use App\Models\StudentCommunications;
use App\Models\StudentObservations;
use App\Models\StudentRewardPoints;
use App\Models\Students;
use App\Models\Stream;
use App\Models\Submission;
use App\Models\ViewTracking;

class StudentProgressService
{
    public static function getData(int $studentId, int $gradeId, ?string $academicYearFilter): array
    {
        $student = Students::select('id', 'user_id', 'school_id', 'grade_id', 'created_at', 'country_id')
            ->with(['user' => fn($q) => $q->select('id', 'name')])
            ->find($studentId);

        if (empty($student)) {
            return [];
        }

        $student = $student->toArray();

        // Weekly / daily challenge count
        $weeklyChallengeCount = StudentRewardPoints::where('student_id', $studentId)
            ->whereIn('reward_type', ['weekly_challenge', 'daily_challenge'])
            ->count();

        // Assignment completion
        $assignmentIds = StudentCommunications::select('id')
            ->where('grade_id', $gradeId)
            ->where(fn($q) => $q->where('school_id', $student['school_id'])->orWhere('school_id', 0))
            ->pluck('id');

        $assignmentTotal     = $assignmentIds->count();
        $assignmentCompleted = $assignmentTotal > 0
            ? Submission::where('student_id', $studentId)
                ->whereIn('assignment_id', $assignmentIds)
                ->count()
            : 0;

        // SCORM / session completion
        $scormIds      = Stream::select('id')->where('agegroup_id', $gradeId)->whereNotNull('scormFile')->pluck('id');
        $scormTotal    = $scormIds->count();
        $scormCompleted = $scormTotal > 0
            ? StudentRewardPoints::where('student_id', $studentId)
                ->where('reward_type', 'video_learning_point')
                ->whereIn('item_id', $scormIds)
                ->count()
            : 0;

        // Industry challenges
        $eventIds = Event::where('is_publish', 1)
            ->where(fn($q) => $q
                ->where('visibility_type', 1)
                ->orWhere(fn($q2) => $q2->where('visibility_type', 2)->where('country_id', $student['country_id'])))
            ->pluck('id');

        $industryChallengeCount = $eventIds->isNotEmpty()
            ? EventChallenge::where('student_id', $studentId)
                ->whereIn('event_id', $eventIds)
                ->count()
            : 0;

        // Reward points (badges)
        $rewardPoints = StudentRewardPointsHelper::getRewardPoints($studentId, $academicYearFilter);

        return [
            'student'              => $student,
            'weeklyChallengeCount' => $weeklyChallengeCount,
            'completionData'       => [
                'assignment_submission' => ['total' => $assignmentTotal,  'completed' => $assignmentCompleted],
                'scorm_completion'      => ['total' => $scormTotal,        'completed' => $scormCompleted],
            ],
            'assignmentPercentage'   => $assignmentTotal > 0 ? round($assignmentCompleted / $assignmentTotal * 100) : 0,
            'modulePercentage'       => $scormTotal > 0      ? round($scormCompleted      / $scormTotal      * 100) : 0,
            'industryChallengeCount' => $industryChallengeCount,
            'projectCount'           => Project::where('student_id', $studentId)
                ->where('is_publish', 1)
                ->count(),
            'reward_points'          => $rewardPoints,
            'total_points'           => array_sum($rewardPoints),
            'play_count'             => ViewTracking::where('item_type', 'audio')
                ->where('student_id', $studentId)
                ->groupBy('student_id')
                ->selectRaw('student_id, SUM(play_count) as total_play_count')
                ->pluck('total_play_count')
                ->first() ?? 0,
        ];
    }
}
