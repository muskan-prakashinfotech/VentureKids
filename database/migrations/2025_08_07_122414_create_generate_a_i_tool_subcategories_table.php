<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGenerateAIToolSubcategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('generate_ai_tool_subcategories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('generate_ai_tool_id')->constrained('generate_ai_tools')->onDelete('cascade');
            $table->string('name');
            $table->string('image'); 
            $table->text('description');
            $table->integer('display_order')->nullable();
            $table->boolean('status')->default(0)->comment('1 = active, 0 = inactive');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('generate_ai_tool_subcategories');
    }
}
