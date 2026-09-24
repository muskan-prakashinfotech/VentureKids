<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOptionalDetailsToStudentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('optional_details_1',512)->nullable()->after('status');
            $table->string('optional_details_2',512)->nullable()->after('optional_details_1');
            $table->string('optional_details_3',512)->nullable()->after('optional_details_2');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['optional_details_1', 'optional_details_2', 'optional_details_3']);
        });
    }
}
