<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddModerationStatusToTopicsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('topics', function (Blueprint $table) {
            $table->enum('moderation_status', ['pending', 'approved', 'rejected'])->default('pending')->after('topic');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('topics', function (Blueprint $table) {
            $table->dropColumn('moderation_status');
        });
    }
}
