<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventChallengeFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('event_challenge_files', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_challenge_id')->references('id')->on('event_challenges')->onUpdate('cascade')->onDelete('cascade');
            $table->string('attachment')->nullable();
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
        Schema::dropIfExists('event_challenge_files');
    }
}
