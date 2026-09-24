<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('public_username', 191)->nullable()->after('name');
            $table->unique('public_username');
        });
    }

    public function down()
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropUnique('students_public_username_unique');
            $table->dropColumn('public_username');
        });
    }
};
