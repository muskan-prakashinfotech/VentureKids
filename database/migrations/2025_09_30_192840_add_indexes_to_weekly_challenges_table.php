<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexesToWeeklyChallengesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('weekly_challenges', function (Blueprint $table) {
            // Primary composite index for most common query
            $table->index(['is_active', 'challenge_type'], 'idx_active_type');
            
            // For sorting by date
            $table->index(['is_active', 'created_at'], 'idx_active_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
       Schema::table('weekly_challenges', function (Blueprint $table) {
            $table->dropIndex('idx_active_type');
            $table->dropIndex('idx_active_date');
        });
    }
}
