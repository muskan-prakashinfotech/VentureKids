<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrainerAllocationNewTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trainer_allocation_new', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('school_id')->nullable()->references('id')->on('schools')->onDelete('cascade');
            $table->unsignedInteger('trainer_id')->nullable()->references('id')->on('trainers')->onDelete('cascade');
            $table->unsignedInteger('school_batch_id')->nullable()->references('id')->on('school_batch')->onDelete('cascade');
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
        Schema::dropIfExists('trainer_allocation_new');
    }
}
