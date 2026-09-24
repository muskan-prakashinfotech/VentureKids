<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddQuizTypeFieldToQuizAnswersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('quiz_answers', function (Blueprint $table) {
            $table->enum('quiz_type', ['session', 'weekly_challenge'])->nullable()->default('session')->after('quiz_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('quiz_answers', function (Blueprint $table) {
            $table->dropColumn('quiz_type');
        });
    }
}
