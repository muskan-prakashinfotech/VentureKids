<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToStreamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('streams', function (Blueprint $table) {
            $table->text('description')->nullable()->after('title');
            $table->string('videoUrl',512)->nullable()->after('description');
            $table->string('image')->nullable()->after('videoUrl');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('streams', function (Blueprint $table) {
            $table->dropColumn('description');
            $table->dropColumn('videoUrl');
            $table->dropColumn('image');
        });
    }
}
