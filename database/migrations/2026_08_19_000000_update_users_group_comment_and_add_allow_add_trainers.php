<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'allow_add_trainers')) {
                $table->unsignedInteger('allow_add_trainers')->nullable()->default(0)->after('group');
            }
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `users` MODIFY `group` INT(11) NOT NULL DEFAULT 1 COMMENT 'Super Admin=1,School=2,Trainer=3,Student=4,Sub Admin=5'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `users` MODIFY `group` INT(11) NOT NULL DEFAULT 1 COMMENT 'Admin=1,School=2,Trainer=3,Student=4'");
        }

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'allow_add_trainers')) {
                $table->dropColumn('allow_add_trainers');
            }
        });
    }
};
