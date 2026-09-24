<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddChallengeImageFieldToWeeklyChallengesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('weekly_challenges', function (Blueprint $table) {
            $table->string('challenge_image')->nullable()->after('challenge_name');
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
            $table->dropColumn('challenge_image');
        });
    }
}
