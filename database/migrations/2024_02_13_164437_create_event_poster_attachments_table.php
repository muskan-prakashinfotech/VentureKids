<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventPosterAttachmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('event_poster_attachments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id')->references('id')->on('events')->onUpdate('cascade')->onDelete('cascade');
            $table->text('title')->nullable();
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
        Schema::dropIfExists('event_poster_attachments');
    }
}
