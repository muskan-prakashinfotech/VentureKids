<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTopicsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('topics', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('grade_id')->nullable();
            $table->unsignedBigInteger('board_id')->nullable();
            $table->unsignedBigInteger('country_id')->nullable();
            $table->text('topic')->nullable();
            $table->text('prompt')->nullable();
            $table->longText('raw_response')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('topics');
    }
}
