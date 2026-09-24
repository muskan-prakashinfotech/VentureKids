<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'no_of_license_purchased')) {
                $table->unsignedInteger('no_of_license_purchased')->nullable()->default(0)->after('allow_add_trainers');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'no_of_license_purchased')) {
                $table->dropColumn('no_of_license_purchased');
            }
        });
    }
};
