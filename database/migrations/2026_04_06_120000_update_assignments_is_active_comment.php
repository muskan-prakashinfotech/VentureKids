<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE `assignments` MODIFY COLUMN `is_active` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=active, 0=inactive, 2=hide'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `assignments` MODIFY COLUMN `is_active` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=active, 0=inactive'");
    }
};
