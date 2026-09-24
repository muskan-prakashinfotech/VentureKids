<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_section_id');
            $table->text('field_text');
            $table->enum('field_type', ['input', 'textarea', 'file'])->default('input');
            $table->text('help_text')->nullable();
            $table->boolean('is_required')->default(1);
            $table->boolean('allow_attachments')->default(0)->comment('1 = allowed, 0 = not allowed');
            $table->string('allowed_types')->nullable();
            $table->boolean('allowed_multiples')->default(0)->comment('1 = allowed, 0 = not allowed');
            $table->integer('display_order')->nullable();
            $table->boolean('status')->default(1)->comment('1 = active, 0 = inactive');
            $table->timestamps();

            $table->index('project_section_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_questions');
    }
};
