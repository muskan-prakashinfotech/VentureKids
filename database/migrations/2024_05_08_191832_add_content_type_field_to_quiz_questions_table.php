<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddContentTypeFieldToQuizQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('quiz_questions', function (Blueprint $table) {
            $table->enum('content_type', ['session', 'weekly_challenge'])->nullable()->default('session')->after('content_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('quiz_questions', function (Blueprint $table) {
            $table->dropColumn('content_type');
        });
    }
}
