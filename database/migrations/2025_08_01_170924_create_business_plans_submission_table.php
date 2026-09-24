<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBusinessPlansSubmissionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('business_plans_submission', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_plan_id');
            $table->unsignedBigInteger('student_id');

            $table->boolean('is_submit')
                  ->default(0)
                  ->comment('0 = Not Submitted, 1 = Submitted');

            $table->date('submission_date')->nullable();
            $table->foreign('business_plan_id')->references('id')->on('business_plans')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('business_plans_submission');
    }
}
