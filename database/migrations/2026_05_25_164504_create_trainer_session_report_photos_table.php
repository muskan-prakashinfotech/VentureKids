<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrainerSessionReportPhotosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trainer_session_report_photos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('report_id');
            $table->enum('photo_type', ['without_faces', 'with_faces']);
            $table->string('file_path');
            $table->string('original_name');
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
        Schema::dropIfExists('trainer_session_report_photos');
    }
}
