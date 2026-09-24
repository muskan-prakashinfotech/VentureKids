<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFormTokenToBusinessPlanAnswersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('business_plan_answers', function (Blueprint $table) {
            $table->string('form_token')->nullable()->after('is_correct')->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('business_plan_answers', function (Blueprint $table) {
           $table->dropColumn('form_token');
        });
    }
}
