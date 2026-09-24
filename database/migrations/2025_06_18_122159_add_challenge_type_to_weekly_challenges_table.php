<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddChallengeTypeToWeeklyChallengesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('weekly_challenges', function (Blueprint $table) {
            $table->enum('challenge_type', ['weekly','daily'])->nullable()->default('weekly')->after('challenge_image');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('weekly_challenges', function (Blueprint $table) {
            //
        });
    }
}
