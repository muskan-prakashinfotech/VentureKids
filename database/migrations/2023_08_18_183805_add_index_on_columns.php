<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexOnColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE `projects` ADD INDEX(`student_id`)');
        DB::statement('ALTER TABLE `students` ADD INDEX(`school_id`)');
        DB::statement('ALTER TABLE `students` ADD INDEX(`user_id`)');
        DB::statement('ALTER TABLE `students` ADD INDEX(`grade_id`)');
        DB::statement('ALTER TABLE `allocation_event` ADD INDEX(`school_id`)');
        DB::statement('ALTER TABLE `assignments` ADD INDEX(`trainer_id`)');
        DB::statement('ALTER TABLE `assignmentsfiles` ADD INDEX(`assignment_id`)');
        DB::statement('ALTER TABLE `assignment_comment` ADD INDEX(`assignment_id`)');
        DB::statement('ALTER TABLE `class_schedule` ADD INDEX(`school_id`)');
        DB::statement('ALTER TABLE `class_schedule` ADD INDEX(`day`)');
        DB::statement('ALTER TABLE `trainer_allocation` ADD INDEX(`school_id`)');
        DB::statement('ALTER TABLE `events` ADD INDEX(`country_id`)');
        DB::statement('ALTER TABLE `event_challenges` ADD INDEX(`event_id`)');
        DB::statement('ALTER TABLE `event_challenge_files` ADD INDEX(`event_challenge_id`)');
        DB::statement('ALTER TABLE `grades` ADD INDEX(`id`)');
        DB::statement('ALTER TABLE `model_has_roles` ADD INDEX(`model_id`)');
        DB::statement('ALTER TABLE `model_has_roles` ADD INDEX(`model_type`)');
        DB::statement('ALTER TABLE `projectfiles` ADD INDEX(`project_id`)');
        DB::statement('ALTER TABLE `projectfiles` ADD INDEX(`student_id`)');
        DB::statement('ALTER TABLE `projects` ADD INDEX(`student_id`)');
        DB::statement('ALTER TABLE `quiz_answers` ADD INDEX(`student_id`)');
        DB::statement('ALTER TABLE `quiz_answers` ADD INDEX(`quiz_id`)');
        DB::statement('ALTER TABLE `quiz_answers` ADD INDEX(`question_id`)');
        DB::statement('ALTER TABLE `quiz_attempts` ADD INDEX(`student_id`)');
        DB::statement('ALTER TABLE `quiz_attempts` ADD INDEX(`quiz_id`)');
        DB::statement('ALTER TABLE `quiz_questions` ADD INDEX(`content_id`)');
        DB::statement('ALTER TABLE `schools` ADD INDEX(`id`)');
        DB::statement('ALTER TABLE `schools` ADD INDEX(`country_id`)');
        DB::statement('ALTER TABLE `schools` ADD INDEX(`user_id`)');
        DB::statement('ALTER TABLE `school_notifications` ADD INDEX(`school_id`)');
        DB::statement('ALTER TABLE `streams` ADD INDEX(`agegroup_id`)');
        DB::statement('ALTER TABLE `students` ADD INDEX(`id`)');
        DB::statement('ALTER TABLE `students` ADD INDEX(`user_id`)');
        DB::statement('ALTER TABLE `students` ADD INDEX(`country_id`)');
        DB::statement('ALTER TABLE `students` ADD INDEX(`school_id`)');
        DB::statement('ALTER TABLE `studentscontents` ADD INDEX(`ageGroup_id`)');
        DB::statement('ALTER TABLE `student_attendance` ADD INDEX(`student_id`)');
        DB::statement('ALTER TABLE `student_attendance` ADD INDEX(`trainer_id`)');
        DB::statement('ALTER TABLE `student_feedback` ADD INDEX(`student_id`)');
        DB::statement('ALTER TABLE `student_feedback` ADD INDEX(`level`)');
        DB::statement('ALTER TABLE `student_notifications` ADD INDEX(`student_id`)');
        DB::statement('ALTER TABLE `submissions` ADD INDEX(`student_id`)');
        DB::statement('ALTER TABLE `submissions` ADD INDEX(`assignment_id`)');
        DB::statement('ALTER TABLE `todo` ADD INDEX(`trainer_id`)');
        DB::statement('ALTER TABLE `trainercontents` ADD INDEX(`agegroup_id`)');
        DB::statement('ALTER TABLE `trainerlavels` ADD INDEX(`id`)');
        DB::statement('ALTER TABLE `trainers` ADD INDEX(`id`)');
        DB::statement('ALTER TABLE `trainers` ADD INDEX(`user_id`)');
        DB::statement('ALTER TABLE `trainers` ADD INDEX(`country_id`)');
        DB::statement('ALTER TABLE `trainerstreams` ADD INDEX(`agegroup_id`)');
        DB::statement('ALTER TABLE `trainer_allocation` ADD INDEX(`trainer_id`)');
        DB::statement('ALTER TABLE `trainer_allocation` ADD INDEX(`day`)');
        DB::statement('ALTER TABLE `trainer_content_images` ADD INDEX(`trainercontents_id`)');
        DB::statement('ALTER TABLE `trainer_education_background` ADD INDEX(`trainer_id`)');
        DB::statement('ALTER TABLE `trainer_notifications` ADD INDEX(`trainer_id`)');
        DB::statement('ALTER TABLE `trainer_past_achievements` ADD INDEX(`trainer_id`)');
        DB::statement('ALTER TABLE `userprofiles` ADD INDEX(`user_id`)');
        DB::statement('ALTER TABLE `users` ADD INDEX(`country_id`)');
        DB::statement('ALTER TABLE `users` ADD INDEX(`id`)');
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
