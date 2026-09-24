<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_sections', function (Blueprint $table) {
            $table->id();
            $table->string('section_title');
            $table->integer('display_order')->nullable();
            $table->boolean('status')->default(1)->comment('1 = active, 0 = inactive');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_sections');
    }
};
