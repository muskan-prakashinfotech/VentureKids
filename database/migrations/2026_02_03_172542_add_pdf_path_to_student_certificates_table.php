<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPdfPathToStudentCertificatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('student_certificates', function (Blueprint $table) {
            $table->string('pdf_path')->nullable()->after('token_expires_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('student_certificates', function (Blueprint $table) {
            $table->dropColumn('pdf_path');
        });
    }
}
