<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVisibilityTypeToEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('events', function (Blueprint $table) {
            $table->tinyInteger('visibility_type')
              ->default(2) // 2 = Country-Specific (for existing records)
              ->after('event_video')
              ->comment('1 = Global, 2 = Country-Specific');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('events', function (Blueprint $table) {
              $table->dropColumn('visibility_type');
        });
    }
}
