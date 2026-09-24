<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSchoolsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('schools', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('school_name');
            $table->string('school_address');
            $table->string('year_establish');
            $table->string('incharge_name');
            $table->string('email');
            $table->string('contact_number');
            $table->string('partner_name');
            $table->date('course_start_date');
            $table->string('create_entrepreneurship');
            $table->date('weekly_class_time');
            $table->integer('package')->default(0);
            $table->string('school_logo');
            $table->string('upload_excel');
            $table->text('school_grade');
            $table->integer('status')->default(0);
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
        Schema::dropIfExists('schools');
    }
}
