<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoginTrackingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('login_trackings', function (Blueprint $table) {
            $table->id();
            $table->integer('user_type')->default(1)->comment('Student=1, School=2, Trainer=3 ');
            $table->unsignedBigInteger('item_id'); // ID of the user (admin, school, trainer, student)
            $table->dateTime('login_at'); // When login occurred
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
        Schema::dropIfExists('login_trackings');
    }
}
