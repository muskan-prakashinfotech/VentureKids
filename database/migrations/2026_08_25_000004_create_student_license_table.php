<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentLicenseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('student_license', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('school_id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('level_id');
            $table->string('license_key')->nullable();
            $table->tinyInteger('status')->default(1)->comment('1=active, 0=inactive');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'student_id']);
            $table->index(['school_id', 'level_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('student_license');
    }
}
