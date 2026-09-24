<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateViewTrackingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('view_trackings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id');
            $table->unsignedInteger('student_id');
            $table->enum('item_type', ['audio', 'video'])->default('audio');
            $table->unsignedBigInteger('play_count')->default(0);
            $table->unique(['item_id', 'student_id', 'item_type']);
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
        Schema::dropIfExists('view_trackings');
    }
}
