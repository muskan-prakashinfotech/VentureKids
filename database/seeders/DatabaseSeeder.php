<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        Schema::disableForeignKeyConstraints();

        $this->call(AuthTableSeeder::class);
        $this->call(AdminOthersSeeder::class);
        $this->call(GradeSeeder::class);
        $this->call(TrainerLevelSeeder::class);
        $this->call(ProjectSectionSeeder::class);
        $this->call(ProjectQuestionSeeder::class);
        $this->call(StudentStreamSeeder::class);
        $this->call(TrainerStreamSeeder::class);
        $this->call(StudentContentSeeder::class);
        $this->call(TrainerContentSeeder::class);
        $this->call(StudentGradeSeeder::class);
        $this->call(StudentBoardSeeder::class);
        $this->call(RealQAssessmentSubjectSeeder::class);
        $this->call(RealQAssessmentParameterSeeder::class);
        $this->call(RealQAssessmentScaleSeeder::class);
        $this->call(RealQAssessmentRubricSeeder::class);
        $this->call(RealQAssessmentTopicSeeder::class);
        $this->call(RealQAssessmentQuestionSeeder::class);
        $this->call(SchoolSeeder::class);
        $this->call(AdditionalStudentSeeder::class);
        $this->call(RealQAssessmentSchoolAssignmentSeeder::class);
        $this->call(AssessmentAnswerSeeder::class);
        $this->call(AssessmentStudentReportSeeder::class);
        $this->call(RealQStudentParameterScoreSeeder::class);
        $this->call(AssessmentSchoolReportSeeder::class);
        $this->call(StandardAssessmentCategorySeeder::class);
        $this->call(StandardAssessmentQuestionSeeder::class);
        $this->call(StandardAssessmentStudentAnswerSeeder::class);
        $this->call(StandardAssessmentReportSeeder::class);
        $this->call(AssignmentSeeder::class);
        $this->call(ObservationSeeder::class);
        $this->call(ExternalSessionSeeder::class);
        $this->call(AIToolSeeder::class);
        $this->call(StudentProjectSeeder::class);
        $this->call(MarketplaceSeeder::class);
        $this->call(DailyChallengeSeeder::class);
        $this->call(IndustryChallengeSeeder::class);
        $this->call(SessionObservationSeeder::class);
        $this->call(StudentRewardCompletionSeeder::class);
        $this->call(PartnerSeeder::class);
        $this->call(PartnerCreatedSchoolSeeder::class);
        Schema::enableForeignKeyConstraints();
    }
}
