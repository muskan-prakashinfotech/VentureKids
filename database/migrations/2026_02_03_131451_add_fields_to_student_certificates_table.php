<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddFieldsToStudentCertificatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('student_certificates')->truncate();

        Schema::table('student_certificates', function (Blueprint $table) {
            $table->dropColumn(['unique_id', 'issue_date', 'download_token', 'token_expires_at']);
        });
        
        Schema::table('student_certificates', function (Blueprint $table) {
            $table->string('unique_id')->unique()->after('id');
            $table->date('issue_date')->after('trainer_id');
            $table->string('download_token')->nullable()->after('issue_date');
            $table->timestamp('token_expires_at')->nullable()->after('download_token');
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
            $table->dropColumn(['unique_id', 'issue_date', 'download_token', 'token_expires_at']);
        });
    }
}
