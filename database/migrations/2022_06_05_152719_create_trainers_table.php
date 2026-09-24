<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTrainersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trainers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id');
            $table->string('trainer_name');
            $table->string('email');
            $table->Integer('hour');
            $table->double('trainer_fee', 10, 2);
            $table->string('contact_no')->nullable();

            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('join_date')->nullable();
            $table->string('date_of_birth')->nullable();
            $table->string('image')->nullable();
            $table->tinyInteger('mode')->default(1)->unsigned();
            $table->tinyInteger('type')->default(1)->unsigned();
            $table->tinyInteger('status')->default(1)->unsigned();
            $table->integer('no_of_hour_per_week')->default(0);
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
        Schema::dropIfExists('trainers');
    }
}
