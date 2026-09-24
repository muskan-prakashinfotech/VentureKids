<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRealqAssessmentRubricsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('realq_assessment_rubrics', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('scale_id');
            $table->unsignedTinyInteger('score')->default(1);
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();

            // $table->foreign('scale_id')->references('id')->on('realq_assessment_scale')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('realq_assessment_rubrics');
    }
}
