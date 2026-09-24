<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameGenerateAiToolIdInAiToolSubcategories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ai_tool_subcategories', function (Blueprint $table) {
            $table->renameColumn('generate_ai_tool_id', 'ai_tool_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ai_tool_subcategories', function (Blueprint $table) {
            $table->renameColumn('ai_tool_id', 'generate_ai_tool_id');
        });
    }
}
