<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSessionPresentationToTrainercontentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('trainercontents', function (Blueprint $table) {
            $table->string('session_presentation')->nullable()->after('worksheet_name');
            $table->string('session_presentation_name')->nullable()->after('session_presentation');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('trainercontents', function (Blueprint $table) {
            $table->dropColumn('session_presentation');
            $table->dropColumn('session_presentation_name');
        });
    }
}
