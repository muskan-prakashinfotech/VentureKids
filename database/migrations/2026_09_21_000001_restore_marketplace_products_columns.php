<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The live `marketplace_products` table was hand-altered outside of
 * Laravel's migration system at some point: student_id/name/currency_id/story
 * were moved out into a new `marketplaces` table (linked back via
 * marketplace_id), but the application code (StudentMarketplaceController,
 * MarketplaceProduct model, and the marketplace blade views) was never
 * updated to match and still expects those four columns directly on
 * marketplace_products. This restores them and backfills existing rows
 * from their linked marketplaces row, bringing the schema back in line
 * with 2026_08_14_000001_create_marketplace_products_table.php and the
 * current application code.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('marketplace_products', function (Blueprint $table) {
            if (!Schema::hasColumn('marketplace_products', 'student_id')) {
                $table->unsignedBigInteger('student_id')->nullable()->after('id');
            }
            if (!Schema::hasColumn('marketplace_products', 'name')) {
                $table->string('name', 60)->nullable()->after('student_id');
            }
            if (!Schema::hasColumn('marketplace_products', 'currency_id')) {
                $table->unsignedBigInteger('currency_id')->nullable()->after('name');
            }
            if (!Schema::hasColumn('marketplace_products', 'story')) {
                $table->string('story', 500)->nullable()->after('currency_id');
            }
        });

        // Backfill from the linked marketplaces row where one exists.
        DB::statement('
            UPDATE marketplace_products mp
            INNER JOIN marketplaces m ON m.id = mp.marketplace_id
            SET mp.student_id = m.student_id,
                mp.name = m.name,
                mp.currency_id = m.currency_id,
                mp.story = m.story
            WHERE mp.marketplace_id IS NOT NULL
        ');

        // Orphaned rows (marketplace_id points at a deleted/missing marketplace):
        // all confirmed to be draft test rows, so fill with safe placeholders
        // rather than leaving required-by-app-code fields null.
        $defaultCurrencyId = DB::table('currencies')->orderBy('id')->value('id');

        DB::table('marketplace_products')
            ->whereNull('student_id')
            ->update([
                'name' => 'My Marketplace',
                'currency_id' => $defaultCurrencyId,
                'story' => '',
            ]);
    }

    public function down(): void
    {
        Schema::table('marketplace_products', function (Blueprint $table) {
            $table->dropColumn(['student_id', 'name', 'currency_id', 'story']);
        });
    }
};
