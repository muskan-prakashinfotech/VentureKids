<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketplace_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->string('name', 60);
            $table->unsignedBigInteger('currency_id');
            $table->string('story', 500);
            $table->string('description', 500);
            $table->string('special_feature', 300)->comment('What makes your product special');
            $table->decimal('price', 10, 2);
            $table->tinyInteger('status')->default(0)->comment('0 = draft, 1 = published');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketplace_products');
    }
};
