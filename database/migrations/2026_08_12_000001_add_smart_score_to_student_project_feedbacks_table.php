<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_project_feedbacks', function (Blueprint $table) {
            $table->unsignedTinyInteger('smart_score')->nullable()->after('private_suggestions');
        });
    }

    public function down(): void
    {
        Schema::table('student_project_feedbacks', function (Blueprint $table) {
            $table->dropColumn('smart_score');
        });
    }
};
