<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE realq_assessment_questions
            MODIFY moderation_status ENUM('pending', 'approved', 'rejected', 'archived')
            NOT NULL DEFAULT 'pending'
        ");
    }

    public function down(): void
    {
        DB::statement("
            UPDATE realq_assessment_questions
            SET moderation_status = 'approved'
            WHERE moderation_status = 'archived'
        ");

        DB::statement("
            ALTER TABLE realq_assessment_questions
            MODIFY moderation_status ENUM('pending', 'approved', 'rejected')
            NOT NULL DEFAULT 'pending'
        ");
    }
};
