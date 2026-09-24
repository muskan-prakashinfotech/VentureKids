<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCreatedByToExternalSessionTable extends Migration
{
    public function up()
    {
        Schema::table('external_session', function (Blueprint $table) {
            if (!Schema::hasColumn('external_session', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('title');
            }
        });
    }

    public function down()
    {
        Schema::table('external_session', function (Blueprint $table) {
            if (Schema::hasColumn('external_session', 'created_by')) {
                $table->dropColumn('created_by');
            }
        });
    }
}
