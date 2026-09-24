<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventChallengesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('event_challenges', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id')->references('id')->on('events')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedBigInteger('student_id')->references('id')->on('students')->onUpdate('cascade')->onDelete('cascade');
            $table->text('description')->nullable();
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
        Schema::dropIfExists('event_challenges');
    }
}
