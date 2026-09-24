<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrainerContentImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trainer_content_images', function (Blueprint $table) {
            $table->id();
            $table->integer('trainercontents_id')->nullable();
            $table->string('attachment')->nullable();
            $table->timestamps();

            $table->foreign('trainercontents_id')->references('id')->on('trainercontents')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('trainer_content_images');
    }
}
