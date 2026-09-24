<?php

namespace Database\Seeders;

use App\Models\RealQAssessmentParameter;
use App\Models\RealQAssessmentScale;
use App\Models\RealQAssessmentSchoolAssignment;
use App\Models\School;
use App\Models\StudentBoard;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Seeds the `realq_assessment_school_assignments` table — assigns Demo
 * School 1 to the RealQ program for StudentGrade 1, active now. Mirrors
 * Backend\SchoolController's real assignment flow: one row per grade, with
 * a CSV of parameter ids and a single scale id. Requires
 * RealQAssessmentParameterSeeder, RealQAssessmentScaleSeeder and
 * StudentBoardSeeder to have already run.
 */
class RealQAssessmentSchoolAssignmentSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::where('official_email_id', 'school1@venturekids.test')->first();
        $board = StudentBoard::where('name', 'CBSE')->first();
        $scale = RealQAssessmentScale::where('name', 'RealQ 4-Point Growth Scale')->first();
        $parameterIds = RealQAssessmentParameter::pluck('id')->all();

        if (!$school || !$board || !$scale || empty($parameterIds)) {
            $this->command?->error('School/board/scale/parameters not found — run SchoolSeeder, StudentBoardSeeder, RealQAssessmentScaleSeeder and RealQAssessmentParameterSeeder first.');
            return;
        }

        RealQAssessmentSchoolAssignment::firstOrCreate(
            ['school_id' => $school->id, 'realq_assessment_assigned_grade_id' => 1],
            [
                'realq_assessment_assigned' => 1,
                'realq_assessment_enabled' => 1,
                'realq_assessment_enabled_from' => Carbon::now()->subDays(30)->format('Y-m-d'),
                'realq_assessment_enabled_to' => Carbon::now()->addDays(60)->format('Y-m-d'),
                'realq_assessment_assigned_board_id' => $board->id,
                'realq_assessment_assigned_parameters_id' => implode(',', $parameterIds),
                'realq_assessment_assigned_scale_id' => $scale->id,
            ]
        );

        $this->command?->info("Assigned {$school->school_name} to RealQ Assessment for StudentGrade 1.");
    }
}
