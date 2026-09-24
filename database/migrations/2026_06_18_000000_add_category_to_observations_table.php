<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCategoryToObservationsTable extends Migration
{
    public function up()
    {
        Schema::table('observations', function (Blueprint $table) {
            $table->enum('category', ['skill', 'mindset'])->nullable()->after('name');
        });
    }

    public function down()
    {
        Schema::table('observations', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }
}
