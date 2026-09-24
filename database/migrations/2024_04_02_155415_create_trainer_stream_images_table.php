<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrainerStreamImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trainer_stream_images', function (Blueprint $table) {
            $table->id();
            $table->integer('stream_id')->nullable();
            $table->string('attachment')->nullable();
            $table->timestamps();
            $table->foreign('stream_id')->references('id')->on('trainerstreams')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('trainer_stream_images');
    }
}
