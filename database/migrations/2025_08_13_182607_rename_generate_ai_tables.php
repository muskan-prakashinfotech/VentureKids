<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameGenerateAiTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::rename('generate_ai_tools', 'ai_tools');
        Schema::rename('generate_ai_tool_subcategories', 'ai_tool_subcategories');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
       Schema::rename('ai_tools', 'generate_ai_tools');
       Schema::rename('ai_tool_subcategories', 'generate_ai_tool_subcategories');
    }
}
