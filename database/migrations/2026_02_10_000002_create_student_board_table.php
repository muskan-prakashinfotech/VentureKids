<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentBoardTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('student_board', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->boolean('is_other')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('student_board');
    }
}
