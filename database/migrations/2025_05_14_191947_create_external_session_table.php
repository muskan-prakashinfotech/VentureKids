<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExternalSessionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('external_session', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255);
            $table->text('agenda')->nullable();
            $table->dateTime('date_time'); 
            $table->string('zoom_link', 512); 
            $table->boolean('send_zoom_link')->default(true); 
            $table->string('speaker', 100); 
            $table->string('attendees', 512); 
            $table->string('levels', 512); 
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
        Schema::dropIfExists('external_session');
    }
}
